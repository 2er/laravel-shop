<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Http\Requests\OrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductSku;
use App\Models\UserAddress;
use Carbon\Carbon;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function store(OrderRequest $request)
    {
        // 获取当前登录用户
        $user = $request->user();

        $order = \DB::transaction(function () use ($request,$user) {
            // 获取收货地址信息
            $address = UserAddress::find($request->input('address_id'));
            // 更新此收货地址的最后使用时间
            $address->update(['last_used_at' => Carbon::now()]);
            // 创建一个订单
            $order = new Order([
                'address' => [
                    'address' => $address->full_address,
                    'zip' => $address->zip,
                    'contact_name' => $address->contact_name,
                    'contact_phone' => $address->contact_phone
                ],
                'remark' => $request->input('remark'),
                'total_amount' => 0
            ]);

            // 订单关联当前用户
            $order->user()->associate($user);
            // 写入数据库
            $order->save();

            $totalAmount = 0;

            $items = $request->input('items');

            // 遍历用户提交的 items
            foreach ($items as $data) {
                // 获取 sku 信息
                $sku = ProductSku::find($data['sku_id']);
                // 创建一个 OrderItem ,并直接与 Order 关联
                $item = $order->items()->make([
                    'price' => $sku->price,
                    'amount' => $data['amount']
                ]);
                $item->product()->associate($sku->product_id);
                $item->productSku()->associate($sku);
                $item->save();
                $totalAmount += $sku->price * $data['amount'];

                if ($sku->decreaseStock($data['amount']) <= 0) {
                    throw new InvalidRequestException('该商品库存不足');
                }
            }

            // 更新订单总金额
            $order->update(['total_amount' => $totalAmount]);

            // 将下单商品从购物车中移除
            $skuIds = collect($request->input('items'))->pluck('sku_id');
            $user->cartItems()->whereIn('product_sku_id',$skuIds)->delete();

            return $order;
        });

        return $order;
    }
}
