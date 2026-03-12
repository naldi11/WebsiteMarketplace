@extends('layouts.app')

@section('content')
    <div class="py-12">
        <div class="max-w-4xl mx-auto bg-white rounded-3xl shadow-xl overflow-hidden">
            <div class="bg-indigo-600 px-8 py-6">
                <h2 class="text-2xl font-bold text-white">Konfirmasi Pembelian Keranjang</h2>
                <p class="text-indigo-100">Anda akan membeli {{ $cartItems->sum('quantity') }} barang.</p>
            </div>

            <div class="p-8">
                <div class="space-y-6 mb-8">
                    @foreach($cartItems as $item)
                        <div class="flex items-center gap-4 pb-4 border-b border-gray-100 last:border-0">
                            <img src="{{ Storage::url($item->product->image) }}"
                                class="w-16 h-16 object-cover rounded-lg bg-gray-100">
                            <div class="flex-1">
                                <h3 class="font-bold text-gray-900">{{ $item->product->name }}</h3>
                                @if($item->product->hasDiscount())
                                    <p class="text-sm text-gray-500">
                                        {{ $item->quantity }} x
                                        <span class="line-through text-gray-400">Rp
                                            {{ number_format($item->product->price, 0, ',', '.') }}</span>
                                        <span class="text-red-500 font-semibold">Rp
                                            {{ number_format($item->product->effective_price, 0, ',', '.') }}</span>
                                    </p>
                                @else
                                    <p class="text-sm text-gray-500">{{ $item->quantity }} x Rp
                                        {{ number_format($item->product->price, 0, ',', '.') }}
                                    </p>
                                @endif
                            </div>
                            <div class="font-bold text-gray-900">
                                Rp {{ number_format($item->quantity * $item->product->effective_price, 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="space-y-4 mb-4 pt-4 border-t border-gray-100">
                    <form action="{{ route('checkout.cart') }}" method="GET" class="flex gap-2">
                        <input type="text" name="voucher_code" placeholder="Punya kode voucher?" value="{{ $voucherCode }}"
                            class="flex-1 rounded-lg border-gray-300 text-sm focus:ring-indigo-500 focus:border-indigo-500">
                        <button type="submit"
                            class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-bold hover:bg-gray-900 transition">Cek</button>
                    </form>
                    @if(session('error'))
                        <p class="text-red-500 text-sm">{{ session('error') }}</p>
                    @endif
                    @if($voucherCode)
                        <p class="text-green-600 text-sm font-bold">Voucher {{ $voucherCode }} digunakan!</p>
                    @endif
                </div>

                <div class="flex justify-between items-center text-gray-600 mb-2">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($totalPrice, 0, ',', '.') }}</span>
                </div>

                @if(isset($discount) && $discount > 0)
                    <div class="flex justify-between items-center text-green-600 mb-2 font-medium">
                        <span>Diskon</span>
                        <span>- Rp {{ number_format($discount, 0, ',', '.') }}</span>
                    </div>
                @endif

                <div
                    class="flex justify-between items-center text-xl font-bold text-gray-900 pt-4 border-t border-gray-100 mb-8">
                    <span>Total Pembayaran</span>
                    <span>Rp {{ number_format($finalPrice ?? $totalPrice, 0, ',', '.') }}</span>
                </div>

                <form action="{{ route('transactions.store_cart') }}" method="POST">
                    @csrf
                    <input type="hidden" name="voucher_code" value="{{ $voucherCode }}">
                    <div class="bg-yellow-50 text-yellow-800 p-4 rounded-xl mb-6 text-sm">
                        <p class="font-bold mb-1">Penting:</p>
                        <p>Pastikan saldo Anda cukup atau segera lakukan transfer manual ke Admin setelah ini.</p>
                    </div>

                    <button type="submit"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-indigo-500/30 transition transform hover:-translate-y-0.5">
                        Bayar Semua Sekarang
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection