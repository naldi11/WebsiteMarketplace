@extends('layouts.admin')

@section('content')
    <div class="pt-0 pb-2">
        <!-- Header & Time Filter -->
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6 mb-12">
            <div>
                <h1 class="text-4xl font-black tracking-tighter uppercase italic text-slate-800">Pusat Kendali</h1>
                <p class="text-slate-500 mt-1 font-mono text-xs uppercase tracking-widest">Analitik Platform Real-time & Status Sistem</p>
            </div>
            <div class="flex flex-wrap border border-indigo-100 p-1 gap-1 bg-white/40 rounded-xl shadow-lg">
                @php $periods = ['today' => 'Hari Ini', 'week' => 'Mingguan', 'month' => 'Bulanan', 'year' => 'Tahunan', 'all' => 'Semua Waktu']; @endphp
                @foreach($periods as $key => $label)
                    <a href="{{ route('admin.dashboard', ['period' => $key]) }}"
                       class="px-5 py-2 text-xs font-black uppercase transition-all rounded-lg {{ $period === $key ? 'bg-gradient-to-r from-violet-500 to-purple-600 text-white' : 'text-slate-800 hover:bg-white/60' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Executive Summary Cards - Glassmorphism -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-8 mb-12">
            <!-- GMV -->
            <div class="bg-gradient-to-br from-violet-500 to-purple-600 text-white rounded-2xl p-8 shadow-lg shadow-violet-200 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 opacity-10 group-hover:opacity-20 transition-all">
                    <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="relative z-10">
                    <div class="text-[10px] font-black uppercase tracking-[0.2em] mb-4 flex items-center gap-2 text-white/80">
                        <span class="w-2 h-2 bg-white/80 rounded-full"></span> GMV (Kotor)
                    </div>
                    <div class="text-white font-black text-3xl mb-2 tracking-tighter">Rp {{ number_format($stats['total_sales'], 0, ',', '.') }}</div>
                    <div class="text-[9px] font-mono text-white/80 uppercase mt-4">Nilai Kotor Barang Platform</div>
                </div>
            </div>

            <!-- Net Revenue -->
            <div class="bg-gradient-to-br from-blue-500 to-cyan-500 text-white rounded-2xl p-8 shadow-lg shadow-blue-200 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 opacity-10 group-hover:opacity-20 transition-all">
                    <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
                <div class="relative z-10">
                    <div class="text-[10px] font-black uppercase tracking-[0.2em] mb-4 flex items-center gap-2 text-white/80">
                        <span class="w-2 h-2 bg-white/80 rounded-full"></span> Pendapatan Bersih
                    </div>
                    <div class="text-white font-black text-3xl mb-2 tracking-tighter">Rp {{ number_format($stats['platform_profit'], 0, ',', '.') }}</div>
                    <div class="text-[9px] font-mono text-white/80 uppercase mt-4">Pendapatan Bersih (Biaya Layanan)</div>
                </div>
            </div>

            <!-- Escrow Balance -->
            <div class="bg-gradient-to-br from-emerald-500 to-teal-500 text-white rounded-2xl p-8 shadow-lg shadow-emerald-200 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 opacity-10 group-hover:opacity-20 transition-all">
                    <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <div class="relative z-10">
                    <div class="text-[10px] font-black uppercase tracking-[0.2em] mb-4 flex items-center gap-2 text-white/80">
                        <span class="w-2 h-2 bg-white/80 rounded-full"></span> Dana Escrow (Pending)
                    </div>
                    <div class="text-white font-black text-3xl mb-2 tracking-tighter">Rp {{ number_format($stats['total_escrow_pending'], 0, ',', '.') }}</div>
                    <div class="text-[9px] font-mono text-white/80 uppercase mt-4">Dana Ditahan untuk Pesanan Aktif</div>
                </div>
            </div>

            <!-- Pending Refunds -->
            <div class="bg-gradient-to-br from-amber-500 to-orange-500 text-white rounded-2xl p-8 shadow-lg shadow-amber-200 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 opacity-10 group-hover:opacity-20 transition-all">
                    <svg class="w-32 h-32" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-4 7h4m-4 4h4m-9 0h.01M9 12h.01"></path></svg>
                </div>
                <div class="relative z-10">
                    <div class="text-[10px] font-black uppercase tracking-[0.2em] mb-4 flex items-center gap-2 text-white/80">
                        <span class="w-2 h-2 bg-white/80 rounded-full"></span> Refund Pending
                    </div>
                    <div class="text-white font-black text-3xl mb-2 tracking-tighter">{{ number_format($stats['pending_refunds'], 0, ',', '.') }} Kasus</div>
                    <div class="text-[9px] font-mono text-white/80 uppercase mt-4">Pengembalian Dana Menunggu Transfer</div>
                </div>
            </div>

            <!-- Users -->
            <div class="bg-gradient-to-br from-pink-500 to-rose-500 text-white rounded-2xl p-8 shadow-lg shadow-pink-200 relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 opacity-10 group-hover:opacity-20 transition-all">
                    <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div class="relative z-10">
                    <div class="text-[10px] font-black uppercase tracking-[0.2em] mb-4 flex items-center gap-2 text-white/80">
                        <span class="w-2 h-2 bg-white/80 rounded-full"></span> Pengguna Aktif
                    </div>
                    <div class="text-white font-black text-3xl mb-2 tracking-tighter">{{ number_format($stats['total_users'], 0, ',', '.') }}</div>
                    <div class="text-[9px] font-mono text-white/80 uppercase mt-4">Total Pengguna Platform</div>
                </div>
            </div>
        </div>

        <!-- The Analytic Arena -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <!-- Left: Line Chart -->
            <div class="lg:col-span-2 glass-card overflow-hidden">
                <div class="gradient-header-purple px-8 py-6 text-white flex justify-between items-center">
                    <h2 class="font-black text-xl uppercase tracking-tighter italic">Tren Keuangan</h2>
                    <span class="px-3 py-1 bg-white/20 text-white text-[10px] font-black uppercase rounded-lg">{{ $periods[$period] }}</span>
                </div>
                <div class="p-8 h-[400px]">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>

            <!-- Right: Doughnut Chart -->
            <div class="glass-card overflow-hidden">
                <div class="gradient-header-purple px-8 py-6 text-white">
                    <h2 class="font-black text-xl uppercase tracking-tighter italic">Distribusi Pesanan</h2>
                </div>
                <div class="p-8 h-[400px] flex flex-col items-center justify-center">
                    @if(array_sum($orderStatus) > 0)
                        <canvas id="orderStatusChart"></canvas>
                    @else
                        <div class="text-center">
                            <div class="w-16 h-16 border border-indigo-100 mx-auto mb-4 flex items-center justify-center rounded-xl">
                                <span class="font-black text-slate-400">?</span>
                            </div>
                            <p class="text-[10px] font-black uppercase opacity-40">Tidak Ada Data</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Rankings & Logs -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-12">
            <!-- Top Sellers -->
            <div class="glass-card overflow-hidden">
                <div class="gradient-header-purple px-8 py-6 text-white">
                    <h2 class="font-black text-xl uppercase tracking-tighter flex items-center gap-3 italic">
                        <span class="w-3 h-3 bg-white/80 rounded-full"></span> Penjual Terbaik
                    </h2>
                </div>
                <div class="divide-y divide-indigo-50">
                    @forelse($topSellers as $seller)
                        <div class="flex items-center gap-6 p-6 hover:bg-white/40 transition-all group">
                            <div class="w-12 h-12 border border-indigo-100 flex items-center justify-center font-black group-hover:bg-gradient-to-br group-hover:from-violet-500 group-hover:to-purple-600 group-hover:text-white transition-all italic text-xl rounded-xl text-slate-800">
                                {{ $loop->iteration }}
                            </div>
                            <div class="flex-1">
                                <h4 class="font-black uppercase text-sm tracking-tight text-slate-800">{{ $seller->shop_name ?? $seller->name }}</h4>
                                <p class="text-[10px] font-mono text-slate-500 uppercase">{{ $seller->completed_sales }} Penjualan Berhasil</p>
                            </div>
                            <div class="text-right">
                                <div class="font-black text-lg text-slate-800">Rp {{ number_format($seller->total_earnings, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center text-slate-400 font-black uppercase text-xs">Belum Ada Data</div>
                    @endforelse
                </div>
            </div>

            <!-- Top Categories (Market Movers) -->
            <div class="glass-card overflow-hidden">
                <div class="gradient-header-purple px-8 py-6 text-white">
                    <h2 class="font-black text-xl uppercase tracking-tighter flex items-center gap-3 italic">
                        <span class="w-3 h-3 bg-white/80 rounded-full"></span> Kategori Terlaris
                    </h2>
                </div>
                <div class="divide-y divide-indigo-50">
                    @forelse($topCategories as $cat)
                        <div class="flex items-center gap-6 p-6 hover:bg-white/40 transition-all group">
                            <div class="w-12 h-12 border border-indigo-100 flex items-center justify-center font-black rounded-xl text-slate-800">
                                @if($cat->icon)
                                    <img src="{{ Storage::url($cat->icon) }}" class="w-6 h-6 object-contain grayscale">
                                @else
                                    {{ substr($cat->name, 0, 1) }}
                                @endif
                            </div>
                            <div class="flex-1">
                                <h4 class="font-black uppercase text-sm tracking-tight text-slate-800">{{ $cat->name }}</h4>
                            </div>
                            <div class="text-right">
                                <span class="px-3 py-1 border border-indigo-100 bg-white/60 text-[9px] font-black uppercase italic rounded-lg text-slate-800">
                                    {{ $cat->ordered_count }} Penjualan
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center text-slate-400 font-black uppercase text-xs">Tidak Ada Data</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Transactions (Live Order Stream) -->
        <div class="glass-card overflow-hidden mb-8">
            <div class="gradient-header-purple px-8 py-6 text-white flex justify-between items-center">
                <h2 class="font-black text-xl uppercase tracking-tighter flex items-center gap-3 italic">
                    <span class="w-3 h-3 bg-white/80 rounded-full"></span> Transaksi Terkini
                </h2>
                <a href="{{ route('admin.transactions') }}" class="text-[10px] font-black uppercase underline decoration-2 hover:no-underline transition-all text-white/90">Lihat Semua Pesanan</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="glass-table-header text-slate-600 font-black uppercase">
                        <tr>
                            <th class="px-6 py-4">Pembeli</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Nilai GMV</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-indigo-50 font-bold">
                        @forelse($recentTransactions as $trx)
                        <tr class="hover:bg-white/40 transition-all">
                            <td class="px-6 py-6">
                                <div class="uppercase text-sm text-slate-800">{{ $trx->buyer->name }}</div>
                                <div class="text-[9px] font-mono text-slate-400 uppercase mt-1">#{{ $trx->id }} • {{ $trx->payment_method_code }}</div>
                            </td>
                            <td class="px-6 py-6 text-center">
                                <span class="px-3 py-1 border border-indigo-100 bg-white/60 text-[9px] font-black uppercase inline-block rounded-lg text-slate-800">
                                    {{ str_replace('_', ' ', $trx->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-6 text-right font-black text-sm text-slate-800">
                                Rp {{ number_format($trx->total_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-20 text-center text-slate-400 font-black uppercase italic tracking-widest">Belum Ada Transaksi</td>
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
        Chart.defaults.font.family = "'Space Grotesk', sans-serif";
        Chart.defaults.font.weight = 'bold';
        Chart.defaults.color = '#475569';

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
                            label: 'GMV (Kotor)',
                            data: chartData.sales,
                            borderColor: '#7c3aed',
                            backgroundColor: 'rgba(124,58,237,0.08)',
                            borderWidth: 4,
                            fill: true,
                            tension: 0,
                            pointBackgroundColor: '#7c3aed',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 8
                        },
                        {
                            label: 'Pendapatan Bersih',
                            data: chartData.profit,
                            borderColor: '#06b6d4',
                            backgroundColor: 'rgba(6,182,212,0.05)',
                            borderWidth: 4,
                            fill: true,
                            tension: 0,
                            pointBackgroundColor: '#06b6d4',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 8
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                            align: 'end',
                            labels: {
                                boxWidth: 12,
                                boxHeight: 12,
                                padding: 25,
                                font: { size: 11, weight: '900' }
                            }
                        }
                    },
                    scales: {
                        x: { border: { width: 1, color: '#e0e7ff' }, grid: { display: false } },
                        y: {
                            border: { width: 1, color: '#e0e7ff' },
                            grid: { color: '#f1f5f9' },
                            ticks: {
                                callback: function(value) {
                                    if(value >= 1000000) return (value / 1000000) + 'M';
                                    if(value >= 1000) return (value / 1000) + 'K';
                                    return value;
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
                    labels: ['Selesai', 'Menunggu', 'Gagal'],
                    datasets: [{
                        data: [orderStatusData.completed, orderStatusData.pending, orderStatusData.cancelled],
                        backgroundColor: ['#7c3aed', '#06b6d4', '#e2e8f0'],
                        borderWidth: 3,
                        borderColor: '#fff',
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                            labels: {
                                boxWidth: 12,
                                boxHeight: 12,
                                padding: 25,
                                font: { size: 10, weight: '900' }
                            }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
