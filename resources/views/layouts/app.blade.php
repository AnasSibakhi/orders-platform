<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? config('app.name', 'Orders Platform') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-900 antialiased">
    @auth
        <div class="flex min-h-screen">
            <aside class="hidden w-64 shrink-0 bg-gray-900 text-gray-200 md:block">
                <div class="px-6 py-5">
                    <div class="text-lg font-semibold text-white">{{ config('app.name', 'Orders Platform') }}</div>
                    @isset($currentBusiness)
                        <div class="mt-1 truncate text-xs text-gray-400">{{ $currentBusiness->name }}</div>
                    @endisset
                </div>
                <nav class="mt-2 space-y-1 px-3 text-sm">
                    @isset($currentBusiness)
                        <a href="{{ route('business.dashboard', $currentBusiness) }}" class="block rounded-md px-3 py-2 hover:bg-gray-800">لوحة التحكم</a>
                        <a href="{{ route('orders.index', $currentBusiness) }}" class="block rounded-md px-3 py-2 hover:bg-gray-800">الطلبات</a>
                        <a href="{{ route('customers.index', $currentBusiness) }}" class="block rounded-md px-3 py-2 hover:bg-gray-800">العملاء</a>
                        <a href="{{ route('channels.index', $currentBusiness) }}" class="block rounded-md px-3 py-2 hover:bg-gray-800">القنوات</a>
                        <a href="{{ route('team.index', $currentBusiness) }}" class="block rounded-md px-3 py-2 hover:bg-gray-800">الفريق</a>
                        <a href="{{ route('business.switch') }}" class="mt-4 block rounded-md px-3 py-2 text-gray-400 hover:bg-gray-800 hover:text-gray-200">تبديل النشاط التجاري</a>
                    @else
                        <a href="{{ route('home') }}" class="block rounded-md px-3 py-2 hover:bg-gray-800">الرئيسية</a>
                    @endisset
                </nav>
            </aside>

            <div class="flex-1">
                <header class="flex items-center justify-between border-b bg-white px-6 py-4">
                    <h1 class="text-lg font-semibold">{{ $title ?? '' }}</h1>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm text-gray-500 hover:text-gray-800">
                            {{ auth()->user()->name }} — تسجيل الخروج
                        </button>
                    </form>
                </header>
                <main class="p-6">
                    @if (session('status'))
                        <div class="mb-4 rounded-md bg-green-50 px-4 py-3 text-sm text-green-700">
                            {{ session('status') }}
                        </div>
                    @endif
                    @if ($errors->any())
                        <div class="mb-4 rounded-md bg-red-50 px-4 py-3 text-sm text-red-700">
                            <ul class="list-inside list-disc">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    {{ $slot }}
                </main>
            </div>
        </div>
    @else
        <div class="flex min-h-screen items-center justify-center px-4">
            <div class="w-full max-w-sm">
                <div class="mb-6 text-center text-xl font-semibold">
                    {{ config('app.name', 'Orders Platform') }}
                </div>
                {{ $slot }}
            </div>
        </div>
    @endauth
</body>
</html>
