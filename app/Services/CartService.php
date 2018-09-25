<?php
/**
 * Created by PhpStorm.
 * User: wuchuanchuan
 * Date: 2018/9/25
 * Time: 上午10:58
 */
namespace App\Services;

use App\Models\CartItem;
use Auth;

class CartService
{
    public function get()
    {
        return Auth::user()->cartItems()->with(['productSku.product'])->get();
    }

    public function add($skuId, $amount)
    {
        $user = Auth::user();

        if ($cartItem = $user->cartItems()->where('product_sku_id', $skuId)->first()) {
            $cartItem->update(['amount' => $cartItem->amount + $amount]);
        } else {
            $cartItem = new CartItem(['amount' => $amount]);
            $cartItem->user()->associate($user);
            $cartItem->productSku()->associate($skuId);
            $cartItem->save();
        }

        return $cartItem;
    }

    public function remove($skuIds)
    {
        if (!is_array($skuIds)) {
            $skuIds = [$skuIds];
        }

        return Auth::user()->cartItems()->whereIn('product_sku_id', $skuIds)->delete();
    }
}