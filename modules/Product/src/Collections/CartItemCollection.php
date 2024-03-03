<?php

namespace Modules\Product\Collections;

use Illuminate\Support\Collection;
use Modules\Product\DTOs\CartItem;
use Modules\Product\DTOs\ProductDto;
use Modules\Product\Models\Product;

class CartItemCollection
{
    /**
     * @param  \Illuminate\Support\Collection<CartItem>  $items
     */
    public function __construct(
        protected Collection $items
    ) {
    }

    public static function fromCheckoutData(array $data): CartItemCollection
    {
		/** Version from screencast */
//        $cartItems = collect($data)->map(function (array $productDetails) {
//            return new CartItem(
//                ProductDto::fromEloquentModel(Product::find($productDetails['id'])),
//                $productDetails['quantity']
//            );
//        });
//
//        return new self($cartItems);

		/** Optimized version */
		$cartData = collect($data);
		$products = Product::whereIn('id', $cartData->pluck('id'))->get();

		$cartItems = $products->map(function(Product $productModel) use ($cartData) {
			$cartItem = $cartData->where('id', $productModel->id)->first();

			return new CartItem(
				ProductDto::fromEloquentModel($productModel),
				$cartItem->quantity
			);
		});

		return new self($cartItems);
    }

    public static function fromProduct(ProductDto $product, int $quantity = 1): CartItemCollection
    {
        return new self(collect([
            new CartItem($product, $quantity)
        ]));
    }

    public function totalInCents(): int
    {
        return $this->items->sum(fn(CartItem $cartItem) => $cartItem->quantity * $cartItem->product->priceInCents
        );
    }

    /**
     * @return \Illuminate\Support\Collection<CartItem>
     */
    public function items(): Collection
    {
        return $this->items;
    }
}
