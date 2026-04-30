@extends('layouts.admin')

@section('title', 'Kelola Voucher')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Kelola Voucher</h1>
            <p class="mt-2 text-sm text-gray-700">Buat, ubah, atau hapus kupon diskon untuk pelanggan.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16">
            <button onclick="openAddModal()" class="inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Voucher
            </button>
        </div>
    </div>

    <!-- Voucher Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 text-gray-500 uppercase text-[11px] font-bold tracking-wider">
                <tr>
                    <th class="px-6 py-4 text-left">Kode</th>
                    <th class="px-6 py-4 text-left">Tipe & Diskon</th>
                    <th class="px-6 py-4 text-left">Min. Belanja</th>
                    <th class="px-6 py-4 text-left">Kuota</th>
                    <th class="px-6 py-4 text-left">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @foreach($vouchers as $v)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 py-1 bg-indigo-50 text-indigo-700 rounded font-black text-sm">{{ $v->code }}</span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <div class="font-bold">
                            {{ $v->discount_type == 'percent' ? $v->discount_amount . '%' : 'Rp ' . number_format($v->discount_amount, 0, ',', '.') }}
                        </div>
                        @if($v->discount_type == 'percent' && $v->max_discount_amount)
                            <div class="text-xs text-gray-400">Maks Rp {{ number_format($v->max_discount_amount, 0, ',', '.') }}</div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        Rp {{ number_format($v->min_purchase, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <div class="flex flex-col">
                            <span>{{ $v->usage_count }} / {{ $v->usage_limit }}</span>
                            <div class="w-full bg-gray-100 rounded-full h-1 mt-1">
                                <div class="bg-indigo-500 h-1 rounded-full" style="width: {{ min(100, ($v->usage_count / $v->usage_limit) * 100) }}%"></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col gap-1 items-start">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $v->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                {{ $v->is_active ? 'Aktif' : 'Non-Aktif' }}
                            </span>
                            @if($v->target_user_id)
                                <span class="text-[10px] text-blue-600 font-medium italic">User #{{ $v->target_user_id }}</span>
                            @else
                                <span class="text-[10px] text-gray-400 font-medium uppercase tracking-tighter">Global</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <button onclick='editVoucher(@json($v))' class="text-indigo-600 hover:text-indigo-900 mr-4 font-bold">Edit</button>
                        <form action="{{ route('admin.vouchers.destroy', $v) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus voucher {{ $v->code }}?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 font-bold">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah/Edit -->
<div id="voucherModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm hidden z-50 overflow-y-auto">
    <div class="min-h-screen px-4 text-center">
        <span class="inline-block h-screen align-middle" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block w-full max-w-xl p-8 my-8 text-left align-middle bg-white shadow-2xl rounded-3xl transform transition-all border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-2xl font-black text-gray-900 tracking-tighter" id="modalTitle">Tambah Voucher</h3>
                    <p class="text-gray-500 text-sm mt-1">Konfigurasi pengaturan diskon anda.</p>
                </div>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 p-2 bg-gray-50 rounded-full transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form id="voucherForm" action="{{ route('admin.vouchers.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Kode Voucher</label>
                        <input type="text" name="code" id="vCode" required placeholder="CTH: SALE50"
                            class="w-full px-4 py-3 border-2 border-gray-100 rounded-xl focus:border-indigo-500 focus:ring-0 outline-none transition-all font-bold">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Tipe Diskon</label>
                        <select name="discount_type" id="vType" required
                            class="w-full px-4 py-3 border-2 border-gray-100 rounded-xl focus:border-indigo-500 focus:ring-0 outline-none transition-all">
                            <option value="fixed">Nominal Pasti (Rp)</option>
                            <option value="percent">Persentase (%)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Nilai Diskon</label>
                        <input type="number" name="discount_amount" id="vAmount" required placeholder="Contoh: 50000"
                            class="w-full px-4 py-3 border-2 border-gray-100 rounded-xl focus:border-indigo-500 focus:ring-0 outline-none transition-all">
                    </div>

                    <div id="maxDiscountRow">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Maks. Diskon (Rp)</label>
                        <input type="number" name="max_discount_amount" id="vMaxAmount" placeholder="Khusus Tipe %"
                            class="w-full px-4 py-3 border-2 border-gray-100 rounded-xl focus:border-indigo-500 focus:ring-0 outline-none transition-all text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Min. Belanja (Rp)</label>
                        <input type="number" name="min_purchase" id="vMinPurchase" required value="0"
                            class="w-full px-4 py-3 border-2 border-gray-100 rounded-xl focus:border-indigo-500 focus:ring-0 outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Kuota Penggunaan</label>
                        <input type="number" name="usage_limit" id="vLimit" required value="100"
                            class="w-full px-4 py-3 border-2 border-gray-100 rounded-xl focus:border-indigo-500 focus:ring-0 outline-none transition-all">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Kategori Khusus (S&K)</label>
                        <select name="category_id" id="vCategoryId"
                            class="w-full px-4 py-3 border-2 border-gray-100 rounded-xl focus:border-indigo-500 focus:ring-0 outline-none transition-all text-sm">
                            <option value="">Semua Kategori (Global)</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-[10px] text-gray-400 font-medium italic">* Voucher hanya bisa digunakan jika ada item dari kategori ini di keranjang.</p>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Target User ID (Opsional)</label>
                        <input type="number" name="target_user_id" id="vTargetId" placeholder="Kosong = Semua User"
                            class="w-full px-4 py-3 border-2 border-gray-100 rounded-xl focus:border-indigo-500 focus:ring-0 outline-none transition-all text-sm">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Syarat & Ketentuan</label>
                        <textarea name="terms" id="vTerms" rows="3" placeholder="Contoh: Berlaku khusus produk tertentu, dsb."
                            class="w-full px-4 py-3 border-2 border-gray-100 rounded-xl focus:border-indigo-500 focus:ring-0 outline-none transition-all text-sm"></textarea>
                    </div>

                    <div class="md:col-span-2 py-2">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" id="vIsActive" value="1" checked
                                class="w-5 h-5 text-indigo-600 border-2 border-gray-200 rounded focus:ring-indigo-500">
                            <span class="ml-3 text-sm font-bold text-gray-700">Aktifkan Voucher Ini</span>
                        </label>
                    </div>
                </div>

                <div class="mt-8 flex gap-3">
                    <button type="button" onclick="closeModal()" class="flex-1 px-4 py-3 text-sm font-bold text-gray-400 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors uppercase tracking-widest">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-3 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-xl shadow-indigo-200 transition-all uppercase tracking-widest">Simpan Voucher</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function closeModal() {
        document.getElementById('voucherModal').classList.add('hidden');
        document.getElementById('voucherForm').reset();
        document.getElementById('voucherForm').action = "{{ route('admin.vouchers.store') }}";
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('modalTitle').innerText = 'Tambah Voucher';
    }

    function openAddModal() {
        document.getElementById('voucherModal').classList.remove('hidden');
        document.getElementById('modalTitle').innerText = 'Tambah Voucher';
        document.getElementById('voucherForm').action = "{{ route('admin.vouchers.store') }}";
        document.getElementById('formMethod').value = 'POST';
    }

    function editVoucher(voucher) {
        document.getElementById('voucherModal').classList.remove('hidden');
        document.getElementById('modalTitle').innerText = 'Edit Voucher';
        document.getElementById('voucherForm').action = `/admin/vouchers/${voucher.id}`;
        document.getElementById('formMethod').value = 'PUT';
        
        document.getElementById('vCode').value = voucher.code;
        document.getElementById('vType').value = voucher.discount_type;
        document.getElementById('vAmount').value = voucher.discount_amount;
        document.getElementById('vMaxAmount').value = voucher.max_discount_amount || '';
        document.getElementById('vMinPurchase').value = voucher.min_purchase;
        document.getElementById('vLimit').value = voucher.usage_limit;
        document.getElementById('vTargetId').value = voucher.target_user_id || '';
        document.getElementById('vCategoryId').value = voucher.category_id || '';
        document.getElementById('vTerms').value = voucher.terms || '';
        document.getElementById('vIsActive').checked = voucher.is_active == 1;
    }
</script>
@endpush
@endsection