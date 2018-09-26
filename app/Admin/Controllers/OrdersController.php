<?php

namespace App\Admin\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Http\Requests\Admin\HandleRefundRequest;
use App\Http\Requests\Request;
use App\Models\Order;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class OrdersController extends Controller
{
    use HasResourceActions;

    public function index()
    {
        return Admin::content(function (Content $content) {
            $content->header('订单列表');
            $content->body($this->grid());
        });
    }

    public function show(Order $order)
    {
        return Admin::content(function (Content $content) use ($order) {
            $content->header('订单详情');
            // body 方法可以接受 Laravel 的视图作为参数
            $content->body(view('admin.orders.show', compact('order')));
        });
    }

    public function ship(Order $order, Request $request)
    {
        // 判断该订单是否已付款
        if (!$order->paid_at) {
            throw new InvalidRequestException('该订单未付款');
        }

        // 判断该订单是否已发货
        if ($order->ship_status !== Order::SHIP_STATUS_PENDING) {
            throw new InvalidRequestException('该订单已发货');
        }

        // Laravel 5.5 之后，validate 方法可以返回校验过的值
        $data = $this->validate(
            $request,
            [
                'express_company' => ['required'],
                'express_no' => ['required']
            ],
            [],
            [
                'express_company' => '物流公司',
                'express_no' => '物流单号'
            ]
        );

        // 订单状态改成已发货，并且存入物流信息
        $order->update([
            'ship_status' => Order::SHIP_STATUS_DELIVERED,
            // 我们在 Order 模型的 $casts 属性里指明了 ship_data 是个数组
            // 因此这里可以直接把数组传过去
            'ship_data' => $data
        ]);

        // 返回上一页
        return redirect()->back();
    }

    public function handleRefund(HandleRefundRequest $request, Order $order)
    {
        // 判断订单状态是否正确
        if ($order->refund_status !== Order::REFUND_STATUS_APPLIED) {
            throw new InvalidRequestException('订单状态不正确');
        }

        // 是否同意退款
        if ($request->input('agree')) {
            // 调用退款逻辑
            $this->_refundOrder($order);
        } else {
            // 将拒绝的理由放到 extra 字段中
            $extra = $order->extra ?: [];
            $extra['refund_disagree_reason'] = $request->input('reason');
            // 将订单状态改为未退款
            $order->update([
                'refund_status' => Order::REFUND_STATUS_PENDING,
                'extra' => $extra
            ]);
        }

        return $order;
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Order::class, function (Grid $grid) {
            // 只展示已支付的，并且按支付时间倒序排序
            $grid->model()->whereNotNull('paid_at')->orderBy('paid_at', 'desc');

            $grid->no('订单流水号');
            // 展示关联关系的字段时，使用 column 方法
            $grid->column('user.name', '买家');

            $grid->total_amount('总金额')->sortable();
            $grid->paid_at('支付时间')->sortable();

            $grid->ship_status('物流')->display(function ($value) {
                return Order::$shipStatusMap[$value];
            });

            $grid->refund_status('退款状态')->display(function ($value) {
                return Order::$refundStatusMap[$value];
            });

            // 禁用创建订单按钮
            $grid->disableCreateButton();

            // 操作
            $grid->actions(function ($actions) {
                // 禁用删除和编辑按钮
                $actions->disableDelete();
                $actions->disableEdit();
            });

            // 批量操作
            $grid->tools(function ($tools) {
                // 禁用批量删除按钮
                $tools->batch(function ($batch) {
                    $batch->disableDelete();
                });
            });

        });
    }

    protected function _refundOrder(Order $order)
    {
        // 判断该订单的退款方式
        switch ($order->payment_method) {
            case 'wechat':
                // todo
                break;
            case 'alipay':
                // 生成退款单号
                $refundNo = Order::getAvailableRefundNo();
                // 调用支付宝退款
                $ret = app('alipay')->refund([
                    'out_trade_no' => $order->no,
                    'refund_amount' => $order->total_amount,
                    'out_request_no' => $refundNo
                ]);

                \Log::info(print_r($ret,true));

                // 根据支付宝的文档，如果返回值中有 sub_code 字段说明退款失败
                if ($ret->sub_code) {
                    // 将退款失败的 sub_code 保存到 extra 字段中
                    $extra = $order->extra ?: [];
                    $extra['refund_failed_code'] = $ret->sub_code;
                    $order->update([
                        'refund_no' => $refundNo,
                        'refund_status' => Order::REFUND_STATUS_FAILED,
                        'extra' => $extra
                    ]);
                } else {
                    // 将订单退款状态更新为退款成功
                    $order->update([
                        'refund_no' => $refundNo,
                        'refund_status' => Order::REFUND_STATUS_SUCCESS
                    ]);
                }

                break;
            default:
                throw new InvalidRequestException('未知的订单支付方式：'.$order->payment_method);
                break;
        }
    }

}
