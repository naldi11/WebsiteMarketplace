@extends('layouts.admin')

@section('title', 'Conflict_Report Matrix')

@section('content')
<div class="pt-0 pb-8">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-12">
        <div>
            <h1 class="text-4xl font-black tracking-tighter uppercase italic text-black">Conflict_Report Matrix</h1>
            <p class="text-gray-500 mt-1 font-mono text-xs uppercase tracking-widest text-black">Transaction Dispute & Issue Logging System</p>
        </div>
    </div>

    {{-- Tabs - Neo Brutalism --}}
    <div class="mb-12">
        <nav class="flex flex-wrap gap-4">
            <a href="{{ route('admin.reports', ['status' => 'pending']) }}" 
                class="px-6 py-3 border-[3px] border-black text-xs font-black uppercase italic tracking-widest transition-all {{ $status === 'pending' ? 'bg-black text-white shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]' : 'bg-white text-black hover:bg-gray-50' }}">
                Pending_Queue [{{ $counts['pending'] }}]
            </a>
            <a href="{{ route('admin.reports', ['status' => 'resolved']) }}" 
                class="px-6 py-3 border-[3px] border-black text-xs font-black uppercase italic tracking-widest transition-all {{ $status === 'resolved' ? 'bg-black text-white shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]' : 'bg-white text-black hover:bg-gray-50' }}">
                Resolved_Nodes [{{ $counts['resolved'] }}]
            </a>
            <a href="{{ route('admin.reports', ['status' => 'dismissed']) }}" 
                class="px-6 py-3 border-[3px] border-black text-xs font-black uppercase italic tracking-widest transition-all {{ $status === 'dismissed' ? 'bg-black text-white shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]' : 'bg-white text-black hover:bg-gray-50' }}">
                Dismissed_Log [{{ $counts['dismissed'] }}]
            </a>
            <a href="{{ route('admin.reports', ['status' => 'all']) }}" 
                class="px-6 py-3 border-[3px] border-black text-xs font-black uppercase italic tracking-widest transition-all {{ $status === 'all' ? 'bg-black text-white shadow-[6px_6px_0px_0px_rgba(0,0,0,1)]' : 'bg-white text-black hover:bg-gray-50' }}">
                Total_Matrix
            </a>
        </nav>
    </div>

    <!-- Report Matrix - Neo Brutalism -->
    <div class="bg-white border-[3px] border-black neo-brutalism overflow-hidden mb-12">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-gray-100 border-b-[3px] border-black text-black font-black uppercase italic">
                    <tr>
                        <th class="px-8 py-6">Timestamp_Log</th>
                        <th class="px-8 py-6">Reporter_Node</th>
                        <th class="px-8 py-6">Transaction_ID</th>
                        <th class="px-8 py-6">Incident_Vector</th>
                        <th class="px-8 py-6">Conflict_Status</th>
                        <th class="px-8 py-6 text-right">Protocol_Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y-2 divide-gray-100 font-bold">
                    @forelse($reports as $report)
                    <tr class="hover:bg-gray-50 transition-all">
                        <td class="px-8 py-6 whitespace-nowrap font-mono text-[10px] text-gray-500">
                            {{ $report->created_at->format('Y-m-d_H:i:s') }}
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            <div class="font-black text-black uppercase italic tracking-tighter">{{ $report->user->name }}</div>
                            <div class="text-[9px] text-gray-400 font-mono tracking-widest uppercase mt-1">{{ str_replace('_', '.', $report->type) }}</div>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            <a href="{{ route('admin.transactions.show', $report->transaction_id) }}" class="px-3 py-1 border-2 border-black text-[10px] font-black bg-black text-white hover:bg-white hover:text-black transition-all">
                                TXN_{{ $report->transaction_id }}
                            </a>
                        </td>
                        <td class="px-8 py-6">
                            <div class="font-black text-black uppercase italic tracking-tighter text-sm leading-none">{{ $report->reason }}</div>
                            <div class="text-[10px] text-gray-400 truncate max-w-[200px] mt-1 font-mono uppercase">{{ $report->description }}</div>
                        </td>
                        <td class="px-8 py-6 whitespace-nowrap">
                            <span class="px-3 py-1 border-2 border-black text-[9px] font-black uppercase tracking-widest {{ $report->status === 'pending' ? 'bg-white text-black' : ($report->status === 'resolved' ? 'bg-black text-white shadow-[3px_3px_0px_0px_rgba(0,0,0,1)]' : 'bg-gray-100 text-gray-400') }}">
                                {{ strtoupper($report->status) }}
                            </span>
                        </td>
                        <td class="px-8 py-6 text-right whitespace-nowrap">
                            <a href="{{ route('admin.reports.show', $report) }}" class="px-4 py-2 bg-white text-black border-2 border-black text-[10px] font-black uppercase italic hover:bg-black hover:text-white transition-all shadow-[4px_4px_0px_0px_rgba(0,0,0,1)]">Inspect</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-8 py-24 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 border-[3px] border-black flex items-center justify-center font-black text-2xl mb-4 italic">!</div>
                                <p class="text-xs font-black uppercase tracking-widest text-gray-400 italic">No Conflict Records Detected</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="mt-8 font-black">
        {{ $reports->links() }}
    </div>
</div>
@endsection
