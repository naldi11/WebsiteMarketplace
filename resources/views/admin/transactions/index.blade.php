@extends('layouts.admin')

@section('title', 'Log Transaksi')

@section('content')
    <div class="pt-0 pb-2">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-12">
            <div>
                <h1 class="text-4xl font-black tracking-tighter uppercase italic text-slate-800">Log Transaksi</h1>
                <p class="text-slate-500 mt-1 font-mono text-xs uppercase tracking-widest">Aliran Pesanan Global & Kontrol Pembukuan</p>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="mb-8 border border-indigo-100 rounded-xl bg-white/40 p-1 inline-flex flex-wrap gap-1">
            <a href="{{ route('admin.transactions', ['tab' => 'all']) }}"
                class="px-6 py-2 text-xs font-black uppercase transition-all rounded-lg {{ $tab === 'all' ? 'gradient-header-green text-white' : 'text-slate-600 hover:bg-indigo-50/50' }}">
                Semua Pesanan ({{ $counts['all'] }})
            </a>
            <a href="{{ route('admin.transactions', ['tab' => 'payment']) }}"
                class="px-6 py-2 text-xs font-black uppercase transition-all rounded-lg {{ $tab === 'payment' ? 'gradient-header-green text-white' : 'text-slate-600 hover:bg-indigo-50/50' }}">
                Verifikasi Pembayaran ({{ $counts['payment'] }})
            </a>
            <a href="{{ route('admin.transactions', ['tab' => 'release']) }}"
                class="px-6 py-2 text-xs font-black uppercase transition-all rounded-lg {{ $tab === 'release' ? 'gradient-header-green text-white' : 'text-slate-600 hover:bg-indigo-50/50' }}">
                Lepas Dana ({{ $counts['release'] }})
            </a>
        </div>

        <!-- Transaction Table -->
        <div class="glass-card overflow-hidden mb-12">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="glass-table-header text-slate-600 font-black uppercase">
                        <tr>
                            <th class="px-8 py-6">Referensi</th>
                            <th class="px-8 py-6">Pihak</th>
                            <th class="px-8 py-6 text-right">Nilai (GMV)</th>
                            <th class="px-8 py-6 text-center">Status</th>
                            <th class="px-8 py-6">Waktu</th>
                            <th class="px-8 py-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-indigo-50 font-bold">
                        @forelse($transactions as $tx)
                            <tr class="hover:bg-indigo-50/50 transition-all">
                                <td class="px-8 py-6">
                                    <div class="font-black text-sm text-slate-800 uppercase">#{{ $tx->id }}</div>
                                    <div class="text-[9px] font-mono text-slate-400 mt-1 uppercase">INV-{{ str_pad($tx->id, 5, '0', STR_PAD_LEFT) }}</div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col gap-1 uppercase">
                                        <div class="text-slate-800 tracking-tight"><span class="text-slate-400 mr-2 font-mono">B:</span>{{ $tx->buyer->name }}</div>
                                        <div class="text-slate-500 text-[10px]"><span class="text-slate-400 mr-2 font-mono">S:</span>{{ $tx->seller->name ?? 'SYSTEM_ERR' }}</div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="font-black text-sm text-slate-800">Rp {{ number_format($tx->total_amount, 0, ',', '.') }}</div>
                                    <div class="text-[9px] font-mono text-slate-400 uppercase mt-1">{{ $tx->payment_method_code }}</div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    @php
                                        $statusLabels = [
                                            'pending'           => 'MENUNGGU BAYAR',
                                            'paid_verified'     => 'SIAP KIRIM',
                                            'processing'        => 'DIPROSES',
                                            'shipped'           => 'DIKIRIM',
                                            'received'          => 'TUNGGU RILIS',
                                            'completed'         => 'SELESAI',
                                            'payment_rejected'  => 'DITOLAK',
                                            'cancelled'         => 'DIBATALKAN',
                                        ];
                                        $statusBadge = match($tx->status) {
                                            'completed'                     => 'badge-active',
                                            'pending', 'received'           => 'badge-inactive',
                                            'payment_rejected', 'cancelled' => 'badge-danger',
                                            default                         => 'badge-inactive',
                                        };
                                    @endphp
                                    <span class="{{ $statusBadge }}">
                                        {{ $statusLabels[$tx->status] ?? $tx->status }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 font-mono text-[10px] text-slate-400 uppercase">
                                    {{ $tx->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <a href="{{ route('admin.transactions.show', $tx->id) }}"
                                        class="btn-outline rounded-xl inline-block px-4 py-2 text-[10px] font-black uppercase">
                                        Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-24 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 border border-indigo-100 rounded-xl flex items-center justify-center font-black text-2xl mb-4 italic text-slate-400">?</div>
                                        <p class="text-xs font-black uppercase tracking-widest text-slate-400 italic">Belum Ada Transaksi</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $transactions->links() }}
        </div>
    </div>
@endsection
