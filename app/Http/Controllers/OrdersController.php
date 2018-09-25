<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\UserAddress;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function store(OrderRequest $request, OrderService $orderService)
    {
        // 获取当前登录用户
        $user = $request->user();
        $address = UserAddress::find($request->input('address_id'));

        return $orderService->store($user, $address, $request->input('remark'), $request->input('items'));
    }

    public function index (Request $request)
    {
        $orders = Order::query()
            ->with(['items.product','items.productSku'])
            ->where('user_id', $request->user()->id)
            ->orderBy('created_at', 'DESC')
            ->paginate();

        return view('orders.index', compact('orders'));
    }

    public function show (Request $request, Order $order)
    {
        $this->authorize('own', $order);
        return view('orders.show', ['order' => $order->load(['items.product','items.productSku'])]);
    }
}
