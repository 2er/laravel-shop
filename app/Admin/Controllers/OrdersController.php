<?php

namespace App\Admin\Controllers;

use App\Exceptions\InvalidRequestException;
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

}
