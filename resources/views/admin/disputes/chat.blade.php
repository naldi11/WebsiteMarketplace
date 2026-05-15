@extends('layouts.admin')

@section('content')
    <div class="pb-4 -mt-2">

        {{-- ── TOP BAR ── --}}
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('admin.disputes.show', $dispute->id) }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 border-[3px] border-black font-black text-xs uppercase hover:bg-black hover:text-white transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Detail
            </a>
            <div class="flex-1">
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl font-black tracking-tighter uppercase italic">
                        God View Chat — Sengketa #D{{ $dispute->id }}
                    </h1>
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-purple-600 text-white text-[10px] font-black uppercase tracking-widest">
                        <span class="w-1.5 h-1.5 rounded-full bg-purple-300 animate-pulse"></span>
                        Mode Pantauan Admin
                    </span>
                </div>
                <p class="text-gray-500 font-mono text-xs uppercase mt-0.5">
                    <span class="text-blue-600 font-bold">{{ $dispute->buyer->name }}</span>
                    <span class="mx-2 text-gray-300">↔</span>
                    <span class="text-orange-600 font-bold">{{ $dispute->seller->name }}</span>
                    <span class="mx-2 text-gray-300">|</span>
                    Status: <span
                        class="font-bold text-black">{{ strtoupper(str_replace('_', ' ', $dispute->status)) }}</span>
                </p>
            </div>
        </div>

        @if(session('success'))
            <div
                class="flex items-center gap-3 bg-green-50 border-l-4 border-green-500 text-green-800 px-5 py-3 mb-5 font-bold text-sm">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- ── MAIN LAYOUT ── --}}
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6" style="height: calc(100vh - 220px); min-height: 550px;">

            {{-- ══════════════ CHAT PANEL ══════════════ --}}
            <div
                class="xl:col-span-8 flex flex-col overflow-hidden border-[3px] border-black shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">

                {{-- Chat Header --}}
                <div class="bg-gray-900 text-white px-6 py-4 flex items-center justify-between shrink-0">
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <div
                                class="w-10 h-10 rounded-full bg-gradient-to-br from-purple-500 to-purple-700 flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </div>
                            <span
                                class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-400 rounded-full border-2 border-gray-900"></span>
                        </div>
                        <div>
                            <p class="font-black text-sm uppercase tracking-wide">Percakapan Sengketa</p>
                            <p class="text-gray-400 text-[10px] font-mono">{{ $messages->count() }} pesan tercatat</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div
                            class="flex items-center gap-1.5 bg-blue-600/20 border border-blue-500/30 px-3 py-1.5 rounded-full">
                            <div
                                class="w-5 h-5 rounded-full bg-blue-500 flex items-center justify-center text-white text-[9px] font-black">
                                {{ substr($dispute->buyer->name, 0, 1) }}
                            </div>
                            <span class="text-blue-300 text-[10px] font-black">{{ $dispute->buyer->name }}</span>
                        </div>
                        <div
                            class="flex items-center gap-1.5 bg-orange-600/20 border border-orange-500/30 px-3 py-1.5 rounded-full">
                            <div
                                class="w-5 h-5 rounded-full bg-orange-500 flex items-center justify-center text-white text-[9px] font-black">
                                {{ substr($dispute->seller->name, 0, 1) }}
                            </div>
                            <span class="text-orange-300 text-[10px] font-black">{{ $dispute->seller->name }}</span>
                        </div>
                        <div
                            class="flex items-center gap-1.5 bg-purple-600/20 border border-purple-500/30 px-3 py-1.5 rounded-full">
                            <div
                                class="w-5 h-5 rounded-full bg-purple-500 flex items-center justify-center text-white text-[9px] font-black">
                                A</div>
                            <span class="text-purple-300 text-[10px] font-black">Admin</span>
                        </div>
                    </div>
                </div>

                {{-- Messages Area --}}
                <div class="flex-1 overflow-y-auto bg-gray-50" id="chatBox"
                    style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23e5e7eb\' fill-opacity=\'0.5\'%3E%3Ccircle cx=\'3\' cy=\'3\' r=\'1\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');">
                    <div class="p-5 space-y-4">
                        @forelse($messages as $msg)
                            @php
                                $isBuyer = $msg->sender_id === $dispute->buyer_id;
                                $isSeller = $msg->sender_id === $dispute->seller_id;
                                $isAdmin = !$isBuyer && !$isSeller;
                                $isSystem = str_contains($msg->message, '⚖️') || str_contains($msg->message, '✅') || str_contains($msg->message, '🔍') || str_contains($msg->message, '📦') || str_contains($msg->message, '👮') || str_contains($msg->message, 'TAHAPAN') || str_contains($msg->message, 'REFUND BERHASIL') || str_contains($msg->message, 'Catatan Admin');
                                // Bersihkan prefix [ADMIN] dan emoji di depan pesan admin
                                $cleanAdminMsg = preg_replace('/^[\p{So}\p{Sm}\p{Sk}\p{Sc}\p{Ps}\p{Pe}\s]*\[ADMIN\]\s*/u', '', $msg->message);
                                $cleanAdminMsg = trim($cleanAdminMsg);
                            @endphp

                            @if($isSystem && !$isAdmin)
                                {{-- System / Notifikasi Otomatis --}}
                                <div class="flex justify-center my-3">
                                    <div class="max-w-lg w-full">
                                        <div
                                            class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-3.5 text-center shadow-sm">
                                            <div class="flex items-center justify-center gap-2 mb-1.5">
                                                <div
                                                    class="w-5 h-5 rounded-full bg-amber-400 flex items-center justify-center shrink-0">
                                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                                <span
                                                    class="text-amber-700 text-[10px] font-black uppercase tracking-widest">Notifikasi
                                                    Sistem</span>
                                            </div>
                                            <p class="text-amber-800 text-[11px] font-semibold whitespace-pre-line leading-relaxed">
                                                {{ $msg->message }}</p>
                                            <p class="text-amber-500 text-[9px] mt-2 font-mono">
                                                {{ $msg->created_at?->format('d M Y, H:i') }}</p>
                                        </div>
                                    </div>
                                </div>

                            @elseif($isAdmin)
                                {{-- Admin Message --}}
                                <div class="flex justify-center my-3">
                                    <div class="max-w-lg w-full">
                                        <div class="relative bg-purple-600 text-white rounded-2xl px-5 py-3.5 shadow-lg">
                                            <div class="flex items-center gap-2 mb-2">
                                                <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center">
                                                    <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                                <span class="text-purple-200 text-[10px] font-black uppercase tracking-widest">Admin
                                                    — Intervensi</span>
                                            </div>
                                            <p class="text-white text-sm font-semibold whitespace-pre-wrap leading-relaxed">
                                                {{ $cleanAdminMsg }}</p>
                                            <p class="text-purple-300 text-[9px] mt-2 font-mono text-right">
                                                {{ $msg->created_at?->format('d M Y, H:i') }}</p>
                                            {{-- Triangle --}}
                                            <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-4 h-2 bg-purple-600"
                                                style="clip-path: polygon(0 0, 100% 0, 50% 100%)"></div>
                                        </div>
                                    </div>
                                </div>

                            @elseif($isBuyer)
                                {{-- Buyer (Left) --}}
                                <div class="flex items-end gap-3">
                                    <div
                                        class="w-8 h-8 rounded-full bg-blue-500 text-white text-xs font-black flex items-center justify-center shrink-0 shadow">
                                        {{ substr($dispute->buyer->name, 0, 1) }}
                                    </div>
                                    <div class="max-w-xs lg:max-w-sm">
                                        <p class="text-[10px] text-blue-600 font-black uppercase mb-1.5 pl-1">
                                            {{ $dispute->buyer->name }} · Pembeli
                                        </p>
                                        @if($msg->attachment)
                                            <img src="{{ Storage::url($msg->attachment) }}"
                                                class="rounded-2xl rounded-bl-none border-2 border-blue-200 max-w-full mb-1.5 shadow-sm"
                                                alt="attachment">
                                        @endif
                                        @if($msg->message)
                                            <div
                                                class="bg-white border border-gray-200 text-gray-800 text-sm px-4 py-2.5 rounded-2xl rounded-bl-none whitespace-pre-wrap shadow-sm leading-relaxed">
                                                {{ $msg->message }}
                                            </div>
                                        @endif
                                        <p class="text-[9px] text-gray-400 mt-1 pl-1 font-mono">
                                            {{ $msg->created_at?->format('d M, H:i') }}</p>
                                    </div>
                                </div>

                            @else
                                {{-- Seller (Right) --}}
                                <div class="flex items-end justify-end gap-3">
                                    <div class="max-w-xs lg:max-w-sm">
                                        <p class="text-[10px] text-orange-600 font-black uppercase mb-1.5 pr-1 text-right">
                                            {{ $dispute->seller->name }} · Penjual
                                        </p>
                                        @if($msg->attachment)
                                            <img src="{{ Storage::url($msg->attachment) }}"
                                                class="rounded-2xl rounded-br-none border-2 border-orange-200 max-w-full mb-1.5 ml-auto shadow-sm"
                                                alt="attachment">
                                        @endif
                                        @if($msg->message)
                                            <div
                                                class="bg-orange-500 text-white text-sm px-4 py-2.5 rounded-2xl rounded-br-none whitespace-pre-wrap shadow-sm leading-relaxed">
                                                {{ $msg->message }}
                                            </div>
                                        @endif
                                        <p class="text-[9px] text-gray-400 mt-1 pr-1 font-mono text-right">
                                            {{ $msg->created_at?->format('d M, H:i') }}</p>
                                    </div>
                                    <div
                                        class="w-8 h-8 rounded-full bg-orange-500 text-white text-xs font-black flex items-center justify-center shrink-0 shadow">
                                        {{ substr($dispute->seller->name, 0, 1) }}
                                    </div>
                                </div>
                            @endif

                        @empty
                            <div class="flex flex-col items-center justify-center py-20 text-center">
                                <div class="w-16 h-16 rounded-full bg-gray-200 flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </div>
                                <p class="text-gray-400 text-sm font-black uppercase tracking-widest">Belum Ada Pesan</p>
                                <p class="text-gray-300 text-xs mt-1">Percakapan sengketa belum dimulai</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Admin Send Form --}}
                <div class="shrink-0 bg-white border-t-[3px] border-black px-5 py-4">
                    <p
                        class="text-[10px] font-black uppercase tracking-widest text-purple-600 mb-3 flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                clip-rule="evenodd" />
                        </svg>
                        Kirim Pesan sebagai Admin — Terlihat oleh Pembeli & Penjual
                    </p>
                    <form action="{{ route('admin.disputes.chat.send', $dispute->id) }}" method="POST" class="flex gap-3">
                        @csrf
                        <div class="flex-1 relative">
                            <input type="text" name="message" placeholder="Ketik pesan intervensi admin..."
                                class="w-full border-[2.5px] border-gray-200 focus:border-purple-500 rounded-xl px-4 py-3 text-sm font-medium outline-none transition-all bg-gray-50 focus:bg-white pr-12"
                                required>
                            <div class="absolute right-3 top-1/2 -translate-y-1/2 text-purple-400">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                        </div>
                        <button type="submit"
                            class="px-6 py-3 bg-purple-600 hover:bg-purple-700 active:bg-purple-800 text-white font-black text-xs uppercase tracking-wide rounded-xl transition-all flex items-center gap-2 shadow-lg shadow-purple-500/30">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                            </svg>
                            Kirim
                        </button>
                    </form>
                </div>
            </div>

            {{-- ══════════════ SIDEBAR ══════════════ --}}
            <div class="xl:col-span-4 flex flex-col gap-4 overflow-y-auto">

                {{-- Info Sengketa --}}
                <div class="bg-white border-[3px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                    <div class="px-5 py-4 bg-black text-white">
                        <h3 class="font-black text-xs uppercase tracking-widest">Info Sengketa</h3>
                    </div>
                    <div class="p-5 space-y-3 text-xs">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-500 font-semibold">ID Sengketa</span>
                            <span class="font-black text-lg">#D{{ $dispute->id }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-500 font-semibold">Transaksi</span>
                            <span class="font-bold">#{{ $dispute->transaction_id }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-500 font-semibold">Nilai</span>
                            <span class="font-black text-base">Rp
                                {{ number_format($dispute->transaction->total_amount ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-gray-500 font-semibold">Status</span>
                            <span
                                class="px-2 py-1 bg-black text-white font-black uppercase text-[9px] tracking-widest">{{ strtoupper(str_replace('_', ' ', $dispute->status)) }}</span>
                        </div>
                        @if($dispute->winner)
                            <div class="flex justify-between items-center py-2">
                                <span class="text-gray-500 font-semibold">Pemenang</span>
                                <span
                                    class="font-black uppercase text-sm {{ $dispute->winner === 'buyer' ? 'text-blue-600' : 'text-orange-600' }}">
                                    {{ $dispute->winner === 'buyer' ? 'Pembeli' : 'Penjual' }}
                                </span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Pihak yang Bersengketa --}}
                <div class="bg-white border-[3px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                    <div class="px-5 py-4 bg-black text-white">
                        <h3 class="font-black text-xs uppercase tracking-widest">Pihak Bersengketa</h3>
                    </div>
                    <div class="p-5 space-y-4">
                        {{-- Pembeli --}}
                        <div class="flex items-center gap-3 p-3 bg-blue-50 border-2 border-blue-100 rounded-xl">
                            <div
                                class="w-10 h-10 rounded-full bg-blue-500 text-white font-black flex items-center justify-center text-base shrink-0">
                                {{ substr($dispute->buyer->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-[9px] font-black uppercase text-blue-500 tracking-widest mb-0.5">Pembeli
                                    (Pelapor)</p>
                                <p class="font-black text-sm text-gray-800">{{ $dispute->buyer->name }}</p>
                                <p class="text-[10px] text-gray-500">{{ $dispute->buyer->email }}</p>
                            </div>
                        </div>
                        {{-- Penjual --}}
                        <div class="flex items-center gap-3 p-3 bg-orange-50 border-2 border-orange-100 rounded-xl">
                            <div
                                class="w-10 h-10 rounded-full bg-orange-500 text-white font-black flex items-center justify-center text-base shrink-0">
                                {{ substr($dispute->seller->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-[9px] font-black uppercase text-orange-500 tracking-widest mb-0.5">Penjual
                                    (Terlapor)</p>
                                <p class="font-black text-sm text-gray-800">{{ $dispute->seller->name }}</p>
                                <p class="text-[10px] text-gray-500">{{ $dispute->seller->email }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Alasan Sengketa --}}
                <div class="bg-white border-[3px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                    <div class="px-5 py-4 bg-black text-white">
                        <h3 class="font-black text-xs uppercase tracking-widest">Alasan Sengketa</h3>
                    </div>
                    <div class="p-5">
                        <p class="text-sm font-bold text-gray-700 mb-2">{{ $dispute->reason }}</p>
                        @if($dispute->description)
                            <p class="text-xs text-gray-500 leading-relaxed bg-gray-50 rounded-lg p-3 border border-gray-100">
                                {{ $dispute->description }}</p>
                        @endif
                    </div>
                </div>

                {{-- Panel Resolusi --}}
                <a href="{{ route('admin.disputes.show', $dispute->id) }}"
                    class="flex items-center justify-center gap-2 w-full py-4 bg-black hover:bg-gray-800 text-white font-black text-xs uppercase tracking-widest transition-all border-[3px] border-black shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" />
                    </svg>
                    Panel Resolusi
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7" />
                    </svg>
                </a>

                {{-- Auto Refresh Counter --}}
                <div class="bg-gray-900 text-white p-4 rounded-xl text-center">
                    <p class="text-[10px] text-gray-400 font-mono uppercase tracking-widest mb-2">Auto-refresh dalam</p>
                    <div id="refreshCountdown" class="text-4xl font-black tabular-nums text-white">15</div>
                    <p class="text-[9px] text-gray-500 mt-1">detik</p>
                    <div class="mt-3 w-full bg-gray-700 rounded-full h-1 overflow-hidden">
                        <div id="refreshBar" class="h-full bg-purple-500 transition-all duration-1000 ease-linear"
                            style="width: 100%"></div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <script>
        // Scroll to bottom on load
        const chatBox = document.getElementById('chatBox');
        if (chatBox) chatBox.scrollTop = chatBox.scrollHeight;

        // Auto refresh countdown with progress bar
        let secs = 15;
        const totalSecs = 15;
        const countdown = document.getElementById('refreshCountdown');
        const bar = document.getElementById('refreshBar');

        setInterval(() => {
            secs--;
            if (countdown) countdown.textContent = secs;
            if (bar) bar.style.width = ((secs / totalSecs) * 100) + '%';
            if (secs <= 0) location.reload();
        }, 1000);
    </script>
@endsection