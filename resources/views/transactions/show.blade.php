@extends('layouts.app')

@section('content')
    <div class="py-3 px-3">
        <div class="max-w-full mx-auto">
            {{-- Back Button --}}
            <a href="{{ route('transactions.history') }}"
                class="inline-flex items-center text-gray-600 hover:text-pink-600 font-medium transition mb-4">
                <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Kembali
            </a>

            {{-- Main Card --}}
            <div class="bg-white rounded-2xl shadow-sm border-2 border-gray-200 overflow-hidden">
                {{-- Header --}}
                <div class="bg-gradient-to-r from-pink-500 to-pink-600 px-6 py-4 flex justify-between items-center">
                    <div>
                        <h2 class="text-lg font-bold text-white">Detail Transaksi</h2>
                        <p class="text-pink-100 text-xs">{{ $transaction->created_at->format('d M Y, H:i') }}</p>
                    </div>
                    <span class="px-3 py-1 bg-white/20 text-white rounded-full text-sm font-bold">#{{ $transaction->id }}</span>
                </div>

                <div class="p-6 space-y-6">
                    {{-- Admin Verification Panel --}}
                    @if(auth()->user()->role == 'admin' && $transaction->status == 'pending')
                        <div class="bg-yellow-50 p-4 rounded-xl border border-yellow-200 mb-6">
                            <h3 class="font-bold text-yellow-800 text-sm mb-2">👮 Panel Admin: Verifikasi Pembayaran</h3>
                            <div class="flex items-center gap-4">
                                @if($transaction->payment_proof)
                                    <a href="{{ Storage::url($transaction->payment_proof) }}" target="_blank"
                                        class="text-blue-600 hover:text-blue-800 underline text-sm font-semibold">
                                        Lihat Bukti
                                    </a>
                                @endif
                                <form action="{{ route('admin.verify', $transaction) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-bold text-sm shadow-md transition">
                                        ✅ Verifikasi Pembayaran
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    {{-- Order Tracking Timeline --}}
                    @if($transaction->trackingLogs->count() > 0)
                        <div class="bg-gray-50 rounded-xl p-4 border border-gray-100">
                            <h3 class="font-bold text-gray-900 text-sm mb-4 flex items-center gap-2">
                                <span>📍</span> Lacak Pesanan
                            </h3>
                            <div class="relative">
                                <div class="absolute left-3 top-2 bottom-2 w-0.5 bg-gray-200"></div>
                                <div class="space-y-4">
                                    @foreach($transaction->trackingLogs->reverse() as $log)
                                        @php
                                            $statusInfo = $log->statusInfo;
                                            $colorClasses = [
                                                'gray' => 'bg-gray-100 text-gray-600',
                                                'yellow' => 'bg-yellow-100 text-yellow-600',
                                                'green' => 'bg-green-100 text-green-600',
                                                'blue' => 'bg-blue-100 text-blue-600',
                                                'purple' => 'bg-purple-100 text-purple-600',
                                                'teal' => 'bg-teal-100 text-teal-600',
                                            ];
                                            $bgColor = $colorClasses[$statusInfo['color']] ?? 'bg-gray-100 text-gray-600';
                                        @endphp
                                        <div class="relative flex gap-3 pl-8">
                                            <div class="absolute left-0 w-6 h-6 rounded-full {{ $bgColor }} flex items-center justify-center text-sm z-10">
                                                {{ $statusInfo['icon'] }}
                                            </div>
                                            <div class="flex-1 pb-3 {{ !$loop->last ? 'border-b border-gray-100' : '' }}">
                                                <div class="flex justify-between items-start">
                                                    <div>
                                                        <p class="font-bold text-gray-900 text-sm">{{ $log->title }}</p>
                                                        @if($log->description)
                                                            <p class="text-xs text-gray-500 mt-0.5">{{ $log->description }}</p>
                                                        @endif
                                                    </div>
                                                    <span class="text-[10px] text-gray-400 shrink-0">{{ $log->created_at->format('d/m H:i') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Seller Update Status Buttons (Before Shipping) --}}
                    @if($transaction->seller_id == auth()->id() && $transaction->status == 'paid_verified')
                        <div class="bg-blue-50 p-4 rounded-xl border border-blue-100">
                            <h4 class="font-bold text-blue-800 text-sm mb-3 flex items-center gap-2">
                                <span>📦</span> Update Status Pesanan
                            </h4>
                            <div class="flex flex-wrap gap-2 mb-4">
                                <form action="{{ route('transactions.updateStatus', $transaction) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="status" value="processing">
                                    <button type="submit" class="px-3 py-1.5 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-xs font-bold transition">
                                        📦 Diproses
                                    </button>
                                </form>
                                <form action="{{ route('transactions.updateStatus', $transaction) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="status" value="packaging">
                                    <button type="submit" class="px-3 py-1.5 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-xs font-bold transition">
                                        🎁 Dikemas
                                    </button>
                                </form>
                                <form action="{{ route('transactions.updateStatus', $transaction) }}" method="POST" class="inline">
                                    @csrf
                                    <input type="hidden" name="status" value="ready_to_ship">
                                    <button type="submit" class="px-3 py-1.5 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg text-xs font-bold transition">
                                        ✨ Siap Kirim
                                    </button>
                                </form>
                            </div>
                            
                            {{-- Input Resi Form --}}
                            <form action="{{ route('transactions.ship', $transaction) }}" method="POST" class="space-y-3 pt-3 border-t border-blue-200">
                                @csrf
                                <p class="text-xs text-blue-600 font-bold">Masukkan Resi Pengiriman</p>
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <select name="courier" required class="w-full rounded-xl border-gray-200 text-sm focus:border-pink-500 focus:ring-pink-500">
                                            <option value="">Pilih Kurir</option>
                                            <option value="jne">JNE</option>
                                            <option value="jnt">J&T Express</option>
                                            <option value="sicepat">SiCepat</option>
                                            <option value="anteraja">AnterAja</option>
                                            <option value="pos">POS Indonesia</option>
                                            <option value="tiki">TIKI</option>
                                            <option value="gosend">GoSend</option>
                                            <option value="grab">GrabExpress</option>
                                            <option value="lainnya">Lainnya</option>
                                        </select>
                                    </div>
                                    <div>
                                        <input type="text" name="tracking_number" required 
                                            class="w-full rounded-xl border-gray-200 text-sm focus:border-pink-500 focus:ring-pink-500"
                                            placeholder="Nomor Resi">
                                    </div>
                                </div>
                                <button type="submit" 
                                    class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-2.5 rounded-xl shadow-lg shadow-pink-500/30 transition">
                                    🚚 Konfirmasi Pengiriman
                                </button>
                            </form>
                        </div>
                    @endif

                    {{-- Shipping Info (After Shipped) --}}
                    @if($transaction->tracking_number && in_array($transaction->status, ['shipped', 'received', 'completed']))
                        <div class="bg-purple-50 p-4 rounded-xl border border-purple-100">
                            <div class="flex items-start gap-3">
                                <span class="text-xl">🚚</span>
                                <div class="flex-1">
                                    <p class="text-xs text-purple-600 font-bold mb-1">Informasi Pengiriman</p>
                                    <p class="font-bold text-gray-900">{{ strtoupper($transaction->courier) }}</p>
                                    <p class="text-sm text-gray-600">No. Resi: <span class="font-mono font-bold">{{ $transaction->tracking_number }}</span></p>
                                    @if($transaction->shipped_at)
                                        <p class="text-xs text-gray-500 mt-1">Dikirim: {{ $transaction->shipped_at->format('d M Y, H:i') }}</p>
                                    @endif
                                    
                                    {{-- Tracking Links --}}
                                    @if($transaction->tracking_url)
                                        <a href="{{ $transaction->tracking_url }}" target="_blank" 
                                            class="inline-flex items-center gap-1 mt-2 px-3 py-1.5 bg-purple-600 text-white rounded-lg text-xs font-bold hover:bg-purple-700 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                                            </svg>
                                            Lacak di {{ strtoupper($transaction->courier) }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif

                    {{-- Shipping Address --}}
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <div class="flex items-start gap-3">
                            <span class="text-xl">📍</span>
                            <div class="flex-1">
                                <p class="text-xs text-gray-500 mb-1">Alamat Pengiriman</p>
                                <p class="font-medium text-gray-900 text-sm">{{ $transaction->shipping_address }}</p>
                            </div>
                        </div>
                        @if($transaction->message)
                            <div class="mt-3 pt-3 border-t border-gray-200">
                                <p class="text-xs text-gray-500 mb-1">Catatan Pembeli</p>
                                <p class="text-sm text-gray-700">{{ $transaction->message }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Items List --}}
                    <div>
                        <h3 class="font-bold text-gray-900 text-sm mb-3">Barang yang Dibeli</h3>
                        <div class="space-y-3">
                            @foreach($transaction->items as $item)
                                <div class="flex gap-3 p-3 bg-gray-50 rounded-xl">
                                    <img src="{{ Storage::url($item->product->image) }}"
                                        class="w-16 h-16 rounded-lg object-cover bg-gray-200">
                                    <div class="flex-1">
                                        <h4 class="font-bold text-gray-900 text-sm">{{ $item->product->name }}</h4>
                                        <div class="flex justify-between items-center mt-1">
                                            <span class="text-xs text-gray-500">{{ $item->quantity }} x Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                                            <span class="font-bold text-pink-600 text-sm">Rp {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</span>
                                        </div>
                                        @if($item->price < $item->product->price)
                                            <p class="text-[10px] text-green-600 mt-0.5">💰 Diskon dari Rp {{ number_format($item->product->price, 0, ',', '.') }}</p>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Payment Summary --}}
                    <div class="bg-gray-50 p-4 rounded-xl border border-gray-100">
                        <h4 class="font-bold text-gray-900 text-sm mb-3">Rincian Pembayaran</h4>
                        <div class="space-y-2 text-sm">
                            @php
                                $subtotal = $transaction->items->sum(fn($i) => $i->price * $i->quantity);
                            @endphp
                            <div class="flex justify-between text-gray-600">
                                <span>Subtotal Produk</span>
                                <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                            </div>
                            @if($transaction->service_fee > 0)
                                <div class="flex justify-between text-gray-600">
                                    <span>Biaya Layanan (10%)</span>
                                    <span>Rp {{ number_format($transaction->service_fee, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            @if($transaction->discount_total > 0)
                                <div class="flex justify-between text-green-600">
                                    <span>Diskon</span>
                                    <span>-Rp {{ number_format($transaction->discount_total, 0, ',', '.') }}</span>
                                </div>
                            @endif
                            <div class="flex justify-between items-center pt-2 border-t border-gray-200 text-lg">
                                <span class="font-bold text-gray-900">Total Pembayaran</span>
                                <span class="font-bold text-pink-600">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                            </div>
                        </div>
                        
                        {{-- Payment Method & Instructions --}}
                        @if($transaction->payment_method_code)
                            <div class="mt-3 pt-3 border-t border-gray-200">
                                <p class="text-xs text-gray-500">Metode Pembayaran</p>
                                <p class="font-bold text-gray-900 text-sm">{{ strtoupper($transaction->payment_method_code) }}</p>
                                
                                @php
                                    $pm = \App\Models\PaymentMethod::where('code', $transaction->payment_method_code)->first();
                                @endphp
                                @if($pm && $transaction->status == 'waiting_payment')
                                    <div class="mt-2 bg-blue-50 p-3 rounded-lg border border-blue-100">
                                        <p class="text-xs font-bold text-blue-800 mb-2">Instruksi Pembayaran:</p>
                                        <div class="text-xs text-blue-700 whitespace-pre-line">{{ $pm->instructions }}</div>
                                    </div>
                                @endif
                            </div>
                        @endif

                        @if($transaction->seller_id == auth()->id())
                            <div class="mt-3 pt-3 border-t border-gray-200 bg-green-50 p-3 rounded-lg -mx-1">
                                <p class="text-xs text-green-600 font-bold mb-1">💰 Pendapatan Anda</p>
                                <p class="text-lg font-bold text-green-700">Rp {{ number_format($transaction->seller_amount ?? $subtotal, 0, ',', '.') }}</p>
                                <p class="text-[10px] text-green-600">Setelah dipotong biaya layanan platform</p>
                            </div>
                            
                            @if($transaction->transfer_proof)
                                <div class="mt-3 pt-3 border-t border-gray-200">
                                    <a href="{{ Storage::url($transaction->transfer_proof) }}" target="_blank"
                                        class="block w-full text-center bg-green-100 hover:bg-green-200 text-green-800 font-bold py-2.5 rounded-xl transition">
                                        📄 Lihat Bukti Pencairan
                                    </a>
                                </div>
                            @endif
                        @endif
                    </div>

                    {{-- Action Buttons --}}
                    <div class="space-y-3">
                        {{-- Upload Payment Proof (Buyer) --}}
                        @if($transaction->buyer_id == auth()->id() && $transaction->status == 'waiting_payment')
                            <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl border-2 border-orange-300 shadow-sm overflow-hidden">
                                <!-- Header -->
                                <div class="bg-orange-500 px-4 py-3">
                                    <p class="text-white font-bold text-base">💳 Upload Bukti Pembayaran</p>
                                </div>
                                
                                <!-- Content -->
                                <div class="p-4">
                                    @if(session('success'))
                                        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 px-4 py-3 rounded mb-4 shadow-sm">
                                            <div class="flex items-center">
                                                <span class="text-2xl mr-3">✅</span>
                                                <span class="font-bold">{{ session('success') }}</span>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @if(session('error'))
                                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 px-4 py-3 rounded mb-4 shadow-sm">
                                            <div class="flex items-center">
                                                <span class="text-2xl mr-3">❌</span>
                                                <span class="font-bold">{{ session('error') }}</span>
                                            </div>
                                        </div>
                                    @endif
                                    
                                    @error('proof')
                                        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 px-3 py-2 rounded mb-4 text-sm">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                    
                                    <form action="{{ route('transactions.upload_proof', $transaction) }}" method="POST" enctype="multipart/form-data" id="uploadProofForm">
                                        @csrf
                                        
                                        <!-- File Input Area -->
                                        <div class="mb-4">
                                            <label for="proofFile" class="block">
                                                <div class="bg-white border-2 border-dashed border-orange-300 rounded-xl p-12 text-center cursor-pointer hover:border-orange-500 hover:bg-orange-50 transition-all duration-200">
                                                    <!-- Initial State -->
                                                    <div id="fileLabel">
                                                        <svg class="w-24 h-24 mx-auto text-orange-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                                        </svg>
                                                        <p class="text-xl font-bold text-gray-800 mb-2">Klik untuk Pilih File</p>
                                                        <p class="text-base text-gray-500">Format: JPG, PNG, PDF</p>
                                                        <p class="text-sm text-gray-400 mt-2">Maksimal 2MB</p>
                                                    </div>
                                                    
                                                    <!-- File Selected State -->
                                                    <div id="fileNameDisplay" class="hidden">
                                                        <div class="bg-green-50 border-2 border-green-400 rounded-lg p-5">
                                                            <div class="flex items-center justify-center mb-3">
                                                                <svg class="w-12 h-12 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                                                </svg>
                                                            </div>
                                                            <p class="text-lg text-green-600 font-bold mb-3 text-center">✅ File Terpilih</p>
                                                            <p class="text-2xl font-bold text-green-900 break-all text-center px-2" id="fileName"></p>
                                                            <div class="text-center mt-4">
                                                                <button type="button" onclick="resetFileInput()" class="inline-block px-5 py-3 bg-red-100 hover:bg-red-200 text-gray-900 rounded-lg font-bold text-base transition">
                                                                    Ganti File Lain
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                            <input type="file" name="proof" id="proofFile" accept="image/*,application/pdf" required class="hidden" onchange="showFileName(this)">
                                        </div>
                                        
                                        <!-- Submit Button -->
                                        <button type="submit" id="submitBtn" disabled 
                                            class="w-full bg-gray-300 text-gray-900 font-bold py-4 rounded-xl transition-all duration-200 cursor-not-allowed text-lg uppercase tracking-wide">
                                            Pilih File Terlebih Dahulu
                                        </button>
                                    </form>
                                </div>
                                
                                <script>
                                    function showFileName(input) {
                                        const fileLabel = document.getElementById('fileLabel');
                                        const fileNameDisplay = document.getElementById('fileNameDisplay');
                                        const fileName = document.getElementById('fileName');
                                        const submitBtn = document.getElementById('submitBtn');
                                        
                                        if (input.files && input.files[0]) {
                                            const file = input.files[0];
                                            
                                            // Check file size (2MB = 2048KB)
                                            if (file.size > 2048 * 1024) {
                                                alert('❌ File terlalu besar! Maksimal 2MB');
                                                input.value = '';
                                                return;
                                            }
                                            
                                            fileLabel.classList.add('hidden');
                                            fileNameDisplay.classList.remove('hidden');
                                            fileName.textContent = file.name;
                                            
                                            // Enable and style submit button
                                            submitBtn.disabled = false;
                                            submitBtn.className = 'w-full bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-gray-900 font-bold py-4 rounded-xl shadow-lg shadow-orange-500/30 transition-all duration-200 transform hover:scale-[1.02] text-lg uppercase tracking-wide';
                                            submitBtn.textContent = 'UPLOAD BUKTI PEMBAYARAN';
                                        }
                                    }
                                    
                                    function resetFileInput() {
                                        const fileInput = document.getElementById('proofFile');
                                        const fileLabel = document.getElementById('fileLabel');
                                        const fileNameDisplay = document.getElementById('fileNameDisplay');
                                        const submitBtn = document.getElementById('submitBtn');
                                        
                                        fileInput.value = '';
                                        fileLabel.classList.remove('hidden');
                                        fileNameDisplay.classList.add('hidden');
                                        
                                        // Reset submit button
                                        submitBtn.disabled = true;
                                        submitBtn.className = 'w-full bg-gray-300 text-gray-500 font-bold py-4 rounded-xl transition-all duration-200 cursor-not-allowed text-base uppercase tracking-wide';
                                        submitBtn.textContent = 'Pilih File Terlebih Dahulu';
                                    }
                                    
                                    // Form submit handler
                                    document.getElementById('uploadProofForm')?.addEventListener('submit', function(e) {
                                        const submitBtn = document.getElementById('submitBtn');
                                        submitBtn.disabled = true;
                                        submitBtn.className = 'w-full bg-gray-400 text-white font-bold py-4 rounded-xl cursor-wait text-base uppercase tracking-wide';
                                        submitBtn.innerHTML = '⏳ Sedang Mengupload... Mohon Tunggu';
                                    });
                                </script>
                            </div>
                        @endif

                        {{-- View Payment Proof --}}
                        @if($transaction->payment_proof)
                            <a href="{{ Storage::url($transaction->payment_proof) }}" target="_blank"
                                class="block w-full text-center bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold py-2.5 rounded-xl transition">
                                📄 Lihat Bukti Pembayaran
                            </a>
                        @endif

                        {{-- Confirm Received (Buyer) with Photos --}}
                        @if($transaction->buyer_id == auth()->id() && $transaction->status == 'shipped')
                            <div class="p-4 bg-green-50 rounded-xl border border-green-200">
                                <p class="text-sm font-bold text-green-700 mb-3">📦 Konfirmasi Barang Diterima</p>
                                <p class="text-xs text-green-600 mb-3">Upload foto barang yang diterima (min. 1, maks. 5 foto)</p>
                                
                                <form action="{{ route('transactions.confirm', $transaction) }}" method="POST" enctype="multipart/form-data" id="confirmForm">
                                    @csrf
                                    
                                    {{-- Photo Preview Container --}}
                                    <div id="photoPreview" class="grid grid-cols-3 gap-2 mb-3 hidden"></div>
                                    
                                    {{-- Photo Input Buttons --}}
                                    <div class="flex gap-2 mb-3">
                                        {{-- Camera Button --}}
                                        <label class="flex-1 cursor-pointer">
                                            <input type="file" name="receipt_photos[]" accept="image/*" capture="environment" 
                                                class="hidden" onchange="handlePhotoSelect(this)">
                                            <div class="flex items-center justify-center gap-2 bg-blue-100 hover:bg-blue-200 text-blue-700 font-bold py-3 rounded-xl transition">
                                                📷 Kamera
                                            </div>
                                        </label>
                                        
                                        {{-- Gallery Button --}}
                                        <label class="flex-1 cursor-pointer">
                                            <input type="file" name="receipt_photos[]" accept="image/*" multiple 
                                                class="hidden" onchange="handlePhotoSelect(this)">
                                            <div class="flex items-center justify-center gap-2 bg-purple-100 hover:bg-purple-200 text-purple-700 font-bold py-3 rounded-xl transition">
                                                🖼️ Galeri
                                            </div>
                                        </label>
                                    </div>
                                    
                                    {{-- Hidden container for actual file inputs --}}
                                    <div id="fileInputContainer"></div>
                                    
                                    {{-- Photo count indicator --}}
                                    <p id="photoCount" class="text-xs text-gray-500 mb-3 text-center hidden">
                                        <span id="photoCountNum">0</span>/5 foto dipilih
                                    </p>
                                    
                                    @error('receipt_photos')
                                        <p class="text-xs text-red-500 mb-2">{{ $message }}</p>
                                    @enderror
                                    
                                    <button type="submit" id="submitBtn" disabled
                                        class="w-full bg-gray-300 text-gray-500 font-bold py-3 rounded-xl transition cursor-not-allowed">
                                        ✅ Konfirmasi Diterima
                                    </button>
                                </form>
                            </div>
                            
                            <script>
                                let selectedFiles = [];
                                const maxPhotos = 5;
                                
                                function handlePhotoSelect(input) {
                                    const files = Array.from(input.files);
                                    
                                    files.forEach(file => {
                                        if (selectedFiles.length < maxPhotos) {
                                            selectedFiles.push(file);
                                        }
                                    });
                                    
                                    updatePreview();
                                    input.value = ''; // Reset input so same file can be selected again
                                }
                                
                                function removePhoto(index) {
                                    selectedFiles.splice(index, 1);
                                    updatePreview();
                                }
                                
                                function updatePreview() {
                                    const previewContainer = document.getElementById('photoPreview');
                                    const photoCount = document.getElementById('photoCount');
                                    const photoCountNum = document.getElementById('photoCountNum');
                                    const submitBtn = document.getElementById('submitBtn');
                                    const fileInputContainer = document.getElementById('fileInputContainer');
                                    
                                    previewContainer.innerHTML = '';
                                    fileInputContainer.innerHTML = '';
                                    
                                    if (selectedFiles.length > 0) {
                                        previewContainer.classList.remove('hidden');
                                        photoCount.classList.remove('hidden');
                                        submitBtn.disabled = false;
                                        submitBtn.className = 'w-full bg-green-500 hover:bg-green-600 text-white font-bold py-3 rounded-xl shadow-lg shadow-green-500/30 transition';
                                    } else {
                                        previewContainer.classList.add('hidden');
                                        photoCount.classList.add('hidden');
                                        submitBtn.disabled = true;
                                        submitBtn.className = 'w-full bg-gray-300 text-gray-500 font-bold py-3 rounded-xl transition cursor-not-allowed';
                                    }
                                    
                                    photoCountNum.textContent = selectedFiles.length;
                                    
                                    selectedFiles.forEach((file, index) => {
                                        // Create preview
                                        const reader = new FileReader();
                                        reader.onload = function(e) {
                                            const div = document.createElement('div');
                                            div.className = 'relative';
                                            div.innerHTML = `
                                                <img src="${e.target.result}" class="w-full h-20 object-cover rounded-lg">
                                                <button type="button" onclick="removePhoto(${index})" 
                                                    class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white rounded-full text-xs flex items-center justify-center hover:bg-red-600">
                                                    ×
                                                </button>
                                            `;
                                            previewContainer.appendChild(div);
                                        };
                                        reader.readAsDataURL(file);
                                        
                                        // Create hidden file input with DataTransfer
                                        const dt = new DataTransfer();
                                        dt.items.add(file);
                                        const input = document.createElement('input');
                                        input.type = 'file';
                                        input.name = 'receipt_photos[]';
                                        input.files = dt.files;
                                        input.style.display = 'none';
                                        fileInputContainer.appendChild(input);
                                    });
                                }
                            </script>
                        @endif
                    </div>

                    {{-- Review Section --}}
                    @if(($transaction->status == 'completed' || $transaction->status == 'received') && !$transaction->review && $transaction->buyer_id == auth()->id())
                        <div class="border-t pt-6">
                            <h3 class="text-sm font-bold text-gray-900 mb-4">⭐ Berikan Ulasan</h3>
                            <form action="{{ route('reviews.store', $transaction) }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-2">Rating</label>
                                    <div class="flex gap-2 flex-wrap">
                                        @for($i = 5; $i >= 1; $i--)
                                            <label class="cursor-pointer">
                                                <input type="radio" name="rating" value="{{ $i }}" class="hidden peer" {{ $i == 5 ? 'checked' : '' }}>
                                                <span class="block px-3 py-2 rounded-lg border-2 border-gray-200 peer-checked:border-yellow-400 peer-checked:bg-yellow-50 transition text-sm">
                                                    {{ str_repeat('⭐', $i) }}
                                                </span>
                                            </label>
                                        @endfor
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Komentar</label>
                                    <textarea name="comment" rows="3" required
                                        class="w-full rounded-xl border-gray-200 text-sm focus:border-pink-500 focus:ring-pink-500"
                                        placeholder="Bagaimana pengalaman belanja Anda?"></textarea>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Foto (Opsional)</label>
                                    <input type="file" name="photo" accept="image/*"
                                        class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-pink-50 file:text-pink-700 hover:file:bg-pink-100">
                                </div>
                                <button type="submit"
                                    class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-2.5 rounded-xl shadow-lg shadow-pink-500/30 transition">
                                    Kirim Ulasan
                                </button>
                            </form>
                        </div>
                    @endif

                    {{-- Display Review --}}
                    @if($transaction->review)
                        <div class="border-t pt-6">
                            <h3 class="text-sm font-bold text-gray-900 mb-3">Ulasan</h3>
                            <div class="bg-gray-50 p-4 rounded-xl">
                                <div class="flex items-center gap-2 mb-2">
                                    <span class="text-yellow-400">{{ str_repeat('⭐', $transaction->review->rating) }}</span>
                                    <span class="text-xs text-gray-500">{{ $transaction->review->created_at->format('d M Y') }}</span>
                                </div>
                                <p class="text-sm text-gray-700">{{ $transaction->review->comment }}</p>
                                @if($transaction->review->photo)
                                    <img src="{{ Storage::url($transaction->review->photo) }}" class="mt-3 rounded-lg max-h-40 object-cover">
                                @endif
                            </div>
                        </div>
                    @endif

                    {{-- Report Issue Section --}}
                    <div class="border-t pt-6 mt-6">
                        <div class="flex flex-col sm:flex-row justify-between items-center gap-4 bg-red-50 p-4 rounded-2xl border border-red-100">
                            <div>
                                <h4 class="text-sm font-bold text-red-800 flex items-center gap-2">
                                    <span>⚠️</span> Ada Masalah dengan Transaksi?
                                </h4>
                                <p class="text-xs text-red-600 mt-1">Jika barang tidak sampai, status macet, atau ada kecurangan, silakan lapor Admin.</p>
                            </div>
                            <button onclick="document.getElementById('reportModal').classList.remove('hidden')"
                                class="w-full sm:w-auto px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-xs font-bold rounded-xl shadow-md transition whitespace-nowrap">
                                📢 Lapor Masalah
                            </button>
                        </div>
                    </div>

                    {{-- Whatsapp Support Floating --}}
                    @php
                        $adminPhone = \App\Models\SystemSetting::where('key', 'admin_whatsapp')->first()->value ?? '628123456789';
                        $waText = urlencode("Halo Admin, saya mengalami masalah dengan Pesanan #" . $transaction->id . ". Status saat ini: " . $transaction->status);
                    @endphp
                    <div class="mt-4">
                        <a href="https://wa.me/{{ $adminPhone }}?text={{ $waText }}" target="_blank"
                            class="flex items-center justify-center gap-2 w-full p-3 bg-[#25D366] hover:bg-[#128C7E] text-white rounded-xl font-bold text-sm shadow-lg transition">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            Hubungi Admin via WhatsApp
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Report Modal --}}
    <div id="reportModal" class="fixed inset-0 z-50 overflow-y-auto hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" onclick="document.getElementById('reportModal').classList.add('hidden')"></div>
            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
            <div class="inline-block align-middle bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('reports.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="transaction_id" value="{{ $transaction->id }}">
                    <input type="hidden" name="type" value="{{ auth()->id() == $transaction->buyer_id ? 'buyer_issue' : 'seller_issue' }}">
                    
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10">
                                <span class="text-xl">📢</span>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">Laporkan Masalah</h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1">Alasan Melapor</label>
                                        <select name="reason" required class="w-full rounded-xl border-gray-200 text-sm focus:border-red-500 focus:ring-red-500">
                                            @if(auth()->id() == $transaction->buyer_id)
                                                <option value="Barang tidak sampai">Barang belum sampai / tidak dikirim</option>
                                                <option value="Barang tidak sesuai">Barang tidak sesuai deskripsi</option>
                                                <option value="Penjual tidak merespon">Penjual tidak merespon</option>
                                                <option value="Lainnya">Lainnya</option>
                                            @else
                                                <option value="Pembeli tidak membayar">Pembeli tidak mengunggah bukti bayar</option>
                                                <option value="Bukti bayar palsu">Bukti bayar tidak valid/palsu</option>
                                                <option value="Pembeli tidak bisa dihubungi">Pembeli tidak merespon</option>
                                                <option value="Lainnya">Lainnya</option>
                                            @endif
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1">Detail Masalah</label>
                                        <textarea name="description" rows="4" required
                                            class="w-full rounded-xl border-gray-200 text-sm focus:border-red-500 focus:ring-red-500"
                                            placeholder="Jelaskan secara detail masalah yang Anda alami..."></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse gap-2">
                        <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-red-600 text-base font-bold text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 sm:w-auto sm:text-sm">
                            Kirim Laporan
                        </button>
                        <button type="button" onclick="document.getElementById('reportModal').classList.add('hidden')"
                            class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-bold text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-pink-500 sm:mt-0 sm:w-auto sm:text-sm">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection