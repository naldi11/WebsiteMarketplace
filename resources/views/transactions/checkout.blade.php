@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            }
            50% {
                transform: scale(1.02);
                box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
            }
        }
    </style>
    
    <section class="py-5">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cart.index') }}">Keranjang</a></li>
                    <li class="breadcrumb-item active">Checkout</li>
                </ol>
            </nav>

            <h2 class="display-7 text-uppercase mb-4">Checkout</h2>

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('transactions.store_cart') }}" method="POST" id="checkoutForm">
                @csrf
                <input type="hidden" name="selected_items" value="{{ $selectedItemString }}">
                @if(isset($appliedVoucher) && $appliedVoucher)
                    <input type="hidden" name="voucher_code" value="{{ $appliedVoucher['code'] }}">
                @endif

                <div class="row">
                    <!-- Left Column -->
                    <div class="col-lg-8">
                        <!-- Address Section -->
                        <div class="card mb-4">
                            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                                <span><strong>1.</strong> Alamat Pengiriman</span>
                                <a href="{{ route('addresses.index') }}" class="btn btn-sm btn-outline-light">Kelola</a>
                            </div>
                            <div class="card-body">
                                @if($addresses->count() > 0)
                                    <div class="row">
                                        @foreach($addresses as $address)
                                            <div class="col-md-6 mb-3">
                                                <label class="d-block h-100">
                                                    <input type="radio" name="address_id" value="{{ $address->id }}" 
                                                        class="d-none address-radio"
                                                        {{ $address->is_default ? 'checked' : '' }}
                                                        data-lat="{{ $address->latitude }}"
                                                        data-lng="{{ $address->longitude }}"
                                                        data-address="{{ $address->formatted_address }}">
                                                    <div class="card h-100 address-card" style="cursor: pointer;">
                                                        <div class="card-body">
                                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                                <h6 class="mb-0">{{ $address->label_icon }} {{ $address->label }}</h6>
                                                                @if($address->is_default)
                                                                    <span class="badge bg-primary">Utama</span>
                                                                @endif
                                                            </div>
                                                            <p class="mb-1"><strong>{{ $address->recipient_name }}</strong></p>
                                                            <p class="mb-1 text-muted small">{{ $address->phone }}</p>
                                                            <p class="mb-0 text-muted small">{{ Str::limit($address->formatted_address, 80) }}</p>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                    <div id="mapPreview" class="mt-3" style="height: 200px; display: none;"></div>
                                    <input type="hidden" name="address" id="selectedAddressText">
                                @else
                                    <div class="text-center py-4">
                                        <p class="text-muted mb-3">Belum ada alamat tersimpan</p>
                                        <a href="{{ route('addresses.index') }}" class="btn btn-dark">Tambah Alamat</a>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Payment Method -->
                        <div class="mb-4">
                            <div class="text-center mb-4">
                                <div class="d-inline-block px-4 py-2" style="background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%); border-radius: 30px;">
                                    <h5 class="mb-0 text-white fw-bold">
                                        <span class="badge bg-white text-dark me-2" style="border-radius: 50%; width: 30px; height: 30px; line-height: 20px;">2</span>
                                        💳 Metode Pembayaran
                                    </h5>
                                </div>
                            </div>

                            @forelse($paymentMethods as $type => $methods)
                                <!-- Payment Type Group -->
                                <div class="mb-4">
                                    <div class="d-flex align-items-center mb-3 px-3">
                                        <div style="flex: 1; height: 2px; background: linear-gradient(to right, transparent, #dee2e6);"></div>
                                        <h6 class="mx-3 mb-0 text-muted fw-bold text-uppercase" style="font-size: 12px; letter-spacing: 1px;">
                                            {{ $methods->first()->type_icon }} {{ $methods->first()->type_label }}
                                        </h6>
                                        <div style="flex: 1; height: 2px; background: linear-gradient(to left, transparent, #dee2e6);"></div>
                                    </div>
                                    
                                    <div class="row g-3">
                                        @foreach($methods as $method)
                                            <div class="col-md-6">
                                                <label class="d-block h-100" style="cursor: pointer;">
                                                    <input type="radio" name="payment_method" value="{{ $method->code }}" 
                                                        class="d-none payment-radio"
                                                        data-fee="{{ $method->admin_fee ?? 0 }}"
                                                        data-fee-percent="{{ $method->admin_fee_percent ?? 0 }}"
                                                        {{ $loop->parent->first && $loop->first ? 'checked' : '' }}>
                                                    
                                                    <div class="payment-method-card h-100 position-relative overflow-hidden" 
                                                         style="background: white;
                                                                border: 2px solid #e9ecef;
                                                                border-radius: 15px;
                                                                transition: all 0.3s ease;
                                                                box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                                                        
                                                        <!-- Gradient overlay when selected -->
                                                        <div class="selected-overlay position-absolute w-100 h-100" 
                                                             style="top: 0; left: 0; 
                                                                    background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
                                                                    opacity: 0;
                                                                    transition: opacity 0.3s ease;
                                                                    pointer-events: none;"></div>
                                                        
                                                        <div class="card-body p-3 position-relative">
                                                            <div class="d-flex align-items-start gap-3">
                                                                <!-- Icon -->
                                                                <div class="payment-icon d-flex align-items-center justify-content-center flex-shrink-0" 
                                                                     style="width: 50px; height: 50px; 
                                                                            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
                                                                            border-radius: 12px;
                                                                            font-size: 24px;
                                                                            transition: all 0.3s ease;">
                                                                    {{ $method->icon }}
                                                                </div>
                                                                
                                                                <!-- Content -->
                                                                <div class="flex-grow-1">
                                                                    <div class="fw-bold text-dark mb-1" style="font-size: 15px;">
                                                                        {{ $method->name }}
                                                                    </div>
                                                                    
                                                                    @if($method->account_number)
                                                                        <div class="d-flex align-items-center gap-2 mb-2">
                                                                            <span class="badge" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); font-size: 10px; padding: 4px 8px;">
                                                                                No. Rekening
                                                                            </span>
                                                                            <code class="text-dark" style="font-size: 13px; background: #f8f9fa; padding: 2px 8px; border-radius: 6px;">
                                                                                {{ $method->account_number }}
                                                                            </code>
                                                                        </div>
                                                                    @endif
                                                                    
                                                                    @if($method->instructions)
                                                                        <p class="text-muted mb-0 small" style="font-size: 11px; line-height: 1.4;">
                                                                            {{ Str::limit($method->instructions, 60) }}
                                                                        </p>
                                                                    @endif
                                                                </div>
                                                                
                                                                <!-- Check indicator -->
                                                                <div class="check-indicator position-absolute" 
                                                                     style="top: 10px; right: 10px; 
                                                                            width: 24px; height: 24px;
                                                                            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                                                                            border-radius: 50%;
                                                                            display: flex;
                                                                            align-items: center;
                                                                            justify-content: center;
                                                                            opacity: 0;
                                                                            transform: scale(0.5);
                                                                            transition: all 0.3s ease;">
                                                                    <svg width="12" height="12" fill="white" viewBox="0 0 16 16">
                                                                        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
                                                                    </svg>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <div class="mb-3" style="font-size: 48px; opacity: 0.3;">💳</div>
                                    <p class="text-muted">Tidak ada metode pembayaran tersedia</p>
                                </div>
                            @endforelse
                        </div>

                        <!-- Notes -->
                        <div class="card mb-4">
                            <div class="card-header bg-dark text-white">
                                <strong>3.</strong> Catatan (Opsional)
                            </div>
                            <div class="card-body">
                                <textarea name="notes" class="form-control" rows="2" placeholder="Catatan untuk penjual..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column - Summary -->
                    <div class="col-lg-4">
                        <div class="card sticky-top" style="top: 15px;">
                            <div class="card-header bg-dark text-white text-uppercase">
                                Ringkasan Pesanan
                            </div>
                            <div class="card-body">
                                <!-- Items -->
                                <div class="mb-3">
                                    @foreach($cartItems as $item)
                                        <div class="d-flex gap-3 mb-3 pb-3 border-bottom">
                                            <img src="{{ Storage::url($item->product->image) }}" style="width: 50px; height: 50px; object-fit: cover;">
                                            <div class="flex-grow-1">
                                                <p class="mb-0 small">{{ Str::limit($item->product->name, 30) }}</p>
                                                @if($item->product->hasDiscount())
                                                    <small class="text-muted">{{ $item->quantity }}x @ <span class="text-decoration-line-through">Rp {{ number_format($item->product->price, 0, ',', '.') }}</span></small>
                                                    <small class="text-success fw-bold d-block">Rp {{ number_format($item->product->effective_price, 0, ',', '.') }}</small>
                                                @else
                                                    <small class="text-muted">{{ $item->quantity }}x @ Rp {{ number_format($item->product->price, 0, ',', '.') }}</small>
                                                @endif
                                            </div>
                                            <div>
                                                <span style="color: var(--primary-color)">Rp {{ number_format($item->product->effective_price * $item->quantity, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <!-- Voucher Input -->
                                <div class="mb-3 pb-3 border-bottom">
                                    <label class="form-label small fw-bold">Kode Voucher</label>
                                    @if(isset($appliedVoucher) && $appliedVoucher)
                                        <div class="alert alert-success d-flex justify-content-between align-items-center p-2 mb-0">
                                            <div>
                                                <span class="fw-bold">{{ $appliedVoucher['code'] }}</span>
                                                <div class="small text-success">- Rp {{ number_format($discountAmount ?? 0, 0, ',', '.') }}</div>
                                                @if(isset($appliedVoucher['terms']) && $appliedVoucher['terms'])
                                                    <div class="small text-muted mt-1" style="font-size: 11px; font-style: italic;">
                                                        S&K: {{ $appliedVoucher['terms'] }}
                                                    </div>
                                                @endif
                                            </div>
                                            <button type="button" onclick="removeVoucher()" class="btn btn-link btn-sm text-danger p-0 text-decoration-none" title="Hapus Voucher">
                                                <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </div>
                                    @else
                                        <div class="d-flex gap-2 w-100">
                                            <input type="text" id="voucherCodeInput" class="form-control form-control-sm flex-grow-1" style="width: auto;" placeholder="Kode Promo">
                                            <button type="button" onclick="applyVoucher()" class="btn btn-dark btn-sm text-nowrap">Gunakan</button>
                                        </div>
                                        @if(session('voucher_error'))
                                            <div class="text-danger small mt-1">{{ session('voucher_error') }}</div>
                                        @endif
                                        @if(session('error'))
                                            <div class="text-danger small mt-1">{{ session('error') }}</div>
                                        @endif
                                    @endif
                                </div>

                                <div class="d-flex justify-content-between mb-2">
                                    <span>Subtotal</span>
                                    <span>Rp {{ number_format($subtotal, 0, ',', '.') }}</span>
                                </div>
                                
                                @if(isset($discountAmount) && $discountAmount > 0)
                                    <div class="d-flex justify-content-between mb-2 text-success">
                                        <span>Diskon Voucher</span>
                                        <span>- Rp {{ number_format($discountAmount, 0, ',', '.') }}</span>
                                    </div>
                                @endif
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Biaya Layanan</span>
                                    <span>Rp {{ number_format($serviceFee, 0, ',', '.') }}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2" id="adminFeeRow" style="display: none !important;">
                                    <span>Biaya Admin (Pembayaran)</span>
                                    <span id="adminFeeAmount">Rp 0</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <strong>Total</strong>
                                    <strong style="color: var(--primary-color)" id="grandTotalDisplay">Rp {{ number_format($totalPrice, 0, ',', '.') }}</strong>
                                </div>

                                <!-- Escrow Notice -->
                                <div class="alert alert-info small mb-3">
                                    <strong>🔒 Pembayaran Aman</strong><br>
                                    Dana ditahan sampai barang diterima.
                                </div>

                                <button type="submit" class="btn btn-dark text-uppercase w-100">
                                    Bayar Sekarang
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        let map = null;
        let marker = null;

        // Form Validation - Payment Method Required
        document.getElementById('checkoutForm').addEventListener('submit', function(e) {
            const paymentMethod = document.querySelector('input[name="payment_method"]:checked');
            
            if (!paymentMethod) {
                e.preventDefault();
                
                // Show SweetAlert error
                Swal.fire({
                    icon: 'warning',
                    title: 'Metode Pembayaran Belum Dipilih',
                    text: 'Silakan pilih metode pembayaran terlebih dahulu untuk melanjutkan checkout.',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#667eea'
                }).then(() => {
                    // Smooth scroll to payment section
                    const paymentSection = document.querySelector('.payment-method-card');
                    if (paymentSection) {
                        paymentSection.closest('.mb-4').scrollIntoView({ 
                            behavior: 'smooth', 
                            block: 'center' 
                        });
                        
                        // Add pulse animation to first payment card
                        const firstCard = document.querySelector('.payment-method-card');
                        firstCard.style.animation = 'pulse 0.5s ease-in-out 3';
                        setTimeout(() => {
                            firstCard.style.animation = '';
                        }, 1500);
                    }
                });
                
                return false;
            }
        });

        // Address card selection
        document.querySelectorAll('.address-radio').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.address-card').forEach(c => c.classList.remove('border-primary', 'bg-light'));
                this.closest('label').querySelector('.address-card').classList.add('border-primary', 'bg-light');
                
                const lat = parseFloat(this.dataset.lat);
                const lng = parseFloat(this.dataset.lng);
                document.getElementById('selectedAddressText').value = this.dataset.address;
                
                if (lat && lng) {
                    document.getElementById('mapPreview').style.display = 'block';
                    if (!map) {
                        map = L.map('mapPreview').setView([lat, lng], 15);
                        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
                    } else {
                        map.setView([lat, lng], 15);
                    }
                    if (marker) map.removeLayer(marker);
                    marker = L.marker([lat, lng]).addTo(map);
                }
            });
        });


        // Payment method selection with new design
        document.querySelectorAll('.payment-radio').forEach(radio => {
            radio.addEventListener('change', function() {
                // Remove selection from all cards
                document.querySelectorAll('.payment-method-card').forEach(card => {
                    card.style.border = '2px solid #e9ecef';
                    card.querySelector('.selected-overlay').style.opacity = '0';
                    card.querySelector('.check-indicator').style.opacity = '0';
                    card.querySelector('.check-indicator').style.transform = 'scale(0.5)';
                    card.querySelector('.payment-icon').style.background = 'linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%)';
                });
                
                // Add selection to checked card
                const selectedCard = this.closest('label').querySelector('.payment-method-card');
                selectedCard.style.border = '2px solid #667eea';
                selectedCard.querySelector('.selected-overlay').style.opacity = '1';
                selectedCard.querySelector('.check-indicator').style.opacity = '1';
                selectedCard.querySelector('.check-indicator').style.transform = 'scale(1)';
                selectedCard.querySelector('.payment-icon').style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                
                // Calculate and update Admin Fee display
                calculateAdminFee(this);
            });
        });

        // Backend prices for calculation
        const baseSubtotalRaw = {{ $subtotal }};
        const baseTotalPriceRow = {{ $totalPrice }};
        
        function calculateAdminFee(radioInput) {
            const fee = parseFloat(radioInput.dataset.fee) || 0;
            const feePercent = parseFloat(radioInput.dataset.feePercent) || 0;
            
            let adminFee = 0;
            if (feePercent > 0) {
                adminFee += Math.ceil(baseSubtotalRaw * feePercent / 100);
            }
            if (fee > 0) {
                adminFee += fee;
            }
            
            const adminFeeRow = document.getElementById('adminFeeRow');
            const adminFeeAmount = document.getElementById('adminFeeAmount');
            const grandTotalDisplay = document.getElementById('grandTotalDisplay');
            
            if (adminFee > 0) {
                adminFeeRow.style.setProperty('display', 'flex', 'important');
                adminFeeAmount.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(adminFee);
            } else {
                adminFeeRow.style.setProperty('display', 'none', 'important');
            }
            
            // Re-calculate grand total
            const newTotal = baseTotalPriceRow + adminFee;
            grandTotalDisplay.textContent = 'Rp ' + new Intl.NumberFormat('id-ID').format(newTotal);
        }

        // Add hover effects for payment method cards
        document.querySelectorAll('.payment-method-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                if (!this.querySelector('.payment-radio').checked) {
                    this.style.transform = 'translateY(-4px)';
                    this.style.boxShadow = '0 8px 24px rgba(102, 126, 234, 0.25)';
                }
            });
            card.addEventListener('mouseleave', function() {
                if (!this.querySelector('.payment-radio').checked) {
                    this.style.transform = 'translateY(0)';
                    this.style.boxShadow = '0 2px 8px rgba(0,0,0,0.05)';
                }
            });
        });

        // Initialize default selections
        document.querySelector('.address-radio:checked')?.dispatchEvent(new Event('change'));
        document.querySelector('.payment-radio:checked')?.dispatchEvent(new Event('change'));

        // Apply Voucher Function
        function applyVoucher() {
            const code = document.getElementById('voucherCodeInput').value.trim();
            if (!code) {
                alert('Please enter a voucher code');
                return;
            }

            fetch('{{ route("checkout.voucher") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ code: code })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Reload to show discount
                } else {
                    alert(data.message || 'Voucher tidak valid');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menggunakan voucher');
            });
        }

        // Remove Voucher Function
        function removeVoucher() {
            if (!confirm('Hapus voucher yang sudah diterapkan?')) return;

            fetch('{{ route("checkout.voucher.remove") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                location.reload(); // Reload to remove discount
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Terjadi kesalahan saat menghapus voucher');
            });
        }
    </script>
    @endpush
@endsection