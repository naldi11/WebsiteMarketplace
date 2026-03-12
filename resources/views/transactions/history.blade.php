@extends('layouts.app')

@section('content')
    <section class="padding-large">
        <div class="container">
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">Beranda</a></li>
                    <li class="breadcrumb-item active">Riwayat Transaksi</li>
                </ol>
            </nav>

            <h2 class="display-7 text-uppercase mb-4">Riwayat Transaksi</h2>

            <!-- Tab Navigation -->
            <ul class="nav nav-tabs mb-4" id="historyTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="purchases-tab" data-bs-toggle="tab" data-bs-target="#purchases"
                        type="button" role="tab">
                        Pembelian ({{ $purchases->count() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="sales-tab" data-bs-toggle="tab" data-bs-target="#sales" type="button"
                        role="tab">
                        Penjualan ({{ $sales->count() }})
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="historyTabsContent">
                <!-- Purchases Tab -->
                <div class="tab-pane fade show active" id="purchases" role="tabpanel">
                    @if($purchases->count() > 0)
                        @foreach($purchases as $transaction)
                            <div class="card mb-3">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted">{{ $transaction->created_at->format('d M Y, H:i') }}</small>
                                        <small class="ms-2">#{{ $transaction->id }}</small>
                                    </div>
                                    <span
                                        class="badge 
                                                                                                                                        @if($transaction->status == 'completed') bg-success
                                                                                                                                        @elseif($transaction->status == 'pending') bg-warning text-dark
                                                                                                                                        @elseif($transaction->status == 'processing') bg-info
                                                                                                                                        @elseif($transaction->status == 'shipped') bg-primary
                                                                                                                                        @elseif($transaction->status == 'cancelled') bg-danger
                                                                                                                                        @else bg-secondary @endif">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </div>
                                <div class="card-body">
                                    @foreach($transaction->items as $item)
                                        <div class="d-flex gap-3 mb-3">
                                            <img src="{{ Storage::url($item->product->image) }}" alt="{{ $item->product->name }}"
                                                style="width: 60px; height: 60px; object-fit: cover;">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0">{{ $item->product->name }}</h6>
                                                <small class="text-muted">{{ $item->quantity }}x @ Rp
                                                    {{ number_format($item->price, 0, ',', '.') }}</small>
                                            </div>
                                            <div class="text-end">
                                                <span style="color: var(--primary-color)">Rp
                                                    {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted">Penjual: {{ $transaction->seller->name ?? '-' }}</small>
                                        </div>
                                        <div class="text-end">
                                            <strong>Total: </strong>
                                            <strong style="color: var(--primary-color)">Rp
                                                {{ number_format($transaction->total_amount, 0, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                    <div class="mt-3 pt-3 border-top d-flex gap-2">
                                        @if($transaction->status == 'completed' || $transaction->status == 'received' || $transaction->status == 'cancelled')
                                            @if($transaction->status == 'cancelled')
                                                <button class="btn btn-danger btn-sm w-100" disabled>Pesanan Dibatalkan</button>
                                            @else
                                                <button class="btn btn-secondary btn-sm w-100" disabled>Selesai</button>
                                            @endif
                                        @else
                                            <a href="{{ route('transactions.show', $transaction) }}"
                                                class="btn btn-outline-dark btn-sm flex-grow-1">
                                                Lihat Detail & Bayar
                                            </a>
                                            <form action="{{ route('transactions.cancel', $transaction) }}" method="POST"
                                                onsubmit="return confirm('Apakah anda yakin ingin membatalkan pesanan ini?');">
                                                @csrf
                                                <button type="submit" class="btn btn-danger btn-sm text-nowrap">Batalkan</button>
                                            </form>
                                        @endif
                                        @if($transaction->status == 'shipped')
                                            <a href="{{ route('transactions.show', $transaction) }}"
                                                class="btn btn-success btn-sm ms-2">
                                                Konfirmasi & Upload Bukti
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <p class="text-muted">Belum ada pembelian</p>
                            <a href="{{ route('home') }}" class="btn btn-dark">Mulai Belanja</a>
                        </div>
                    @endif
                </div>

                <!-- Sales Tab -->
                <div class="tab-pane fade" id="sales" role="tabpanel">
                    @if($sales->count() > 0)
                        @foreach($sales as $transaction)
                            <div class="card mb-3">
                                <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                    <div>
                                        <small class="text-muted">{{ $transaction->created_at->format('d M Y, H:i') }}</small>
                                        <small class="ms-2">#{{ $transaction->id }}</small>
                                    </div>
                                    <span
                                        class="badge 
                                                                                                                                        @if($transaction->status == 'completed') bg-success
                                                                                                                                        @elseif($transaction->status == 'pending') bg-warning text-dark
                                                                                                                                        @elseif($transaction->status == 'processing') bg-info
                                                                                                                                        @elseif($transaction->status == 'shipped') bg-primary
                                                                                                                                        @elseif($transaction->status == 'cancelled') bg-danger
                                                                                                                                        @else bg-secondary @endif">
                                        {{ ucfirst($transaction->status) }}
                                    </span>
                                </div>
                                <div class="card-body">
                                    @foreach($transaction->items as $item)
                                        <div class="d-flex gap-3 mb-3">
                                            <img src="{{ Storage::url($item->product->image) }}" alt="{{ $item->product->name }}"
                                                style="width: 60px; height: 60px; object-fit: cover;">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-0">{{ $item->product->name }}</h6>
                                                <small class="text-muted">{{ $item->quantity }}x @ Rp
                                                    {{ number_format($item->price, 0, ',', '.') }}</small>
                                            </div>
                                            <div class="text-end">
                                                <span style="color: var(--primary-color)">Rp
                                                    {{ number_format($item->price * $item->quantity, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    @endforeach
                                    <hr>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <small class="text-muted">Pembeli: {{ $transaction->user->name ?? '-' }}</small>
                                        </div>
                                        <div class="text-end">
                                            <strong>Total: </strong>
                                            <strong style="color: var(--primary-color)">Rp
                                                {{ number_format($transaction->total_amount, 0, ',', '.') }}</strong>
                                        </div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-3 pt-3 border-top">
                                        @if($transaction->status == 'completed' || $transaction->status == 'received')
                                            <button class="btn btn-secondary btn-sm w-100" disabled>Selesai</button>
                                        @else
                                            <a href="{{ route('transactions.show', $transaction) }}"
                                                class="btn btn-outline-dark btn-sm w-100">
                                                Lihat Detail
                                            </a>
                                        @endif
                                    </div>
                                    @if($transaction->status == 'processing')
                                        <div class="mt-3">
                                            <a href="{{ route('transactions.ship', $transaction) }}"
                                                class="btn btn-primary btn-sm w-100">Kirim Pesanan</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    @else
                        <div class="text-center py-5">
                            <p class="text-muted">Belum ada penjualan</p>
                            <a href="{{ route('products.create') }}" class="btn btn-dark">Jual Barang</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection