@extends('layouts.admin')

@section('content')
    <div class="pb-4 -mt-2">

        {{-- ── TOP BAR ── --}}
        <div class="flex items-center gap-4 mb-6">
            <a href="{{ route('admin.disputes.show', $dispute->id) }}"
                class="inline-flex items-center gap-2 px-4 py-2.5 btn-outline rounded-xl font-black text-xs uppercase transition-all">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali ke Detail
            </a>
            <div class="flex-1">
                <div class="flex items-center gap-3 flex-wrap">
                    <h1 class="text-2xl font-black tracking-tighter uppercase italic">
                        Percakapan Laporan Masalah #D{{ $dispute->id }}
                    </h1>
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-purple-600 text-white text-[10px] font-black uppercase tracking-widest rounded-full">
                        <span class="w-1.5 h-1.5 rounded-full bg-purple-300 animate-pulse"></span>
                        Mode Pantauan Admin
                    </span>
                </div>
                <p class="text-gray-500 font-mono text-xs uppercase mt-0.5">
                    <span class="text-blue-600 font-bold">{{ $dispute->buyer->name }}</span>
                    <span class="mx-2 text-gray-300">↔</span>
                    <span class="text-orange-600 font-bold">{{ $dispute->seller->name }}</span>
                    <span class="mx-2 text-gray-300">|</span>
                    Status: <span class="font-bold text-slate-800">{{ strtoupper(str_replace('_', ' ', $dispute->status)) }}</span>
                </p>
            </div>
        </div>

        @if(session('success'))
            <div class="flex items-center gap-3 bg-green-50 border-l-4 border-green-500 text-green-800 px-5 py-3 mb-5 font-bold text-sm rounded-xl">
                <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- ── MAIN LAYOUT ── --}}
        <div class="grid grid-cols-1 xl:grid-cols-12 gap-6 items-start">

            {{-- ══════════════ CHAT PANEL ══════════════ --}}
            <div class="xl:col-span-8 flex flex-col glass-card shadow-xl" style="height: calc(100vh - 210px); min-height: 520px;">

                {{-- Chat Header --}}
                <div class="gradient-header-red text-white px-6 py-4 flex items-center justify-between shrink-0 rounded-t-2xl">
                    <div class="flex items-center gap-4">
                        <div class="relative">
                            <div class="w-10 h-10 rounded-full bg-white/20 backdrop-blur flex items-center justify-center">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                </svg>
                            </div>
                            <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 bg-green-400 rounded-full border-2 border-white/50"></span>
                        </div>
                        <div>
                            <p class="font-black text-sm uppercase tracking-wide">Percakapan</p>
                            <p class="text-white/60 text-[10px] font-mono">{{ $messages->count() }} pesan tercatat</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1.5 bg-blue-600/20 border border-blue-400/30 px-3 py-1.5 rounded-full">
                            <div class="w-5 h-5 rounded-full bg-blue-500 flex items-center justify-center text-white text-[9px] font-black">
                                {{ substr($dispute->buyer->name, 0, 1) }}
                            </div>
                            <span class="text-blue-200 text-[10px] font-black">{{ $dispute->buyer->name }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 bg-orange-600/20 border border-orange-400/30 px-3 py-1.5 rounded-full">
                            <div class="w-5 h-5 rounded-full bg-orange-500 flex items-center justify-center text-white text-[9px] font-black">
                                {{ substr($dispute->seller->name, 0, 1) }}
                            </div>
                            <span class="text-orange-200 text-[10px] font-black">{{ $dispute->seller->name }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 bg-purple-600/20 border border-purple-400/30 px-3 py-1.5 rounded-full">
                            <div class="w-5 h-5 rounded-full bg-purple-500 flex items-center justify-center text-white text-[9px] font-black">A</div>
                            <span class="text-purple-200 text-[10px] font-black">Admin</span>
                        </div>
                    </div>
                </div>

                {{-- Messages Area --}}
                <div class="flex-1 overflow-y-auto bg-gradient-to-b from-slate-50/60 to-indigo-50/40" id="chatBox"
                    style="background-image: url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23e0e7ff\' fill-opacity=\'0.4\'%3E%3Ccircle cx=\'3\' cy=\'3\' r=\'1\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');">
                    <div class="p-5 space-y-4">
                        @forelse($messages as $msg)
                            @php
                                $isBuyer  = $msg->sender_id === $dispute->buyer_id;
                                $isSeller = $msg->sender_id === $dispute->seller_id;
                                $isAdmin  = !$isBuyer && !$isSeller;
                                $isSystem = str_contains($msg->message, '⚖️') || str_contains($msg->message, '✅') || str_contains($msg->message, '🔍') || str_contains($msg->message, '📦') || str_contains($msg->message, '👮') || str_contains($msg->message, 'TAHAPAN') || str_contains($msg->message, 'REFUND BERHASIL') || str_contains($msg->message, 'Catatan Admin');
                                $cleanAdminMsg = preg_replace('/^[\p{So}\p{Sm}\p{Sk}\p{Sc}\p{Ps}\p{Pe}\s]*\[ADMIN\]\s*/u', '', $msg->message);
                                $cleanAdminMsg = trim($cleanAdminMsg);
                            @endphp

                            @if($isSystem && !$isAdmin)
                                {{-- System / Notifikasi Otomatis --}}
                                <div class="flex justify-center my-3">
                                    <div class="max-w-lg w-full">
                                        <div class="bg-amber-50 border border-amber-200 rounded-2xl px-5 py-3.5 text-center shadow-sm backdrop-blur">
                                            <div class="flex items-center justify-center gap-2 mb-1.5">
                                                <div class="w-5 h-5 rounded-full bg-amber-400 flex items-center justify-center shrink-0">
                                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                                <span class="text-amber-700 text-[10px] font-black uppercase tracking-widest">Notifikasi Sistem</span>
                                            </div>
                                            <p class="text-amber-800 text-[11px] font-semibold whitespace-pre-line leading-relaxed">{{ $msg->message }}</p>
                                            <p class="text-amber-500 text-[9px] mt-2 font-mono">{{ $msg->created_at?->format('d M Y, H:i') }}</p>
                                        </div>
                                    </div>
                                </div>

                            @elseif($isAdmin)
                                {{-- Admin Message — purple glass --}}
                                <div class="flex justify-center my-3">
                                    <div class="max-w-lg w-full">
                                        <div class="relative bg-purple-600/90 backdrop-blur text-white rounded-2xl px-5 py-3.5 shadow-lg shadow-purple-500/25">
                                            <div class="flex items-center gap-2 mb-2">
                                                <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center">
                                                    <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                                <span class="text-purple-200 text-[10px] font-black uppercase tracking-widest">Admin — Intervensi</span>
                                            </div>
                                            <p class="text-white text-sm font-semibold whitespace-pre-wrap leading-relaxed">{{ $cleanAdminMsg }}</p>
                                            <p class="text-purple-300 text-[9px] mt-2 font-mono text-right">{{ $msg->created_at?->format('d M Y, H:i') }}</p>
                                            <div class="absolute -bottom-2 left-1/2 -translate-x-1/2 w-4 h-2 bg-purple-600"
                                                style="clip-path: polygon(0 0, 100% 0, 50% 100%)"></div>
                                        </div>
                                    </div>
                                </div>

                            @elseif($isBuyer)
                                {{-- Buyer (Left) — blue glass --}}
                                <div class="flex items-end gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 text-white text-xs font-black flex items-center justify-center shrink-0 shadow-lg shadow-blue-500/30">
                                        {{ substr($dispute->buyer->name, 0, 1) }}
                                    </div>
                                    <div class="max-w-xs lg:max-w-sm">
                                        <p class="text-[10px] text-blue-600 font-black uppercase mb-1.5 pl-1">
                                            {{ $dispute->buyer->name }} · Pembeli
                                        </p>
                                        @if($msg->attachment)
                                            <img src="{{ Storage::url($msg->attachment) }}"
                                                class="rounded-2xl rounded-bl-none border border-blue-200 max-w-full mb-1.5 shadow-sm"
                                                alt="attachment">
                                        @endif
                                        @if($msg->message)
                                            <div class="bg-blue-50/80 backdrop-blur border border-blue-100 text-gray-800 text-sm px-4 py-2.5 rounded-2xl rounded-bl-none whitespace-pre-wrap shadow-sm leading-relaxed">
                                                {{ $msg->message }}
                                            </div>
                                        @endif
                                        <p class="text-[9px] text-gray-400 mt-1 pl-1 font-mono">{{ $msg->created_at?->format('d M, H:i') }}</p>
                                    </div>
                                </div>

                            @else
                                {{-- Seller (Right) — green glass --}}
                                <div class="flex items-end justify-end gap-3">
                                    <div class="max-w-xs lg:max-w-sm">
                                        <p class="text-[10px] text-emerald-600 font-black uppercase mb-1.5 pr-1 text-right">
                                            {{ $dispute->seller->name }} · Penjual
                                        </p>
                                        @if($msg->attachment)
                                            <img src="{{ Storage::url($msg->attachment) }}"
                                                class="rounded-2xl rounded-br-none border border-emerald-200 max-w-full mb-1.5 ml-auto shadow-sm"
                                                alt="attachment">
                                        @endif
                                        @if($msg->message)
                                            <div class="bg-emerald-500/90 backdrop-blur text-white text-sm px-4 py-2.5 rounded-2xl rounded-br-none whitespace-pre-wrap shadow-sm shadow-emerald-500/20 leading-relaxed">
                                                {{ $msg->message }}
                                            </div>
                                        @endif
                                        <p class="text-[9px] text-gray-400 mt-1 pr-1 font-mono text-right">{{ $msg->created_at?->format('d M, H:i') }}</p>
                                    </div>
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-400 to-green-600 text-white text-xs font-black flex items-center justify-center shrink-0 shadow-lg shadow-green-500/30">
                                        {{ substr($dispute->seller->name, 0, 1) }}
                                    </div>
                                </div>
                            @endif

                        @empty
                            <div class="flex flex-col items-center justify-center py-20 text-center">
                                <div class="w-16 h-16 rounded-full bg-indigo-50 flex items-center justify-center mb-4">
                                    <svg class="w-8 h-8 text-indigo-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                                    </svg>
                                </div>
                                <p class="text-gray-400 text-sm font-black uppercase tracking-widest">Belum Ada Pesan</p>
                                <p class="text-gray-300 text-xs mt-1">Percakapan belum dimulai</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Admin Send Form --}}
                <div class="shrink-0 bg-white/80 backdrop-blur border-t border-indigo-100 px-5 py-4 rounded-b-2xl">
                    <p class="text-[10px] font-black uppercase tracking-widest text-purple-600 mb-3 flex items-center gap-2">
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
                                class="w-full border border-indigo-100 focus:border-purple-400 rounded-xl px-4 py-3 text-sm font-medium outline-none transition-all bg-white/60 focus:bg-white pr-12"
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
                                <path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z" />
                            </svg>
                            Kirim
                        </button>
                    </form>
                </div>
            </div>

            {{-- ══════════════ SIDEBAR ══════════════ --}}
            <div class="xl:col-span-4 flex flex-col gap-4">

                {{-- Info Laporan --}}
                <div class="glass-card overflow-hidden shadow-md">
                    <div class="gradient-header-red px-4 py-3 text-white flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        <h3 class="font-black text-xs uppercase tracking-widest">Info Laporan</h3>
                    </div>
                    <div class="p-4 space-y-2 text-xs">
                        <div class="flex justify-between items-center py-1.5 border-b border-indigo-50">
                            <span class="text-slate-500 font-medium">ID</span>
                            <span class="font-black text-base text-slate-800">#D{{ $dispute->id }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-indigo-50">
                            <span class="text-slate-500 font-medium">Transaksi</span>
                            <a href="{{ route('admin.transactions.show', $dispute->transaction_id) }}" class="font-bold text-indigo-600 hover:underline">#{{ $dispute->transaction_id }}</a>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-indigo-50">
                            <span class="text-slate-500 font-medium">Nilai</span>
                            <span class="font-black text-slate-800">Rp {{ number_format($dispute->transaction->total_amount ?? 0, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center py-1.5 border-b border-indigo-50">
                            <span class="text-slate-500 font-medium">Status</span>
                            <span class="px-2 py-0.5 bg-gradient-to-r from-red-500 to-rose-600 text-white font-black uppercase text-[9px] rounded-lg">{{ strtoupper(str_replace('_', ' ', $dispute->status)) }}</span>
                        </div>
                        @if($dispute->winner)
                        <div class="flex justify-between items-center py-1.5">
                            <span class="text-slate-500 font-medium">Pemenang</span>
                            <span class="font-black text-sm {{ $dispute->winner === 'buyer' ? 'text-blue-600' : 'text-emerald-600' }}">
                                {{ $dispute->winner === 'buyer' ? 'Pembeli' : 'Penjual' }}
                            </span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Pihak Terlibat --}}
                <div class="glass-card overflow-hidden shadow-md">
                    <div class="gradient-header-blue px-4 py-3 text-white flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
                        <h3 class="font-black text-xs uppercase tracking-widest">Pihak Terlibat</h3>
                    </div>
                    <div class="p-4 space-y-2">
                        <div class="flex items-center gap-2.5 p-2.5 bg-blue-50/80 border border-blue-100 rounded-xl">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-blue-600 text-white font-black flex items-center justify-center text-sm shrink-0">{{ substr($dispute->buyer->name, 0, 1) }}</div>
                            <div class="min-w-0">
                                <p class="text-[9px] font-black uppercase text-blue-500 tracking-wide">Pembeli (Pelapor)</p>
                                <p class="font-bold text-xs text-slate-800 truncate">{{ $dispute->buyer->name }}</p>
                                <p class="text-[9px] text-slate-400 truncate">{{ $dispute->buyer->email }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2.5 p-2.5 bg-emerald-50/80 border border-emerald-100 rounded-xl">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-400 to-green-600 text-white font-black flex items-center justify-center text-sm shrink-0">{{ substr($dispute->seller->name, 0, 1) }}</div>
                            <div class="min-w-0">
                                <p class="text-[9px] font-black uppercase text-emerald-600 tracking-wide">Penjual (Terlapor)</p>
                                <p class="font-bold text-xs text-slate-800 truncate">{{ $dispute->seller->name }}</p>
                                <p class="text-[9px] text-slate-400 truncate">{{ $dispute->seller->email }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Alasan --}}
                <div class="glass-card overflow-hidden shadow-md">
                    <div class="gradient-header-amber px-4 py-3 text-white flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                        <h3 class="font-black text-xs uppercase tracking-widest">Alasan</h3>
                    </div>
                    <div class="p-4">
                        <p class="text-sm font-bold text-slate-700 mb-1.5">{{ $dispute->reason }}</p>
                        @if($dispute->description)
                            <p class="text-xs text-slate-500 leading-relaxed bg-white/50 rounded-xl p-3 border border-indigo-50">{{ $dispute->description }}</p>
                        @endif
                    </div>
                </div>

                {{-- Tombol Panel Resolusi --}}
                <a href="{{ route('admin.disputes.show', $dispute->id) }}"
                    class="flex items-center justify-center gap-2 w-full py-3.5 btn-gradient text-white font-black text-xs uppercase tracking-wide rounded-xl shadow-lg">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                    Panel Resolusi
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 5l7 7-7 7"/></svg>
                </a>

                {{-- Auto Refresh --}}
                <div class="glass-card p-3.5 rounded-xl text-center">
                    <p class="text-[9px] text-slate-400 font-mono uppercase tracking-widest mb-1">Auto-refresh dalam</p>
                    <div id="refreshCountdown" class="text-3xl font-black tabular-nums text-slate-700">15</div>
                    <p class="text-[9px] text-slate-400 mt-0.5">detik</p>
                    <div class="mt-2.5 w-full bg-indigo-50 rounded-full h-1 overflow-hidden">
                        <div id="refreshBar" class="h-full bg-gradient-to-r from-purple-500 to-rose-500 transition-all duration-1000 ease-linear" style="width: 100%"></div>
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
