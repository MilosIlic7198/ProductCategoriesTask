<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Throwable;

//Defined enums for default values specific to each field.
enum ProductDefault: string {
    case Unknown = 'Unknown';
    case NoInformation = 'N/I';
    case NoPrice = '0.00';
}

class ProcessProductData implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Example of property hooks. (PHP 8.4)
     * The array of products with validation and transformation via property hooks.
     * Property hooks work on individual properties, so we apply them to $products as an array, validating/transforming each row within the setter.
     * Property hooks let us define custom logic for getting and setting property values directly within the property declaration.
     * This property book is both writing and reading data.
     */
    public array $products {
        set {
            //Validate and transform each product.
            $this->products = array_map(function (array $product): array {
                if (empty($product['product_number'])) {
                    throw new InvalidArgumentException("Product number is required.");
                }
                if (empty($product['category_name'])) {
                    throw new InvalidArgumentException("Category name is required for product {$product['product_number']}.");
                }

                //Transformation.
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
            }, $value);
        }
        get {
            return $this->products;
        }
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $products)
    {
        //This triggers the setter hook immediately, validating and transforming the data when the job is instantiated.
        //For large chunks, for example from 500 to 1000, this is fine, but for more, i would consider separating validating/transforming or moving it in handle method instead to avoid memory issues during serialization.
        $this->products = $products;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(): void
    {
        try {
            $jobId = $this->job ? $this->job->getJobId() : 'N/A';
            Log::channel('jobs')->info('Starting product data processing', ['jobId' => $jobId, 'count' => count($this->products)]);            
            //Database transaction.
            DB::transaction(function () {
                //Collect category, department, and manufacturer values.
                $categories = $this->getUniqueValues('category_name');
                $departments =  $this->getUniqueValues('department_name');
                $manufacturers =  $this->getUniqueValues('manufacturer_name');

                $categoryIds = $this->insertAndGetIds('categories', $categories);
                $departmentIds = $this->insertAndGetIds('departments', $departments);
                $manufacturerIds = $this->insertAndGetIds('manufacturers', $manufacturers);

                $this->insertProducts($categoryIds, $departmentIds, $manufacturerIds);
            });

            Log::channel('jobs')->info('Finished product data processing', ['jobId' => $jobId]);
        } catch (Throwable $e) {

            Log::channel('jobs')->error('Failed to process product data in job', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'jobId' => $jobId,
            ]);
            $this->fail($e);
        }
    }

    /**
     * Collect unique category, department, and manufacturer values by key.
     */
    private function getUniqueValues(string $key): array
    {
        return array_unique(array_column($this->products, $key));
    }

    /**
     * Insert missing records and retrieve their IDs.
     *
     * @param  string  $table
     * @param  array  $arr
     * @return array
     */
    private function insertAndGetIds(string $table, array $names): array
    {
        $existingRecords = DB::table($table)->whereIn('name', $names)->pluck('id', 'name')->all();
        $missing = array_diff($names, array_keys($existingRecords));

        if (!empty($missing)) {
            $timestamp = now('CET');
            $insertData = array_map(fn(string $name): array => [
                'name' => $name,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ], $missing);
            DB::table($table)->insert($insertData);
        }

        return DB::table($table)->whereIn('name', $names)->pluck('id', 'name')->all();
    }

    /**
     * Insert or update products into the database.
     *
     * @param  array  $categories
     * @param  array  $departments
     * @param  array  $manufacturers
     * @return void
     */
    private function insertProducts(array $categories, array $departments, array $manufacturers): void
    {
        $productsToInsert = [];
        $timestamp = now('CET');

        $productsToInsert = array_map(fn(array $productData): array => [
            'product_number' => $productData['product_number'],
            'category_id' => $categories[$productData['category_name']],
            'department_id' => $departments[$productData['department_name']],
            'manufacturer_id' => $manufacturers[$productData['manufacturer_name']],
            'upc' => $productData['upc'],
            'sku' => $productData['sku'],
            'regular_price' => $productData['regular_price'],
            'sale_price' => $productData['sale_price'],
            'description' => $productData['description'],
            'created_at' => $timestamp,
            'updated_at' => $timestamp,
        ], $this->products);

        //Bulk insert products.
        DB::table('products')->upsert(
            $productsToInsert,
            ['product_number'], //Unique field that checks for duplicates.
            ['updated_at'] //Field to update if the duplicate exists.
        );
    }
}
