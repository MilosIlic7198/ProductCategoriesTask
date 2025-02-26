<?php

namespace App\Services;

use InvalidArgumentException;

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
        //Mutations.
        $data = [
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

        //Type casting.
        return [
            'product_number' => (string) $data['product_number'],
            'category_name' => (string) $data['category_name'],
            'department_name' => (string) $data['department_name'],
            'manufacturer_name' => (string) $data['manufacturer_name'],
            'upc' => $this->castToInt($data['upc'], 'UPC'),
            'sku' => $this->castToInt($data['sku'], 'SKU'),
            'regular_price' => $this->castToFloat($data['regular_price'], 'regular_price'),
            'sale_price' => $this->castToFloat($data['sale_price'], 'sale_price'),
            'description' => (string) $data['description'],
        ];
    }

    private function castToInt(string $value, string $fieldName): int
    {
        if ($value === ProductDefault::NoInformation->value) {
            return 0;
        } elseif (!is_numeric($value) || (int)$value != $value) {
            throw new InvalidArgumentException("Failed to cast $fieldName to integer: $value");
        } else {
            return (int) $value;
        }
    }

    private function castToFloat(string $value, string $fieldName): float{
        if ($value === ProductDefault::NoPrice->value) {
            return 0.00;
        } elseif (!is_numeric($value)) {
            throw new InvalidArgumentException("Failed to cast $fieldName to float: $value");
        } else {
            return (float) $value;
        }
    }
}
