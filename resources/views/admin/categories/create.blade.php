@extends('layouts.admin')

@section('content')
    <div class="max-w-xl mx-auto">
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Tambah Kategori Baru</h1>
            <p class="text-slate-500 mt-1">Buat kategori untuk mengelompokkan produk.</p>
        </div>

        <div class="glass-card p-8">
            <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data"
                class="space-y-6">
                @csrf

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Nama Kategori</label>
                    <input type="text" name="name" required placeholder="Contoh: Elektronik, Fashion..."
                        class="block w-full px-4 py-3 rounded-xl border border-indigo-200 focus:ring-2 focus:ring-indigo-300 transition-colors bg-white/40 hover:bg-white">
                </div>

                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Icon (Opsional)</label>
                    <input type="file" name="icon" accept="image/*" class="block w-full text-sm text-slate-500
                                    file:mr-4 file:py-2.5 file:px-4
                                    file:rounded-full file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-indigo-50 file:text-indigo-700
                                    hover:file:bg-indigo-100 transition">
                    <p class="text-xs text-slate-400 mt-2">Format: JPG, PNG. Maksimal: 2MB.</p>
                </div>

                <div class="pt-4 flex gap-4">
                    <a href="{{ route('admin.categories') }}"
                        class="btn-outline rounded-xl flex-1 font-bold py-3 text-center">
                        Batal
                    </a>
                    <button type="submit"
                        class="btn-gradient text-white rounded-xl flex-1 font-bold py-3">
                        Simpan Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
