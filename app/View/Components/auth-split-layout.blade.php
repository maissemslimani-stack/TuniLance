<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'TuniLance' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="antialiased">
    <div class="min-h-screen grid md:grid-cols-2">

        {{-- Left marketing panel --}}
        <div class="relative hidden md:flex flex-col justify-between bg-slate-50 p-10 overflow-hidden">
            <a href="{{ route('landing') }}" class="flex items-center gap-2 font-bold text-slate-900 z-10">
                <span class="text-emerald-600">◈</span> TuniLance
            </a>

            <div class="z-10">
                <h1 class="text-3xl font-bold text-slate-900 leading-tight">
                    Hire Top Tunisian Freelancers
                </h1>
                <p class="text-slate-600 mt-3 max-w-sm">
                    Connect with top Tunisian talent or find your next big project with secure escrow payments.
                </p>

                <div class="flex gap-3 mt-6">
                    <div class="bg-white rounded-xl p-4 flex items-center gap-2 shadow-sm">
                        <span class="text-emerald-600">✅</span>
                        <span class="text-sm text-slate-700">Verified by Tunisia Escrow</span>
                    </div>
                    <div class="bg-white rounded-xl p-4 flex items-center gap-2 shadow-sm">
                        <span>💳</span>
                        <span class="text-sm text-slate-700">Zero Deposit Fees</span>
                    </div>
                </div>
            </div>

            <img src="{{ asset('images/auth-office.jpg') }}" alt=""
                class="absolute bottom-0 left-0 w-full h-2/5 object-cover opacity-90">
        </div>

        {{-- Right form panel --}}
        <div class="flex flex-col justify-center px-8 md:px-16 py-12 bg-white">
            <div class="max-w-sm mx-auto w-full">
                {{ $slot }}
            </div>
        </div>
    </div>

    <footer class="text-center text-xs text-slate-400 py-6 border-t border-slate-100">
        © {{ date('Y') }} TuniLance Platform. Secured by Tunisia Escrow.
        <a href="#" class="ml-4 hover:text-slate-600">Terms</a>
        <a href="#" class="ml-2 hover:text-slate-600">Privacy</a>
        <a href="#" class="ml-2 hover:text-slate-600">Support</a>
    </footer>
</body>