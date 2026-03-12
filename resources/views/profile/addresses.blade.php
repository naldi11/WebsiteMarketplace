@extends('layouts.app')

@section('content')
    <div class="py-3 px-3">
        <div class="max-w-4xl mx-auto">
            {{-- Header --}}
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">Alamat Tersimpan</h1>
                    <p class="text-xs text-gray-500">Kelola alamat pengiriman Anda</p>
                </div>
                <button onclick="openAddModal()"
                    class="bg-pink-600 hover:bg-pink-700 text-white font-bold py-2 px-4 rounded-xl text-xs shadow-lg shadow-pink-500/30 transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Alamat
                </button>
            </div>

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="mb-4 p-3 rounded-xl bg-green-50 border border-green-200 text-green-700 text-xs">
                    {{ session('success') }}
                </div>
            @endif

            {{-- Address List --}}
            @if($addresses->count() > 0)
                <div class="grid gap-3">
                    @foreach($addresses as $address)
                        <div
                            class="bg-white rounded-xl border-2 {{ $address->is_default ? 'border-pink-500' : 'border-gray-200' }} p-4 relative overflow-hidden transition hover:shadow-md">
                            {{-- Default Badge --}}
                            @if($address->is_default)
                                <div
                                    class="absolute top-0 right-0 bg-pink-500 text-white text-[10px] font-bold px-3 py-1 rounded-bl-xl">
                                    UTAMA
                                </div>
                            @endif

                            <div class="flex items-start gap-4">
                                {{-- Icon --}}
                                <div class="w-12 h-12 rounded-xl bg-pink-50 flex items-center justify-center text-2xl shrink-0">
                                    {{ $address->label_icon }}
                                </div>

                                {{-- Details --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="font-bold text-gray-900 text-sm">{{ $address->label }}</span>
                                        <span class="text-gray-400">•</span>
                                        <span class="text-xs text-gray-600">{{ $address->recipient_name }}</span>
                                    </div>
                                    <p class="text-xs text-gray-600 mb-1">{{ $address->phone }}</p>
                                    <p class="text-xs text-gray-500 line-clamp-2">{{ $address->formatted_address }}</p>

                                    {{-- Map Preview --}}
                                    @if($address->latitude && $address->longitude)
                                        <div class="mt-2 h-24 rounded-lg overflow-hidden border border-gray-200"
                                            id="map-preview-{{ $address->id }}"></div>
                                    @endif
                                </div>

                                {{-- Actions --}}
                                <div class="flex flex-col gap-1 shrink-0">
                                    @if(!$address->is_default)
                                        <form action="{{ route('addresses.default', $address) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-[10px] text-pink-600 hover:text-pink-700 font-bold">
                                                Set Utama
                                            </button>
                                        </form>
                                    @endif
                                    <button onclick="openEditModal({{ json_encode($address) }})"
                                        class="text-[10px] text-gray-500 hover:text-gray-700 font-bold">
                                        Edit
                                    </button>
                                    <form action="{{ route('addresses.destroy', $address) }}" method="POST"
                                        onsubmit="return confirm('Hapus alamat ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-[10px] text-red-500 hover:text-red-700 font-bold">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-16 bg-white rounded-2xl border-2 border-dashed border-gray-200">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center text-3xl">
                        📍
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Belum ada alamat</h3>
                    <p class="text-sm text-gray-500 mb-4">Tambahkan alamat pengiriman Anda</p>
                    <button onclick="openAddModal()"
                        class="bg-pink-600 hover:bg-pink-700 text-white font-bold py-2 px-6 rounded-xl text-sm transition">
                        Tambah Alamat
                    </button>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Add/Edit Address --}}
    <div id="addressModal" class="fixed inset-0 z-50 hidden">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeModal()"></div>
        <div
            class="absolute inset-4 md:inset-auto md:top-1/2 md:left-1/2 md:-translate-x-1/2 md:-translate-y-1/2 md:w-full md:max-w-2xl bg-white rounded-2xl shadow-2xl overflow-hidden flex flex-col max-h-[90vh]">
            {{-- Modal Header --}}
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                <h3 id="modalTitle" class="text-lg font-bold text-gray-900">Tambah Alamat Baru</h3>
                <button onclick="closeModal()" class="p-2 hover:bg-gray-100 rounded-full transition">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>

            {{-- Modal Body --}}
            <form id="addressForm" method="POST" class="flex-1 overflow-y-auto">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="p-6 space-y-4">
                    {{-- Label Selection --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2">Simpan Sebagai</label>
                        <div class="flex flex-wrap gap-2">
                            @foreach($labels as $key => $value)
                                <label class="cursor-pointer">
                                    <input type="radio" name="label" value="{{ $value }}" class="hidden peer" {{ $loop->first ? 'checked' : '' }}>
                                    <span
                                        class="inline-block px-4 py-2 rounded-xl border-2 border-gray-200 text-xs font-bold text-gray-600 peer-checked:border-pink-500 peer-checked:bg-pink-50 peer-checked:text-pink-600 transition">
                                        {{ $value }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Recipient Info --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nama Penerima</label>
                            <input type="text" name="recipient_name" required
                                class="w-full rounded-xl border-gray-200 text-sm focus:border-pink-500 focus:ring-pink-500"
                                placeholder="Nama lengkap penerima">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Nomor Telepon</label>
                            <div class="flex">
                                <span
                                    class="inline-flex items-center px-3 rounded-l-xl border border-r-0 border-gray-200 bg-gray-50 text-gray-500 text-sm font-medium">
                                    +62
                                </span>
                                <input type="tel" name="phone" required
                                    class="w-full rounded-r-xl border-gray-200 text-sm focus:border-pink-500 focus:ring-pink-500"
                                    placeholder="8123456789" pattern="[0-9]*" inputmode="numeric"
                                    oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>
                        </div>
                    </div>

                    {{-- Map Section --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-2">Pilih Lokasi di Peta</label>
                        <div class="relative">
                            {{-- Search Box --}}
                            <div class="absolute top-3 left-3 right-3 z-[1000]">
                                <div class="relative">
                                    <input type="text" id="mapSearch"
                                        class="w-full pl-10 pr-4 py-2.5 rounded-xl border-0 shadow-lg text-sm focus:ring-2 focus:ring-pink-500"
                                        placeholder="Cari alamat atau lokasi...">
                                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                                {{-- Search Results --}}
                                <div id="searchResults"
                                    class="hidden mt-2 bg-white rounded-xl shadow-lg max-h-48 overflow-y-auto"></div>
                            </div>

                            {{-- Map Container --}}
                            <div id="mapPicker" class="h-64 rounded-xl border-2 border-gray-200 overflow-hidden"></div>

                            {{-- Current Location Button --}}
                            <button type="button" onclick="getCurrentLocation()"
                                class="absolute bottom-3 right-3 z-[1000] bg-white p-2.5 rounded-xl shadow-lg hover:bg-gray-50 transition">
                                <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z">
                                    </path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                </svg>
                            </button>
                        </div>
                        <p class="text-[10px] text-gray-500 mt-1">Klik pada peta atau geser marker untuk memilih lokasi</p>
                    </div>

                    {{-- Hidden Coordinates --}}
                    <input type="hidden" name="latitude" id="inputLatitude">
                    <input type="hidden" name="longitude" id="inputLongitude">

                    {{-- Address Details --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">Alamat Lengkap</label>
                        <textarea name="full_address" id="inputFullAddress" rows="2" required
                            class="w-full rounded-xl border-gray-200 text-sm focus:border-pink-500 focus:ring-pink-500"
                            placeholder="Nama jalan, nomor rumah, RT/RW, gedung, lantai, dll..."></textarea>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Provinsi</label>
                            <input type="text" name="province" id="inputProvince"
                                class="w-full rounded-xl border-gray-200 text-sm focus:border-pink-500 focus:ring-pink-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Kota/Kabupaten</label>
                            <input type="text" name="city" id="inputCity"
                                class="w-full rounded-xl border-gray-200 text-sm focus:border-pink-500 focus:ring-pink-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Kecamatan</label>
                            <input type="text" name="district" id="inputDistrict"
                                class="w-full rounded-xl border-gray-200 text-sm focus:border-pink-500 focus:ring-pink-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-700 mb-1">Kode Pos</label>
                            <input type="text" name="postal_code" id="inputPostalCode"
                                class="w-full rounded-xl border-gray-200 text-sm focus:border-pink-500 focus:ring-pink-500">
                        </div>
                    </div>

                    {{-- Set as Default --}}
                    <label class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl cursor-pointer">
                        <input type="checkbox" name="is_default" value="1"
                            class="w-5 h-5 rounded border-gray-300 text-pink-600 focus:ring-pink-500">
                        <div>
                            <span class="text-sm font-bold text-gray-900">Jadikan Alamat Utama</span>
                            <p class="text-xs text-gray-500">Alamat ini akan dipilih otomatis saat checkout</p>
                        </div>
                    </label>
                </div>

                {{-- Modal Footer --}}
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 shrink-0">
                    <button type="submit"
                        class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-pink-500/30 transition">
                        Simpan Alamat
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- Leaflet CSS & JS --}}
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <style>
        .leaflet-container {
            font-family: inherit;
        }

        .custom-marker {
            background: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
            border: 3px solid white;
            border-radius: 50% 50% 50% 0;
            transform: rotate(-45deg);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        }

        .custom-marker::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 10px;
            height: 10px;
            background: white;
            border-radius: 50%;
        }
    </style>

    <script>
        let map, marker;
        let searchTimeout;
        let currentLat = null;
        let currentLng = null;

        // Initialize modal map - will try to get user's GPS first
        function initMap(lat = null, lng = null) {
            if (map) {
                map.remove();
            }

            // If no coordinates provided, try to get current location
            if (!lat || !lng) {
                // Use Medan/Sumatera Utara as default instead of Jakarta
                lat = 3.5952;
                lng = 98.6722;

                // Try to get actual location
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        (position) => {
                            currentLat = position.coords.latitude;
                            currentLng = position.coords.longitude;
                            if (map) {
                                map.setView([currentLat, currentLng], 15);
                                marker.setLatLng([currentLat, currentLng]);
                                updateCoordinates(currentLat, currentLng);
                                reverseGeocode(currentLat, currentLng);
                            }
                        },
                        (error) => {
                            console.log('Geolocation not available, using default');
                        },
                        { timeout: 5000 }
                    );
                }
            } else {
                currentLat = lat;
                currentLng = lng;
            }

            map = L.map('mapPicker', {
                center: [lat, lng],
                zoom: 15,
                zoomControl: false
            });

            // Add zoom control to bottom right
            L.control.zoom({ position: 'bottomleft' }).addTo(map);

            // Base layers
            const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '© OpenStreetMap',
                maxZoom: 19
            });

            const satelliteLayer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: '© Esri',
                maxZoom: 19
            });

            const cartoLayer = L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                attribution: '© CartoDB',
                maxZoom: 19
            });

            // Default layer
            cartoLayer.addTo(map);

            // Layer control
            const baseLayers = {
                "🗺️ Peta": cartoLayer,
                "🛣️ Street": osmLayer,
                "🛰️ Satelit": satelliteLayer
            };
            L.control.layers(baseLayers, null, { position: 'topright' }).addTo(map);

            // Create custom marker icon
            const customIcon = L.divIcon({
                className: 'custom-marker-wrapper',
                html: '<div class="custom-marker" style="width:30px;height:30px;position:relative;"></div>',
                iconSize: [30, 42],
                iconAnchor: [15, 42],
                popupAnchor: [0, -42]
            });

            // Add draggable marker
            marker = L.marker([lat, lng], {
                draggable: true,
                icon: customIcon
            }).addTo(map);

            // Update current position when map moves
            map.on('moveend', function () {
                const center = map.getCenter();
                currentLat = center.lat;
                currentLng = center.lng;
            });

            // Update on drag end
            marker.on('dragend', function (e) {
                const pos = e.target.getLatLng();
                updateCoordinates(pos.lat, pos.lng);
                reverseGeocode(pos.lat, pos.lng);
            });

            // Update on map click
            map.on('click', function (e) {
                marker.setLatLng(e.latlng);
                updateCoordinates(e.latlng.lat, e.latlng.lng);
                reverseGeocode(e.latlng.lat, e.latlng.lng);
            });

            // Search functionality
            const searchInput = document.getElementById('mapSearch');
            const searchResults = document.getElementById('searchResults');

            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimeout);
                const query = this.value.trim();

                if (query.length < 3) {
                    searchResults.classList.add('hidden');
                    return;
                }

                searchTimeout = setTimeout(() => searchLocation(query), 500);
            });
        }

        // Search location using Nominatim - prioritizes area near current map view
        async function searchLocation(query) {
            const searchResults = document.getElementById('searchResults');

            try {
                // Get current map bounds to prioritize search in visible area
                let viewboxParam = '';
                if (map && currentLat && currentLng) {
                    const bounds = map.getBounds();
                    // Expand bounds slightly for better results
                    const sw = bounds.getSouthWest();
                    const ne = bounds.getNorthEast();
                    viewboxParam = `&viewbox=${sw.lng - 1},${ne.lat + 1},${ne.lng + 1},${sw.lat - 1}&bounded=0`;
                }

                const response = await fetch(
                    `https://nominatim.openstreetmap.org/search?format=json&q=${encodeURIComponent(query)}&countrycodes=id&limit=8${viewboxParam}`
                );
                const data = await response.json();

                // Sort results by distance to current map center
                if (currentLat && currentLng && data.length > 0) {
                    data.sort((a, b) => {
                        const distA = Math.pow(a.lat - currentLat, 2) + Math.pow(a.lon - currentLng, 2);
                        const distB = Math.pow(b.lat - currentLat, 2) + Math.pow(b.lon - currentLng, 2);
                        return distA - distB;
                    });
                }

                if (data.length > 0) {
                    searchResults.innerHTML = data.slice(0, 5).map(item => `
                                    <div class="p-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-0" 
                                         onclick="selectSearchResult(${item.lat}, ${item.lon}, '${item.display_name.replace(/'/g, "\\'")}')">
                                        <p class="text-sm font-medium text-gray-900 line-clamp-1">${item.display_name.split(',')[0]}</p>
                                        <p class="text-xs text-gray-500 line-clamp-1">${item.display_name}</p>
                                    </div>
                                `).join('');
                    searchResults.classList.remove('hidden');
                } else {
                    searchResults.innerHTML = '<div class="p-3 text-sm text-gray-500">Lokasi tidak ditemukan</div>';
                    searchResults.classList.remove('hidden');
                }
            } catch (error) {
                console.error('Search error:', error);
            }
        }

        // Select search result
        function selectSearchResult(lat, lng, name) {
            document.getElementById('searchResults').classList.add('hidden');
            document.getElementById('mapSearch').value = '';

            map.setView([lat, lng], 17);
            marker.setLatLng([lat, lng]);
            updateCoordinates(lat, lng);
            reverseGeocode(lat, lng);
        }

        // Get current location
        function getCurrentLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        currentLat = lat;
                        currentLng = lng;
                        map.setView([lat, lng], 17);
                        marker.setLatLng([lat, lng]);
                        updateCoordinates(lat, lng);
                        reverseGeocode(lat, lng);
                    },
                    (error) => {
                        alert('Tidak dapat mengakses lokasi. Pastikan GPS aktif dan izinkan akses lokasi.');
                    }
                );
            } else {
                alert('Browser tidak mendukung geolocation');
            }
        }

        // Update hidden coordinate inputs
        function updateCoordinates(lat, lng) {
            document.getElementById('inputLatitude').value = lat;
            document.getElementById('inputLongitude').value = lng;
        }

        // Reverse geocode to get address
        async function reverseGeocode(lat, lng) {
            try {
                const response = await fetch(
                    `https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lng}&zoom=18&addressdetails=1`
                );
                const data = await response.json();

                if (data && data.address) {
                    const addr = data.address;
                    document.getElementById('inputFullAddress').value = data.display_name.split(',').slice(0, 3).join(', ');
                    document.getElementById('inputProvince').value = addr.state || '';
                    document.getElementById('inputCity').value = addr.city || addr.town || addr.county || '';
                    document.getElementById('inputDistrict').value = addr.suburb || addr.village || addr.neighbourhood || '';
                    document.getElementById('inputPostalCode').value = addr.postcode || '';
                }
            } catch (error) {
                console.error('Reverse geocode error:', error);
            }
        }

        // Open Add Modal - tries to get GPS location first
        function openAddModal() {
            document.getElementById('modalTitle').textContent = 'Tambah Alamat Baru';
            document.getElementById('addressForm').action = '{{ route("addresses.store") }}';
            document.getElementById('formMethod').value = 'POST';
            document.getElementById('addressForm').reset();
            document.getElementById('addressModal').classList.remove('hidden');

            // Initialize map - will automatically try to get GPS
            setTimeout(() => initMap(), 100);
        }

        // Open Edit Modal
        function openEditModal(address) {
            document.getElementById('modalTitle').textContent = 'Edit Alamat';
            document.getElementById('addressForm').action = `/addresses/${address.id}`;
            document.getElementById('formMethod').value = 'PUT';

            // Fill form
            document.querySelector(`input[name="label"][value="${address.label}"]`).checked = true;
            document.querySelector('input[name="recipient_name"]').value = address.recipient_name;
            document.querySelector('input[name="phone"]').value = address.phone;
            document.getElementById('inputFullAddress').value = address.full_address;
            document.getElementById('inputProvince').value = address.province || '';
            document.getElementById('inputCity').value = address.city || '';
            document.getElementById('inputDistrict').value = address.district || '';
            document.getElementById('inputPostalCode').value = address.postal_code || '';
            document.getElementById('inputLatitude').value = address.latitude || '';
            document.getElementById('inputLongitude').value = address.longitude || '';

            document.getElementById('addressModal').classList.remove('hidden');

            const lat = address.latitude || defaultLat;
            const lng = address.longitude || defaultLng;
            setTimeout(() => initMap(lat, lng), 100);
        }

        // Close Modal
        function closeModal() {
            document.getElementById('addressModal').classList.add('hidden');
        }

        // Initialize mini maps for previews
        document.addEventListener('DOMContentLoaded', function () {
            @foreach($addresses as $address)
                @if($address->latitude && $address->longitude)
                    (function () {
                        const miniMap = L.map('map-preview-{{ $address->id }}', {
                            center: [{{ $address->latitude }}, {{ $address->longitude }}],
                            zoom: 15,
                            zoomControl: false,
                            dragging: false,
                            touchZoom: false,
                            scrollWheelZoom: false,
                            doubleClickZoom: false
                        });
                        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png').addTo(miniMap);
                        L.marker([{{ $address->latitude }}, {{ $address->longitude }}]).addTo(miniMap);
                    })();
                @endif
            @endforeach
                    });
    </script>
@endsection