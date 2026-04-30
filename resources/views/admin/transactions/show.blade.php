@extends('layouts.admin')

@section('title', 'Detail Transaksi #' . $transaction->id)

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-8">
        <div>
            <nav class="flex mb-4 text-sm font-medium text-gray-500" aria-label="Breadcrumb">
                <ol class="inline-flex items-center space-x-1 md:space-x-3">
                    <li class="inline-flex items-center">
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-orange-600">Dashboard</a>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                            <a href="{{ route('admin.transactions') }}" class="hover:text-orange-600">Transaksi</a>
                        </div>
                    </li>
                    <li aria-current="page">
                        <div class="flex items-center">
                            <i class="fas fa-chevron-right text-gray-400 text-xs mx-2"></i>
                            <span class="text-gray-400">#{{ $transaction->id }}</span>
                        </div>
                    </li>
                </ol>
            </nav>
            <h1 class="text-3xl font-bold text-gray-800">Detail Transaksi #{{ $transaction->id }}</h1>
            <p class="text-gray-600 mt-1">Invoice: <span class="font-mono font-semibold">INV-{{ str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }}</span></p>
        </div>
        <div class="mt-4 md:mt-0 flex gap-3">
            <a href="{{ route('admin.transactions') }}" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
            @if($transaction->status == 'completed' || $transaction->status == 'received')
                <a href="{{ route('admin.transactions.invoice', $transaction->id) }}" target="_blank" class="px-4 py-2 bg-white border border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition shadow-sm inline-flex items-center">
                    <i class="fas fa-print mr-2"></i> Cetak Invoice
                </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Status Information -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-6">
                    <h2 class="text-xl font-bold text-gray-800">Status Pesanan</h2>
                    @php
                        $statusClasses = [
                            'pending' => 'bg-orange-100 text-orange-700',
                            'paid_verified' => 'bg-blue-100 text-blue-700',
                            'processing' => 'bg-indigo-100 text-indigo-700',
                            'shipped' => 'bg-purple-100 text-purple-700',
                            'received' => 'bg-teal-100 text-teal-700',
                            'completed' => 'bg-green-100 text-green-700',
                            'cancelled' => 'bg-red-100 text-red-700',
                        ];
                    @endphp
                    <span class="px-4 py-1.5 rounded-full text-sm font-bold {{ $statusClasses[$transaction->status] ?? 'bg-gray-100 text-gray-700' }}">
                        {{ strtoupper($transaction->status) }}
                    </span>
                </div>

                <!-- Simple Progress Stepper -->
                <div class="relative flex items-center justify-between">
                    @php
                        $steps = [
                            ['id' => 'pending', 'label' => 'Menunggu Bayar', 'icon' => 'fa-wallet'],
                            ['id' => 'paid_verified', 'label' => 'Diproses', 'icon' => 'fa-box'],
                            ['id' => 'shipped', 'label' => 'Dikirim', 'icon' => 'fa-truck'],
                            ['id' => 'received', 'label' => 'Diterima', 'icon' => 'fa-check-circle'],
                            ['id' => 'completed', 'label' => 'Selesai', 'icon' => 'fa-flag-checkered'],
                        ];
                        $currentIndex = 0;
                        foreach($steps as $i => $step) {
                            if($transaction->status == $step['id']) $currentIndex = $i;
                        }
                        if($transaction->status == 'processing' || $transaction->status == 'packed') $currentIndex = 1;
                    @endphp
                    @foreach($steps as $i => $step)
                        <div class="z-10 flex flex-col items-center">
                            <div class="w-10 h-10 rounded-full flex items-center justify-center border-2 {{ $i <= $currentIndex ? 'bg-orange-600 border-orange-600 text-white shadow-lg shadow-orange-200' : 'bg-white border-gray-200 text-gray-400' }}">
                                <i class="fas {{ $step['icon'] }} text-sm"></i>
                            </div>
                            <span class="text-[10px] md:text-xs font-semibold mt-2 {{ $i <= $currentIndex ? 'text-orange-600' : 'text-gray-400' }}">{{ $step['label'] }}</span>
                        </div>
                    @endforeach
                    <div class="absolute top-5 left-0 w-full h-0.5 bg-gray-100 -z-0"></div>
                </div>
            </div>

            <!-- Items List -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-50 font-bold text-gray-800 text-lg">Daftar Produk</div>
                <div class="divide-y divide-gray-50">
                    @foreach($transaction->items as $item)
                        <div class="p-6 flex items-center gap-4">
                            <img src="{{ Storage::url($item->product->image) }}" class="w-16 h-16 rounded-xl object-cover bg-gray-50 border border-gray-100">
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-gray-900 truncate">{{ $item->product->name }}</h4>
                                <p class="text-sm text-gray-500">{{ $item->product->category->name ?? 'Kategori' }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-sm text-gray-500">{{ $item->quantity }} x</p>
                                <p class="font-bold text-gray-900">Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="bg-gray-50 p-6 space-y-3">
                    <div class="flex justify-between text-gray-600">
                        <span>Subtotal Barang</span>
                        <span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-gray-600">
                        <span>Ongkos Kirim</span>
                        <span>Rp {{ number_format($transaction->shipping_cost ?? 0, 0, ',', '.') }}</span>
                    </div>
                    @if($transaction->service_fee)
                        <div class="flex justify-between text-gray-600">
                            <span>Biaya Layanan (Platform)</span>
                            <span>Rp {{ number_format($transaction->service_fee, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @if($transaction->admin_fee > 0)
                        <div class="flex justify-between text-gray-600">
                            <span>Biaya Admin (Gateway)</span>
                            <span>Rp {{ number_format($transaction->admin_fee, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    @if($transaction->discount_total)
                        <div class="flex justify-between text-green-600">
                            <span>Diskon Voucher</span>
                            <span>- Rp {{ number_format($transaction->discount_total, 0, ',', '.') }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between items-center pt-3 border-t border-gray-200">
                        <span class="text-lg font-bold text-gray-800">Total Transaksi</span>
                        <span class="text-2xl font-black text-orange-600">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <!-- Multimedia Proofs Section -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Seller Shipment Proof -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 inline-flex items-center">
                        <i class="fas fa-truck-loading text-blue-500 mr-2"></i> Bukti Pengiriman (Seller)
                    </h3>
                    @if($transaction->shipping_proof)
                        <div class="rounded-xl overflow-hidden border border-gray-200">
                             <a href="{{ Storage::url($transaction->shipping_proof) }}" target="_blank">
                                <img src="{{ Storage::url($transaction->shipping_proof) }}" class="w-full h-48 object-cover hover:scale-105 transition duration-500">
                             </a>
                        </div>
                        <div class="mt-3 text-sm text-gray-500 italic">
                            Kurir: <span class="font-bold text-gray-700">{{ strtoupper($transaction->courier) }}</span><br>
                            Resi: <span class="font-mono font-bold text-gray-700">{{ $transaction->tracking_number }}</span>
                        </div>
                    @else
                        <div class="flex flex-col items-center justify-center py-12 bg-gray-50 rounded-xl text-gray-400 border-2 border-dashed border-gray-200">
                            <i class="fas fa-image text-3xl mb-2"></i>
                            <p class="text-xs">Belum ada bukti kirim dari seller</p>
                        </div>
                    @endif
                </div>

                <!-- Buyer Receipt Proof (MULTIMEDIA) -->
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 inline-flex items-center">
                        <i class="fas fa-check-double text-green-500 mr-2"></i> Bukti Penerimaan (Buyer)
                    </h3>
                    @if($transaction->receipt_photos && count($transaction->receipt_photos) > 0)
                        <div class="grid grid-cols-2 gap-2">
                            @foreach($transaction->receipt_photos as $file)
                                @php $ext = pathinfo($file, PATHINFO_EXTENSION); @endphp
                                <div class="rounded-lg overflow-hidden border border-gray-100 relative group h-24 bg-black flex items-center justify-center">
                                    @if(in_array(strtolower($ext), ['mp4', 'mov', 'avi']))
                                        <video class="w-full h-full object-cover opacity-60">
                                            <source src="{{ Storage::url($file) }}">
                                        </video>
                                        <div class="absolute inset-0 flex items-center justify-center text-white text-xl">
                                            <i class="fas fa-play-circle"></i>
                                        </div>
                                    @else
                                        <img src="{{ Storage::url($file) }}" class="w-full h-full object-cover">
                                    @endif
                                    <a href="{{ Storage::url($file) }}" target="_blank" class="absolute inset-0 bg-black bg-opacity-40 opacity-0 group-hover:opacity-100 flex items-center justify-center text-white transition-opacity duration-300">
                                        <i class="fas fa-search-plus"></i>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-[10px] text-gray-400 mt-2">* Klik gambar/video untuk memperbesar</p>
                    @else
                        <div class="flex flex-col items-center justify-center py-12 bg-gray-50 rounded-xl text-gray-400 border-2 border-dashed border-gray-200">
                            <i class="fas fa-camera text-3xl mb-2"></i>
                            <p class="text-xs">Belum ada konfirmasi/bukti dari buyer</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar Actions -->
        <div class="space-y-8">
            <!-- Payment Proof Sidebar -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-gray-800 mb-4">Bukti Pembayaran</h3>
                @if($transaction->payment_proof)
                    <div class="rounded-xl overflow-hidden border border-gray-200 mb-4">
                        <a href="{{ Storage::url($transaction->payment_proof) }}" target="_blank">
                            <img src="{{ Storage::url($transaction->payment_proof) }}" class="w-full h-56 object-cover hover:scale-105 transition duration-500">
                        </a>
                    </div>
                @else
                    <div class="py-8 text-center bg-gray-50 rounded-xl border border-dashed border-gray-200 text-gray-400 mb-4">
                        <i class="fas fa-file-invoice-dollar text-3xl mb-2"></i>
                        <p class="text-xs">Belum di-upload</p>
                    </div>
                @endif
                
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="bg-gray-50 p-2 rounded-lg border border-gray-100">
                        <p class="text-gray-400">Metode</p>
                        <p class="font-bold text-gray-700">{{ $transaction->payment_method_code }}</p>
                    </div>
                    <div class="bg-gray-50 p-2 rounded-lg border border-gray-100">
                        <p class="text-gray-400">Waktu</p>
                        <p class="font-bold text-gray-700">{{ $transaction->created_at->format('H:i, d M') }}</p>
                    </div>
                </div>
            </div>

            <!-- ADMIN ACTIONS CARD -->
            @if($transaction->status == 'pending' || $transaction->status == 'received')
                <div class="bg-white rounded-2xl shadow-lg border border-orange-100 p-6 relative overflow-hidden ring-4 ring-orange-50">
                    <div class="absolute top-0 right-0 p-2 bg-orange-600 text-white transform rotate-45 translate-x-3 -translate-y-3 px-8 text-[10px] font-bold shadow">AKSI BUTUH</div>
                    
                    <h3 class="font-bold text-gray-800 mb-4 inline-flex items-center">
                        <i class="fas fa-exclamation-circle text-orange-500 mr-2"></i> Persetujuan Admin
                    </h3>

                    @if($transaction->status == 'pending')
                    <div class="space-y-4">
                        <div class="p-3 bg-blue-50 text-blue-800 text-xs rounded-lg border border-blue-100 leading-relaxed italic">
                            Cek bukti bayar di atas. Jika sesuai nominal <strong>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</strong>, silakan verifikasi.
                        </div>
                        <form action="{{ route('admin.verify', $transaction) }}" method="POST">
                            @csrf
                            <button class="w-full bg-orange-600 hover:bg-orange-700 text-white font-bold py-3 px-4 rounded-xl transition shadow-lg shadow-orange-200 flex items-center justify-center gap-2">
                                <i class="fas fa-check-circle"></i> Verifikasi Pembayaran
                            </button>
                        </form>

                        <div class="mt-6 pt-6 border-t border-gray-100">
                            <p class="text-xs font-bold text-red-600 mb-2 uppercase">Atau Tolak Pembayaran</p>
                            <form action="{{ route('admin.reject', $transaction) }}" method="POST">
                                @csrf
                                <div class="space-y-2">
                                    <textarea name="note" class="w-full text-xs rounded-lg border-gray-200 focus:ring-red-500 focus:border-red-500 @error('note') border-red-500 @enderror" placeholder="Alasan penolakan (misal: Bukti tidak jelas)">{{ old('note') }}</textarea>
                                    @error('note')
                                        <p class="text-[10px] text-red-600 font-medium">{{ $message }}</p>
                                    @enderror
                                    <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menolak pembayaran ini?')" class="w-full bg-red-100 hover:bg-red-200 text-red-700 font-bold py-2 px-4 rounded-xl transition flex items-center justify-center gap-2">
                                        <i class="fas fa-times-circle"></i> Tolak Pembayaran
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @elseif($transaction->status == 'received')
                    <div class="space-y-4">
                        <div class="p-3 bg-green-50 text-green-800 text-xs rounded-lg border border-green-100 leading-relaxed italic">
                            Barang sudah diterima buyer. Cek Bukti Penerimaan (Multimedia) sebelum melepas dana <strong>Rp {{ number_format($transaction->seller_amount, 0, ',', '.') }}</strong> ke Seller.
                        </div>
                        <form action="{{ route('admin.release', $transaction) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-4">
                                <label class="block text-xs font-bold text-gray-700 mb-1">Upload Bukti Transfer</label>
                                <input type="file" name="transfer_proof" accept="image/*,application/pdf" required
                                    class="w-full text-xs text-gray-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-bold file:bg-green-50 file:text-green-700 hover:file:bg-green-100 border border-gray-200 rounded-lg">
                                @error('transfer_proof')
                                    <p class="text-[10px] text-red-600 font-medium mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            <button class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-4 rounded-xl transition shadow-lg shadow-green-200 flex items-center justify-center gap-2">
                                <i class="fas fa-hand-holding-usd"></i> Lepaskan Dana ke Seller
                            </button>
                        </form>
                    </div>
                    @endif
                </div>
            @endif

            <!-- Log Summary -->
            @if($transaction->trackingLogs && $transaction->trackingLogs->count() > 0)
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                    <h3 class="font-bold text-gray-800 mb-4 inline-flex items-center">
                        <i class="fas fa-history text-gray-400 mr-2"></i> Riwayat Aktivitas
                    </h3>
                    <div class="space-y-4">
                        @foreach($transaction->trackingLogs->take(3) as $log)
                            <div class="border-l-2 border-orange-100 pl-3 py-1">
                                <p class="text-xs font-bold text-gray-800">{{ $log->status_label ?? $log->status }}</p>
                                <p class="text-[10px] text-gray-500 mt-1 italic leading-tight">{{ $log->note ?? 'Perubahan status otomatis' }}</p>
                                <p class="text-[9px] text-gray-400 mt-1 uppercase">{{ $log->created_at->format('d M, H:i') }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection