<?php

namespace App\Listeners;

use App\Events\OrderReviewed;
use App\Models\OrderItem;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateProductRating implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  OrderReviewed  $event
     * @return void
     */
    public function handle(OrderReviewed $event)
    {
        $order = $event->getOrder();

        $orderItems = $order->items()->with(['product'])->where('order_id', $order->id)->get();

        foreach ($orderItems as $orderItem) {

            $result = OrderItem::query()
                ->where('product_id', $orderItem->product_id)
                ->whereHas('order', function ($query) {
                    $query->whereNotNull('paid_at');
                })
                ->first([
                    \DB::raw('count(*) AS review_count'),
                    \DB::raw('avg(rating) AS rating')
                ]);

            // 更新商品的评分数和评分
            $orderItem->product->update([
                'rating' => $result->rating,
                'review_count' => $result->review_count
            ]);
        }

    }
}
