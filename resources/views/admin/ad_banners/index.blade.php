@extends('layouts.admin')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Manajemen Banner Iklan</h1>
        <p class="text-gray-500 mt-1">Kelola banner promo yang tampil di beranda aplikasi.</p>
    </div>
    <button onclick="document.getElementById('addBannerModal').classList.remove('hidden')" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
        Tambah Banner
    </button>
</div>

<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="py-3 px-6 font-semibold text-sm text-gray-600 uppercase tracking-wider">Gambar</th>
                    <th class="py-3 px-6 font-semibold text-sm text-gray-600 uppercase tracking-wider">Judul</th>
                    <th class="py-3 px-6 font-semibold text-sm text-gray-600 uppercase tracking-wider">Status</th>
                    <th class="py-3 px-6 font-semibold text-sm text-gray-600 uppercase tracking-wider text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($adBanners as $banner)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="py-4 px-6">
                        @if($banner->image)
                            <img src="{{ Storage::url($banner->image) }}" alt="{{ $banner->title }}" class="h-16 w-32 object-cover rounded shadow-sm">
                        @else
                            <div class="h-16 w-32 bg-gray-100 flex items-center justify-center rounded text-gray-400">No Image</div>
                        @endif
                    </td>
                    <td class="py-4 px-6 font-medium text-gray-900">{{ $banner->title }}</td>
                    <td class="py-4 px-6">
                        @if($banner->is_active)
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Nonaktif
                            </span>
                        @endif
                    </td>
                    <td class="py-4 px-6 text-right">
                        <button onclick="editBanner({{ $banner }})" class="text-indigo-600 hover:text-indigo-900 font-medium text-sm mr-3">Edit</button>
                        <form action="{{ route('admin.ad_banners.destroy', $banner) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus banner ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-900 font-medium text-sm">Hapus</button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="py-8 text-center text-gray-500">
                        Belum ada banner iklan.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah/Edit -->
<div id="addBannerModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm hidden z-50 overflow-y-auto">
    <div class="min-h-screen px-4 text-center">
        <!-- Spacer -->
        <span class="inline-block h-screen align-middle" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block w-full max-w-md p-6 my-8 text-left align-middle bg-white shadow-xl rounded-2xl transform transition-all">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-lg font-bold text-gray-900" id="modalTitle">Tambah Banner Iklan</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form id="bannerForm" action="{{ route('admin.ad_banners.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Judul Banner</label>
                        <input type="text" name="title" id="bannerTitle" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 outline-none transition-shadow">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Gambar Banner</label>
                        <input type="file" name="image" id="bannerImage" accept="image/*"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 outline-none transition-shadow">
                        <p class="text-xs text-gray-500 mt-1" id="imageHelp">Format: JPG, PNG. Ukuran ideal melebar (landscape).</p>
                    </div>

                    <div class="flex items-center">
                        <input type="checkbox" name="is_active" id="bannerIsActive" value="1" checked
                            class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="bannerIsActive" class="ml-2 block text-sm text-gray-900">Aktifkan Banner</label>
                    </div>
                </div>

                <div class="mt-8 flex gap-3 justify-end">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">Simpan Banner</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function closeModal() {
        document.getElementById('addBannerModal').classList.add('hidden');
        document.getElementById('bannerForm').reset();
        document.getElementById('bannerForm').action = "{{ route('admin.ad_banners.store') }}";
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('modalTitle').innerText = 'Tambah Banner Iklan';
        document.getElementById('bannerImage').required = true;
        document.getElementById('imageHelp').innerText = 'Format: JPG, PNG. Ukuran ideal melebar (landscape). Wajib diisi.';
    }

    function editBanner(banner) {
        document.getElementById('addBannerModal').classList.remove('hidden');
        document.getElementById('modalTitle').innerText = 'Edit Banner Iklan';
        document.getElementById('bannerForm').action = `/admin/ad-banners/${banner.id}`;
        document.getElementById('formMethod').value = 'PUT';
        
        document.getElementById('bannerTitle').value = banner.title;
        document.getElementById('bannerIsActive').checked = banner.is_active == 1;
        
        document.getElementById('bannerImage').required = false;
        document.getElementById('imageHelp').innerText = 'Biarkan kosong jika tidak ingin mengubah gambar.';
    }
    
    // Set required true natively
    document.getElementById('bannerImage').required = true;
</script>
@endpush
@endsection
