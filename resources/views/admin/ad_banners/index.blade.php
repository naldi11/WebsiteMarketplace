@extends('layouts.admin')

@section('content')
<div class="pt-0 pb-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
        <div>
            <h1 class="text-4xl font-black tracking-tighter uppercase italic text-slate-800">Banner Iklan</h1>
            <p class="text-slate-500 mt-1 font-mono text-xs uppercase tracking-widest">Promosi Depan & Distribusi Banner</p>
        </div>
        <button onclick="document.getElementById('addBannerModal').classList.remove('hidden')" class="btn-gradient text-white rounded-xl px-6 py-3 text-sm font-black uppercase tracking-tighter italic flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
            Tambah Banner
        </button>
    </div>

    <!-- Banner Matrix -->
    <div class="glass-card overflow-hidden mb-12">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="glass-table-header text-slate-600 font-black uppercase">
                    <tr>
                        <th class="px-8 py-6">Gambar Banner</th>
                        <th class="px-8 py-6">Judul</th>
                        <th class="px-8 py-6">Status</th>
                        <th class="px-8 py-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-indigo-50 font-bold">
                    @forelse($adBanners as $banner)
                    <tr class="hover:bg-indigo-50/50 transition-all">
                        <td class="px-8 py-6">
                            @if($banner->image)
                                <div class="h-20 w-40 overflow-hidden rounded-xl border border-indigo-100">
                                    <img src="{{ Storage::url($banner->image) }}" alt="{{ $banner->title }}" class="h-full w-full object-cover">
                                </div>
                            @else
                                <div class="h-20 w-40 border border-indigo-100 rounded-xl bg-slate-50 flex items-center justify-center font-black italic text-slate-400 uppercase text-[10px]">TIDAK ADA GAMBAR</div>
                            @endif
                        </td>
                        <td class="px-8 py-6 font-black text-sm text-slate-800 uppercase italic tracking-tighter">{{ $banner->title }}</td>
                        <td class="px-8 py-6">
                            @if($banner->is_active)
                                <span class="badge-active">AKTIF</span>
                            @else
                                <span class="badge-inactive">NONAKTIF</span>
                            @endif
                        </td>
                        <td class="px-8 py-6 text-right whitespace-nowrap">
                            <div class="flex justify-end gap-3 font-black uppercase italic text-[10px]">
                                <button onclick="editBanner({{ $banner }})" class="btn-outline rounded-xl px-4 py-2 italic">Edit</button>
                                <form action="{{ route('admin.ad_banners.destroy', $banner) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus banner {{ $banner->title }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn-danger text-white rounded-xl px-4 py-2">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-8 py-24 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 border border-indigo-100 rounded-xl flex items-center justify-center font-black text-2xl mb-4 italic text-slate-400">!</div>
                                <p class="text-xs font-black uppercase tracking-widest text-slate-400 italic">Belum Ada Banner</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit -->
<div id="addBannerModal" class="fixed inset-0 bg-black/60 hidden z-50 overflow-y-auto transition-all duration-200 backdrop-blur-sm">
    <div class="min-h-screen px-4 text-center flex items-center justify-center">
        <div class="inline-block w-full max-w-md p-0 my-8 text-left align-middle bg-white/95 transform transition-all border border-white/50 shadow-2xl rounded-2xl overflow-hidden">

            <div class="gradient-header-indigo p-8 text-white flex justify-between items-center rounded-t-2xl">
                <div>
                    <h3 class="text-3xl font-black uppercase italic tracking-tighter" id="modalTitle">Konfigurasi Banner</h3>
                    <p class="text-indigo-100 text-[10px] mt-1 font-mono uppercase tracking-widest">Upload & Atur Banner Promosi</p>
                </div>
                <button onclick="closeModal()" class="border-2 border-white/50 w-10 h-10 rounded-xl flex items-center justify-center hover:bg-white/20 transition-all text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form id="bannerForm" action="{{ route('admin.ad_banners.store') }}" method="POST" enctype="multipart/form-data" class="p-10 bg-white">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="space-y-8">
                    <div>
                        <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-slate-500">Judul Banner</label>
                        <input type="text" name="title" id="bannerTitle" required placeholder="SUMMER_COLLECTION_V1"
                            class="w-full px-5 py-4 bg-white/40 border border-indigo-200 focus:ring-2 focus:ring-indigo-300 rounded-xl outline-none transition-all font-black uppercase italic text-sm shadow-lg">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-slate-500">File Gambar</label>
                        <div class="relative">
                            <input type="file" name="image" id="bannerImage" accept="image/*"
                                class="w-full px-5 py-4 bg-white/40 border border-indigo-200 focus:ring-2 focus:ring-indigo-300 rounded-xl outline-none transition-all font-mono text-[10px] shadow-lg">
                        </div>
                        <p class="text-[9px] text-slate-400 mt-3 font-mono uppercase tracking-widest italic" id="imageHelp">* Format: JPG/PNG. Rasio gambar 2:1 direkomendasikan.</p>
                    </div>

                    <div class="flex items-center gap-4 p-4 border border-indigo-100 rounded-xl bg-white/40 shadow-lg">
                        <input type="checkbox" name="is_active" id="bannerIsActive" value="1" checked
                            class="w-5 h-5 text-indigo-600 border-indigo-200 rounded focus:ring-indigo-300 cursor-pointer">
                        <label for="bannerIsActive" class="text-xs font-black uppercase italic tracking-widest cursor-pointer text-slate-700">Banner ini AKTIF</label>
                    </div>
                </div>

                <div class="mt-12 flex gap-6">
                    <button type="button" onclick="closeModal()" class="btn-outline rounded-xl flex-1 px-8 py-5 text-sm font-black uppercase italic">Batal</button>
                    <button type="submit" class="btn-gradient text-white rounded-xl flex-1 px-8 py-5 text-sm font-black uppercase italic shadow-xl">Simpan Banner</button>
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
        document.getElementById('modalTitle').innerText = 'Tambah Banner';
        document.getElementById('bannerImage').required = true;
        document.getElementById('imageHelp').innerText = '* Format: JPG/PNG. Rasio gambar 2:1 direkomendasikan.';
    }

    function editBanner(banner) {
        const modal = document.getElementById('addBannerModal');
        modal.classList.remove('hidden');
        document.getElementById('modalTitle').innerText = 'Edit Banner';
        document.getElementById('bannerForm').action = `/admin/ad-banners/${banner.id}`;
        document.getElementById('formMethod').value = 'PUT';

        document.getElementById('bannerTitle').value = banner.title;
        document.getElementById('bannerIsActive').checked = banner.is_active == 1;

        document.getElementById('bannerImage').required = false;
        document.getElementById('imageHelp').innerText = '* Biarkan kosong untuk mempertahankan gambar yang ada.';
    }

    document.getElementById('bannerImage').required = true;
</script>
@endpush
@endsection
