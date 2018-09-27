<?php
/**
 * Created by PhpStorm.
 * User: wuchuanchuan
 * Date: 2018/9/25
 * Time: 上午11:13
 */

namespace App\Services;

use App\Exceptions\CouponCodeUnavailableException;
use App\Exceptions\InvalidRequestException;
use App\Jobs\CloseOrder;
use App\Models\CouponCode;
use App\Models\Order;
use App\Models\ProductSku;
use App\Models\User;
use App\Models\UserAddress;
use Auth;
use Carbon\Carbon;

class OrderService
{
    public function store(User $user, UserAddress $address, $remark, $items, CouponCode $coupon = null)
    {

        // 如果传入了优惠券，则要判断其有效性
        if ($coupon) {
            $coupon->checkAvailable($user);
        }

        // 开启一个数据库事务
        $order = \DB::transaction(function () use ($user, $address, $remark, $items, $coupon) {
            // 更新地址的最后使用时间
            $address->update(['last_used_at'=>Carbon::now()]);
            // 创建订单
            $order = new Order([
                'address' => [
                    'address' => $address->full_address,
                    'zip' => $address->zip,
                    'contact_name' => $address->contact_name,
                    'contact_phone' => $address->contact_phone
                ],
                'remark' => $remark,
                'total_amount' => 0
            ]);

            // 关联当前用户
            $order->user()->associate($user);
            // 保存
            $order->save();

            $totalAmount = 0;

            // 遍历用户提交的 SKU
            foreach ($items as $data) {
                $sku = ProductSku::find($data['sku_id']);
                // 创建一个 orderItem 并直接与 order 关联
                $item = $order->items()->make([
                    'amount' => $data['amount'],
                    'price' => $sku->price
                ]);

                $item->product()->associate($sku->product_id);
                $item->productSku()->associate($sku);

                $item->save();

                $totalAmount += $sku->price * $data['amount'];

                if ($sku->decreaseStock($data['amount']) <= 0) {
                    throw new InvalidRequestException('改商品库存不足');
                }
            }

            if ($coupon) {
                // 总金额已经计算出来了，检查是否符合优惠券规则
                $coupon->checkAvailable($user, $totalAmount);
                $totalAmount = $coupon->getAdjustedPrice($totalAmount);
                // 将订单和优惠券关联
                $order->couponCode()->associate($coupon);
                // 增加优惠券的使用量
                if ($coupon->changeUsed() <= 0) {
                    throw new CouponCodeUnavailableException('该优惠券已经被兑完');
                }
            }

            // 更新订单总金额
            $order->update(['total_amount'=>$totalAmount]);

            // 删除购物车信息
            $skuIds = collect($items)->pluck('sku_id')->all();

            app(CartService::class)->remove($skuIds);

            return $order;

        });

        dispatch(new CloseOrder($order, config('app.order_ttl')));

        return $order;
    }
}