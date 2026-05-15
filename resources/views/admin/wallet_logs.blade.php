@extends('layouts.admin')

@section('content')
<div class="pt-0 pb-10">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
        <div>
            <h1 class="text-4xl font-black tracking-tighter uppercase italic">Log Audit MeyPay</h1>
            <p class="text-gray-500 mt-1 font-mono text-xs uppercase tracking-widest">Transparansi Keuangan & Aliran Integritas Transaksi</p>
        </div>

        <form action="{{ route('admin.wallet_logs') }}" method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari user atau deskripsi..."
                class="px-4 py-2 border border-indigo-100 rounded-xl text-xs font-bold uppercase focus:outline-none focus:border-purple-400 bg-white/60 backdrop-blur transition-all">

            <select name="type" class="px-4 py-2 border border-indigo-100 rounded-xl text-xs font-black uppercase focus:outline-none focus:border-purple-400 bg-white/60 backdrop-blur transition-all">
                <option value="">Semua Tipe</option>
                <option value="topup"   {{ request('type') == 'topup'   ? 'selected' : '' }}>Topup</option>
                <option value="payment" {{ request('type') == 'payment' ? 'selected' : '' }}>Payment</option>
                <option value="payout"  {{ request('type') == 'payout'  ? 'selected' : '' }}>Payout</option>
                <option value="refund"  {{ request('type') == 'refund'  ? 'selected' : '' }}>Refund</option>
            </select>

            <button type="submit" class="btn-gradient text-white px-6 py-2 text-xs font-black uppercase rounded-xl transition-all">
                Filter
            </button>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="glass-card overflow-hidden">
        <div class="gradient-header-purple px-6 py-5 text-white flex items-center justify-between">
            <h2 class="font-black text-sm uppercase tracking-tight">Riwayat Log Wallet</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="glass-table-header text-slate-600 font-black uppercase">
                    <tr>
                        <th class="px-6 py-5">Waktu</th>
                        <th class="px-6 py-5">Pengguna / Wallet</th>
                        <th class="px-6 py-5">Keterangan</th>
                        <th class="px-6 py-5">Tipe</th>
                        <th class="px-6 py-5 text-right">Jumlah</th>
                        <th class="px-6 py-5 text-right">Saldo Setelah</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-indigo-50 font-bold">
                    @forelse($logs as $log)
                    <tr class="hover:bg-white/40 transition-all">
                        <td class="px-6 py-6 font-mono text-[10px] text-gray-500">
                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="px-6 py-6">
                            <div class="uppercase text-sm text-slate-800">{{ $log->wallet->user->name ?? 'SYSTEM' }}</div>
                            <div class="text-[9px] font-mono text-gray-400 uppercase mt-1">{{ $log->wallet->wallet_number }}</div>
                        </td>
                        <td class="px-6 py-6">
                            <div class="text-gray-600 italic">{{ $log->description }}</div>
                            @if($log->reference_id)
                                <div class="text-[9px] font-mono text-slate-800 mt-1 uppercase">Ref: {{ $log->reference_type }} #{{ $log->reference_id }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-6">
                            @php
                                $typeColor = match($log->type) {
                                    'topup'   => 'bg-teal-50 text-teal-700 border-teal-200',
                                    'payment' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'payout'  => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'refund'  => 'bg-orange-50 text-orange-700 border-orange-200',
                                    default   => 'bg-slate-50 text-slate-700 border-slate-200',
                                };
                            @endphp
                            <span class="px-2 py-1 border rounded-lg text-[9px] font-black uppercase {{ $typeColor }}">
                                {{ $log->type }}
                            </span>
                        </td>
                        <td class="px-6 py-6 text-right font-black text-sm {{ $log->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $log->amount > 0 ? '+' : '' }}{{ number_format($log->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-6 text-right font-black text-sm text-slate-800">
                            Rp {{ number_format($log->balance_after, 0, ',', '.') }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-20 text-center text-gray-400 font-black uppercase italic tracking-widest">
                            Tidak ada data yang ditemukan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $logs->links() }}
    </div>
</div>
@endsection
