<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Market Mahasiswa') }}</title>

    <!-- Bootstrap & MiniStore CSS -->
    <link rel="stylesheet" type="text/css" href="{{ asset('MiniStore-1.0.0/css/bootstrap.min.css') }}">
    <link rel="stylesheet" type="text/css" href="{{ asset('MiniStore-1.0.0/style.css') }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" />

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Jost:wght@300;400;500;600;700&family=Lato:wght@300;400;700&display=swap"
        rel="stylesheet">

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --primary-color: #72AEC8;
            --bs-primary-rgb: 114, 174, 200;
            --accent-color: #717171;
        }
    </style>
</head>

<body data-bs-spy="scroll" data-bs-target="#navbar" data-bs-smooth-scroll="true">
    <!-- SVG Icons -->
    <svg xmlns="http://www.w3.org/2000/svg" style="display: none;">
        <symbol id="search" viewBox="0 0 32 32">
            <path fill="currentColor"
                d="M19 3C13.488 3 9 7.488 9 13c0 2.395.84 4.59 2.25 6.313L3.281 27.28l1.439 1.44l7.968-7.969A9.922 9.922 0 0 0 19 23c5.512 0 10-4.488 10-10S24.512 3 19 3zm0 2c4.43 0 8 3.57 8 8s-3.57 8-8 8s-8-3.57-8-8s3.57-8 8-8z" />
        </symbol>
        <symbol id="user" viewBox="0 0 16 16">
            <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H3Zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6Z" />
        </symbol>
        <symbol id="cart" viewBox="0 0 16 16">
            <path
                d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .491.592l-1.5 8A.5.5 0 0 1 13 12H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
        </symbol>
        <symbol id="heart" viewBox="0 0 16 16">
            <path
                d="m8 2.748-.717-.737C5.6.281 2.514.878 1.4 3.053c-.523 1.023-.641 2.5.314 4.385.92 1.815 2.834 3.989 6.286 6.357 3.452-2.368 5.365-4.542 6.286-6.357.955-1.886.838-3.362.314-4.385C13.486.878 10.4.28 8.717 2.01L8 2.748zM8 15C-7.333 4.868 3.279-3.04 7.824 1.143c.06.055.119.112.176.171a3.12 3.12 0 0 1 .176-.17C12.72-3.042 23.333 4.867 8 15z" />
        </symbol>
        <symbol id="cart-outline" viewBox="0 0 16 16">
            <path
                d="M0 1.5A.5.5 0 0 1 .5 1H2a.5.5 0 0 1 .485.379L2.89 3H14.5a.5.5 0 0 1 .49.598l-1 5a.5.5 0 0 1-.465.401l-9.397.472L4.415 11H13a.5.5 0 0 1 0 1H4a.5.5 0 0 1-.491-.408L2.01 3.607 1.61 2H.5a.5.5 0 0 1-.5-.5zM3.102 4l.84 4.479 9.144-.459L13.89 4H3.102zM5 12a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm7 0a2 2 0 1 0 0 4 2 2 0 0 0 0-4zm-7 1a1 1 0 1 1 0 2 1 1 0 0 1 0-2zm7 0a1 1 0 1 1 0 2 1 1 0 0 1 0-2z" />
        </symbol>
        <symbol id="star-fill" viewBox="0 0 16 16">
            <path
                d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z" />
        </symbol>
        <symbol id="chevron-left" viewBox="0 0 16 16">
            <path fill-rule="evenodd"
                d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z" />
        </symbol>
        <symbol id="chevron-right" viewBox="0 0 16 16">
            <path fill-rule="evenodd"
                d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z" />
        </symbol>
        <symbol id="navbar-icon" viewBox="0 0 16 16">
            <path
                d="M14 10.5a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0 0 1h3a.5.5 0 0 0 .5-.5zm0-3a.5.5 0 0 0-.5-.5h-7a.5.5 0 0 0 0 1h7a.5.5 0 0 0 .5-.5zm0-3a.5.5 0 0 0-.5-.5h-11a.5.5 0 0 0 0 1h11a.5.5 0 0 0 .5-.5z" />
        </symbol>
        <symbol id="eye" viewBox="0 0 16 16">
            <path
                d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z" />
            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z" />
        </symbol>
        <symbol id="eye-slash" viewBox="0 0 16 16">
            <path
                d="M13.359 11.238C15.06 9.72 16 8 16 8s-3-5.5-8-5.5a7.028 7.028 0 0 0-2.79.588l.77.771A5.944 5.944 0 0 1 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.134 13.134 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755-.165.165-.337.328-.517.486l.708.709z" />
            <path
                d="M11.297 9.176a3.5 3.5 0 0 0-4.474-4.474l.823.823a2.5 2.5 0 0 1 2.829 2.829l.822.822zm-2.943 1.299.822.822a3.5 3.5 0 0 1-4.474-4.474l.823.823a2.5 2.5 0 0 0 2.829 2.829z" />
            <path
                d="M3.35 5.47c-.18.16-.353.322-.518.487A13.134 13.134 0 0 0 1.172 8l.195.288c.335.48.83 1.12 1.465 1.755C4.121 11.332 5.881 12.5 8 12.5c.716 0 1.39-.133 2.02-.36l.77.772A7.029 7.029 0 0 1 8 13.5C3 13.5 0 8 0 8s.939-1.721 2.641-3.238l.708.709zm10.296 8.884-12-12 .708-.708 12 12-.708.708z" />
        </symbol>
    </svg>

    <!-- Header -->
    <!-- Header -->
    <header id="header" class="site-header position-fixed w-100 text-black bg-white shadow-sm" style="z-index: 1030;">
        <nav id="header-nav" class="navbar navbar-expand-lg px-3">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ route('home') }}">
                    <span class="fw-bold fs-4">Market<span style="color: var(--primary-color)">Mahasiswa</span></span>
                </a>

                <button id="customNavbarToggler" class="navbar-toggler d-flex d-lg-none order-3 p-2 border-0"
                    type="button" aria-expanded="false" aria-label="Toggle navigation" onclick="toggleMobileMenu()">
                    <svg class="navbar-icon" width="30" height="30">
                        <use xlink:href="#navbar-icon"></use>
                    </svg>
                </button>

                <!-- Mobile Cart Icon (Visible on Mobile) -->
                @auth
                    <div class="d-flex d-lg-none order-2 ms-auto me-2">
                        <a href="{{ route('cart.index') }}" class="position-relative text-dark">
                            <svg class="cart" width="24" height="24" fill="currentColor">
                                <use xlink:href="#cart"></use>
                            </svg>
                            @if(auth()->user()->cart()->sum('quantity') > 0)
                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                    style="font-size: 8px;">
                                    {{ auth()->user()->cart()->sum('quantity') }}
                                </span>
                            @endif
                        </a>
                    </div>
                @endauth

                <div class="offcanvas offcanvas-end bg-white" id="bdNavbar" tabindex="-1"
                    aria-labelledby="bdNavbarOffcanvasLabel" style="z-index: 1050;">
                    <div class="offcanvas-header px-4 border-bottom">
                        <h5 class="offcanvas-title fw-bold" id="bdNavbarOffcanvasLabel">Menu</h5>
                        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"
                            aria-label="Close"></button>
                    </div>
                    <div class="offcanvas-body">
                        <!-- Navbar items here -->
                        <ul id="navbar"
                            class="navbar-nav text-uppercase justify-content-end align-items-center flex-grow-1 pe-3">
                            <li class="nav-item">
                                <a class="nav-link me-4 text-dark {{ request()->routeIs('home') || request()->routeIs('products.index') ? 'active' : '' }}"
                                    href="{{ route('home') }}">Beranda</a>
                            </li>

                            @auth
                            <li class="nav-item">
                                <a class="nav-link me-4 text-dark {{ request()->routeIs('products.create') ? 'active' : '' }}"
                                    href="{{ route('products.create') }}">Jual Barang</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link me-4 text-dark {{ request()->routeIs('products.my') ? 'active' : '' }}"
                                    href="{{ route('products.my') }}">Barang Saya</a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link me-4 text-dark {{ request()->routeIs('transactions.history') ? 'active' : '' }}"
                                    href="{{ route('transactions.history') }}">Riwayat</a>
                            </li>
                            <li class="nav-item dropdown">
                                <a class="nav-link me-4 text-dark dropdown-toggle link-dark" data-bs-toggle="dropdown"
                                    href="#" role="button" aria-expanded="false">Akun</a>
                                <ul class="dropdown-menu">
                                    <li><a href="{{ route('profile.edit') }}" class="dropdown-item">Profil</a></li>
                                    <li><a href="{{ route('addresses.index') }}" class="dropdown-item">Alamat</a>
                                    </li>
                                    <li><a href="{{ route('wishlist.index') }}" class="dropdown-item">Wishlist</a>
                                    </li>
                                    <li><a href="{{ route('seller.balance') }}" class="dropdown-item">Saldo Saya</a>
                                    </li>
                                    @if(auth()->user()->role === 'admin')
                                        <li>
                                            <hr class="dropdown-divider">
                                        </li>
                                        <li><a href="{{ route('admin.dashboard') }}" class="dropdown-item text-danger">Admin
                                                Panel</a></li>
                                    @endif
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <form method="POST" action="{{ route('logout') }}">
                                            @csrf
                                            <button type="submit" class="dropdown-item text-danger">Keluar</button>
                                        </form>
                                    </li>
                                </ul>
                            </li>
                            @endif

                            <!-- Filter Button (Desktop Only) -->
                            @if(request()->routeIs('home') || request()->routeIs('products.index'))
                                <li class="nav-item d-none d-lg-block me-2">
                                    <button class="btn btn-outline-dark btn-sm text-uppercase px-3" data-bs-toggle="modal"
                                        data-bs-target="#desktopFilterModal">
                                        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                            class="me-1" style="position: relative; top: -1px;">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4">
                                            </path>
                                        </svg>
                                        Filter
                                    </button>
                                </li>
                            @endif

                            <li class="nav-item">
                                <div class="user-items ps-5">
                                    <ul class="d-flex justify-content-end list-unstyled mb-0">
                                        @auth
                                            <li class="pe-3">
                                                <a href="{{ route('wishlist.index') }}" title="Wishlist"
                                                    class="position-relative">
                                                    <svg class="heart" width="18" height="18" fill="currentColor">
                                                        <use xlink:href="#heart"></use>
                                                    </svg>
                                                    @if(auth()->user()->wishlist()->count() > 0)
                                                        <span
                                                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                                            style="font-size: 10px;">
                                                            {{ auth()->user()->wishlist()->count() }}
                                                        </span>
                                                    @endif
                                                </a>
                                            </li>
                                            <li class="pe-3">
                                                <a href="{{ route('profile.edit') }}" title="Profil">
                                                    <svg class="user" width="18" height="18" fill="currentColor">
                                                        <use xlink:href="#user"></use>
                                                    </svg>
                                                </a>
                                            </li>
                                            <li>
                                                <a href="{{ route('cart.index') }}" class="position-relative"
                                                    title="Keranjang">
                                                    <svg class="cart" width="18" height="18" fill="currentColor">
                                                        <use xlink:href="#cart"></use>
                                                    </svg>
                                                    @if(auth()->user()->cart()->sum('quantity') > 0)
                                                        <span
                                                            class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                                            style="font-size: 10px;">
                                                            {{ auth()->user()->cart()->sum('quantity') }}
                                                        </span>
                                                    @endif
                                                </a>
                                            </li>
                                        @else
                                            <li class="pe-3">
                                                <a href="{{ route('login') }}"
                                                    class="btn btn-outline-dark btn-sm text-uppercase">Masuk</a>
                                            </li>
                                            <li>
                                                <a href="{{ route('register') }}"
                                                    class="btn btn-dark btn-sm text-uppercase">Daftar</a>
                                            </li>
                                        @endauth
                                    </ul>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </header>

    <!-- Main Content -->
    <main style="padding-top: 100px;">
        <!-- Flash Messages -->
        @if(session('success'))
            <div class="container">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="container">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        @endif

        @yield('content')
    </main>

    <!-- Footer -->
    <footer id="footer" class="overflow-hidden bg-light mt-5">
        <div class="container">
            <div class="row py-5">
                <div class="col-lg-4 col-md-6 mb-4">
                    <h5 class="fw-bold mb-3">Market<span style="color: var(--primary-color)">Mahasiswa</span></h5>
                    <p class="text-muted">Platform jual beli khusus mahasiswa. Aman, mudah, dan terpercaya.</p>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="text-uppercase fw-bold mb-3">Menu</h6>
                    <ul class="list-unstyled">
                        <li class="mb-2"><a href="{{ route('home') }}"
                                class="text-muted text-decoration-none">Beranda</a></li>
                        @auth
                            <li class="mb-2"><a href="{{ route('products.create') }}"
                                    class="text-muted text-decoration-none">Jual Barang</a></li>
                            <li class="mb-2"><a href="{{ route('cart.index') }}"
                                    class="text-muted text-decoration-none">Keranjang</a></li>
                        @endauth
                    </ul>
                </div>
                <div class="col-lg-2 col-md-6 mb-4">
                    <h6 class="text-uppercase fw-bold mb-3">Kategori</h6>
                    <ul class="list-unstyled">
                        @php $categories = \App\Models\Category::take(4)->get(); @endphp
                        @foreach($categories as $cat)
                            <li class="mb-2"><a href="{{ route('home', ['category' => $cat->id]) }}"
                                    class="text-muted text-decoration-none">{{ $cat->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div class="col-lg-4 col-md-6 mb-4">
                    <h6 class="text-uppercase fw-bold mb-3">Info</h6>
                    <p class="text-muted mb-1">Escrow Payment System</p>
                    <p class="text-muted small">Pembayaran aman - dana ditahan sampai barang diterima.</p>
                </div>
            </div>
            <div class="row border-top py-4">
                <div class="col-12 text-center">
                    <p class="text-muted mb-0">&copy; {{ date('Y') }} Market Mahasiswa. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Mobile Menu Overlay -->
    <div id="mobileMenuOverlay" class="d-lg-none">
        <div class="mobile-menu-backdrop" onclick="toggleMobileMenu()"></div>
        <div class="mobile-menu-content">
            <div class="mobile-menu-header">
                <h5 class="fw-bold m-0"><span style="color: var(--primary-color)">Menu</span></h5>
                <button type="button" class="btn-close" onclick="toggleMobileMenu()"></button>
            </div>
            <div class="mobile-menu-body">
                <div class="mobile-nav-item">
                    <a href="{{ route('home') }}"
                        class="mobile-nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Beranda</a>
                </div>
                @auth
                    <div class="mobile-nav-item">
                        <a href="{{ route('products.create') }}"
                            class="mobile-nav-link {{ request()->routeIs('products.create') ? 'active' : '' }}">Jual
                            Barang</a>
                    </div>
                    <div class="mobile-nav-item">
                        <a href="{{ route('products.my') }}"
                            class="mobile-nav-link {{ request()->routeIs('products.my') ? 'active' : '' }}">Barang Saya</a>
                    </div>
                    <div class="mobile-nav-item">
                        <a href="{{ route('transactions.history') }}"
                            class="mobile-nav-link {{ request()->routeIs('transactions.history') ? 'active' : '' }}">Riwayat</a>
                    </div>
                    <div class="mobile-nav-item">
                        <a href="{{ route('cart.index') }}"
                            class="mobile-nav-link {{ request()->routeIs('cart.index') ? 'active' : '' }}">
                            Keranjang
                            @if(auth()->user()->cart()->sum('quantity') > 0)
                                <span
                                    class="badge bg-danger rounded-pill ms-2">{{ auth()->user()->cart()->sum('quantity') }}</span>
                            @endif
                        </a>
                    </div>

                    <div class="mobile-nav-item mt-4 mb-2">
                        <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 1px;">Akun Saya</span>
                    </div>

                    <div class="mobile-nav-item">
                        <a href="{{ route('profile.edit') }}" class="mobile-nav-link">Profil</a>
                    </div>
                    <div class="mobile-nav-item">
                        <a href="{{ route('addresses.index') }}" class="mobile-nav-link">Alamat</a>
                    </div>
                    <div class="mobile-nav-item">
                        <a href="{{ route('wishlist.index') }}" class="mobile-nav-link">
                            Wishlist
                            @if(auth()->user()->wishlist()->count() > 0)
                                <span class="badge bg-danger rounded-pill ms-2">{{ auth()->user()->wishlist()->count() }}</span>
                            @endif
                        </a>
                    </div>
                    <div class="mobile-nav-item">
                        <a href="{{ route('seller.balance') }}" class="mobile-nav-link">Saldo Saya</a>
                    </div>

                    @if(auth()->user()->role === 'admin')
                        <div class="mobile-nav-item">
                            <a href="{{ route('admin.dashboard') }}" class="mobile-nav-link text-danger">Admin Panel</a>
                        </div>
                    @endif

                    <div class="mobile-nav-item border-0">
                        <form method="POST" action="{{ route('logout') }}" class="w-100">
                            @csrf
                            <button type="submit"
                                class="mobile-nav-link text-danger bg-transparent border-0 p-0 w-100 text-start"
                                style="outline: none;">Keluar</button>
                        </form>
                    </div>
                @endauth

                @guest
                    <div class="mobile-nav-item mt-3">
                        <a href="{{ route('login') }}" class="btn btn-outline-dark w-100 mb-2">Masuk</a>
                        <a href="{{ route('register') }}" class="btn btn-dark w-100">Daftar</a>
                    </div>
                @endguest
            </div>
        </div>
    </div>

    <script>
        function toggleMobileMenu() {
            const overlay = document.getElementById('mobileMenuOverlay');
            overlay.classList.toggle('active');
            if (overlay.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }

        function togglePassword(btn) {
            const input = btn.parentElement.querySelector('input');
            const icon = btn.querySelector('use');
            if (input.type === 'password') {
                input.type = 'text';
                icon.setAttribute('xlink:href', '#eye');
            } else {
                input.type = 'password';
                icon.setAttribute('xlink:href', '#eye-slash');
            }
        }
    </script>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>

    @stack('scripts')
</body>

</html>