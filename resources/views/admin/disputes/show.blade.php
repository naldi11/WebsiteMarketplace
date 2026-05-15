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
    @if(in_array($dispute->status, ['buyer_won','buyer_shipping_back','seller_received_back','refunded']) && $dispute->winner === 'buyer')
    <div class="glass-card p-6 mb-8">
        <p class="text-xs font-black uppercase text-gray-500 mb-5">TAHAPAN PENGEMBALIAN BARANG — Alur Refund Pembeli</p>
        @php
            $steps = [
                ['status' => 'buyer_won',            'label' => 'Keputusan Admin',   'icon' => '1', 'desc' => 'Admin memutuskan pembeli menang'],
                ['status' => 'buyer_shipping_back',  'label' => 'Kirim Balik',       'icon' => '2', 'desc' => 'Pembeli input resi pengiriman balik'],
                ['status' => 'seller_received_back', 'label' => 'Penjual Konfirmasi','icon' => '3', 'desc' => 'Penjual konfirmasi terima barang'],
                ['status' => 'refunded',              'label' => 'Refund Otomatis',   'icon' => '4', 'desc' => 'Saldo masuk ke wallet pembeli'],
            ];
            $order = array_column($steps, 'status');
            $currentIdx = array_search($dispute->status, $order);
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

        @if($dispute->status === 'buyer_shipping_back')
        <div class="mt-5 p-4 bg-purple-50 border border-purple-200 rounded-xl text-xs">
            <p class="font-black text-purple-700 mb-1">📬 Resi Pengiriman Balik</p>
            <p><span class="text-gray-500 font-bold">Kurir:</span> {{ $dispute->return_courier }}</p>
            <p><span class="text-gray-500 font-bold">No. Resi:</span> {{ $dispute->return_tracking_number }}</p>
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

                    {{-- STEP 2: Putuskan Pemenang --}}
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
                              onsubmit="return confirm('Konfirmasi bahwa penjual SUDAH menerima barang? Refund akan langsung diproses!')">
                            @csrf
                            <button class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-black uppercase text-[10px] transition-all rounded-xl">
                             Konfirmasi Penjual Terima Barang → Refund
                            </button>
                        </form>
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
                            @if($dispute->status === 'refunded') Refund Berhasil Diproses
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

            {{-- Info Saldo --}}
            <div class="glass-card p-5">
                <h4 class="font-black text-xs uppercase mb-3 text-gray-500">💼 Simulasi Dana</h4>
                @php
                    $buyerWallet  = \App\Models\Wallet::where('user_id', $dispute->buyer_id)->first();
                    $sellerWallet = \App\Models\Wallet::where('user_id', $dispute->seller_id)->first();
                    $txAmount     = $dispute->transaction->total_amount ?? 0;
                    $platformFee  = round($txAmount * 0.10);
                    $netToSeller  = $txAmount - $platformFee;
                @endphp
                <div class="space-y-3 text-xs">
                    <div class="p-3 bg-blue-50 rounded-xl border border-blue-100">
                        <p class="font-black text-blue-600 mb-1">Wallet Pembeli</p>
                        <div class="grid grid-cols-2 gap-1">
                            <span class="text-gray-500">Saldo:</span>
                            <span class="font-bold text-right">Rp {{ number_format($buyerWallet?->balance ?? 0, 0, ',', '.') }}</span>
                            <span class="text-gray-500">Pending:</span>
                            <span class="font-bold text-right">Rp {{ number_format($buyerWallet?->pending_balance ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="p-3 bg-orange-50 rounded-xl border border-orange-100">
                        <p class="font-black text-orange-600 mb-1">Wallet Penjual</p>
                        <div class="grid grid-cols-2 gap-1">
                            <span class="text-gray-500">Saldo:</span>
                            <span class="font-bold text-right">Rp {{ number_format($sellerWallet?->balance ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="p-3 bg-white/40 rounded-xl border border-indigo-100 text-[10px]">
                        <div class="flex justify-between"><span>Nilai TXN:</span><span class="font-bold">Rp {{ number_format($txAmount, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between text-blue-600"><span>Refund Pembeli:</span><span class="font-bold">+Rp {{ number_format($txAmount, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between text-green-600"><span>Net Penjual (90%):</span><span class="font-bold">+Rp {{ number_format($netToSeller, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between text-gray-500"><span>Fee Platform (10%):</span><span class="font-bold">Rp {{ number_format($platformFee, 0, ',', '.') }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Chat Terintegrasi ── --}}
    <div class="glass-card overflow-hidden mt-8">
        <div class="gradient-header-red px-6 py-4 text-white flex items-center gap-3 rounded-t-2xl">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
            </svg>
            <span class="font-black uppercase tracking-tight">Percakapan Laporan #D{{ $dispute->id }}</span>
        </div>

        <div class="p-6 space-y-3 max-h-96 overflow-y-auto bg-gradient-to-b from-slate-50/60 to-indigo-50/40" id="chatBox">
            @forelse($messages as $msg)
                @php
                    $isAdmin  = !in_array($msg->sender_id, [$dispute->buyer_id, $dispute->seller_id]);
                    $isBuyer  = ($msg->sender_id === $dispute->buyer_id);
                    $isSystem = str_contains($msg->message, '⚖️') || str_contains($msg->message, '✅')
                             || str_contains($msg->message, '🔍') || str_contains($msg->message, '📦')
                             || str_contains($msg->message, '🎉');
                @endphp
                @if($isSystem)
                    <div class="flex justify-center">
                        <span class="text-[10px] bg-white/60 border border-indigo-100 rounded-full px-3 py-1 text-slate-500 font-mono">
                            {{ $msg->message }}
                        </span>
                    </div>
                @elseif($isAdmin)
                    <div class="flex justify-center">
                        <div class="max-w-sm bg-purple-600/90 text-white px-4 py-2 rounded-2xl text-xs font-medium shadow">
                            {{ $msg->message }}
                            <div class="text-purple-200 text-[9px] mt-1 text-right">{{ $msg->created_at->format('H:i') }} · Admin</div>
                        </div>
                    </div>
                @elseif($isBuyer)
                    <div class="flex justify-start gap-2">
                        <div class="w-7 h-7 rounded-full bg-blue-500 flex items-center justify-center text-white text-[10px] font-black flex-shrink-0">
                            {{ substr($dispute->transaction->buyer->name ?? 'B', 0, 1) }}
                        </div>
                        <div class="max-w-xs">
                            <div class="text-[9px] text-slate-400 mb-1">{{ $dispute->transaction->buyer->name ?? 'Pembeli' }}</div>
                            <div class="bg-blue-50/80 border border-blue-100 px-4 py-2 rounded-2xl text-xs text-slate-700 shadow-sm">
                                {{ $msg->message }}
                                <div class="text-slate-400 text-[9px] mt-1">{{ $msg->created_at->format('H:i') }}</div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="flex justify-end gap-2">
                        <div class="max-w-xs text-right">
                            <div class="text-[9px] text-slate-400 mb-1">{{ $dispute->transaction->seller->name ?? 'Penjual' }}</div>
                            <div class="bg-emerald-500/90 text-white px-4 py-2 rounded-2xl text-xs shadow-sm">
                                {{ $msg->message }}
                                <div class="text-emerald-100 text-[9px] mt-1">{{ $msg->created_at->format('H:i') }}</div>
                            </div>
                        </div>
                        <div class="w-7 h-7 rounded-full bg-emerald-500 flex items-center justify-center text-white text-[10px] font-black flex-shrink-0">
                            {{ substr($dispute->transaction->seller->name ?? 'S', 0, 1) }}
                        </div>
                    </div>
                @endif
            @empty
                <p class="text-center text-slate-400 text-xs py-8">Percakapan belum dimulai.</p>
            @endforelse
        </div>

        <div class="border-t border-indigo-100 p-4 rounded-b-2xl">
            <form action="{{ route('admin.disputes.chat.send', $dispute->id) }}" method="POST" class="flex gap-3">
                @csrf
                <input type="text" name="message" placeholder="Ketik pesan sebagai admin..."
                    class="flex-1 px-4 py-2 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none bg-white/60">
                <button type="submit" class="btn-gradient text-white px-5 py-2 rounded-xl text-sm font-semibold">
                    Kirim
                </button>
            </form>
        </div>
    </div>

</div>
@endsection
