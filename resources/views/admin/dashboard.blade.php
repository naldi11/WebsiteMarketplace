@extends('layouts.admin')

@section('content')
    <div class="pt-0 pb-2">
        <!-- Header & Time Filter Minimalist -->
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6 mb-12">
            <div>
                <h1 class="text-4xl font-black tracking-tighter uppercase italic">Pusat Kendali</h1>
                <p class="text-gray-500 mt-1 font-mono text-xs uppercase tracking-widest">Analitik Platform Real-time & Status Sistem</p>
            </div>
            <div class="flex flex-wrap border-[3px] border-black p-1 gap-1 bg-white neo-brutalism">
                @php $periods = ['today' => 'Hari Ini', 'week' => 'Mingguan', 'month' => 'Bulanan', 'year' => 'Tahunan', 'all' => 'Semua Waktu']; @endphp
                @foreach($periods as $key => $label)
                    <a href="{{ route('admin.dashboard', ['period' => $key]) }}"
                       class="px-5 py-2 text-xs font-black uppercase transition-all {{ $period === $key ? 'bg-black text-white' : 'text-black hover:bg-gray-100' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Executive Summary Cards - Neo Brutalism -->
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-8 mb-12">
            <!-- GMV -->
            <div class="bg-white border-[3px] border-black p-8 neo-brutalism relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 opacity-[0.03] group-hover:opacity-[0.08] transition-all">
                    <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="relative z-10">
                    <div class="text-[10px] font-black uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 bg-black"></span> GMV (Kotor)
                    </div>
                    <div class="text-3xl font-black mb-2 tracking-tighter">Rp {{ number_format($stats['total_sales'], 0, ',', '.') }}</div>
                    <div class="text-[9px] font-mono text-gray-400 uppercase mt-4">Nilai Kotor Barang Platform</div>
                </div>
            </div>

            <!-- Net Revenue -->
            <div class="bg-white border-[3px] border-black p-8 neo-brutalism relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 opacity-[0.03] group-hover:opacity-[0.08] transition-all">
                    <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
                <div class="relative z-10">
                    <div class="text-[10px] font-black uppercase tracking-[0.2em] mb-4 flex items-center gap-2 text-black">
                        <span class="w-2 h-2 bg-black"></span> Pendapatan Bersih
                    </div>
                    <div class="text-3xl font-black mb-2 tracking-tighter">Rp {{ number_format($stats['platform_profit'], 0, ',', '.') }}</div>
                    <div class="text-[9px] font-mono text-gray-400 uppercase mt-4">Pendapatan Bersih (Biaya Layanan)</div>
                </div>
            </div>

            <!-- MeyPay Total Balance -->
            <div class="bg-white border-[3px] border-black p-8 neo-brutalism relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 opacity-[0.03] group-hover:opacity-[0.08] transition-all">
                    <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                </div>
                <div class="relative z-10">
                    <div class="text-[10px] font-black uppercase tracking-[0.2em] mb-4 flex items-center gap-2 text-black">
                        <span class="w-2 h-2 bg-black"></span> MeyPay Tersedia
                    </div>
                    <div class="text-3xl font-black mb-2 tracking-tighter">Rp {{ number_format($stats['total_wallet_balance'], 0, ',', '.') }}</div>
                    <div class="text-[9px] font-mono text-gray-400 uppercase mt-4">Beredar di Dompet Pengguna</div>
                </div>
            </div>

            <!-- MeyPay Pending/Escrow -->
            <div class="bg-white border-[3px] border-black p-8 neo-brutalism relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 opacity-[0.03] group-hover:opacity-[0.08] transition-all">
                    <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <div class="relative z-10">
                    <div class="text-[10px] font-black uppercase tracking-[0.2em] mb-4 flex items-center gap-2 text-black">
                        <span class="w-2 h-2 bg-black"></span> MeyPay Escrow
                    </div>
                    <div class="text-3xl font-black mb-2 tracking-tighter">Rp {{ number_format($stats['total_wallet_pending'], 0, ',', '.') }}</div>
                    <div class="text-[9px] font-mono text-gray-400 uppercase mt-4">Dana Ditahan untuk Pesanan Aktif</div>
                </div>
            </div>

            <!-- Users -->
            <div class="bg-black border-[3px] border-black p-8 neo-brutalism relative overflow-hidden group text-white">
                <div class="absolute -right-4 -top-4 opacity-10 group-hover:opacity-20 transition-all">
                    <svg class="w-32 h-32" fill="currentColor" viewBox="0 0 24 24"><path d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div class="relative z-10">
                    <div class="text-[10px] font-black uppercase tracking-[0.2em] mb-4 flex items-center gap-2">
                        <span class="w-2 h-2 bg-white"></span> Pengguna Aktif
                    </div>
                    <div class="text-3xl font-black mb-2 tracking-tighter">{{ number_format($stats['total_users'], 0, ',', '.') }}</div>
                    <div class="text-[9px] font-mono text-gray-400 uppercase mt-4">Total Pengguna Platform</div>
                </div>
            </div>
        </div>

        <!-- The Analytic Arena -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-12">
            <!-- Left: Line Chart -->
            <div class="lg:col-span-2 bg-white border-[3px] border-black neo-brutalism overflow-hidden">
                <div class="px-8 py-6 border-b-[3px] border-black bg-gray-50 flex justify-between items-center">
                    <h2 class="font-black text-xl uppercase tracking-tighter italic">Tren Keuangan</h2>
                    <span class="px-3 py-1 bg-black text-white text-[10px] font-black uppercase">{{ $periods[$period] }}</span>
                </div>
                <div class="p-8 h-[400px]">
                    <canvas id="performanceChart"></canvas>
                </div>
            </div>

            <!-- Right: Doughnut Chart -->
            <div class="bg-white border-[3px] border-black neo-brutalism overflow-hidden">
                <div class="px-8 py-6 border-b-[3px] border-black bg-gray-50">
                    <h2 class="font-black text-xl uppercase tracking-tighter italic">Distribusi Pesanan</h2>
                </div>
                <div class="p-8 h-[400px] flex flex-col items-center justify-center">
                    @if(array_sum($orderStatus) > 0)
                        <canvas id="orderStatusChart"></canvas>
                    @else
                        <div class="text-center">
                            <div class="w-16 h-16 border-[3px] border-black mx-auto mb-4 flex items-center justify-center">
                                <span class="font-black">?</span>
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
            <div class="bg-white border-[3px] border-black neo-brutalism overflow-hidden">
                <div class="px-8 py-6 border-b-[3px] border-black bg-black text-white">
                    <h2 class="font-black text-xl uppercase tracking-tighter flex items-center gap-3 italic">
                        <span class="w-3 h-3 bg-white"></span> Penjual Terbaik
                    </h2>
                </div>
                <div class="divide-y-2 divide-black">
                    @forelse($topSellers as $seller)
                        <div class="flex items-center gap-6 p-6 hover:bg-gray-50 transition-all group">
                            <div class="w-12 h-12 border-[3px] border-black flex items-center justify-center font-black group-hover:bg-black group-hover:text-white transition-all italic text-xl">
                                {{ $loop->iteration }}
                            </div>
                            <div class="flex-1">
                                <h4 class="font-black uppercase text-sm tracking-tight">{{ $seller->shop_name ?? $seller->name }}</h4>
                                <p class="text-[10px] font-mono text-gray-500 uppercase">{{ $seller->completed_sales }} Penjualan Berhasil</p>
                            </div>
                            <div class="text-right">
                                <div class="font-black text-lg">Rp {{ number_format($seller->total_earnings, 0, ',', '.') }}</div>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center text-gray-400 font-black uppercase text-xs">Belum Ada Data</div>
                    @endforelse
                </div>
            </div>

            <!-- Top Categories (Market Movers) -->
            <div class="bg-white border-[3px] border-black neo-brutalism overflow-hidden">
                <div class="px-8 py-6 border-b-[3px] border-black bg-black text-white">
                    <h2 class="font-black text-xl uppercase tracking-tighter flex items-center gap-3 italic">
                        <span class="w-3 h-3 bg-white"></span> Kategori Terlaris
                    </h2>
                </div>
                <div class="divide-y-2 divide-black">
                    @forelse($topCategories as $cat)
                        <div class="flex items-center gap-6 p-6 hover:bg-gray-50 transition-all group">
                            <div class="w-12 h-12 border-[3px] border-black flex items-center justify-center font-black">
                                @if($cat->icon)
                                    <img src="{{ Storage::url($cat->icon) }}" class="w-6 h-6 object-contain grayscale">
                                @else
                                    {{ substr($cat->name, 0, 1) }}
                                @endif
                            </div>
                            <div class="flex-1">
                                <h4 class="font-black uppercase text-sm tracking-tight">{{ $cat->name }}</h4>
                            </div>
                            <div class="text-right">
                                <span class="px-3 py-1 border-2 border-black bg-white text-[9px] font-black uppercase italic">
                                    {{ $cat->ordered_count }} Penjualan
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="p-12 text-center text-gray-400 font-black uppercase text-xs">Tidak Ada Data</div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Recent Transactions (Live Order Stream) -->
        <div class="bg-white border-[3px] border-black neo-brutalism overflow-hidden mb-8">
            <div class="px-8 py-6 border-b-[3px] border-black bg-black text-white flex justify-between items-center">
                <h2 class="font-black text-xl uppercase tracking-tighter flex items-center gap-3 italic">
                    <span class="w-3 h-3 bg-white"></span> Transaksi Terkini
                </h2>
                <a href="{{ route('admin.transactions') }}" class="text-[10px] font-black uppercase underline decoration-2 hover:no-underline transition-all">Lihat Semua Pesanan</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-100 border-b-2 border-black text-black font-black uppercase">
                        <tr>
                            <th class="px-6 py-4">Pembeli</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-right">Nilai GMV</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-gray-100 font-bold">
                        @forelse($recentTransactions as $trx)
                        <tr class="hover:bg-gray-50 transition-all">
                            <td class="px-6 py-6">
                                <div class="uppercase text-sm">{{ $trx->buyer->name }}</div>
                                <div class="text-[9px] font-mono text-gray-400 uppercase mt-1">#{{ $trx->id }} • {{ $trx->payment_method_code }}</div>
                            </td>
                            <td class="px-6 py-6 text-center">
                                <span class="px-3 py-1 border-2 border-black bg-white text-[9px] font-black uppercase inline-block">
                                    {{ str_replace('_', ' ', $trx->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-6 text-right font-black text-sm">
                                Rp {{ number_format($trx->total_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="3" class="px-6 py-20 text-center text-gray-400 font-black uppercase italic tracking-widest">Buffer Empty</td>
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
        Chart.defaults.color = '#000';

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
                            borderColor: '#000',
                            backgroundColor: 'rgba(0,0,0,0.05)',
                            borderWidth: 4,
                            fill: true,
                            tension: 0,
                            pointBackgroundColor: '#000',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                            pointRadius: 5,
                            pointHoverRadius: 8
                        },
                        {
                            label: 'Pendapatan Bersih',
                            data: chartData.profit,
                            borderColor: '#999',
                            backgroundColor: 'rgba(153,153,153,0.05)',
                            borderWidth: 4,
                            fill: true,
                            tension: 0,
                            pointBackgroundColor: '#999',
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
                        x: { border: { width: 3, color: '#000' }, grid: { display: false } },
                        y: { 
                            border: { width: 3, color: '#000' },
                            grid: { color: '#eee' },
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
                        backgroundColor: ['#000', '#666', '#ccc'],
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