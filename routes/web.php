<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// 首页
Route::get('/', 'PagesController@root')->name('root');

Auth::routes();

Route::group(['middleware' => 'auth'], function () {

    // 邮箱验证
    Route::get('/email_verify_notice', 'PagesController@emailVerifyNotice')->name('email_verify_notice');

    // 发送验证邮件
    Route::get('/email_verification/send', 'EmailVerificationController@send')->name('email_verification.send');
    // 验证邮箱
    Route::get('/email_verification/verify', 'EmailVerificationController@verify')->name('email_verification.verify');

    Route::group(['middleware' => 'email_verified'], function () {
        // 收货地址列表
        Route::get('user_addresses', 'UserAddressesController@index')->name('user_addresses.index');
        // 新增收货地址
        Route::get('user_addresses/create', 'UserAddressesController@create')->name('user_addresses.create');
        // 保存收货地址
        Route::post('user_addresses', 'UserAddressesController@store')->name('user_addresses.store');
        // 修改收货地址
        Route::get('user_addresses/{user_address}', 'UserAddressesController@edit')->name('user_addresses.edit');
        // 修改收货地址
        Route::put('user_addresses/{user_address}', 'UserAddressesController@update')->name('user_addresses.update');
        // 删除收货地址
        Route::delete('user_addresses/{user_address}', 'UserAddressesController@destroy')->name('user_addresses.destroy');
        // 添加收藏
        Route::post('products/{product}/favorite', 'ProductsController@favor')->name('products.favor');
        // 取消收藏
        Route::delete('products/{product}/favorite', 'ProductsController@disfavor')->name('products.disfavor');
        // 收藏列表
        Route::get('products/favorites', 'ProductsController@favorites')->name('products.favorites');
        // 添加购物车
        Route::post('cart', 'CartController@add')->name('cart.add');
        // 购物车列表
        Route::get('cart', 'CartController@index')->name('cart.index');
        // 删除购物车
        Route::delete('cart/{sku}', 'CartController@remove')->name('cart.remove');
        // 创建订单
        Route::post('orders', 'OrdersController@store')->name('orders.store');
        // 订单列表
        Route::get('orders', 'OrdersController@index')->name('orders.index');
        // 订单详情
        Route::get('orders/{order}', 'OrdersController@show')->name('orders.show');
        // 订单支付（支付宝网页支付）
        Route::get('payment/{order}/alipay', 'PaymentController@payByAlipay')->name('payment.alipay');
        // 支付宝支付前端回调
        Route::get('payment/alipay/return', 'PaymentController@alipayReturn')->name('payment.alipay.return');
    });
});

// 商品列表
Route::get('products', 'ProductsController@index')->name('products.index');
// 商品详情
Route::get('products/{product}', 'ProductsController@show')->name('products.show');
// 支付宝服务器端通知
Route::post('payment/alipay/notify', 'PaymentController@alipayNotify')->name('payment.alipay.notify');