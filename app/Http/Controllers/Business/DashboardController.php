<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\Channel;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Business $business): View
    {
        $stats = [
            'total_orders' => Order::count(),
            'new_orders' => Order::where('status', Order::STATUS_NEW)->count(),
            'processing_orders' => Order::where('status', Order::STATUS_PROCESSING)->count(),
            'total_customers' => Customer::count(),
            'connected_channels' => Channel::where('status', Channel::STATUS_CONNECTED)->count(),
        ];

        $recentOrders = Order::with(['customer', 'channel'])
            ->latest()
            ->limit(8)
            ->get();

        return view('business.dashboard', [
            'business' => $business,
            'stats' => $stats,
            'recentOrders' => $recentOrders,
        ]);
    }
}
