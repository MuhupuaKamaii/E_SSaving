<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Balance;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Calculate Profits (Only count successful withdrawals for accurate revenue)
        $withdrawalProfit = Transaction::where('status', 'Withdrawn_CASH_Success')
            ->get()
            ->sum(function($trx) {
                $parts = explode(' ', strtoupper($trx->message));
                return (isset($parts[1]) && is_numeric($parts[1])) ? (float)$parts[1] * 0.03 : 0;
            });

        $maintenanceRevenue = Transaction::where('status', 'fee_deducted')->count() * 0.50;

        // Fetch Users (with search)
        $users = Balance::when($search, function($query, $search) {
            $query->where('phone_number', 'like', "%{$search}%");
        })->orderBy('amount', 'desc')->get();

        // Fetch Transactions (with search)
        $transactions = Transaction::when($search, function($query, $search) {
            $query->where('from', 'like', "%{$search}%");
        })->latest()->limit(50)->get();

        return Inertia::render('dashboard', [
            'stats' => [
                'withdrawal_profit' => number_format($withdrawalProfit, 2),
                'maintenance_revenue' => number_format($maintenanceRevenue, 2),
                'total_revenue' => number_format($withdrawalProfit + $maintenanceRevenue, 2),
                'total_vault' => number_format(Balance::sum('amount'), 2),
            ],
            'transactions' => $transactions,
            'users' => $users,
            'filters' => $request->only(['search'])
        ]);
    }

    /**
     * Logic for the "Mark as Paid" button
     */
    public function approveTransaction($id)
    {
        // 1. Find the transaction or fail
        $transaction = Transaction::findOrFail($id);

        // 2. Only update if it is currently pending to avoid double-processing
        if ($transaction->status === 'Withdrawn_CASH_Pending') {
            $transaction->update([
                'status' => 'Withdrawn_CASH_Success'
            ]);

            // 3. Return back with a success message
            return back()->with('message', 'Payment marked as successful!');
        }

        return back()->with('error', 'Transaction already processed or invalid.');
    }
}