<?php

namespace App\Http\Controllers;


use App\Http\Requests\AddCartRequest;
use App\Models\CartItem;

class CartController extends Controller
{
    public function add (AddCartRequest $request)
    {
        $user = $request->user();
        $skuId  = $request->input('sku_id');

        // 从数据库中查询该商品是否已存在购物车中
        if ($cartItem = $user->cartItems()->where('product_sku_id', $skuId)->first()) {
            // 如果存在则叠加商品数量
            $cartItem->amount += $request->input('amount');
            $cartItem->save();
        } else {
            // 创建一个新的购物车记录
            $cartItem = new CartItem($request->only('amount'));
            $cartItem->user()->associate($user);
            $cartItem->productSku()->associate($skuId);
            $cartItem->save();
        }

        return [];
    }
}
