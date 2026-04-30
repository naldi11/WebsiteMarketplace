@extends('layouts.admin')

@section('content')
    <div class="pt-0 pb-2">
        <!-- Header & Time Filter -->
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-black bg-clip-text text-transparent bg-gradient-to-r from-gray-900 via-indigo-800 to-gray-600 tracking-tight">CEO Command Center</h1>
                <p class="text-gray-500 mt-1 font-medium">Analitik makro platform dan ringkasan eksekutif secara riil-time.</p>
            </div>
            <div class="flex flex-wrap bg-white rounded-xl shadow-sm border border-gray-100 p-1.5 gap-1">
                @php $periods = ['today' => 'Hari Ini', 'week' => 'Minggu Ini', 'month' => 'Bulan Ini', 'year' => 'Tahun Ini', 'all' => 'Semua Waktu']; @endphp
                @foreach($periods as $key => $label)
                    <a href="{{ route('admin.dashboard', ['period' => $key]) }}"
                       class="px-4 py-2 text-sm font-bold rounded-lg transition-all duration-300 transform {{ $period === $key ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-200 scale-105' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Executive Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
            <!-- GMV -->
            <div class="relative overflow-hidden bg-gradient-to-br from-indigo-700 to-blue-900 p-6 rounded-2xl shadow-xl hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 text-white group">
                <div class="absolute -right-6 -top-6 text-white/10 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="relative z-10">
                    <div class="flex items-center gap-2 mb-1">
                        <div class="text-indigo-100 text-xs font-bold uppercase tracking-wider">Gross Merchandise Value (GMV)</div>
                    </div>
                    <div class="text-3xl font-black mb-1">Rp {{ number_format($stats['total_sales'], 0, ',', '.') }}</div>
                    <div class="text-xs text-indigo-100 mt-3 font-medium bg-white/20 px-3 py-1.5 rounded-lg w-max backdrop-blur-md">
                        Total perputaran uang kotor platform
                    </div>
                </div>
            </div>

            <!-- Net Revenue -->
            <div class="relative overflow-hidden bg-gradient-to-br from-emerald-600 to-teal-800 p-6 rounded-2xl shadow-xl hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 text-white group">
                <div class="absolute -right-6 -top-6 text-white/10 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
                <div class="relative z-10">
                    <div class="text-emerald-100 text-xs font-bold mb-1 uppercase tracking-wider">Net Platform Revenue</div>
                    <div class="text-3xl font-black mb-1">Rp {{ number_format($stats['platform_profit'], 0, ',', '.') }}</div>
                    <div class="text-xs text-emerald-100 mt-3 font-medium bg-white/20 px-3 py-1.5 rounded-lg w-max backdrop-blur-md">
                       Laba murni (Service + Admin Fee)
                    </div>
                </div>
            </div>

            <!-- Escrow Funds -->
            <div class="relative overflow-hidden bg-gradient-to-br from-slate-700 to-gray-900 p-6 rounded-2xl shadow-xl hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 text-white group">
                <div class="absolute -right-6 -top-6 text-white/10 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </div>
                <div class="relative z-10">
                    <div class="text-gray-300 text-xs font-bold mb-1 uppercase tracking-wider">Retained Seller Funds</div>
                    <div class="text-3xl font-black mb-1">Rp {{ number_format($stats['escrow_funds'], 0, ',', '.') }}</div>
                    <div class="text-xs text-gray-300 mt-3 font-medium bg-white/20 px-3 py-1.5 rounded-lg w-max backdrop-blur-md">
                        Total saldo penjual tertahan di platform
                    </div>
                </div>
            </div>

            <!-- Total User Base -->
            <a href="{{ route('admin.users') }}" class="relative overflow-hidden bg-gradient-to-br from-violet-600 to-purple-800 p-6 rounded-2xl shadow-xl hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 text-white cursor-pointer group hover:ring-4 hover:ring-purple-400/50 block">
                <div class="absolute -right-6 -top-6 text-white/10 group-hover:scale-110 transition-transform duration-500">
                    <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div class="relative z-10 h-full flex flex-col justify-between">
                    <div>
                        <div class="text-purple-200 text-xs font-bold mb-1 uppercase tracking-wider">Active User Base</div>
                        <div class="text-3xl font-black">{{ $stats['total_users'] }}</div>
                    </div>
                    <div class="mt-4 flex items-center text-xs font-bold text-white bg-white/20 py-2 px-4 rounded-lg w-max backdrop-blur-md group-hover:bg-white/30 transition">
                        Kelola Manajemen User <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </div>
                </div>
            </a>
        </div>

        <!-- The Analytic Arena -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Left: Line Chart (Financial Trends) -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h2 class="font-black text-gray-900 text-lg">Tren Keuangan <span class="text-indigo-600 font-bold ml-1 text-sm bg-indigo-50 px-2 py-1 rounded-md">{{ $periods[$period] }}</span></h2>
                </div>
                <div class="p-6 flex-1 w-full h-[350px]">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>

            <!-- Right: Doughnut Chart (Order Status) -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden flex flex-col">
                <div class="px-6 py-5 border-b border-gray-100">
                    <h2 class="font-black text-gray-900 text-lg">Komposisi Order</h2>
                </div>
                <div class="p-6 flex-1 w-full flex flex-col items-center justify-center relative min-h-[350px]">
                    @if(array_sum($orderStatus) > 0)
                        <canvas id="orderStatusChart" class="max-w-[280px] max-h-[280px]"></canvas>
                    @else
                        <div class="flex flex-col items-center justify-center text-gray-400">
                            <svg class="w-16 h-16 mb-4 text-gray-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            <span class="font-medium text-sm">Belum ada transaksi</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Leaderboards & Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Top Sellers -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <h2 class="font-black text-gray-900 text-lg flex items-center gap-2">
                        <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M11.3 1.046A1 1 0 0112 2v5h4a1 1 0 01.82 1.573l-7 10A1 1 0 018 18v-5H4a1 1 0 01-.82-1.573l7-10a1 1 0 011.12-.38z" clip-rule="evenodd"></path></svg>
                        Top Sellers
                    </h2>
                </div>
                <div class="p-0">
                    @forelse($topSellers as $seller)
                        <div class="flex items-center gap-4 p-4 border-b border-gray-50 hover:bg-gray-50/80 transition">
                            <div class="w-10 h-10 flex items-center justify-center bg-gradient-to-br {{ $loop->iteration == 1 ? 'from-yellow-400 to-yellow-600 text-white shadow-lg shadow-yellow-200' : ($loop->iteration == 2 ? 'from-gray-300 to-gray-500 text-white' : ($loop->iteration == 3 ? 'from-amber-600 to-amber-800 text-white' : 'bg-gray-100 text-gray-600')) }}  rounded-full font-black text-sm">
                                #{{ $loop->iteration }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-gray-900 truncate">{{ $seller->shop_name ?? $seller->name }}</h4>
                                <p class="text-xs font-medium text-gray-500">{{ $seller->completed_sales }} Transaksi Berhasil</p>
                            </div>
                            <div class="text-right">
                                <div class="text-sm font-black text-emerald-600 truncate">Rp {{ number_format($seller->total_earnings, 0, ',', '.') }}</div>
                                <div class="text-[10px] uppercase font-bold text-gray-400">Total Pencapaian</div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-400 text-sm">Tidak ada data Top Seller.</div>
                    @endforelse
                </div>
            </div>

            <!-- Top Categories -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-gray-50 to-white">
                    <h2 class="font-black text-gray-900 text-lg flex items-center gap-2">
                        <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Market Movers (Top Kategori)
                    </h2>
                </div>
                <div class="p-0">
                    @forelse($topCategories as $cat)
                        <div class="flex items-center gap-4 p-4 border-b border-gray-50 hover:bg-gray-50/80 transition">
                            <div class="w-10 h-10 bg-indigo-50 rounded-xl flex items-center justify-center p-2 shadow-sm border border-indigo-100/50">
                                @if($cat->icon)
                                    <img src="{{ Storage::url($cat->icon) }}" class="w-full h-full object-contain">
                                @else
                                    <span class="text-indigo-400 font-bold text-xs">{{ substr($cat->name, 0, 1) }}</span>
                                @endif
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-gray-900 truncate">{{ $cat->name }}</h4>
                            </div>
                            <div class="text-right">
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-gray-100 text-gray-800 border border-gray-200">
                                    {{ $cat->ordered_count }} Penjualan
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-400 text-sm">Tidak ada data Kategori.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Transactions -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
                <h2 class="font-black text-gray-900 text-lg">Live Order Stream</h2>
                <a href="{{ route('admin.transactions') }}" class="text-indigo-600 text-sm hover:text-indigo-800 font-bold transition flex items-center gap-1">Lihat Semua <svg class="w-4 h-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path></svg></a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50 text-gray-700 uppercase text-[11px] font-black tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Pembeli & Order ID</th>
                            <th class="px-6 py-4">Status & Waktu</th>
                            <th class="px-6 py-4 text-right">Nilai Transaksi (GMV)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($recentTransactions as $trx)
                        <tr class="hover:bg-gray-50/50 transition duration-150">
                            <td class="px-6 py-4">
                                <div class="font-bold text-gray-900 text-sm">{{ $trx->buyer->name }}</div>
                                <div class="text-xs text-gray-400 font-medium font-mono mt-0.5">#{{ $trx->id }} • {{ $trx->payment_method_code }}</div>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusDetails = [
                                        'waiting_payment' => ['bg-yellow-100 text-yellow-700 border-yellow-200', 'Menunggu Bayar'],
                                        'pending' => ['bg-orange-100 text-orange-700 border-orange-200', 'Verifikasi Admin'],
                                        'paid_verified' => ['bg-blue-100 text-blue-700 border-blue-200', 'Dibayar'],
                                        'processing' => ['bg-indigo-100 text-indigo-700 border-indigo-200', 'Diproses'],
                                        'shipped' => ['bg-purple-100 text-purple-700 border-purple-200', 'Dikirim'],
                                        'received' => ['bg-emerald-100 text-emerald-700 border-emerald-200', 'Diterima'],
                                        'completed' => ['bg-emerald-100 text-emerald-800 border-emerald-300', 'Selesai'],
                                        'cancelled' => ['bg-red-100 text-red-700 border-red-200', 'Dibatalkan'],
                                    ];
                                    $s = $statusDetails[$trx->status] ?? ['bg-gray-100 text-gray-600', ucfirst($trx->status)];
                                @endphp
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-black border {{ $s[0] }} shadow-sm">
                                    {{ $s[1] }}
                                </span>
                                <div class="text-[11px] text-gray-400 font-medium mt-1">{{ $trx->created_at->diffForHumans() }}</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="font-black text-gray-900">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-12 text-center text-gray-400 text-sm">Tidak ada pergerakan transaksi baru.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // --- 1. PERFORMANCE LINE CHART ---
        const ctxPerformance = document.getElementById('performanceChart');
        if(ctxPerformance) {
            const chartData = @json($chartData);
            
            new Chart(ctxPerformance.getContext('2d'), {
                type: 'line',
                data: {
                    labels: chartData.labels,
                    datasets: [
                        {
                            label: 'GMV (Penjualan Kotor) Rp',
                            data: chartData.sales,
                            borderColor: '#3730A3', // Indigo-800
                            backgroundColor: 'rgba(55, 48, 163, 0.08)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: '#3730A3',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: '#3730A3',
                            pointRadius: 3,
                            pointHoverRadius: 6
                        },
                        {
                            label: 'Net Platform Revenue (Rp)',
                            data: chartData.profit,
                            borderColor: '#059669', // Emerald-600
                            backgroundColor: 'rgba(5, 150, 105, 0.08)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.3,
                            pointBackgroundColor: '#059669',
                            pointBorderColor: '#fff',
                            pointHoverBackgroundColor: '#fff',
                            pointHoverBorderColor: '#059669',
                            pointRadius: 3,
                            pointHoverRadius: 6
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: {
                                usePointStyle: true,
                                boxWidth: 8,
                                padding: 20,
                                font: { family: "'Inter', sans-serif", size: 12, weight: 'bold' }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            titleFont: { family: "'Inter', sans-serif", size: 13, weight: 'bold' },
                            bodyFont: { family: "'Inter', sans-serif", size: 13, weight: '500' },
                            padding: 12,
                            cornerRadius: 12,
                            boxPadding: 6,
                            callbacks: {
                                label: function(context) {
                                    let label = context.dataset.label || '';
                                    if (label) { label += ': '; }
                                    if (context.parsed.y !== null) {
                                        label += new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(context.parsed.y);
                                    }
                                    return label;
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false, drawBorder: false },
                            ticks: { font: { family: "'Inter', sans-serif", weight: '600', color: '#6B7280' } }
                        },
                        y: {
                            grid: { color: '#f3f4f6', drawBorder: false },
                            ticks: {
                                font: { family: "'Inter', sans-serif", weight: '600', color: '#6B7280' },
                                maxTicksLimit: 6,
                                callback: function(value) {
                                    if(value >= 1000000) return 'Rp ' + (value / 1000000) + ' Jt';
                                    if(value >= 1000) return 'Rp ' + (value / 1000) + ' Rb';
                                    return 'Rp ' + value;
                                }
                            }
                        }
                    }
                }
            });
        }

        // --- 2. ORDER STATUS DOUGHNUT CHART ---
        const ctxDoughnut = document.getElementById('orderStatusChart');
        if(ctxDoughnut) {
            const orderStatusData = @json($orderStatus);
            
            new Chart(ctxDoughnut.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Selesai', 'Pending/Proses', 'Batal'],
                    datasets: [{
                        data: [orderStatusData.completed, orderStatusData.pending, orderStatusData.cancelled],
                        backgroundColor: [
                            '#10B981', // Emerald 500
                            '#F59E0B', // Amber 500
                            '#EF4444'  // Red 500
                        ],
                        borderWidth: 0,
                        hoverOffset: 4
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '75%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                usePointStyle: true,
                                padding: 20,
                                font: { family: "'Inter', sans-serif", size: 12, weight: 'bold' }
                            }
                        },
                        tooltip: {
                            backgroundColor: 'rgba(17, 24, 39, 0.95)',
                            titleFont: { family: "'Inter', sans-serif", size: 13, weight: 'bold' },
                            bodyFont: { family: "'Inter', sans-serif", size: 14, weight: 'bold' },
                            padding: 12,
                            cornerRadius: 8,
                            callbacks: {
                                label: function(context) {
                                    return ' ' + context.label + ': ' + context.parsed + ' Trx';
                                }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush