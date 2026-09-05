<x-app-layout>
    <x-slot name="title">القنوات</x-slot>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-lg border bg-white shadow-sm lg:col-span-2">
            <div class="border-b px-5 py-3 text-sm font-semibold text-gray-700">القنوات المضافة</div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-right text-gray-500">
                    <tr>
                        <th class="px-5 py-2 font-medium">الاسم</th>
                        <th class="px-5 py-2 font-medium">النوع</th>
                        <th class="px-5 py-2 font-medium">الحالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($channels as $channel)
                        <tr>
                            <td class="px-5 py-2">{{ $channel->name }}</td>
                            <td class="px-5 py-2 text-gray-500">{{ $types[$channel->type] ?? $channel->type }}</td>
                            <td class="px-5 py-2">
                                <span @class([
                                    'rounded-full px-2 py-0.5 text-xs',
                                    'bg-green-100 text-green-700' => $channel->status === 'connected',
                                    'bg-gray-100 text-gray-600' => $channel->status === 'disconnected',
                                    'bg-red-100 text-red-700' => $channel->status === 'error',
                                ])>
                                    {{ $channel->status }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-6 text-center text-gray-400">لا يوجد قنوات بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="rounded-lg border bg-white p-5 shadow-sm">
            <h2 class="mb-3 text-sm font-semibold text-gray-700">إضافة قناة</h2>
            <p class="mb-3 text-xs text-gray-400">
                هذا يسجّل مكان القناة بالنظام فقط. الربط الفعلي (WhatsApp Cloud API / Instagram Graph API)
                يُفعَّل في مرحلة لاحقة من خطة التنفيذ.
            </p>
            <form method="POST" action="{{ route('channels.store', $business) }}" class="space-y-3">
                @csrf
                <select name="type" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach ($types as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <input type="text" name="name" placeholder="اسم للتعريف (مثال: واتساب المتجر الرئيسي)" required
                       class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <button type="submit" class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    إضافة
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
