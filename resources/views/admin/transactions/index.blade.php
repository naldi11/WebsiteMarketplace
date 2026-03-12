@extends('layouts.admin')

@section('title', 'Manajemen Transaksi')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Manajemen Transaksi</h1>
                <p class="text-gray-600 mt-2">Pantau dan kelola semua transaksi di platform.</p>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="mb-6 border-b border-gray-200 bg-white rounded-t-xl shadow-sm px-4">
            <ul class="flex flex-wrap -mb-px text-sm font-medium text-center text-gray-500">
                <li class="mr-2">
                    <a href="{{ route('admin.transactions', ['tab' => 'all']) }}"
                        class="inline-flex items-center justify-center p-4 border-b-2 rounded-t-lg group {{ $tab === 'all' ? 'text-orange-600 border-orange-600 active' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}">
                        <i class="fas fa-list-ul mr-2"></i>Semua Pesanan
                        <span
                            class="ml-2 bg-gray-100 text-gray-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">{{ $counts['all'] }}</span>
                    </a>
                </li>
                <li class="mr-2">
                    <a href="{{ route('admin.transactions', ['tab' => 'payment']) }}"
                        class="inline-flex items-center justify-center p-4 border-b-2 rounded-t-lg group {{ $tab === 'payment' ? 'text-orange-600 border-orange-600 active' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}">
                        <i class="fas fa-wallet mr-2"></i>Verifikasi Bayar
                        <span
                            class="ml-2 bg-orange-100 text-orange-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">{{ $counts['payment'] }}</span>
                    </a>
                </li>
                <li class="mr-2">
                    <a href="{{ route('admin.transactions', ['tab' => 'release']) }}"
                        class="inline-flex items-center justify-center p-4 border-b-2 rounded-t-lg group {{ $tab === 'release' ? 'text-orange-600 border-orange-600 active' : 'border-transparent hover:text-gray-600 hover:border-gray-300' }}">
                        <i class="fas fa-hand-holding-usd mr-2"></i>Pelepasan Dana
                        <span
                            class="ml-2 bg-green-100 text-green-800 text-xs font-semibold px-2.5 py-0.5 rounded-full">{{ $counts['release'] }}</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- Transaction Table -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-50 text-gray-700 font-semibold uppercase text-xs tracking-wider">
                        <tr>
                            <th class="px-6 py-4 border-b">ID / Invoice</th>
                            <th class="px-6 py-4 border-b">Pembeli & Penjual</th>
                            <th class="px-6 py-4 border-b">Total</th>
                            <th class="px-6 py-4 border-b">Status</th>
                            <th class="px-6 py-4 border-b">Tanggal</th>
                            <th class="px-6 py-4 border-b text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($transactions as $tx)
                            <tr class="hover:bg-gray-50 transition-colors duration-200">
                                <td class="px-6 py-4">
                                    <span class="font-bold text-gray-800">#{{ $tx->id }}</span>
                                    <div class="text-xs text-gray-500 mt-1">INV-{{ str_pad($tx->id, 5, '0', STR_PAD_LEFT) }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col">
                                        <span class="text-sm font-medium text-gray-900"><i
                                                class="fas fa-user text-gray-400 mr-1"></i> B: {{ $tx->buyer->name }}</span>
                                        <span class="text-xs text-gray-500 mt-1"><i class="fas fa-store text-gray-400 mr-1"></i>
                                            S: {{ $tx->seller->name ?? '-' }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-bold text-gray-900">Rp
                                        {{ number_format($tx->total_amount, 0, ',', '.') }}</div>
                                    <div class="text-xs text-gray-500">{{ $tx->payment_method_code }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusClasses = [
                                            'pending' => 'bg-orange-100 text-orange-700',
                                            'paid_verified' => 'bg-blue-100 text-blue-700',
                                            'processing' => 'bg-indigo-100 text-indigo-700',
                                            'shipped' => 'bg-purple-100 text-purple-700',
                                            'received' => 'bg-teal-100 text-teal-700',
                                            'completed' => 'bg-green-100 text-green-700',
                                            'payment_rejected' => 'bg-red-100 text-red-700',
                                            'cancelled' => 'bg-red-100 text-red-700',
                                        ];
                                        $statusLabels = [
                                            'pending' => 'Verifikasi Bayar',
                                            'paid_verified' => 'Siap Kirim',
                                            'processing' => 'Diproses',
                                            'shipped' => 'Dikirim',
                                            'received' => 'Butuh Pelepasan Dana',
                                            'completed' => 'Selesai',
                                            'payment_rejected' => 'Pembayaran Ditolak',
                                            'cancelled' => 'Dibatalkan',
                                        ];
                                    @endphp
                                    <span
                                        class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClasses[$tx->status] ?? 'bg-gray-100 text-gray-700' }}">
                                        {{ $statusLabels[$tx->status] ?? $tx->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ $tx->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('admin.transactions.show', $tx->id) }}"
                                        class="inline-flex items-center px-4 py-2 border border-transparent text-sm leading-5 font-semibold rounded-lg text-white bg-orange-500 hover:bg-orange-600 focus:outline-none focus:shadow-outline-orange transition duration-150 ease-in-out shadow-sm">
                                        <i class="fas fa-eye mr-2"></i> Detail
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-gray-500">
                                    <div class="flex flex-col items-center">
                                        <i class="fas fa-receipt text-4xl text-gray-200 mb-4"></i>
                                        <p>Belum ada data transaksi untuk tab ini.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $transactions->links() }}
        </div>
    </div>
@endsection