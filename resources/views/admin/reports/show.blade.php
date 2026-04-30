@extends('layouts.admin')

@section('title', 'Detail Laporan #' . $report->id)

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="mb-6">
        <a href="{{ route('admin.reports') }}" class="text-sm text-pink-600 hover:text-pink-900 flex items-center gap-1">
            ← Kembali ke Daftar Laporan
        </a>
    </div>

    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6 flex justify-between items-center bg-gray-50">
            <div>
                <h3 class="text-lg leading-6 font-bold text-gray-900">Laporan Masalah #{{ $report->id }}</h3>
                <p class="mt-1 max-w-2xl text-sm text-gray-500">Dikirim pada {{ $report->created_at->format('d F Y H:i') }}</p>
            </div>
            <div>
                @if($report->status === 'pending')
                    <span class="inline-flex rounded-full bg-yellow-100 px-3 py-1 text-sm font-semibold leading-5 text-yellow-800">PENDING</span>
                @elseif($report->status === 'resolved')
                    <span class="inline-flex rounded-full bg-green-100 px-3 py-1 text-sm font-semibold leading-5 text-green-800">SELESAI</span>
                @else
                    <span class="inline-flex rounded-full bg-gray-100 px-3 py-1 text-sm font-semibold leading-5 text-gray-800">DITOLAK</span>
                @endif
            </div>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:p-0">
            <dl class="sm:divide-y sm:divide-gray-200">
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Pelapor</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <div class="font-bold">{{ $report->user->name }}</div>
                        <div class="text-gray-500">{{ $report->user->email }}</div>
                        <div class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-pink-100 text-pink-800 capitalize">
                            {{ str_replace('_', ' ', $report->type) }}
                        </div>
                    </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Transaksi Terkait</dt>
                    <dd class="mt-1 text-sm text-pink-600 font-bold sm:mt-0 sm:col-span-2">
                        <a href="{{ route('admin.transactions.show', $report->transaction_id) }}" class="underline">
                            #{{ $report->transaction_id }} - Lihat Detail Transaksi
                        </a>
                    </dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Alasan Laporan</dt>
                    <dd class="mt-1 text-sm text-gray-900 font-bold sm:mt-0 sm:col-span-2">{{ $report->reason }}</dd>
                </div>
                <div class="py-4 sm:py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">Deskripsi Masalah</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2 whitespace-pre-wrap">{{ $report->description }}</dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- Admin Actions --}}
    <div class="mt-8 bg-white shadow sm:rounded-lg p-6">
        <h3 class="text-lg leading-6 font-bold text-gray-900 mb-4">Tanggapan Admin</h3>
        <form action="{{ route('admin.reports.update', $report) }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Ubah Status</label>
                    <select name="status" class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-pink-500 focus:border-pink-500 sm:text-sm rounded-md shadow-sm">
                        <option value="pending" {{ $report->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="resolved" {{ $report->status === 'resolved' ? 'selected' : '' }}>Selesai (Resolved)</option>
                        <option value="dismissed" {{ $report->status === 'dismissed' ? 'selected' : '' }}>Tolak (Dismissed)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Catatan Internal / Penjelasan</label>
                    <textarea name="admin_note" rows="4" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-pink-500 focus:border-pink-500 sm:text-sm"
                        placeholder="Masukkan catatan tindakan yang diambil...">{{ $report->admin_note }}</textarea>
                </div>
                <div class="flex justify-end gap-2">
                    @php
                        $userPhone = $report->user->phone ?? '';
                        $waText = urlencode("Halo " . $report->user->name . ", Saya Admin dari Pikirku. Mengenai laporan Anda #" . $report->id . " terkait transaksi #" . $report->transaction_id . "...");
                    @endphp
                    @if($userPhone)
                        <a href="https://wa.me/{{ $userPhone }}?text={{ $waText }}" target="_blank"
                            class="inline-flex items-center px-4 py-2 border border-green-500 text-sm font-medium rounded-md text-green-600 bg-white hover:bg-green-50 shadow-sm transition">
                            💬 Hubungi {{ $report->user->name }} via WA
                        </a>
                    @endif
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-bold rounded-md text-white bg-pink-600 hover:bg-pink-700 shadow-sm transition">
                        Simpan Perubahan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
