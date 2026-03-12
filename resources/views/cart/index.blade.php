@extends('layouts.app')

@section('content')
    <section class="padding-large">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                    <li class="breadcrumb-item active">Keranjang</li>
                </ol>
            </nav>

            <h2 class="display-7 text-uppercase mb-4">Keranjang Belanja</h2>

            @if($cartItems->count() > 0)
                <form action="{{ route('checkout.review') }}" method="POST" id="cartForm">
                    @csrf
                    <div class="row">
                        <!-- Cart Items -->
                        <div class="col-lg-8">
                            @php
                                $groupedItems = $cartItems->groupBy(fn($item) => $item->product->user_id);
                            @endphp

                            @foreach($groupedItems as $sellerId => $items)
                                <div class="card mb-4">
                                    <div class="card-header bg-light d-flex align-items-center gap-3">
                                        <input type="checkbox" class="form-check-input seller-checkbox"
                                            data-seller="{{ $sellerId }}" checked>
                                        <img src="{{ $items->first()->product->user->avatar ? Storage::url($items->first()->product->user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($items->first()->product->user->name) }}"
                                            class="rounded-circle" style="width: 30px; height: 30px; object-fit: cover;">
                                        <strong>{{ $items->first()->product->user->name }}</strong>
                                    </div>
                                    <div class="card-body p-0">
                                        <table class="table table-borderless mb-0">
                                            <tbody>
                                                @foreach($items as $item)
                                                    <tr class="border-bottom">
                                                        <td style="width: 40px;" class="align-middle ps-3">
                                                            <input type="checkbox" name="items[]" value="{{ $item->id }}"
                                                                class="form-check-input item-checkbox" data-seller="{{ $sellerId }}"
                                                                data-price="{{ $item->product->effective_price }}"
                                                                data-qty="{{ $item->quantity }}" checked>
                                                        </td>
                                                        <td style="width: 100px;">
                                                            <img src="{{ Storage::url($item->product->image) }}"
                                                                alt="{{ $item->product->name }}" class="img-fluid"
                                                                style="width: 80px; height: 80px; object-fit: cover;">
                                                        </td>
                                                        <td class="align-middle">
                                                            <a href="{{ route('products.show', $item->product) }}"
                                                                class="text-dark text-decoration-none">
                                                                <h6 class="mb-1">{{ $item->product->name }}</h6>
                                                            </a>
                                                            <small class="text-muted">Stok: {{ $item->product->stock }}</small>
                                                        </td>
                                                        <td class="align-middle text-center" style="width: 150px;">
                                                            <div class="input-group input-group-sm" style="width: 120px;">
                                                                <button type="button" class="btn btn-outline-secondary qty-btn"
                                                                    data-action="decrease" data-id="{{ $item->id }}">−</button>
                                                                <input type="number" class="form-control text-center qty-input"
                                                                    value="{{ $item->quantity }}" min="1"
                                                                    max="{{ $item->product->stock }}" data-id="{{ $item->id }}"
                                                                    readonly>
                                                                <button type="button" class="btn btn-outline-secondary qty-btn"
                                                                    data-action="increase" data-id="{{ $item->id }}">+</button>
                                                            </div>
                                                        </td>
                                                        <td class="align-middle text-end" style="width: 150px;">
                                                            @if($item->product->hasDiscount())
                                                                <small class="text-muted text-decoration-line-through d-block">Rp
                                                                    {{ number_format($item->product->price * $item->quantity, 0, ',', '.') }}</small>
                                                                <span class="fw-bold" style="color: var(--primary-color)">Rp
                                                                    {{ number_format($item->product->effective_price * $item->quantity, 0, ',', '.') }}</span>
                                                            @else
                                                                <span class="fw-bold" style="color: var(--primary-color)">Rp
                                                                    {{ number_format($item->product->price * $item->quantity, 0, ',', '.') }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="align-middle text-end pe-3" style="width: 50px;">
                                                            <button type="button" class="btn btn-sm btn-outline-danger"
                                                                onclick="removeItem({{ $item->id }})">
                                                                <svg width="16" height="16" fill="none" stroke="currentColor"
                                                                    viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                                    </path>
                                                                </svg>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Summary -->
                        <div class="col-lg-4">
                            <div class="card sticky-top" style="top: 120px;">
                                <div class="card-header bg-dark text-white text-uppercase">
                                    Ringkasan Belanja
                                </div>
                                <div class="card-body">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Item dipilih</span>
                                        <span id="selectedCount">{{ $cartItems->count() }}</span>
                                    </div>
                                    <hr>
                                    <div class="d-flex justify-content-between mb-3">
                                        <strong>Total</strong>
                                        <strong id="totalPrice" style="color: var(--primary-color)">Rp
                                            {{ number_format($cartItems->sum(fn($item) => $item->product->effective_price * $item->quantity), 0, ',', '.') }}</strong>
                                    </div>
                                    <button type="submit" class="btn btn-dark text-uppercase w-100">
                                        Checkout
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            @else
                <div class="text-center py-5">
                    <svg class="mb-4" width="80" height="80" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        style="color: #ddd;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                            d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                    <h4 class="text-muted">Keranjang Kosong</h4>
                    <p class="text-muted">Yuk mulai belanja!</p>
                    <a href="{{ route('home') }}" class="btn btn-dark text-uppercase">Belanja Sekarang</a>
                </div>
            @endif
        </div>
    </section>

    @push('scripts')
        <script>
            // Checkbox logic
            document.querySelectorAll('.seller-checkbox').forEach(cb => {
                cb.addEventListener('change', function () {
                    const sellerId = this.dataset.seller;
                    document.querySelectorAll(`.item-checkbox[data-seller="${sellerId}"]`).forEach(item => {
                        item.checked = this.checked;
                    });
                    updateTotal();
                });
            });

            document.querySelectorAll('.item-checkbox').forEach(cb => {
                cb.addEventListener('change', updateTotal);
            });

            function updateTotal() {
                let total = 0;
                let count = 0;
                document.querySelectorAll('.item-checkbox:checked').forEach(cb => {
                    total += parseInt(cb.dataset.price) * parseInt(cb.dataset.qty);
                    count++;
                });
                document.getElementById('totalPrice').textContent = 'Rp ' + total.toLocaleString('id-ID');
                document.getElementById('selectedCount').textContent = count;
            }

            // Quantity buttons
            document.querySelectorAll('.qty-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const id = this.dataset.id;
                    const action = this.dataset.action;
                    const input = document.querySelector(`.qty-input[data-id="${id}"]`);
                    let qty = parseInt(input.value);

                    if (action === 'increase') qty++;
                    else if (action === 'decrease' && qty > 1) qty--;

                    input.value = qty;

                    // Update row price
                    const price = parseInt(document.querySelector(`.item-checkbox[value="${id}"]`).dataset.price);
                    const rowTotalElement = document.querySelector(`.qty-input[data-id="${id}"]`).closest('tr').querySelector('.text-end .fw-bold');
                    rowTotalElement.innerText = 'Rp ' + (price * qty).toLocaleString('id-ID');

                    // Update checkbox data
                    const checkbox = document.querySelector(`.item-checkbox[value="${id}"]`);
                    checkbox.dataset.qty = qty;
                    updateTotal();

                    // AJAX update
                    fetch(`/cart/${id}`, {
                        method: 'PUT',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ quantity: qty })
                    })
                        .then(res => res.json())
                        .then(data => {
                            if (data.cart_count !== undefined) {
                                document.querySelectorAll('a[href*="cart"]').forEach(link => {
                                    let badge = link.querySelector('.badge');
                                    if (!badge && data.cart_count > 0) {
                                        badge = document.createElement('span');
                                        badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                                        badge.style.fontSize = '10px';
                                        link.appendChild(badge);
                                    }
                                    if (badge) {
                                        badge.innerText = data.cart_count;
                                        if (data.cart_count === 0) badge.remove();
                                    }
                                });
                            }
                        });
                });
            });

            function removeItem(id) {
                Swal.fire({
                    title: 'Hapus item?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#212529',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`/cart/${id}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        }).then(() => location.reload());
                    }
                });
            }
        </script>
    @endpush
@endsection