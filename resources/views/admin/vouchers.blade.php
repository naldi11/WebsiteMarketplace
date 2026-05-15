@extends('layouts.admin')

@section('title', 'Voucher Control')

@section('content')
<div class="pt-0 pb-2">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
        <div>
            <h1 class="text-4xl font-black tracking-tighter uppercase italic text-black">Kontrol Voucher</h1>
            <p class="text-gray-500 mt-1 font-mono text-xs uppercase tracking-widest text-black">Manajemen Kampanye & Parameter Diskon</p>
        </div>
        <div class="mt-4 sm:mt-0">
            <button onclick="openAddModal()" class="px-6 py-3 bg-black text-white border-[3px] border-black text-sm font-black uppercase tracking-tighter hover:bg-white hover:text-black transition-all neo-brutalism italic flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path></svg>
                Buat Voucher Baru
            </button>
        </div>
    </div>

    <!-- Voucher Table - Neo Brutalism -->
    <div class="bg-white border-[3px] border-black neo-brutalism overflow-hidden mb-12">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-100 border-b-[3px] border-black text-black font-black uppercase italic">
                    <tr>
                        <th class="px-8 py-6">Kode</th>
                        <th class="px-8 py-6">Logika Diskon</th>
                        <th class="px-8 py-6">Min. Pembelian</th>
                        <th class="px-8 py-6">Alokasi</th>
                        <th class="px-8 py-6">Status</th>
                        <th class="px-8 py-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-gray-100 font-bold">
                    @forelse($vouchers as $v)
                    <tr class="hover:bg-gray-50 transition-all">
                        <td class="px-8 py-6 whitespace-nowrap">
                            <span class="px-3 py-1 border-2 border-black bg-black text-white font-black text-sm uppercase tracking-widest">{{ $v->code }}</span>
                            <div class="mt-2 text-[9px] text-gray-400 font-mono uppercase tracking-tighter">{{ $v->name }}</div>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            <div class="font-black text-sm text-black uppercase">
                                {{ $v->discount_type == 'percent' ? $v->discount_amount . '%' : 'Rp ' . number_format($v->discount_amount, 0, ',', '.') }}
                            </div>
                            @if($v->discount_type == 'percent' && $v->max_discount_amount)
                                <div class="text-[9px] font-mono text-gray-400 uppercase mt-1">CAP: Rp {{ number_format($v->max_discount_amount, 0, ',', '.') }}</div>
                            @endif
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap font-mono text-black">
                            Rp {{ number_format($v->min_purchase, 0, ',', '.') }}
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            <div class="flex flex-col gap-2">
                                <div class="flex justify-between text-[10px] font-black uppercase tracking-tighter">
                                    <span>Terpakai: {{ $v->usage_count }}</span>
                                    <span>Batas: {{ $v->usage_limit }}</span>
                                </div>
                                <div class="w-32 bg-gray-100 border border-black h-2 overflow-hidden">
                                    <div class="bg-black h-full" style="width: {{ min(100, ($v->usage_count / $v->usage_limit) * 100) }}%"></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            <div class="flex flex-col gap-2 items-start">
                                <span class="px-3 py-1 border-2 border-black text-[9px] font-black uppercase {{ $v->is_active ? 'bg-white text-black' : 'bg-black text-white' }}">
                                    {{ $v->is_active ? 'AKTIF' : 'NONAKTIF' }}
                                </span>
                                @if($v->target_user_id)
                                    <span class="text-[9px] font-mono text-gray-400 uppercase italic">Target: #{{ $v->target_user_id }}</span>
                                @else
                                    <span class="text-[9px] font-mono text-gray-400 uppercase">Global_Access</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap text-right">
                            <div class="flex justify-end gap-3">
                                <button onclick='editVoucher(@json($v))' class="px-4 py-2 border-2 border-black text-[10px] font-black uppercase hover:bg-black hover:text-white transition-all italic">Edit</button>
                                <form action="{{ route('admin.vouchers.destroy', $v) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus voucher {{ $v->code }} dari sistem?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-4 py-2 bg-black text-white border-2 border-black text-[10px] font-black uppercase hover:bg-white hover:text-black transition-all">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-8 py-24 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 border-[3px] border-black flex items-center justify-center font-black text-2xl mb-4 italic">?</div>
                                <p class="text-xs font-black uppercase tracking-widest text-gray-400 italic">Belum Ada Voucher</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Minimalist - Neo Brutalism -->
<div id="voucherModal" class="fixed inset-0 bg-black/60 hidden z-50 overflow-y-auto transition-all duration-200 opacity-0 backdrop-blur-sm">
    <div class="min-h-screen px-4 text-center flex items-center justify-center">
        <div class="inline-block w-full max-w-2xl p-0 my-8 text-left align-middle bg-white rounded-none transform transition-all overflow-hidden border-[4px] border-black shadow-[20px_20px_0px_0px_rgba(0,0,0,1)]">
            
            <!-- Header Modal -->
            <div class="bg-black p-8 text-white flex justify-between items-center">
                <div>
                    <h3 class="text-3xl font-black uppercase italic tracking-tighter" id="modalTitle">Konfigurasi Voucher</h3>
                    <p class="text-gray-400 text-[10px] mt-1 font-mono uppercase tracking-widest">Atur Parameter Diskon</p>
                </div>
                <button onclick="closeModal()" class="border-2 border-white w-10 h-10 flex items-center justify-center hover:bg-white hover:text-black transition-all">
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
                            <span class="w-8 h-8 bg-black text-white flex items-center justify-center font-black italic">01</span>
                            <h4 class="font-black uppercase text-lg italic tracking-tighter">Identitas Voucher</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div class="md:col-span-2">
                                <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-gray-500">Nama Kampanye</label>
                                <input type="text" name="name" id="vName" required placeholder="MEGA_SALE_PROTOCOL"
                                    class="w-full px-5 py-4 bg-white border-[3px] border-black rounded-none focus:bg-gray-50 outline-none transition-all font-black uppercase italic text-lg shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-gray-500">Kode Voucher</label>
                                <input type="text" name="code" id="vCode" required placeholder="SALE50"
                                    class="w-full px-5 py-4 bg-white border-[3px] border-black rounded-none focus:bg-gray-50 outline-none transition-all font-black tracking-widest uppercase text-xl shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-gray-500">Deskripsi Singkat</label>
                                <input type="text" name="description" id="vDescription" placeholder="10K OFF PROTOCOL"
                                    class="w-full px-5 py-4 bg-white border-[3px] border-black rounded-none focus:bg-gray-50 outline-none transition-all text-sm font-bold uppercase shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Skema Diskon -->
                    <div>
                        <div class="flex items-center gap-4 mb-8">
                            <span class="w-8 h-8 bg-black text-white flex items-center justify-center font-black italic">02</span>
                            <h4 class="font-black uppercase text-lg italic tracking-tighter">Skema Nilai</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-gray-500">Tipe Diskon</label>
                                <select name="discount_type" id="vType" required onchange="toggleDiscountFields(); generateTerms();"
                                    class="w-full px-5 py-4 bg-white border-[3px] border-black rounded-none focus:bg-gray-50 outline-none transition-all font-black uppercase italic shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                                    <option value="fixed">FLAT_VALUE (IDR)</option>
                                    <option value="percent">PERCENTAGE (%)</option>
                                </select>
                            </div>

                            <div>
                                <label id="vAmountLabel" class="block text-[10px] font-black uppercase mb-3 tracking-widest text-gray-500">Nilai Diskon</label>
                                <div class="relative">
                                    <span id="vAmountPrefix" class="absolute left-5 top-4.5 font-black text-lg">Rp</span>
                                    <input type="number" name="discount_amount" id="vAmount" required placeholder="0" oninput="generateTerms()"
                                        class="w-full pl-14 pr-5 py-4 bg-white border-[3px] border-black rounded-none focus:bg-gray-50 outline-none transition-all font-black text-2xl shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                                    <span id="vAmountSuffix" class="absolute right-5 top-4.5 font-black text-xl hidden">%</span>
                                </div>
                            </div>

                            <div id="maxDiscountRow" class="hidden">
                                <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-red-600">Max Cap (IDR)</label>
                                <input type="number" name="max_discount_amount" id="vMaxAmount" placeholder="0" oninput="generateTerms()"
                                    class="w-full px-5 py-4 bg-white border-[3px] border-red-600 rounded-none outline-none transition-all font-black text-xl shadow-[4px_4px_0px_0px_rgba(220,38,38,1)]">
                            </div>

                            <div>
                                <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-gray-500">Min. Entry Threshold (IDR)</label>
                                <input type="number" name="min_purchase" id="vMinPurchase" required value="0" oninput="generateTerms()"
                                    class="w-full px-5 py-4 bg-white border-[3px] border-black rounded-none focus:bg-gray-50 outline-none transition-all font-black text-xl shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Kuota & Periode -->
                    <div>
                        <div class="flex items-center gap-4 mb-8">
                            <span class="w-8 h-8 bg-black text-white flex items-center justify-center font-black italic">03</span>
                            <h4 class="font-black uppercase text-lg italic tracking-tighter">Alokasi & Periode</h4>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-gray-500">Kuota Per Pengguna</label>
                                <input type="number" name="usage_limit" id="vLimit" required value="1"
                                    class="w-full px-5 py-4 border-[3px] border-black rounded-none font-black text-lg shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-gray-500">Kuota Global Sistem</label>
                                <input type="number" name="quota_total" id="vQuotaTotal" required value="100"
                                    class="w-full px-5 py-4 border-[3px] border-black rounded-none font-black text-lg shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-gray-500">Tanggal Mulai</label>
                                <input type="datetime-local" name="start_date" id="vStartDate"
                                    class="w-full px-5 py-4 border-[3px] border-black rounded-none font-black text-xs uppercase shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                            </div>
                            <div>
                                <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-gray-500">Tanggal Berakhir</label>
                                <input type="datetime-local" name="end_date" id="vEndDate" oninput="generateTerms()"
                                    class="w-full px-5 py-4 border-[3px] border-black rounded-none font-black text-xs uppercase shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: S&K -->
                    <div>
                        <div class="flex items-center gap-4 mb-8">
                            <span class="w-8 h-8 bg-black text-white flex items-center justify-center font-black italic">04</span>
                            <h4 class="font-black uppercase text-lg italic tracking-tighter">Syarat & Ketentuan</h4>
                        </div>
                        <textarea name="terms" id="vTerms" rows="4" placeholder="Automatic script generation..."
                            class="w-full px-6 py-5 bg-gray-50 border-[3px] border-black rounded-none outline-none transition-all text-xs font-black italic uppercase tracking-tighter shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]"></textarea>
                        <p class="text-[9px] text-gray-400 mt-3 font-mono uppercase tracking-widest italic">* Sistem akan mengisi otomatis berdasarkan input</p>
                    </div>

                    <!-- Status -->
                    <div class="flex items-center gap-6 p-6 border-[3px] border-black bg-gray-50 shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">
                        <input type="checkbox" name="is_active" id="vIsActive" value="1" checked class="w-8 h-8 border-[3px] border-black rounded-none text-black focus:ring-0 cursor-pointer">
                        <label class="text-xs font-black uppercase italic tracking-widest cursor-pointer">Voucher ini AKTIF</label>
                    </div>
                </div>

                <!-- Footer -->
                <div class="mt-16 flex gap-6">
                    <button type="button" onclick="closeModal()" class="flex-1 px-8 py-5 text-sm font-black border-[3px] border-black hover:bg-black hover:text-white transition-all uppercase italic">Batal</button>
                    <button type="submit" class="flex-1 px-8 py-5 text-sm font-black bg-black text-white border-[3px] border-black hover:bg-white hover:text-black transition-all uppercase italic shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">Simpan</button>
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
        
        // 1. Potongan
        if (amount > 0) {
            if (type === 'percent') {
                termsText += `DISCOUNT_${amount}%_INITIATED`;
                if (maxAmount > 0) {
                    termsText += ` (MAX_CAP_IDR_${new Number(maxAmount).toLocaleString('id-ID')})`;
                }
            } else {
                termsText += `VALUE_REDUCTION_IDR_${new Number(amount).toLocaleString('id-ID')}_ACTIVATED`;
            }
        } else {
            termsText += "SPECIAL_PROMO_DEPLOYED";
        }

        // 2. Minimal Belanja
        if (minPurchase > 0) {
            termsText += ` WITHIN_MIN_THRESHOLD_IDR_${new Number(minPurchase).toLocaleString('id-ID')}.`;
        } else {
            termsText += " ZERO_THRESHOLD_MODE.";
        }

        // 3. Tanggal Berakhir
        if (endDate) {
            const dateObj = new Date(endDate);
            const options = { day: 'numeric', month: 'long', year: 'numeric' };
            const formattedDate = dateObj.toLocaleDateString('id-ID', options);
            termsText += ` VALID_UNTIL_${formattedDate.toUpperCase()}.`;
        }

        termsText += " NO_STACK_WITH_OTHER_CAMPAIGNS.";
        
        termsArea.value = termsText.toUpperCase();
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
            label.innerText = 'PERCENT_VECTOR (%)';
        } else {
            maxDiscountRow.classList.add('hidden');
            prefix.classList.remove('hidden');
            suffix.classList.add('hidden');
            label.innerText = 'NOMINAL_VECTOR (IDR)';
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
            document.getElementById('modalTitle').innerText = 'Voucher Config';
            toggleDiscountFields();
        }, 200);
    }

    function openAddModal() {
        const modal = document.getElementById('voucherModal');
        modal.classList.remove('hidden');
        setTimeout(() => modal.classList.remove('opacity-0'), 10);
        document.getElementById('modalTitle').innerText = 'Deploy New Voucher';
        document.getElementById('voucherForm').action = "{{ route('admin.vouchers.store') }}";
        document.getElementById('formMethod').value = 'POST';
        toggleDiscountFields();
    }

    function editVoucher(voucher) {
        const modal = document.getElementById('voucherModal');
        modal.classList.remove('hidden');
        setTimeout(() => modal.classList.remove('opacity-0'), 10);
        document.getElementById('modalTitle').innerText = 'Modify Protocol';
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