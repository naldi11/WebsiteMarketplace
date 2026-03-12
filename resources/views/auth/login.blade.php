@extends('layouts.app')

@section('content')
    <section class="padding-large bg-light-blue">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-5">
                    <div class="card shadow-sm">
                        <div class="card-body p-5">
                            <div class="text-center mb-4">
                                <h2 class="display-7 text-uppercase">Masuk</h2>
                                <p class="text-muted">Selamat datang kembali!</p>
                            </div>

                            <form method="POST" action="{{ route('login') }}">
                                @csrf

                                <div class="mb-3">
                                    <label for="login" class="form-label">Email / Nomor Telepon</label>
                                    <input type="text" class="form-control @error('login') is-invalid @enderror" id="login"
                                        name="login" value="{{ old('login') }}" required autofocus>
                                    @error('login')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <div class="input-group">
                                        <input type="password" class="form-control @error('password') is-invalid @enderror"
                                            id="password" name="password" required>
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

                                <div class="mb-4 form-check">
                                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                    <label class="form-check-label" for="remember">Ingat saya</label>
                                </div>

                                <button type="submit" class="btn btn-dark text-uppercase w-100 mb-3">
                                    Masuk
                                </button>

                                <div class="text-center">
                                    <span class="text-muted">Belum punya akun?</span>
                                    <a href="{{ route('register') }}" class="text-decoration-none"
                                        style="color: var(--primary-color)">Daftar</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection