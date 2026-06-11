@extends('layouts.admin')

@section('content')
<div class="pt-0 pb-10">
    <!-- Header -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
        <div>
            <h1 class="text-4xl font-black tracking-tighter uppercase italic text-slate-800">Pencatatan WD Penjual</h1>
            <p class="text-gray-500 mt-1 font-mono text-xs uppercase tracking-widest">Daftar Transfer Dana Penjualan Manual ke Rekening Bank Penjual (WD)</p>
        </div>

        <form action="{{ route('admin.payout_logs') }}" method="GET" class="flex flex-wrap gap-2">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari penjual atau catatan..."
                class="px-4 py-2 border border-indigo-100 rounded-xl text-xs font-bold uppercase focus:outline-none focus:border-purple-400 bg-white/60 backdrop-blur transition-all">

            <select name="status" class="px-4 py-2 border border-indigo-100 rounded-xl text-xs font-black uppercase focus:outline-none focus:border-purple-400 bg-white/60 backdrop-blur transition-all">
                <option value="">Semua Status</option>
                <option value="pending"   {{ request('status') == 'pending'   ? 'selected' : '' }}>Pending</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
            </select>

            <button type="submit" class="bg-gradient-to-r from-orange-600 to-amber-600 hover:from-orange-700 hover:to-amber-700 text-white px-6 py-2 text-xs font-black uppercase rounded-xl transition-all shadow-md shadow-orange-500/10">
                Filter
            </button>
        </form>
    </div>

    @if(session('success'))
    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-black uppercase tracking-tight rounded-xl flex items-center gap-3">
        <span class="text-lg">✅</span>
        <div>{{ session('success') }}</div>
    </div>
    @endif

    @if(session('error'))
    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-black uppercase tracking-tight rounded-xl flex items-center gap-3">
        <span class="text-lg">❌</span>
        <div>{{ session('error') }}</div>
    </div>
    @endif

    <!-- Payout Table -->
    <div class="glass-card overflow-hidden shadow-xl">
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 px-6 py-5 text-white flex items-center justify-between">
            <h2 class="font-black text-sm uppercase tracking-tight">Riwayat Transfer Dana (Payout / WD)</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="glass-table-header text-slate-600 font-black uppercase">
                    <tr>
                        <th class="px-6 py-5">Waktu Pengajuan</th>
                        <th class="px-6 py-5">Penjual</th>
                        <th class="px-6 py-5">Pesanan</th>
                        <th class="px-6 py-5 text-right font-black">Jumlah Transfer</th>
                        <th class="px-6 py-5">Info Bank Penerima</th>
                        <th class="px-6 py-5 text-center">Status</th>
                        <th class="px-6 py-5 text-center">Aksi / Bukti</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-indigo-55 font-bold">
                    @forelse($payouts as $log)
                    <tr class="hover:bg-white/40 transition-all">
                        <td class="px-6 py-6 font-mono text-[10px] text-gray-500">
                            {{ $log->created_at->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="px-6 py-6">
                            <div class="uppercase text-sm text-slate-800 leading-tight">{{ $log->seller->name ?? 'UNKNOWN' }}</div>
                            <div class="text-[9px] font-mono text-gray-400 uppercase mt-1">{{ $log->seller->email ?? '' }}</div>
                        </td>
                        <td class="px-6 py-6 text-gray-600 font-mono text-[11px]">
                            <div>TXN: #{{ $log->transaction_id }}</div>
                            @if($log->transaction && $log->transaction->transaction_number)
                                <div class="text-[9px] text-orange-600 mt-1 uppercase font-bold">{{ $log->transaction->transaction_number }}</div>
                            @endif
                        </td>
                        <td class="px-6 py-6 text-right font-black text-sm text-emerald-600">
                            Rp {{ number_format($log->amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-6">
                            <div class="text-slate-800 font-black uppercase text-[11px]">{{ $log->bank_name ?? 'Belum diatur' }}</div>
                            <div class="font-mono text-xs text-gray-600 mt-0.5">{{ $log->account_number ?? 'Belum diatur' }}</div>
                            <div class="text-[9px] text-gray-400 mt-0.5 uppercase">A.N. {{ $log->account_holder_name ?? 'Belum diatur' }}</div>
                        </td>
                        <td class="px-6 py-6 text-center">
                            @php
                                $statusColor = match($log->status) {
                                    'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    default     => 'bg-amber-50 text-amber-700 border-amber-200',
                                };
                            @endphp
                            <span class="px-3 py-1.5 border rounded-lg text-[9px] font-black uppercase tracking-wider {{ $statusColor }}">
                                {{ $log->status === 'completed' ? 'Selesai' : 'Pending' }}
                            </span>
                        </td>
                        <td class="px-6 py-6 text-center">
                            @if($log->status === 'completed')
                                <div class="flex flex-col items-center gap-1.5">
                                    @if($log->transfer_proof)
                                        <a href="{{ Storage::url($log->transfer_proof) }}" target="_blank" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 border border-slate-300 text-slate-700 rounded-lg text-[9px] font-black uppercase tracking-wider flex items-center gap-1 transition-all">
                                            👁️ Lihat Bukti
                                        </a>
                                    @endif
                                    <span class="text-[9px] text-gray-400 font-mono uppercase">Oleh: {{ $log->admin->name ?? 'System' }}</span>
                                </div>
                            @else
                                <button type="button" 
                                        onclick="openPayoutModal({{ json_encode($log) }})"
                                        class="px-4 py-2 bg-gradient-to-r from-orange-550 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white rounded-xl text-[10px] font-black uppercase tracking-wider shadow-lg shadow-orange-500/10 hover:shadow-xl transition-all">
                                    💰 Transfer & Rilis
                                </button>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-20 text-center text-gray-400 font-black uppercase italic tracking-widest">
                            Tidak ada data pencatatan payout yang ditemukan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $payouts->links() }}
    </div>
</div>

<!-- Payout Action Modal -->
<div id="payoutModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
    <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div class="fixed inset-0 transition-opacity" aria-hidden="true">
            <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm"></div>
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal Content -->
        <div class="inline-block align-bottom bg-white rounded-3xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full border border-indigo-50">
            <div class="bg-gradient-to-r from-orange-500 to-amber-600 px-6 py-5 text-white flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-black uppercase italic tracking-tight">Konfirmasi & Selesaikan Payout</h3>
                    <p class="text-[10px] text-orange-100 font-mono uppercase tracking-widest mt-1">Upload Bukti Transfer Manual ke Penjual</p>
                </div>
                <button type="button" onclick="closePayoutModal()" class="text-white hover:text-orange-200 transition-all font-bold text-lg">✕</button>
            </div>

            <form id="payoutForm" method="POST" enctype="multipart/form-data" class="p-8 space-y-6">
                @csrf
                
                <div class="p-4 bg-slate-50 border border-slate-100 rounded-2xl flex justify-between items-center">
                    <div>
                        <p class="text-[9px] font-mono text-gray-400 uppercase tracking-widest">Jumlah yang Harus Ditransfer</p>
                        <p id="modalAmount" class="text-2xl font-black text-emerald-600 mt-1">Rp 0</p>
                    </div>
                    <div class="text-right">
                        <p class="text-[9px] font-mono text-gray-400 uppercase tracking-widest">ID Transaksi</p>
                        <p id="modalRef" class="text-xs font-mono font-bold text-slate-700 mt-1">TXN: #0</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Bank Name -->
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-black uppercase tracking-wider text-slate-600">Bank Tujuan</label>
                        <input type="text" name="bank_name" id="bank_name" required placeholder="Contoh: BCA / Mandiri / BRI"
                            class="w-full px-4 py-3 border border-indigo-100 rounded-xl text-xs font-bold focus:outline-none focus:border-orange-500 transition-all">
                    </div>

                    <!-- Account Holder Name -->
                    <div class="space-y-1.5">
                        <label class="block text-[10px] font-black uppercase tracking-wider text-slate-600">Nama Pemilik Rekening</label>
                        <input type="text" name="account_holder_name" id="account_holder_name" required placeholder="Contoh: Budi Santoso"
                            class="w-full px-4 py-3 border border-indigo-100 rounded-xl text-xs font-bold focus:outline-none focus:border-orange-500 transition-all">
                    </div>
                </div>

                <!-- Account Number -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-600">Nomor Rekening Tujuan</label>
                    <input type="text" name="account_number" id="account_number" required placeholder="Contoh: 1234567890"
                        class="w-full px-4 py-3 border border-indigo-100 rounded-xl text-xs font-bold focus:outline-none focus:border-orange-500 transition-all">
                </div>

                <!-- File Transfer Proof -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-600">Bukti Transfer (Gambar / PDF)</label>
                    <input type="file" name="transfer_proof" required accept="image/*,application/pdf"
                        class="w-full text-xs text-slate-600 font-bold border border-indigo-100 rounded-xl file:mr-4 file:py-2.5 file:px-4 file:rounded-xl file:border-0 file:text-[10px] file:font-black file:uppercase file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 transition-all">
                </div>

                <!-- Notes -->
                <div class="space-y-1.5">
                    <label class="block text-[10px] font-black uppercase tracking-wider text-slate-600">Catatan Internal Admin (Opsional)</label>
                    <textarea name="notes" id="notes" rows="2" placeholder="Catatan tambahan..."
                        class="w-full px-4 py-3 border border-indigo-100 rounded-xl text-xs font-bold focus:outline-none focus:border-orange-500 transition-all"></textarea>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end gap-3 pt-4 border-t border-indigo-50">
                    <button type="button" onclick="closePayoutModal()"
                        class="px-5 py-3 border border-indigo-100 rounded-xl text-xs font-black uppercase tracking-wider text-slate-600 hover:bg-slate-50 transition-all">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-6 py-3 bg-gradient-to-r from-orange-500 to-amber-600 hover:from-orange-600 hover:to-amber-700 text-white rounded-xl text-xs font-black uppercase tracking-wider shadow-lg shadow-orange-500/10 hover:shadow-xl transition-all">
                        🚀 Selesaikan & Rilis
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openPayoutModal(payoutRecord) {
        const modal = document.getElementById('payoutModal');
        const form = document.getElementById('payoutForm');
        
        // Set Action URL
        form.action = `/admin/payout-logs/${payoutRecord.id}/complete`;

        // Format Amount to IDR
        const amountFormatted = new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(payoutRecord.amount);
        document.getElementById('modalAmount').innerText = amountFormatted;
        
        // Set transaction ref details
        let refText = `TXN: #${payoutRecord.transaction_id}`;
        if (payoutRecord.transaction && payoutRecord.transaction.transaction_number) {
            refText += ` | ${payoutRecord.transaction.transaction_number}`;
        }
        document.getElementById('modalRef').innerText = refText;

        // Set default values if already exists in the record
        document.getElementById('bank_name').value = payoutRecord.bank_name || '';
        document.getElementById('account_number').value = payoutRecord.account_number || '';
        document.getElementById('account_holder_name').value = payoutRecord.account_holder_name || '';
        document.getElementById('notes').value = payoutRecord.notes || '';

        // Show Modal
        modal.classList.remove('hidden');
    }

    function closePayoutModal() {
        const modal = document.getElementById('payoutModal');
        modal.classList.add('hidden');
    }
</script>
@endsection
