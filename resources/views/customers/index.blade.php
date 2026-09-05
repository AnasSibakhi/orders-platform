<x-app-layout>
    <x-slot name="title">العملاء</x-slot>

    <form method="GET" class="mb-4">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="بحث بالاسم أو البريد أو الجوال..."
               class="w-full max-w-sm rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
    </form>

    <div class="overflow-hidden rounded-lg border bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-right text-gray-500">
                <tr>
                    <th class="px-5 py-2 font-medium">الاسم</th>
                    <th class="px-5 py-2 font-medium">الجوال</th>
                    <th class="px-5 py-2 font-medium">البريد الإلكتروني</th>
                    <th class="px-5 py-2 font-medium">عدد الطلبات</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($customers as $customer)
                    <tr>
                        <td class="px-5 py-2">
                            <a href="{{ route('customers.show', [$business, $customer]) }}" class="text-indigo-600 hover:underline">
                                {{ $customer->name ?? 'بدون اسم' }}
                            </a>
                        </td>
                        <td class="px-5 py-2 text-gray-500">{{ $customer->phone_normalized ?? '—' }}</td>
                        <td class="px-5 py-2 text-gray-500">{{ $customer->email ?? '—' }}</td>
                        <td class="px-5 py-2">{{ $customer->orders_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-5 py-6 text-center text-gray-400">لا يوجد عملاء بعد</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $customers->links() }}</div>
</x-app-layout>
