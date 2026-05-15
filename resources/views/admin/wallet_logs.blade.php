@extends('layouts.admin')

@section('content')
<div class="pt-0 pb-10">
    <!-- Header Neo Brutalism -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
        <div>
            <h1 class="text-4xl font-black tracking-tighter uppercase italic">Log Audit MeyPay</h1>
            <p class="text-gray-500 mt-1 font-mono text-xs uppercase tracking-widest">Transparansi Keuangan & Aliran Integritas Transaksi</p>
        </div>
        
        <form action="{{ route('admin.wallet_logs') }}" method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}" 
                placeholder="Cari user atau deskripsi..." 
                class="px-4 py-2 border-[3px] border-black text-xs font-bold uppercase focus:outline-none neo-brutalism">
            
            <select name="type" class="px-4 py-2 border-[3px] border-black text-xs font-black uppercase focus:outline-none neo-brutalism bg-white">
                <option value="">Semua Tipe</option>
                <option value="topup" {{ request('type') == 'topup' ? 'selected' : '' }}>Topup</option>
                <option value="payment" {{ request('type') == 'payment' ? 'selected' : '' }}>Payment</option>
                <option value="payout" {{ request('type') == 'payout' ? 'selected' : '' }}>Payout</option>
                <option value="refund" {{ request('type') == 'refund' ? 'selected' : '' }}>Refund</option>
            </select>

            <button type="submit" class="px-6 py-2 bg-black text-white text-xs font-black uppercase neo-brutalism hover:translate-x-1 hover:translate-y-1 hover:shadow-none transition-all">
                Filter
            </button>
        </form>
    </div>

    <!-- Logs Table -->
    <div class="bg-white border-[3px] border-black neo-brutalism overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-100 border-b-[3px] border-black text-black font-black uppercase">
                    <tr>
                        <th class="px-6 py-5">Waktu</th>
                        <th class="px-6 py-5">Pengguna / Wallet</th>
                        <th class="px-6 py-5">Keterangan</th>
                        <th class="px-6 py-5">Tipe</th>
                        <th class="px-6 py-5 text-right">Jumlah</th>
                        <th class="px-6 py-5 text-right">Saldo Setelah</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-gray-100 font-bold">
                    @forelse($logs as $log)
                    <tr class="hover:bg-gray-50 transition-all">
                        <td class="px-6 py-6 font-mono text-[10px] text-gray-500">
                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="px-6 py-6">
                            <div class="uppercase text-sm">{{ $log->wallet->user->name ?? 'SYSTEM' }}</div>
                            <div class="text-[9px] font-mono text-gray-400 uppercase mt-1">{{ $log->wallet->wallet_number }}</div>
                        </td>
                        <td class="px-6 py-6">
                            <div class="text-gray-600 italic">{{ $log->description }}</div>
                            @if($log->reference_id)
                                <div class="text-[9px] font-mono text-black mt-1 uppercase">Ref: {{ $log->reference_type }} #{{ $log->reference_id }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-6">
                            <span class="px-2 py-1 border-2 border-black bg-white text-[9px] font-black uppercase">
                                {{ $log->type }}
                            </span>
                        </td>
                        <td class="px-6 py-6 text-right font-black text-sm {{ $log->amount > 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ $log->amount > 0 ? '+' : '' }}{{ number_format($log->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-6 text-right font-black text-sm">
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
