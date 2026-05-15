<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Techno Market</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Space Grotesk', sans-serif;
            background-color: #ffffff;
        }

        .neo-brutalism {
            box-shadow: 4px 4px 0px 0px rgba(0, 0, 0, 1);
        }

        .neo-brutalism-lg {
            box-shadow: 8px 8px 0px 0px rgba(0, 0, 0, 1);
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        ::-webkit-scrollbar-thumb {
            background: #000;
        }
    </style>
</head>

<body class="bg-white text-black antialiased">
    <div class="flex min-h-screen">
        <!-- Sidebar Minimalist -->
        <aside class="w-64 bg-white border-r-[3px] border-black hidden md:flex flex-col fixed h-full z-20">
            <div class="p-6 bg-black text-white border-b-[3px] border-black">
                <div class="flex flex-col">
                    <span class="text-xl font-black uppercase tracking-tighter italic">
                        ADMIN
                    </span>
                    <span class="text-[10px] font-mono uppercase tracking-widest opacity-60">Market Barang Bekas</span>
                </div>
            </div>

            <nav class="flex-1 px-4 py-8 space-y-2 overflow-y-auto">
                @php
                    $navItems = [
                        ['route' => 'admin.dashboard', 'label' => 'Dashboard', 'icon' => 'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                        ['route' => 'admin.users', 'label' => 'Manajemen User', 'icon' => 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                        ['route' => 'admin.transactions', 'label' => 'Transaksi', 'icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                        ['route' => 'admin.vouchers', 'label' => 'Voucher', 'icon' => 'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
                        ['route' => 'admin.payment_methods', 'label' => 'Metode Pembayaran', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                        ['route' => 'admin.categories', 'label' => 'Kategori', 'icon' => 'M4 6h16M4 12h16m-7 6h7'],
                        ['route' => 'admin.disputes.index', 'label' => 'Laporan Masalah', 'icon' => 'M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3'],
                        ['route' => 'admin.balances', 'label' => 'Saldo & Keuangan', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['route' => 'admin.wallet_logs', 'label' => 'Log Audit MeyPay', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        ['route' => 'admin.ad_banners', 'label' => 'Banner Iklan', 'icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        ['route' => 'admin.settings', 'label' => 'Pengaturan', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                    ];
                @endphp

                @foreach($navItems as $item)
                    <a href="{{ route($item['route']) }}"
                        class="flex items-center gap-4 px-4 py-3 text-sm font-bold border-2 transition-all {{ request()->routeIs($item['route'] . '*') ? 'bg-black text-white border-black neo-brutalism' : 'text-black border-transparent hover:border-black' }}">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="{{ $item['icon'] }}">
                            </path>
                        </svg>
                        <span class="uppercase tracking-tight">{{ $item['label'] }}</span>
                    </a>
                @endforeach

                <div class="border-t-2 border-black my-6"></div>

                <a href="{{ route('products.index') }}"
                    class="flex items-center gap-4 px-4 py-3 text-sm font-bold text-black border-2 border-transparent hover:border-black transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span class="uppercase italic">Kembali ke Toko</span>
                </a>
            </nav>

            <!-- Bottom Profile -->
            <div class="p-4 border-t-[3px] border-black bg-gray-50">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 border-2 border-black bg-white flex items-center justify-center font-black">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-black uppercase truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[9px] font-mono uppercase text-gray-500">Admin Utama</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="border-2 border-black p-1.5 hover:bg-black hover:text-white transition-all">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1">
                                </path>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 md:ml-64 min-h-screen">
            <!-- Mobile Header Minimalist -->
            <div
                class="md:hidden h-20 bg-white border-b-[3px] border-black flex items-center justify-between px-6 sticky top-0 z-30">
                <div class="flex flex-col">
                    <span class="font-black text-xl uppercase italic tracking-tighter">ADMIN</span>
                    <span class="text-[9px] font-mono uppercase opacity-50">Kendali Toko</span>
                </div>
                <button class="border-2 border-black p-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                            d="M4 6h16M4 12h16M4 18h16"></path>
                    </svg>
                </button>
            </div>

            <!-- Content Container -->
            <div class="p-6 md:p-10">
                <!-- Alerts / Sessions -->
                @if(session('success'))
                    <div class="mb-8 bg-white border-[3px] border-black p-5 neo-brutalism flex items-center gap-4">
                        <div class="bg-black text-white p-1">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7">
                                </path>
                            </svg>
                        </div>
                        <div class="uppercase font-bold text-sm tracking-tight">{{ session('success') }}</div>
                    </div>
                @endif

                @if(session('error'))
                    <div
                        class="mb-8 bg-white border-[3px] border-red-600 p-5 shadow-[4px_4px_0px_0px_rgba(220,38,38,1)] flex items-center gap-4 text-red-600">
                        <div class="bg-red-600 text-white p-1">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </div>
                        <div class="uppercase font-bold text-sm tracking-tight">{{ session('error') }}</div>
                    </div>
                @endif

                <!-- Yield Page Content -->
                <div class="min-h-screen">
                    @yield('content')
                </div>

                <!-- Footer Log -->
                <footer
                    class="mt-20 pt-10 border-t-2 border-black flex flex-col md:flex-row justify-between items-center gap-4 text-[10px] font-mono uppercase text-gray-400">
                    <div>Techno Market Admin &copy; 2026</div>
                    <div class="flex gap-6">
                        <span>Terminal: Aktif</span>
                        <span>Waktu Berjalan: {{ now()->diffForHumans() }}</span>
                    </div>
                </footer>
            </div>
        </main>
    </div>

    @stack('scripts')
</body>

</html>