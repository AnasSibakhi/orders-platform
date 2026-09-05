<x-app-layout>
    <x-slot name="title">الفريق</x-slot>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-lg border bg-white shadow-sm lg:col-span-2">
            <div class="border-b px-5 py-3 text-sm font-semibold text-gray-700">أعضاء الفريق</div>
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-right text-gray-500">
                    <tr>
                        <th class="px-5 py-2 font-medium">الاسم</th>
                        <th class="px-5 py-2 font-medium">البريد الإلكتروني</th>
                        <th class="px-5 py-2 font-medium">الدور</th>
                        <th class="px-5 py-2 font-medium"></th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse ($members as $member)
                        <tr>
                            <td class="px-5 py-2">{{ $member->user->name }}</td>
                            <td class="px-5 py-2 text-gray-500">{{ $member->user->email }}</td>
                            <td class="px-5 py-2">{{ $member->role }}</td>
                            <td class="px-5 py-2 text-left">
                                @if (! $member->isOwner() && $member->user_id !== auth()->id() && $currentMembership->canManageTeam())
                                    <form method="POST" action="{{ route('team.destroy', [$business, $member]) }}"
                                          onsubmit="return confirm('إزالة هذا العضو من الفريق؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 hover:underline">إزالة</button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-6 text-center text-gray-400">لا يوجد أعضاء بعد</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($currentMembership->canManageTeam())
            <div class="rounded-lg border bg-white p-5 shadow-sm">
                <h2 class="mb-3 text-sm font-semibold text-gray-700">إضافة عضو</h2>
                <p class="mb-3 text-xs text-gray-400">
                    يجب أن يكون للشخص حساب مسجّل بالمنصة مسبقًا (أنشئ رابط دعوة للتسجيل التلقائي في مرحلة لاحقة).
                </p>
                <form method="POST" action="{{ route('team.store', $business) }}" class="space-y-3">
                    @csrf
                    <input type="email" name="email" placeholder="البريد الإلكتروني" required
                           class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <select name="role" class="block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="agent">Agent</option>
                        <option value="manager">Manager</option>
                    </select>
                    <button type="submit" class="w-full rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">
                        إضافة
                    </button>
                </form>
            </div>
        @endif
    </div>
</x-app-layout>
