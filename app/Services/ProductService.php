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
    public function getAllProducts(): array
    {
        return $this->confirm(function () {
            return Product::all();
        }, 'Products fetched successfully.');
    }

    /**
     * Get products of a specific category.
     */
    public function getProductsOfCategory(Category $category): array
    {
        return $this->confirm(function () use ($category) {
            return $category->products;
        }, 'Products fetched successfully for the specified category.');
    }

    /**
     * Update a product.
     */
    public function updateProduct(Product $product, array $data): array
    {
        return $this->confirm(function () use ($product, $data) {
            //Set updated_at to current CET time.
            $data['updated_at'] = now('CET');
            $product->update($data);
            return $product;
        }, 'Product updated successfully.');
    }

    /**
     * Delete a product.
     */
    public function deleteProduct(Product $product): array
    {
        return $this->confirm(function () use ($product) {
            $product->delete();
            return null;
        }, 'Product deleted successfully.');
    }

    /**
    * Generate a CSV file for products of a specific category.
    *
    * @param Category $category The instance of the category
    * @return array The formatted response.
    */
    public function generateCsv(Category $category): array
    {
        try {
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
            return $this->formatResponse(false, 'An error occurred while generating the CSV.', null);
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
    private function confirm(callable $operation, string $successMessage): array
    /**
     * The callable is a way to pass a function as an argument that the method can execute.
     */
    {
        try {
            $result = $operation();
            return $this->formatResponse(true, $successMessage, $result);
        } catch (Exception $e) {
            return $this->formatResponse(false, 'Server error occurred.', null);
        }
    }

    /**
     * Format a response based on success or failure.
     */
    private function formatResponse(bool $success, string $message, $payload = null): array
    {
        return [
            'success' => $success,
            'message' => $message,
            'payload' => $payload,
        ];
    }
}
