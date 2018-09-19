@extends('layouts.app')
@section('title', ($address->id ? '修改' : '新增').'收货地址')

@section('content')
    <div class="row">
        <div class="col-lg-10 col-lg-offset-1">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <h2 class="text-center">
                        {{($address->id ? '修改' : '新增').'收货地址'}}
                    </h2>
                </div>
                <div class="panel-body">
                    <!-- 输出后端报错开始 -->
                    @if (count($errors) > 0)
                        <div class="alert alert-danger">
                            <h4>有错误发生：</h4>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li><i class="glyphicon glyphicon-remove"></i> {{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    <!-- 输出后端报错结束 -->
                    <form class="form-horizontal" role="form" action="{{ $address->id ? route('user_addresses.update', ['user_address'=>$address->id]) : route('user_addresses.store') }}" method="post">
                        @if($address->id)
                        {{method_field('PUT')}}
                        @endif
                        <!-- 引入 csrf token 字段 -->
                        {{ csrf_field() }}
                        <!-- 注意这里多了 @change -->
                        <user-addresses-create-and-edit :init-value="{{ json_encode([$address->province, $address->city, $address->district]) }}"></user-addresses-create-and-edit>
                        <div class="form-group">
                            <label class="control-label col-sm-2">详细地址</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="address" value="{{ old('address', $address->address) }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-2">邮编</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="zip" value="{{ old('zip', $address->zip) }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-2">姓名</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="contact_name" value="{{ old('contact_name', $address->contact_name) }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-sm-2">电话</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="contact_phone" value="{{ old('contact_phone', $address->contact_phone) }}">
                            </div>
                        </div>
                        <div class="form-group text-center">
                            <button type="submit" class="btn btn-primary">提交</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@stop