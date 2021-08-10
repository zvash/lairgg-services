<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Product;
use App\Repositories\ShopRepository;
use App\Traits\Responses\ResponseMaker;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    use ResponseMaker;

    /**
     * @param Request $request
     * @param ShopRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function products(Request $request, ShopRepository $repository)
    {
        $sortBy = $request->get('by', 'is_featured');
        $direction = $request->get('direction', 'desc');
        return $this->success($repository->productList($sortBy, $direction));
    }

    /**
     * @param Request $request
     * @param Product $product
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getProductById(Request $request, Product $product)
    {
        if (
            $product->status == ProductStatus::COMING_SOON ||
            ($product->status == ProductStatus::ACTIVE && $product->quantity > 0)
        ) {
            return $this->success($product->load('images'));
        }
        return $this->failNotFound();
    }
}
