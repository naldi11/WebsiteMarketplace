@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-2xl mx-auto bg-white p-8 rounded-2xl shadow-lg shadow-indigo-100 border border-gray-100">
            <h2 class="text-2xl font-bold mb-6 text-gray-900 border-b pb-4">Edit Barang</h2>

            <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data"
                class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Image Upload Preview -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Foto Utama</label>
                    <div class="flex gap-4 items-center">
                        <img src="{{ Storage::url($product->image) }}"
                            class="w-24 h-24 rounded-lg object-cover bg-gray-100">
                        <input type="file" name="image" accept="image/*" class="block w-full text-sm text-gray-500
                                        file:mr-4 file:py-2 file:px-4
                                        file:rounded-full file:border-0
                                        file:text-sm file:font-semibold
                                        file:bg-indigo-50 file:text-indigo-700
                                        hover:file:bg-indigo-100">
                    </div>
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengubah foto utama</p>
                </div>

                <!-- Additional Images -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Foto Tambahan (Opsional)</label>
                    @if($product->images && count($product->images) > 0)
                        <div class="flex gap-2 mb-3 flex-wrap">
                            @foreach($product->images as $img)
                                <img src="{{ Storage::url($img->image_path) }}"
                                    class="w-20 h-20 rounded-lg object-cover bg-gray-100">
                            @endforeach
                        </div>
                    @endif
                    <input type="file" name="additional_images[]" multiple accept="image/*" class="block w-full text-sm text-gray-500
                                    file:mr-4 file:py-2 file:px-4
                                    file:rounded-full file:border-0
                                    file:text-sm file:font-semibold
                                    file:bg-blue-50 file:text-blue-700
                                    hover:file:bg-blue-100">
                    <p class="text-xs text-gray-500 mt-1">Bisa pilih banyak file sekaligus (max 5). Foto lama akan diganti
                        jika upload baru.</p>
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Barang</label>
                    <input type="text" name="name" id="name" required value="{{ old('name', $product->name) }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="category_id" class="block text-sm font-medium text-gray-700">Kategori</label>
                        <select name="category_id" id="category_id" required
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="condition" class="block text-sm font-medium text-gray-700">Kondisi</label>
                        <select name="condition" id="condition" required
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <option value="like_new" {{ (old('condition', $product->condition) == 'like_new') ? 'selected' : '' }}>Seperti Baru</option>
                            <option value="used" {{ (old('condition', $product->condition) == 'used') ? 'selected' : '' }}>Bekas</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="stock" class="block text-sm font-medium text-gray-700">Stok</label>
                        <input type="number" name="stock" id="stock" value="{{ old('stock', $product->stock) }}" min="0"
                            required
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label for="weight" class="block text-sm font-medium text-gray-700">Berat (Gram)</label>
                        <input type="number" name="weight" id="weight" value="{{ old('weight', $product->weight) }}" min="1"
                            required
                            class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Harga (Rp)</label>
                    <input type="number" name="price" id="price" required value="{{ old('price', $product->price) }}"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                </div>

                <div>
                    <label for="discount_price" class="block text-sm font-medium text-gray-700">Jumlah Diskon (Rp) <span
                            class="text-gray-400 font-normal">- Opsional</span></label>
                    <input type="number" name="discount_price" id="discount_price"
                        value="{{ old('discount_price', $product->discount_price) }}" min="0"
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50"
                        {{ $product->hasDiscount() && old('remove_discount') ? 'disabled' : '' }}>
                    <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ada diskon.</p>
                </div>

                <div>
                    <label for="location" class="block text-sm font-medium text-gray-700">Lokasi Barang</label>
                    <div class="flex gap-2">
                        <input type="text" name="location" id="location" required
                            value="{{ old('location', $product->location) }}"
                            class="mt-1 block flex-1 rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                        <button type="button" id="btnGetLocation" class="mt-1 px-4 py-2 bg-gray-100 border border-gray-300 rounded-lg text-sm font-bold">📍 GPS</button>
                    </div>
                    <div id="map" class="mt-2 rounded-lg border border-gray-200" style="height: 200px; z-index: 1;"></div>
                    <input type="hidden" name="latitude" id="latitude" value="{{ old('latitude', $product->latitude) }}">
                    <input type="hidden" name="longitude" id="longitude" value="{{ old('longitude', $product->longitude) }}">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">Deskripsi</label>
                    <textarea name="description" id="description" rows="4" required
                        class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">{{ old('description', $product->description) }}</textarea>
                </div>

                <button type="submit"
                    class="w-full bg-indigo-600 text-white font-bold py-3 rounded-full hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/30">
                    Simpan Perubahan
                </button>
            </form>
        </div>
    </div>
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
        <script>
            // Map Initialization
            var initialLat = {{ $product->latitude ?? -6.2000 }};
            var initialLng = {{ $product->longitude ?? 106.8166 }};
            
            var map = L.map('map').setView([initialLat, initialLng], 15);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; OpenStreetMap contributors'
            }).addTo(map);

            var marker = L.marker([initialLat, initialLng], {draggable: true}).addTo(map);

            function updateMarker(lat, lng) {
                marker.setLatLng([lat, lng]);
                map.setView([lat, lng], 15);
                document.getElementById('latitude').value = lat;
                document.getElementById('longitude').value = lng;
            }

            marker.on('dragend', function(e) {
                var pos = marker.getLatLng();
                updateMarker(pos.lat, pos.lng);
            });

            map.on('click', function(e) {
                updateMarker(e.latlng.lat, e.latlng.lng);
            });

            document.getElementById('btnGetLocation').addEventListener('click', function () {
                const btn = this;
                if (navigator.geolocation) {
                    btn.innerHTML = 'Mencari...';
                    navigator.geolocation.getCurrentPosition(function (position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        updateMarker(lat, lng);
                        btn.innerHTML = '✅ Lokasi Device';
                    }, function (error) {
                        alert('Gagal mendapatkan lokasi GPS: ' + error.message);
                        btn.innerHTML = '📍 GPS';
                    });
                } else {
                    alert("Geolocation tidak didukung oleh browser.");
                }
            });
        </script>
    @endpush
@endsection