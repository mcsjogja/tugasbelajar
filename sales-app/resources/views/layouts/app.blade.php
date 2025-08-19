<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Sales App') }} - @yield('title', 'Dashboard')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100">
    <div class="min-h-screen">
        <!-- Navigation -->
        <nav class="bg-white shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="flex-shrink-0 flex items-center">
                            <h1 class="text-xl font-bold text-gray-900">Sales App</h1>
                        </div>

                        <!-- Navigation Links -->
                        @auth
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <a href="{{ route('dashboard') }}" 
                               class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('dashboard') ? 'border-b-2 border-blue-500 text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                                Dashboard
                            </a>
                            
                            <a href="{{ route('products.index') }}" 
                               class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('products.*') ? 'border-b-2 border-blue-500 text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                                Products
                            </a>
                            
                            @if(auth()->user()->isAdmin() || auth()->user()->isKasir())
                            <a href="{{ route('transactions.index') }}" 
                               class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('transactions.*') ? 'border-b-2 border-blue-500 text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                                Transactions
                            </a>
                            @endif
                            
                            @if(auth()->user()->isAdmin())
                            <div class="relative" x-data="{ open: false }">
                                <button @click="open = !open" 
                                        class="inline-flex items-center px-1 pt-1 text-sm font-medium {{ request()->routeIs('reports.*') ? 'border-b-2 border-blue-500 text-gray-900' : 'text-gray-500 hover:text-gray-700' }}">
                                    Reports
                                    <svg class="ml-1 h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                
                                <div x-show="open" @click.away="open = false" 
                                     class="absolute left-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50">
                                    <a href="{{ route('reports.sales') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Sales Report</a>
                                    <a href="{{ route('reports.purchases') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Purchase Report</a>
                                    <a href="{{ route('reports.inventory') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Inventory Report</a>
                                    <a href="{{ route('reports.profit') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Profit Report</a>
                                </div>
                            </div>
                            @endif
                        </div>
                        @endauth
                    </div>

                    <!-- User Menu -->
                    @auth
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-500">{{ auth()->user()->name }}</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                            @if(auth()->user()->isAdmin()) bg-red-100 text-red-800 
                            @elseif(auth()->user()->isKasir()) bg-blue-100 text-blue-800 
                            @else bg-green-100 text-green-800 @endif">
                            {{ ucfirst(auth()->user()->role) }}
                        </span>
                        
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="text-sm text-gray-500 hover:text-gray-700">
                                Logout
                            </button>
                        </form>
                    </div>
                    @endauth
                </div>
            </div>
        </nav>

        <!-- Page Content -->
        <main class="py-6">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Flash Messages -->
                @if(session('success'))
                <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}
                </div>
                @endif

                @if(session('error'))
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    {{ session('error') }}
                </div>
                @endif

                @if($errors->any())
                <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                    <ul class="list-disc list-inside">
                        @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>

    <!-- Alpine.js -->
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>