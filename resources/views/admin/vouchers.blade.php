@extends('layouts.admin')

@section('content')
    <div class="py-6">
        <h1 class="text-2xl font-bold text-gray-900 tracking-tight mb-6">Kelola Voucher</h1>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-8">
            <h2 class="font-bold text-gray-900 mb-4">Buat Voucher Baru</h2>
            <form action="{{ route('admin.vouchers.store') }}" method="POST" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Kode</label>
                    <input type="text" name="code" class="w-full rounded-lg border-gray-300" placeholder="CTH: SALE50" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Tipe Diskon</label>
                    <select name="discount_type" class="w-full rounded-lg border-gray-300" required>
                        <option value="fixed">Nominal Pasti (Rp)</option>
                        <option value="percent">Persentase (%)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nilai Diskon</label>
                    <input type="number" name="discount_amount" class="w-full rounded-lg border-gray-300" placeholder="50000 / 10" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Maks Diskon (Khusus %)</label>
                    <input type="number" name="max_discount_amount" class="w-full rounded-lg border-gray-300" placeholder="Kosongkan Bila Fixed">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Min. Belanja (Rp)</label>
                    <input type="number" name="min_purchase" class="w-full rounded-lg border-gray-300" value="0">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Limit Kuota</label>
                    <input type="number" name="usage_limit" class="w-full rounded-lg border-gray-300" value="100" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Target User ID</label>
                    <input type="number" name="target_user_id" class="w-full rounded-lg border-gray-300" placeholder="Kosong = Global">
                </div>
                <div class="lg:col-span-1">
                    <button class="w-full bg-indigo-600 text-white px-6 py-2 rounded-xl font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-500/20 transition">Simpan</button>
                </div>
            </form>
        </div>

        <!-- List -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left text-sm text-gray-500">
                <thead class="bg-gray-50 text-gray-600 uppercase text-[11px] font-bold tracking-wider">
                    <tr>
                        <th class="px-6 py-4">Kode</th>
                        <th class="px-6 py-4">Tipe & Diskon</th>
                        <th class="px-6 py-4">Min. Belanja</th>
                        <th class="px-6 py-4">Terpakai</th>
                        <th class="px-6 py-4">Status & Target</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($vouchers as $v)
                        <tr>
                            <td class="px-6 py-4 font-bold text-gray-900">{{ $v->code }}</td>
                            <td class="px-6 py-4">
                                {{ $v->discount_type == 'percent' ? $v->discount_amount . '%' : 'Rp ' . number_format($v->discount_amount, 0, ',', '.') }}
                                @if($v->discount_type == 'percent' && $v->max_discount_amount)
                                    <br><span class="text-xs text-gray-400">Maks Rp {{ number_format($v->max_discount_amount, 0, ',', '.') }}</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">Rp {{ number_format($v->min_purchase, 0, ',', '.') }}</td>
                            <td class="px-6 py-4">{{ $v->usage_count }} / {{ $v->usage_limit }}</td>
                            <td class="px-6 py-4 flex flex-col items-start gap-1">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider {{ $v->is_active ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                                    {{ $v->is_active ? 'Aktif' : 'Non-Aktif' }}
                                </span>
                                @if($v->target_user_id)
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] bg-blue-100 text-blue-700">Khusus User #{{ $v->target_user_id }}</span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[10px] bg-gray-100 text-gray-700">Global</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection