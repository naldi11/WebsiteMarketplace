@extends('layouts.admin')

@section('content')
    <div class="py-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Manajemen Saldo</h1>

        <!-- Platform Earnings Summary -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Pendapatan Platform</p>
                        <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($platformEarnings, 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">Dari biaya layanan 10%</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-green-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Penjual Aktif</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $sellers->count() }}</p>
                        <p class="text-xs text-gray-400 mt-1">Penjual dengan transaksi selesai</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-amber-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Rata-rata Pendapatan/Transaksi</p>
                        <p class="text-2xl font-bold text-gray-900">Rp
                            {{ number_format($avgEarningsPerTx ?? 0, 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">Berdasarkan transaksi selesai</p>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-red-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Saldo Ditarik</p>
                        <p class="text-2xl font-bold text-gray-900">Rp
                            {{ number_format($totalPlatformWithdrawn ?? 0, 0, ',', '.') }}
                        </p>
                        <p class="text-xs text-gray-400 mt-1">Oleh semua penjual</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Earnings Breakdown -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-8">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Pendapatan Platform Bulanan ({{ now()->year }})</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-200">
                            @php
                                $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                            @endphp
                            @foreach($bulan as $b)
                                <th class="py-3 px-2 text-center font-semibold text-gray-600">{{ $b }}</th>
                            @endforeach
                            <th class="py-3 px-3 text-center font-bold text-indigo-600">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            @for($i = 1; $i <= 12; $i++)
                                <td
                                    class="py-3 px-2 text-center {{ isset($monthlyEarnings[$i]) ? 'font-semibold text-gray-900' : 'text-gray-400' }}">
                                    Rp {{ number_format($monthlyEarnings[$i] ?? 0, 0, ',', '.') }}
                                </td>
                            @endfor
                            <td class="py-3 px-3 text-center font-bold text-indigo-600">
                                Rp {{ number_format(array_sum($monthlyEarnings), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Seller Balance Table -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-bold text-gray-900 mb-4">Saldo Penjual</h2>
            @if($sellers->isEmpty())
                <div class="text-center py-12 text-gray-400">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4">
                        </path>
                    </svg>
                    <p class="text-lg font-medium">Belum ada transaksi selesai</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wide">
                                <th class="text-left py-3 px-4 rounded-l-lg">#</th>
                                <th class="text-left py-3 px-4">Penjual</th>
                                <th class="text-right py-3 px-4">Total Penjualan</th>
                                <th class="text-right py-3 px-4">Biaya Layanan</th>
                                <th class="text-right py-3 px-4">Total Ditarik</th>
                                <th class="text-right py-3 px-4 rounded-r-lg">Pendapatan Bersih</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sellers as $index => $seller)
                                <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                    <td class="py-4 px-4 text-gray-400">{{ $index + 1 }}</td>
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-3">
                                            <img src="{{ $seller->avatar ? Storage::url($seller->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($seller->name) }}"
                                                class="w-8 h-8 rounded-full bg-gray-100">
                                            <div>
                                                <p class="font-semibold text-gray-900">{{ $seller->name }}</p>
                                                <p class="text-xs text-gray-500">{{ $seller->email }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-right font-medium text-gray-700">
                                        Rp {{ number_format($seller->total_sales ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 text-right text-red-500">
                                        - Rp {{ number_format($seller->total_service_fees ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 text-right text-amber-600">
                                        Rp {{ number_format($seller->total_withdrawn ?? 0, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-bold text-green-600">
                                        Rp {{ number_format($seller->seller_earnings ?? 0, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-gray-50 font-bold">
                                <td colspan="2" class="py-3 px-4 rounded-l-lg">Total</td>
                                <td class="py-3 px-4 text-right text-gray-900">
                                    Rp {{ number_format($sellers->sum('total_sales'), 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right text-red-500">
                                    - Rp {{ number_format($sellers->sum('total_service_fees'), 0, ',', '.') }}
                                </td>
                                <td class="py-3 px-4 text-right text-green-600 rounded-r-lg">
                                    Rp {{ number_format($sellers->sum('seller_earnings'), 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endif
            <!-- Recent Transactions Breakdown -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mt-8">
                <h2 class="text-lg font-bold text-gray-900 mb-4">Riwayat Pendapatan per Transaksi</h2>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wide">
                                <th class="text-left py-3 px-4 rounded-l-lg">ID</th>
                                <th class="text-left py-3 px-4">Penjual</th>
                                <th class="text-left py-3 px-4">Tgl Selesai</th>
                                <th class="text-right py-3 px-4">Total Bayar</th>
                                <th class="text-right py-3 px-4">Biaya Layanan</th>
                                <th class="text-right py-3 px-4 rounded-r-lg">Diterima Penjual</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($latestTransactions as $tx)
                                <tr class="border-b border-gray-50 hover:bg-gray-50 transition">
                                    <td class="py-4 px-4 font-mono text-gray-500">#{{ $tx->id }}</td>
                                    <td class="py-4 px-4">
                                        <div class="font-medium text-gray-900">{{ $tx->seller->name ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">Pembeli: {{ $tx->buyer->name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="py-4 px-4 text-gray-600">
                                        {{ $tx->updated_at->format('d M Y H:i') }}
                                    </td>
                                    <td class="py-4 px-4 text-right text-gray-700">
                                        Rp {{ number_format($tx->total_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 text-right text-red-500">
                                        - {{ number_format($tx->service_fee, 0, ',', '.') }}
                                    </td>
                                    <td class="py-4 px-4 text-right font-bold text-green-600">
                                        Rp {{ number_format($tx->seller_amount, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
@endsection