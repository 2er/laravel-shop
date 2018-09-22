<?php

namespace App\Http\Requests;

use App\Models\ProductSku;
use Illuminate\Validation\Rule;

class OrderRequest extends Request
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            // 判断用户提交的地址 ID 是否存在于数据库，并且是属于用户的地址
            // 后面这个地址非常重要，否则恶意用户会以不同的地址 ID，不断提交订单来遍历出所有收货地址
            'address_id' => ['required', Rule::exists('user_addresses', 'id')->where('user_id', $this->user()->id)],
            'items' => ['required', 'array'],
            'items.*.sku_id' => [ // 检查 items 数组中的每一个sku_id
                'required',
                function ($attribute, $value, $fail) {
                    if (!$sku = ProductSku::find($value)) {
                        $fail('该商品不存在');
                        return;
                    }
                    if (!$sku->product->on_sale) {
                        $fail('该商品未上架');
                        return;
                    }
                    if ($sku->stock == 0) {
                        $fail('该商品已售完');
                        return;
                    }
                    // 获取当前索引
                    if (preg_match('/items.(\d+).sku_id/', $attribute, $m)) {
                        $index = $m[1];
                        // 根据索引获取当前商品提交的数量 amount
                        $amount = $this->input('items')[$index]['amount'];
                        if ($amount && $amount > $sku->stock) {
                            $fail('该商品库存不足');
                            return;
                        }
                    }
                }
            ],
            'items.*.amount' => ['required','integer','min:1']
        ];
    }
}
