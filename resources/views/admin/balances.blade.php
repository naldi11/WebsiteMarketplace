@extends('layouts.admin')

@section('content')
<div class="pt-0 pb-8">
    <div class="mb-12">
        <h1 class="text-4xl font-black tracking-tighter uppercase italic text-black">Buku Keuangan</h1>
        <p class="text-gray-500 mt-1 font-mono text-xs uppercase tracking-widest text-black">Pantauan Likuiditas Global & Pendapatan Platform</p>
    </div>

    <!-- Platform Earnings Summary - Neo Brutalism -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8 mb-12">
        <div class="bg-white border-[3px] border-black p-8 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] hover:shadow-none transition-all cursor-default">
            <div class="flex flex-col gap-4">
                <div class="w-12 h-12 bg-black text-white flex items-center justify-center border-2 border-black">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-500 mb-1">Pendapatan Platform</p>
                    <p class="text-2xl font-black text-black">Rp {{ number_format($platformEarnings, 0, ',', '.') }}</p>
                    <p class="text-[9px] font-mono text-gray-400 uppercase mt-2 italic">Alokasi Biaya: 10% dari Transaksi</p>
                </div>
            </div>
        </div>

        <div class="bg-white border-[3px] border-black p-8 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] hover:shadow-none transition-all cursor-default">
            <div class="flex flex-col gap-4">
                <div class="w-12 h-12 bg-black text-white flex items-center justify-center border-2 border-black">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-500 mb-1">Entitas Aktif</p>
                    <p class="text-2xl font-black text-black">{{ $sellers->count() }} Penjual</p>
                    <p class="text-[9px] font-mono text-gray-400 uppercase mt-2 italic">Unit Transaksi Terverifikasi</p>
                </div>
            </div>
        </div>

        <div class="bg-white border-[3px] border-black p-8 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] hover:shadow-none transition-all cursor-default">
            <div class="flex flex-col gap-4">
                <div class="w-12 h-12 bg-black text-white flex items-center justify-center border-2 border-black">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-500 mb-1">Efisiensi Rata-rata</p>
                    <p class="text-2xl font-black text-black">Rp {{ number_format($avgEarningsPerTx ?? 0, 0, ',', '.') }}</p>
                    <p class="text-[9px] font-mono text-gray-400 uppercase mt-2 italic">Pendapatan per Transaksi</p>
                </div>
            </div>
        </div>

        <div class="bg-white border-[3px] border-black p-8 shadow-[8px_8px_0px_0px_rgba(0,0,0,1)] hover:shadow-none transition-all cursor-default">
            <div class="flex flex-col gap-4">
                <div class="w-12 h-12 bg-black text-white flex items-center justify-center border-2 border-black">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-[10px] font-black uppercase tracking-widest text-gray-500 mb-1">Total Pencairan</p>
                    <p class="text-2xl font-black text-black">Rp {{ number_format($totalPlatformWithdrawn ?? 0, 0, ',', '.') }}</p>
                    <p class="text-[9px] font-mono text-gray-400 uppercase mt-2 italic">Kumulatif Penarikan Penjual</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Earnings Breakdown -->
    <div class="bg-white border-[3px] border-black p-10 mb-12 neo-brutalism">
        <h2 class="text-xl font-black text-black uppercase italic tracking-tighter mb-8 border-b-2 border-black pb-4">Analisis Bulanan ({{ now()->year }})</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-xs font-black uppercase italic">
                <thead>
                    <tr class="bg-gray-100 border-b-[3px] border-black">
                        @php
                            $bulan = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
                        @endphp
                        @foreach($bulan as $b)
                            <th class="py-6 px-3 text-center border-r-2 border-black last:border-r-0">{{ $b }}</th>
                        @endforeach
                        <th class="py-6 px-5 text-center bg-black text-white">TOTAL</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="font-mono text-sm">
                        @for($i = 1; $i <= 12; $i++)
                            <td class="py-8 px-3 text-center border-r-2 border-black last:border-r-0 {{ isset($monthlyEarnings[$i]) ? 'text-black font-black' : 'text-gray-300' }}">
                                {{ number_format($monthlyEarnings[$i] ?? 0, 0, ',', '.') }}
                            </td>
                        @endfor
                        <td class="py-8 px-5 text-center font-black bg-gray-50 border-l-[3px] border-black">
                            {{ number_format(array_sum($monthlyEarnings), 0, ',', '.') }}
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Seller Balance Table -->
    <div class="bg-white border-[3px] border-black p-10 mb-12 neo-brutalism">
        <h2 class="text-xl font-black text-black uppercase italic tracking-tighter mb-8 border-b-2 border-black pb-4">Ledger Saldo Penjual</h2>
        @if($sellers->isEmpty())
            <div class="text-center py-24 flex flex-col items-center">
                <div class="w-20 h-20 border-[4px] border-black flex items-center justify-center font-black text-4xl mb-6 italic">!</div>
                <p class="text-xs font-black uppercase tracking-widest text-gray-400 italic font-mono">Tidak Ada Penjual Terdaftar</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs font-bold uppercase">
                    <thead>
                        <tr class="bg-gray-100 border-b-[3px] border-black text-black italic">
                            <th class="text-left py-6 px-8 border-r-2 border-black">No.</th>
                            <th class="text-left py-6 px-8 border-r-2 border-black">Identitas</th>
                            <th class="text-right py-6 px-8 border-r-2 border-black">Total Penjualan</th>
                            <th class="text-right py-6 px-8 border-r-2 border-black">Biaya Layanan</th>
                            <th class="text-right py-6 px-8 border-r-2 border-black">Dicairkan</th>
                            <th class="text-right py-6 px-8">Saldo Bersih</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-gray-100">
                        @foreach($sellers as $index => $seller)
                            <tr class="hover:bg-gray-50 transition-all">
                                <td class="py-6 px-8 font-mono text-gray-400 border-r-2 border-black">{{ sprintf('%03d', $index + 1) }}</td>
                                <td class="py-6 px-8 border-r-2 border-black">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 border-2 border-black grayscale">
                                            <img src="{{ $seller->avatar ? Storage::url($seller->avatar) : 'https://ui-avatars.com/api/?name=' . urlencode($seller->name) . '&background=000&color=fff' }}" class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <p class="font-black text-black italic uppercase text-sm leading-tight">{{ $seller->name }}</p>
                                            <p class="text-[9px] font-mono text-gray-400 uppercase tracking-tighter">{{ $seller->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-6 px-8 text-right font-black text-black border-r-2 border-black">
                                    Rp {{ number_format($seller->total_sales ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="py-6 px-8 text-right text-black font-black border-r-2 border-black italic">
                                    - Rp {{ number_format($seller->total_service_fees ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="py-6 px-8 text-right text-gray-500 border-r-2 border-black">
                                    Rp {{ number_format($seller->total_withdrawn ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="py-6 px-8 text-right font-black bg-black text-white italic text-sm">
                                    Rp {{ number_format($seller->seller_earnings ?? 0, 0, ',', '.') }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-black text-white font-black italic uppercase">
                            <td colspan="2" class="py-6 px-8 border-r-2 border-white">TOTAL KESELURUHAN</td>
                            <td class="py-6 px-8 text-right border-r-2 border-white">
                                Rp {{ number_format($sellers->sum('total_sales'), 0, ',', '.') }}
                            </td>
                            <td class="py-6 px-8 text-right border-r-2 border-white">
                                - Rp {{ number_format($sellers->sum('total_service_fees'), 0, ',', '.') }}
                            </td>
                            <td colspan="2" class="py-6 px-8 text-right text-xl tracking-tighter">
                                Rp {{ number_format($sellers->sum('seller_earnings'), 0, ',', '.') }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endif
    </div>

    <!-- Recent Transactions Breakdown -->
    <div class="bg-white border-[3px] border-black p-10 neo-brutalism">
        <h2 class="text-xl font-black text-black uppercase italic tracking-tighter mb-8 border-b-2 border-black pb-4">Log Aliran Transaksi</h2>
        <div class="overflow-x-auto">
            <table class="w-full text-[10px] font-bold uppercase">
                <thead>
                    <tr class="bg-gray-100 border-b-[3px] border-black italic">
                        <th class="text-left py-4 px-6 border-r-2 border-black">ID</th>
                        <th class="text-left py-4 px-6 border-r-2 border-black">Pihak</th>
                        <th class="text-left py-4 px-6 border-r-2 border-black">Waktu</th>
                        <th class="text-right py-4 px-6 border-r-2 border-black">Nilai</th>
                        <th class="text-right py-4 px-6 border-r-2 border-black">Biaya</th>
                        <th class="text-right py-4 px-6">Bersih</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-gray-100">
                    @foreach($latestTransactions as $tx)
                        <tr class="hover:bg-gray-50 transition-all">
                            <td class="py-4 px-6 font-mono text-gray-500 border-r-2 border-black">#{{ $tx->id }}</td>
                            <td class="py-4 px-6 border-r-2 border-black">
                                <div class="font-black text-black italic">{{ $tx->seller->name ?? 'SYSTEM_UNIDENTIFIED' }}</div>
                                <div class="text-[8px] font-mono text-gray-400 uppercase italic">Pembeli: {{ $tx->buyer->name ?? 'TIDAK DIKETAHUI' }}</div>
                            </td>
                            <td class="py-4 px-6 text-gray-600 border-r-2 border-black font-mono">
                                {{ $tx->updated_at->format('Y/m/d H:i:s') }}
                            </td>
                            <td class="py-4 px-6 text-right text-black font-black border-r-2 border-black">
                                Rp {{ number_format($tx->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 text-right text-black italic border-r-2 border-black">
                                - {{ number_format($tx->service_fee, 0, ',', '.') }}
                            </td>
                            <td class="py-4 px-6 text-right font-black text-black italic bg-gray-50">
                                Rp {{ number_format($tx->seller_amount, 0, ',', '.') }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection