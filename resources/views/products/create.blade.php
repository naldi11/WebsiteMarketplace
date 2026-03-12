@extends('layouts.app')

@section('content')
    <section class="padding-large">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <nav aria-label="breadcrumb" class="mb-4">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                            <li class="breadcrumb-item active">Jual Barang</li>
                        </ol>
                    </nav>

                    <h2 class="display-7 text-uppercase mb-4">Jual Barang Baru</h2>

                    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Basic Info -->
                        <div class="card mb-4">
                            <div class="card-header bg-dark text-white text-uppercase">
                                Informasi Produk
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama Produk *</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                        name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="description" class="form-label">Deskripsi *</label>
                                    <textarea class="form-control @error('description') is-invalid @enderror"
                                        id="description" name="description" rows="4"
                                        required>{{ old('description') }}</textarea>
                                    @error('description')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="category_id" class="form-label">Kategori *</label>
                                        <select class="form-select @error('category_id') is-invalid @enderror"
                                            id="category_id" name="category_id" required>
                                            <option value="">Pilih Kategori</option>
                                            @foreach($categories as $category)
                                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('category_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="condition" class="form-label">Kondisi *</label>
                                        <select class="form-select @error('condition') is-invalid @enderror" id="condition"
                                            name="condition" required>
                                            <option value="new" {{ old('condition') == 'new' ? 'selected' : '' }}>Baru
                                            </option>
                                            <option value="like_new" {{ old('condition') == 'like_new' ? 'selected' : '' }}>
                                                Seperti Baru</option>
                                            <option value="good" {{ old('condition') == 'good' ? 'selected' : '' }}>Baik
                                            </option>
                                            <option value="fair" {{ old('condition') == 'fair' ? 'selected' : '' }}>Cukup
                                            </option>
                                        </select>
                                        @error('condition')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="location" class="form-label">Lokasi Barang *</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control @error('location') is-invalid @enderror"
                                            id="location" name="location" value="{{ old('location') }}"
                                            placeholder="Kota/Kabupaten" required>
                                        <button class="btn btn-outline-secondary fw-bold" type="button"
                                            id="btnGetLocation">📍 Ambil Lokasi GPS</button>
                                    </div>
                                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude') }}">
                                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude') }}">
                                    @error('location')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Price & Stock -->
                        <div class="card mb-4">
                            <div class="card-header bg-dark text-white text-uppercase">
                                Harga & Stok
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="price" class="form-label">Harga (Rp) *</label>
                                        <input type="number" class="form-control @error('price') is-invalid @enderror"
                                            id="price" name="price" value="{{ old('price') }}" min="1000" required>
                                        @error('price')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="stock" class="form-label">Stok *</label>
                                        <input type="number" class="form-control @error('stock') is-invalid @enderror"
                                            id="stock" name="stock" value="{{ old('stock', 1) }}" min="1" required>
                                        @error('stock')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="weight" class="form-label">Berat (Gram) *</label>
                                    <input type="number" class="form-control @error('weight') is-invalid @enderror"
                                        id="weight" name="weight" value="{{ old('weight') }}" min="1" required>
                                    @error('weight')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="discount_price" class="form-label">Harga Diskon (Rp) <span
                                            class="text-muted fw-normal">- Opsional</span></label>
                                    <input type="number" class="form-control @error('discount_price') is-invalid @enderror"
                                        id="discount_price" name="discount_price" value="{{ old('discount_price') }}"
                                        min="0">
                                    <small class="text-muted">Kosongkan jika tidak ada diskon. Harga diskon harus lebih
                                        rendah dari harga normal.</small>
                                    @error('discount_price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Images -->
                        <div class="card mb-4">
                            <div class="card-header bg-dark text-white text-uppercase">
                                Foto Produk
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <label for="product_images" class="form-label">Foto Produk (1-6 foto) *</label>
                                    <input type="file" class="form-control @error('product_images.*') is-invalid @enderror"
                                        id="product_images" name="product_images[]" accept="image/*" capture="environment"
                                        multiple required>
                                    <small class="text-muted d-block mt-1">
                                        📷 Bisa pilih banyak file sekaligus ATAU foto langsung dari kamera (max 6 foto)
                                    </small>
                                    @error('product_images.*')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div id="productImagesPreview" class="mt-3 d-flex gap-2 flex-wrap"></div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-3">
                            <button type="submit" class="btn btn-dark btn-medium text-uppercase flex-grow-1">
                                Posting Produk
                            </button>
                            <a href="{{ route('home') }}" class="btn btn-outline-dark">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            // Image Preview
            const imgInput = document.getElementById('product_images');
            if (imgInput) {
                imgInput.addEventListener('change', function (e) {
                    const preview = document.getElementById('productImagesPreview');
                    preview.innerHTML = '';
                    Array.from(this.files).forEach(file => {
                        const img = document.createElement('img');
                        img.src = URL.createObjectURL(file);
                        img.style.width = '80px';
                        img.style.height = '80px';
                        img.style.objectFit = 'cover';
                        img.className = 'rounded';
                        preview.appendChild(img);
                    });
                });
            }

            document.getElementById('btnGetLocation').addEventListener('click', function () {
                const btn = this;
                if (navigator.geolocation) {
                    btn.innerHTML = 'Sedang mencari...';
                    navigator.geolocation.getCurrentPosition(function (position) {
                        document.getElementById('latitude').value = position.coords.latitude;
                        document.getElementById('longitude').value = position.coords.longitude;
                        btn.innerHTML = '✅ Lokasi Tersimpan';
                        // Opsional: jika GPS didapat set nama lokasi sementara
                        if (!document.getElementById('location').value) {
                            document.getElementById('location').value = "Menggunakan GPS Device";
                        }
                    }, function (error) {
                        alert('Gagal mendapatkan lokasi GPS: ' + error.message);
                        btn.innerHTML = '📍 Ambil Lokasi GPS';
                    });
                } else {
                    alert("Geolocation tidak didukung oleh browser Anda.");
                }
            });
        </script>
    @endpush
@endsection