<x-app-layout>
    <x-slot name="title">طلب #{{ $order->id }}</x-slot>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="rounded-lg border bg-white p-5 shadow-sm">
                <div class="mb-3 flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-gray-700">تفاصيل الطلب</h2>
                    <x-order-status-badge :status="$order->status" />
                </div>
                <dl class="grid grid-cols-2 gap-y-2 text-sm">
                    <dt class="text-gray-500">العميل</dt>
                    <dd>{{ $order->customer->name ?? '—' }}</dd>
                    <dt class="text-gray-500">القناة</dt>
                    <dd>{{ $order->channel->name ?? '—' }}</dd>
                    <dt class="text-gray-500">المسؤول</dt>
                    <dd>{{ $order->assignedTo->name ?? '—' }}</dd>
                    <dt class="text-gray-500">رقم الطلب الخارجي</dt>
                    <dd>{{ $order->external_order_id ?? '—' }}</dd>
                    <dt class="text-gray-500">الإجمالي</dt>
                    <dd>{{ number_format($order->total, 2) }} {{ $order->currency }}</dd>
                </dl>
                @if ($order->notes)
                    <p class="mt-3 rounded-md bg-gray-50 p-3 text-sm text-gray-600">{{ $order->notes }}</p>
                @endif
            </div>

            <div class="rounded-lg border bg-white shadow-sm">
                <div class="border-b px-5 py-3 text-sm font-semibold text-gray-700">المنتجات</div>
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-right text-gray-500">
                        <tr>
                            <th class="px-5 py-2 font-medium">المنتج</th>
                            <th class="px-5 py-2 font-medium">الكمية</th>
                            <th class="px-5 py-2 font-medium">السعر</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse ($order->items as $item)
                            <tr>
                                <td class="px-5 py-2">{{ $item->product_name }}</td>
                                <td class="px-5 py-2">{{ $item->quantity }}</td>
                                <td class="px-5 py-2">{{ number_format($item->price, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-5 py-4 text-center text-gray-400">لا يوجد منتجات مسجّلة</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="rounded-lg border bg-white shadow-sm">
                <div class="border-b px-5 py-3 text-sm font-semibold text-gray-700">سجل الحالات</div>
                <ul class="divide-y text-sm">
                    @forelse ($order->statusHistory as $entry)
                        <li class="px-5 py-3">
                            <span class="text-gray-500">{{ $entry->from_status ?? 'إنشاء' }} ←</span>
                            <x-order-status-badge :status="$entry->to_status" />
                            <span class="text-gray-400"> — {{ $entry->changedBy->name ?? 'النظام' }} — {{ $entry->created_at->diffForHumans() }}</span>
                        </li>
                    @empty
                        <li class="px-5 py-4 text-center text-gray-400">لا يوجد سجل بعد</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="rounded-lg border bg-white p-5 shadow-sm">
            <h2 class="mb-3 text-sm font-semibold text-gray-700">تغيير الحالة</h2>
            <form method="POST" action="{{ route('orders.status', [$business, $order]) }}" class="space-y-3">
                @csrf
                @method('PATCH')
                <select name="status" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach ($statuses as $status)
                        <option value="{{ $status }}" @selected($order->status === $status)>{{ $status }}</option>
                    @endforeach
                </select>
                <button type="submit" class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                    تحديث الحالة
                </button>
            </form>
        </div>
    </div>
</x-app-layout>
