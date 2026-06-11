@extends('layouts.app')

@section('content')
    <section class="padding-large bg-light-blue">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <h2 class="display-7 text-uppercase">Daftar</h2>
                                <p class="text-muted">Buat akun baru untuk mulai berjualan</p>
                            </div>

                            <form method="POST" action="{{ route('register') }}">
                                @csrf

                                <div class="mb-3">
                                    <label for="name" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" id="name"
                                        name="name" value="{{ old('name') }}" required autofocus>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email"
                                        name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="phone" class="form-label">Nomor Telepon</label>
                                    <div class="input-group">
                                        <span class="input-group-text">+62</span>
                                        <input type="tel" class="form-control @error('phone') is-invalid @enderror"
                                            id="phone" name="phone" value="{{ old('phone') }}" placeholder="8xxxxxxxxxx"
                                            required>
                                    </div>
                                    @error('phone')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">Password</label>
                                        <div class="input-group">
                                            <input type="password"
                                                class="form-control @error('password') is-invalid @enderror" id="password"
                                                name="password" required>
                                            <button type="button" class="btn btn-outline-secondary"
                                                onclick="togglePassword(this)">
                                                <svg width="18" height="18" fill="currentColor">
                                                    <use xlink:href="#eye-slash"></use>
                                                </svg>
                                            </button>
                                            @error('password')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                                        <div class="input-group">
                                            <input type="password" class="form-control" id="password_confirmation"
                                                name="password_confirmation" required>
                                            <button type="button" class="btn btn-outline-secondary"
                                                onclick="togglePassword(this)">
                                                <svg width="18" height="18" fill="currentColor">
                                                    <use xlink:href="#eye"></use>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="role" class="form-label">Daftar Sebagai</label>
                                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required onchange="toggleBankSection()">
                                        <option value="buyer" {{ old('role') === 'buyer' ? 'selected' : '' }}>Pembeli (Untuk Belanja Barang Bekas)</option>
                                        <option value="seller" {{ old('role') === 'seller' ? 'selected' : '' }}>Penjual (Untuk Mulai Berjualan)</option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Bank Account Section for Seller -->
                                <div id="bankSection" style="display: {{ old('role') === 'seller' ? 'block' : 'none' }};" class="p-4 bg-light rounded-3 mb-3 border">
                                    <h5 class="fw-bold text-uppercase mb-3" style="font-size: 0.85rem; color: #555;">Detail Rekening Bank (Wajib untuk Penjual)</h5>
                                    
                                    <div class="mb-3">
                                        <label for="bank_name" class="form-label">Nama Bank</label>
                                        <input type="text" class="form-control @error('bank_name') is-invalid @enderror" id="bank_name"
                                            name="bank_name" value="{{ old('bank_name') }}" placeholder="Contoh: BCA / Mandiri / BRI">
                                        @error('bank_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="bank_account_number" class="form-label">Nomor Rekening</label>
                                        <input type="text" class="form-control @error('bank_account_number') is-invalid @enderror" id="bank_account_number"
                                            name="bank_account_number" value="{{ old('bank_account_number') }}" placeholder="Contoh: 123456789">
                                        @error('bank_account_number')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="bank_account_name" class="form-label">Nama Pemilik Rekening</label>
                                        <input type="text" class="form-control @error('bank_account_name') is-invalid @enderror" id="bank_account_name"
                                            name="bank_account_name" value="{{ old('bank_account_name') }}" placeholder="Sesuai rekening bank">
                                        @error('bank_account_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="mb-4 form-check">
                                    <input type="checkbox" class="form-check-input @error('terms') is-invalid @enderror"
                                        id="terms" name="terms" {{ old('terms') ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="terms">
                                        Saya setuju dengan <a href="#" style="color: var(--primary-color)"
                                            data-bs-toggle="modal" data-bs-target="#termsModal">syarat dan
                                            ketentuan</a>
                                    </label>
                                    @error('terms')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button type="submit" class="btn btn-dark text-uppercase w-100 mb-3">
                                    Daftar
                                </button>

                                <div class="text-center">
                                    <span class="text-muted">Sudah punya akun?</span>
                                    <a href="{{ route('login') }}" class="text-decoration-none"
                                        style="color: var(--primary-color)">Masuk</a>
                                </div>
                            </form>

                            <script>
                                function toggleBankSection() {
                                    const role = document.getElementById('role').value;
                                    const bankSection = document.getElementById('bankSection');
                                    const bankInputs = bankSection.querySelectorAll('input');
                                    
                                    if (role === 'seller') {
                                        bankSection.style.display = 'block';
                                        bankInputs.forEach(input => input.setAttribute('required', 'required'));
                                    } else {
                                        bankSection.style.display = 'none';
                                        bankInputs.forEach(input => {
                                            input.removeAttribute('required');
                                        });
                                    }
                                }
                                
                                document.addEventListener('DOMContentLoaded', function() {
                                    toggleBankSection();
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Terms & Conditions Modal -->
    <div class="modal fade" id="termsModal" tabindex="-1" aria-labelledby="termsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="termsModalLabel">Syarat dan Ketentuan - Market Barang Bekas</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto; white-space: pre-line;">
                    {!! nl2br(e($terms->value ?? 'Syarat & Ketentuan sedang diperbarui.')) !!}

                    <hr class="my-4">
                    <h5 class="fw-bold">Kebijakan Privasi</h5>
                    {!! nl2br(e($privacy->value ?? 'Kebijakan Privasi sedang diperbarui.')) !!}
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-dark" data-bs-dismiss="modal">Saya Mengerti</button>
                </div>
            </div>
        </div>
    </div>
@endsection