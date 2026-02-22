<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Transaction;
use App\Models\Balance;
use Illuminate\Support\Facades\Log;

Route::post('/test', function (Request $request) {
    // 1. SECURITY: Verify Secret Key
    if ($request->header('X-Vault-Key') !== env('VAULT_SECRET_KEY')) {
        return response()->json(['error' => 'Unauthorized Access'], 401);
    }

    $phone = $request->from;
    $incomingMessage = strtoupper(trim($request->text));

    if (empty($incomingMessage)) {
        return response()->json(['reply' => 'ERROR: EMPTY MESSAGE']);
    }

    $userBalance = Balance::firstOrCreate(['phone_number' => $phone], ['amount' => 0]);

    // 2. LOGIC: ACTIVITY EXTENSION & MAINTENANCE FEE (N$0.50)
    $daysInactive = $userBalance->updated_at->diffInDays(now());
    $feeAlert = "";

    if ($daysInactive > 7 && $userBalance->amount > 0) {
        $userBalance->amount = max(0, $userBalance->amount - 0.50);
        
        Transaction::create([
            'from'    => $phone, 
            'message' => "FEE: INACTIVITY N$0.50", 
            'status'  => 'fee_deducted'
        ]);

        // Fee alert shown inline (no newlines) so SMS replies are single-line friendly.
        $feeAlert = " (Note: N$0.50 inactivity fee applied)";
        $userBalance->save(); 
    }

    // 3. COMMAND: BALANCE
    if ($incomingMessage == 'BALANCE') {
    $reply = "Vault balance: N$".number_format($userBalance->amount, 2) . $feeAlert;
        $status = 'balance_check';
    }

    // 4. COMMAND: WITHDRAW (6.5% Fee)
    elseif (str_starts_with($incomingMessage, 'WITHDRAW')) {
        $parts = explode(' ', $incomingMessage);
        $amount = (isset($parts[1]) && is_numeric($parts[1])) ? (float)$parts[1] : 0;
        $type = isset($parts[2]) ? strtoupper($parts[2]) : 'CASH';

            if ($type == 'CASH' && $amount < 10) {
                $reply = "Minimum withdraw is N$10.00." . $feeAlert;
                $status = 'denied_low_amount';
            } elseif ($amount > $userBalance->amount) {
                $reply = "Insufficient funds. Balance: N$".number_format($userBalance->amount, 2) . $feeAlert;
                $status = 'denied_no_funds';
            } else {
                $userGets = $amount - ($amount * 0.065);
                $userBalance->amount -= $amount;

                if ($type === 'AIRTIME') {
                    $payoutSuccess = processAirtimePayout($phone, $userGets);
                    $status = $payoutSuccess ? "Withdrawn_AIRTIME_Success" : "Withdrawn_AIRTIME_Failed";
                    $reply = "Success: AIRTIME. Sent: N$".number_format($userGets, 2).". New balance: N$".number_format($userBalance->amount, 2) . $feeAlert;
                } else {
                    $status = "Withdrawn_CASH_Pending";
                    $reply = "Withdrawal requested: CASH N$".number_format($amount, 2).". Amount after fee: N$".number_format($userGets, 2).". New balance: N$".number_format($userBalance->amount, 2) . $feeAlert;
                }
            }
    }

    // 5. COMMAND: DEPOSIT
    elseif (is_numeric($incomingMessage)) {
        $saveAmount = (float)$incomingMessage;
        if ($saveAmount > 0) {
            $userBalance->amount += $saveAmount;
        $reply = "Saved N$".number_format($saveAmount, 2) . ". Total balance: N$".number_format($userBalance->amount, 2) . $feeAlert;
            $status = 'deposit';
        } else {
            $reply = "Error: Invalid amount.";
            $status = 'deposit_error';
        }
    }

    // 6. DEFAULT: MENU
    else {
    $reply = "E-SAVING VAULT: Send an amount to SAVE to deposit. Use 'WITHDRAW [amt] [CASH/AIR]' to withdraw, or send 'BALANCE' to check your balance.";
        $status = 'help_request';
    }

    $userBalance->save();
    Transaction::create(['from' => $phone, 'message' => $request->text, 'status' => $status]);
    
    sendSmsNotification($phone, $reply);

    return response()->json(['reply' => $reply]);
});

function sendSmsNotification($to, $message) {
    Log::info("SMS TO $to: $message");
}

function processAirtimePayout($phone, $amount) {
    Log::info("AIRTIME PAYOUT: Sending N$$amount to $phone via MTC/Telecom Gateway");
    return true; 
}