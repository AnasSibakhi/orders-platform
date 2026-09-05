<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Business $business, Request $request): View
    {
        $customers = $business->customers()
            ->withCount('orders')
            ->when(
                $request->filled('q'),
                fn ($q) => $q->where(function ($q) use ($request) {
                    $term = '%'.$request->string('q').'%';
                    $q->where('name', 'like', $term)
                        ->orWhere('email', 'like', $term)
                        ->orWhere('phone_normalized', 'like', $term);
                })
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('customers.index', [
            'business' => $business,
            'customers' => $customers,
        ]);
    }

    public function show(Business $business, int $customer): View
    {
        $customer = $business->customers()
            ->with(['orders' => fn ($q) => $q->latest(), 'channelIdentities.channel'])
            ->findOrFail($customer);

        return view('customers.show', [
            'business' => $business,
            'customer' => $customer,
        ]);
    }
}
