<x-app-layout>
    <x-slot name="title">إنشاء نشاط تجاري</x-slot>

    <div class="mx-auto max-w-md">
        <p class="mb-4 text-sm text-gray-600">
            قبل ما تبدأ، سوّي نشاطك التجاري الأول — هذا هو "المساحة" اللي راح
            تُدار فيها كل طلباتك وقنواتك وفريقك.
        </p>

        <form method="POST" action="{{ route('onboarding.store') }}" class="space-y-4 rounded-lg border bg-white p-6 shadow-sm">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-700">اسم النشاط التجاري</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                       placeholder="مثال: متجر لمسة أناقة"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <button type="submit" class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                إنشاء ومتابعة
            </button>
        </form>
    </div>
</x-app-layout>
