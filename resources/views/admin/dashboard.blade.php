@extends('layouts.admin')

@section('content')
    <div class="py-8">
        <div class="flex justify-between items-center mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 tracking-tight">Dashboard Overview</h1>
                <p class="text-gray-500 mt-1">Ringkasan aktivitas toko Anda hari ini.</p>
            </div>
            <div class="text-right">
                <p class="text-sm font-medium text-gray-500">{{ now()->isoFormat('dddd, D MMMM Y') }}</p>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-indigo-50 text-indigo-600 rounded-xl">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-gray-500 text-sm font-medium">Omset Penjualan</div>
                        <div class="text-2xl font-bold text-gray-900">Rp
                            {{ number_format($stats['total_sales'], 0, ',', '.') }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-blue-50 text-blue-600 rounded-xl">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-gray-500 text-sm font-medium">Total User</div>
                        <div class="text-2xl font-bold text-gray-900">{{ $stats['total_users'] }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-orange-50 text-orange-600 rounded-xl">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-gray-500 text-sm font-medium">Menunggu Konfirmasi</div>
                        <div class="text-2xl font-bold text-gray-900">{{ $stats['pending_transactions'] }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-green-50 text-green-600 rounded-xl">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                        </svg>
                    </div>
                    <div>
                        <div class="text-gray-500 text-sm font-medium">Produk Aktif</div>
                        <div class="text-2xl font-bold text-gray-900">{{ $stats['active_products'] }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Left Column (Transactions) -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Recent Transactions -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100 flex justify-between items-center">
                        <h2 class="font-bold text-gray-900 text-lg">Transaksi Terbaru</h2>
                        <a href="{{ route('admin.transactions') }}"
                            class="text-indigo-600 text-sm hover:underline font-medium">Lihat Semua</a>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm text-gray-600">
                            <thead class="bg-gray-50 text-gray-700 uppercase text-xs font-semibold">
                                <tr>
                                    <th class="px-6 py-3">User</th>
                                    <th class="px-6 py-3">Total</th>
                                    <th class="px-6 py-3">Status</th>
                                    <th class="px-6 py-3 text-right">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50">
                                @forelse($recentTransactions as $trx)
                                                        <tr class="hover:bg-gray-50 transition">
                                                            <td class="px-6 py-4 font-medium text-gray-900">
                                                                {{ $trx->buyer->name }}
                                                                <div class="text-xs text-xs text-gray-400 font-normal">Order #{{ $trx->id }}</div>
                                                            </td>
                                                            <td class="px-6 py-4 font-bold">Rp {{ number_format($trx->total_amount, 0, ',', '.') }}</td>
                                                            <td class="px-6 py-4">
                                                                <span
                                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                                            {{ $trx->status == 'completed' ? 'bg-green-100 text-green-800' :
                                    ($trx->status == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                                                    {{ ucfirst($trx->status) }}
                                                                </span>
                                                            </td>
                                                            <td class="px-6 py-4 text-right">
                                                                @if($trx->status == 'pending')
                                                                    <form action="{{ route('admin.verify', $trx) }}" method="POST">
                                                                        @csrf
                                                                        <button
                                                                            class="text-indigo-600 hover:text-indigo-900 font-bold text-xs bg-indigo-50 px-3 py-1.5 rounded-lg transition">Verifikasi</button>
                                                                    </form>
                                                                @else
                                                                    <span class="text-gray-400 text-xs">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="px-6 py-8 text-center text-gray-400">Belum ada transaksi.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right Column (Alerts & Top Products) -->
            <div class="space-y-8">
                <!-- Low Stock Alert -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-50 bg-red-50 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                            </path>
                        </svg>
                        <h2 class="font-bold text-red-900 text-lg">Stok Menipis</h2>
                    </div>
                    <div class="p-0">
                        @forelse($lowStockProducts as $p)
                            <div class="flex items-center gap-4 p-4 border-b border-gray-50 last:border-0 hover:bg-gray-50">
                                <img src="{{ Storage::url($p->image) }}" class="w-12 h-12 rounded-lg object-cover bg-gray-100">
                                <div class="flex-1">
                                    <h4 class="text-sm font-bold text-gray-900 line-clamp-1">{{ $p->name }}</h4>
                                    <p class="text-xs text-red-500 font-medium">Sisa Stok: {{ $p->stock }}</p>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-gray-400 text-sm">Semua stok aman.</div>
                        @endforelse
                    </div>
                </div>

                <!-- Top Products -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-100">
                        <h2 class="font-bold text-gray-900 text-lg">Produk Terlaris</h2>
                    </div>
                    <div class="p-0">
                        @forelse($topProducts as $p)
                            <div class="flex items-center gap-4 p-4 border-b border-gray-50 last:border-0 hover:bg-gray-50">
                                <div
                                    class="w-8 h-8 flex items-center justify-center bg-gray-100 rounded-full font-bold text-xs text-gray-600">
                                    #{{ $loop->iteration }}</div>
                                <div class="flex-1">
                                    <h4 class="text-sm font-bold text-gray-900 line-clamp-1">{{ $p->name }}</h4>
                                    <p class="text-xs text-gray-500">{{ $p->total_sold ?? 0 }} Terjual</p>
                                </div>
                            </div>
                        @empty
                            <div class="p-6 text-center text-gray-400 text-sm">Belum ada data penjualan.</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection