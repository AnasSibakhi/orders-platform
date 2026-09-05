<x-app-layout>
    <x-slot name="title">تأكيد البريد الإلكتروني</x-slot>

    <div class="space-y-4 rounded-lg border bg-white p-6 shadow-sm text-sm text-gray-600">
        <p>شكرًا لتسجيلك! قبل البدء، هل يمكنك تأكيد بريدك الإلكتروني عن طريق الرابط الذي أرسلناه لك؟</p>

        @if (session('status') == 'verification-link-sent')
            <p class="text-green-700">تم إرسال رابط تحقق جديد إلى بريدك الإلكتروني.</p>
        @endif

        <div class="flex items-center justify-between">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <button type="submit" class="text-indigo-600 hover:underline">إعادة إرسال رابط التحقق</button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-gray-500 hover:underline">تسجيل الخروج</button>
            </form>
        </div>
    </div>
</x-app-layout>
