@extends('layouts.app')

@section('content')
    <div class="min-h-[70vh] flex flex-col justify-center py-12 sm:px-6 lg:px-8">
        <div class="sm:mx-auto sm:w-full sm:max-w-md">
            <h2 class="text-center text-3xl font-extrabold text-gray-900 tracking-tight">Verifikasi OTP</h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                Masukkan 6 digit kode yang dikirim ke kontak Anda.
            </p>
        </div>

        <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
            <div class="bg-white py-8 px-4 shadow-xl shadow-gray-200 border border-gray-100 sm:rounded-2xl sm:px-10">
                <form class="space-y-6" action="{{ route('password.verify') }}" method="POST">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Kode OTP</label>
                        <div class="mt-1">
                            <input name="otp" type="text" placeholder="123456" maxlength="6"
                                class="text-center tracking-widest text-2xl font-bold appearance-none block w-full px-3 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-200 focus:outline-none focus:ring-black focus:border-black">
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full flex justify-center py-2.5 px-4 border border-transparent rounded-lg shadow-sm text-sm font-bold text-white bg-gray-900 hover:bg-black focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-900 transition">
                        Verifikasi
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection