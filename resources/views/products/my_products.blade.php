@extends('layouts.app')

@section('content')
    <section class="padding-large">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="display-7 text-uppercase mb-1">Barang Saya</h2>
                    <p class="text-muted mb-0">{{ $products->count() }} produk terdaftar</p>
                </div>
                <a href="{{ route('products.create') }}" class="btn btn-dark text-uppercase">
                    + Tambah Barang
                </a>
            </div>

            @if($products->count() > 0)
                <!-- Desktop Table -->
                <div class="card d-none d-md-block">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Produk</th>
                                    <th>Harga</th>
                                    <th>Stok</th>
                                    <th>Kategori</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($products as $product)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"
                                                    style="width: 60px; height: 60px; object-fit: cover;">
                                                <div>
                                                    <a href="{{ route('products.show', $product) }}"
                                                        class="text-dark text-decoration-none fw-bold">{{ $product->name }}</a>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="align-middle">
                                            <span style="color: var(--primary-color)">Rp
                                                {{ number_format($product->price, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="align-middle">
                                            @if($product->stock > 0)
                                                <span class="badge bg-success">{{ $product->stock }}</span>
                                            @else
                                                <span class="badge bg-danger">Habis</span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-muted">{{ $product->category->name ?? '-' }}</td>
                                        <td class="align-middle">
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('products.show', $product) }}"
                                                    class="btn btn-sm btn-outline-secondary">Lihat</a>
                                                <a href="{{ route('products.edit', $product) }}"
                                                    class="btn btn-sm btn-outline-dark">Edit</a>
                                                <form action="{{ route('products.destroy', $product) }}" method="POST"
                                                    onsubmit="return confirm('Yakin ingin menghapus?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Mobile Cards -->
                <div class="d-md-none">
                    @foreach($products as $product)
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex gap-3">
                                    <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"
                                        style="width: 80px; height: 80px; object-fit: cover;">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1">{{ $product->name }}</h6>
                                        <p class="mb-1" style="color: var(--primary-color)">Rp
                                            {{ number_format($product->price, 0, ',', '.') }}</p>
                                        <small class="text-muted">Stok: {{ $product->stock }}</small>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 mt-3">
                                    <a href="{{ route('products.edit', $product) }}"
                                        class="btn btn-sm btn-outline-dark flex-grow-1">Edit</a>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="flex-grow-1"
                                        onsubmit="return confirm('Hapus?');">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">Hapus</button>
                                    </form>
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
                            d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                    </svg>
                    <h4 class="text-muted">Belum Ada Produk</h4>
                    <p class="text-muted">Mulai jual barang pertamamu!</p>
                    <a href="{{ route('products.create') }}" class="btn btn-dark text-uppercase">Jual Sekarang</a>
                </div>
            @endif
        </div>
    </section>
@endsection