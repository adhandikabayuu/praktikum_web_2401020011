<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Praktikum</title>
</head>
<body>

    <h1>Login Praktikum</h1>

    <form method="POST" action="{{ route('login.proses') }}">
        @csrf

        <p>
            <label>Email</label>
            <input
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
            >
        </p>

        <p>
            <label>Kata Sandi</label>
            <input
                type="password"
                name="password"
                required
            >
        </p>

        @error('email')
            <p style="color: red;">{{ $message }}</p>
        @enderror

        <button type="submit">Masuk</button>
    </form>

</body>
</html>
