@extends('layouts.admin')

@section('content')
<div class="pb-4">

    {{-- Back + Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.disputes.index') }}" class="btn-outline rounded-xl px-4 py-2 font-black text-xs uppercase transition-all">← Kembali</a>
            <div>
                <h1 class="text-3xl font-black tracking-tighter uppercase italic">Laporan #D{{ $dispute->id }}</h1>
                <p class="text-gray-500 mt-0.5 font-mono text-xs uppercase">TXN #{{ $dispute->transaction_id }} • {{ $dispute->created_at->diffForHumans() }}</p>
            </div>
        </div>
        {{-- God View Button --}}
        <a href="{{ route('admin.disputes.chat', $dispute->id) }}"
           class="flex items-center gap-2 px-5 py-3 bg-purple-600 hover:bg-purple-700 text-white font-black text-xs uppercase transition-all rounded-xl shadow-lg">
            💬 Lihat Chat Laporan
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-800 px-6 py-4 mb-6 font-bold text-sm rounded-xl">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-800 px-6 py-4 mb-6 font-bold text-sm rounded-xl">{{ session('error') }}</div>
    @endif

    {{-- ── STEPPER TAHAPAN (hanya tampil jika buyer menang / proses pengembalian) ── --}}
    @if(in_array($dispute->status, ['buyer_won','buyer_shipping_back','seller_received_back','refund_transferred','refunded']) && $dispute->winner === 'buyer')
    <div class="glass-card p-6 mb-8">
        <p class="text-xs font-black uppercase text-gray-500 mb-5">TAHAPAN PENGEMBALIAN BARANG — Alur Refund Pembeli</p>
        @php
            $steps = [
                ['status' => 'buyer_won',            'label' => 'Keputusan Admin',   'icon' => '1', 'desc' => 'Admin memutuskan pembeli menang'],
                ['status' => 'buyer_shipping_back',  'label' => 'Kirim Balik',       'icon' => '2', 'desc' => 'Pembeli input resi & bukti kirim balik'],
                ['status' => 'seller_received_back', 'label' => 'Penjual Konfirmasi','icon' => '3', 'desc' => 'Penjual konfirmasi terima barang'],
                ['status' => 'refund_transferred',   'label' => 'Transfer Manual',   'icon' => '4', 'desc' => 'Admin transfer manual & bukti diunggah'],
                ['status' => 'refunded',             'label' => 'Selesai',           'icon' => '5', 'desc' => 'Pembeli konfirmasi dana diterima'],
            ];
            $order = array_column($steps, 'status');
            $currentIdx = array_search($dispute->status, $order);
            if ($currentIdx === false) {
                if ($dispute->status === 'open' || $dispute->status === 'admin_reviewing') {
                    $currentIdx = -1;
                } else {
                    $currentIdx = 0;
                }
            }
        @endphp
        <div class="flex items-start gap-0">
            @foreach($steps as $i => $step)
            @php $done = $currentIdx >= $i; $active = $currentIdx === $i; @endphp
            <div class="flex-1 flex flex-col items-center relative">
                {{-- Connector --}}
                @if(!$loop->last)
                <div class="absolute top-5 left-1/2 w-full h-[3px] {{ $done && !$active ? 'bg-green-500' : 'bg-indigo-100' }}" style="z-index:0"></div>
                @endif
                {{-- Circle --}}
                <div class="w-10 h-10 rounded-full border-2 flex items-center justify-center text-lg z-10
                    {{ $done ? 'bg-green-500 border-green-500 text-white shadow-lg shadow-green-500/30' : 'bg-white/80 border-indigo-200 text-gray-400' }}">
                    @if($done && !$active) ✓ @else {{ $loop->iteration }} @endif
                </div>
                <p class="text-[10px] font-black uppercase mt-2 text-center {{ $active ? 'text-slate-800' : ($done ? 'text-green-600' : 'text-gray-400') }}">
                    {{ $step['label'] }}
                </p>
                <p class="text-[9px] text-gray-400 text-center mt-0.5 px-1">{{ $step['desc'] }}</p>
            </div>
            @endforeach
        </div>

        @if(in_array($dispute->status, ['buyer_shipping_back', 'seller_received_back', 'refund_transferred', 'refunded']) && $dispute->return_tracking_number)
        <div class="mt-5 p-4 bg-purple-50 border border-purple-200 rounded-xl text-xs flex items-center justify-between gap-4 flex-wrap">
            <div>
                <p class="font-black text-purple-700 mb-2">📬 Resi Pengiriman Balik</p>
                <p><span class="text-gray-500 font-bold">Kurir:</span> {{ $dispute->return_courier }}</p>
                <p class="mt-1"><span class="text-gray-500 font-bold">No. Resi:</span> {{ $dispute->return_tracking_number }}</p>
            </div>
            @if($dispute->return_shipping_proof)
            <div class="w-36 h-24 rounded-xl overflow-hidden border border-purple-200 shrink-0">
                <a href="{{ Storage::url($dispute->return_shipping_proof) }}" target="_blank">
                    <img src="{{ Storage::url($dispute->return_shipping_proof) }}" class="w-full h-full object-cover hover:scale-105 transition duration-300">
                </a>
            </div>
            @endif
        </div>
        @endif
    </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">

        {{-- LEFT COLUMN --}}
        <div class="xl:col-span-2 space-y-6">

            {{-- Status Badge --}}
            <div class="glass-card p-8">
                <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
                    <div>
                        @php
                            $statusBadge = match($dispute->status) {
                                'open'                 => ['bg-red-100 text-red-700 border-red-400', 'TERBUKA'],
                                'admin_reviewing'      => ['bg-yellow-100 text-yellow-800 border-yellow-400', 'DITINJAU'],
                                'buyer_won'            => ['bg-blue-100 text-blue-700 border-blue-400', 'PEMBELI MENANG — Tunggu Kirim Balik'],
                                'buyer_shipping_back'  => ['bg-purple-100 text-purple-700 border-purple-400', 'BARANG DIKIRIM BALIK'],
                                'seller_received_back' => ['bg-indigo-100 text-indigo-700 border-indigo-400', 'BARANG DITERIMA PENJUAL'],
                                'refund_transferred'   => ['bg-pink-100 text-pink-700 border-pink-400', 'REFUND DITRANSFER — Tunggu Konfirmasi Pembeli'],
                                'seller_won'           => ['bg-green-100 text-green-700 border-green-400', 'PENJUAL MENANG — Transaksi Selesai'],
                                'refunded'             => ['bg-teal-100 text-teal-700 border-teal-400', 'DIREFUND — Selesai'],
                                'closed'               => ['bg-gray-100 text-gray-600 border-gray-400', 'DITUTUP'],
                                default                => ['bg-gray-100 text-gray-600 border-gray-400', strtoupper($dispute->status)],
                            };
                        @endphp
                        <span class="px-4 py-2 border text-sm font-black rounded-xl {{ $statusBadge[0] }}">{{ $statusBadge[1] }}</span>
                    </div>
                    @if($dispute->winner)
                    <div class="text-sm font-black px-3 py-1 border rounded-xl {{ $dispute->winner === 'buyer' ? 'border-blue-400 text-blue-700' : 'border-green-400 text-green-700' }}">
                        Pemenang: {{ strtoupper($dispute->winner === 'buyer' ? 'PEMBELI' : 'PENJUAL') }}
                    </div>
                    @endif
                </div>

                {{-- Info penjual menang → no rating --}}
                @if($dispute->status === 'seller_won')
                <div class="mb-5 bg-red-50 border border-red-300 p-4 rounded-xl text-xs">
                    <p class="font-black text-red-700">Pembeli TIDAK DAPAT memberikan rating pada transaksi ini.</p>
                    <p class="text-red-500 mt-1">Akses rating telah diblokir otomatis karena penjual memenangkan laporan masalah ini.</p>
                </div>
                @endif

                {{-- Pihak yang terlibat --}}
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-blue-50 border border-blue-200 p-4 rounded-xl">
                        <p class="text-[10px] font-black text-blue-600 uppercase mb-1">Pembeli (Pelapor)</p>
                        <p class="font-black text-sm">{{ $dispute->buyer->name ?? '-' }}</p>
                        <p class="text-xs text-gray-500">{{ $dispute->buyer->email ?? '' }}</p>
                    </div>
                    <div class="bg-orange-50 border border-orange-200 p-4 rounded-xl">
                        <p class="text-[10px] font-black text-orange-600 uppercase mb-1">Penjual (Terlapor)</p>
                        <p class="font-black text-sm">{{ $dispute->seller->name ?? '-' }}</p>
                        <p class="text-xs text-gray-500">{{ $dispute->seller->email ?? '' }}</p>
                    </div>
                </div>

                {{-- Detail --}}
                <div class="space-y-3 text-sm">
                    <div class="flex gap-3"><span class="font-black text-gray-500 w-28 shrink-0">Alasan:</span><span class="font-bold">{{ $dispute->reason }}</span></div>
                    @if($dispute->description)
                    <div class="flex gap-3"><span class="font-black text-gray-500 w-28 shrink-0">Deskripsi:</span><span>{{ $dispute->description }}</span></div>
                    @endif
                    @if($dispute->admin_notes)
                    <div class="flex gap-3"><span class="font-black text-gray-500 w-28 shrink-0">Catatan Admin:</span><span class="bg-yellow-50 border border-yellow-200 px-3 py-1 rounded-xl text-yellow-800 font-semibold">{{ $dispute->admin_notes }}</span></div>
                    @endif
                    @if($dispute->resolvedBy)
                    <div class="flex gap-3"><span class="font-black text-gray-500 w-28 shrink-0">Diputus Oleh:</span><span class="font-bold">Admin {{ $dispute->resolvedBy->name }}</span></div>
                    @endif
                </div>
            </div>

            {{-- Rincian Transaksi --}}
            <div class="glass-card overflow-hidden">
                <div class="px-8 py-5 border-b border-indigo-100 bg-white/40">
                    <h3 class="font-black text-sm uppercase text-slate-800">Rincian Transaksi #{{ $dispute->transaction_id }}</h3>
                </div>
                <div class="p-8">
                    @php $tx = $dispute->transaction; @endphp
                    <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                        <div><span class="font-black text-gray-500">Total:</span> <span class="font-bold">Rp {{ number_format($tx->total_amount ?? 0, 0, ',', '.') }}</span></div>
                        <div><span class="font-black text-gray-500">Status TX:</span> <span class="font-bold uppercase">{{ $tx->status ?? '-' }}</span></div>
                        <div><span class="font-black text-gray-500">Metode:</span> <span class="font-bold">{{ $tx->payment_method_code ?? '-' }}</span></div>
                        <div><span class="font-black text-gray-500">Tanggal:</span> <span class="font-bold">{{ $tx->created_at?->format('d M Y H:i') ?? '-' }}</span></div>
                    </div>
                    @if($tx->items && $tx->items->count())
                    <div class="border-t border-indigo-100 pt-4">
                        @foreach($tx->items as $item)
                        <div class="flex items-center gap-4 py-2 border-b border-indigo-50 last:border-0">
                            @if($item->product?->image)
                            <img src="{{ Storage::url($item->product->image) }}" class="w-12 h-12 object-cover rounded-xl border border-indigo-100" alt="">
                            @endif
                            <div class="flex-1">
                                <p class="font-bold text-sm">{{ $item->product?->name ?? 'Produk' }}</p>
                                <p class="text-xs text-gray-500">x{{ $item->quantity }} × Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                            </div>
                            <p class="font-black text-sm">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</p>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>

            {{-- Activity Log --}}
            <div class="glass-card overflow-hidden">
                <div class="gradient-header-red px-8 py-5 text-white">
                    <h3 class="font-black text-sm uppercase">Riwayat Aktivitas</h3>
                </div>
                <div class="p-8">
                    @forelse($dispute->logs->sortByDesc('created_at') as $log)
                    <div class="flex gap-4 pb-4 mb-4 border-b border-indigo-50 last:border-0 last:mb-0 last:pb-0">
                        @php
                            $iconBg = match($log->actor_type ?? 'system') {
                                'admin'  => 'bg-purple-100 text-purple-600',
                                'buyer'  => 'bg-blue-100 text-blue-600',
                                'seller' => 'bg-orange-100 text-orange-600',
                                default  => 'bg-gray-100 text-gray-500',
                            };
                        @endphp
                        <div class="w-8 h-8 rounded-full {{ $iconBg }} flex items-center justify-center text-[10px] font-black uppercase shrink-0 mt-1">
                            {{ substr($log->actor_type ?? 'S', 0, 1) }}
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs font-black uppercase {{ $iconBg === 'bg-purple-100 text-purple-600' ? 'text-purple-600' : ($iconBg === 'bg-blue-100 text-blue-600' ? 'text-blue-600' : ($iconBg === 'bg-orange-100 text-orange-600' ? 'text-orange-600' : 'text-gray-500')) }}">
                                    {{ $log->actor_type ?? 'system' }}
                                </span>
                                <span class="text-[9px] text-gray-400 font-mono">{{ $log->created_at?->format('d/m H:i') }}</span>
                            </div>
                            <p class="text-sm text-gray-800 whitespace-pre-line">{{ $log->notes }}</p>
                        </div>
                    </div>
                    @empty
                    <p class="text-gray-400 text-xs font-black uppercase text-center py-4">Belum ada aktivitas</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- RIGHT COLUMN: Action Panel --}}
        <div class="space-y-4">

            {{-- God View Button --}}
            <a href="{{ route('admin.disputes.chat', $dispute->id) }}"
               class="flex items-center justify-center gap-2 w-full py-4 bg-purple-600 hover:bg-purple-700 text-white font-black text-sm uppercase transition-all rounded-xl shadow-lg shadow-purple-500/20">
                Lihat Chat Laporan
            </a>

            {{-- PANEL AKSI --}}
            <div class="glass-card overflow-hidden">
                <div class="gradient-header-red px-6 py-4 text-white">
                    <h3 class="font-black uppercase text-sm">Panel Resolusi Admin</h3>
                </div>
                <div class="p-5 space-y-4">

                    {{-- STEP 1: Tandai Reviewing --}}
                    @if($dispute->status === 'open')
                    <div class="border border-yellow-300 bg-yellow-50 p-4 rounded-xl">
                        <p class="text-xs font-black uppercase text-yellow-700 mb-2">Langkah 1 — Mulai Tinjau</p>
                        <form action="{{ route('admin.disputes.reviewing', $dispute->id) }}" method="POST">
                            @csrf
                            <button class="w-full py-3 bg-yellow-500 hover:bg-yellow-600 text-white font-black uppercase text-xs transition-all rounded-xl">
                            Tandai Sedang Ditinjau
                            </button>
                        </form>
                    </div>
                    @endif

                    @if(in_array($dispute->status, ['open', 'admin_reviewing']))
                    <div class="border border-indigo-100 p-4 rounded-xl">
                        <p class="text-xs font-black uppercase text-gray-700 mb-3">Putuskan Pemenang</p>
                        <form action="{{ route('admin.disputes.resolve', $dispute->id) }}" method="POST" class="space-y-3"
                               onsubmit="return confirm('Yakin? Keputusan tidak bisa dibatalkan!')">
                            @csrf
                            <textarea name="admin_notes" rows="2" placeholder="Alasan keputusan..."
                                class="w-full border border-indigo-100 focus:border-indigo-400 px-3 py-2 text-xs outline-none resize-none rounded-xl transition-all bg-white/60"></textarea>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="submit" name="winner" value="buyer"
                                    class="py-3 bg-blue-600 hover:bg-blue-700 text-white font-black uppercase text-[10px] transition-all rounded-xl flex flex-col items-center gap-0.5 shadow-lg shadow-blue-500/20">
                                    <span class="text-lg">🔵</span>
                                    <span>Pembeli Menang</span>
                                    <span class="opacity-70 text-[9px] font-normal normal-case">→ Tahapan refund</span>
                                </button>
                                <button type="submit" name="winner" value="seller"
                                    class="py-3 bg-green-600 hover:bg-green-700 text-white font-black uppercase text-[10px] transition-all rounded-xl flex flex-col items-center gap-0.5 shadow-lg shadow-green-500/20">
                                    <span class="text-lg">🟢</span>
                                    <span>Penjual Menang</span>
                                    <span class="opacity-70 text-[9px] font-normal normal-case">→ Dana ke penjual (-10%)</span>
                                </button>
                            </div>
                        </form>
                    </div>
                    @endif

                    {{-- ADMIN KONFIRMASI PENJUAL TERIMA BARANG (jika penjual tidak aktif) --}}
                    @if(in_array($dispute->status, ['buyer_won', 'buyer_shipping_back']))
                    <div class="border border-indigo-200 bg-indigo-50 p-4 rounded-xl">
                        <p class="text-xs font-black uppercase text-indigo-700 mb-1">Admin Override</p>
                        <p class="text-[9px] text-indigo-500 mb-3">Jika penjual tidak konfirmasi dalam 3 hari, admin bisa paksa konfirmasi.</p>
                        <form action="{{ route('admin.disputes.confirmReceived', $dispute->id) }}" method="POST"
                               onsubmit="return confirm('Konfirmasi bahwa penjual SUDAH menerima barang? Status akan diubah ke penerimaan barang selesai, dan admin harus melakukan transfer manual!')">
                            @csrf
                            <button class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-black uppercase text-[10px] transition-all rounded-xl">
                             Konfirmasi Penjual Terima Barang
                            </button>
                        </form>
                    </div>
                    @endif

                    {{-- FORM REFUND MANUAL DARI ADMIN --}}
                    @if($dispute->status === 'seller_received_back')
                    <div class="border border-purple-200 bg-purple-50 p-4 rounded-xl space-y-3">
                        <p class="text-xs font-black uppercase text-purple-700 mb-1">📝 Proses Refund Manual</p>
                        <p class="text-[9px] text-purple-500 mb-2">Silakan lakukan transfer manual ke rekening Pembeli, lalu unggah bukti transfer di bawah ini.</p>
                        
                        <form action="{{ route('admin.disputes.refundManual', $dispute->id) }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                            @csrf
                            <div>
                                <label class="block text-[9px] font-black uppercase text-gray-500 mb-0.5">Nama Bank Penerima</label>
                                <input type="text" name="bank_name" placeholder="Contoh: Bank BCA / Mandiri" required
                                       class="w-full border border-gray-200 focus:border-purple-400 px-3 py-1.5 text-xs outline-none rounded-xl bg-white">
                            </div>
                            <div>
                                <label class="block text-[9px] font-black uppercase text-gray-500 mb-0.5">Nomor Rekening</label>
                                <input type="text" name="account_number" placeholder="Contoh: 123456789" required
                                       class="w-full border border-gray-200 focus:border-purple-400 px-3 py-1.5 text-xs outline-none rounded-xl bg-white">
                            </div>
                            <div>
                                <label class="block text-[9px] font-black uppercase text-gray-500 mb-0.5">Nama Pemilik Rekening</label>
                                <input type="text" name="account_holder_name" placeholder="Nama pemilik rekening" required
                                       class="w-full border border-gray-200 focus:border-purple-400 px-3 py-1.5 text-xs outline-none rounded-xl bg-white">
                            </div>
                            <div>
                                <label class="block text-[9px] font-black uppercase text-gray-500 mb-0.5">Bukti Transfer (Gambar)</label>
                                <input type="file" name="transfer_proof" required accept="image/*"
                                       class="w-full border border-gray-200 focus:border-purple-400 px-3 py-1.5 text-xs outline-none rounded-xl bg-white">
                            </div>
                            <div>
                                <label class="block text-[9px] font-black uppercase text-gray-500 mb-0.5">Catatan (Opsional)</label>
                                <textarea name="notes" rows="2" placeholder="Catatan tambahan..."
                                          class="w-full border border-gray-200 focus:border-purple-400 px-3 py-1.5 text-xs outline-none rounded-xl bg-white resize-none"></textarea>
                            </div>
                            <button type="submit" class="w-full py-2 bg-purple-600 hover:bg-purple-700 text-white font-black uppercase text-[10px] transition-all rounded-xl shadow-md">
                             Kirim Bukti & Konfirmasi Transfer
                            </button>
                        </form>
                    </div>
                    @endif

                    {{-- TAMPILAN BUKTI TRANSFER DARI ADMIN --}}
                    @if(in_array($dispute->status, ['refund_transferred', 'refunded']))
                    <div class="border border-green-200 bg-green-50 p-4 rounded-xl space-y-3">
                        <p class="text-xs font-black uppercase text-green-700 mb-1">💰 Bukti Transfer Refund Admin</p>
                        
                        @php
                            $refundRec = \App\Models\RefundRecord::where('dispute_id', $dispute->id)->where('status', 'completed')->first();
                        @endphp
                        
                        @if($refundRec)
                        <div class="text-xs space-y-1 text-gray-700">
                            <p><span class="font-bold">Nama Bank:</span> {{ $refundRec->bank_name }}</p>
                            <p><span class="font-bold">No Rekening:</span> {{ $refundRec->account_number }}</p>
                            <p><span class="font-bold">Pemilik:</span> {{ $refundRec->account_holder_name }}</p>
                            <p><span class="font-bold">Waktu Transfer:</span> {{ $refundRec->refunded_at ? $refundRec->refunded_at->format('d M Y H:i') : '-' }}</p>
                            @if($refundRec->notes)
                            <p><span class="font-bold">Catatan:</span> {{ $refundRec->notes }}</p>
                            @endif
                        </div>
                        @endif

                        @if($dispute->admin_refund_proof)
                        <div class="mt-2 rounded-xl overflow-hidden border border-green-200 max-w-full">
                            <a href="{{ Storage::url($dispute->admin_refund_proof) }}" target="_blank" class="block text-center bg-white p-2">
                                <img src="{{ Storage::url($dispute->admin_refund_proof) }}" class="max-h-40 mx-auto object-contain hover:scale-105 transition duration-300">
                                <span class="text-[9px] text-gray-400 font-semibold mt-1 block">Klik untuk memperbesar</span>
                            </a>
                        </div>
                        @endif
                        
                        @if($dispute->status === 'refund_transferred')
                        <div class="bg-yellow-50 border border-yellow-200 p-2.5 rounded-xl text-[9px] text-yellow-800 font-semibold">
                            ⚠️ Menunggu Pembeli mengonfirmasi penerimaan dana di aplikasi Android.
                        </div>
                        @endif
                    </div>
                    @endif

                    {{-- FORCE REFUND --}}
                    @if(in_array($dispute->status, ['buyer_won', 'buyer_shipping_back', 'seller_received_back', 'open', 'admin_reviewing']))
                    <div class="border border-red-200 bg-red-50 p-4 rounded-xl">
                        <p class="text-xs font-black uppercase text-red-700 mb-1">Force Refund (Bypass Barang)</p>
                        <p class="text-[9px] text-red-500 mb-3">Refund langsung tanpa menunggu pengembalian barang.</p>
                        <form action="{{ route('admin.disputes.forceRefund', $dispute->id) }}" method="POST"
                               onsubmit="return confirm('FORCE REFUND: Proses refund LANGSUNG. Lanjutkan?')">
                            @csrf
                            <input type="hidden" name="admin_notes" value="Force refund oleh admin">
                            <button class="w-full py-2 bg-red-600 hover:bg-red-700 text-white font-black uppercase text-[10px] transition-all rounded-xl">
                             Proses Refund Sekarang
                            </button>
                        </form>
                    </div>
                    @endif

                    {{-- SELESAI --}}
                    @if(in_array($dispute->status, ['refunded', 'closed', 'seller_won']))
                    <div class="bg-slate-50 border border-indigo-100 p-4 rounded-xl text-center">
                        <p class="text-3xl mb-2">
                            @if($dispute->status === 'refunded') 💰
                            @elseif($dispute->status === 'seller_won') 🟢
                            @else ✅
                            @endif
                        </p>
                        <p class="font-black text-sm uppercase text-gray-700">
                            @if($dispute->status === 'refunded') Refund Selesai (Pembeli Konfirmasi)
                            @elseif($dispute->status === 'seller_won') Penjual Menang — Selesai
                            @else Dispute Ditutup
                            @endif
                        </p>
                        @if($dispute->resolved_at)
                        <p class="text-[10px] text-gray-400 mt-1">{{ $dispute->resolved_at->format('d M Y H:i') }}</p>
                        @endif
                    </div>
                    @endif
            </div>


        </div>
    </div>

</div>
@endsection
