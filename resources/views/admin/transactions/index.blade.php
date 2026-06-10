@extends('layouts.admin')

@section('title', 'Log Transaksi')

@section('content')
    <div class="pt-0 pb-2">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6 mb-12">
            <div>
                <h1 class="text-4xl font-black tracking-tighter uppercase italic text-slate-800">Log Transaksi</h1>
                <p class="text-slate-500 mt-1 font-mono text-xs uppercase tracking-widest">Aliran Pesanan Global & Kontrol Pembukuan</p>
            </div>

            <form action="{{ route('admin.transactions') }}" method="GET" class="flex flex-wrap gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari referensi, pembeli, penjual..."
                    class="px-4 py-2 border border-indigo-100 rounded-xl text-xs font-bold uppercase focus:outline-none focus:border-purple-400 bg-white/60 backdrop-blur transition-all">

                <select name="status" class="px-4 py-2 border border-indigo-100 rounded-xl text-xs font-black uppercase focus:outline-none focus:border-purple-400 bg-white/60 backdrop-blur transition-all">
                    <option value="">Semua Status</option>
                    <option value="waiting_payment"  {{ request('status') == 'waiting_payment'  ? 'selected' : '' }}>Menunggu Bayar</option>
                    <option value="pending"          {{ request('status') == 'pending'          ? 'selected' : '' }}>Menunggu Verifikasi</option>
                    <option value="paid_verified"    {{ request('status') == 'paid_verified'    ? 'selected' : '' }}>Siap Kirim</option>
                    <option value="processing"       {{ request('status') == 'processing'       ? 'selected' : '' }}>Diproses</option>
                    <option value="shipped"          {{ request('status') == 'shipped'          ? 'selected' : '' }}>Dikirim</option>
                    <option value="received"         {{ request('status') == 'received'         ? 'selected' : '' }}>Tunggu Rilis</option>
                    <option value="completed"        {{ request('status') == 'completed'        ? 'selected' : '' }}>Selesai</option>
                    <option value="payment_rejected" {{ request('status') == 'payment_rejected' ? 'selected' : '' }}>Ditolak</option>
                    <option value="cancelled"        {{ request('status') == 'cancelled'        ? 'selected' : '' }}>Dibatalkan</option>
                </select>

                <button type="submit" class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white px-6 py-2 text-xs font-black uppercase rounded-xl transition-all shadow-md shadow-indigo-500/10">
                    Filter
                </button>
                @if(request()->filled('search') || request()->filled('status'))
                    <a href="{{ route('admin.transactions') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 text-xs font-black uppercase rounded-xl transition-all border border-slate-300 flex items-center justify-center">
                        Reset
                    </a>
                @endif
            </form>
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
                                            'waiting_payment'   => 'MENUNGGU BAYAR',
                                            'pending'           => 'MENUNGGU VERIFIKASI',
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
                                            'pending'                       => 'badge-warning',
                                            'received'                      => 'badge-info',
                                            'payment_rejected', 'cancelled' => 'badge-danger',
                                            default                         => 'badge-inactive',
                                        };
                                    @endphp
                                    <span class="{{ $statusBadge }}">
                                        {{ $statusLabels[$tx->status] ?? strtoupper($tx->status) }}
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
