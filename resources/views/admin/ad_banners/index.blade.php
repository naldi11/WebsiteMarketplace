@extends('layouts.admin')

@section('content')
<div class="pt-0 pb-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
        <div>
            <h1 class="text-4xl font-black tracking-tighter uppercase italic text-black">Banner Iklan</h1>
            <p class="text-gray-500 mt-1 font-mono text-xs uppercase tracking-widest text-black">Promosi Depan & Distribusi Banner</p>
        </div>
        <button onclick="document.getElementById('addBannerModal').classList.remove('hidden')" class="px-6 py-3 bg-black text-white border-[3px] border-black text-sm font-black uppercase tracking-tighter hover:bg-white hover:text-black transition-all neo-brutalism italic flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
            Tambah Banner
        </button>
    </div>

    <!-- Banner Matrix - Neo Brutalism -->
    <div class="bg-white border-[3px] border-black neo-brutalism overflow-hidden mb-12">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-100 border-b-[3px] border-black text-black font-black uppercase italic">
                    <tr>
                        <th class="px-8 py-6">Gambar Banner</th>
                        <th class="px-8 py-6">Judul</th>
                        <th class="px-8 py-6">Status</th>
                        <th class="px-8 py-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-gray-100 font-bold">
                    @forelse($adBanners as $banner)
                    <tr class="hover:bg-gray-50 transition-all">
                        <td class="px-8 py-6">
                            @if($banner->image)
                                <div class="h-20 w-40 overflow-hidden">
                                    <img src="{{ Storage::url($banner->image) }}" alt="{{ $banner->title }}" class="h-full w-full object-cover">
                                </div>
                            @else
                                <div class="h-20 w-40 border-[3px] border-black bg-gray-100 flex items-center justify-center font-black italic text-gray-400 uppercase">TIDAK ADA GAMBAR</div>
                            @endif
                        </td>
                        <td class="px-8 py-6 font-black text-sm text-black uppercase italic tracking-tighter">{{ $banner->title }}</td>
                        <td class="px-8 py-6">
                            <span class="px-3 py-1 border-2 border-black text-[10px] font-black uppercase tracking-widest {{ $banner->is_active ? 'bg-black text-white' : 'bg-white text-black' }}">
                                {{ $banner->is_active ? 'AKTIF' : 'NONAKTIF' }}
                            </span>
                        </td>
                        <td class="px-8 py-6 text-right whitespace-nowrap">
                            <div class="flex justify-end gap-3 font-black uppercase italic text-[10px]">
                                <button onclick="editBanner({{ $banner }})" class="px-4 py-2 border-2 border-black hover:bg-black hover:text-white transition-all italic">Edit</button>
                                <form action="{{ route('admin.ad_banners.destroy', $banner) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus banner {{ $banner->title }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-4 py-2 bg-black text-white border-2 border-black hover:bg-white hover:text-black transition-all">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-8 py-24 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 border-[3px] border-black flex items-center justify-center font-black text-2xl mb-4 italic">!</div>
                                <p class="text-xs font-black uppercase tracking-widest text-gray-400 italic">Belum Ada Banner</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Neo Brutalism -->
<div id="addBannerModal" class="fixed inset-0 bg-black/60 hidden z-50 overflow-y-auto transition-all duration-200 backdrop-blur-sm">
    <div class="min-h-screen px-4 text-center flex items-center justify-center">
        <div class="inline-block w-full max-w-md p-0 my-8 text-left align-middle bg-white transform transition-all border-[4px] border-black shadow-[20px_20px_0px_0px_rgba(0,0,0,1)]">
            
            <div class="bg-black p-8 text-white flex justify-between items-center">
                <div>
                    <h3 class="text-3xl font-black uppercase italic tracking-tighter" id="modalTitle">Konfigurasi Banner</h3>
                    <p class="text-gray-400 text-[10px] mt-1 font-mono uppercase tracking-widest">Upload & Atur Banner Promosi</p>
                </div>
                <button onclick="closeModal()" class="border-2 border-white w-10 h-10 flex items-center justify-center hover:bg-white hover:text-black transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form id="bannerForm" action="{{ route('admin.ad_banners.store') }}" method="POST" enctype="multipart/form-data" class="p-10 bg-white">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="space-y-8">
                    <div>
                        <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-gray-500">Judul Banner</label>
                        <input type="text" name="title" id="bannerTitle" required placeholder="SUMMER_COLLECTION_V1"
                            class="w-full px-5 py-4 bg-white border-[3px] border-black rounded-none focus:bg-gray-50 outline-none transition-all font-black uppercase italic text-sm shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-gray-500">File Gambar</label>
                        <div class="relative">
                            <input type="file" name="image" id="bannerImage" accept="image/*"
                                class="w-full px-5 py-4 bg-white border-[3px] border-black rounded-none focus:bg-gray-50 outline-none transition-all font-mono text-[10px] shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                        </div>
                        <p class="text-[9px] text-gray-400 mt-3 font-mono uppercase tracking-widest italic" id="imageHelp">* Matrix support: JPG/PNG. Aspect ratio: 2:1 Recommended.</p>
                    </div>

                    <div class="flex items-center gap-4 p-4 border-[3px] border-black bg-gray-50 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                        <input type="checkbox" name="is_active" id="bannerIsActive" value="1" checked
                            class="w-8 h-8 text-black border-[3px] border-black rounded-none focus:ring-0 cursor-pointer">
                        <label for="bannerIsActive" class="text-xs font-black uppercase italic tracking-widest cursor-pointer">Banner ini AKTIF</label>
                    </div>
                </div>

                <div class="mt-12 flex gap-6">
                    <button type="button" onclick="closeModal()" class="flex-1 px-8 py-5 text-sm font-black border-[3px] border-black hover:bg-black hover:text-white transition-all uppercase italic">Batal</button>
                    <button type="submit" class="flex-1 px-8 py-5 text-sm font-black bg-black text-white border-[3px] border-black hover:bg-white hover:text-black transition-all uppercase italic shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">Simpan Banner</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function closeModal() {
        const modal = document.getElementById('addBannerModal');
        modal.classList.add('hidden');
        document.getElementById('bannerForm').reset();
        document.getElementById('bannerForm').action = "{{ route('admin.ad_banners.store') }}";
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('modalTitle').innerText = 'Asset Config';
        document.getElementById('bannerImage').required = true;
        document.getElementById('imageHelp').innerText = '* Matrix support: JPG/PNG. Aspect ratio: 2:1 Recommended.';
    }

    function editBanner(banner) {
        const modal = document.getElementById('addBannerModal');
        modal.classList.remove('hidden');
        document.getElementById('modalTitle').innerText = 'Modify Protocol';
        document.getElementById('bannerForm').action = `/admin/ad-banners/${banner.id}`;
        document.getElementById('formMethod').value = 'PUT';
        
        document.getElementById('bannerTitle').value = banner.title;
        document.getElementById('bannerIsActive').checked = banner.is_active == 1;
        
        document.getElementById('bannerImage').required = false;
        document.getElementById('imageHelp').innerText = '* Null value will retain current visual buffer.';
    }
    
    document.getElementById('bannerImage').required = true;
</script>
@endpush
@endsection
