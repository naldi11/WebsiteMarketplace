@if($products->count() > 0)
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-3 gap-6">
        <!-- Products Loop -->
        @foreach($products as $product)
            <div
                class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-shadow duration-300 border border-gray-100 overflow-hidden flex flex-col h-full group">
                <div class="relative aspect-video sm:aspect-square overflow-hidden bg-gray-100">
                    <img src="{{ Storage::url($product->image) }}" alt="{{ $product->name }}"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                    <div class="absolute top-3 right-3">
                        <div class="bg-white/90 backdrop-blur text-xs font-semibold px-2 py-1 rounded-lg shadow-sm">
                            {{ $product->category->name ?? 'Umum' }}
                        </div>
                    </div>
                </div>
                <div class="p-5 flex flex-col flex-1">
                    <div class="flex justify-between items-start mb-2">
                        <h3 class="text-lg font-bold text-gray-900 line-clamp-1 group-hover:text-pink-600 transition">
                            {{ $product->name }}
                        </h3>
                    </div>
                    <p class="text-gray-500 text-sm mb-4 line-clamp-2 flex-1">{{ $product->description }}</p>

                    <div class="flex items-center justify-between mt-auto pt-4 border-t border-gray-50">
                        <div>
                            <p class="text-xs text-gray-400">Harga</p>
                            <p class="text-pink-600 font-bold text-lg">Rp
                                {{ number_format($product->price, 0, ',', '.') }}
                            </p>
                        </div>
                        <a href="{{ route('products.show', $product) }}"
                            class="inline-flex items-center justify-center p-2 rounded-full bg-gray-50 text-pink-600 hover:bg-pink-50 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-8">
        {{ $products->links() }}
    </div>
@else
    <div class="text-center py-20 bg-white rounded-3xl border border-dashed border-gray-300">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada barang ditemukan</h3>
        <p class="mt-1 text-sm text-gray-500">Coba kata kunci lain atau reset filter.</p>
    </div>
@endif