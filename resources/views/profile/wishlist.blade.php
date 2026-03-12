@extends('layouts.app')

@section('content')
    <div class="py-5 bg-light-blue" style="min-height: 80vh;">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                    <li class="breadcrumb-item active">Wishlist</li>
                </ol>
            </nav>

            <h2 class="display-7 text-uppercase mb-4">Wishlist Saya</h2>

            @if($wishlists->count() > 0)
                <div class="row">
                    @foreach($wishlists as $item)
                        <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                            <div class="product-card position-relative">
                                <!-- Remove from Wishlist -->
                                <button type="button" class="wishlist-btn active"
                                    onclick="removeFromWishlist({{ $item->product->id }}, this)">
                                    <svg width="18" height="18" fill="currentColor" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                                        </path>
                                    </svg>
                                </button>

                                <!-- Image -->
                                <a href="{{ route('products.show', $item->product) }}">
                                    <div class="image-holder">
                                        <img src="{{ Storage::url($item->product->image) }}" alt="{{ $item->product->name }}"
                                            class="img-fluid" style="height: 250px; width: 100%; object-fit: cover;">
                                    </div>
                                </a>

                                <!-- Add to Cart (Hover) -->
                                @if($item->product->user_id !== auth()->id() && $item->product->stock > 0)
                                    <div class="cart-concern position-absolute">
                                        <div class="cart-button d-flex">
                                            <form action="{{ route('cart.store', $item->product) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-medium btn-black">
                                                    Tambah
                                                    <svg class="cart-outline" width="16" height="16" fill="white">
                                                        <use xlink:href="#cart-outline"></use>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endif

                                <!-- Details -->
                                <div class="card-detail d-flex justify-content-between align-items-baseline pt-3">
                                    <h3 class="card-title text-uppercase mb-0" style="font-size: 14px;">
                                        <a href="{{ route('products.show', $item->product) }}"
                                            class="text-decoration-none text-dark">
                                            {{ Str::limit($item->product->name, 25) }}
                                        </a>
                                    </h3>
                                    <span class="item-price">Rp {{ number_format($item->product->price, 0, ',', '.') }}</span>
                                </div>
                                <div class="card-seller text-muted" style="font-size: 12px;">
                                    {{ $item->product->user->name }}
                                    @if($item->product->stock == 0)
                                        <span class="badge bg-danger ms-2">Habis</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5">
                    <svg class="mb-4" width="80" height="80" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        style="color: #ddd;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                            d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                        </path>
                    </svg>
                    <h4 class="text-muted">Wishlist Kosong</h4>
                    <p class="text-muted">Simpan barang favorit kamu!</p>
                    <a href="{{ route('home') }}" class="btn btn-dark text-uppercase">Jelajahi Produk</a>
                </div>
            @endif
        </div>
        </section>

        @push('scripts')
            <script>
                function removeFromWishlist(productId, btn) {
                    fetch(`/wishlist/${productId}/toggle`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    }).then(() => {
                        btn.closest('.col-lg-3').remove();
                        Swal.fire({
                            icon: 'success',
                            title: 'Dihapus dari wishlist',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    });
                }
            </script>
        @endpush
@endsection