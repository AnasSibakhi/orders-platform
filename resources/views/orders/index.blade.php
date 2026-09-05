<x-app-layout>
    <x-slot name="title">الطلبات</x-slot>

    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('orders.index', $business) }}"
           @class(['rounded-full px-3 py-1 text-xs', 'bg-gray-900 text-white' => ! $activeStatus, 'bg-white border text-gray-600' => $activeStatus])>
            الكل
        </a>
        @foreach ($statuses as $status)
            <a href="{{ route('orders.index', [$business, 'status' => $status]) }}"
               @class(['rounded-full px-3 py-1 text-xs border', 'bg-gray-900 text-white' => $activeStatus === $status, 'bg-white text-gray-600' => $activeStatus !== $status])>
                <x-order-status-badge :status="$status" class="!bg-transparent !p-0 !text-inherit" />
            </a>
        @endforeach
    </div>

    <div class="overflow-hidden rounded-lg border bg-white shadow-sm">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-right text-gray-500">
                <tr>
                    <th class="px-5 py-2 font-medium">#</th>
                    <th class="px-5 py-2 font-medium">العميل</th>
                    <th class="px-5 py-2 font-medium">القناة</th>
                    <th class="px-5 py-2 font-medium">الحالة</th>
                    <th class="px-5 py-2 font-medium">المسؤول</th>
                    <th class="px-5 py-2 font-medium">الإجمالي</th>
                    <th class="px-5 py-2 font-medium">التاريخ</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse ($orders as $order)
                    <tr>
                        <td class="px-5 py-2">
                            <a href="{{ route('orders.show', [$business, $order]) }}" class="text-indigo-600 hover:underline">#{{ $order->id }}</a>
                        </td>
                        <td class="px-5 py-2">{{ $order->customer->name ?? '—' }}</td>
                        <td class="px-5 py-2 text-gray-500">{{ $order->channel->name ?? '—' }}</td>
                        <td class="px-5 py-2"><x-order-status-badge :status="$order->status" /></td>
                        <td class="px-5 py-2 text-gray-500">{{ $order->assignedTo->name ?? '—' }}</td>
                        <td class="px-5 py-2">{{ number_format($order->total, 2) }} {{ $order->currency }}</td>
                        <td class="px-5 py-2 text-gray-500">{{ $order->created_at->diffForHumans() }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-5 py-6 text-center text-gray-400">لا يوجد طلبات مطابقة</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $orders->links() }}</div>
</x-app-layout>
