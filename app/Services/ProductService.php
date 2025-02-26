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
    *
    * @param int $categoryId The ID of the category
    * @return array The formatted response.
    */
    public function generateCsv(int $categoryId)
    {
        try {
            $category = Category::find($categoryId);
            if (!$category) {
                return $this->formatResponse(false, 'The category with this id does not exist.', null, false);
            }
            
            $filename = $this->generateFilename($category->name);
            $storagePath = $this->ensureStorageDirectory();
            $csvData = $this->prepareCsvData($category->products);

            $this->writeCsvFile($storagePath, $filename, $csvData);

            return $this->formatResponse(
                true,
                'The CSV file has successfully generated. You can copy link to the browser to download it.',
                url('storage/csv/' . $filename)
            );
        } catch (Exception $e) {
            return $this->formatResponse(false, 'An error occurred while generating the CSV.', null, true);
        }
    }


    /**
    * Generate filename based on category name and timestamp.
    */
    private function generateFilename(string $categoryName): string
    {
        //Structuring the file name.
        $sanitizedName = preg_replace('/[^a-zA-Z0-9]/', '_', strtolower($categoryName));
        $sanitizedName = trim($sanitizedName, '_'); //This part was not fun at all.
        $sanitizedName = preg_replace('/_+/', '_', $sanitizedName);
        
        $timestamp = Carbon::now('CET')->format('Y_m_d-H_i');
        return "{$sanitizedName}_{$timestamp}.csv";
    }

    /**
    * Ensure the storage directory exists and return its path.
    */
    private function ensureStorageDirectory(): string
    {
        //Path for csv.
        $path = storage_path('app/public/csv');
        // //Check if the folder exists.
        if (!file_exists($path)) {
            mkdir($path, 0775, true); //Create it if not.
        }
        return $path;
    }

    /**
    * Prepare CSV data from products collection.
    */
    private function prepareCsvData($products): array
    {
        //Headers.
        $csvData = [['Product Number', 'UPC', 'SKU', 'Regular Price', 'Sale Price', 'Description']];
        //Adding content.
        foreach ($products as $product) {
            $csvData[] = [
                $product->product_number,
                $product->upc,
                $product->sku,
                $product->regular_price,
                $product->sale_price,
                $product->description,
            ];
        }
        return $csvData;
    }

    /**
    * Write data to CSV file.
    */
    private function writeCsvFile(string $storagePath, string $filename, array $csvData): void
    {
        //Generate CSV file and store in storage folder.
        $filePath = "$storagePath/$filename";
        $file = fopen($filePath, 'w');
        //Inserting content.
        foreach ($csvData as $line) {
            fputcsv($file, $line);
        }
        fclose($file);
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
