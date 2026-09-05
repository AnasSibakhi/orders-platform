<x-app-layout>
    <x-slot name="title">اختر النشاط التجاري</x-slot>

    <div class="mx-auto max-w-lg space-y-3">
        @forelse ($businesses as $business)
            <a href="{{ route('business.dashboard', $business) }}"
               class="block rounded-lg border bg-white p-4 shadow-sm hover:border-indigo-300">
                <div class="font-semibold">{{ $business->name }}</div>
                <div class="text-xs text-gray-500">دورك: {{ $business->pivot->role }}</div>
            </a>
        @empty
            <p class="text-gray-500">ما عندك أي نشاط تجاري بعد.</p>
        @endforelse

        <a href="{{ route('onboarding.create') }}" class="block text-center text-sm text-indigo-600 hover:underline">
            + إنشاء نشاط تجاري جديد
        </a>
    </div>
</x-app-layout>
