<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Daftar ke SIMPA</title>
    <link rel="stylesheet" href="{{ asset('css/simpa.css') }}">
</head>
<body class="login-page">
    <form class="card login-card" method="POST" action="{{ route('register.store') }}">
        @csrf
        <h1>Buat Akun SIMPA</h1>
        <p>Daftar sebagai peminjam alat.</p>
        @include('partials.flash')
        <label class="form-group">
            <span>Nama</span>
            <input class="input" type="text" name="name" value="{{ old('name') }}" maxlength="255" required autocomplete="name">
        </label>
        <label class="form-group">
            <span>Email</span>
            <input class="input" type="email" name="email" value="{{ old('email') }}" required autocomplete="email">
        </label>
        <label class="form-group">
            <span>Password</span>
            <input class="input" type="password" name="password" required minlength="8" autocomplete="new-password">
        </label>
        <label class="form-group">
            <span>Konfirmasi Password</span>
            <input class="input" type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
        </label>
        <button class="btn btn-primary full">Daftar</button>
        <p class="center">Sudah punya akun? <a href="{{ route('login') }}">Masuk</a></p>
    </form>
</body>
</html>