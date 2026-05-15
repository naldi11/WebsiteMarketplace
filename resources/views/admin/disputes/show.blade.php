@extends('layouts.admin')

@section('content')
<div class="pb-4">

    {{-- Back + Header --}}
    <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.disputes.index') }}" class="px-4 py-2 border-[3px] border-black font-black text-xs uppercase hover:bg-black hover:text-white transition-all">← Kembali</a>
            <div>
                <h1 class="text-3xl font-black tracking-tighter uppercase italic">Laporan #D{{ $dispute->id }}</h1>
                <p class="text-gray-500 mt-0.5 font-mono text-xs uppercase">TXN #{{ $dispute->transaction_id }} • {{ $dispute->created_at->diffForHumans() }}</p>
            </div>
        </div>
        {{-- God View Button --}}
        <a href="{{ route('admin.disputes.chat', $dispute->id) }}"
           class="flex items-center gap-2 px-5 py-3 bg-purple-600 hover:bg-purple-700 text-white font-black text-xs uppercase transition-all">
            👁️ Pantau Chat Sengketa
        </a>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="bg-green-50 border-l-4 border-green-500 text-green-800 px-6 py-4 mb-6 font-bold text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-50 border-l-4 border-red-500 text-red-800 px-6 py-4 mb-6 font-bold text-sm">{{ session('error') }}</div>
    @endif

    {{-- ── STEPPER TAHAPAN (hanya tampil jika buyer menang / proses pengembalian) ── --}}
    @if(in_array($dispute->status, ['buyer_won','buyer_shipping_back','seller_received_back','refunded']) && $dispute->winner === 'buyer')
    <div class="bg-white border-[3px] border-black p-6 mb-8">
        <p class="text-xs font-black uppercase text-gray-500 mb-5">TAHAPAN PENGEMBALIAN BARANG — Alur Refund Pembeli</p>
        @php
            $steps = [
                ['status' => 'buyer_won',            'label' => 'Keputusan Admin',         'icon' => '1', 'desc' => 'Admin memutuskan pembeli menang'],
                ['status' => 'buyer_shipping_back',  'label' => 'Kirim Balik',              'icon' => '2', 'desc' => 'Pembeli input resi pengiriman balik'],
                ['status' => 'seller_received_back', 'label' => 'Penjual Konfirmasi',       'icon' => '3', 'desc' => 'Penjual konfirmasi terima barang'],
                ['status' => 'refunded',              'label' => 'Refund Otomatis',          'icon' => '4', 'desc' => 'Saldo masuk ke wallet pembeli'],
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
                <div class="absolute top-5 left-1/2 w-full h-[3px] {{ $done && !$active ? 'bg-green-500' : 'bg-gray-200' }}" style="z-index:0"></div>
                @endif
                {{-- Circle --}}
                <div class="w-10 h-10 rounded-full border-[3px] flex items-center justify-center text-lg z-10
                    {{ $done ? 'bg-green-500 border-green-500 text-white' : 'bg-white border-gray-300 text-gray-400' }}">
                    @if($done && !$active) ✓ @else {{ $loop->iteration }} @endif
                </div>
                <p class="text-[10px] font-black uppercase mt-2 text-center {{ $active ? 'text-black' : ($done ? 'text-green-600' : 'text-gray-400') }}">
                    {{ $step['label'] }}
                </p>
                <p class="text-[9px] text-gray-400 text-center mt-0.5 px-1">{{ $step['desc'] }}</p>
            </div>
            @endforeach
        </div>

        @if($dispute->status === 'buyer_shipping_back')
        <div class="mt-5 p-4 bg-purple-50 border-2 border-purple-200 rounded text-xs">
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
            <div class="bg-white border-[3px] border-black p-8">
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
                        <span class="px-4 py-2 border-2 text-sm font-black rounded {{ $statusBadge[0] }}">{{ $statusBadge[1] }}</span>
                    </div>
                    @if($dispute->winner)
                    <div class="text-sm font-black px-3 py-1 border-2 {{ $dispute->winner === 'buyer' ? 'border-blue-400 text-blue-700' : 'border-green-400 text-green-700' }}">
                        Pemenang: {{ strtoupper($dispute->winner === 'buyer' ? 'PEMBELI' : 'PENJUAL') }}
                    </div>
                    @endif
                </div>

                {{-- Info penjual menang → no rating --}}
                @if($dispute->status === 'seller_won')
                <div class="mb-5 bg-red-50 border-2 border-red-300 p-4 rounded text-xs">
                    <p class="font-black text-red-700">Pembeli TIDAK DAPAT memberikan rating pada transaksi ini.</p>
                    <p class="text-red-500 mt-1">Akses rating telah diblokir otomatis karena penjual memenangkan sengketa.</p>
                </div>
                @endif

                {{-- Pihak yang bersengketa --}}
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-blue-50 border-2 border-blue-200 p-4 rounded">
                        <p class="text-[10px] font-black text-blue-600 uppercase mb-1">Pembeli (Pelapor)</p>
                        <p class="font-black text-sm">{{ $dispute->buyer->name ?? '-' }}</p>
                        <p class="text-xs text-gray-500">{{ $dispute->buyer->email ?? '' }}</p>
                    </div>
                    <div class="bg-orange-50 border-2 border-orange-200 p-4 rounded">
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
                    <div class="flex gap-3"><span class="font-black text-gray-500 w-28 shrink-0">Catatan Admin:</span><span class="bg-yellow-50 border border-yellow-200 px-3 py-1 rounded text-yellow-800 font-semibold">{{ $dispute->admin_notes }}</span></div>
                    @endif
                    @if($dispute->resolvedBy)
                    <div class="flex gap-3"><span class="font-black text-gray-500 w-28 shrink-0">Diputus Oleh:</span><span class="font-bold">Admin {{ $dispute->resolvedBy->name }}</span></div>
                    @endif
                </div>
            </div>

            {{-- Rincian Transaksi --}}
            <div class="bg-white border-[3px] border-black">
                <div class="px-8 py-5 border-b-[3px] border-black bg-gray-50">
                    <h3 class="font-black text-sm uppercase">Rincian Transaksi #{{ $dispute->transaction_id }}</h3>
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
                    <div class="border-t pt-4">
                        @foreach($tx->items as $item)
                        <div class="flex items-center gap-4 py-2 border-b border-gray-100 last:border-0">
                            @if($item->product?->image)
                            <img src="{{ Storage::url($item->product->image) }}" class="w-12 h-12 object-cover rounded border" alt="">
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
            <div class="bg-white border-[3px] border-black">
                <div class="px-8 py-5 border-b-[3px] border-black bg-black text-white">
                    <h3 class="font-black text-sm uppercase">Riwayat Aktivitas</h3>
                </div>
                <div class="p-8">
                    @forelse($dispute->logs->sortByDesc('created_at') as $log)
                    <div class="flex gap-4 pb-4 mb-4 border-b border-gray-100 last:border-0 last:mb-0 last:pb-0">
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
               class="flex items-center justify-center gap-2 w-full py-4 bg-purple-600 hover:bg-purple-700 text-white font-black text-sm uppercase transition-all">
                Pantau Chat Sengketa
            </a>

            {{-- PANEL AKSI --}}
            <div class="bg-white border-[3px] border-black">
                <div class="px-6 py-4 bg-black text-white border-b-[3px] border-black">
                    <h3 class="font-black uppercase text-sm">Panel Resolusi Admin</h3>
                </div>
                <div class="p-5 space-y-4">

                    {{-- STEP 1: Tandai Reviewing --}}
                    @if($dispute->status === 'open')
                    <div class="border-2 border-yellow-300 bg-yellow-50 p-4 rounded">
                        <p class="text-xs font-black uppercase text-yellow-700 mb-2">Langkah 1 — Mulai Tinjau</p>
                        <form action="{{ route('admin.disputes.reviewing', $dispute->id) }}" method="POST">
                            @csrf
                            <button class="w-full py-3 bg-yellow-500 hover:bg-yellow-600 text-white font-black uppercase text-xs transition-all">
                            Tandai Sedang Ditinjau
                            </button>
                        </form>
                    </div>
                    @endif

                    {{-- STEP 2: Putuskan Pemenang --}}
                    @if(in_array($dispute->status, ['open', 'admin_reviewing']))
                    <div class="border-2 border-black p-4">
                        <p class="text-xs font-black uppercase text-gray-700 mb-3">Putuskan Pemenang</p>
                        <form action="{{ route('admin.disputes.resolve', $dispute->id) }}" method="POST" class="space-y-3"
                              onsubmit="return confirm('Yakin? Keputusan tidak bisa dibatalkan!')">
                            @csrf
                            <textarea name="admin_notes" rows="2" placeholder="Alasan keputusan..."
                                class="w-full border-2 border-gray-300 focus:border-black px-3 py-2 text-xs outline-none resize-none rounded transition-all"></textarea>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="submit" name="winner" value="buyer"
                                    class="py-3 bg-blue-600 hover:bg-blue-700 text-white font-black uppercase text-[10px] transition-all rounded flex flex-col items-center gap-0.5">
                                    <span class="text-lg">🔵</span>
                                    <span>Pembeli Menang</span>
                                    <span class="opacity-70 text-[9px] font-normal normal-case">→ Tahapan refund</span>
                                </button>
                                <button type="submit" name="winner" value="seller"
                                    class="py-3 bg-green-600 hover:bg-green-700 text-white font-black uppercase text-[10px] transition-all rounded flex flex-col items-center gap-0.5">
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
                    <div class="border-2 border-indigo-200 bg-indigo-50 p-4 rounded">
                        <p class="text-xs font-black uppercase text-indigo-700 mb-1">Admin Override</p>
                        <p class="text-[9px] text-indigo-500 mb-3">Jika penjual tidak konfirmasi dalam 3 hari, admin bisa paksa konfirmasi.</p>
                        <form action="{{ route('admin.disputes.confirmReceived', $dispute->id) }}" method="POST"
                              onsubmit="return confirm('Konfirmasi bahwa penjual SUDAH menerima barang? Refund akan langsung diproses!')">
                            @csrf
                            <button class="w-full py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-black uppercase text-[10px] transition-all rounded">
                             Konfirmasi Penjual Terima Barang → Refund
                            </button>
                        </form>
                    </div>
                    @endif

                    {{-- FORCE REFUND --}}
                    @if(in_array($dispute->status, ['buyer_won', 'buyer_shipping_back', 'seller_received_back', 'open', 'admin_reviewing']))
                    <div class="border-2 border-red-200 bg-red-50 p-4 rounded">
                        <p class="text-xs font-black uppercase text-red-700 mb-1">Force Refund (Bypass Barang)</p>
                        <p class="text-[9px] text-red-500 mb-3">Refund langsung tanpa menunggu pengembalian barang.</p>
                        <form action="{{ route('admin.disputes.forceRefund', $dispute->id) }}" method="POST"
                              onsubmit="return confirm('FORCE REFUND: Proses refund LANGSUNG. Lanjutkan?')">
                            @csrf
                            <input type="hidden" name="admin_notes" value="Force refund oleh admin">
                            <button class="w-full py-2 bg-red-600 hover:bg-red-700 text-white font-black uppercase text-[10px] transition-all rounded">
                             Proses Refund Sekarang
                            </button>
                        </form>
                    </div>
                    @endif

                    {{-- SELESAI --}}
                    @if(in_array($dispute->status, ['refunded', 'closed', 'seller_won']))
                    <div class="bg-gray-50 border-2 border-gray-200 p-4 rounded text-center">
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
            <div class="bg-white border-[3px] border-black p-5">
                <h4 class="font-black text-xs uppercase mb-3 text-gray-500">💼 Simulasi Dana</h4>
                @php
                    $buyerWallet  = \App\Models\Wallet::where('user_id', $dispute->buyer_id)->first();
                    $sellerWallet = \App\Models\Wallet::where('user_id', $dispute->seller_id)->first();
                    $txAmount     = $dispute->transaction->total_amount ?? 0;
                    $platformFee  = round($txAmount * 0.10);
                    $netToSeller  = $txAmount - $platformFee;
                @endphp
                <div class="space-y-3 text-xs">
                    <div class="p-3 bg-blue-50 rounded border border-blue-100">
                        <p class="font-black text-blue-600 mb-1">Wallet Pembeli</p>
                        <div class="grid grid-cols-2 gap-1">
                            <span class="text-gray-500">Saldo:</span>
                            <span class="font-bold text-right">Rp {{ number_format($buyerWallet?->balance ?? 0, 0, ',', '.') }}</span>
                            <span class="text-gray-500">Pending:</span>
                            <span class="font-bold text-right">Rp {{ number_format($buyerWallet?->pending_balance ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="p-3 bg-orange-50 rounded border border-orange-100">
                        <p class="font-black text-orange-600 mb-1">Wallet Penjual</p>
                        <div class="grid grid-cols-2 gap-1">
                            <span class="text-gray-500">Saldo:</span>
                            <span class="font-bold text-right">Rp {{ number_format($sellerWallet?->balance ?? 0, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="p-3 bg-gray-50 rounded border text-[10px]">
                        <div class="flex justify-between"><span>Nilai TXN:</span><span class="font-bold">Rp {{ number_format($txAmount, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between text-blue-600"><span>Refund Pembeli:</span><span class="font-bold">+Rp {{ number_format($txAmount, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between text-green-600"><span>Net Penjual (90%):</span><span class="font-bold">+Rp {{ number_format($netToSeller, 0, ',', '.') }}</span></div>
                        <div class="flex justify-between text-gray-500"><span>Fee Platform (10%):</span><span class="font-bold">Rp {{ number_format($platformFee, 0, ',', '.') }}</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
