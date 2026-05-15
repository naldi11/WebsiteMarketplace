@extends('layouts.admin')

@section('content')
<div class="pt-0 pb-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
        <div>
            <h1 class="text-4xl font-black tracking-tighter uppercase italic text-slate-800">Matriks Kategori</h1>
            <p class="text-slate-500 mt-1 font-mono text-xs uppercase tracking-widest">Taksonomi & Manajemen Pengelompokan</p>
        </div>
        <a href="{{ route('admin.categories.create') }}"
            class="btn-gradient text-white rounded-xl px-6 py-3 text-sm font-black uppercase tracking-tighter italic flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Kategori
        </a>
    </div>

    <div class="glass-card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="glass-table-header text-slate-600 font-black uppercase">
                    <tr>
                        <th class="px-8 py-6">Ikon</th>
                        <th class="px-8 py-6">Nama Kategori</th>
                        <th class="px-8 py-6">Slug</th>
                        <th class="px-8 py-6">Jumlah Produk</th>
                        <th class="px-8 py-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-indigo-50 font-bold">
                    @forelse($categories as $category)
                        <tr class="hover:bg-indigo-50/50 transition-all">
                            <td class="px-8 py-6 whitespace-nowrap">
                                @if($category->icon)
                                    <div class="w-12 h-12 border border-indigo-100 rounded-xl overflow-hidden shadow-lg">
                                        <img src="{{ Storage::url($category->icon) }}"
                                            class="w-full h-full object-cover">
                                    </div>
                                @else
                                    <div class="w-12 h-12 border border-indigo-100 rounded-xl bg-slate-50 flex items-center justify-center text-slate-400">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                @endif
                            </td>
                            <td class="px-8 py-6 font-black text-sm text-slate-800 uppercase italic tracking-tighter">{{ $category->name }}</td>
                            <td class="px-8 py-6 font-mono text-[10px] text-slate-400 uppercase tracking-widest">{{ $category->slug }}</td>
                            <td class="px-8 py-6">
                                <span class="badge-active italic">
                                    {{ $category->products_count }} Produk
                                </span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('admin.categories.edit', $category) }}"
                                        class="btn-outline rounded-xl px-4 py-2 text-[10px] font-black uppercase italic">Edit</a>
                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST"
                                        onsubmit="return confirm('Hapus kategori {{ $category->name }}?');" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="btn-danger text-white rounded-xl px-4 py-2 text-[10px] font-black uppercase">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-24 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 border border-indigo-100 rounded-xl flex items-center justify-center font-black text-2xl mb-4 italic text-slate-400">!</div>
                                    <p class="text-xs font-black uppercase tracking-widest text-slate-400 italic">Belum Ada Kategori</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
