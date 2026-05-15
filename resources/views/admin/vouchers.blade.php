@extends('layouts.admin')

@section('title', 'Manajemen Voucher')

@section('content')
<div class="pt-0 pb-2">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
        <div>
            <h1 class="text-4xl font-black tracking-tighter uppercase italic text-slate-800">Kontrol Voucher</h1>
            <p class="text-slate-500 mt-1 font-mono text-xs uppercase tracking-widest">Manajemen Kampanye & Parameter Diskon</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button onclick="openAddModal()" class="px-6 py-3 btn-gradient-amber text-white rounded-xl text-sm font-black uppercase tracking-tighter italic flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                Buat Voucher Baru
            </button>
        </div>
    </div>

    <!-- Voucher Table - Glassmorphism -->
    <div class="glass-card overflow-hidden mb-12">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="glass-table-header text-slate-600 font-black uppercase">
                    <tr>
                        <th class="px-8 py-6">Kode</th>
                        <th class="px-8 py-6">Logika Diskon</th>
                        <th class="px-8 py-6">Min. Pembelian</th>
                        <th class="px-8 py-6">Alokasi</th>
                        <th class="px-8 py-6">Status</th>
                        <th class="px-8 py-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-indigo-50 font-bold">
                    @forelse($vouchers as $v)
                    <tr class="hover:bg-white/40 transition-all">
                        <td class="px-8 py-6 whitespace-nowrap">
                            <span class="px-3 py-1 border border-indigo-100 bg-gradient-to-r from-amber-500 to-orange-500 text-white font-black text-sm uppercase tracking-widest rounded-lg">{{ $v->code }}</span>
                            <div class="mt-2 text-[9px] text-slate-400 font-mono uppercase tracking-tighter">{{ $v->name }}</div>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            <div class="font-black text-sm text-slate-800 uppercase">
                                {{ $v->discount_type == 'percent' ? $v->discount_amount . '%' : 'Rp ' . number_format($v->discount_amount, 0, ',', '.') }}
                            </div>
                            @if($v->discount_type == 'percent' && $v->max_discount_amount)
                                <div class="text-[9px] font-mono text-slate-400 uppercase mt-1">Maks: Rp {{ number_format($v->max_discount_amount, 0, ',', '.') }}</div>
                            @endif
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap font-mono text-slate-800">
                            Rp {{ number_format($v->min_purchase, 0, ',', '.') }}
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            <div class="flex flex-col gap-2">
                                <div class="flex justify-between text-[10px] font-black uppercase tracking-tighter">
                                    <span>Terpakai: {{ $v->usage_count }}</span>
                                    <span>Batas: {{ $v->usage_limit }}</span>
                                </div>
                                <div class="w-32 bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-gradient-to-r from-amber-400 to-orange-500 h-full rounded-full" style="width: {{ min(100, ($v->usage_count / $v->usage_limit) * 100) }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            <div class="flex flex-col gap-2 items-start">
                                <span class="{{ $v->is_active ? 'badge-active' : 'badge-inactive' }}">
                                    {{ $v->is_active ? 'AKTIF' : 'NONAKTIF' }}
                                </span>
                                @if($v->target_user_id)
                                    <span class="text-[9px] font-mono text-slate-400 uppercase italic">Target: #{{ $v->target_user_id }}</span>
                                @else
                                    <span class="text-[9px] font-mono text-slate-400 uppercase">Semua Pengguna</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap text-right">
                            <div class="flex justify-end gap-3">
                                <button onclick='editVoucher(@json($v))' class="px-4 py-2 btn-outline rounded-xl text-[10px] font-black uppercase italic">Edit</button>
                                <form action="{{ route('admin.vouchers.destroy', $v) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus voucher {{ $v->code }} dari sistem?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-4 py-2 btn-danger text-white rounded-xl text-[10px] font-black uppercase">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-8 py-24 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 border border-indigo-100 flex items-center justify-center font-black text-2xl mb-4 italic rounded-xl">?</div>
                                <p class="text-xs font-black uppercase tracking-widest text-slate-400 italic">Belum Ada Voucher</p>
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
<div id="voucherModal" class="fixed inset-0 bg-black/60 hidden z-50 overflow-y-auto transition-all duration-200 opacity-0 backdrop-blur-sm">
    <div class="min-h-screen px-4 text-center flex items-center justify-center">
        <div class="inline-block w-full max-w-2xl p-0 my-8 text-left align-middle bg-white rounded-2xl transform transition-all overflow-hidden border border-white/50 shadow-2xl">

            <!-- Header Modal -->
            <div class="gradient-header-amber p-8 text-white flex justify-between items-center rounded-t-2xl">
                <div>
                    <h3 class="text-3xl font-black uppercase italic tracking-tighter" id="modalTitle">Konfigurasi Voucher</h3>
                    <p class="text-white/70 text-[10px] mt-1 font-mono uppercase tracking-widest">Atur Parameter Diskon</p>
                </div>
                <button onclick="closeModal()" class="border border-white/50 w-10 h-10 flex items-center justify-center hover:bg-white/20 transition-all rounded-lg">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form id="voucherForm" action="{{ route('admin.vouchers.store') }}" method="POST" class="p-10 bg-white">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="space-y-12">
                    <!-- Section 1: Identitas -->
                    <div>
                        <div class="flex items-center gap-4 mb-8">
                            <span class="w-8 h-8 bg-gradient-to-br from-amber-400 to-orange-500 text-white flex items-center justify-center font-black italic rounded-lg">01</span>
                            <h4 class="font-black uppercase text-lg italic tracking-tighter text-slate-800">Identitas Voucher</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-slate-500">Nama Kampanye</label>
                                <input type="text" name="name" id="vName" required placeholder="Contoh: Promo Lebaran 2026"
                                    class="w-full px-5 py-4 bg-white border border-indigo-100 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 rounded-xl outline-none transition-all font-black uppercase italic text-lg shadow-lg">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-slate-500">Kode Voucher</label>
                                <input type="text" name="code" id="vCode" required placeholder="SALE50"
                                    class="w-full px-5 py-4 bg-white border border-indigo-100 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 rounded-xl outline-none transition-all font-black tracking-widest uppercase text-xl shadow-lg">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-slate-500">Deskripsi Singkat</label>
                                <input type="text" name="description" id="vDescription" placeholder="Contoh: Hemat 10ribu untuk semua produk"
                                    class="w-full px-5 py-4 bg-white border border-indigo-100 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 rounded-xl outline-none transition-all text-sm font-bold uppercase shadow-lg">
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Skema Diskon -->
                    <div>
                        <div class="flex items-center gap-4 mb-8">
                            <span class="w-8 h-8 bg-gradient-to-br from-amber-400 to-orange-500 text-white flex items-center justify-center font-black italic rounded-lg">02</span>
                            <h4 class="font-black uppercase text-lg italic tracking-tighter text-slate-800">Skema Nilai</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-slate-500">Tipe Diskon</label>
                                <select name="discount_type" id="vType" required onchange="toggleDiscountFields(); generateTerms();"
                                    class="w-full px-5 py-4 bg-white border border-indigo-100 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 rounded-xl outline-none transition-all font-black uppercase italic shadow-lg">
                                    <option value="fixed">NOMINAL TETAP (IDR)</option>
                                    <option value="percent">PERSENTASE (%)</option>
                                </select>
                            </div>

                            <div>
                                <label id="vAmountLabel" class="block text-[10px] font-black uppercase mb-3 tracking-widest text-slate-500">Nilai Diskon</label>
                                <div class="relative">
                                    <span id="vAmountPrefix" class="absolute left-5 top-4.5 font-black text-lg">Rp</span>
                                    <input type="number" name="discount_amount" id="vAmount" required placeholder="0" oninput="generateTerms()"
                                        class="w-full pl-14 pr-5 py-4 bg-white border border-indigo-100 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 rounded-xl outline-none transition-all font-black text-2xl shadow-lg">
                                    <span id="vAmountSuffix" class="absolute right-5 top-4.5 font-black text-xl hidden">%</span>
                                </div>
                            </div>

                            <div id="maxDiscountRow" class="hidden">
                                <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-red-600">Maksimum Diskon (IDR)</label>
                                <input type="number" name="max_discount_amount" id="vMaxAmount" placeholder="0" oninput="generateTerms()"
                                    class="w-full px-5 py-4 bg-white border border-red-200 focus:ring-2 focus:ring-red-300 focus:border-red-400 rounded-xl outline-none transition-all font-black text-xl shadow-lg">
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-slate-500">Minimal Pembelian (IDR)</label>
                                <input type="number" name="min_purchase" id="vMinPurchase" required value="0" oninput="generateTerms()"
                                    class="w-full px-5 py-4 bg-white border border-indigo-100 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 rounded-xl outline-none transition-all font-black text-xl shadow-lg">
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Kuota & Periode -->
                    <div>
                        <div class="flex items-center gap-4 mb-8">
                            <span class="w-8 h-8 bg-gradient-to-br from-amber-400 to-orange-500 text-white flex items-center justify-center font-black italic rounded-lg">03</span>
                            <h4 class="font-black uppercase text-lg italic tracking-tighter text-slate-800">Alokasi & Periode</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-slate-500">Kuota Per Pengguna</label>
                                <input type="number" name="usage_limit" id="vLimit" required value="1"
                                    class="w-full px-5 py-4 border border-indigo-100 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 rounded-xl font-black text-lg shadow-lg">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-slate-500">Kuota Global Sistem</label>
                                <input type="number" name="quota_total" id="vQuotaTotal" required value="100"
                                    class="w-full px-5 py-4 border border-indigo-100 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 rounded-xl font-black text-lg shadow-lg">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-slate-500">Tanggal Mulai</label>
                                <input type="datetime-local" name="start_date" id="vStartDate"
                                    class="w-full px-5 py-4 border border-indigo-100 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 rounded-xl font-black text-xs uppercase shadow-lg">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-slate-500">Tanggal Berakhir</label>
                                <input type="datetime-local" name="end_date" id="vEndDate" oninput="generateTerms()"
                                    class="w-full px-5 py-4 border border-indigo-100 focus:ring-2 focus:ring-indigo-300 focus:border-indigo-400 rounded-xl font-black text-xs uppercase shadow-lg">
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: S&K -->
                    <div>
                        <div class="flex items-center gap-4 mb-8">
                            <span class="w-8 h-8 bg-gradient-to-br from-amber-400 to-orange-500 text-white flex items-center justify-center font-black italic rounded-lg">04</span>
                            <h4 class="font-black uppercase text-lg italic tracking-tighter text-slate-800">Syarat & Ketentuan</h4>
                        </div>
                        <textarea name="terms" id="vTerms" rows="4" placeholder="Akan diisi otomatis berdasarkan input di atas..."
                            class="w-full px-6 py-5 bg-white/40 border border-indigo-100 rounded-xl outline-none transition-all text-xs font-black italic uppercase tracking-tighter shadow-lg"></textarea>
                        <p class="text-[9px] text-slate-400 mt-3 font-mono uppercase tracking-widest italic">* Sistem akan mengisi otomatis berdasarkan input di atas</p>
                    </div>

                    <!-- Status -->
                    <div class="flex items-center gap-6 p-6 border border-indigo-100 bg-white/40 shadow-lg rounded-xl">
                        <input type="checkbox" name="is_active" id="vIsActive" value="1" checked class="w-8 h-8 border border-indigo-100 rounded text-amber-500 focus:ring-0 cursor-pointer">
                        <label class="text-xs font-black uppercase italic tracking-widest cursor-pointer text-slate-800">Voucher ini AKTIF</label>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-16 flex gap-6">
                    <button type="button" onclick="closeModal()" class="flex-1 px-8 py-5 text-sm font-black btn-outline rounded-xl uppercase italic">Batal</button>
                    <button type="submit" class="flex-1 px-8 py-5 text-sm font-black btn-gradient-amber text-white rounded-xl uppercase italic shadow-xl">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function generateTerms() {
        const type = document.getElementById('vType').value;
        const amount = document.getElementById('vAmount').value;
        const minPurchase = document.getElementById('vMinPurchase').value;
        const endDate = document.getElementById('vEndDate').value;
        const maxAmount = document.getElementById('vMaxAmount').value;
        const termsArea = document.getElementById('vTerms');

        let termsText = "";

        if (amount > 0) {
            if (type === 'percent') {
                termsText += `Diskon ${amount}%`;
                if (maxAmount > 0) {
                    termsText += ` (maks. Rp ${new Number(maxAmount).toLocaleString('id-ID')})`;
                }
            } else {
                termsText += `Potongan Rp ${new Number(amount).toLocaleString('id-ID')}`;
            }
        } else {
            termsText += "Promo Spesial";
        }

        if (minPurchase > 0) {
            termsText += ` untuk pembelian minimal Rp ${new Number(minPurchase).toLocaleString('id-ID')}.`;
        } else {
            termsText += " berlaku untuk semua pembelian.";
        }

        if (endDate) {
            const dateObj = new Date(endDate);
            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            const formattedDate = dateObj.toLocaleDateString('id-ID', options);
            termsText += ` Berlaku hingga ${formattedDate}.`;
        }

        termsText += " Tidak dapat digabung dengan promo lain.";

        termsArea.value = termsText;
    }

    function toggleDiscountFields() {
        const type = document.getElementById('vType').value;
        const maxDiscountRow = document.getElementById('maxDiscountRow');
        const prefix = document.getElementById('vAmountPrefix');
        const suffix = document.getElementById('vAmountSuffix');
        const label = document.getElementById('vAmountLabel');

        if (type === 'percent') {
            maxDiscountRow.classList.remove('hidden');
            prefix.classList.add('hidden');
            suffix.classList.remove('hidden');
            label.innerText = 'PERSENTASE DISKON (%)';
        } else {
            maxDiscountRow.classList.add('hidden');
            prefix.classList.remove('hidden');
            suffix.classList.add('hidden');
            label.innerText = 'NILAI DISKON (IDR)';
        }
    }

    function closeModal() {
        const modal = document.getElementById('voucherModal');
        modal.classList.add('opacity-0');
        setTimeout(() => {
            modal.classList.add('hidden');
            document.getElementById('voucherForm').reset();
            document.getElementById('voucherForm').action = "{{ route('admin.vouchers.store') }}";
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('modalTitle').innerText = 'Konfigurasi Voucher';
            toggleDiscountFields();
        }, 200);
    }

    function openAddModal() {
        const modal = document.getElementById('voucherModal');
        modal.classList.remove('hidden');
        setTimeout(() => modal.classList.remove('opacity-0'), 10);
        document.getElementById('modalTitle').innerText = 'Buat Voucher Baru';
        document.getElementById('voucherForm').action = "{{ route('admin.vouchers.store') }}";
        document.getElementById('formMethod').value = 'POST';
        toggleDiscountFields();
    }

    function editVoucher(voucher) {
        const modal = document.getElementById('voucherModal');
        modal.classList.remove('hidden');
        setTimeout(() => modal.classList.remove('opacity-0'), 10);
        document.getElementById('modalTitle').innerText = 'Edit Voucher';
        document.getElementById('voucherForm').action = `/admin/vouchers/${voucher.id}`;
        document.getElementById('formMethod').value = 'PUT';

        document.getElementById('vName').value = voucher.name || '';
        document.getElementById('vCode').value = voucher.code;
        document.getElementById('vType').value = voucher.discount_type;
        document.getElementById('vAmount').value = voucher.discount_amount;
        document.getElementById('vMaxAmount').value = voucher.max_discount_amount || '';
        document.getElementById('vMinPurchase').value = voucher.min_purchase;
        document.getElementById('vLimit').value = voucher.usage_limit;
        document.getElementById('vQuotaTotal').value = voucher.quota_total || 100;

        if (voucher.start_date) {
            document.getElementById('vStartDate').value = new Date(voucher.start_date).toISOString().slice(0, 16);
        }
        if (voucher.end_date) {
            document.getElementById('vEndDate').value = new Date(voucher.end_date).toISOString().slice(0, 16);
        }

        document.getElementById('vDescription').value = voucher.description || '';
        document.getElementById('vTerms').value = voucher.terms || '';
        document.getElementById('vIsActive').checked = voucher.is_active == 1;

        toggleDiscountFields();
    }

    window.onload = function() {
        toggleDiscountFields();
    };
</script>
@endpush
@endsection
