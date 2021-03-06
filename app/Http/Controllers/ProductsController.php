<?php

namespace App\Http\Controllers;

use App\Exceptions\InvalidRequestException;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use App\Models\Product;

class ProductsController extends Controller
{
    public function index (Request $request)
    {
        // 创建一个查询构造器
        $builder = Product::query()->where('on_sale', true);
        // 判断是否有search参数提交
        if ($search = $request->input('search', '')) {
            $like = '%'.$search.'%';
            // 模糊搜索商品标题、商品详情、SKU 标题、SKU描述
            $builder->where(function ($query) use ($like) {
                $query->where('title', 'like', $like)
                    ->orWhere('description', 'like', $like)
                    ->orWhereHas('skus', function ($query) use ($like) {
                        $query->where('title', 'like', $like)
                            ->orWhere('description', 'like', $like);
                    });
            });
        }

        // 是否有提交 order 参数，如果有就赋值给 $order 变量
        if ($order = $request->input('order', '')) {
            // 是否以 _desc 或者 _asc 结尾
            if (preg_match('/^(.+)_(desc|asc)$/', $order, $m)) {
                // 如果开头是这 3 个字符串之一，则是合法的排序
                if (in_array($m[1], ['price', 'sold_count', 'rating'])) {
                    // 根据传入的排序值来构造排序参数
                    $builder->orderBy($m[1], $m[2]);
                }
            }
        }
        $products = $builder->paginate(16);

        $filters = compact('search', 'order');

        return view('products.index', compact('products', 'filters'));
    }

    public function show (Request $request, Product $product)
    {
        // 判断商品是否上架
        if (!$product->on_sale) {
            throw new InvalidRequestException('商品未上架');
        }

        $favored = false;
        if ($request->user())
        {
            if ($request->user()->favoriteProducts()->find($product->id)) {
                $favored = true;
            }
        }

        // 获取评价
        $reviews = OrderItem::query()
            ->with(['order.user', 'productSku'])
            ->where('product_id', $product->id)
            ->whereNotNull('reviewed_at')
            ->orderBy('reviewed_at', 'desc')
            ->limit(10)
            ->get();

        return view('products.show', compact('product', 'favored', 'reviews'));
    }

    public function favor (Request $request, Product $product)
    {
        if ($request->user()->favoriteProducts()->find($product->id)) {
            return [];
        }

        $request->user()->favoriteProducts()->attach($product->id);
        return [];
    }

    public function disfavor (Request $request, Product $product)
    {
        $request->user()->favoriteProducts()->detach($product->id);
        return [];
    }

    public function favorites (Request $request)
    {
        $products = $request->user()->favoriteProducts()->paginate(16);

        return view('products.favorites', ['products' => $products]);
    }
}
