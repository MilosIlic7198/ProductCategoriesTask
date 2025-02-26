<?php

namespace App\Services;

//Defined enums for default values specific to each field.
enum ProductDefault: string {
    case Unknown = 'Unknown';
    case NoInformation = 'N/I';
    case NoPrice = '0.00';
}

class ProductTransformerService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function transform(array $product): array
    {
        return [
            'product_number' => trim($product['product_number']),
            'category_name' => trim($product['category_name']),
            'department_name' => empty($product['department_name']) ? ProductDefault::Unknown->value : trim($product['department_name']),
            'manufacturer_name' => empty($product['manufacturer_name']) ? ProductDefault::Unknown->value : trim($product['manufacturer_name']),
            'upc' => empty($product['upc']) ? ProductDefault::NoInformation->value : trim($product['upc']),
            'sku' => empty($product['sku']) ? ProductDefault::NoInformation->value : trim($product['sku']),
            'regular_price' => empty($product['regular_price']) ? ProductDefault::NoPrice->value : trim($product['regular_price']),
            'sale_price' => empty($product['sale_price']) ? ProductDefault::NoPrice->value : trim($product['sale_price']),
            'description' => empty($product['description']) ? ProductDefault::NoInformation->value : trim($product['description']),
        ];
    }

    public function transformChunk(array $products): array
    {
        return array_map(function ($product) {
            return $this->transform($product);
        }, $products);
    }
}
