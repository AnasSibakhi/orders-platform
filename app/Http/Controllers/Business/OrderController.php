<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Order;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Business $business, Request $request): View
    {
        $orders = Order::with(['customer', 'channel', 'assignedTo'])
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('orders.index', [
            'business' => $business,
            'orders' => $orders,
            'statuses' => Order::STATUSES,
            'activeStatus' => $request->get('status'),
        ]);
    }

    /**
     * $order is intentionally an int, not an implicitly-bound Order model.
     * Implicit binding resolves during SubstituteBindings, which can run
     * before IdentifyBusiness sets the tenant context — relying on the
     * global scope there would be timing-dependent and risky. Fetching
     * through $business->orders() is explicit and correct regardless of
     * middleware ordering: it can never return another business's order.
     */
    public function show(Business $business, int $order): View
    {
        $order = $business->orders()
            ->with(['customer', 'channel', 'assignedTo', 'items', 'statusHistory.changedBy'])
            ->findOrFail($order);

        return view('orders.show', [
            'business' => $business,
            'order' => $order,
            'statuses' => Order::STATUSES,
        ]);
    }

    public function updateStatus(Business $business, int $order, Request $request, AuditLogService $auditLog): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:'.implode(',', Order::STATUSES)],
        ]);

        $order = $business->orders()->findOrFail($order);

        $order->transitionTo($validated['status']);

        $auditLog->log('order.status_changed', Order::class, $order->id, ['status' => $validated['status']]);

        return back()->with('status', 'تم تحديث حالة الطلب.');
    }
}
