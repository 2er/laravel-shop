<?php

use Faker\Generator as Faker;

$factory->define(App\Models\CouponCode::class, function (Faker $faker) {

    // 首先随机取得一个类型
    $type = array_random(array_keys(\App\Models\CouponCode::$typeMap));
    // 取得类型后生成对应的折扣
    $value = $type === \App\Models\CouponCode::TYPE_FIXED ? random_int(1, 200) : random_int(1, 50);
    // 如果是固定金额，订单金额必须比优惠金额高 0.01 元
    if ($type === \App\Models\CouponCode::TYPE_FIXED) {
        $minAmount = $value + 0.01;
    } else {
        // 如果是百分比折扣，则有 50% 的概率不需要最低订单金额
        if (random_int(0, 100) < 50) {
            $minAmount = 0;
        } else {
            $minAmount = random_int(100, 1000);
        }
    }

    return [
        'name' => join(' ', $faker->words),
        'code' => \App\Models\CouponCode::findAvailableCode(),
        'type'       => $type,
        'value'      => $value,
        'total'      => 1000,
        'used'       => 0,
        'min_amount' => $minAmount,
        'not_before' => null,
        'not_after'  => null,
        'enabled'    => true,
    ];
});
