<?php

namespace App\Listeners;

use App\Events\OrderPaid;
use App\Models\OrderItem;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

// implements ShouldQueue 代表此监听器是异步的
class UpdateProductSoldCount implements ShouldQueue
{

    /**
     * Handle the event.
     *
     * @param  OrderPaid  $event
     * @return void
     */
    public function handle(OrderPaid $event)
    {
        // 从此事件中取出该订单
        $order = $event->getOrder();

        // 循环遍历订单的信息
        foreach ($order->items as $orderItem) {

            $product = $orderItem->product;

            // 对商品进行销量计算
            $soldCount = OrderItem::query()
                ->where('product_id', $product->id)
                ->whereHas('order', function ($query) {
                    $query->whereNotNull('paid_at');
                })->sum('amount');

            //更新商品销量
            $product->update(['sold_count'=>$soldCount]);
        }
    }
}
