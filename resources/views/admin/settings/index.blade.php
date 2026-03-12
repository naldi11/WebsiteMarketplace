@extends('layouts.admin')

@section('content')
    <div class="max-w-4xl">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-gray-900 tracking-tight">Pengaturan Sistem</h1>
            <p class="text-gray-500 mt-1">Kelola konten hukum dan informasi platform secara dinamis untuk Website dan
                Aplikasi Mobile.</p>
        </div>

        <form action="{{ route('admin.settings.update') }}" method="POST" class="space-y-6">
            @csrf

            @foreach($settings as $setting)
                <div
                    class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden transition-all hover:shadow-md">
                    <div class="p-6 border-b border-gray-50 bg-gray-50/50">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="text-sm font-semibold text-gray-900 uppercase tracking-wider">
                                    {{ $setting->description }}</h3>
                                <p class="text-xs text-indigo-600 font-mono mt-1">Key: {{ $setting->key }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="p-6">
                        <label class="block text-sm font-medium text-gray-700 mb-3 sr-only">Konten</label>
                        <textarea name="settings[{{ $setting->key }}]" rows="10" placeholder="Masukkan konten di sini..."
                            class="w-full rounded-xl border-gray-200 bg-gray-50/30 text-gray-800 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all p-4 text-sm leading-relaxed">{{ $setting->value }}</textarea>

                        <div class="mt-4 flex items-start gap-2 text-xs text-gray-400">
                            <svg class="w-4 h-4 mt-0.5 text-indigo-400 shrink-0" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Gunakan format teks polos. Baris baru akan dijaga secara otomatis di frontend. Perubahan akan
                                segera berdampak pada seluruh pengguna platform.</span>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="flex items-center justify-end pt-4">
                <button type="submit"
                    class="inline-flex items-center gap-2 px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-semibold rounded-xl shadow-lg shadow-indigo-200 transition-all active:scale-95">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4">
                        </path>
                    </svg>
                    Simpan Semua Perubahan
                </button>
            </div>
        </form>
    </div>

    @push('scripts')
        <style>
            textarea {
                resize: vertical;
                min-height: 200px;
            }
        </style>
    @endpush
@endsection