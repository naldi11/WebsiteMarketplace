<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - @yield('title', 'Techno Market')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Space Grotesk', sans-serif;
            background: linear-gradient(135deg, #f5f3ff 0%, #ede9fe 25%, #dbeafe 55%, #d1fae5 85%, #fef3c7 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }

        /* ── Sidebar ── */
        #admin-sidebar {
            background: linear-gradient(180deg, #1e1b4b 0%, #312e81 30%, #1d4ed8 65%, #0c4a6e 100%);
        }

        /* ── Glass Card ── */
        .glass-card {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.6);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(99, 102, 241, 0.08);
        }

        /* ── Glass Table Header ── */
        .glass-table-header {
            background: rgba(99, 102, 241, 0.06);
            border-bottom: 1px solid rgba(99, 102, 241, 0.12);
        }

        /* ── Gradient Headers (per section) ── */
        .gradient-header-purple { background: linear-gradient(135deg, #6366f1, #8b5cf6); }
        .gradient-header-blue   { background: linear-gradient(135deg, #3b82f6, #06b6d4); }
        .gradient-header-green  { background: linear-gradient(135deg, #10b981, #34d399); }
        .gradient-header-amber  { background: linear-gradient(135deg, #f59e0b, #f97316); }
        .gradient-header-teal   { background: linear-gradient(135deg, #14b8a6, #06b6d4); }
        .gradient-header-pink   { background: linear-gradient(135deg, #ec4899, #f43f5e); }
        .gradient-header-red    { background: linear-gradient(135deg, #ef4444, #f43f5e); }
        .gradient-header-orange { background: linear-gradient(135deg, #f97316, #ef4444); }
        .gradient-header-indigo { background: linear-gradient(135deg, #6366f1, #4f46e5); }
        .gradient-header-slate  { background: linear-gradient(135deg, #64748b, #475569); }

        /* ── Buttons ── */
        .btn-gradient {
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border: none;
            box-shadow: 0 4px 15px rgba(99, 102, 241, 0.35);
            transition: all 0.2s;
        }
        .btn-gradient:hover {
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
            transform: translateY(-1px);
        }
        .btn-gradient-amber {
            background: linear-gradient(135deg, #f59e0b, #f97316);
            border: none;
            box-shadow: 0 4px 15px rgba(245, 158, 11, 0.35);
            transition: all 0.2s;
        }
        .btn-gradient-amber:hover {
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.5);
            transform: translateY(-1px);
        }
        .btn-gradient-teal {
            background: linear-gradient(135deg, #14b8a6, #06b6d4);
            border: none;
            box-shadow: 0 4px 15px rgba(20, 184, 166, 0.35);
            transition: all 0.2s;
        }
        .btn-gradient-teal:hover {
            box-shadow: 0 6px 20px rgba(20, 184, 166, 0.5);
            transform: translateY(-1px);
        }
        .btn-gradient-green {
            background: linear-gradient(135deg, #10b981, #34d399);
            border: none;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.35);
            transition: all 0.2s;
        }
        .btn-gradient-green:hover {
            box-shadow: 0 6px 20px rgba(16, 185, 129, 0.5);
            transform: translateY(-1px);
        }
        .btn-danger {
            background: linear-gradient(135deg, #ef4444, #f43f5e);
            border: none;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
            transition: all 0.2s;
        }
        .btn-danger:hover {
            background: linear-gradient(135deg, #dc2626, #e11d48);
            box-shadow: 0 6px 20px rgba(239, 68, 68, 0.45);
            transform: translateY(-1px);
        }
        .btn-outline {
            background: rgba(255,255,255,0.7);
            border: 1px solid rgba(99,102,241,0.3);
            color: #6366f1;
            transition: all 0.2s;
        }
        .btn-outline:hover {
            background: rgba(99,102,241,0.08);
            border-color: #6366f1;
        }

        /* ── Badges ── */
        .badge-active   { background: #dcfce7; color: #15803d; border: 1px solid #bbf7d0; }
        .badge-inactive { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
        .badge-danger   { background: #fee2e2; color: #b91c1c; border: 1px solid #fecaca; }
        .badge-warning  { background: #fef3c7; color: #b45309; border: 1px solid #fde68a; }
        .badge-info     { background: #dbeafe; color: #1d4ed8; border: 1px solid #bfdbfe; }

        /* ── Scrollbar ── */
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(99,102,241,0.3); border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: rgba(99,102,241,0.5); }

        /* ── Nav item colors per route ── */
        .nav-active-dashboard  { background: linear-gradient(135deg,#6366f1,#8b5cf6); }
        .nav-active-users      { background: linear-gradient(135deg,#3b82f6,#06b6d4); }
        .nav-active-trans      { background: linear-gradient(135deg,#10b981,#34d399); }
        .nav-active-vouchers   { background: linear-gradient(135deg,#f59e0b,#f97316); }
        .nav-active-payment    { background: linear-gradient(135deg,#14b8a6,#06b6d4); }
        .nav-active-categories { background: linear-gradient(135deg,#ec4899,#f43f5e); }
        .nav-active-disputes   { background: linear-gradient(135deg,#ef4444,#f43f5e); }
        .nav-active-balances   { background: linear-gradient(135deg,#14b8a6,#10b981); }
        .nav-active-wallet     { background: linear-gradient(135deg,#8b5cf6,#6366f1); }
        .nav-active-banners    { background: linear-gradient(135deg,#6366f1,#4f46e5); }
        .nav-active-settings   { background: linear-gradient(135deg,#64748b,#475569); }
    </style>
</head>

<body class="antialiased">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside id="admin-sidebar" class="w-64 hidden md:flex flex-col fixed h-full z-20">
            <!-- Logo -->
            <div class="p-6 border-b border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="text-white font-black text-sm uppercase tracking-tight">Admin Panel</div>
                        <div class="text-white/50 text-[10px] font-mono uppercase tracking-widest">Techno Market</div>
                    </div>
                </div>
            </div>

            <nav class="flex-1 px-3 py-5 space-y-1 overflow-y-auto">
                @php
                    $navItems = [
                        ['route'=>'admin.dashboard',   'label'=>'Dashboard',          'color'=>'dashboard',  'icon'=>'M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z'],
                        ['route'=>'admin.users',        'label'=>'Manajemen User',     'color'=>'users',      'icon'=>'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
                        ['route'=>'admin.transactions', 'label'=>'Transaksi',          'color'=>'trans',      'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                        ['route'=>'admin.vouchers',     'label'=>'Voucher',            'color'=>'vouchers',   'icon'=>'M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z'],
                        ['route'=>'admin.payment_methods','label'=>'Metode Pembayaran','color'=>'payment',    'icon'=>'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                        ['route'=>'admin.categories',   'label'=>'Kategori',           'color'=>'categories', 'icon'=>'M4 6h16M4 12h16m-7 6h7'],
                        ['route'=>'admin.disputes.index','label'=>'Laporan Masalah',   'color'=>'disputes',   'icon'=>'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z'],
                        ['route'=>'admin.balances',     'label'=>'Saldo & Keuangan',   'color'=>'balances',   'icon'=>'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        ['route'=>'admin.wallet_logs',  'label'=>'Log Audit MeyPay',   'color'=>'wallet',     'icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                        ['route'=>'admin.ad_banners',   'label'=>'Banner Iklan',       'color'=>'banners',    'icon'=>'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z'],
                        ['route'=>'admin.settings',     'label'=>'Pengaturan',         'color'=>'settings',   'icon'=>'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                    ];
                @endphp

                @foreach($navItems as $item)
                    @php $isActive = request()->routeIs($item['route'] . '*'); @endphp
                    <a href="{{ route($item['route']) }}"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold transition-all duration-200
                            {{ $isActive
                                ? 'nav-active-'.$item['color'].' text-white shadow-lg'
                                : 'text-white/65 hover:text-white hover:bg-white/10' }}">
                        <svg class="w-4.5 h-4.5 flex-shrink-0 {{ $isActive ? 'text-white' : 'text-white/60' }}" style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon'] }}"/>
                        </svg>
                        <span class="tracking-tight">{{ $item['label'] }}</span>
                    </a>
                @endforeach

                <div class="border-t border-white/10 my-4"></div>

                <a href="{{ route('products.index') }}"
                    class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-semibold text-white/60 hover:text-white hover:bg-white/10 transition-all">
                    <svg class="w-4.5 h-4.5 flex-shrink-0" style="width:18px;height:18px" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                    <span>Kembali ke Toko</span>
                </a>
            </nav>

            <!-- Profile -->
            <div class="p-3 border-t border-white/10">
                <div class="flex items-center gap-3 p-3 rounded-xl bg-white/10">
                    <div class="w-9 h-9 rounded-full bg-gradient-to-br from-violet-400 to-indigo-500 flex items-center justify-center font-bold text-white text-sm flex-shrink-0">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[10px] text-white/50 uppercase tracking-wide">Admin Utama</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="w-8 h-8 rounded-lg bg-white/10 hover:bg-red-500/80 flex items-center justify-center transition-all" title="Keluar">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 md:ml-64 min-h-screen">
            <!-- Mobile Header -->
            <div class="md:hidden h-16 bg-white/80 backdrop-blur-xl border-b border-white/60 flex items-center justify-between px-5 sticky top-0 z-30 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-lg bg-gradient-to-br from-violet-500 to-indigo-600 flex items-center justify-center">
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="font-black text-slate-800 text-sm uppercase tracking-tight">Admin Panel</span>
                </div>
                <button class="w-9 h-9 rounded-xl bg-slate-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
            </div>

            <!-- Content Container -->
            <div class="p-5 md:p-8">
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center gap-3 shadow-sm">
                        <div class="w-8 h-8 rounded-full bg-emerald-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-emerald-800">{{ session('success') }}</span>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 flex items-center gap-3 shadow-sm">
                        <div class="w-8 h-8 rounded-full bg-red-500 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <span class="text-sm font-semibold text-red-800">{{ session('error') }}</span>
                    </div>
                @endif

                <div class="min-h-screen">
                    @yield('content')
                </div>

                <footer class="mt-16 pt-6 border-t border-indigo-100/60 flex flex-col md:flex-row justify-between items-center gap-3 text-[11px] text-slate-400 font-medium">
                    <div>Techno Market Admin &copy; {{ date('Y') }}</div>
                    <div class="flex gap-4">
                        <span>Status: <span class="text-emerald-500 font-semibold">Aktif</span></span>
                        <span>{{ now()->format('d M Y, H:i') }}</span>
                    </div>
                </footer>
            </div>
        </main>
    </div>

    @stack('scripts')
</body>
</html>
