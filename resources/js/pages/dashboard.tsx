import AppLayout from '@/layouts/app-layout';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head, router } from '@inertiajs/react';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Dashboard', href: dashboard().url }];

interface Transaction {
    id: number;
    created_at: string;
    from: string;
    message: string;
    status: string;
}

interface UserBalance {
    id: number;
    phone_number: string;
    amount: number;
    updated_at: string;
}

interface DashboardProps {
    stats: {
        withdrawal_profit: string;
        maintenance_revenue: string;
        total_revenue: string;
        total_vault: string;
    };
    transactions: Transaction[];
    users: UserBalance[];
    filters: { search: string };
}

export default function Dashboard({ stats, transactions, users, filters }: DashboardProps) {
    const [search, setSearch] = useState(filters.search || '');

    const handleSearch = (e: React.FormEvent) => {
        e.preventDefault();
        router.get('/dashboard', { search }, { preserveState: true });
    };

    const approveTransaction = (id: number) => {
        if (confirm('Are you sure you want to mark this cash withdrawal as paid?')) {
            router.post(`/transactions/${id}/approve`);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="E-Saving Command Center" />
            <div className="flex h-full flex-1 flex-col gap-6 p-6 bg-gray-50 dark:bg-neutral-950">
                
                <h2 className="text-2xl font-black text-gray-800 dark:text-white uppercase tracking-tight">💰 E-Saving Command Center</h2>

                {/* 1. STATS CARDS */}
                <div className="grid gap-4 md:grid-cols-4">
                    <div className="rounded-2xl bg-gradient-to-br from-blue-700 to-blue-900 p-6 shadow-lg text-white">
                        <p className="text-xs font-bold uppercase opacity-70">Withdrawal Profit (3%)</p>
                        <h2 className="mt-2 text-3xl font-black">N${stats.withdrawal_profit}</h2>
                    </div>
                    <div className="rounded-2xl bg-gradient-to-br from-yellow-400 to-orange-600 p-6 shadow-lg text-black">
                        <p className="text-xs font-bold uppercase opacity-70">Maintenance Revenue</p>
                        <h2 className="mt-2 text-3xl font-black">N${stats.maintenance_revenue}</h2>
                    </div>
                    <div className="rounded-2xl bg-emerald-600 p-6 shadow-lg text-white">
                        <p className="text-xs font-bold uppercase opacity-70">Total Net Revenue</p>
                        <h2 className="mt-2 text-3xl font-black">N${stats.total_revenue}</h2>
                    </div>
                    <div className="rounded-2xl bg-gray-800 p-6 shadow-lg text-white">
                        <p className="text-xs font-bold uppercase opacity-70">Vault Total</p>
                        <h2 className="mt-2 text-3xl font-black">N${stats.total_vault}</h2>
                    </div>
                </div>

                {/* 2. SEARCH BAR */}
                <form onSubmit={handleSearch} className="flex gap-2">
                    <input 
                        type="text" 
                        placeholder="Search Phone Number..." 
                        className="flex-1 rounded-lg border-gray-300 dark:bg-neutral-900 dark:border-neutral-700 text-sm"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                    />
                    <button type="submit" className="bg-blue-600 text-white px-4 py-2 rounded-lg font-bold text-sm hover:bg-blue-700 transition-colors">Search</button>
                </form>

                {/* 3. USER DIRECTORY */}
                <div className="rounded-xl border bg-white dark:bg-neutral-900 overflow-hidden shadow-sm">
                    <div className="p-4 bg-gray-100 dark:bg-neutral-800 font-bold border-b">User Vault Directory</div>
                    <table className="w-full text-left text-sm">
                        <thead>
                            <tr className="text-gray-500 border-b">
                                <th className="p-4">Phone Number</th>
                                <th className="p-4">Balance</th>
                                <th className="p-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {users.map(user => (
                                <tr key={user.id} className="border-b hover:bg-gray-50 dark:hover:bg-neutral-800">
                                    <td className="p-4 font-bold">{user.phone_number}</td>
                                    <td className="p-4 text-emerald-600 font-black">N${user.amount}</td>
                                    <td className="p-4">
                                        <span className="bg-emerald-100 text-emerald-700 px-2 py-1 rounded-full text-[10px] font-bold uppercase">Active</span>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>

                {/* 4. TRANSACTION FEED */}
                <div className="rounded-xl border bg-white dark:bg-neutral-900 overflow-hidden shadow-sm">
                    <div className="p-4 bg-gray-800 text-white font-bold">Live Transaction Feed</div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-left text-sm">
                            <thead className="bg-gray-50 dark:bg-neutral-800 text-gray-600 border-b">
                                <tr>
                                    <th className="p-4">Time</th>
                                    <th className="p-4">User</th>
                                    <th className="p-4">Message</th>
                                    <th className="p-4">System Status</th>
                                    <th className="p-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {transactions.map(trx => (
                                    <tr key={trx.id} className="border-b hover:bg-gray-50 dark:hover:bg-neutral-800 transition-colors">
                                        <td className="p-4 text-xs text-gray-500">{new Date(trx.created_at).toLocaleString()}</td>
                                        <td className="p-4 font-bold">{trx.from}</td>
                                        <td className="p-4 font-mono text-xs italic">{trx.message}</td>
                                        <td className="p-4">
                                            <span className={`px-2 py-1 rounded text-[10px] font-bold uppercase ${
                                                trx.status.includes('Pending') 
                                                    ? 'bg-orange-100 text-orange-700 border border-orange-200' 
                                                    : 'bg-blue-50 text-blue-700 border border-blue-100'
                                            }`}>
                                                {trx.status.replace(/_/g, ' ')}
                                            </span>
                                        </td>
                                        <td className="p-4">
                                            {trx.status === 'Withdrawn_CASH_Pending' && (
                                                <button
                                                    onClick={() => approveTransaction(trx.id)}
                                                    className="bg-emerald-600 hover:bg-emerald-700 text-white text-[10px] font-black py-1.5 px-3 rounded shadow-sm transition-all active:scale-95 uppercase tracking-wider"
                                                >
                                                    Mark as Paid
                                                </button>
                                            )}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}