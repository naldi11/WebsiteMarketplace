@extends('layouts.admin')

@section('title', 'Payment Protocol')

@section('content')
<div class="pt-0 pb-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
        <div>
            <h1 class="text-4xl font-black tracking-tighter uppercase italic text-black">Metode Pembayaran</h1>
            <p class="text-gray-500 mt-1 font-mono text-xs uppercase tracking-widest text-black">Manajemen Gateway Transaksi</p>
        </div>
        <button onclick="openAddModal()" class="px-6 py-3 bg-black text-white border-[3px] border-black text-sm font-black uppercase tracking-tighter hover:bg-white hover:text-black transition-all neo-brutalism italic flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 4v16m8-8H4"></path>
            </svg>
            Tambah Metode Pembayaran
        </button>
    </div>

    <!-- Payment Methods Table - Neo Brutalism -->
    <div class="bg-white border-[3px] border-black neo-brutalism overflow-hidden mb-12">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-100 border-b-[3px] border-black text-black font-black uppercase italic">
                    <tr>
                        <th class="px-8 py-6">Nama Gateway</th>
                        <th class="px-8 py-6">Nomor Akun</th>
                        <th class="px-8 py-6">Tipe</th>
                        <th class="px-8 py-6">Biaya</th>
                        <th class="px-8 py-6">Status</th>
                        <th class="px-8 py-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-gray-100 font-bold">
                    @forelse($paymentMethods as $pm)
                    <tr class="hover:bg-gray-50 transition-all">
                        <td class="px-8 py-6 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-12 w-12 border-2 border-black flex items-center justify-center bg-black text-white text-lg font-black italic">
                                    {{ $pm->icon ?? '💳' }}
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-black text-black uppercase italic tracking-tighter leading-none">{{ $pm->name }}</div>
                                    <div class="text-[9px] text-gray-400 font-mono tracking-widest uppercase mt-1">{{ $pm->code }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            <span class="font-mono bg-white px-3 py-1 border-2 border-black text-[10px] uppercase font-black {{ $pm->account_number ? 'text-black' : 'text-gray-300' }}">
                                {{ $pm->account_number ?? 'NULL_PTR' }}
                            </span>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            <span class="px-3 py-1 border-2 border-black text-[9px] font-black uppercase tracking-widest bg-gray-100 italic">
                                {{ $pm->type_label }}
                            </span>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            @if($pm->admin_fee > 0 || $pm->admin_fee_percent > 0)
                                <div class="flex flex-col text-[10px] text-black font-black italic">
                                    @if($pm->admin_fee > 0)
                                        <span>IDR_{{ number_format($pm->admin_fee, 0, ',', '.') }}</span>
                                    @endif
                                    @if($pm->admin_fee_percent > 0)
                                        <span class="text-gray-500">+{{ floatval($pm->admin_fee_percent) }}%</span>
                                    @endif
                                </div>
                            @else
                                <span class="text-gray-300 italic text-[10px] font-mono">GRATIS</span>
                            @endif
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            <span class="px-3 py-1 border-2 border-black text-[9px] font-black uppercase tracking-widest {{ $pm->is_active ? 'bg-black text-white' : 'bg-white text-black' }}">
                                {{ $pm->is_active ? 'AKTIF' : 'NONAKTIF' }}
                            </span>
                        </td>
                        <td class="px-8 py-6 text-right whitespace-nowrap">
                            <div class="flex justify-end gap-3 font-black uppercase italic text-[10px]">
                                <button onclick='editPaymentMethod(@json($pm))' class="px-4 py-2 border-2 border-black hover:bg-black hover:text-white transition-all">Edit</button>
                                <form action="{{ route('admin.payment_methods.destroy', $pm) }}" method="POST" class="inline-block" onsubmit="return confirm('Hapus metode {{ $pm->name }}?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="px-4 py-2 bg-black text-white border-2 border-black hover:bg-white hover:text-black transition-all">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-8 py-24 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 border-[3px] border-black flex items-center justify-center font-black text-2xl mb-4 italic">!</div>
                                <p class="text-xs font-black uppercase tracking-widest text-gray-400 italic">Belum Ada Metode Pembayaran</p>
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
<div id="paymentModal" class="fixed inset-0 bg-black/60 hidden z-50 overflow-y-auto transition-all duration-200 backdrop-blur-sm">
    <div class="min-h-screen px-4 text-center flex items-center justify-center">
        <div class="inline-block w-full max-w-xl p-0 my-8 text-left align-middle bg-white transform transition-all border-[4px] border-black shadow-[20px_20px_0px_0px_rgba(0,0,0,1)]">
            
            <div class="bg-black p-8 text-white flex justify-between items-center">
                <div>
                    <h3 class="text-3xl font-black uppercase italic tracking-tighter" id="modalTitle">Konfigurasi Gateway</h3>
                    <p class="text-gray-400 text-[10px] mt-1 font-mono uppercase tracking-widest">Atur Metode Penerimaan Dana</p>
                </div>
                <button onclick="closeModal()" class="border-2 border-white w-10 h-10 flex items-center justify-center hover:bg-white hover:text-black transition-all">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form id="paymentForm" action="{{ route('admin.payment_methods.store') }}" method="POST" class="p-10 bg-white">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div>
                        <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-gray-500">Kode</label>
                        <input type="text" name="code" id="pCode" required placeholder="bank_bca"
                            class="w-full px-5 py-4 bg-white border-[3px] border-black rounded-none focus:bg-gray-50 outline-none transition-all font-black uppercase text-sm shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                    </div>
                    
                    <div>
                        <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-gray-500">Nama Tampilan</label>
                        <input type="text" name="name" id="pName" required placeholder="BCA Protocol"
                            class="w-full px-5 py-4 bg-white border-[3px] border-black rounded-none focus:bg-gray-50 outline-none transition-all font-black uppercase italic text-sm shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-gray-500">Nomor Rekening</label>
                        <input type="text" name="account_number" id="pAccountNumber" placeholder="1234xxx"
                            class="w-full px-5 py-4 bg-white border-[3px] border-black rounded-none focus:bg-gray-50 outline-none transition-all font-mono font-black text-lg shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-gray-500">Tipe Pembayaran</label>
                        <select name="type" id="pType" required
                            class="w-full px-5 py-4 bg-white border-[3px] border-black rounded-none focus:bg-gray-50 outline-none transition-all font-black uppercase italic text-sm shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                            <option value="bank_transfer">BANK_TRANSFER</option>
                            <option value="ewallet">E_WALLET</option>
                            <option value="qris">QRIS_SCAN</option>
                            <option value="credit_card">CREDIT_CARD</option>
                            <option value="cod">CASH_ON_DELIVERY</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-gray-500">Ikon</label>
                        <input type="text" name="icon" id="pIcon" placeholder="🏦" maxlength="10"
                            class="w-full px-5 py-4 bg-white border-[3px] border-black rounded-none focus:bg-gray-50 outline-none transition-all text-center text-2xl shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-gray-500">Biaya Tetap (IDR)</label>
                        <input type="number" name="admin_fee" id="pAdminFee" value="0" step="0.01"
                            class="w-full px-5 py-4 bg-white border-[3px] border-black rounded-none focus:bg-gray-50 outline-none transition-all font-black text-lg shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                    </div>

                    <div>
                        <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-gray-500">Biaya Persentase (%)</label>
                        <input type="number" name="admin_fee_percent" id="pAdminFeePercent" value="0" step="0.01" max="100"
                            class="w-full px-5 py-4 bg-white border-[3px] border-black rounded-none focus:bg-gray-50 outline-none transition-all font-black text-lg shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                    </div>

                    <div class="flex items-center pt-6 md:col-span-2">
                        <label class="flex items-center cursor-pointer gap-4 p-4 border-[3px] border-black bg-gray-50 w-full shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">
                            <input type="checkbox" name="is_active" id="pIsActive" value="1" checked
                                class="w-8 h-8 text-black border-[3px] border-black rounded-none focus:ring-0">
                            <span class="text-xs font-black uppercase italic tracking-widest">Metode ini AKTIF</span>
                        </label>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-[10px] font-black uppercase mb-3 tracking-widest text-gray-500">Instruksi Pembayaran</label>
                        <textarea name="instructions" id="pInstructions" rows="3" placeholder="Input execution steps..."
                            class="w-full px-6 py-4 bg-white border-[3px] border-black rounded-none focus:bg-gray-50 outline-none transition-all font-black italic uppercase text-xs shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]"></textarea>
                    </div>
                </div>

                <div class="mt-12 flex gap-6">
                    <button type="button" onclick="closeModal()" class="flex-1 px-8 py-5 text-sm font-black border-[3px] border-black hover:bg-black hover:text-white transition-all uppercase italic">Batal</button>
                    <button type="submit" class="flex-1 px-8 py-5 text-sm font-black bg-black text-white border-[3px] border-black hover:bg-white hover:text-black transition-all uppercase italic shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]">Simpan</button>
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
        document.getElementById('modalTitle').innerText = 'Gateway Config';
    }

    function openAddModal() {
        const modal = document.getElementById('paymentModal');
        modal.classList.remove('hidden');
        document.getElementById('modalTitle').innerText = 'Register Entry';
        document.getElementById('paymentForm').action = "{{ route('admin.payment_methods.store') }}";
        document.getElementById('formMethod').value = 'POST';
    }

    function editPaymentMethod(pm) {
        const modal = document.getElementById('paymentModal');
        modal.classList.remove('hidden');
        document.getElementById('modalTitle').innerText = 'Modify Protocol';
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