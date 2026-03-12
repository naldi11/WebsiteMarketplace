@extends('layouts.app')

@section('content')
    <section class="padding-large">
        <div class="container">
            <div class="row">
                <!-- Product Images -->
                <div class="col-lg-6 mb-4">
                    <div class="product-gallery">
                        <div class="main-image mb-3 bg-light rounded d-flex align-items-center justify-content-center"
                            style="height: 500px;">
                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}" class="img-fluid"
                                style="max-height: 100%; max-width: 100%; object-fit: contain;">
                        </div>
                        @if($product->images && count($product->images) > 0)
                            <div class="thumbnail-images d-flex gap-2">
                                <img src="{{ Storage::url($product->image) }}" alt="thumb" class="img-thumbnail active"
                                    style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;"
                                    onclick="changeMainImage(this.src)">
                                @foreach($product->images as $img)
                                    <img src="{{ Storage::url($img->image_path) }}" alt="thumb" class="img-thumbnail"
                                        style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;"
                                        onclick="changeMainImage(this.src)">
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Product Info -->
                <div class="col-lg-6">
                    <nav aria-label="breadcrumb" class="mb-3">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                            @if($product->category)
                                <li class="breadcrumb-item"><a
                                        href="{{ route('home', ['category' => $product->category->id]) }}">{{ $product->category->name }}</a>
                                </li>
                            @endif
                            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($product->name, 30) }}</li>
                        </ol>
                    </nav>

                    <h1 class="display-6 text-uppercase fw-bold mb-3">{{ $product->name }}</h1>

                    <div class="price mb-3">
                        @if($product->hasDiscount())
                            <div class="d-flex align-items-center gap-3">
                                <span class="fs-3 fw-bold" style="color: #dc3545;">Rp
                                    {{ number_format($product->discount_price, 0, ',', '.') }}</span>
                                <span class="badge bg-danger fs-6">-{{ $product->discount_percent }}%</span>
                            </div>
                            <span class="text-muted text-decoration-line-through fs-5">Rp
                                {{ number_format($product->price, 0, ',', '.') }}</span>
                        @else
                            <span class="fs-3 fw-bold" style="color: var(--primary-color)">Rp
                                {{ number_format($product->price, 0, ',', '.') }}</span>
                        @endif
                    </div>

                    <div class="product-meta mb-4">
                        <div class="d-flex gap-4 text-muted mb-2">
                            <span><strong>Stok:</strong> {{ $product->stock }} tersedia</span>
                            <span><strong>Kategori:</strong> {{ $product->category->name ?? 'Umum' }}</span>
                        </div>
                    </div>

                    <!-- Seller Info -->
                    <div class="seller-box border p-3 mb-4">
                        <div class="d-flex align-items-center gap-3">
                            <img src="{{ $product->user->avatar ? Storage::url($product->user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($product->user->name) }}"
                                alt="{{ $product->user->name }}" class="rounded-circle"
                                style="width: 50px; height: 50px; object-fit: cover;">
                            <div>
                                <h6 class="mb-0 fw-bold">{{ $product->user->name }}</h6>
                                <small class="text-muted">Penjual</small>
                            </div>
                        </div>
                        @if($product->user->phone)
                            <div class="mt-3">
                                <a href="https://wa.me/{{ preg_replace('/^0/', '62', $product->user->phone) }}?text={{ urlencode('Halo kak, saya tertarik dengan produk ' . $product->name . ' yang ada di MarketMahasiswa.') }}"
                                    target="_blank"
                                    class="btn btn-success btn-sm w-100 fw-bold text-uppercase d-flex align-items-center justify-content-center gap-2">
                                    <svg width="18" height="18" fill="currentColor" viewBox="0 0 16 16">
                                        <path
                                            d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.627.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.59-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654s.71 1.916.81 2.049c.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.338-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232" />
                                    </svg>
                                    Chat Penjual Via WhatsApp
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Actions -->
                @auth
                    @if($product->user_id !== auth()->id())
                        <div class="d-flex gap-3 mb-4">
                            <form action="{{ route('cart.store', $product) }}" method="POST" class="flex-grow-1">
                                @csrf
                                <div class="input-group">
                                    <input type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}"
                                        class="form-control" style="max-width: 100px;">
                                    <button type="submit" class="btn btn-dark btn-medium text-uppercase flex-grow-1">
                                        Tambah ke Keranjang
                                    </button>
                                </div>
                            </form>
                            <button type="button" class="btn btn-outline-dark" onclick="toggleWishlist({{ $product->id }}, this)">
                                <svg width="20" height="20"
                                    fill="{{ in_array($product->id, $wishlistIds ?? []) ? 'currentColor' : 'none' }}"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                    </path>
                                </svg>
                            </button>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <a href="{{ route('products.edit', $product) }}" class="btn btn-dark me-2">Edit Produk</a>
                            Ini produk Anda
                        </div>
                    @endif
                @else
                    <div class="alert alert-warning">
                        <a href="{{ route('login') }}" class="alert-link">Masuk</a> untuk membeli produk ini.
                    </div>
                @endauth

                <!-- Description -->
                <div class="product-description mt-4">
                    <h5 class="text-uppercase mb-3">Deskripsi</h5>
                    <div class="text-muted">
                        {!! nl2br(e($product->description)) !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- Reviews Section -->
        @if($product->reviews && $product->reviews->count() > 0)
            <div class="row mt-5">
                <div class="col-12">
                    <h4 class="text-uppercase mb-4">Ulasan ({{ $product->reviews->count() }})</h4>
                    <div class="row">
                        @foreach($product->reviews as $review)
                            <div class="col-md-6 mb-3">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="d-flex align-items-center mb-2">
                                            <img src="{{ optional($review->reviewer ?? $review->user)->avatar ? Storage::url(optional($review->reviewer ?? $review->user)->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode(optional($review->reviewer ?? $review->user)->name ?? 'User') }}"
                                                class="rounded-circle me-2" style="width: 40px; height: 40px; object-fit: cover;">
                                            <div>
                                                <h6 class="mb-0">
                                                    {{ optional($review->reviewer ?? $review->user)->name ?? 'Deleted User' }}</h6>
                                                <div class="rating">
                                                    @for($i = 1; $i <= 5; $i++)
                                                        <svg class="star-fill" width="14" height="14"
                                                            fill="{{ $i <= $review->rating ? 'var(--primary-color)' : '#ddd' }}">
                                                            <use xlink:href="#star-fill"></use>
                                                        </svg>
                                                    @endfor
                                                </div>
                                            </div>
                                        </div>
                                        <p class="text-muted mb-0">{{ $review->comment }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
        </div>
    </section>

    @push('scripts')
        <script>
            function changeMainImage(src) {
                document.querySelector('.main-image img').src = src;
            }

            function toggleWishlist(productId, btn) {
                fetch(`/wishlist/${productId}/toggle`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        const svg = btn.querySelector('svg');
                        const isFilled = svg.getAttribute('fill') === 'currentColor';
                        svg.setAttribute('fill', isFilled ? 'none' : 'currentColor');
                        Swal.fire({
                            icon: 'success',
                            title: isFilled ? 'Dihapus dari wishlist' : 'Ditambahkan ke wishlist',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    });
            }
        </script>
    @endpush
@endsection