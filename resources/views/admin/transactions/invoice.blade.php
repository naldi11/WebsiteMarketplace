<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice - #INV-{{ str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }}</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
        }
        .invoice-container {
            width: 210mm; /* A4 width */
            min-height: 297mm; /* A4 height */
            margin: 2rem auto;
            background: white;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
            padding: 40px;
        }
        @media print {
            body { background: white; }
            .invoice-container { margin: 0; box-shadow: none; width: 100%; height: 100%; }
            .no-print { display: none !important; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
    </style>
</head>
<body class="antialiased">

    <!-- Action Bar -->
    <div class="max-w-[210mm] mx-auto mt-6 mb-2 flex justify-between items-center no-print px-4">
        <div class="text-sm text-gray-500">Pratinjau Cetak Faktur. Margin disesuaikan untuk ukuran kertas A4.</div>
        <button onclick="window.print()" class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-lg font-semibold shadow-md transition flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd" />
            </svg>
            Cetak Invoice
        </button>
    </div>

    <!-- Invoice Paper -->
    <div class="invoice-container rounded-xl relative overflow-hidden">
        <!-- Top Colored Bar -->
        <div class="absolute top-0 left-0 w-full h-2 bg-orange-500"></div>

        <!-- Header Start -->
        <div class="flex justify-between items-start border-b border-gray-100 pb-8 mb-8 mt-4">
            <div>
                <h1 class="text-4xl font-extrabold text-orange-600 tracking-tight">Techno<span class="text-gray-800">Market</span></h1>
                <p class="text-sm text-gray-500 mt-1">Platform Belanja Elektonik & Digital Terpercaya</p>
                <div class="mt-4 text-sm text-gray-600 space-y-1">
                    <p>contact@technomarket.com</p>
                    <p>DKI Jakarta, Indonesia</p>
                </div>
            </div>
            <div class="text-right">
                <h2 class="text-3xl font-bold text-gray-200">INVOICE</h2>
                <p class="text-lg font-semibold text-gray-800 mt-1">#INV-{{ str_pad($transaction->id, 5, '0', STR_PAD_LEFT) }}</p>
                
                <div class="mt-4 text-sm grid grid-cols-2 gap-x-4 gap-y-2 text-right">
                    <div class="text-gray-500">Tanggal Transaksi:</div>
                    <div class="font-medium text-gray-800">{{ $transaction->created_at->format('d M Y, H:i') }}</div>
                    
                    <div class="text-gray-500">Status Pembayaran:</div>
                    <div class="font-bold text-green-600 uppercase">{{ $transaction->status }}</div>
                </div>
            </div>
        </div>

        <!-- Addresses -->
        <div class="grid grid-cols-2 gap-8 mb-8">
            <div class="bg-gray-50 p-5 rounded-lg border border-gray-100">
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">DITAGIHKAN KEPADA:</div>
                <h3 class="text-lg font-bold text-gray-800">{{ $transaction->buyer->name }}</h3>
                <p class="text-sm text-gray-600 mt-2 leading-relaxed">
                    {{ $transaction->shippingAddressRecord->full_address ?? 'Alamat tidak direkam' }}<br>
                    <strong>Tlp:</strong> {{ $transaction->buyer->phone ?? '-' }}
                </p>
            </div>
            <div class="bg-blue-50/50 p-5 rounded-lg border border-blue-100/50">
                <div class="text-xs font-bold text-blue-400 uppercase tracking-wider mb-2">INFORMASI PENJUAL:</div>
                <h3 class="text-lg font-bold text-gray-800">
                    {{ $transaction->seller->shop_name ?? ($transaction->seller->name ?? 'TechnoMarket Seller') }}
                </h3>
                <p class="text-sm text-gray-600 mt-2 leading-relaxed">
                    <strong>PIC:</strong> {{ $transaction->seller->name ?? '-' }}<br>
                    <strong>Email:</strong> {{ $transaction->seller->email ?? '-' }}<br>
                    <strong>Tlp:</strong> {{ $transaction->seller->phone ?? '-' }}
                </p>
            </div>
        </div>
        
        <!-- Payment Details -->
        <div class="mb-8">
            <h4 class="text-sm font-bold text-gray-800 mb-3 border-b pb-2">Rincian Pembayaran & Pengiriman</h4>
            <div class="grid grid-cols-3 gap-4 text-sm">
                <div>
                    <span class="text-gray-500 block">Metode Pembayaran:</span>
                    <span class="font-semibold text-gray-800">{{ strtoupper($transaction->payment_method) }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block">Kurir / Pengiriman:</span>
                    <span class="font-semibold text-gray-800">{{ strtoupper($transaction->courier ?? '-') }} / {{ strtoupper($transaction->delivery_type ?? '-') }}</span>
                </div>
                <div>
                    <span class="text-gray-500 block">Nomor Resi:</span>
                    <span class="font-mono font-semibold text-gray-800">{{ $transaction->tracking_number ?? 'Belum ada' }}</span>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <div class="mb-8 overflow-hidden border border-gray-200 rounded-lg">
            <table class="w-full text-left text-sm text-gray-600">
                <thead class="bg-gray-50 text-gray-700 text-xs uppercase border-b border-gray-200">
                    <tr>
                        <th class="px-5 py-3 font-semibold">Deskripsi Produk</th>
                        <th class="px-5 py-3 font-semibold text-center hidden md:table-cell">Kuantitas</th>
                        <th class="px-5 py-3 font-semibold text-right">Harga Satuan</th>
                        <th class="px-5 py-3 font-semibold text-right text-gray-900 bg-gray-100">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @php $itemsSubtotal = 0; @endphp
                    @foreach($transaction->items as $item)
                    @php $itemsSubtotal += ($item->price * $item->quantity); @endphp
                    <tr class="hover:bg-gray-50/50">
                        <td class="px-5 py-4">
                            <div class="font-medium text-gray-800">{{ $item->product ? $item->product->name : 'Produk Tidak Ditetapkan' }}</div>
                        </td>
                        <td class="px-5 py-4 text-center hidden md:table-cell">{{ $item->quantity }}</td>
                        <td class="px-5 py-4 text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                        <td class="px-5 py-4 text-right font-medium text-gray-800 bg-gray-50 border-l border-gray-100">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals Summary -->
        <div class="flex justify-end">
            <div class="w-full md:w-1/2 lg:w-5/12 space-y-3 text-sm">
                <div class="flex justify-between items-center text-gray-600">
                    <span>Subtotal Produk</span>
                    <span class="font-medium">Rp {{ number_format($itemsSubtotal, 0, ',', '.') }}</span>
                </div>
                
                <div class="flex justify-between items-center text-gray-600">
                    <span>Ongkos Kirim</span>
                    <span class="font-medium">Rp {{ number_format($transaction->shipping_cost ?? 0, 0, ',', '.') }}</span>
                </div>

                <div class="flex justify-between items-center text-gray-600">
                    <span>Biaya Layanan (Platform)</span>
                    <span class="font-medium">Rp {{ number_format($transaction->service_fee ?? 0, 0, ',', '.') }}</span>
                </div>

                @if($transaction->admin_fee > 0)
                <div class="flex justify-between items-center text-gray-600">
                    <span>Biaya Admin (Gateway)</span>
                    <span class="font-medium">Rp {{ number_format($transaction->admin_fee, 0, ',', '.') }}</span>
                </div>
                @endif

                @if($transaction->discount_total > 0)
                <div class="flex justify-between items-center text-red-500 font-medium">
                    <span>
                        Diskon Voucher
                        @if($transaction->voucher_code)
                            <span class="text-xs bg-red-100 text-red-600 px-1.5 py-0.5 rounded ml-1">{{ $transaction->voucher_code }}</span>
                        @endif
                    </span>
                    <span>- Rp {{ number_format($transaction->discount_total, 0, ',', '.') }}</span>
                </div>
                @endif
                
                <div class="pt-4 mt-2 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <span class="text-lg font-bold text-gray-800">Total Pembayaran</span>
                        <span class="text-xl font-black text-orange-600">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Note -->
        <div class="mt-16 pt-8 border-t border-dashed border-gray-200 text-center text-xs text-gray-400">
            <p>Ini adalah dokumen sah secara elektronik yang diterbitkan oleh sistem TechnoMarket.</p>
            <p>Terima kasih telah berbelanja menggunakan platform kami!</p>
        </div>
    </div>
</body>
</html>
