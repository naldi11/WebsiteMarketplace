@extends('layouts.admin')

@section('content')
    <div class="pt-0 pb-2">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8">
            <div>
                <h1 class="text-3xl font-black bg-clip-text text-transparent bg-gradient-to-r from-gray-900 via-indigo-800 to-gray-600 tracking-tight">Manajemen User</h1>
                <p class="text-gray-500 mt-1 font-medium">Kontrol penuh atas seluruh pengguna, pembeli, maupun penjual terdaftar.</p>
            </div>
            
            <!-- Tabs -->
            <div class="flex bg-white rounded-xl shadow-sm border border-gray-100 p-1.5 gap-1">
                @php 
                    $tabs = [
                        'all' => 'Semua User', 
                        'buyers' => 'Pembeli', 
                        'sellers' => 'Penjual', 
                        'suspended' => 'Suspended'
                    ]; 
                @endphp
                @foreach($tabs as $key => $label)
                    <a href="{{ route('admin.users', ['tab' => $key]) }}"
                       class="px-4 py-2 text-sm font-bold rounded-lg transition-all duration-300 transform {{ $tab === $key ? 'bg-gradient-to-r from-indigo-600 to-violet-600 text-white shadow-lg shadow-indigo-200 scale-105' : 'text-gray-500 hover:text-gray-900 hover:bg-gray-100' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        @if(session('success'))
            <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl relative shadow-sm" role="alert">
                 <span class="block sm:inline font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl relative shadow-sm" role="alert">
                 <span class="block sm:inline font-medium">{{ session('error') }}</span>
            </div>
        @endif

        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-gray-600">
                    <thead class="bg-gray-50/50 text-gray-500 uppercase text-[11px] font-bold tracking-wider">
                        <tr>
                            <th class="px-6 py-4">Pengguna</th>
                            <th class="px-6 py-4">Kontak</th>
                            <th class="px-6 py-4">Status & Peran</th>
                            <th class="px-6 py-4">Tanggal Gabung</th>
                            <th class="px-6 py-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @forelse($users as $user)
                        <tr class="hover:bg-gray-50/80 transition duration-150 {{ $user->is_suspended ? 'bg-red-50/30' : '' }}">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    @if($user->avatar)
                                        <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-full object-cover shadow-sm">
                                    @else
                                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-100 to-violet-100 text-indigo-600 flex items-center justify-center font-bold shadow-sm">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="font-bold text-gray-900">{{ $user->name }}</div>
                                        @if($user->isSeller())
                                            <div class="text-xs text-indigo-600 font-medium">🛒 {{ $user->shop_name ?? 'Toko Penjual' }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-800">{{ $user->email }}</div>
                                <div class="text-xs text-gray-500">{{ $user->phone }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex px-2.5 py-1 rounded-full text-[11px] font-bold shadow-sm {{ $user->is_suspended ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-emerald-100 text-emerald-700 border border-emerald-200' }}">
                                    {{ $user->is_suspended ? 'Disuspend' : 'Aktif' }}
                                </span>
                                <span class="ml-1 inline-flex px-2.5 py-1 rounded-full text-[11px] font-bold shadow-sm bg-gray-100 text-gray-600 border border-gray-200">
                                    {{ $user->isSeller() ? 'Penjual' : 'Pembeli' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-medium text-gray-500 whitespace-nowrap">
                                {{ $user->created_at->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <form action="{{ route('admin.users.suspend', $user->id) }}" method="POST" class="inline-block">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center px-3 py-1.5 shadow-sm text-xs font-bold rounded-lg transition-colors {{ $user->is_suspended ? 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200' : 'bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200' }}"
                                            onclick="return confirm('Yakinkah Anda ingin {{ $user->is_suspended ? 'mengaktifkan kembali' : 'menangguhkan (suspend)' }} {{ $user->name }}?')">
                                        {{ $user->is_suspended ? 'Unsuspend' : 'Suspend' }}
                                    </button>
                                </form>

                                <form action="{{ route('admin.users.delete', $user->id) }}" method="POST" class="inline-block ml-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="inline-flex items-center px-3 py-1.5 shadow-sm text-xs font-bold rounded-lg bg-red-50 text-red-700 hover:bg-red-100 border border-red-200 transition-colors"
                                            onclick="return confirm('PERINGATAN KRITIKAL: Anda yakin ingin MENGHAPUS PERMANEN user {{ $user->name }}? Seluruh data yang terkait dengannya akan hilang (cascade).')">
                                        Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                    <span class="font-medium text-gray-500">Tidak ada user ditemukan pada tab ini.</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($users->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
