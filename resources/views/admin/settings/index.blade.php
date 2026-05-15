@extends('layouts.admin')

@section('content')
<div class="pt-0 pb-8">
    <div class="mb-12">
        <h1 class="text-4xl font-black tracking-tighter uppercase italic text-black">Pengaturan Sistem</h1>
        <p class="text-gray-500 mt-1 font-mono text-xs uppercase tracking-widest text-black">Konfigurasi Global & Manajemen Ketentuan</p>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST" id="globalSettingsForm">
        @csrf
        
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
            <!-- Navigation Sidebar - Tab Switcher -->
            <div class="lg:col-span-4 space-y-4">
                <div class="bg-black text-white p-6 border-[3px] border-black shadow-[8px_8px_0px_0px_rgba(0,0,0,1)]">
                    <h3 class="text-xs font-black uppercase italic tracking-widest mb-2">Daftar Pengaturan</h3>
                    <p class="text-[10px] font-mono text-gray-400 uppercase leading-relaxed">Pilih pengaturan untuk mengubah parameter spesifik.</p>
                </div>
                
                <div class="space-y-3" id="tabContainer">
                    @foreach($settings as $setting)
                        <button type="button" 
                            onclick="switchTab('{{ $setting->key }}')"
                            id="btn-{{ $setting->key }}"
                            class="tab-btn w-full text-left p-5 bg-white border-[3px] border-black text-black font-black uppercase italic text-xs tracking-tighter hover:bg-black hover:text-white transition-all shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] active:shadow-none {{ $loop->first ? 'active-tab' : '' }}">
                            <span class="font-mono text-[10px] mr-2 text-gray-400 not-italic">#{{ $loop->iteration }}</span>
                            {{ $setting->description }}
                        </button>
                    @endforeach
                </div>

                <div class="pt-8">
                    <button type="submit"
                        class="w-full px-8 py-6 bg-black text-white border-[4px] border-black text-sm font-black uppercase italic tracking-tighter hover:bg-white hover:text-black transition-all shadow-[10px_10px_0px_0px_rgba(0,0,0,1)] flex items-center justify-center gap-3">
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
                    <div id="node-{{ $setting->key }}" class="setting-pane bg-white border-[3px] border-black shadow-[12px_12px_0px_0px_rgba(0,0,0,1)] overflow-hidden transition-all {{ $loop->first ? '' : 'hidden' }}">
                        <div class="p-6 border-b-[3px] border-black bg-gray-100 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-black text-white flex items-center justify-center font-black italic border-2 border-black">
                                    {{ $loop->iteration }}
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-black uppercase italic tracking-tighter">
                                        {{ $setting->description }}
                                    </h3>
                                    <p class="text-[10px] text-gray-500 font-mono mt-1 uppercase tracking-widest">Kunci Pengaturan: {{ $setting->key }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="p-8">
                            <div class="mb-6 flex justify-between items-end">
                                <label class="text-[10px] font-black uppercase tracking-widest text-gray-400">Area Konten</label>
                                <span class="text-[9px] font-mono text-gray-300 italic uppercase">Format: Teks Biasa</span>
                            </div>
                            
                            <textarea name="settings[{{ $setting->key }}]" rows="12" placeholder="Input protocol data..."
                                class="w-full bg-gray-50 border-[3px] border-black rounded-none focus:bg-white outline-none transition-all p-8 font-mono text-xs leading-relaxed text-black shadow-inner min-h-[400px]">{{ $setting->value }}</textarea>

                            <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="p-4 border-2 border-black bg-gray-50 flex items-center gap-4 italic grayscale opacity-70">
                                    <div class="w-8 h-8 border-2 border-black flex items-center justify-center font-black text-xs not-italic">!</div>
                                    <span class="text-[9px] font-black uppercase tracking-widest leading-tight">Integritas Data Terverifikasi</span>
                                </div>
                                <div class="p-4 border-2 border-black bg-white flex items-center gap-4 shadow-[4px_4px_0px_0px_rgba(0,0,0,1)] grayscale">
                                    <svg class="w-6 h-6 text-black" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <span class="text-[9px] font-black uppercase tracking-widest leading-tight">Siap Diperbarui</span>
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
        background-color: black !important;
        color: white !important;
        box-shadow: none !important;
        transform: translate(4px, 4px);
    }
    .active-tab span {
        color: #4b5563 !important;
    }
    textarea {
        resize: vertical;
        scrollbar-width: thin;
        scrollbar-color: black white;
    }
    textarea::-webkit-scrollbar {
        width: 8px;
    }
    textarea::-webkit-scrollbar-track {
        background: white;
        border-left: 1px solid black;
    }
    textarea::-webkit-scrollbar-thumb {
        background: black;
    }
</style>
@endpush
@endsection