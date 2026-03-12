@extends('layouts.admin')

@section('content')
    <div class="py-6">
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight mb-6">Kelola Metode Pembayaran</h1>

        @if(session('success'))
            <div class="alert alert-success mb-4">{{ session('success') }}</div>
        @endif

        <!-- Form Tambah Metode Pembayaran -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-8">
            <h2 class="font-bold text-gray-900 mb-4">Tambah Metode Pembayaran Baru</h2>
            <form action="{{ route('admin.payment_methods.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kode *</label>
                        <input type="text" name="code" class="w-full rounded-lg border-gray-300" placeholder="bank_bca"
                            required>
                        <small class="text-gray-500">Format: bank_bca, gopay, dll</small>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama *</label>
                        <input type="text" name="name" class="w-full rounded-lg border-gray-300"
                            placeholder="BCA Virtual Account" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Rekening</label>
                        <input type="text" name="account_number" class="w-full rounded-lg border-gray-300"
                            placeholder="1234567890">
                        <small class="text-gray-500">Nomor rekening atau akun</small>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipe *</label>
                        <select name="type" class="w-full rounded-lg border-gray-300" required>
                            <option value="bank_transfer">Transfer Bank</option>
                            <option value="ewallet">E-Wallet</option>
                            <option value="qris">QRIS</option>
                            <option value="credit_card">Kartu Kredit</option>
                            <option value="cod">Bayar di Tempat</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Icon (Emoji)</label>
                        <input type="text" name="icon" class="w-full rounded-lg border-gray-300" placeholder="🏦"
                            maxlength="10">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Biaya Admin (Rp)</label>
                        <input type="number" name="admin_fee" class="w-full rounded-lg border-gray-300" placeholder="0"
                            min="0" step="0.01">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Biaya Admin (%)</label>
                        <input type="number" name="admin_fee_percent" class="w-full rounded-lg border-gray-300"
                            placeholder="0" min="0" max="100" step="0.01">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                        <input type="number" name="sort_order" class="w-full rounded-lg border-gray-300" placeholder="0"
                            value="0">
                    </div>
                    <div class="flex items-center pt-6">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_active" value="1" checked class="rounded border-gray-300">
                            <span class="text-sm font-medium text-gray-700">Aktif</span>
                        </label>
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Instruksi Pembayaran</label>
                    <textarea name="instructions" class="w-full rounded-lg border-gray-300" rows="2"
                        placeholder="Cara pembayaran..."></textarea>
                </div>
                <div class="mt-4">
                    <button type="submit"
                        class="bg-indigo-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-500/20 transition">
                        Simpan Metode Pembayaran
                    </button>
                </div>
            </form>
        </div>

        <!-- Daftar Metode Pembayaran -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left text-sm text-gray-500">
                <thead class="bg-gray-50 text-gray-600 uppercase text-[11px] font-bold tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Metode</th>
                        <th class="px-6 py-4">Nomor Rekening/Akun</th>
                        <th class="px-6 py-4">Tipe</th>
                        <th class="px-6 py-4">Fee</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($paymentMethods as $pm)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <span class="text-2xl">{{ $pm->icon ?? $pm->type_icon }}</span>
                                    <div>
                                        <div class="font-bold text-gray-900">{{ $pm->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $pm->code }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="font-mono text-sm {{ $pm->account_number ? 'text-gray-900 font-semibold' : 'text-gray-400' }}">
                                    {{ $pm->account_number ?? '-' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-blue-100 text-blue-700">
                                    {{ $pm->type_label }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs">
                                @if($pm->admin_fee > 0)
                                    <div>+ Rp {{ number_format($pm->admin_fee, 0, ',', '.') }}</div>
                                @endif
                                @if($pm->admin_fee_percent > 0)
                                    <div>+ {{ $pm->admin_fee_percent }}%</div>
                                @endif
                                @if($pm->admin_fee == 0 && $pm->admin_fee_percent == 0)
                                    <span class="text-gray-400">Gratis</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $pm->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $pm->is_active ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex gap-2">
                                    <button onclick='editPaymentMethod(@json($pm))'
                                        class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        Edit
                                    </button>
                                    <form action="{{ route('admin.payment_methods.destroy', $pm) }}" method="POST"
                                        onsubmit="return confirm('Yakin hapus metode pembayaran ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                Belum ada metode pembayaran. Silakan tambahkan metode pembayaran baru.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
        <div class="bg-white rounded-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="p-6">
                    <h3 class="text-xl font-bold text-gray-900 mb-4">Edit Metode Pembayaran</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Kode *</label>
                            <input type="text" name="code" id="edit_code" class="w-full rounded-lg border-gray-300"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama *</label>
                            <input type="text" name="name" id="edit_name" class="w-full rounded-lg border-gray-300"
                                required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Rekening/Akun</label>
                            <input type="text" name="account_number" id="edit_account_number"
                                class="w-full rounded-lg border-gray-300">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tipe *</label>
                            <select name="type" id="edit_type" class="w-full rounded-lg border-gray-300" required>
                                <option value="bank_transfer">Transfer Bank</option>
                                <option value="ewallet">E-Wallet</option>
                                <option value="qris">QRIS</option>
                                <option value="credit_card">Kartu Kredit</option>
                                <option value="cod">Bayar di Tempat</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Icon</label>
                            <input type="text" name="icon" id="edit_icon" class="w-full rounded-lg border-gray-300"
                                maxlength="10">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Biaya Admin (Rp)</label>
                            <input type="number" name="admin_fee" id="edit_admin_fee"
                                class="w-full rounded-lg border-gray-300" min="0" step="0.01">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Biaya Admin (%)</label>
                            <input type="number" name="admin_fee_percent" id="edit_admin_fee_percent"
                                class="w-full rounded-lg border-gray-300" min="0" max="100" step="0.01">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Urutan</label>
                            <input type="number" name="sort_order" id="edit_sort_order"
                                class="w-full rounded-lg border-gray-300">
                        </div>
                        <div class="flex items-center pt-6">
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="is_active" id="edit_is_active" value="1"
                                    class="rounded border-gray-300">
                                <span class="text-sm font-medium text-gray-700">Aktif</span>
                            </label>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Instruksi Pembayaran</label>
                        <textarea name="instructions" id="edit_instructions" class="w-full rounded-lg border-gray-300"
                            rows="2"></textarea>
                    </div>

                    <div class="flex gap-3 mt-6">
                        <button type="submit"
                            class="bg-indigo-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-indigo-700">
                            Update
                        </button>
                        <button type="button" onclick="closeEditModal()"
                            class="bg-gray-200 text-gray-700 px-6 py-2 rounded-xl font-bold hover:bg-gray-300">
                            Batal
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            function editPaymentMethod(pm) {
                document.getElementById('editForm').action = `/admin/payment-methods/${pm.id}`;
                document.getElementById('edit_code').value = pm.code || '';
                document.getElementById('edit_name').value = pm.name || '';
                document.getElementById('edit_account_number').value = pm.account_number || '';
                document.getElementById('edit_type').value = pm.type || 'bank_transfer';
                document.getElementById('edit_icon').value = pm.icon || '';
                document.getElementById('edit_admin_fee').value = pm.admin_fee || 0;
                document.getElementById('edit_admin_fee_percent').value = pm.admin_fee_percent || 0;
                document.getElementById('edit_sort_order').value = pm.sort_order || 0;
                document.getElementById('edit_is_active').checked = pm.is_active == 1;
                document.getElementById('edit_instructions').value = pm.instructions || '';
                document.getElementById('editModal').classList.remove('hidden');
            }

            function closeEditModal() {
                document.getElementById('editModal').classList.add('hidden');
            }
        </script>
    @endpush
@endsection