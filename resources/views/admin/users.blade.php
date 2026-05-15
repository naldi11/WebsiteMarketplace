@extends('layouts.admin')

@section('content')
    <div class="pt-0 pb-2">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
            <div>
                <h1 class="text-4xl font-black tracking-tighter uppercase italic">Database Pengguna</h1>
                <p class="text-gray-500 mt-1 font-mono text-xs uppercase tracking-widest text-black">Manajemen Identitas & Kontrol Akses</p>
            </div>
            
            <!-- Tabs - Neo Brutalism -->
            <div class="flex border-[3px] border-black p-1 gap-1 bg-white neo-brutalism">
                @php 
                    $tabs = [
                        'all' => 'Semua Pengguna', 
                        'buyers' => 'Pembeli', 
                        'sellers' => 'Penjual', 
                        'suspended' => 'Diblokir'
                    ]; 
                @endphp
                @foreach($tabs as $key => $label)
                    <a href="{{ route('admin.users', ['tab' => $key]) }}"
                       class="px-5 py-2 text-xs font-black uppercase transition-all {{ $tab === $key ? 'bg-black text-white' : 'text-black hover:bg-gray-100' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        @if(session('success'))
            <div class="mb-8 border-[3px] border-black bg-white p-6 neo-brutalism flex items-center gap-4">
                 <div class="w-8 h-8 bg-black text-white flex items-center justify-center font-black">✓</div>
                 <span class="text-sm font-black uppercase tracking-tight">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-8 border-[3px] border-black bg-black p-6 neo-brutalism flex items-center gap-4 text-white">
                 <div class="w-8 h-8 bg-white text-black flex items-center justify-center font-black">!</div>
                 <span class="text-sm font-black uppercase tracking-tight">{{ session('error') }}</span>
            </div>
        @endif

        <div class="bg-white border-[3px] border-black neo-brutalism overflow-hidden mb-12">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-100 border-b-[3px] border-black text-black font-black uppercase italic">
                        <tr>
                            <th class="px-8 py-6">Identitas</th>
                            <th class="px-8 py-6">Kontak</th>
                            <th class="px-8 py-6">Status & Peran</th>
                            <th class="px-8 py-6">Bergabung</th>
                            <th class="px-8 py-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y-2 divide-gray-100 font-bold">
                        @forelse($users as $user)
                        <tr class="hover:bg-gray-50 transition-all {{ $user->is_suspended ? 'bg-gray-50 opacity-60' : '' }}">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 border-[3px] border-black overflow-hidden bg-white">
                                        @if($user->avatar)
                                            <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}" class="w-full h-full object-cover grayscale">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center font-black text-xl italic uppercase">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-black uppercase text-sm tracking-tight text-black">{{ $user->name }}</div>
                                        @if($user->isSeller())
                                            <div class="text-[9px] font-mono text-gray-500 uppercase flex items-center gap-1 mt-1">
                                                <span class="w-2 h-2 bg-black"></span> Shop: {{ $user->shop_name ?? 'NOT_SET' }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="uppercase tracking-tighter">{{ $user->email }}</div>
                                <div class="text-[10px] font-mono text-gray-400 mt-1">{{ $user->phone }}</div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex flex-wrap gap-2">
                                    <span class="px-3 py-1 border-2 border-black text-[9px] font-black uppercase {{ $user->is_suspended ? 'bg-black text-white' : 'bg-white text-black' }}">
                                        {{ $user->is_suspended ? 'TERMINATED' : 'ACTIVE' }}
                                    </span>
                                    <span class="px-3 py-1 border-2 border-black bg-gray-100 text-[9px] font-black uppercase">
                                        {{ $user->isSeller() ? 'SELLER' : 'BUYER' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-8 py-6 font-mono text-[10px] text-gray-400 uppercase">
                                {{ $user->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex justify-end gap-3">
                                    <form action="{{ route('admin.users.suspend', $user->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" 
                                                class="px-4 py-2 border-2 border-black text-[10px] font-black uppercase transition-all {{ $user->is_suspended ? 'bg-white text-black hover:bg-gray-100' : 'bg-white text-black hover:bg-black hover:text-white' }}"
                                                onclick="return confirm('Execute protocol: {{ $user->is_suspended ? 'ACTIVATE' : 'SUSPEND' }} user {{ $user->name }}?')">
                                            {{ $user->is_suspended ? 'Reactivate' : 'Suspend' }}
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.users.delete', $user->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" 
                                                class="px-4 py-2 bg-black text-white border-2 border-black text-[10px] font-black uppercase hover:bg-white hover:text-black transition-all"
                                                onclick="return confirm('PERINGATAN KRITIS: Hapus pengguna {{ $user->name }} dari sistem? Tindakan ini tidak bisa dibatalkan.')">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="px-8 py-24 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 border-[3px] border-black flex items-center justify-center font-black text-2xl mb-4 italic">?</div>
                                    <span class="text-xs font-black uppercase tracking-widest text-gray-400 italic">Tidak Ada Data Pengguna</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            @if($users->hasPages())
                <div class="px-8 py-6 border-t-[3px] border-black bg-gray-50">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
