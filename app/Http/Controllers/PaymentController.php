<?php

namespace App\Http\Controllers;

use App\Events\OrderPaid;
use App\Exceptions\InvalidRequestException;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function payByAlipay(Order $order, Request $request)
    {
        // 判断当前订单是否属于当前用户
        $this->authorize('own', $order);

        // 订单是否已支付或者已关闭
        if ($order->paid_at || $order->closed) {
            throw new InvalidRequestException('订单状态不正确');
        }

        // 调用支付宝网页支付
        return app('alipay')->web([
            'out_trade_no' => $order->no,
            'total_amount' => $order->total_amount,
            'subject' => '支付 Laravel Shop 的订单：'.$order->no
        ]);
    }

    public function alipayReturn()
    {
        try {

            app('alipay')->verify();

        } catch (\Exception $e) {

            return view('pages.error', ['msg' => '数据不正确']);

        }

        return view('pages.success', ['msg' => '支付成功']);
    }

    public function alipayNotify()
    {
        // 校验输入参数
        $data = app('alipay')->verify();

        $order = Order::where('no', $data->out_trade_no)->first();

        if (!$order) {
            return 'fail';
        }

        if ($order->paid_at) {
            return app('alipay')->success();
        }

        $order->update([
            'paid_at' => Carbon::now(),
            'payment_method' => 'alipay',
            'payment_no' => $data->trade_no
        ]);

        $this->afterPaid($order);

        return app('alipay')->success();
    }

    public function afterPaid(Order $order)
    {
        event(new OrderPaid($order));
    }
}
