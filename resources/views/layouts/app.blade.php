<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin - MovieHub')</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        .sidebar {
            min-height: calc(100vh - 56px);
            background-color: #f8f9fa;
        }
        .main-content {
            min-height: calc(100vh - 56px);
        }
        .navbar-brand {
            font-weight: bold;
        }
        .list-group-item {
            border: none;
            border-radius: 0.375rem;
            margin-bottom: 0.25rem;
        }
        .list-group-item:hover {
            background-color: #e9ecef;
        }
        .list-group-item.active {
            background-color: #0d6efd;
            border-color: #0d6efd;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
                <i class="fas fa-film me-2"></i>MovieHub Admin
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}">
                            <i class="fas fa-home me-1"></i>Về trang chủ
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <div class="col-md-2 sidebar p-0">
                <div class="list-group list-group-flush p-3">
                    <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-tachometer-alt me-2"></i>Bảng điều khiển
                    </a>
                    <a href="{{ route('admin.suat-chieu.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-calendar-alt me-2"></i>Suất chiếu
                    </a>
                    <a href="{{ route('admin.ghe.index') }}" class="list-group-item list-group-item-action">
                        <i class="fas fa-chair me-2"></i>Ghế
                    </a>
                </div>
            </div>
            <div class="col-md-10 main-content p-4">
                @yield('content')
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    @yield('scripts')
</body>
</html>