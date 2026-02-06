<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ config('app.name', 'Property Management') }}</title>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="w-full bg-white relative">
    <header class="w-full bg-white sticky top-0 z-50">
        <nav class="mx-auto max-w-7xl p-4 flex flex-row justify-between items-center gap-2 w-full">
            <div>
                <a href="{{ route('home') }}" class="text-2xl font-bold">
                    <h1><span class="text-[#008080]">PRO</span>PATY</h1>
                </a>
            </div>
            <div class="flex flex-row gap-4 text-lg font-semibold">
                <a href="{{ route('home') }}">Home</a>
                <a href="">Services</a>
                <a href="">About</a>
                <a href="">Help</a>
            </div>
            <div class="space-x-4">
                <a href="{{ route('login') }}" class="inline-block bg-white text-[#008080] hover:text-white px-6 py-3 rounded-lg font-semibold hover:bg-[#008080] transition">
                    Login
                </a>
                <a href="{{ route('register') }}" class="inline-block bg-transparent border-2 border-[#008080] text-[#008080] px-6 py-3 rounded-lg font-semibold hover:bg-white hover:text-[#20CFC6] transition">
                    Get Started
                </a>
            </div>
        </nav>
    </header>
    <main class="mx-auto max-w-7xl p-4">
        {{-- Hero section --}}
        <div class="flex flex-row gap-4 justify-around items-center h-screen">
            <div class="w-1/2 space-y-4">
                <h1 class="text-6xl font-bold">Welcome to <span class="text-[#008080]">Propaty</span></h1>
                <p class="text-lg text-gray-600">
                    Automated rent collection and property management
                </p>
                <a href="{{ route('register') }}" class="inline-block bg-transparent border-2 border-[#008080] text-[#008080] px-6 py-3 rounded-lg font-semibold hover:bg-white hover:text-[#20CFC6] transition">
                    Get Started
                </a>
            </div>
            <div class="w-1/2">
                <img class="w-full h-full object-cover" src="{{ asset('assets/dashboard.png') }}" alt="Hero Image">
            </div>

         </div>
    </main>
    {{-- <div class="h-screen w-full bg-blue-500">
        <div class="text-center">
            <h1 class="text-4xl font-bold text-white mb-4">Welcome to Property Management System</h1>
            <p class="text-white text-lg mb-8">Automated rent collection and property management</p>
        </div>

    </div> --}}
</body>
</html>