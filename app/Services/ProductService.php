<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Carbon\Carbon;
use Exception;

class ProductService
{
    /**
     * Get all products.
     */
    public function getAllProducts()
    {
        return $this->confirm(function () {
            return Product::all();
        }, 'Products fetched successfully.');
    }

    /**
     * Get products of a specific category.
     */
    public function getProductsOfCategory(int $id)
    {
        return $this->confirmCategory($id, function ($category) {
            return $category->products;
        }, 'Products fetched successfully for the specified category.');
    }

    /**
     * Update a product.
     */
    public function updateProduct(int $id, array $data)
    {
        return $this->confirmProduct($id, function ($product) use ($data) {
            $product->update($data);
            return $product;
        }, 'Product updated successfully.');
    }

    /**
     * Delete a product.
     */
    public function deleteProduct(int $id)
    {
        return $this->confirmProduct($id, function ($product) {
            $product->delete();
            return null;
        }, 'Product deleted successfully.');
    }

    /**
     * Generate a CSV file for products of a specific category.
     */
    public function generateCsv(int $categoryId)
    {
        try {
            $category = Category::find($categoryId);
            if (!$category) {
                return $this->formatResponse(false, 'The category with this id does not exist.', null, false);
            }

            $categoryName = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($category->name));
            $categoryName = trim($categoryName, '_');
            $categoryName = preg_replace('/_+/', '_', $categoryName);

            $date = Carbon::now('CET')->format('Y_m_d-H_i');
            $filename = $categoryName . '_' . $date . '.csv';

            $storagePath = storage_path('app/public/csv');
            if (!file_exists($storagePath)) {
                mkdir($storagePath, 0775, true);
            }

            $csvContent = [];
            $csvContent[] = ['Product Number', 'UPC', 'SKU', 'Regular Price', 'Sale Price', 'Description'];

            foreach ($category->products as $product) {
                $csvContent[] = [
                    $product->product_number,
                    $product->upc,
                    $product->sku,
                    $product->regular_price,
                    $product->sale_price,
                    $product->description,
                ];
            }

            $path = $storagePath . '/' . $filename;
            $file = fopen($path, 'w');
            foreach ($csvContent as $line) {
                fputcsv($file, $line);
            }
            fclose($file);

            $fileUrl = url('storage/csv/' . $filename);
            return $this->formatResponse(true, 'The CSV file has successfully generated. You can copy link to the browser to download it.', $fileUrl);
        } catch (Exception $e) {
            return $this->formatResponse(false, 'An error occurred while generating the CSV.', null, true);
        }
    }

    /**
     * Execute a closure and return formated response.
     */
    private function confirm(callable $operation, string $successMessage)
    {
        try {
            $result = $operation();
            return $this->formatResponse(true, $successMessage, $result);
        } catch (Exception $e) {
            return $this->formatResponse(false, 'Server error occurred.', null, true);
        }
    }

    /**
     * Execute a closure with a category and handle not found case.
     *
     * Executes a given operation on a category if it exists, handling errors and formatting the response.
     *
     * @param int $id The id of the category.
     * @param callable $operation A function to execute on the found category, receiving the category as an argument.
     * @param string $successMessage The message to return on success.
     * @return array The formatted response.
     * 
     */
    private function confirmCategory(int $id, callable $operation, string $successMessage)
    {
        try {
            $category = Category::find($id);
            if (!$category) {
                return $this->formatResponse(false, 'The category with this id does not exist.', null, false);
            }
            $result = $operation($category);
            return $this->formatResponse(true, $successMessage, $result);
        } catch (Exception $e) {
            return $this->formatResponse(false, 'Server error occurred.', null, true);
        }
    }

    /**
     * Execute a closure with a product and handle not found case.
     */
    private function confirmProduct(int $id, callable $operation, string $successMessage)
    {
        try {
            $product = Product::find($id);
            if (!$product) {
                return $this->formatResponse(false, 'The product with this id does not exist.', null, false);
            }
            $result = $operation($product);
            return $this->formatResponse(true, $successMessage, $result);
        } catch (Exception $e) {
            return $this->formatResponse(false, 'Server error occurred.', null, true);
        }
    }

    /**
     * Format a response based on success or failure.
     */
    private function formatResponse(bool $success, string $message, $payload = null, bool $isServerError = false)
    {
        return [
            'success' => $success,
            'message' => $message,
            'payload' => $payload,
            'isServerError' => $isServerError,
        ];
    }
}
