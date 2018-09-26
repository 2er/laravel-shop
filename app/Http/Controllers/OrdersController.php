<?php

namespace App\Http\Controllers;

use App\Events\OrderReviewed;
use App\Exceptions\InvalidRequestException;
use App\Http\Requests\ApplyRefundRequest;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\SendReviewRequest;
use App\Models\Order;
use App\Models\UserAddress;
use App\Services\OrderService;
use Carbon\Carbon;
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

    public function received(Request $request, Order $order)
    {
        $this->authorize('own',$order);

        if ($order->ship_status !== Order::SHIP_STATUS_DELIVERED) {
            throw new InvalidRequestException('发货状态不正确');
        }

        $order->update(['ship_status'=>Order::SHIP_STATUS_RECEIVED]);

        // 返回订单信息
        return $order;
    }

    public function review(Order $order)
    {
        // 校验权限
        $this->authorize('own', $order);

        // 判断是否已经支付
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不可评价');
        }

        // 使用 load 方法加载关联数据，避免 N+1 性能问题
        $order = $order->load(['items.product','items.productSku']);

        return view('orders.review', compact('order'));
    }

    public function sendReview(SendReviewRequest $request, Order $order)
    {
        // 权限校验
        $this->authorize('own', $order);

        // 判断订单是否已经支付
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不可评价');
        }

        // 判断是否已经评价
        if ($order->reviewed) {
            throw new InvalidRequestException('该订单已评价，不能重复评价');
        }

        $reviews = $request->input('reviews');

        // 开启事务
        \DB::transaction(function () use ($reviews, $order) {

            // 遍历用户提交的评价
            foreach ($reviews as $review) {

                $orderItem = $order->items()->find($review['id']);

                // 保存评价
                $orderItem->update([
                    'rating' => $review['rating'],
                    'review' => $review['review'],
                    'reviewed_at' => Carbon::now(),
                ]);

            }

            // 更新订单评价状态
            $order->update(['reviewed'=>true]);

            // 触发商品评分数据更新
            event(new OrderReviewed($order));

        });

        return redirect()->back();
    }

    public function applyRefund(ApplyRefundRequest $request, Order $order)
    {
        // 检验订单是否属于该用户
        $this->authorize('own', $order);

        // 检查订单是否已经支付
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未支付，不能退款');
        }

        // 判断该订单的退款状态
        if ($order->refund_status !== Order::REFUND_STATUS_PENDING) {
            throw new InvalidRequestException('该订单已申请过退款，不能重复申请');
        }

        // 将输入的退款理由保存在 extra 字段中
        $extra = $order->extra ?: [];
        $extra['refund_reason'] = $request->input('reason');

        // 将订单退款状态改为已申请退款
        $order->update([
            'refund_status' => Order::REFUND_STATUS_APPLIED,
            'extra' => $extra
        ]);

        return $order;
    }
}
