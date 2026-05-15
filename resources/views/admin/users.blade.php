@extends('layouts.admin')

@section('content')
    <div class="pt-0 pb-2">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
            <div>
                <h1 class="text-4xl font-black tracking-tighter uppercase italic">Database Pengguna</h1>
                <p class="text-slate-500 mt-1 font-mono text-xs uppercase tracking-widest">Manajemen Identitas & Kontrol Akses</p>
            </div>

            <!-- Tabs -->
            <div class="flex border border-indigo-100 rounded-xl p-1 gap-1 bg-white/40">
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
                       class="px-5 py-2 text-xs font-black uppercase transition-all rounded-lg {{ $tab === $key ? 'gradient-header-blue text-white' : 'text-slate-600 hover:bg-indigo-50/50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        @if(session('success'))
            <div class="mb-8 glass-card p-6 flex items-center gap-4 border-l-4 border-green-400">
                 <div class="w-8 h-8 bg-green-500 text-white flex items-center justify-center font-black rounded-lg">✓</div>
                 <span class="text-sm font-black uppercase tracking-tight text-slate-700">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="mb-8 glass-card p-6 flex items-center gap-4 border-l-4 border-red-400">
                 <div class="w-8 h-8 bg-red-500 text-white flex items-center justify-center font-black rounded-lg">!</div>
                 <span class="text-sm font-black uppercase tracking-tight text-red-700">{{ session('error') }}</span>
            </div>
        @endif

        <div class="glass-card overflow-hidden mb-12">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="glass-table-header text-slate-600 font-black uppercase">
                        <tr>
                            <th class="px-8 py-6">Identitas</th>
                            <th class="px-8 py-6">Kontak</th>
                            <th class="px-8 py-6">Status & Peran</th>
                            <th class="px-8 py-6">Bergabung</th>
                            <th class="px-8 py-6 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-indigo-50 font-bold">
                        @forelse($users as $user)
                        <tr class="hover:bg-indigo-50/50 transition-all {{ $user->is_suspended ? 'bg-white/40 opacity-60' : '' }}">
                            <td class="px-8 py-6">
                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 border border-indigo-100 rounded-xl overflow-hidden bg-white">
                                        @if($user->avatar)
                                            <img src="{{ Storage::url($user->avatar) }}" alt="{{ $user->name }}" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center font-black text-xl italic uppercase text-slate-600 bg-indigo-50">
                                                {{ substr($user->name, 0, 1) }}
                                            </div>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-black uppercase text-sm tracking-tight text-slate-800">{{ $user->name }}</div>
                                        @if($user->isSeller())
                                            <div class="text-[9px] font-mono text-slate-500 uppercase flex items-center gap-1 mt-1">
                                                <span class="w-2 h-2 bg-indigo-400 rounded-full"></span> Shop: {{ $user->shop_name ?? 'NOT_SET' }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="uppercase tracking-tighter text-slate-700">{{ $user->email }}</div>
                                <div class="text-[10px] font-mono text-slate-400 mt-1">{{ $user->phone }}</div>
                            </td>
                            <td class="px-8 py-6">
                                <div class="flex flex-wrap gap-2">
                                    @if($user->is_suspended)
                                        <span class="badge-danger">DITANGGUHKAN</span>
                                    @else
                                        <span class="badge-active">AKTIF</span>
                                    @endif
                                    @if($user->isSeller())
                                        <span class="badge-info">PENJUAL</span>
                                    @else
                                        <span class="badge-inactive">PEMBELI</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-8 py-6 font-mono text-[10px] text-slate-400 uppercase">
                                {{ $user->created_at->format('d/m/Y') }}
                            </td>
                            <td class="px-8 py-6 text-right">
                                <div class="flex justify-end gap-3">
                                    <form action="{{ route('admin.users.suspend', $user->id) }}" method="POST">
                                        @csrf
                                        <button type="submit"
                                                class="px-4 py-2 text-[10px] font-black uppercase transition-all rounded-xl {{ $user->is_suspended ? 'bg-gradient-to-r from-green-500 to-emerald-600 text-white shadow-lg shadow-green-200' : 'bg-gradient-to-r from-orange-500 to-amber-600 text-white shadow-lg shadow-orange-200' }}"
                                                onclick="return confirm('{{ $user->is_suspended ? 'Aktifkan kembali' : 'Tangguhkan' }} pengguna {{ $user->name }}?')">
                                            {{ $user->is_suspended ? 'Aktifkan' : 'Tangguhkan' }}
                                        </button>
                                    </form>

                                    <form action="{{ route('admin.users.delete', $user->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="btn-danger text-white rounded-xl px-4 py-2 text-[10px] font-black uppercase"
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
                                    <div class="w-16 h-16 border border-indigo-100 rounded-xl flex items-center justify-center font-black text-2xl mb-4 italic text-slate-400">?</div>
                                    <span class="text-xs font-black uppercase tracking-widest text-slate-400 italic">Tidak Ada Data Pengguna</span>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($users->hasPages())
                <div class="px-8 py-6 border-t border-indigo-100 bg-white/40">
                    {{ $users->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
