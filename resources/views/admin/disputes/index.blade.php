@extends('layouts.admin')

@section('content')
    <div class="pb-4">
        {{-- Header --}}
        <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-6 mb-10">
            <div>
                <h1 class="text-4xl font-black tracking-tighter uppercase italic">Pusat Sengketa</h1>
                <p class="text-gray-500 mt-1 font-mono text-xs uppercase tracking-widest">Manajemen Sengketa & Resolusi
                    Kasus</p>
            </div>
            {{-- Status Tabs --}}
            <div class="flex flex-wrap border-[3px] border-black p-1 gap-1 bg-white">
                @php
                    $tabs = [
                        'open' => ['label' => 'Aktif', 'count' => $counts['open']],
                        'buyer_won' => ['label' => 'Pembeli Menang', 'count' => $counts['buyer_won']],
                        'seller_won' => ['label' => 'Penjual Menang', 'count' => $counts['seller_won']],
                        'refunded' => ['label' => 'Direfund', 'count' => $counts['refunded']],
                        'closed' => ['label' => 'Selesai', 'count' => $counts['closed']],
                        'all' => ['label' => 'Semua', 'count' => $counts['all']],
                    ];
                @endphp
                @foreach($tabs as $key => $tab)
                    <a href="{{ route('admin.disputes.index', ['status' => $key]) }}" class="px-4 py-2 text-xs font-black uppercase transition-all flex items-center gap-1
                                  {{ $status === $key ? 'bg-black text-white' : 'text-black hover:bg-gray-100' }}">
                        {{ $tab['label'] }}
                        @if($tab['count'] > 0)
                            <span
                                class="bg-red-500 text-white text-[9px] rounded-full px-1.5 py-0.5 font-black">{{ $tab['count'] }}</span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="bg-green-50 border-l-4 border-green-500 text-green-800 px-6 py-4 mb-6 font-bold text-sm">
                ✓ {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-50 border-l-4 border-red-500 text-red-800 px-6 py-4 mb-6 font-bold text-sm">
                ✗ {{ session('error') }}
            </div>
        @endif

        {{-- Disputes Table --}}
        <div class="bg-white border-[3px] border-black overflow-hidden">
            <div class="px-8 py-5 border-b-[3px] border-black bg-black text-white flex justify-between items-center">
                <h2 class="font-black text-lg uppercase tracking-tighter">Daftar Dispute</h2>
                <span class="text-xs font-mono opacity-60">{{ $disputes->total() }} kasus ditemukan</span>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50 border-b-2 border-black text-black font-black uppercase">
                        <tr>
                            <th class="px-6 py-4">#ID</th>
                            <th class="px-6 py-4">Transaksi</th>
                            <th class="px-6 py-4">Pembeli</th>
                            <th class="px-6 py-4">Penjual</th>
                            <th class="px-6 py-4">Alasan</th>
                            <th class="px-6 py-4 text-center">Status</th>
                            <th class="px-6 py-4 text-center">Tanggal</th>
                            <th class="px-6 py-4 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($disputes as $dispute)
                            <tr class="hover:bg-gray-50 transition-all">
                                <td class="px-6 py-5 font-black text-sm">#D{{ $dispute->id }}</td>
                                <td class="px-6 py-5">
                                    <div class="font-bold">#TXN-{{ $dispute->transaction_id }}</div>
                                    <div class="text-gray-400 font-mono text-[9px]">
                                        Rp {{ number_format($dispute->transaction->total_amount ?? 0, 0, ',', '.') }}
                                    </div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="font-bold">{{ $dispute->buyer->name ?? '-' }}</div>
                                    <div class="text-gray-400 text-[9px]">{{ $dispute->buyer->email ?? '' }}</div>
                                </td>
                                <td class="px-6 py-5">
                                    <div class="font-bold">{{ $dispute->seller->name ?? '-' }}</div>
                                    <div class="text-gray-400 text-[9px]">{{ $dispute->seller->email ?? '' }}</div>
                                </td>
                                <td class="px-6 py-5 max-w-[150px]">
                                    <div class="truncate font-semibold">{{ $dispute->reason }}</div>
                                </td>
                                <td class="px-6 py-5 text-center">
                                    @php
                                        $badge = match ($dispute->status) {
                                            'open' => ['bg-red-100 text-red-700 border-red-300', 'Terbuka'],
                                            'admin_reviewing' => ['bg-yellow-100 text-yellow-700 border-yellow-300', 'Ditinjau'],
                                            'buyer_won' => ['bg-blue-100 text-blue-700 border-blue-300', 'Pembeli Menang'],
                                            'buyer_shipping_back' => ['bg-purple-100 text-purple-700 border-purple-300', 'Kirim Balik'],
                                            'seller_received_back' => ['bg-indigo-100 text-indigo-700 border-indigo-300', 'Barang Kembali'],
                                            'seller_won' => ['bg-green-100 text-green-700 border-green-300', 'Penjual Menang'],
                                            'refunded' => ['bg-teal-100 text-teal-700 border-teal-300', 'Direfund'],
                                            'closed' => ['bg-gray-100 text-gray-600 border-gray-300', 'Ditutup'],
                                            default => ['bg-gray-100 text-gray-600 border-gray-300', $dispute->status],
                                        };
                                    @endphp
                                    <span class="px-2 py-1 border text-[9px] font-black rounded {{ $badge[0] }}">
                                        {{ $badge[1] }}
                                    </span>
                                </td>
                                <td class="px-6 py-5 text-center text-gray-500 font-mono text-[9px]">
                                    {{ $dispute->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-6 py-5 text-center">
                                    <a href="{{ route('admin.disputes.show', $dispute->id) }}"
                                        class="px-4 py-2 bg-black text-white text-[10px] font-black uppercase hover:bg-gray-800 transition-all inline-block">
                                        Detail →
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8"
                                    class="px-6 py-16 text-center text-gray-400 font-black uppercase italic text-xs">
                                    Tidak ada dispute dengan status ini
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($disputes->hasPages())
                <div class="px-6 py-4 border-t-2 border-gray-100">
                    {{ $disputes->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection