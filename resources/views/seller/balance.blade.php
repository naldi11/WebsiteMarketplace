@extends('layouts.app')

@section('content')
    <div class="py-3 px-3">
        <div class="max-w-4xl mx-auto space-y-6">
            {{-- Header --}}
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-bold text-gray-900">💰 Saldo Penjual</h1>
                    <p class="text-sm text-gray-500">Kelola pendapatan dan penarikan saldo</p>
                </div>
            </div>

            {{-- Balance Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                {{-- Available Balance --}}
                <div class="rounded-2xl p-5 shadow-lg"
                    style="background: linear-gradient(135deg, #22c55e 0%, #16a34a 100%);">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                            style="background: rgba(255,255,255,0.2);">
                            <span class="text-xl">💵</span>
                        </div>
                        <p class="text-sm font-medium" style="color: #bbf7d0;">Saldo Tersedia</p>
                    </div>
                    <p class="text-2xl font-bold text-white">Rp
                        {{ number_format($balance->available_balance, 0, ',', '.') }}</p>
                    <p class="text-xs mt-1" style="color: #bbf7d0;">Dapat ditarik ke rekening</p>
                </div>

                {{-- Pending Balance --}}
                <div class="rounded-2xl p-5 shadow-lg"
                    style="background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%);">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                            style="background: rgba(255,255,255,0.2);">
                            <span class="text-xl">⏳</span>
                        </div>
                        <p class="text-sm font-medium" style="color: #fef3c7;">Saldo Pending</p>
                    </div>
                    <p class="text-2xl font-bold text-white">Rp {{ number_format($balance->pending_balance, 0, ',', '.') }}
                    </p>
                    <p class="text-xs mt-1" style="color: #fef3c7;">Menunggu pesanan selesai</p>
                </div>

                {{-- Total Withdrawn --}}
                <div class="rounded-2xl p-5 shadow-lg"
                    style="background: linear-gradient(135deg, #a855f7 0%, #ec4899 100%);">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                            style="background: rgba(255,255,255,0.2);">
                            <span class="text-xl">🏦</span>
                        </div>
                        <p class="text-sm font-medium" style="color: #f3e8ff;">Total Ditarik</p>
                    </div>
                    <p class="text-2xl font-bold text-white">Rp
                        {{ number_format($balance->withdrawn_balance, 0, ',', '.') }}</p>
                    <p class="text-xs mt-1" style="color: #f3e8ff;">Total penarikan berhasil</p>
                </div>
            </div>

            {{-- Stats Row --}}
            <div class="grid grid-cols-3 gap-4">
                <div class="bg-white rounded-xl p-4 border-2 border-gray-100 text-center">
                    <p class="text-2xl font-bold text-pink-600">{{ $stats['total_sales'] }}</p>
                    <p class="text-xs text-gray-500">Pesanan Selesai</p>
                </div>
                <div class="bg-white rounded-xl p-4 border-2 border-gray-100 text-center">
                    <p class="text-2xl font-bold text-green-600">Rp {{ number_format($stats['total_earnings'] / 1000, 0) }}K
                    </p>
                    <p class="text-xs text-gray-500">Total Pendapatan</p>
                </div>
                <div class="bg-white rounded-xl p-4 border-2 border-gray-100 text-center">
                    <p class="text-2xl font-bold text-blue-600">{{ $stats['pending_orders'] }}</p>
                    <p class="text-xs text-gray-500">Pesanan Aktif</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Withdrawal Form --}}
                <div class="bg-white rounded-2xl border-2 border-gray-200 p-5">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span>🏦</span> Tarik Saldo
                    </h3>

                    @if($balance->available_balance >= 50000)
                        <form action="{{ route('seller.withdraw') }}" method="POST" class="space-y-4">
                            @csrf
                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Jumlah Penarikan</label>
                                <div class="relative">
                                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">Rp</span>
                                    <input type="number" name="amount"
                                        class="w-full pl-10 rounded-xl border-gray-200 focus:border-pink-500 focus:ring-pink-500"
                                        min="50000" max="{{ $balance->available_balance }}"
                                        value="{{ min(100000, $balance->available_balance) }}" required>
                                </div>
                                <p class="text-[10px] text-gray-500 mt-1">Minimal Rp 50.000 • Maks Rp
                                    {{ number_format($balance->available_balance, 0, ',', '.') }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Bank</label>
                                <select name="bank_name" required
                                    class="w-full rounded-xl border-gray-200 focus:border-pink-500 focus:ring-pink-500">
                                    <option value="">Pilih Bank</option>
                                    <option value="BCA">BCA</option>
                                    <option value="Mandiri">Mandiri</option>
                                    <option value="BNI">BNI</option>
                                    <option value="BRI">BRI</option>
                                    <option value="CIMB Niaga">CIMB Niaga</option>
                                    <option value="Danamon">Danamon</option>
                                    <option value="BSI">BSI</option>
                                    <option value="Permata">Permata</option>
                                    <option value="DANA">DANA</option>
                                    <option value="OVO">OVO</option>
                                    <option value="GoPay">GoPay</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Nomor Rekening</label>
                                <input type="text" name="account_number"
                                    class="w-full rounded-xl border-gray-200 focus:border-pink-500 focus:ring-pink-500"
                                    placeholder="1234567890" required>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 mb-1">Nama Pemilik Rekening</label>
                                <input type="text" name="account_name"
                                    class="w-full rounded-xl border-gray-200 focus:border-pink-500 focus:ring-pink-500"
                                    placeholder="Nama sesuai rekening" value="{{ auth()->user()->name }}" required>
                            </div>

                            <button type="submit"
                                class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-pink-500/30 transition">
                                🏦 Ajukan Penarikan
                            </button>
                        </form>
                    @else
                        <div class="text-center py-8">
                            <div class="text-4xl mb-3">💸</div>
                            <p class="text-gray-500 text-sm">Saldo minimal Rp 50.000 untuk menarik</p>
                            <p class="text-xs text-gray-400 mt-1">Saldo tersedia: Rp
                                {{ number_format($balance->available_balance, 0, ',', '.') }}
                            </p>
                        </div>
                    @endif
                </div>

                {{-- Recent Transactions --}}
                <div class="bg-white rounded-2xl border-2 border-gray-200 p-5">
                    <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
                        <span>📋</span> Transaksi Terakhir
                    </h3>

                    @if($recentTransactions->count() > 0)
                        <div class="space-y-3 max-h-80 overflow-y-auto">
                            @foreach($recentTransactions as $trx)
                                <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-xl">
                                    <div
                                        class="w-10 h-10 rounded-lg bg-{{ $trx->status == 'completed' ? 'green' : 'blue' }}-100 flex items-center justify-center text-lg">
                                        {{ $trx->status == 'completed' ? '✅' : '📦' }}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-gray-900 truncate">
                                            {{ $trx->items->first()->product->name ?? 'Produk' }}
                                            @if($trx->items->count() > 1)
                                                <span class="text-xs text-gray-400">+{{ $trx->items->count() - 1 }}</span>
                                            @endif
                                        </p>
                                        <p class="text-[10px] text-gray-500">
                                            {{ $trx->buyer->name ?? 'Pembeli' }} • {{ $trx->created_at->format('d/m/y') }}
                                        </p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-sm font-bold text-{{ $trx->status == 'completed' ? 'green' : 'blue' }}-600">
                                            +Rp {{ number_format($trx->seller_amount ?? $trx->total_amount, 0, ',', '.') }}
                                        </p>
                                        <span
                                            class="text-[10px] px-2 py-0.5 rounded-full 
                                                                    {{ $trx->status == 'completed' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700' }}">
                                            {{ $trx->status == 'completed' ? 'Selesai' : ucfirst(str_replace('_', ' ', $trx->status)) }}
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <a href="{{ route('transactions.history') }}"
                            class="block text-center text-sm text-pink-600 font-bold mt-4 hover:underline">
                            Lihat Semua Transaksi →
                        </a>
                    @else
                        <div class="text-center py-8">
                            <div class="text-4xl mb-3">📦</div>
                            <p class="text-gray-500 text-sm">Belum ada transaksi</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Info Box --}}
            <div class="bg-blue-50 rounded-xl p-4 border border-blue-100">
                <h4 class="font-bold text-blue-800 text-sm mb-2 flex items-center gap-2">
                    <span>ℹ️</span> Informasi Saldo
                </h4>
                <ul class="text-xs text-blue-700 space-y-1">
                    <li>• <strong>Saldo Pending</strong>: Dana dari pesanan yang belum selesai (escrow)</li>
                    <li>• <strong>Saldo Tersedia</strong>: Dana dari pesanan yang sudah dikonfirmasi pembeli & dilepas admin
                    </li>
                    @php
                        $serviceFeeSetting = \App\Models\SystemSetting::where('key', 'service_fee_percent')->first();
                        $serviceFeePercent = $serviceFeeSetting ? (float)$serviceFeeSetting->value : 10;
                    @endphp
                    <li>• Biaya layanan platform: <strong>{{ $serviceFeePercent }}%</strong> dari setiap transaksi</li>
                    <li>• Minimum penarikan: <strong>Rp 50.000</strong></li>
                    <li>• Proses pencairan: 1-3 hari kerja setelah disetujui admin</li>
                </ul>
            </div>
        </div>
    </div>
@endsection