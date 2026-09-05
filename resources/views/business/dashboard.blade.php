<x-app-layout>
    <x-slot name="title">لوحة التحكم — {{ $business->name }}</x-slot>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5">
        <div class="rounded-lg border bg-white p-5 shadow-sm">
            <div class="text-sm text-gray-500">إجمالي الطلبات</div>
            <div class="mt-1 text-2xl font-semibold">{{ $stats['total_orders'] }}</div>
        </div>
        <div class="rounded-lg border bg-white p-5 shadow-sm">
            <div class="text-sm text-gray-500">طلبات جديدة</div>
            <div class="mt-1 text-2xl font-semibold text-amber-600">{{ $stats['new_orders'] }}</div>
        </div>
        <div class="rounded-lg border bg-white p-5 shadow-sm">
            <div class="text-sm text-gray-500">قيد المعالجة</div>
            <div class="mt-1 text-2xl font-semibold text-blue-600">{{ $stats['processing_orders'] }}</div>
        </div>
        <div class="rounded-lg border bg-white p-5 shadow-sm">
            <div class="text-sm text-gray-500">العملاء</div>
            <div class="mt-1 text-2xl font-semibold">{{ $stats['total_customers'] }}</div>
        </div>
        <div class="rounded-lg border bg-white p-5 shadow-sm">
            <div class="text-sm text-gray-500">قنوات متصلة</div>
            <div class="mt-1 text-2xl font-semibold text-green-600">{{ $stats['connected_channels'] }}</div>
        </div>
    </div>

    <div class="mt-8 rounded-lg border bg-white shadow-sm">
        <div class="flex items-center justify-between border-b px-5 py-3">
            <span class="text-sm font-semibold text-gray-700">أحدث الطلبات</span>
            <a href="{{ route('orders.index', $business) }}" class="text-xs text-indigo-600 hover:underline">عرض الكل</a>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-right text-gray-500">
                <tr>
                    <th class="px-5 py-2 font-medium">#</th>
                    <th class="px-5 py-2 font-medium">العميل</th>
                    <th class="px-5 py-2 font-medium">القناة</th>
                    <th class="px-5 py-2 font-medium">الحالة</th>
                    <th class="px-5 py-2 font-medium">الإجمالي</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($recentOrders as $order)
                    <tr>
                        <td class="px-5 py-2">
                            <a href="{{ route('orders.show', [$business, $order]) }}" class="text-indigo-600 hover:underline">#{{ $order->id }}</a>
                        </td>
                        <td class="px-5 py-2">{{ $order->customer->name ?? '—' }}</td>
                        <td class="px-5 py-2 text-gray-500">{{ $order->channel->name ?? '—' }}</td>
                        <td class="px-5 py-2">
                            <x-order-status-badge :status="$order->status" />
                        </td>
                        <td class="px-5 py-2">{{ number_format($order->total, 2) }} {{ $order->currency }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-5 py-6 text-center text-gray-400">لا يوجد طلبات بعد</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-app-layout>
