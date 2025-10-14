<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng ký</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
</head>
<body>
<main class="container">
    <h1>Đăng ký</h1>

    @if ($errors->any())
        <article style="border-left: 4px solid #e11d48; padding-left: 12px;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </article>
    @endif

    <form method="POST" action="{{ route('register') }}">
        @csrf
        <label>
            Họ tên
            <input type="text" name="ho_ten" value="{{ old('ho_ten') }}" required>
        </label>
        <label>
            Email
            <input type="email" name="email" value="{{ old('email') }}" required>
        </label>
        <label>
            Mật khẩu
            <input type="password" name="password" required>
        </label>
        <label>
            Xác nhận mật khẩu
            <input type="password" name="password_confirmation" required>
        </label>
        <button type="submit">Tạo tài khoản</button>
    </form>

    <p>Đã có tài khoản? <a href="{{ route('login.form') }}">Đăng nhập</a></p>
</main>
</body>
</html>