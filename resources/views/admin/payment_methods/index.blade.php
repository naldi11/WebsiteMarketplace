@extends('layouts.admin')

@section('title', 'Metode Pembayaran')

@section('content')
<div class="pt-0 pb-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
        <div>
            <h1 class="text-4xl font-black tracking-tighter uppercase italic text-slate-800">Metode Pembayaran</h1>
            <p class="text-slate-500 mt-1 font-mono text-xs uppercase tracking-widest">Manajemen Gateway Transaksi</p>
        </div>
        <button onclick="openAddModal()" class="px-6 py-3 btn-gradient-teal text-white rounded-xl text-sm font-black uppercase tracking-tighter italic flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Metode Pembayaran
        </button>
    </div>

    <!-- Payment Methods Table - Glassmorphism -->
    <div class="glass-card overflow-hidden mb-12">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="glass-table-header text-slate-600 font-black uppercase">
                    <tr>
                        <th class="px-8 py-6">Nama Gateway</th>
                        <th class="px-8 py-6">Nomor Akun</th>
                        <th class="px-8 py-6">Tipe</th>
                        <th class="px-8 py-6">Biaya</th>
                        <th class="px-8 py-6">Status</th>
                        <th class="px-8 py-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-indigo-50 font-bold">
                    @forelse($paymentMethods as $pm)
                    <tr class="hover:bg-white/40 transition-all">
                        <td class="px-8 py-6 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-12 w-12 border border-indigo-100 flex items-center justify-center bg-gradient-to-br from-teal-500 to-cyan-500 text-white text-lg font-black italic rounded-xl">
                                    {{ $pm->icon ?? '💳' }}
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-black text-slate-800 uppercase italic tracking-tighter leading-none">{{ $pm->name }}</div>
                                    <div class="text-[9px] text-slate-400 font-mono tracking-widest uppercase mt-1">{{ $pm->code }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            <span class="font-mono bg-white/60 px-3 py-1 border border-indigo-100 text-[10px] uppercase font-black rounded-lg {{ $pm->account_number ? 'text-slate-800' : 'text-slate-300' }}">
                                {{ $pm->account_number ?? '-' }}
                            </span>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            <span class="px-3 py-1 border border-indigo-100 text-[9px] font-black uppercase tracking-widest bg-slate-50 italic rounded-lg text-slate-800">
                                {{ $pm->type_label }}
                            </span>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            @if($pm->admin_fee > 0 || $pm->admin_fee_percent > 0)
                                <div class="flex flex-col text-[10px] text-slate-800 font-black italic">
                                    @if($pm->admin_fee > 0)
                                        <span>IDR_{{ number_format($pm->admin_fee, 0, ',', '.') }}</span>
                                    @endif
                                    @if($pm->admin_fee_percent > 0)
                                        <span class="text-slate-500">+{{ floatval($pm->admin_fee_percent) }}%</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-slate-300 italic text-[10px] font-mono">GRATIS</span>
                            @endif
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            <span class="{{ $pm->is_active ? 'badge-active' : 'badge-inactive' }}">
                                {{ $pm->is_active ? 'AKTIF' : 'NONAKTIF' }}
                            </span>
                        </td>
                        <td class="px-8 py-6 text-right whitespace-nowrap">
                            <div class="flex justify-end gap-3 font-black uppercase italic text-[10px]">
                                <button onclick='editPaymentMethod(@json($pm))' class="px-4 py-2 btn-outline rounded-xl">Edit</button>
                                <form action="{{ route('admin.payment_methods.destroy', $pm) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus metode {{ $pm->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-4 py-2 btn-danger text-white rounded-xl">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-8 py-24 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 border border-indigo-100 flex items-center justify-center font-black text-2xl mb-4 italic rounded-xl text-slate-400">!</div>
                                <p class="text-xs font-black uppercase tracking-widest text-slate-400 italic">Belum Ada Metode Pembayaran</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Glassmorphism -->
<div id="paymentModal" class="fixed inset-0 bg-black/60 hidden z-50 overflow-y-auto transition-all duration-200 backdrop-blur-sm">
    <div class="min-h-screen px-4 text-center flex items-center justify-center">
        <div class="inline-block w-full max-w-xl p-0 my-8 text-left align-middle bg-white rounded-2xl transform transition-all border border-white/50 shadow-2xl">

            <div class="gradient-header-teal p-8 text-white flex justify-between items-center rounded-t-2xl">
                <div>
                    <h3 class="text-3xl font-black uppercase italic tracking-tighter" id="modalTitle">Konfigurasi Gateway</h3>
                    <p class="text-white/70 text-[10px] mt-1 font-mono uppercase tracking-widest">Atur Metode Penerimaan Dana</p>
                </div>
                <button onclick="closeModal()" class="border border-white/50 w-10 h-10 flex items-center justify-center hover:bg-white/20 transition-all rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form id="paymentForm" action="{{ route('admin.payment_methods.store') }}" method="POST" class="p-10 bg-white">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-slate-500">Kode</label>
                        <input type="text" name="code" id="pCode" required placeholder="bank_bca"
                            class="w-full px-5 py-4 bg-white border border-indigo-100 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 rounded-xl outline-none transition-all font-black uppercase text-sm shadow-lg">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-slate-500">Nama Tampilan</label>
                        <input type="text" name="name" id="pName" required placeholder="Contoh: Transfer BCA"
                            class="w-full px-5 py-4 bg-white border border-indigo-100 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 rounded-xl outline-none transition-all font-black uppercase italic text-sm shadow-lg">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-slate-500">Nomor Rekening</label>
                        <input type="text" name="account_number" id="pAccountNumber" placeholder="1234xxx"
                            class="w-full px-5 py-4 bg-white border border-indigo-100 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 rounded-xl outline-none transition-all font-mono font-black text-lg shadow-lg">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-slate-500">Tipe Pembayaran</label>
                        <select name="type" id="pType" required
                            class="w-full px-5 py-4 bg-white border border-indigo-100 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 rounded-xl outline-none transition-all font-black uppercase italic text-sm shadow-lg">
                            <option value="bank_transfer">Transfer Bank</option>
                            <option value="ewallet">Dompet Digital</option>
                            <option value="qris">QRIS</option>
                            <option value="credit_card">Kartu Kredit</option>
                            <option value="cod">Bayar di Tempat (COD)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-slate-500">Ikon</label>
                        <input type="text" name="icon" id="pIcon" placeholder="🏦" maxlength="10"
                            class="w-full px-5 py-4 bg-white border border-indigo-100 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 rounded-xl outline-none transition-all text-center text-2xl shadow-lg">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-slate-500">Biaya Tetap (IDR)</label>
                        <input type="number" name="admin_fee" id="pAdminFee" value="0" step="0.01"
                            class="w-full px-5 py-4 bg-white border border-indigo-100 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 rounded-xl outline-none transition-all font-black text-lg shadow-lg">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-slate-500">Biaya Persentase (%)</label>
                        <input type="number" name="admin_fee_percent" id="pAdminFeePercent" value="0" step="0.01" max="100"
                            class="w-full px-5 py-4 bg-white border border-indigo-100 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 rounded-xl outline-none transition-all font-black text-lg shadow-lg">
                    </div>

                    <div class="flex items-center pt-6 md:col-span-2">
                        <label class="flex items-center cursor-pointer gap-4 p-4 border border-indigo-100 bg-white/40 w-full shadow-lg rounded-xl">
                            <input type="checkbox" name="is_active" id="pIsActive" value="1" checked
                                class="w-8 h-8 text-teal-500 border border-indigo-100 rounded focus:ring-0">
                            <span class="text-xs font-black uppercase italic tracking-widest text-slate-800">Metode ini AKTIF</span>
                        </label>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-slate-500">Instruksi Pembayaran</label>
                        <textarea name="instructions" id="pInstructions" rows="3" placeholder="Tulis langkah-langkah cara pembayaran..."
                            class="w-full px-6 py-4 bg-white border border-indigo-100 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 rounded-xl outline-none transition-all font-black italic uppercase text-xs shadow-lg"></textarea>
                    </div>
                </div>

                <div class="mt-12 flex gap-6">
                    <button type="button" onclick="closeModal()" class="flex-1 px-8 py-5 text-sm font-black btn-outline rounded-xl uppercase italic">Batal</button>
                    <button type="submit" class="flex-1 px-8 py-5 text-sm font-black btn-gradient-teal text-white rounded-xl uppercase italic shadow-xl">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function closeModal() {
        const modal = document.getElementById('paymentModal');
        modal.classList.add('hidden');
        document.getElementById('paymentForm').reset();
        document.getElementById('paymentForm').action = "{{ route('admin.payment_methods.store') }}";
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('modalTitle').innerText = 'Konfigurasi Pembayaran';
    }

    function openAddModal() {
        const modal = document.getElementById('paymentModal');
        modal.classList.remove('hidden');
        document.getElementById('modalTitle').innerText = 'Tambah Metode Pembayaran';
        document.getElementById('paymentForm').action = "{{ route('admin.payment_methods.store') }}";
        document.getElementById('formMethod').value = 'POST';
    }

    function editPaymentMethod(pm) {
        const modal = document.getElementById('paymentModal');
        modal.classList.remove('hidden');
        document.getElementById('modalTitle').innerText = 'Edit Metode Pembayaran';
        document.getElementById('paymentForm').action = `/admin/payment-methods/${pm.id}`;
        document.getElementById('formMethod').value = 'PUT';

        document.getElementById('pCode').value = pm.code || '';
        document.getElementById('pName').value = pm.name || '';
        document.getElementById('pAccountNumber').value = pm.account_number || '';
        document.getElementById('pType').value = pm.type || 'bank_transfer';
        document.getElementById('pIcon').value = pm.icon || '';
        document.getElementById('pAdminFee').value = pm.admin_fee || 0;
        document.getElementById('pAdminFeePercent').value = pm.admin_fee_percent || 0;
        document.getElementById('pInstructions').value = pm.instructions || '';
        document.getElementById('pIsActive').checked = (pm.is_active == 1 || pm.is_active == true);
    }
</script>
@endpush
@endsection
