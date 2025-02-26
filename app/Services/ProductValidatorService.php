<?php

namespace App\Services;

use InvalidArgumentException;

class ProductValidatorService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }
    
    public function validate(array $product): void
    {
        if (empty($product['product_number'])) {
            throw new InvalidArgumentException('Product number is required.');
        }
        if (empty($product['category_name'])) {
            throw new InvalidArgumentException("Category name is required for product {$product['product_number']}.");
        }
    }
}
