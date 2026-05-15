@extends('layouts.admin')

@section('content')
<div class="pt-0 pb-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
        <div>
            <h1 class="text-4xl font-black tracking-tighter uppercase italic text-black">Matriks Kategori</h1>
            <p class="text-gray-500 mt-1 font-mono text-xs uppercase tracking-widest text-black">Taksonomi & Manajemen Pengelompokan</p>
        </div>
        <a href="{{ route('admin.categories.create') }}"
            class="px-6 py-3 bg-black text-white border-[3px] border-black text-sm font-black uppercase tracking-tighter hover:bg-white hover:text-black transition-all neo-brutalism italic flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Kategori
        </a>
    </div>

    <div class="bg-white border-[3px] border-black neo-brutalism overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-100 border-b-[3px] border-black text-black font-black uppercase italic">
                    <tr>
                        <th class="px-8 py-6">Icon</th>
                        <th class="px-8 py-6">Nama Kategori</th>
                        <th class="px-8 py-6">Slug</th>
                        <th class="px-8 py-6">Jumlah Produk</th>
                        <th class="px-8 py-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-gray-100 font-bold">
                    @forelse($categories as $category)
                        <tr class="hover:bg-gray-50 transition-all">
                            <td class="px-8 py-6 whitespace-nowrap">
                                @if($category->icon)
                                    <div class="w-12 h-12 border-2 border-black grayscale contrast-125 brightness-90 shadow-[2px_2px_0px_0px_rgba(0,0,0,1)]">
                                        <img src="{{ Storage::url($category->icon) }}"
                                            class="w-full h-full object-cover">
                                    </div>
                                @else
                                    <div class="w-12 h-12 border-2 border-black bg-gray-100 flex items-center justify-center text-black">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3"
                                                d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                @endif
                            </td>
                            <td class="px-8 py-6 font-black text-sm text-black uppercase italic tracking-tighter">{{ $category->name }}</td>
                            <td class="px-8 py-6 font-mono text-[10px] text-gray-400 uppercase tracking-widest">{{ $category->slug }}</td>
                            <td class="px-8 py-6">
                                <span class="px-3 py-1 border-2 border-black text-[10px] font-black uppercase tracking-widest bg-black text-white italic">
                                    {{ $category->products_count }} Produk
                                </span>
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex justify-end gap-3">
                                    <a href="{{ route('admin.categories.edit', $category) }}"
                                        class="px-4 py-2 border-2 border-black text-[10px] font-black uppercase hover:bg-black hover:text-white transition-all italic">Edit</a>
                                    <form action="{{ route('admin.categories.destroy', $category) }}" method="POST"
                                        onsubmit="return confirm('Hapus kategori {{ $category->name }}?');" class="inline-block">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                            class="px-4 py-2 bg-black text-white border-2 border-black text-[10px] font-black uppercase hover:bg-white hover:text-black transition-all">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-8 py-24 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 border-[3px] border-black flex items-center justify-center font-black text-2xl mb-4 italic">!</div>
                                    <p class="text-xs font-black uppercase tracking-widest text-gray-400 italic">Belum Ada Kategori</p>
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