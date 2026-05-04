@extends('layouts.app')

@section('content')
    <!-- Hero Section with Clean Slideshow -->
    <!-- Hero Section with Full Width Layout -->
    <!-- Hero Section with Full Width Layout -->
    <!-- Hero Section with Full Width Layout -->
    <section id="billboard" class="bg-light-blue position-relative overflow-hidden">
        <div class="container-fluid p-0">
            <!-- NOTE: Removed align-items-center so columns stretch (white bg fills height) -->
            <!-- NOTE: Removed align-items-center so columns stretch (white bg fills height) -->
            <div class="row g-0" style="min-height: 65vh;">
                <!-- Text Content -->
                <div class="col-lg-8 p-5 d-flex flex-column justify-content-center bg-white order-2 order-lg-1">
                    <div class="p-lg-4">
                        <h1 class="hero-title text-uppercase text-dark mb-3">Marketplace Mahasiswa</h1>
                        <p class="text-muted mb-4 lead">Jual beli barang dengan sesama mahasiswa. Aman dengan sistem escrow
                            payment.</p>
                        @auth
                            <a href="{{ route('products.create') }}"
                                class="btn btn-dark text-uppercase px-4 py-3 rounded-0">Jual Sekarang</a>
                        @else
                            <a href="{{ route('register') }}" class="btn btn-dark text-uppercase px-4 py-3 rounded-0">Daftar
                                Gratis</a>
                        @endauth
                    </div>
                </div>
                <!-- Slideshow -->
                <div class="col-lg-4 order-1 order-lg-2 pe-lg-5 d-flex align-items-center bg-white">
                    <div class="swiper hero-swiper w-100 h-100">
                        <div class="swiper-wrapper">
                            @foreach($products->take(8) as $product)
                                <div class="swiper-slide h-100">
                                    <div class="w-100 h-100 position-relative">
                                        <a href="{{ route('products.show', $product) }}" class="d-block w-100 h-100">
                                            <!-- Reduced fixed height to 65vh for better aspect ratio -->
                                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"
                                                class="w-100 h-100 bg-white"
                                                style="object-fit: cover; object-position: center; min-height: 65vh; border-radius: 20px;">
                                        </a>
                                        @if($product->hasDiscount())
                                            <span class="badge bg-danger position-absolute"
                                                style="top: 15px; left: 15px; font-size: 1.25rem; z-index: 2; border-radius: 8px; padding: 8px 16px; box-shadow: 0 2px 8px rgba(220,53,69,0.4); font-weight: 700;">-{{ $product->discount_percent }}%</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="swiper-pagination"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Voucher Section -->
    @if(isset($vouchers) && $vouchers->isNotEmpty())
        <section id="vouchers" class="py-5" style="background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);">
            <div class="container">
                <div class="text-center mb-5">
                    <div class="d-inline-block mb-3"
                        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 12px 24px; border-radius: 50px;">
                        <h2 class="fs-4 text-uppercase fw-bold mb-0 text-white">🎉 Voucher Diskon Spesial</h2>
                    </div>
                    <p class="text-muted">Gunakan kode voucher di bawah untuk mendapatkan diskon spesial!</p>
                </div>

                <div class="row g-4">
                    @foreach($vouchers as $voucher)
                        <div class="col-md-6 col-lg-4">
                            <div class="voucher-card position-relative overflow-hidden h-100"
                                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
                                                                                                                border-radius: 20px; 
                                                                                                                box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
                                                                                                                transition: transform 0.3s ease, box-shadow 0.3s ease;"
                                onmouseover="this.style.transform='translateY(-10px)'; this.style.boxShadow='0 20px 60px rgba(102, 126, 234, 0.4)'"
                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 40px rgba(102, 126, 234, 0.3)'">

                                <!-- Decorative circles -->
                                <div
                                    style="position: absolute; top: -30px; right: -30px; width: 120px; height: 120px; background: rgba(255,255,255,0.1); border-radius: 50%;">
                                </div>
                                <div
                                    style="position: absolute; bottom: -40px; left: -40px; width: 150px; height: 150px; background: rgba(255,255,255,0.1); border-radius: 50%;">
                                </div>

                                <div class="card-body p-4 position-relative" style="z-index: 1;">
                                    <!-- Badge -->
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <span class="badge px-3 py-2"
                                            style="background: rgba(255,255,255,0.25); backdrop-filter: blur(10px); border-radius: 20px; font-size: 11px; font-weight: 600; color: white;">
                                            Tersisa {{ $voucher->usage_limit - $voucher->usage_count }}
                                        </span>
                                        <div class="text-end" style="font-size: 40px; opacity: 0.3;">🎟️</div>
                                    </div>

                                    <!-- Voucher Code -->
                                    <div class="text-center mb-4">
                                        <div class="d-inline-block px-4 py-3"
                                            style="background: rgba(255,255,255,0.95); border-radius: 15px; border: 3px dashed #764ba2;">
                                            <div class="text-uppercase fw-bold mb-1"
                                                style="font-size: 24px; letter-spacing: 2px; color: #764ba2; font-family: 'Courier New', monospace;">
                                                {{ $voucher->code }}
                                            </div>
                                            <small style="color: #667eea; font-size: 10px;">Kode Voucher</small>
                                        </div>
                                    </div>

                                    <!-- Discount Amount -->
                                    <div class="text-center text-white mb-3">
                                        <div class="fw-bold mb-1" style="font-size: 14px; opacity: 0.9;">HEMAT HINGGA</div>
                                        <div class="fw-bold" style="font-size: 32px; text-shadow: 2px 2px 4px rgba(0,0,0,0.2);">
                                            Rp {{ number_format($voucher->discount_amount, 0, ',', '.') }}
                                        </div>
                                    </div>

                                    <!-- Min Purchase -->
                                    @if($voucher->min_purchase > 0)
                                        <div class="text-center text-white" style="opacity: 0.85;">
                                            <small style="font-size: 12px;">
                                                📦 Min. pembelian Rp {{ number_format($voucher->min_purchase, 0, ',', '.') }}
                                            </small>
                                        </div>
                                    @endif

                                    <!-- Decorative bottom wave -->
                                    <div
                                        style="position: absolute; bottom: 0; left: 0; right: 0; height: 3px; background: rgba(255,255,255,0.3);">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Info text -->
                <div class="text-center mt-4">
                    <p class="text-muted small mb-0">
                        💡 <em>Salin kode voucher dan tempelkan saat checkout untuk mendapatkan diskon</em>
                    </p>
                </div>
            </div>
        </section>
    @endif

    <!-- Produk Diskon Section -->
    @php
        $discountedProducts = $products->filter(fn($p) => $p->hasDiscount())->take(8);
    @endphp
    @if($discountedProducts->isNotEmpty())
        <section id="discounted-products" class="py-5" style="background: linear-gradient(135deg, #fff5f5 0%, #ffe0e0 100%);">
            <div class="container">
                <div class="text-center mb-5">
                    <div class="d-inline-block mb-3"
                        style="background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); padding: 12px 24px; border-radius: 50px;">
                        <h2 class="fs-4 text-uppercase fw-bold mb-0 text-white">🔥 Produk Diskon</h2>
                    </div>
                    <p class="text-muted">Hemat lebih banyak dengan produk-produk yang sedang diskon!</p>
                </div>
                <div class="row g-4">
                    @foreach($discountedProducts as $discProduct)
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="product-card-new h-100 d-flex flex-column">
                                <div class="product-img-container">
                                    <a href="{{ route('products.show', $discProduct) }}" class="d-block w-100 h-100 bg-white">
                                        <img src="{{ Storage::url($discProduct->image) }}" alt="{{ $discProduct->name }}"
                                            style="width: 100%; height: 100%; object-fit: contain; object-position: center;">
                                    </a>
                                    <span class="badge bg-danger position-absolute"
                                        style="top: 8px; left: 8px; font-size: 0.85rem; z-index: 2; border-radius: 6px; padding: 5px 10px; font-weight: 700;">-{{ $discProduct->discount_percent }}%</span>
                                </div>
                                <div class="product-info d-flex flex-column">
                                    <span class="product-category">{{ $discProduct->category->name ?? 'Lainnya' }}</span>
                                    <h3 class="product-name flex-grow-1"
                                        style="min-height: 48px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        <a href="{{ route('products.show', $discProduct) }}"
                                            class="text-decoration-none text-dark">{{ $discProduct->name }}</a>
                                    </h3>
                                    <div class="product-footer mt-auto pt-2 border-top">
                                        <div class="d-flex flex-column align-items-start">
                                            <span class="text-muted text-decoration-line-through lh-1"
                                                style="font-size: 0.70rem;">Rp
                                                {{ number_format($discProduct->price, 0, ',', '.') }}</span>
                                            <span class="product-price text-nowrap mt-1 lh-1" style="color: #dc3545;">Rp
                                                {{ number_format($discProduct->effective_price, 0, ',', '.') }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif


    <style>
        .hero-title {
            font-size: 3rem;
            font-weight: 800;
            letter-spacing: -2px;
            line-height: 1.1;
        }

        .swiper-pagination-bullet-active {
            background: var(--primary-color) !important;
        }

        @media (max-width: 991px) {
            .hero-title {
                font-size: 2rem;
            }

            /* Mobile Hero Height */
            .hero-slide-img {
                min-height: 50vh;
            }
        }
    </style>

    <!-- Products Section -->
    <section id="products" class="product-store position-relative py-5">
        <div class="container">
            <div class="row">
                <!-- Main Content (Full Width) -->
                <div class="col-12">
                    <!-- Filter & Sort Header with SEARCH -->
                    <div
                        class="d-flex flex-column flex-lg-row justify-content-between align-items-center mb-4 gap-3 bg-white p-3 shadow-sm rounded-4 border">
                        <h2 class="section-title mb-0 fs-5 text-uppercase fw-bold text-nowrap">Katalog</h2>

                        <!-- Search Input (Moved Outside) -->
                        <div class="flex-grow-1 w-100 px-lg-4">
                            <div class="position-relative">
                                <input type="text"
                                    class="form-control rounded-pill border-0 bg-light py-2 ps-5 search-input"
                                    placeholder="Cari produk apa saja..." id="mainSearchInput">
                                <span class="position-absolute top-50 start-0 translate-middle-y ms-3 text-muted">
                                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-2 text-nowrap">
                            <!-- Mobile Filter Toggle (Manual JS) -->
                            <button class="btn btn-outline-dark d-lg-none rounded-pill" type="button"
                                onclick="toggleFilterMobile()">
                                Filter & Sort
                            </button>

                            <span class="text-muted small ms-2"><span id="productCount">{{ $products->count() }}</span>
                                produk</span>
                        </div>
                    </div>

                    <!-- Mobile Filter (Dropdown/Collapse) -->
                    <!-- Removed 'collapse' class to avoid bootstrap interference, handled by JS style -->
                    <div class="d-lg-none mb-4" id="mobileFilterCollapse" style="display: none;">
                        <div class="card card-body bg-white border shadow-sm rounded-4 text-dark">
                            <!-- Mobile Category -->
                            <div class="filter-group mb-4">
                                <label class="filter-label mb-2 fw-bold text-dark">Kategori</label>
                                <div class="d-flex flex-wrap gap-2">
                                    <button class="btn btn-sm btn-outline-dark category-option active" data-id=""
                                        onclick="setCategory(this, '')">Semua</button>
                                    @foreach($categories as $category)
                                        <button class="btn btn-sm btn-outline-dark category-option"
                                            data-id="{{ $category->id }}"
                                            onclick="setCategory(this, '{{ $category->id }}')">{{ $category->name }}</button>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Mobile Price -->
                            <div class="filter-group mb-4">
                                <label class="filter-label mb-2 fw-bold text-dark">Harga</label>
                                <div class="price-options d-flex flex-column gap-2 text-dark">
                                    <label class="custom-radio"><input type="radio" name="priceRange" value="" checked><span
                                            class="radio-mark"></span><span class="radio-label">Semua Harga</span></label>
                                    <label class="custom-radio"><input type="radio" name="priceRange" value="0-50000"><span
                                            class="radio-mark"></span><span class="radio-label">
                                            < Rp 50rb</span></label>
                                    <label class="custom-radio"><input type="radio" name="priceRange"
                                            value="50000-200000"><span class="radio-mark"></span><span
                                            class="radio-label">Rp 50rb - 200rb</span></label>
                                    <label class="custom-radio"><input type="radio" name="priceRange"
                                            value="200000-500000"><span class="radio-mark"></span><span
                                            class="radio-label">Rp 200rb - 500rb</span></label>
                                    <label class="custom-radio"><input type="radio" name="priceRange" value="500000-"><span
                                            class="radio-mark"></span><span class="radio-label">> Rp 500rb</span></label>
                                </div>
                            </div>
                            <!-- Discount Filter -->
                            <div class="filter-group">
                                <label class="custom-radio">
                                    <input type="checkbox" id="discountFilter" value="1">
                                    <span class="text-dark">🔥 Sedang Diskon</span>
                                </label>
                            </div>
                            <!-- Sort -->
                            <div class="filter-group">
                                <label class="filter-label mb-2 fw-bold text-dark">Urutkan</label>
                                <select class="form-select rounded-3 sort-select">
                                    <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru
                                    </option>
                                    <option value="nearest" {{ request('latitude') ? 'selected' : '' }}>Terdekat (GPS)
                                    </option>
                                    <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Harga
                                        Terendah</option>
                                    <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Harga
                                        Tertinggi</option>
                                    <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nama A-Z
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Shared Hidden Input for Category State -->
                    <input type="hidden" id="categoryFilter" value="">


                    <!-- Product Grid -->
                    <div class="row" id="productGrid">
                        @forelse($products as $product)
                            <div class="col-6 col-md-4 col-lg-3 mb-4 product-item" data-name="{{ strtolower($product->name) }}"
                                data-price="{{ $product->effective_price }}" data-category-id="{{ $product->category_id }}"
                                data-date="{{ $product->created_at->timestamp }}"
                                data-has-discount="{{ $product->hasDiscount() ? '1' : '0' }}">
                                <div class="product-card-new h-100 d-flex flex-column">
                                    <div class="product-img-container">
                                        <a href="{{ route('products.show', $product) }}" class="d-block w-100 h-100 bg-white">
                                            <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"
                                                style="width: 100%; height: 100%; object-fit: contain; object-position: center;">
                                        </a>
                                        @auth
                                            <button type="button"
                                                class="wishlist-floating {{ in_array($product->id, $wishlistIds ?? []) ? 'active' : '' }}"
                                                onclick="toggleWishlist({{ $product->id }}, this)">
                                                <svg width="20" height="20"
                                                    fill="{{ in_array($product->id, $wishlistIds ?? []) ? 'currentColor' : 'none' }}"
                                                    stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                                    </path>
                                                </svg>
                                            </button>
                                        @endauth
                                        @if($product->stock <= 5)
                                            <span class="stock-badge">Sisa {{ $product->stock }}</span>
                                        @endif
                                        @if($product->hasDiscount())
                                            <span class="badge bg-danger position-absolute"
                                                style="top: 8px; left: 8px; font-size: 0.85rem; z-index: 2; border-radius: 6px; padding: 5px 10px; font-weight: 700;">-{{ $product->discount_percent }}%</span>
                                        @endif
                                    </div>
                                    <div class="product-info d-flex flex-column">
                                        <span class="product-category mb-1">{{ $product->category->name ?? 'Lainnya' }}</span>
                                        <h3 class="product-name"
                                            style="font-size: 13px; line-height: 1.3; min-height: 34px; max-height: 34px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                            <a href="{{ route('products.show', $product) }}"
                                                class="text-decoration-none text-dark">{{ $product->name }}</a>
                                        </h3>
                                        <div class="product-meta mt-1 mb-2"><span
                                                class="seller-name text-truncate d-inline-block"
                                                style="max-width: 100%; font-size: 10px;">{{ $product->user->name }}</span>
                                        </div>
                                        <div
                                            class="product-footer mt-auto pt-2 border-top d-flex flex-column align-items-stretch gap-2">
                                            @if($product->hasDiscount())
                                                <div class="d-flex flex-column lh-1">
                                                    <span class="text-muted text-decoration-line-through mb-1"
                                                        style="font-size: 0.70rem;">Rp
                                                        {{ number_format($product->price, 0, ',', '.') }}</span>
                                                    <span class="product-price text-nowrap"
                                                        style="color: #dc3545; font-size: 14px;">Rp
                                                        {{ number_format($product->effective_price, 0, ',', '.') }}</span>
                                                </div>
                                            @else
                                                <div class="d-flex flex-column justify-content-end lh-1" style="min-height: 28px;">
                                                    <span class="product-price text-nowrap" style="font-size: 14px;">Rp
                                                        {{ number_format($product->price, 0, ',', '.') }}</span>
                                                </div>
                                            @endif

                                            @auth
                                                @if($product->user_id !== auth()->id())
                                                    <form action="{{ route('cart.store', $product) }}" method="POST"
                                                        class="add-to-cart-form mt-1">
                                                        @csrf
                                                        <div class="d-flex gap-1">
                                                            <a href="{{ route('checkout', $product) }}"
                                                                class="btn btn-dark flex-grow-1 py-1 px-1 btn-sm d-flex align-items-center justify-content-center text-decoration-none text-white w-100"
                                                                style="font-size: 11px; white-space: nowrap;">Beli</a>
                                                            <button type="submit"
                                                                class="btn-add-cart p-1 px-2 d-flex align-items-center justify-content-center"
                                                                style="min-width: 32px; flex-shrink: 0;"><svg width="14" height="14"
                                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                                        stroke-width="2"
                                                                        d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                                                </svg></button>
                                                        </div>
                                                    </form>
                                                @else
                                                    <button class="btn btn-secondary w-100 py-1 px-1 btn-sm cursor-default mt-1"
                                                        style="font-size: 11px;" disabled>Milik Anda</button>
                                                @endif
                                            @else
                                                <div class="d-flex gap-1 w-100 mt-1">
                                                    <a href="{{ route('checkout', $product) }}"
                                                        class="btn btn-dark flex-grow-1 py-1 px-1 btn-sm d-flex align-items-center justify-content-center text-decoration-none text-white"
                                                        style="font-size: 11px;">Beli</a>
                                                    <a href="{{ route('login') }}"
                                                        class="btn-add-cart p-1 px-2 d-flex align-items-center justify-content-center"
                                                        style="min-width: 32px; flex-shrink: 0;"><svg width="14" height="14"
                                                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                                                        </svg></a>
                                                </div>
                                            @endauth
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5">
                                <h4 class="text-muted">Belum ada produk</h4>
                                <a href="{{ route('products.create') }}" class="btn btn-dark mt-3">Jual Sekarang</a>
                            </div>
                        @endforelse
                    </div>

                    <!-- Pagination -->
                    @if(method_exists($products, 'hasPages') && $products->hasPages())
                        <div class="mt-4 d-flex justify-content-center">{{ $products->links() }}</div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Desktop Filter Modal (Transparent) -->
        <div class="modal fade" id="desktopFilterModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content glass-modal rounded-4 border-0">
                    <div class="modal-header border-bottom-0 pb-0">
                        <h5 class="modal-title fw-bold">Filter Produk</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <!-- Desktop Search Removed -->

                        <!-- Desktop Category -->
                        <div class="mb-4">
                            <label class="fw-bold mb-2 small text-uppercase">Kategori</label>
                            <div class="d-flex flex-wrap gap-2">
                                <button class="btn btn-sm btn-outline-dark category-option active" data-id=""
                                    onclick="setCategory(this, '')">Semua</button>
                                @foreach($categories as $category)
                                    <button class="btn btn-sm btn-outline-dark category-option" data-id="{{ $category->id }}"
                                        onclick="setCategory(this, '{{ $category->id }}')">{{ $category->name }}</button>
                                @endforeach
                            </div>
                        </div>

                        <!-- Desktop Price -->
                        <div class="mb-4">
                            <label class="fw-bold mb-2 small text-uppercase">Rentang Harga</label>
                            <div class="d-flex flex-column gap-2">
                                <label class="custom-radio"><input type="radio" name="priceRange" value="" checked><span
                                        class="radio-mark"></span><span class="radio-label">Semua Harga</span></label>
                                <label class="custom-radio"><input type="radio" name="priceRange" value="0-50000"><span
                                        class="radio-mark"></span><span class="radio-label">
                                        < Rp 50rb</span></label>
                                <label class="custom-radio"><input type="radio" name="priceRange" value="50000-200000"><span
                                        class="radio-mark"></span><span class="radio-label">Rp 50rb - 200rb</span></label>
                                <label class="custom-radio"><input type="radio" name="priceRange"
                                        value="200000-500000"><span class="radio-mark"></span><span class="radio-label">Rp
                                        200rb - 500rb</span></label>
                                <label class="custom-radio"><input type="radio" name="priceRange" value="500000-"><span
                                        class="radio-mark"></span><span class="radio-label">> Rp 500rb</span></label>
                            </div>
                        </div>

                        <!-- Desktop Sort -->
                        <div class="mb-0">
                            <label class="fw-bold mb-2 small text-uppercase">Urutkan</label>
                            <select class="form-select rounded-3 sort-select">
                                <option value="newest" {{ request('sort') == 'newest' ? 'selected' : '' }}>Terbaru</option>
                                <option value="nearest" {{ request('latitude') ? 'selected' : '' }}>Terdekat (GPS)</option>
                                <option value="price_low" {{ request('sort') == 'price_low' ? 'selected' : '' }}>Harga
                                    Terendah</option>
                                <option value="price_high" {{ request('sort') == 'price_high' ? 'selected' : '' }}>Harga
                                    Tertinggi</option>
                                <option value="name_asc" {{ request('sort') == 'name_asc' ? 'selected' : '' }}>Nama A-Z
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-dark w-100 rounded-3 text-uppercase fw-bold"
                            data-bs-dismiss="modal">Terapkan Filter</button>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            // DOM References
            const categoryInput = document.getElementById('categoryFilter');
            const productCount = document.getElementById('productCount');
            const productGrid = document.getElementById('productGrid');

            // Initialize Everything
            document.addEventListener('DOMContentLoaded', () => {
                // Bind Search Inputs (Mobile & Desktop)
                document.querySelectorAll('.search-input').forEach(input => {
                    input.addEventListener('input', () => {
                        filterProducts();
                    });
                });

                // Bind Sort Selects
                document.querySelectorAll('.sort-select').forEach(select => {
                    select.addEventListener('change', (e) => {
                        const val = e.target.value;
                        if (val === 'nearest') {
                            if (navigator.geolocation) {
                                Swal.fire({ title: 'Mencari Lokasi...', allowOutsideClick: false, didOpen: () => { Swal.showLoading() } });
                                navigator.geolocation.getCurrentPosition(pos => {
                                    window.location.href = `?latitude=${pos.coords.latitude}&longitude=${pos.coords.longitude}&sort=nearest`;
                                }, err => {
                                    Swal.fire('Gagal', 'Tidak dapat mengakses lokasi', 'error');
                                });
                            } else {
                                Swal.fire('Error', 'Browser tidak mendukung Geolocation', 'error');
                            }
                            return;
                        }

                        // Sync other sort selects
                        document.querySelectorAll('.sort-select').forEach(s => s.value = e.target.value);

                        if (window.location.search.includes('latitude')) {
                            window.location.href = `?sort=${val}`;
                        } else {
                            sortProducts();
                        }
                    });
                });

                // Bind Price Radios
                document.querySelectorAll('input[name="priceRange"]').forEach(radio => {
                    radio.addEventListener('change', filterProducts);
                });

                // Bind Discount Filter
                const discountFilter = document.getElementById('discountFilter');
                if (discountFilter) {
                    discountFilter.addEventListener('change', filterProducts);
                }

                // Add to Cart AJAX
                document.querySelectorAll('.add-to-cart-form').forEach(form => {
                    form.addEventListener('submit', function (e) {
                        e.preventDefault();
                        const btn = this.querySelector('button');
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

                        fetch(this.action, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json'
                            }
                        })
                            .then(res => {
                                if (!res.ok) {
                                    return res.json().then(data => { throw new Error(data.message); });
                                }
                                return res.json();
                            })
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
                                        }
                                    });
                                }

                                Swal.fire({
                                    icon: 'success',
                                    title: 'Ditambahkan!',
                                    text: data.message || 'Produk ditambahkan ke keranjang',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                            })
                            .catch(error => {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Gagal',
                                    text: error.message || 'Tidak bisa menambahkan ke keranjang'
                                });
                            })
                            .finally(() => {
                                btn.disabled = false;
                                btn.innerHTML = '<svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg> Tambah';
                            });
                    });
                });

                // Hero Swiper - Product Slider
                new Swiper('.hero-swiper', {
                    slidesPerView: 1,
                    spaceBetween: 20,
                    loop: true,
                    autoplay: {
                        delay: 3000,
                        disableOnInteraction: false,
                    },
                    pagination: {
                        el: '.swiper-pagination',
                        clickable: true,
                    },
                    breakpoints: {
                        768: { slidesPerView: 1 },
                        1024: { slidesPerView: 1 }
                    }
                });
            });

            // === Global Functions (accessible from onclick handlers) ===

            window.setCategory = function (element, categoryId) {
                categoryInput.value = categoryId;

                document.querySelectorAll('.category-option').forEach(el => {
                    if (el.dataset.id === categoryId) {
                        el.classList.add('active', 'btn-dark');
                        el.classList.remove('btn-outline-dark');
                    } else {
                        el.classList.remove('active', 'btn-dark');
                        el.classList.add('btn-outline-dark');
                    }
                });

                filterProducts();
            };

            function filterProducts() {
                let searchTerm = '';
                document.querySelectorAll('.search-input').forEach(input => {
                    if (input.value) searchTerm = input.value.toLowerCase();
                });

                const categoryId = categoryInput.value;
                const priceRadio = document.querySelector('input[name="priceRange"]:checked');
                const priceRange = priceRadio ? priceRadio.value : '';
                const discountOnly = document.getElementById('discountFilter')?.checked || false;

                const items = document.querySelectorAll('.product-item');
                let visibleCount = 0;

                items.forEach(item => {
                    const name = item.dataset.name;
                    const price = parseInt(item.dataset.price);
                    const itemCatId = item.dataset.categoryId;
                    const hasDiscount = item.dataset.hasDiscount === '1';

                    let show = true;

                    if (searchTerm && !name.includes(searchTerm)) show = false;
                    if (categoryId && itemCatId !== categoryId) show = false;
                    if (discountOnly && !hasDiscount) show = false;

                    if (priceRange) {
                        const parts = priceRange.split('-');
                        const min = parts[0] ? parseInt(parts[0]) : 0;
                        const max = parts[1] ? parseInt(parts[1]) : null;

                        if (price < min) show = false;
                        if (max !== null && price > max) show = false;
                    }

                    item.style.display = show ? '' : 'none';
                    if (show) visibleCount++;
                });

                if (productCount) productCount.textContent = visibleCount;
            }

            function sortProducts() {
                const sortSelect = document.querySelector('.sort-select');
                if (!sortSelect) return;

                const sortBy = sortSelect.value;
                if (sortBy === 'nearest') return; // Server side handled

                const items = Array.from(document.querySelectorAll('.product-item'));

                items.sort((a, b) => {
                    if (sortBy === 'price_low') return parseInt(a.dataset.price) - parseInt(b.dataset.price);
                    if (sortBy === 'price_high') return parseInt(b.dataset.price) - parseInt(a.dataset.price);
                    if (sortBy === 'name_asc') return a.dataset.name.localeCompare(b.dataset.name);
                    return parseInt(b.dataset.date) - parseInt(a.dataset.date);
                });

                items.forEach(item => productGrid.appendChild(item));
            }

            // Wishlist Toggle
            window.toggleWishlist = function (productId, btn) {
                fetch(`/wishlist/${productId}/toggle`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        btn.classList.toggle('active', data.added);
                        const svg = btn.querySelector('svg');
                        svg.setAttribute('fill', data.added ? 'currentColor' : 'none');

                        if (data.wishlist_count !== undefined) {
                            document.querySelectorAll('a[href*="wishlist"]').forEach(link => {
                                let badge = link.querySelector('.badge');
                                if (!badge && data.wishlist_count > 0) {
                                    badge = document.createElement('span');
                                    badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger';
                                    badge.style.fontSize = '10px';
                                    link.appendChild(badge);
                                }
                                if (badge) {
                                    badge.innerText = data.wishlist_count;
                                    if (data.wishlist_count === 0) badge.remove();
                                }
                            });
                        }
                    });
            };

            // Manual Mobile Filter Toggle
            window.toggleFilterMobile = function () {
                const collapse = document.getElementById('mobileFilterCollapse');
                if (collapse.style.display === 'none' || collapse.style.display === '') {
                    collapse.style.display = 'block';
                } else {
                    collapse.style.display = 'none';
                }
            };

            // Copy Voucher Code
            window.copyVoucherCode = function (code) {
                const textArea = document.createElement('textarea');
                textArea.value = code;
                textArea.style.position = 'fixed';
                textArea.style.top = '0';
                textArea.style.left = '0';
                textArea.style.opacity = '0';
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();

                try {
                    const successful = document.execCommand('copy');
                    if (successful) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                text: `Kode "${code}" telah disalin`,
                                timer: 1500,
                                showConfirmButton: false
                            });
                        } else {
                            alert(`Kode "${code}" telah disalin!`);
                        }
                    } else {
                        alert('Gagal menyalin. Kode: ' + code);
                    }
                } catch (err) {
                    alert('Error menyalin kode: ' + code);
                }

                document.body.removeChild(textArea);
            };
        </script>
    @endpush
@endsection