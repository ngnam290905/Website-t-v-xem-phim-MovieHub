<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        // Tự động refresh CSRF token nếu trang load lâu (tránh lỗi 419)
        setTimeout(function() {
            fetch('{{ route('login.form') }}', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(response => response.text())
              .then(html => {
                  const parser = new DOMParser();
                  const doc = parser.parseFromString(html, 'text/html');
                  const newToken = doc.querySelector('meta[name="csrf-token"]');
                  if (newToken) {
                      document.querySelector('meta[name="csrf-token"]').content = newToken.content;
                      document.querySelector('input[name="_token"]').value = newToken.content;
                  }
              });
        }, 60000); // Refresh sau 1 phút
    </script>
    </head>
<body>
<main class="container">
    <h1>Đăng nhập</h1>

    @if ($errors->any())
        <article style="border-left: 4px solid #e11d48; padding-left: 12px;">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </article>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf
        <label>
            Email
            <input type="email" name="email" value="{{ old('email') }}" required>
        </label>
        <label>
            Mật khẩu
            <input type="password" name="password" required>
        </label>
        <button type="submit">Đăng nhập</button>
    </form>

    <p>Chưa có tài khoản? <a href="{{ route('register.form') }}">Đăng ký</a></p>
</main>
</body>
</html>


