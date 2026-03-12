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

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                                <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" required
                                    class="block w-full px-4 py-2.5 rounded-xl border-gray-200 focus:border-pink-500 focus:ring-pink-500 transition-colors bg-gray-50/50 text-sm">
                            </div>

                            <!-- Shop Name -->
                            <div>
                                <label for="shop_name" class="block text-sm font-medium text-gray-700 mb-2">Nama
                                    Toko</label>
                                <input type="text" name="shop_name" id="shop_name"
                                    value="{{ old('shop_name', $user->shop_name) }}" placeholder="Nama toko"
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
                        <div class="border-t border-gray-100 pt-8 mt-4">
                            <h3 class="text-lg font-bold text-gray-900 mb-6">Ganti Password (Opsional)</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">Password
                                        Baru</label>
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
@endsection