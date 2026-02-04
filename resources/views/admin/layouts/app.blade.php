<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - Sucheus</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#F15A23',
                        'primary-dark': '#D14A1A',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <style>
        [x-cloak] { display: none !important; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50">
    <div class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        <aside class="w-64 bg-primary text-white flex-shrink-0 hidden md:block">
            <div class="h-full flex flex-col">
                <!-- Logo -->
                <div class="px-6 py-6 border-b border-primary-dark">
                    <h1 class="text-2xl font-bold">Sucheus Admin</h1>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
                    <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-white text-primary' : 'text-white hover:bg-primary-dark' }}">
                        <i class="fas fa-home w-5"></i>
                        <span class="ml-3 font-medium">Dashboard</span>
                    </a>
                    <a href="#" class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-primary-dark transition-colors">
                        <i class="fas fa-users w-5"></i>
                        <span class="ml-3 font-medium">Users</span>
                    </a>
                    <a href="#" class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-primary-dark transition-colors">
                        <i class="fas fa-store w-5"></i>
                        <span class="ml-3 font-medium">Vendors</span>
                    </a>
                    <a href="#" class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-primary-dark transition-colors">
                        <i class="fas fa-briefcase w-5"></i>
                        <span class="ml-3 font-medium">Services</span>
                    </a>
                    <a href="#" class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-primary-dark transition-colors">
                        <i class="fas fa-shopping-cart w-5"></i>
                        <span class="ml-3 font-medium">Orders</span>
                    </a>
                    <a href="#" class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-primary-dark transition-colors">
                        <i class="fas fa-dollar-sign w-5"></i>
                        <span class="ml-3 font-medium">Transactions</span>
                    </a>
                    <a href="#" class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-primary-dark transition-colors">
                        <i class="fas fa-exclamation-triangle w-5"></i>
                        <span class="ml-3 font-medium">Disputes</span>
                    </a>
                    <a href="#" class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-primary-dark transition-colors">
                        <i class="fas fa-image w-5"></i>
                        <span class="ml-3 font-medium">Banners</span>
                    </a>
                    <a href="#" class="flex items-center px-4 py-3 rounded-lg text-white hover:bg-primary-dark transition-colors">
                        <i class="fas fa-bell w-5"></i>
                        <span class="ml-3 font-medium">Notifications</span>
                    </a>
                </nav>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Navbar -->
            <header class="bg-white shadow-sm z-10">
                <div class="flex items-center justify-between px-6 py-4">
                    <div class="flex items-center">
                        <button class="md:hidden text-gray-600 hover:text-gray-900" onclick="toggleSidebar()">
                            <i class="fas fa-bars text-xl"></i>
                        </button>
                        <h2 class="text-xl font-semibold text-gray-800 ml-4 md:ml-0">@yield('page-title', 'Dashboard')</h2>
                    </div>

                    <!-- Admin Profile Dropdown -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" class="flex items-center space-x-3 focus:outline-none">
                            <div class="text-right hidden sm:block">
                                <p class="text-sm font-medium text-gray-700">{{ auth()->user()->full_name }}</p>
                                <p class="text-xs text-gray-500">Administrator</p>
                            </div>
                            <div class="w-10 h-10 rounded-full bg-primary text-white flex items-center justify-center font-semibold">
                                {{ strtoupper(substr(auth()->user()->first_name, 0, 1)) }}{{ strtoupper(substr(auth()->user()->last_name, 0, 1)) }}
                            </div>
                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                        </button>

                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg py-2 border border-gray-200">
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-user w-4"></i>
                                <span class="ml-2">Profile</span>
                            </a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                <i class="fas fa-cog w-4"></i>
                                <span class="ml-2">Settings</span>
                            </a>
                            <hr class="my-2">
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-gray-100">
                                    <i class="fas fa-sign-out-alt w-4"></i>
                                    <span class="ml-2">Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Flash Messages -->
                @if(session('success'))
                    <div class="mb-6 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-check-circle mr-2"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="mb-6 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg" role="alert">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle mr-2"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            // Mobile sidebar toggle functionality
            document.querySelector('aside').classList.toggle('hidden');
        }
    </script>
</body>
</html>
