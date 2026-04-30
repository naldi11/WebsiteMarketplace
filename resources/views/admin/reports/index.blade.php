@extends('layouts.admin')

@section('title', 'Laporan Masalah Transaksi')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center">
        <div class="sm:flex-auto">
            <h1 class="text-2xl font-bold text-gray-900">Laporan Masalah</h1>
            <p class="mt-2 text-sm text-gray-700">Daftar keluhan dari pembeli dan penjual terkait transaksi yang bermasalah.</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="mt-8 border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <a href="{{ route('admin.reports', ['status' => 'pending']) }}" 
                class="{{ $status === 'pending' ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Pending <span class="ml-2 bg-pink-100 text-pink-600 px-2 py-0.5 rounded-full text-xs">{{ $counts['pending'] }}</span>
            </a>
            <a href="{{ route('admin.reports', ['status' => 'resolved']) }}" 
                class="{{ $status === 'resolved' ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Selesai <span class="ml-2 bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-xs">{{ $counts['resolved'] }}</span>
            </a>
            <a href="{{ route('admin.reports', ['status' => 'dismissed']) }}" 
                class="{{ $status === 'dismissed' ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Ditolak <span class="ml-2 bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full text-xs">{{ $counts['dismissed'] }}</span>
            </a>
            <a href="{{ route('admin.reports', ['status' => 'all']) }}" 
                class="{{ $status === 'all' ? 'border-pink-500 text-pink-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }} whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Semua
            </a>
        </nav>
    </div>

    <div class="mt-8 flex flex-col">
        <div class="-my-2 -mx-4 overflow-x-auto sm:-mx-6 lg:-mx-8">
            <div class="inline-block min-w-full py-2 align-middle md:px-6 lg:px-8">
                <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                    <table class="min-w-full divide-y divide-gray-300">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Waktu</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Pelapor</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Transaksi</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Masalah</th>
                                <th class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Status</th>
                                <th class="relative py-3.5 pl-3 pr-4 sm:pr-6">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @forelse($reports as $report)
                            <tr>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-500 sm:pl-6">
                                    {{ $report->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-900">
                                    <div class="font-medium">{{ $report->user->name }}</div>
                                    <div class="text-gray-500 text-xs text-uppercase">{{ str_replace('_', ' ', $report->type) }}</div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-pink-600 font-bold">
                                    <a href="{{ route('admin.transactions.show', $report->transaction_id) }}">#{{ $report->transaction_id }}</a>
                                </td>
                                <td class="px-3 py-4 text-sm text-gray-500">
                                    <div class="font-bold text-gray-900">{{ $report->reason }}</div>
                                    <div class="truncate max-w-xs">{{ $report->description }}</div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm">
                                    @if($report->status === 'pending')
                                        <span class="inline-flex rounded-full bg-yellow-100 px-2 text-xs font-semibold leading-5 text-yellow-800">Pending</span>
                                    @elseif($report->status === 'resolved')
                                        <span class="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800">Selesai</span>
                                    @else
                                        <span class="inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold leading-5 text-gray-800">Ditolak</span>
                                    @endif
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <a href="{{ route('admin.reports.show', $report) }}" class="text-pink-600 hover:text-pink-900">Detail</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="py-12 text-center">
                                    <div class="text-gray-400 text-3xl mb-2">🍃</div>
                                    <div class="text-gray-500 font-medium">Tidak ada laporan yang ditemukan.</div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $reports->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
