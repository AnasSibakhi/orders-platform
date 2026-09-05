<x-app-layout>
    <x-slot name="title">{{ $customer->name ?? 'عميل' }}</x-slot>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-lg border bg-white p-5 shadow-sm">
            <h2 class="mb-3 text-sm font-semibold text-gray-700">بيانات العميل</h2>
            <dl class="space-y-2 text-sm">
                <div><dt class="text-gray-500">الاسم</dt><dd>{{ $customer->name ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">الجوال</dt><dd>{{ $customer->phone_normalized ?? '—' }}</dd></div>
                <div><dt class="text-gray-500">البريد الإلكتروني</dt><dd>{{ $customer->email ?? '—' }}</dd></div>
            </dl>

            @if ($customer->channelIdentities->isNotEmpty())
                <h3 class="mb-2 mt-4 text-xs font-semibold uppercase text-gray-400">مرتبط عبر</h3>
                <ul class="space-y-1 text-sm text-gray-600">
                    @foreach ($customer->channelIdentities as $identity)
                        <li>{{ $identity->channel->name ?? '—' }} — {{ $identity->external_id }}</li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="rounded-lg border bg-white shadow-sm lg:col-span-2">
            <div class="border-b px-5 py-3 text-sm font-semibold text-gray-700">طلبات هذا العميل</div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-right text-gray-500">
                    <tr>
                        <th class="px-5 py-2 font-medium">#</th>
                        <th class="px-5 py-2 font-medium">الحالة</th>
                        <th class="px-5 py-2 font-medium">الإجمالي</th>
                        <th class="px-5 py-2 font-medium">التاريخ</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($customer->orders as $order)
                        <tr>
                            <td class="px-5 py-2">
                                <a href="{{ route('orders.show', [$business, $order]) }}" class="text-indigo-600 hover:underline">#{{ $order->id }}</a>
                            </td>
                            <td class="px-5 py-2"><x-order-status-badge :status="$order->status" /></td>
                            <td class="px-5 py-2">{{ number_format($order->total, 2) }} {{ $order->currency }}</td>
                            <td class="px-5 py-2 text-gray-500">{{ $order->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-6 text-center text-gray-400">لا يوجد طلبات بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
