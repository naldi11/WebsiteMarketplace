@extends('layouts.app')

@section('content')
    <div class="py-12 bg-light-blue">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-8">
                    <div class="text-center mb-10">
                        <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Edit Profil</h2>
                        <p class="text-gray-500 text-sm mt-2">Perbarui informasi akun Anda.</p>
                    </div>

                    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data"
                        class="space-y-8">
                        @csrf
                        @method('PUT')

                        <!-- Avatar Section -->
                        <div class="flex flex-col items-center justify-center mb-8">
                            <div class="relative group">
                                <img src="{{ $user->avatar ? Storage::url($user->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($user->name) }}"
                                    class="h-28 w-28 rounded-full border-4 border-white shadow-lg object-cover">
                                <div class="absolute inset-0 bg-black/40 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition duration-200 cursor-pointer backdrop-blur-sm"
                                    onclick="document.getElementById('avatar').click()">
                                    <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z">
                                        </path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    </svg>
                                </div>
                            </div>
                            <input type="file" id="avatar" name="avatar" class="hidden" onchange="this.form.submit()">
                            <p class="text-xs text-gray-400 mt-2 hover:text-pink-500 cursor-pointer transition-colors"
                                onclick="document.getElementById('avatar').click()">Klik gambar untuk ganti</p>
                        </div>

                        @if($user->role === 'seller')
                        <!-- Tab Switcher -->
                        <div class="flex border-b border-gray-200 mb-8 gap-4">
                            <button type="button" id="tab-personal-btn" onclick="switchTab('personal')"
                                class="flex-1 pb-3 text-sm font-semibold border-b-2 transition-all duration-200 focus:outline-none text-center">
                                Informasi Pribadi
                            </button>
                            <button type="button" id="tab-seller-btn" onclick="switchTab('seller')"
                                class="flex-1 pb-3 text-sm font-semibold border-b-2 transition-all duration-200 focus:outline-none text-center">
                                Informasi Rekening Bank
                            </button>
                        </div>
                        @endif

                        <!-- Tab Content 1: Informasi Pribadi -->
                        <div id="personal-tab-content" class="space-y-8">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Name -->
                                <div class="md:col-span-2">
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                                        class="block w-full px-4 py-2.5 rounded-xl border-gray-200 focus:border-pink-500 focus:ring-pink-500 transition-colors bg-gray-50/50 text-sm">
                                </div>

                                <!-- Email (Disabled) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                    <div class="relative">
                                        <input type="email" value="{{ $user->email }}" disabled
                                            class="block w-full px-4 py-2.5 rounded-xl border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed text-sm">
                                        <svg class="w-5 h-5 text-gray-400 absolute right-3 top-2.5" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>

                                <!-- Phone (Disabled) -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nomor Telepon</label>
                                    <div class="relative">
                                        <input type="text" value="{{ $user->phone }}" disabled
                                            class="block w-full px-4 py-2.5 rounded-xl border-gray-200 bg-gray-100 text-gray-500 cursor-not-allowed text-sm">
                                        <svg class="w-5 h-5 text-gray-400 absolute right-3 top-2.5" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                            </path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <!-- Change Password Section -->
                            <div class="border-t border-gray-100 pt-8">
                                <h3 class="text-lg font-bold text-gray-900 mb-6">Ganti Password (Opsional)</h3>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                                        <div class="relative">
                                            <input type="password" name="password" id="password" autocomplete="new-password"
                                                class="block w-full px-4 py-2.5 rounded-xl border-gray-200 focus:border-pink-500 focus:ring-pink-500 transition-colors bg-gray-50/50 text-sm placeholder-gray-400 pr-10">
                                            <button type="button"
                                                class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600"
                                                onclick="togglePassword(this)">
                                                <svg class="h-5 w-5" fill="currentColor">
                                                    <use xlink:href="#eye-slash"></use>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div>
                                        <label for="password_confirmation"
                                            class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password</label>
                                        <div class="relative">
                                            <input type="password" name="password_confirmation" id="password_confirmation"
                                                class="block w-full px-4 py-2.5 rounded-xl border-gray-200 focus:border-pink-500 focus:ring-pink-500 transition-colors bg-gray-50/50 text-sm placeholder-gray-400 pr-10">
                                            <button type="button"
                                                class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-gray-600"
                                                onclick="togglePassword(this)">
                                                <svg class="h-5 w-5" fill="currentColor">
                                                    <use xlink:href="#eye-slash"></use>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if($user->role === 'seller')
                        <!-- Tab Content 2: Informasi Rekening Bank -->
                        <div id="seller-tab-content" class="space-y-8 hidden">
                            <div class="pt-4">
                                <h3 class="text-lg font-bold text-gray-900 mb-6">Informasi Rekening Bank</h3>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                    <div>
                                        <label for="bank_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Bank</label>
                                        <input type="text" name="bank_name" id="bank_name" value="{{ old('bank_name', $user->bank_name) }}" placeholder="Contoh: BCA, Mandiri, BRI"
                                            class="block w-full px-4 py-2.5 rounded-xl border-gray-200 focus:border-pink-500 focus:ring-pink-500 transition-colors bg-gray-50/50 text-sm">
                                    </div>
                                    <div>
                                        <label for="bank_account_number" class="block text-sm font-medium text-gray-700 mb-2">Nomor Rekening</label>
                                        <input type="text" name="bank_account_number" id="bank_account_number" value="{{ old('bank_account_number', $user->bank_account_number) }}" placeholder="Contoh: 12345678"
                                            class="block w-full px-4 py-2.5 rounded-xl border-gray-200 focus:border-pink-500 focus:ring-pink-500 transition-colors bg-gray-50/50 text-sm">
                                    </div>
                                    <div>
                                        <label for="bank_account_name" class="block text-sm font-medium text-gray-700 mb-2">Nama Pemilik Rekening</label>
                                        <input type="text" name="bank_account_name" id="bank_account_name" value="{{ old('bank_account_name', $user->bank_account_name) }}" placeholder="Sesuai rekening"
                                            class="block w-full px-4 py-2.5 rounded-xl border-gray-200 focus:border-pink-500 focus:ring-pink-500 transition-colors bg-gray-50/50 text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif

                        <div class="pt-4">
                            <button type="submit"
                                class="w-full bg-gray-900 text-white font-bold py-3.5 rounded-xl hover:bg-black transition transform active:scale-[0.98] shadow-lg shadow-gray-200 text-sm uppercase tracking-wide">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @if($user->role === 'seller')
    <script>
        function switchTab(tab) {
            const personalTabBtn = document.getElementById('tab-personal-btn');
            const sellerTabBtn = document.getElementById('tab-seller-btn');
            const personalContent = document.getElementById('personal-tab-content');
            const sellerContent = document.getElementById('seller-tab-content');
            
            if (!personalTabBtn || !sellerTabBtn) return;

            if (tab === 'seller') {
                // Aktifkan Tab Seller
                sellerContent.classList.remove('hidden');
                personalContent.classList.add('hidden');
                
                sellerTabBtn.classList.add('border-pink-500', 'text-pink-600');
                sellerTabBtn.classList.remove('border-transparent', 'text-gray-500');
                
                personalTabBtn.classList.add('border-transparent', 'text-gray-500');
                personalTabBtn.classList.remove('border-pink-500', 'text-pink-600');

                // Update URL query parameter tanpa reload
                const url = new URL(window.location);
                url.searchParams.set('tab', 'seller');
                window.history.replaceState({}, '', url);
            } else {
                // Aktifkan Tab Personal
                personalContent.classList.remove('hidden');
                sellerContent.classList.add('hidden');
                
                personalTabBtn.classList.add('border-pink-500', 'text-pink-600');
                personalTabBtn.classList.remove('border-transparent', 'text-gray-500');
                
                sellerTabBtn.classList.add('border-transparent', 'text-gray-500');
                sellerTabBtn.classList.remove('border-pink-500', 'text-pink-600');

                const url = new URL(window.location);
                url.searchParams.set('tab', 'personal');
                window.history.replaceState({}, '', url);
            }
        }

        // Jalankan saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab');
            
            // Cek jika ada error validasi khusus data seller
            const hasSellerErrors = @json($errors->has('shop_name') || $errors->has('bank_name') || $errors->has('bank_account_number') || $errors->has('bank_account_name'));

            if (tabParam === 'seller' || hasSellerErrors) {
                switchTab('seller');
            } else {
                switchTab('personal');
            }
        });
    </script>
    @endif
@endsection