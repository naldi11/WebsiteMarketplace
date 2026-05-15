@extends('layouts.admin')

@section('title', 'Transaction Logs')

@section('content')
    <div class="pt-0 pb-2">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-12">
            <div>
                <h1 class="text-4xl font-black tracking-tighter uppercase italic text-black">Log Transaksi</h1>
                <p class="text-gray-500 mt-1 font-mono text-xs uppercase tracking-widest text-black">Aliran Pesanan Global & Kontrol Pembukuan</p>
            </div>
        </div>

        <!-- Tab Navigation - Neo Brutalism -->
        <div class="mb-8 border-[3px] border-black bg-white neo-brutalism p-1 inline-flex flex-wrap gap-1">
            <a href="{{ route('admin.transactions', ['tab' => 'all']) }}"
                class="px-6 py-2 text-xs font-black uppercase transition-all {{ $tab === 'all' ? 'bg-black text-white' : 'text-black hover:bg-gray-100' }}">
                Semua Pesanan ({{ $counts['all'] }})
            </a>
            <a href="{{ route('admin.transactions', ['tab' => 'payment']) }}"
                class="px-6 py-2 text-xs font-black uppercase transition-all {{ $tab === 'payment' ? 'bg-black text-white' : 'text-black hover:bg-gray-100' }}">
                Verifikasi Pembayaran ({{ $counts['payment'] }})
            </a>
            <a href="{{ route('admin.transactions', ['tab' => 'release']) }}"
                class="px-6 py-2 text-xs font-black uppercase transition-all {{ $tab === 'release' ? 'bg-black text-white' : 'text-black hover:bg-gray-100' }}">
                Lepas Dana ({{ $counts['release'] }})
            </a>
        </div>

        <!-- Transaction Table - Neo Brutalism -->
        <div class="bg-white border-[3px] border-black neo-brutalism overflow-hidden mb-12">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-100 border-b-[3px] border-black text-black font-black uppercase italic">
                        <tr>
                            <th class="px-8 py-6">Referensi</th>
                            <th class="px-8 py-6">Pihak</th>
                            <th class="px-8 py-6 text-right">Nilai (GMV)</th>
                            <th class="px-8 py-6 text-center">Status</th>
                            <th class="px-8 py-6">Waktu</th>
                            <th class="px-8 py-6 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-gray-100 font-bold">
                        @forelse($transactions as $tx)
                            <tr class="hover:bg-gray-50 transition-all">
                                <td class="px-8 py-6">
                                    <div class="font-black text-sm text-black uppercase">#{{ $tx->id }}</div>
                                    <div class="text-[9px] font-mono text-gray-400 mt-1 uppercase">INV-{{ str_pad($tx->id, 5, '0', STR_PAD_LEFT) }}</div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col gap-1 uppercase">
                                        <div class="text-black tracking-tight"><span class="text-gray-400 mr-2 font-mono">B:</span>{{ $tx->buyer->name }}</div>
                                        <div class="text-gray-500 text-[10px]"><span class="text-gray-400 mr-2 font-mono">S:</span>{{ $tx->seller->name ?? 'SYSTEM_ERR' }}</div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="font-black text-sm text-black">Rp {{ number_format($tx->total_amount, 0, ',', '.') }}</div>
                                    <div class="text-[9px] font-mono text-gray-400 uppercase mt-1">{{ $tx->payment_method_code }}</div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    @php
                                        $statusLabels = [
                                            'pending' => 'MENUNGGU BAYAR',
                                            'paid_verified' => 'SIAP KIRIM',
                                            'processing' => 'DIPROSES',
                                            'shipped' => 'DIKIRIM',
                                            'received' => 'TUNGGU RILIS',
                                            'completed' => 'SELESAI',
                                            'payment_rejected' => 'DITOLAK',
                                            'cancelled' => 'DIBATALKAN',
                                        ];
                                        $isCritical = in_array($tx->status, ['pending', 'received', 'payment_rejected', 'cancelled']);
                                    @endphp
                                    <span class="px-3 py-1 border-2 border-black text-[9px] font-black uppercase {{ $isCritical ? 'bg-black text-white' : 'bg-white text-black' }}">
                                        {{ $statusLabels[$tx->status] ?? $tx->status }}
                                    </span>
                                </td>
                                <td class="px-8 py-6 font-mono text-[10px] text-gray-400 uppercase">
                                    {{ $tx->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-8 py-6 text-center">
                                    <a href="{{ route('admin.transactions.show', $tx->id) }}"
                                        class="inline-block px-4 py-2 border-2 border-black bg-white text-black text-[10px] font-black uppercase hover:bg-black hover:text-white transition-all italic">
                                        Lihat Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-24 text-center">
                                    <div class="flex flex-col items-center">
                                        <div class="w-16 h-16 border-[3px] border-black flex items-center justify-center font-black text-2xl mb-4 italic">?</div>
                                        <p class="text-xs font-black uppercase tracking-widest text-gray-400 italic">Belum Ada Transaksi</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination - Neo Brutalism Style -->
        <div class="mt-8">
            {{ $transactions->links() }}
        </div>
    </div>
@endsection