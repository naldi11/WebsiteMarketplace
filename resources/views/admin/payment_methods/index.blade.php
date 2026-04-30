@extends('layouts.admin')

@section('title', 'Kelola Metode Pembayaran')

@section('content')
<div class="px-4 sm:px-6 lg:px-8 py-8">
    <div class="sm:flex sm:items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Kelola Metode Pembayaran</h1>
            <p class="mt-2 text-sm text-gray-700">Atur cara pembayaran yang tersedia untuk pelanggan di aplikasi.</p>
        </div>
        <div class="mt-4 sm:mt-0 sm:ml-16">
            <button onclick="openAddModal()" class="inline-flex items-center justify-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Metode
            </button>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 p-4 bg-green-50 border-l-4 border-green-400 text-green-700 text-sm font-medium rounded-r-md">
            {{ session('success') }}
        </div>
    @endif

    <!-- Payment Methods Table -->
    <div class="bg-white shadow overflow-hidden sm:rounded-lg border border-gray-200">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50 text-gray-500 uppercase text-[11px] font-bold tracking-wider">
                <tr>
                    <th class="px-6 py-4 text-left">Metode</th>
                    <th class="px-6 py-4 text-left">Rekening/Akun</th>
                    <th class="px-6 py-4 text-left">Tipe</th>
                    <th class="px-6 py-4 text-left">Biaya Admin</th>
                    <th class="px-6 py-4 text-left">Status</th>
                    <th class="px-6 py-4 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                @forelse($paymentMethods as $pm)
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                            <div class="h-10 w-10 flex-shrink-0 flex items-center justify-center bg-indigo-50 text-indigo-600 rounded-xl {{ strlen($pm->icon ?? '') > 4 ? 'text-[8px]' : (strlen($pm->icon ?? '') > 2 ? 'text-xs' : 'text-xl') }} font-black overflow-hidden border border-indigo-100 italic px-0.5 text-center uppercase break-all leading-none">
                                {{ $pm->icon ?? $pm->type_icon }}
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-bold text-gray-900 leading-tight">{{ $pm->name }}</div>
                                <div class="text-[10px] text-gray-400 font-mono tracking-tighter uppercase">{{ $pm->code }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        <span class="font-mono bg-gray-50 px-2 py-1 rounded border border-gray-100 text-xs {{ $pm->account_number ? 'text-gray-900 font-bold' : 'text-gray-300' }}">
                            {{ $pm->account_number ?? 'N/A' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-100">
                            {{ $pm->type_label }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                        @if($pm->admin_fee > 0 || $pm->admin_fee_percent > 0)
                            <div class="flex flex-col text-[10px] text-gray-700 font-bold">
                                @if($pm->admin_fee > 0)
                                    <span>Rp {{ number_format($pm->admin_fee, 0, ',', '.') }}</span>
                                @endif
                                @if($pm->admin_fee_percent > 0)
                                    <span class="text-indigo-500">+{{ floatval($pm->admin_fee_percent) }}%</span>
                                @endif
                            </div>
                        @else
                            <span class="text-gray-300 italic text-[10px]">Gratis</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $pm->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                            {{ $pm->is_active ? 'Aktif' : 'Non-Aktif' }}
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm">
                        <div class="flex justify-end gap-3 font-bold uppercase tracking-widest text-[10px]">
                            <button onclick='editPaymentMethod(@json($pm))' class="text-indigo-600 hover:text-indigo-800 transition">Edit</button>
                            <form action="{{ route('admin.payment_methods.destroy', $pm) }}" method="POST" class="inline-block" onsubmit="return confirm('Yakin ingin menghapus metode {{ $pm->name }}?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-400 hover:text-red-600 transition">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center">
                        <div class="text-gray-400 mb-2 text-3xl">📭</div>
                        <p class="text-gray-500 font-medium">Belum ada metode pembayaran.</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Tambah/Edit -->
<div id="paymentModal" class="fixed inset-0 bg-gray-900/50 backdrop-blur-sm hidden z-50 overflow-y-auto">
    <div class="min-h-screen px-4 text-center">
        <span class="inline-block h-screen align-middle" aria-hidden="true">&#8203;</span>
        
        <div class="inline-block w-full max-w-xl p-8 my-8 text-left align-middle bg-white shadow-2xl rounded-3xl transform transition-all border border-gray-100">
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h3 class="text-2xl font-black text-gray-900 tracking-tighter" id="modalTitle">Tambah Metode</h3>
                    <p class="text-gray-500 text-sm mt-1">Konfigurasi opsi pembayaran pelanggan.</p>
                </div>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 p-2 bg-gray-50 rounded-full transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form id="paymentForm" action="{{ route('admin.payment_methods.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Kode Metode</label>
                        <input type="text" name="code" id="pCode" required placeholder="CTH: bank_bca"
                            class="w-full px-4 py-3 border-2 border-gray-100 rounded-xl focus:border-indigo-500 focus:ring-0 outline-none transition-all font-bold text-sm">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Nama Metode</label>
                        <input type="text" name="name" id="pName" required placeholder="CTH: BCA Transfer"
                            class="w-full px-4 py-3 border-2 border-gray-100 rounded-xl focus:border-indigo-500 focus:ring-0 outline-none transition-all font-bold text-sm">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Nomor Rekening / Akun</label>
                        <input type="text" name="account_number" id="pAccountNumber" placeholder="1234xxx (Opsional)"
                            class="w-full px-4 py-3 border-2 border-gray-100 rounded-xl focus:border-indigo-500 focus:ring-0 outline-none transition-all font-mono text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Tipe</label>
                        <select name="type" id="pType" required
                            class="w-full px-4 py-3 border-2 border-gray-100 rounded-xl focus:border-indigo-500 focus:ring-0 outline-none transition-all text-sm">
                            <option value="bank_transfer">Transfer Bank</option>
                            <option value="ewallet">E-Wallet</option>
                            <option value="qris">QRIS</option>
                            <option value="credit_card">Kartu Kredit</option>
                            <option value="cod">Bayar di Tempat (COD)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Icon (Emoji)</label>
                        <input type="text" name="icon" id="pIcon" placeholder="🏦" maxlength="10"
                            class="w-full px-4 py-3 border-2 border-gray-100 rounded-xl focus:border-indigo-500 focus:ring-0 outline-none transition-all text-center">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Biaya Admin (Rp)</label>
                        <input type="number" name="admin_fee" id="pAdminFee" value="0" step="0.01"
                            class="w-full px-4 py-3 border-2 border-gray-100 rounded-xl focus:border-indigo-500 focus:ring-0 outline-none transition-all text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Biaya Admin (%)</label>
                        <input type="number" name="admin_fee_percent" id="pAdminFeePercent" value="0" step="0.01" max="100"
                            class="w-full px-4 py-3 border-2 border-gray-100 rounded-xl focus:border-indigo-500 focus:ring-0 outline-none transition-all text-sm">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Urutan Tampilan</label>
                        <input type="number" name="sort_order" id="pSortOrder" value="0"
                            class="w-full px-4 py-3 border-2 border-gray-100 rounded-xl focus:border-indigo-500 focus:ring-0 outline-none transition-all text-sm">
                    </div>

                    <div class="flex items-center pt-6">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="is_active" id="pIsActive" value="1" checked
                                class="w-5 h-5 text-indigo-600 border-2 border-gray-200 rounded focus:ring-indigo-500 transition">
                            <span class="ml-3 text-sm font-bold text-gray-700">Aktifkan Metode Ini</span>
                        </label>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Instruksi Pembayaran</label>
                        <textarea name="instructions" id="pInstructions" rows="3" placeholder="Langkah-langkah pembayaran untuk pelanggan..."
                            class="w-full px-4 py-3 border-2 border-gray-100 rounded-xl focus:border-indigo-500 focus:ring-0 outline-none transition-all text-sm"></textarea>
                    </div>
                </div>

                <div class="mt-8 flex gap-3">
                    <button type="button" onclick="closeModal()" class="flex-1 px-4 py-3 text-sm font-bold text-gray-400 bg-gray-50 rounded-xl hover:bg-gray-100 transition-colors uppercase tracking-widest">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-3 text-sm font-bold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 shadow-xl shadow-indigo-200 transition-all uppercase tracking-widest">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function closeModal() {
        document.getElementById('paymentModal').classList.add('hidden');
        document.getElementById('paymentForm').reset();
        document.getElementById('paymentForm').action = "{{ route('admin.payment_methods.store') }}";
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('modalTitle').innerText = 'Tambah Metode';
    }

    function openAddModal() {
        document.getElementById('paymentModal').classList.remove('hidden');
        document.getElementById('modalTitle').innerText = 'Tambah Metode';
        document.getElementById('paymentForm').action = "{{ route('admin.payment_methods.store') }}";
        document.getElementById('formMethod').value = 'POST';
    }

    function editPaymentMethod(pm) {
        document.getElementById('paymentModal').classList.remove('hidden');
        document.getElementById('modalTitle').innerText = 'Edit Metode';
        document.getElementById('paymentForm').action = `/admin/payment-methods/${pm.id}`;
        document.getElementById('formMethod').value = 'PUT';
        
        document.getElementById('pCode').value = pm.code || '';
        document.getElementById('pName').value = pm.name || '';
        document.getElementById('pAccountNumber').value = pm.account_number || '';
        document.getElementById('pType').value = pm.type || 'bank_transfer';
        document.getElementById('pIcon').value = pm.icon || '';
        document.getElementById('pAdminFee').value = pm.admin_fee || 0;
        document.getElementById('pAdminFeePercent').value = pm.admin_fee_percent || 0;
        document.getElementById('pSortOrder').value = pm.sort_order || 0;
        document.getElementById('pInstructions').value = pm.instructions || '';
        document.getElementById('pIsActive').checked = (pm.is_active == 1 || pm.is_active == true);
    }
</script>
@endpush
@endsection
ion