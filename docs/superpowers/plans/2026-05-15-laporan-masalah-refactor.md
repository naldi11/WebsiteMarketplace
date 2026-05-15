# Laporan Masalah Refactor + Android Refund Progress

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Hapus sistem Report, rename Dispute → "Laporan Masalah", fix admin chat duplikat, fix concurrency refund, dan tambah menu progress refund di Android.

**Architecture:** Lima fase independen. Fase 1–4 di Laravel (backend + admin panel). Fase 5 di Android. Fase 1–4 tidak bergantung satu sama lain setelah Fase 1 selesai (Report dihapus). Fase 5 bergantung pada API yang sudah ada di backend.

**Tech Stack:** Laravel 12 (PHP 8.4), Blade, MySQL, Android Java (com.octania.marketplace), Retrofit 2, Material Design 3.

---

## File Map

### Laravel — Dihapus
- `app/Models/Report.php`
- `app/Http/Controllers/Api/ReportControllerApi.php`
- `app/Http/Controllers/ReportController.php`
- `resources/views/admin/reports/index.blade.php`
- `resources/views/admin/reports/show.blade.php`
- `database/migrations/2026_03_12_181619_create_reports_table.php`

### Laravel — Dimodifikasi
- `routes/api.php` — hapus route /reports
- `routes/web.php` — hapus route reports, rename laporan-masalah nav
- `app/Http/Controllers/AdminController.php` — hapus methods reports(), showReport(), updateReport()
- `resources/views/layouts/admin.blade.php` — rename nav "Sengketa" → "Laporan Masalah", hapus nav "Laporan Masalah" lama
- `resources/views/admin/disputes/index.blade.php` — rename label
- `resources/views/admin/disputes/show.blade.php` — rename label + embed chat
- `resources/views/admin/disputes/chat.blade.php` — rename label
- `app/Http/Controllers/AdminDisputeController.php` — fix chat duplikat + embed chat di show page
- `app/Http/Controllers/Api/DisputeControllerApi.php` — fix concurrency refund
- `app/Http/Controllers/ReviewController.php` — update pesan error

### Android — Dibuat baru
- `app/src/main/java/com/octania/marketplace/data/model/Dispute.java`
- `app/src/main/java/com/octania/marketplace/ui/dispute/DisputeDetailActivity.java`
- `app/src/main/res/layout/activity_dispute_detail.xml`

### Android — Dimodifikasi
- `app/src/main/java/com/octania/marketplace/data/remote/ApiService.java` — verifikasi endpoint dispute sudah ada
- `app/src/main/java/com/octania/marketplace/ui/transaction/TransactionAdapter.java` — tambah tombol "Laporan Masalah" untuk transaksi yang bermasalah
- `app/src/main/AndroidManifest.xml` — daftarkan DisputeDetailActivity

---

## Task 1: Hapus Seluruh Sistem Report (Laravel)

**Files:**
- Delete: `app/Models/Report.php`
- Delete: `app/Http/Controllers/Api/ReportControllerApi.php`
- Delete: `app/Http/Controllers/ReportController.php`
- Delete: `resources/views/admin/reports/index.blade.php`
- Delete: `resources/views/admin/reports/show.blade.php`
- Delete: `database/migrations/2026_03_12_181619_create_reports_table.php`
- Modify: `routes/api.php`
- Modify: `routes/web.php`
- Modify: `app/Http/Controllers/AdminController.php`

- [ ] **Step 1: Hapus file Report**
```bash
rm app/Models/Report.php
rm app/Http/Controllers/Api/ReportControllerApi.php
rm app/Http/Controllers/ReportController.php
rm resources/views/admin/reports/index.blade.php
rm resources/views/admin/reports/show.blade.php
rm database/migrations/2026_03_12_181619_create_reports_table.php
```

- [ ] **Step 2: Hapus route Report di routes/api.php**

Hapus baris ini (ada di dalam `Route::middleware('auth:sanctum')` group):
```php
// Higher level admin tasks (Report submission)
Route::post('/reports', [\App\Http\Controllers\Api\ReportControllerApi::class, 'store']);
```

- [ ] **Step 3: Hapus route Report di routes/web.php**

Hapus baris-baris ini:
```php
Route::post('/reports', [App\Http\Controllers\ReportController::class, 'store'])->name('reports.store');
```

Dan di dalam admin group, hapus:
```php
// Reports
Route::get('/reports', [App\Http\Controllers\AdminController::class, 'reports'])->name('admin.reports');
Route::get('/reports/{report}', [App\Http\Controllers\AdminController::class, 'showReport'])->name('admin.reports.show');
Route::post('/reports/{report}/update', [App\Http\Controllers\AdminController::class, 'updateReport'])->name('admin.reports.update');
```

- [ ] **Step 4: Hapus 3 method Report dari AdminController.php**

Hapus seluruh method `reports()`, `showReport()`, dan `updateReport()` dari `app/Http/Controllers/AdminController.php`. Cari dan hapus blok kode yang dimulai dengan:
```php
public function reports(Request $request)
```
```php
public function showReport(\App\Models\Report $report)
```
```php
public function updateReport(Request $request, \App\Models\Report $report)
```

- [ ] **Step 5: Verifikasi tidak ada referensi Report tersisa**
```bash
grep -r "Report" app/Http/Controllers/ --include="*.php" -l
grep -r "ReportController" routes/ --include="*.php"
grep -r "admin.reports" resources/ --include="*.php" -l
```
Semua output harus kosong atau hanya pada file yang bukan terkait Report.

- [ ] **Step 6: Hapus nav "Laporan Masalah" lama dari layouts/admin.blade.php**

Di `resources/views/layouts/admin.blade.php`, hapus seluruh baris nav item yang mengarah ke `admin.reports`:
```php
['route'=>'admin.reports', 'label'=>'Laporan Masalah', 'color'=>'reports', 'icon'=>'M3 6l3 1m0 0l-3 9...'],
```

- [ ] **Step 7: Commit**
```bash
git add -A
git commit -m "feat: hapus sistem Report sepenuhnya"
```

---

## Task 2: Rename "Sengketa" → "Laporan Masalah" (Laravel)

**Files:**
- Modify: `resources/views/layouts/admin.blade.php`
- Modify: `resources/views/admin/disputes/index.blade.php`
- Modify: `resources/views/admin/disputes/show.blade.php`
- Modify: `resources/views/admin/disputes/chat.blade.php`
- Modify: `app/Http/Controllers/ReviewController.php`

- [ ] **Step 1: Update nav item di layouts/admin.blade.php**

Cari baris nav item Sengketa dan ganti labelnya:
```php
// Sebelum:
['route'=>'admin.disputes.index','label'=>'Sengketa', ...],

// Sesudah:
['route'=>'admin.disputes.index','label'=>'Laporan Masalah', ...],
```

- [ ] **Step 2: Update disputes/index.blade.php**

```php
// Sebelum:
<h1 ...>Pusat Sengketa</h1>
<p ...>Manajemen Sengketa & Resolusi</p>

// Sesudah:
<h1 ...>Laporan Masalah</h1>
<p ...>Manajemen Laporan & Resolusi Sengketa</p>
```

- [ ] **Step 3: Update disputes/show.blade.php — ganti semua "Sengketa"**

Lakukan replace berikut:
- `"👁️ Pantau Chat Sengketa"` → `"💬 Lihat Chat Laporan"`
- `"Akses rating telah diblokir otomatis karena penjual memenangkan sengketa."` → `"Akses rating diblokir karena penjual memenangkan laporan masalah ini."`
- `"Pantau Chat Sengketa"` → `"Lihat Chat Laporan"`
- Komentar `// Pihak yang bersengketa` → `// Pihak yang terlibat`

- [ ] **Step 4: Update disputes/chat.blade.php — ganti semua "Sengketa"**

Replace:
- `"God View Chat — Sengketa #D{{ $dispute->id }}"` → `"Percakapan Laporan Masalah #D{{ $dispute->id }}"`
- `"Percakapan Sengketa"` → `"Percakapan"`
- `"Percakapan sengketa belum dimulai"` → `"Percakapan belum dimulai"`
- `"Info Sengketa"` → `"Info Laporan"`
- `"ID Sengketa"` → `"ID Laporan"`
- `"Pihak Bersengketa"` → `"Pihak Terlibat"`
- `"Alasan Sengketa"` → `"Alasan"`
- Semua komentar `// ... sengketa ...` → update ke "laporan"

- [ ] **Step 5: Update ReviewController.php**

```php
// Sebelum:
'Anda tidak dapat memberikan rating pada transaksi ini karena sengketa dimenangkan oleh penjual.'

// Sesudah:
'Anda tidak dapat memberikan rating pada transaksi ini karena laporan masalah dimenangkan oleh penjual.'
```

- [ ] **Step 6: Verifikasi**
```bash
grep -r "Sengketa\|sengketa" resources/views/admin/ --include="*.php" -n
```
Output hanya boleh ada pada komentar (jika ada), tidak boleh di teks yang tampil ke user.

- [ ] **Step 7: Commit**
```bash
git add -A
git commit -m "feat: rename Sengketa menjadi Laporan Masalah di admin panel"
```

---

## Task 3: Fix Admin Chat — Embed ke Dispute Show Page + Fix Duplikat (Laravel)

**Files:**
- Modify: `app/Http/Controllers/AdminDisputeController.php`
- Modify: `resources/views/admin/disputes/show.blade.php`

**Masalah:** Admin chat ada di halaman terpisah `/admin/disputes/{id}/chat`. Seharusnya chat terintegrasi langsung di halaman show dispute. Selain itu, `sendAdminChat()` membuat 2 Message record untuk 1 pesan admin.

**Fix duplikat:** Kirim hanya 1 pesan dengan `receiver_id = buyer_id`. Query di `viewChat()` sudah mengambil semua pesan yang melibatkan admin, buyer, atau seller — jadi pesan admin ke buyer otomatis muncul di chat view. Untuk seller melihat pesan admin di mobile, kirim juga ke seller tapi JANGAN tampilkan duplikat di admin view (filter `DISTINCT sender_id, message, created_at`).

- [ ] **Step 1: Fix sendAdminChat() di AdminDisputeController.php**

Ganti method `sendAdminChat()` (sekitar baris 284-314):
```php
public function sendAdminChat(Request $request, $id)
{
    $admin = auth()->user();
    $request->validate(['message' => 'required|string|max:2000']);
    $dispute = Dispute::with('transaction')->findOrFail($id);

    $text = "👮 [ADMIN] " . $request->message;

    // Kirim ke buyer
    Message::create([
        'sender_id'   => $admin->id,
        'receiver_id' => $dispute->buyer_id,
        'message'     => $text,
        'is_read'     => 0,
    ]);

    // Kirim ke seller (agar mobile seller juga menerima)
    if ($dispute->seller_id !== $dispute->buyer_id) {
        Message::create([
            'sender_id'   => $admin->id,
            'receiver_id' => $dispute->seller_id,
            'message'     => $text,
            'is_read'     => 0,
        ]);
    }

    $dispute->addLog('admin', $admin->id, 'admin_sent_chat',
        'Admin mengirim pesan: ' . substr($request->message, 0, 100)
    );

    return back()->with('success', 'Pesan berhasil dikirim.');
}
```

- [ ] **Step 2: Fix viewChat() agar tidak tampilkan pesan admin duplikat**

Di `viewChat()` (sekitar baris 257-279), update query untuk deduplikasi pesan admin. Ganti query messages dengan:
```php
// Ambil semua pesan percakapan, deduplikasi pesan admin berdasarkan konten+waktu
$messages = \App\Models\Message::where(function ($q) use ($dispute) {
        $q->where(function ($q2) use ($dispute) {
            $q2->where('sender_id', $dispute->buyer_id)
               ->where('receiver_id', $dispute->seller_id);
        })->orWhere(function ($q2) use ($dispute) {
            $q2->where('sender_id', $dispute->seller_id)
               ->where('receiver_id', $dispute->buyer_id);
        })->orWhere(function ($q2) use ($dispute) {
            // Pesan admin ke buyer saja (hindari duplikat dengan seller)
            $q2->where('sender_id', '!=', $dispute->buyer_id)
               ->where('sender_id', '!=', $dispute->seller_id)
               ->where('receiver_id', $dispute->buyer_id);
        })->orWhere(function ($q2) use ($dispute) {
            $q2->where('sender_id', $dispute->buyer_id)
               ->where('receiver_id', '!=', $dispute->buyer_id)
               ->where('receiver_id', '!=', $dispute->seller_id);
        })->orWhere(function ($q2) use ($dispute) {
            $q2->where('sender_id', $dispute->seller_id)
               ->where('receiver_id', '!=', $dispute->buyer_id)
               ->where('receiver_id', '!=', $dispute->seller_id);
        });
    })
    ->with(['sender', 'receiver'])
    ->orderBy('created_at', 'asc')
    ->get();
```

- [ ] **Step 3: Embed chat section ke dalam disputes/show.blade.php**

Di bagian bawah `resources/views/admin/disputes/show.blade.php`, sebelum `@endsection`, tambahkan section chat langsung:

```html
<!-- ── Chat Terintegrasi ── -->
<div class="glass-card overflow-hidden mt-8">
    <div class="gradient-header-red px-6 py-4 text-white flex items-center gap-3">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
        <span class="font-black uppercase tracking-tight">Percakapan Laporan #D{{ $dispute->id }}</span>
    </div>

    <!-- Pesan -->
    <div class="p-6 space-y-3 max-h-96 overflow-y-auto bg-gradient-to-b from-slate-50/60 to-indigo-50/40" id="chatBox">
        @forelse($messages as $msg)
            @php
                $isAdmin   = ($msg->sender_id !== $dispute->buyer_id && $msg->sender_id !== $dispute->seller_id);
                $isBuyer   = ($msg->sender_id === $dispute->buyer_id);
                $isSystem  = str_contains($msg->message, '⚖️') || str_contains($msg->message, '✅') || str_contains($msg->message, '🔍') || str_contains($msg->message, '📦') || str_contains($msg->message, '🎉');
            @endphp
            @if($isSystem)
                <div class="flex justify-center">
                    <span class="text-[10px] bg-white/60 border border-indigo-100 rounded-full px-3 py-1 text-slate-500 font-mono">
                        {{ $msg->message }}
                    </span>
                </div>
            @elseif($isAdmin)
                <div class="flex justify-center">
                    <div class="max-w-xs bg-purple-600/90 text-white px-4 py-2 rounded-2xl text-xs font-medium shadow">
                        {{ $msg->message }}
                        <div class="text-purple-200 text-[9px] mt-1">{{ $msg->created_at->format('H:i') }}</div>
                    </div>
                </div>
            @elseif($isBuyer)
                <div class="flex justify-start gap-2">
                    <div class="w-7 h-7 rounded-full bg-blue-500 flex items-center justify-center text-white text-[10px] font-black flex-shrink-0">
                        {{ substr($dispute->transaction->buyer->name ?? 'B', 0, 1) }}
                    </div>
                    <div class="max-w-xs">
                        <div class="text-[9px] text-slate-400 mb-1">{{ $dispute->transaction->buyer->name ?? 'Pembeli' }}</div>
                        <div class="bg-blue-50/80 border border-blue-100 px-4 py-2 rounded-2xl text-xs text-slate-700 shadow-sm">
                            {{ $msg->message }}
                            <div class="text-slate-400 text-[9px] mt-1">{{ $msg->created_at->format('H:i') }}</div>
                        </div>
                    </div>
                </div>
            @else
                <div class="flex justify-end gap-2">
                    <div class="max-w-xs">
                        <div class="text-[9px] text-slate-400 mb-1 text-right">{{ $dispute->transaction->seller->name ?? 'Penjual' }}</div>
                        <div class="bg-emerald-500/90 text-white px-4 py-2 rounded-2xl text-xs shadow-sm">
                            {{ $msg->message }}
                            <div class="text-emerald-100 text-[9px] mt-1">{{ $msg->created_at->format('H:i') }}</div>
                        </div>
                    </div>
                    <div class="w-7 h-7 rounded-full bg-emerald-500 flex items-center justify-center text-white text-[10px] font-black flex-shrink-0">
                        {{ substr($dispute->transaction->seller->name ?? 'S', 0, 1) }}
                    </div>
                </div>
            @endif
        @empty
            <p class="text-center text-slate-400 text-xs py-8">Percakapan belum dimulai.</p>
        @endforelse
    </div>

    <!-- Form kirim pesan admin -->
    <div class="border-t border-indigo-100 p-4">
        <form action="{{ route('admin.disputes.chat.send', $dispute->id) }}" method="POST" class="flex gap-3">
            @csrf
            <input type="text" name="message" placeholder="Ketik pesan sebagai admin..."
                class="flex-1 px-4 py-2 border border-indigo-100 rounded-xl text-sm focus:ring-2 focus:ring-indigo-300 focus:outline-none bg-white/60">
            <button type="submit" class="btn-gradient text-white px-5 py-2 rounded-xl text-sm font-semibold">
                Kirim
            </button>
        </form>
    </div>
</div>
```

- [ ] **Step 4: Update show() di AdminDisputeController.php untuk sertakan messages**

Pastikan method `show()` pass `$messages` ke view:
```php
public function show($id)
{
    $dispute = Dispute::with([
        'transaction.buyer',
        'transaction.seller',
        'transaction.items.product',
        'logs',
    ])->findOrFail($id);

    // Ambil pesan percakapan (deduplikasi pesan admin)
    $messages = \App\Models\Message::where(function ($q) use ($dispute) {
            $q->where(function ($q2) use ($dispute) {
                $q2->where('sender_id', $dispute->buyer_id)
                   ->where('receiver_id', $dispute->seller_id);
            })->orWhere(function ($q2) use ($dispute) {
                $q2->where('sender_id', $dispute->seller_id)
                   ->where('receiver_id', $dispute->buyer_id);
            })->orWhere(function ($q2) use ($dispute) {
                $q2->where('sender_id', '!=', $dispute->buyer_id)
                   ->where('sender_id', '!=', $dispute->seller_id)
                   ->where('receiver_id', $dispute->buyer_id);
            })->orWhere(function ($q2) use ($dispute) {
                $q2->where('sender_id', $dispute->buyer_id)
                   ->where('receiver_id', '!=', $dispute->buyer_id)
                   ->where('receiver_id', '!=', $dispute->seller_id);
            })->orWhere(function ($q2) use ($dispute) {
                $q2->where('sender_id', $dispute->seller_id)
                   ->where('receiver_id', '!=', $dispute->buyer_id)
                   ->where('receiver_id', '!=', $dispute->seller_id);
            });
        })
        ->with(['sender'])
        ->orderBy('created_at', 'asc')
        ->get();

    return view('admin.disputes.show', compact('dispute', 'messages'));
}
```

- [ ] **Step 5: PHP syntax check**
```bash
php -l app/Http/Controllers/AdminDisputeController.php
php -l resources/views/admin/disputes/show.blade.php
```
Harus: `No syntax errors detected`

- [ ] **Step 6: Commit**
```bash
git add -A
git commit -m "fix: embed admin chat ke dispute show page, fix duplikat pesan admin"
```

---

## Task 4: Fix Concurrency Refund (Laravel)

**Files:**
- Modify: `app/Http/Controllers/Api/DisputeControllerApi.php`

**Masalah:** `processRefundToBuyer()` tidak terlindungi dari concurrent calls. Jika admin dan seller klik bersamaan, bisa terjadi double refund.

- [ ] **Step 1: Tambah DB lock di processRefundToBuyer()**

Ganti seluruh method `processRefundToBuyer()` di `DisputeControllerApi.php`:
```php
private function processRefundToBuyer(Dispute $dispute)
{
    DB::transaction(function () use ($dispute) {
        // Lock baris dispute untuk mencegah concurrent refund
        $dispute = Dispute::lockForUpdate()->findOrFail($dispute->id);

        if ($dispute->status === 'refunded') {
            return; // Already refunded, skip
        }

        $transaction = $dispute->transaction;
        $amount      = $transaction->total_amount;

        $buyerWallet  = \App\Models\Wallet::getOrCreate($dispute->buyer_id);
        $sellerWallet = \App\Models\Wallet::getOrCreate($dispute->seller_id);

        // Lock kedua wallet
        $buyerWallet  = \App\Models\Wallet::lockForUpdate()->findOrFail($buyerWallet->id);
        $sellerWallet = \App\Models\Wallet::lockForUpdate()->findOrFail($sellerWallet->id);

        // Lepas escrow dari buyer
        if ($buyerWallet->pending_balance >= $amount) {
            $buyerWallet->pending_balance -= $amount;
            $buyerWallet->save();
        }

        // Kredit refund ke buyer
        $buyerWallet->credit(
            $amount,
            'refund',
            "Refund Laporan #D{$dispute->id} — Pesanan #{$transaction->id}",
            'dispute',
            $dispute->id
        );

        // Update status
        $dispute->update(['status' => 'refunded']);
        $transaction->update(['status' => 'disputed_refunded', 'buyer_can_rate' => false]);

        $dispute->addLog('system', null, 'refunded',
            "Dana Rp " . number_format($amount, 0, ',', '.') . " dikembalikan ke pembeli"
        );
    });
}
```

- [ ] **Step 2: Tambah lock di releaseFundsToSeller() juga**

Ganti baris awal di `releaseFundsToSeller()` sebelum operasi wallet:
```php
private function releaseFundsToSeller(Dispute $dispute)
{
    DB::transaction(function () use ($dispute) {
        $dispute = Dispute::lockForUpdate()->findOrFail($dispute->id);

        if ($dispute->status === 'closed') {
            return; // Already closed
        }

        $transaction  = $dispute->transaction;
        $grossAmount  = $transaction->seller_amount;
        $feePercent   = (float) optional(\App\Models\SystemSetting::where('key', 'service_fee_percent')->first())->value ?? 10;
        $platformFee  = round($grossAmount * $feePercent / 100);
        $netToSeller  = $grossAmount - $platformFee;

        $buyerWallet  = \App\Models\Wallet::lockForUpdate()->find(\App\Models\Wallet::getOrCreate($dispute->buyer_id)->id);
        $sellerWallet = \App\Models\Wallet::lockForUpdate()->find(\App\Models\Wallet::getOrCreate($dispute->seller_id)->id);

        if ($buyerWallet && $buyerWallet->pending_balance >= $grossAmount) {
            $buyerWallet->pending_balance -= $grossAmount;
            $buyerWallet->save();
        }

        $sellerWallet->credit($netToSeller, 'payout',
            "Penjualan Laporan #D{$dispute->id} (dipotong {$feePercent}% platform)",
            'dispute', $dispute->id
        );

        \App\Models\PlatformEarning::recordEarning(
            $transaction->id, $platformFee, 0,
            "{$feePercent}% service fee dari Laporan #D{$dispute->id}"
        );

        $dispute->update(['status' => 'closed']);
        $transaction->update(['status' => 'completed']);

        $dispute->addLog('system', null, 'seller_won',
            "Dana Rp " . number_format($netToSeller, 0, ',', '.') . " diteruskan ke penjual"
        );
    });
}
```

- [ ] **Step 3: PHP syntax check**
```bash
php -l app/Http/Controllers/Api/DisputeControllerApi.php
```

- [ ] **Step 4: Commit**
```bash
git add app/Http/Controllers/Api/DisputeControllerApi.php
git commit -m "fix: tambah DB lock untuk mencegah double refund concurrent"
```

---

## Task 5: Android — Model Dispute + DisputeDetailActivity

**Files:**
- Create: `app/src/main/java/com/octania/marketplace/data/model/Dispute.java`
- Create: `app/src/main/java/com/octania/marketplace/ui/dispute/DisputeDetailActivity.java`
- Create: `app/src/main/res/layout/activity_dispute_detail.xml`
- Modify: `app/src/main/java/com/octania/marketplace/ui/transaction/TransactionAdapter.java`
- Modify: `app/src/main/AndroidManifest.xml`

Base path Android: `C:\Users\LENOVO\AndroidStudioProject\AndroidMarketPlace\app\src\main\`

- [ ] **Step 1: Buat Dispute.java model**

Buat file `java/com/octania/marketplace/data/model/Dispute.java`:
```java
package com.octania.marketplace.data.model;

import com.google.gson.annotations.SerializedName;

public class Dispute {
    @SerializedName("id")             private int id;
    @SerializedName("transaction_id") private int transactionId;
    @SerializedName("buyer_id")       private int buyerId;
    @SerializedName("seller_id")      private int sellerId;
    @SerializedName("reason")         private String reason;
    @SerializedName("description")    private String description;
    @SerializedName("status")         private String status;
    @SerializedName("winner")         private String winner;
    @SerializedName("admin_notes")    private String adminNotes;
    @SerializedName("return_courier")          private String returnCourier;
    @SerializedName("return_tracking_number")  private String returnTrackingNumber;
    @SerializedName("buyer_shipped_back_at")   private String buyerShippedBackAt;
    @SerializedName("seller_received_back_at") private String sellerReceivedBackAt;
    @SerializedName("created_at")     private String createdAt;

    // Getters
    public int getId()                     { return id; }
    public int getTransactionId()          { return transactionId; }
    public int getBuyerId()                { return buyerId; }
    public int getSellerId()               { return sellerId; }
    public String getReason()              { return reason; }
    public String getDescription()         { return description; }
    public String getStatus()              { return status; }
    public String getWinner()              { return winner; }
    public String getAdminNotes()          { return adminNotes; }
    public String getReturnCourier()       { return returnCourier; }
    public String getReturnTrackingNumber(){ return returnTrackingNumber; }
    public String getBuyerShippedBackAt()  { return buyerShippedBackAt; }
    public String getSellerReceivedBackAt(){ return sellerReceivedBackAt; }
    public String getCreatedAt()           { return createdAt; }
}
```

- [ ] **Step 2: Tambah endpoint Dispute di ApiService.java (jika belum ada)**

Cek `ApiService.java`. Endpoint berikut harus sudah ada — jika belum, tambahkan:
```java
// Dispute
@GET("disputes/{transactionId}")
Call<ApiResponse<Object>> getDispute(
    @Header("Authorization") String token,
    @Path("transactionId") int transactionId
);

@POST("disputes/{transactionId}")
Call<ApiResponse<Object>> openDispute(
    @Header("Authorization") String token,
    @Path("transactionId") int transactionId,
    @Body Map<String, String> body
);

@Multipart
@POST("disputes/{id}/buyer-ship-back")
Call<ApiResponse<Object>> buyerShipBack(
    @Header("Authorization") String token,
    @Path("id") int disputeId,
    @Part("return_courier") RequestBody courier,
    @Part("return_tracking_number") RequestBody trackingNumber
);
```

- [ ] **Step 3: Buat layout activity_dispute_detail.xml**

Buat file `res/layout/activity_dispute_detail.xml`:
```xml
<?xml version="1.0" encoding="utf-8"?>
<androidx.coordinatorlayout.widget.CoordinatorLayout
    xmlns:android="http://schemas.android.com/apk/res/android"
    xmlns:app="http://schemas.android.com/apk/res-auto"
    android:layout_width="match_parent"
    android:layout_height="match_parent"
    android:background="#F8F9FE">

    <com.google.android.material.appbar.AppBarLayout
        android:layout_width="match_parent"
        android:layout_height="wrap_content">
        <com.google.android.material.appbar.MaterialToolbar
            android:id="@+id/toolbar"
            android:layout_width="match_parent"
            android:layout_height="?attr/actionBarSize"
            android:background="#EF4444"
            app:title="Laporan Masalah"
            app:titleTextColor="@android:color/white"
            app:navigationIconTint="@android:color/white"/>
    </com.google.android.material.appbar.AppBarLayout>

    <ScrollView
        android:layout_width="match_parent"
        android:layout_height="match_parent"
        app:layout_behavior="@string/appbar_scrolling_view_behavior">

        <LinearLayout
            android:layout_width="match_parent"
            android:layout_height="wrap_content"
            android:orientation="vertical"
            android:padding="16dp">

            <!-- Status Card -->
            <com.google.android.material.card.MaterialCardView
                android:id="@+id/cardStatus"
                android:layout_width="match_parent"
                android:layout_height="wrap_content"
                android:layout_marginBottom="12dp"
                app:cardCornerRadius="16dp"
                app:cardElevation="2dp">
                <LinearLayout
                    android:layout_width="match_parent"
                    android:layout_height="wrap_content"
                    android:orientation="vertical"
                    android:padding="16dp">
                    <TextView
                        android:id="@+id/tvDisputeId"
                        android:layout_width="wrap_content"
                        android:layout_height="wrap_content"
                        android:text="Laporan #D—"
                        android:textSize="12sp"
                        android:textColor="#64748B"/>
                    <TextView
                        android:id="@+id/tvStatus"
                        android:layout_width="wrap_content"
                        android:layout_height="wrap_content"
                        android:text="—"
                        android:textSize="20sp"
                        android:textStyle="bold"
                        android:textColor="#EF4444"
                        android:layout_marginTop="4dp"/>
                    <TextView
                        android:id="@+id/tvStatusDesc"
                        android:layout_width="wrap_content"
                        android:layout_height="wrap_content"
                        android:text=""
                        android:textSize="13sp"
                        android:textColor="#64748B"
                        android:layout_marginTop="4dp"/>
                    <TextView
                        android:id="@+id/tvAdminNotes"
                        android:layout_width="match_parent"
                        android:layout_height="wrap_content"
                        android:visibility="gone"
                        android:background="#FEF3C7"
                        android:padding="12dp"
                        android:textSize="13sp"
                        android:textColor="#92400E"
                        android:layout_marginTop="8dp"/>
                </LinearLayout>
            </com.google.android.material.card.MaterialCardView>

            <!-- Alasan Laporan -->
            <com.google.android.material.card.MaterialCardView
                android:id="@+id/cardReason"
                android:layout_width="match_parent"
                android:layout_height="wrap_content"
                android:layout_marginBottom="12dp"
                app:cardCornerRadius="16dp"
                app:cardElevation="2dp">
                <LinearLayout
                    android:layout_width="match_parent"
                    android:layout_height="wrap_content"
                    android:orientation="vertical"
                    android:padding="16dp">
                    <TextView
                        android:layout_width="wrap_content"
                        android:layout_height="wrap_content"
                        android:text="Alasan Laporan"
                        android:textSize="12sp"
                        android:textColor="#64748B"
                        android:textAllCaps="true"/>
                    <TextView
                        android:id="@+id/tvReason"
                        android:layout_width="wrap_content"
                        android:layout_height="wrap_content"
                        android:textSize="15sp"
                        android:textStyle="bold"
                        android:textColor="#1E293B"
                        android:layout_marginTop="4dp"/>
                    <TextView
                        android:id="@+id/tvDescription"
                        android:layout_width="wrap_content"
                        android:layout_height="wrap_content"
                        android:textSize="13sp"
                        android:textColor="#64748B"
                        android:layout_marginTop="4dp"/>
                </LinearLayout>
            </com.google.android.material.card.MaterialCardView>

            <!-- Action: Kirim Resi Balik (tampil saat status = buyer_won) -->
            <com.google.android.material.card.MaterialCardView
                android:id="@+id/cardShipBack"
                android:layout_width="match_parent"
                android:layout_height="wrap_content"
                android:layout_marginBottom="12dp"
                android:visibility="gone"
                app:cardCornerRadius="16dp"
                app:cardElevation="2dp"
                app:strokeColor="#10B981"
                app:strokeWidth="2dp">
                <LinearLayout
                    android:layout_width="match_parent"
                    android:layout_height="wrap_content"
                    android:orientation="vertical"
                    android:padding="16dp">
                    <TextView
                        android:layout_width="wrap_content"
                        android:layout_height="wrap_content"
                        android:text="📦 Kirim Barang Kembali ke Penjual"
                        android:textSize="15sp"
                        android:textStyle="bold"
                        android:textColor="#065F46"/>
                    <TextView
                        android:layout_width="wrap_content"
                        android:layout_height="wrap_content"
                        android:text="Admin memutuskan kamu menang. Kirim barang kembali dan input resi pengiriman."
                        android:textSize="13sp"
                        android:textColor="#64748B"
                        android:layout_marginTop="4dp"
                        android:layout_marginBottom="12dp"/>
                    <com.google.android.material.textfield.TextInputLayout
                        android:layout_width="match_parent"
                        android:layout_height="wrap_content"
                        android:hint="Nama Kurir (contoh: JNE, TIKI)"
                        style="@style/Widget.MaterialComponents.TextInputLayout.OutlinedBox"
                        android:layout_marginBottom="8dp">
                        <com.google.android.material.textfield.TextInputEditText
                            android:id="@+id/etReturnCourier"
                            android:layout_width="match_parent"
                            android:layout_height="wrap_content"/>
                    </com.google.android.material.textfield.TextInputLayout>
                    <com.google.android.material.textfield.TextInputLayout
                        android:layout_width="match_parent"
                        android:layout_height="wrap_content"
                        android:hint="Nomor Resi"
                        style="@style/Widget.MaterialComponents.TextInputLayout.OutlinedBox"
                        android:layout_marginBottom="12dp">
                        <com.google.android.material.textfield.TextInputEditText
                            android:id="@+id/etReturnTracking"
                            android:layout_width="match_parent"
                            android:layout_height="wrap_content"/>
                    </com.google.android.material.textfield.TextInputLayout>
                    <com.google.android.material.button.MaterialButton
                        android:id="@+id/btnShipBack"
                        android:layout_width="match_parent"
                        android:layout_height="wrap_content"
                        android:text="Konfirmasi Pengiriman Balik"
                        app:backgroundTint="#10B981"/>
                </LinearLayout>
            </com.google.android.material.card.MaterialCardView>

            <!-- Status: Menunggu Konfirmasi Penjual (tampil saat buyer_shipping_back) -->
            <com.google.android.material.card.MaterialCardView
                android:id="@+id/cardWaitingSeller"
                android:layout_width="match_parent"
                android:layout_height="wrap_content"
                android:layout_marginBottom="12dp"
                android:visibility="gone"
                app:cardCornerRadius="16dp"
                app:cardElevation="2dp"
                app:strokeColor="#F59E0B"
                app:strokeWidth="2dp">
                <LinearLayout
                    android:layout_width="match_parent"
                    android:layout_height="wrap_content"
                    android:orientation="vertical"
                    android:padding="16dp">
                    <TextView
                        android:layout_width="wrap_content"
                        android:layout_height="wrap_content"
                        android:text="⏳ Menunggu Konfirmasi Penjual"
                        android:textSize="15sp"
                        android:textStyle="bold"
                        android:textColor="#92400E"/>
                    <TextView
                        android:id="@+id/tvTrackingInfo"
                        android:layout_width="wrap_content"
                        android:layout_height="wrap_content"
                        android:textSize="13sp"
                        android:textColor="#64748B"
                        android:layout_marginTop="4dp"/>
                    <TextView
                        android:layout_width="wrap_content"
                        android:layout_height="wrap_content"
                        android:text="Penjual perlu mengkonfirmasi menerima barang kembali. Setelah dikonfirmasi, dana otomatis dikembalikan ke saldo MeyPay kamu."
                        android:textSize="13sp"
                        android:textColor="#64748B"
                        android:layout_marginTop="8dp"/>
                </LinearLayout>
            </com.google.android.material.card.MaterialCardView>

            <!-- Status: Refund Berhasil -->
            <com.google.android.material.card.MaterialCardView
                android:id="@+id/cardRefunded"
                android:layout_width="match_parent"
                android:layout_height="wrap_content"
                android:layout_marginBottom="12dp"
                android:visibility="gone"
                app:cardCornerRadius="16dp"
                app:cardElevation="2dp"
                app:strokeColor="#10B981"
                app:strokeWidth="2dp">
                <LinearLayout
                    android:layout_width="match_parent"
                    android:layout_height="wrap_content"
                    android:orientation="vertical"
                    android:padding="16dp"
                    android:gravity="center">
                    <TextView
                        android:layout_width="wrap_content"
                        android:layout_height="wrap_content"
                        android:text="✅ Dana Berhasil Dikembalikan"
                        android:textSize="16sp"
                        android:textStyle="bold"
                        android:textColor="#065F46"/>
                    <TextView
                        android:layout_width="wrap_content"
                        android:layout_height="wrap_content"
                        android:text="Dana telah masuk ke saldo MeyPay kamu. Cek di menu Dompet MeyPay."
                        android:textSize="13sp"
                        android:textColor="#64748B"
                        android:gravity="center"
                        android:layout_marginTop="8dp"/>
                </LinearLayout>
            </com.google.android.material.card.MaterialCardView>

            <!-- Loading indicator -->
            <ProgressBar
                android:id="@+id/progressBar"
                android:layout_width="wrap_content"
                android:layout_height="wrap_content"
                android:layout_gravity="center_horizontal"
                android:visibility="gone"/>

        </LinearLayout>
    </ScrollView>
</androidx.coordinatorlayout.widget.CoordinatorLayout>
```

- [ ] **Step 4: Buat DisputeDetailActivity.java**

Buat file `java/com/octania/marketplace/ui/dispute/DisputeDetailActivity.java`:
```java
package com.octania.marketplace.ui.dispute;

import android.os.Bundle;
import android.util.Log;
import android.view.MenuItem;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;

import androidx.annotation.NonNull;
import androidx.appcompat.app.AppCompatActivity;
import androidx.appcompat.widget.Toolbar;
import androidx.cardview.widget.CardView;

import com.google.android.material.card.MaterialCardView;
import com.google.gson.Gson;
import com.google.gson.reflect.TypeToken;
import com.octania.marketplace.R;
import com.octania.marketplace.data.model.ApiResponse;
import com.octania.marketplace.data.model.Dispute;
import com.octania.marketplace.data.remote.ApiClient;
import com.octania.marketplace.data.remote.ApiService;

import java.lang.reflect.Type;
import java.util.HashMap;
import java.util.Map;

import okhttp3.MediaType;
import okhttp3.RequestBody;
import retrofit2.Call;
import retrofit2.Callback;
import retrofit2.Response;

public class DisputeDetailActivity extends AppCompatActivity {

    public static final String EXTRA_TRANSACTION_ID = "transaction_id";
    public static final String EXTRA_DISPUTE_ID     = "dispute_id";

    private ApiService apiService;
    private String token;
    private int transactionId;
    private Dispute currentDispute;

    private ProgressBar progressBar;
    private MaterialCardView cardShipBack, cardWaitingSeller, cardRefunded;
    private TextView tvDisputeId, tvStatus, tvStatusDesc, tvAdminNotes;
    private TextView tvReason, tvDescription, tvTrackingInfo;
    private EditText etReturnCourier, etReturnTracking;
    private Button btnShipBack;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_dispute_detail);

        Toolbar toolbar = findViewById(R.id.toolbar);
        setSupportActionBar(toolbar);
        if (getSupportActionBar() != null) getSupportActionBar().setDisplayHomeAsUpEnabled(true);

        transactionId = getIntent().getIntExtra(EXTRA_TRANSACTION_ID, -1);
        token = "Bearer " + getSharedPreferences("auth", MODE_PRIVATE).getString("token", "");

        apiService   = ApiClient.getClient().create(ApiService.class);
        progressBar  = findViewById(R.id.progressBar);
        cardShipBack      = findViewById(R.id.cardShipBack);
        cardWaitingSeller = findViewById(R.id.cardWaitingSeller);
        cardRefunded      = findViewById(R.id.cardRefunded);
        tvDisputeId  = findViewById(R.id.tvDisputeId);
        tvStatus     = findViewById(R.id.tvStatus);
        tvStatusDesc = findViewById(R.id.tvStatusDesc);
        tvAdminNotes = findViewById(R.id.tvAdminNotes);
        tvReason     = findViewById(R.id.tvReason);
        tvDescription  = findViewById(R.id.tvDescription);
        tvTrackingInfo = findViewById(R.id.tvTrackingInfo);
        etReturnCourier  = findViewById(R.id.etReturnCourier);
        etReturnTracking = findViewById(R.id.etReturnTracking);
        btnShipBack      = findViewById(R.id.btnShipBack);

        btnShipBack.setOnClickListener(v -> submitShipBack());

        if (transactionId == -1) {
            Toast.makeText(this, "ID transaksi tidak valid", Toast.LENGTH_SHORT).show();
            finish();
            return;
        }

        loadDispute();
    }

    private void loadDispute() {
        progressBar.setVisibility(View.VISIBLE);
        apiService.getDispute(token, transactionId).enqueue(new Callback<ApiResponse<Object>>() {
            @Override
            public void onResponse(@NonNull Call<ApiResponse<Object>> call,
                                   @NonNull Response<ApiResponse<Object>> response) {
                progressBar.setVisibility(View.GONE);
                if (response.isSuccessful() && response.body() != null && response.body().getData() != null) {
                    Gson gson = new Gson();
                    Type type = new TypeToken<Dispute>(){}.getType();
                    currentDispute = gson.fromJson(gson.toJson(response.body().getData()), type);
                    renderDispute(currentDispute);
                } else {
                    Toast.makeText(DisputeDetailActivity.this,
                        "Gagal memuat data laporan", Toast.LENGTH_SHORT).show();
                }
            }

            @Override
            public void onFailure(@NonNull Call<ApiResponse<Object>> call, @NonNull Throwable t) {
                progressBar.setVisibility(View.GONE);
                Toast.makeText(DisputeDetailActivity.this,
                    "Koneksi gagal: " + t.getMessage(), Toast.LENGTH_SHORT).show();
            }
        });
    }

    private void renderDispute(Dispute d) {
        tvDisputeId.setText("Laporan #D" + d.getId());
        tvReason.setText(d.getReason() != null ? d.getReason() : "-");
        tvDescription.setText(d.getDescription() != null ? d.getDescription() : "");

        if (d.getAdminNotes() != null && !d.getAdminNotes().isEmpty()) {
            tvAdminNotes.setVisibility(View.VISIBLE);
            tvAdminNotes.setText("📋 Catatan Admin: " + d.getAdminNotes());
        }

        // Sembunyikan semua panel aksi dulu
        cardShipBack.setVisibility(View.GONE);
        cardWaitingSeller.setVisibility(View.GONE);
        cardRefunded.setVisibility(View.GONE);

        String status = d.getStatus() != null ? d.getStatus() : "";
        switch (status) {
            case "open":
                tvStatus.setText("⚖️ Menunggu Tinjauan Admin");
                tvStatus.setTextColor(0xFFF97316);
                tvStatusDesc.setText("Laporan kamu sedang ditinjau oleh admin. Harap tunggu.");
                break;
            case "admin_reviewing":
                tvStatus.setText("🔍 Sedang Ditinjau");
                tvStatus.setTextColor(0xFF6366F1);
                tvStatusDesc.setText("Admin sedang meninjau laporan kamu.");
                break;
            case "buyer_won":
                tvStatus.setText("✅ Kamu Menang");
                tvStatus.setTextColor(0xFF10B981);
                tvStatusDesc.setText("Admin memutuskan kamu berhak mendapat refund. Kirim barang kembali ke penjual.");
                cardShipBack.setVisibility(View.VISIBLE);
                break;
            case "buyer_shipping_back":
                tvStatus.setText("📦 Barang Sedang Dikirim Kembali");
                tvStatus.setTextColor(0xFFF59E0B);
                tvStatusDesc.setText("Barang sedang dalam pengiriman kembali ke penjual.");
                cardWaitingSeller.setVisibility(View.VISIBLE);
                String info = "Kurir: " + (d.getReturnCourier() != null ? d.getReturnCourier() : "-")
                            + "\nResi: " + (d.getReturnTrackingNumber() != null ? d.getReturnTrackingNumber() : "-");
                tvTrackingInfo.setText(info);
                break;
            case "seller_received_back":
                tvStatus.setText("⏳ Penjual Sudah Terima Barang");
                tvStatus.setTextColor(0xFF6366F1);
                tvStatusDesc.setText("Penjual sudah mengkonfirmasi menerima barang. Dana sedang diproses.");
                break;
            case "refunded":
                tvStatus.setText("💰 Dana Dikembalikan");
                tvStatus.setTextColor(0xFF10B981);
                tvStatusDesc.setText("Refund berhasil! Dana telah masuk ke saldo MeyPay kamu.");
                cardRefunded.setVisibility(View.VISIBLE);
                break;
            case "seller_won":
            case "closed":
                tvStatus.setText("❌ Laporan Ditutup");
                tvStatus.setTextColor(0xFF64748B);
                tvStatusDesc.setText(d.getWinner() != null && d.getWinner().equals("seller")
                    ? "Admin memutuskan penjual memenangkan laporan ini."
                    : "Laporan telah ditutup.");
                break;
            default:
                tvStatus.setText(status);
                tvStatus.setTextColor(0xFF64748B);
        }
    }

    private void submitShipBack() {
        if (currentDispute == null) return;

        String courier  = etReturnCourier.getText().toString().trim();
        String tracking = etReturnTracking.getText().toString().trim();

        if (courier.isEmpty()) {
            etReturnCourier.setError("Nama kurir wajib diisi");
            return;
        }
        if (tracking.isEmpty()) {
            etReturnTracking.setError("Nomor resi wajib diisi");
            return;
        }

        progressBar.setVisibility(View.VISIBLE);
        btnShipBack.setEnabled(false);

        RequestBody courierBody  = RequestBody.create(MediaType.parse("text/plain"), courier);
        RequestBody trackingBody = RequestBody.create(MediaType.parse("text/plain"), tracking);

        apiService.buyerShipBack(token, currentDispute.getId(), courierBody, trackingBody)
            .enqueue(new Callback<ApiResponse<Object>>() {
                @Override
                public void onResponse(@NonNull Call<ApiResponse<Object>> call,
                                       @NonNull Response<ApiResponse<Object>> response) {
                    progressBar.setVisibility(View.GONE);
                    btnShipBack.setEnabled(true);
                    if (response.isSuccessful()) {
                        Toast.makeText(DisputeDetailActivity.this,
                            "Pengiriman balik berhasil dikonfirmasi!", Toast.LENGTH_LONG).show();
                        loadDispute(); // Refresh tampilan
                    } else {
                        Toast.makeText(DisputeDetailActivity.this,
                            "Gagal mengirim konfirmasi", Toast.LENGTH_SHORT).show();
                    }
                }

                @Override
                public void onFailure(@NonNull Call<ApiResponse<Object>> call, @NonNull Throwable t) {
                    progressBar.setVisibility(View.GONE);
                    btnShipBack.setEnabled(true);
                    Toast.makeText(DisputeDetailActivity.this,
                        "Koneksi gagal", Toast.LENGTH_SHORT).show();
                }
            });
    }

    @Override
    public boolean onOptionsItemSelected(@NonNull MenuItem item) {
        if (item.getItemId() == android.R.id.home) { finish(); return true; }
        return super.onOptionsItemSelected(item);
    }
}
```

- [ ] **Step 5: Daftarkan DisputeDetailActivity di AndroidManifest.xml**

Di `AndroidManifest.xml`, dalam tag `<application>`, tambahkan:
```xml
<activity
    android:name=".ui.dispute.DisputeDetailActivity"
    android:label="Laporan Masalah"
    android:parentActivityName=".ui.transaction.TransactionActivity"/>
```

- [ ] **Step 6: Update TransactionAdapter untuk tampilkan tombol Laporan Masalah**

Di `TransactionAdapter.java`, tambahkan tombol untuk status `shipped` (dan status dispute-related). Di method `onBindViewHolder()`, setelah pengecekan status yang ada, tambahkan logika tombol laporan:

Pertama, tambahkan view tombol di layout `item_transaction.xml` (tambah setelah button upload bukti):
```xml
<com.google.android.material.button.MaterialButton
    android:id="@+id/btnLaporanMasalah"
    android:layout_width="match_parent"
    android:layout_height="wrap_content"
    android:layout_marginTop="4dp"
    android:text="⚠️ Laporan Masalah"
    android:textSize="13sp"
    android:visibility="gone"
    app:backgroundTint="#EF4444"
    style="@style/Widget.MaterialComponents.Button.OutlinedButton"/>
```

Kemudian di adapter, setelah existing status handling:
```java
Button btnLaporan = itemView.findViewById(R.id.btnLaporanMasalah);

// Tampilkan tombol laporan masalah jika transaksi sudah dikirim atau dalam sengketa
if ("shipped".equals(transaction.getStatus())
        || "disputed".equals(transaction.getStatus())
        || "disputed_refunded".equals(transaction.getStatus())) {
    btnLaporan.setVisibility(View.VISIBLE);
    btnLaporan.setText("shipped".equals(transaction.getStatus())
        ? "Buka Laporan Masalah" : "Lihat Laporan Masalah");
    btnLaporan.setOnClickListener(v -> {
        Intent intent = new Intent(context, DisputeDetailActivity.class);
        intent.putExtra(DisputeDetailActivity.EXTRA_TRANSACTION_ID, transaction.getId());
        context.startActivity(intent);
    });
} else {
    btnLaporan.setVisibility(View.GONE);
}
```

- [ ] **Step 7: Build dan test Android**

Di Android Studio:
- Build → Make Project (Ctrl+F9)
- Pastikan tidak ada compilation error
- Jalankan di emulator/device, buka Riwayat Transaksi
- Cari transaksi dengan status "shipped", pastikan tombol "Buka Laporan Masalah" muncul
- Tap tombol → DisputeDetailActivity terbuka → tampil status dispute

- [ ] **Step 8: Commit Android**
```bash
git add app/src/main/java/com/octania/marketplace/ui/dispute/
git add app/src/main/java/com/octania/marketplace/data/model/Dispute.java
git add app/src/main/java/com/octania/marketplace/data/remote/ApiService.java
git add app/src/main/java/com/octania/marketplace/ui/transaction/TransactionAdapter.java
git add app/src/main/res/layout/activity_dispute_detail.xml
git add app/src/main/res/layout/item_transaction.xml
git add app/src/main/AndroidManifest.xml
git commit -m "feat: tambah DisputeDetailActivity dengan alur refund pembeli di Android"
```

---

## Verifikasi End-to-End

- [ ] `php artisan serve` di `C:\laragon\www\WebsiteMarketplace` — tidak ada error
- [ ] Akses `/admin/disputes` — tertulis "Laporan Masalah" di header
- [ ] Akses `/admin/disputes/{id}` — chat sudah embedded di halaman, tidak perlu buka halaman terpisah
- [ ] Akses menu sidebar — "Laporan Masalah" muncul 1x saja (Sengketa sudah diganti, Reports sudah hilang)
- [ ] Tidak ada route `/admin/reports` (404)
- [ ] Android: Transaksi "shipped" punya tombol "Buka Laporan Masalah"
- [ ] Android: Buka dispute → tampil status dengan step yang benar
- [ ] Android: Status `buyer_won` → form resi muncul → submit → status berubah ke `buyer_shipping_back`
