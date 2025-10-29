<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Admin - MovieHub')</title>
<<<<<<< HEAD
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            background-color: #343a40;
            min-height: calc(100vh - 56px);
        }
        .sidebar .nav-link {
            color: #adb5bd;
            padding: 0.75rem 1rem;
        }
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: #495057;
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: #007bff;
        }
        .navbar-brand {
            font-weight: bold;
        }
    </style>
    
    @stack('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
                <i class="fas fa-film me-2"></i>MovieHub Admin
            </a>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
                                    @vite(['resources/css/app.css','resources/js/app.js'])
                                @else
                                    <script src="https://cdn.tailwindcss.com"></script>
                                @endif
                            </head>
                            <body class="min-h-screen bg-[#0d0f14] text-white">
                                <header class="bg-[#151822] border-b border-[#262833]">
                                    <div class="max-w-7xl mx-auto px-4 py-4 flex items-center justify-between gap-6">
                                        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 shrink-0">
                                            <img src="{{ asset('images/logo.jpg') }}" alt="MovieHub" class="h-16 w-16 object-contain rounded">
                                            <span class="text-xl font-semibold">Admin</span>
                                        </a>
                                        <a href="{{ route('home') }}" class="text-sm hover:text-[#F53003]">← Về trang chủ</a>
                                    </div>
                                </header>

                                <div class="max-w-7xl mx-auto px-4 py-8 flex gap-6">
                                    <aside class="hidden lg:block w-64 shrink-0">
                                        <div class="bg-[#151822] border border-[#262833] rounded-xl p-4">
                                            <h3 class="font-semibold mb-3">Quản trị</h3>
                                            <nav class="flex flex-col gap-1 text-sm">
                                                <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded hover:bg-[#222533]">Bảng điều khiển</a>
                                                <a href="#movies" class="px-3 py-2 rounded hover:bg-[#222533]">Phim</a>
                                                <a href="#showtimes" class="px-3 py-2 rounded hover:bg-[#222533]">Suất chiếu</a>
                                                <a href="#tickets" class="px-3 py-2 rounded hover:bg-[#222533]">Vé</a>
                                                <a href="{{ route('admin.users.index') }}" class="px-3 py-2 rounded hover:bg-[#222533]">Người dùng</a>
                                            </nav>
                                        </div>
                                </nav>

                                    <main class="flex-1">
                                        @yield('content')
                                    </main>   
                                </div>

                                @stack('scripts')
                        </body>
                        </html>
                    </ul>
                </div>
            </div>
>>>>>>> origin/hanh
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" 
                               href="{{ route('admin.dashboard') }}">
                                <i class="fas fa-tachometer-alt me-2"></i>Bảng điều khiển
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.movies.*') ? 'active' : '' }}" 
                               href="{{ route('admin.movies.index') }}">
                                <i class="fas fa-film me-2"></i>Quản lý phim
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-calendar-alt me-2"></i>Suất chiếu
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-ticket-alt me-2"></i>Vé
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#">
                                <i class="fas fa-users me-2"></i>Người dùng
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="pt-3 pb-2 mb-3">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    @stack('scripts')
</body>
</html>


