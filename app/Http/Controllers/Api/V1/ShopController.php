<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ProductStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Order;
use App\Product;
use App\Repositories\CountryRepository;
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

    /**
     * @param StoreOrderRequest $request
     * @param ShopRepository $repository
     * @param CountryRepository $countryRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function storeOrder(StoreOrderRequest $request, ShopRepository $repository, CountryRepository $countryRepository)
    {
        $user = $request->user();
        $inputs = $request->validated();
        try {
            return $this->success($repository->createPendingOrder($user, $inputs, $countryRepository));
        } catch (\Exception $exception) {
            return $this->failMessage($exception->getMessage(), 400);
        }
    }

    /**
     * @param Request $request
     * @param Order $order
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getOrder(Request $request, Order $order)
    {
        $user = $request->user();
        if ($user->id == $order->user_id) {
            return $this->success($order);
        }
        return $this->failNotFound();
    }

    /**
     * @param CountryRepository $repository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function getCountries(CountryRepository $repository)
    {
        return $this->success($repository->getAllAsArray());
    }
}
