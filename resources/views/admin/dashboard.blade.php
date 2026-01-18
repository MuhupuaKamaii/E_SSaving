<!DOCTYPE html>
<html lang="en">
<head>
    <title>E-Saving | Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        .card-gold { background: linear-gradient(45deg, #FFD700, #FFA500); color: black; }
        .card-blue { background: linear-gradient(45deg, #1e3c72, #2a5298); color: white; }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h2 class="fw-bold mb-4">💰 E-SAVING COMMAND CENTER</h2>
        
        <div class="row g-3">
            <div class="col-md-4">
                <div class="card card-blue p-4 shadow border-0">
                    <h6>Withdrawal Profit (3%)</h6>
                    <h2 class="fw-bold">N${{ number_format($withdrawal_profit, 2) }}</h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card card-gold p-4 shadow border-0">
                    <h6>Maintenance Revenue</h6>
                    <h2 class="fw-bold">N${{ number_format($maintenance_revenue, 2) }}</h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white p-4 shadow border-0">
                    <h6>Total Net Revenue</h6>
                    <h2 class="fw-bold">N${{ number_format($total_revenue, 2) }}</h2>
                </div>
            </div>
        </div>

        <div class="mt-5 bg-white p-4 rounded shadow-sm">
            <h4>Live Transaction Feed</h4>
            <form action="/admin/dashboard" method="GET" class="mb-4 d-flex">
    <input type="text" name="search" class="form-control me-2" placeholder="Search Phone Number..." value="{{ $search }}">
    <button type="submit" class="btn btn-primary">Search</button>
    <a href="/admin/dashboard" class="btn btn-outline-secondary ms-2">Reset</a>
</form>
            <table class="table table-hover mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>Time</th>
                        <th>User (Phone)</th>
                        <th>Message</th>
                        <th>System Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recent_transactions as $trx)
                    <tr>
                        <td>{{ $trx->created_at->format('d M, H:i') }}</td>
                        <td>{{ $trx->from }}</td>
                        <td><code>{{ $trx->message }}</code></td>
                        <td><span class="badge bg-info text-dark">{{ $trx->status }}</span></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>