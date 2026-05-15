@extends('layouts.admin')

@section('content')
<div class="pt-0 pb-8">
    <div class="mb-12">
        <h1 class="text-4xl font-black tracking-tighter uppercase italic text-slate-800">Pengaturan Sistem</h1>
        <p class="text-gray-500 mt-1 font-mono text-xs uppercase tracking-widest">Konfigurasi Global & Manajemen Ketentuan</p>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST" id="globalSettingsForm">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            <!-- Navigation Sidebar - Tab Switcher -->
            <div class="lg:col-span-4 space-y-4">
                <div class="gradient-header-slate text-white p-6 rounded-2xl shadow-xl">
                    <h3 class="text-xs font-black uppercase italic tracking-widest mb-2">Daftar Pengaturan</h3>
                    <p class="text-[10px] font-mono text-white/60 uppercase leading-relaxed">Pilih pengaturan untuk mengubah parameter spesifik.</p>
                </div>

                <div class="space-y-3" id="tabContainer">
                    @foreach($settings as $setting)
                        <button type="button"
                            onclick="switchTab('{{ $setting->key }}')"
                            id="btn-{{ $setting->key }}"
                            class="tab-btn w-full text-left p-5 glass-card text-slate-800 font-black uppercase italic text-xs tracking-tighter hover:bg-white/80 transition-all shadow-lg {{ $loop->first ? 'active-tab' : '' }}">
                            <span class="font-mono text-[10px] mr-2 text-gray-400 not-italic">#{{ $loop->iteration }}</span>
                            {{ $setting->description }}
                        </button>
                    @endforeach
                </div>

                <div class="pt-8">
                    <button type="submit"
                        class="w-full px-8 py-6 btn-gradient text-white border border-white/50 text-sm font-black uppercase italic tracking-tighter transition-all shadow-2xl flex items-center justify-center gap-3 rounded-2xl">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path>
                        </svg>
                        Simpan Semua Pengaturan
                    </button>
                    <p class="text-[9px] font-mono text-gray-400 uppercase mt-4 text-center tracking-widest italic">Semua pengaturan akan diperbarui serentak</p>
                </div>
            </div>

            <!-- Content Area - Switchable Matrix Editor -->
            <div class="lg:col-span-8">
                @foreach($settings as $setting)
                    <div id="node-{{ $setting->key }}" class="setting-pane glass-card shadow-2xl overflow-hidden transition-all {{ $loop->first ? '' : 'hidden' }}">
                        <div class="gradient-header-slate px-6 py-5 text-white flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-white/20 backdrop-blur text-white flex items-center justify-center font-black italic rounded-xl border border-white/30">
                                    {{ $loop->iteration }}
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-white uppercase italic tracking-tighter">
                                        {{ $setting->description }}
                                    </h3>
                                    <p class="text-[10px] text-white/60 font-mono mt-1 uppercase tracking-widest">Kunci Pengaturan: {{ $setting->key }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-8">
                            <div class="mb-6 flex justify-between items-end">
                                <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Area Konten</label>
                                <span class="text-[9px] font-mono text-gray-300 italic uppercase">Format: Teks Biasa</span>
                            </div>

                            <textarea name="settings[{{ $setting->key }}]" rows="12" placeholder="Input protocol data..."
                                class="w-full bg-white/40 border border-indigo-100 rounded-xl focus:bg-white focus:border-indigo-400 outline-none transition-all p-8 font-mono text-xs leading-relaxed text-slate-800 shadow-inner min-h-[400px]">{{ $setting->value }}</textarea>

                            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="p-4 border border-indigo-100 bg-white/40 rounded-xl flex items-center gap-4 opacity-70">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-slate-400 to-slate-600 flex items-center justify-center font-black text-xs text-white">!</div>
                                    <span class="text-[9px] font-black uppercase tracking-widest leading-tight text-slate-600">Integritas Data Terverifikasi</span>
                                </div>
                                <div class="p-4 border border-indigo-100 bg-white/60 rounded-xl flex items-center gap-4 shadow-lg">
                                    <svg class="w-6 h-6 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span class="text-[9px] font-black uppercase tracking-widest leading-tight text-slate-600">Siap Diperbarui</span>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    function switchTab(key) {
        // Hide all panes
        document.querySelectorAll('.setting-pane').forEach(pane => {
            pane.classList.add('hidden');
        });

        // Show active pane
        document.getElementById('node-' + key).classList.remove('hidden');

        // Update button styles
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active-tab');
        });
        document.getElementById('btn-' + key).classList.add('active-tab');
    }
</script>
<style>
    .active-tab {
        background: linear-gradient(135deg, #475569, #334155) !important;
        color: white !important;
        box-shadow: 0 4px 15px rgba(71, 85, 105, 0.3) !important;
    }
    .active-tab span {
        color: #94a3b8 !important;
    }
    textarea {
        resize: vertical;
        scrollbar-width: thin;
        scrollbar-color: #818cf8 #f1f5f9;
    }
    textarea::-webkit-scrollbar {
        width: 8px;
    }
    textarea::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    textarea::-webkit-scrollbar-thumb {
        background: #818cf8;
        border-radius: 4px;
    }
</style>
@endpush
@endsection
