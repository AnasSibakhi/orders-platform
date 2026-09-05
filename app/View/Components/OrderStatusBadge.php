<?php

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class OrderStatusBadge extends Component
{
    public function __construct(public string $status) {}

    public function colorClasses(): string
    {
        return match ($this->status) {
            'new' => 'bg-amber-100 text-amber-700',
            'processing' => 'bg-blue-100 text-blue-700',
            'paid' => 'bg-indigo-100 text-indigo-700',
            'shipped' => 'bg-purple-100 text-purple-700',
            'completed' => 'bg-green-100 text-green-700',
            'cancelled' => 'bg-red-100 text-red-700',
            default => 'bg-gray-100 text-gray-700',
        };
    }

    public function label(): string
    {
        return match ($this->status) {
            'new' => 'جديد',
            'processing' => 'قيد المعالجة',
            'paid' => 'مدفوع',
            'shipped' => 'تم الشحن',
            'completed' => 'مكتمل',
            'cancelled' => 'ملغي',
            default => $this->status,
        };
    }

    public function render(): View
    {
        return view('components.order-status-badge');
    }
}
