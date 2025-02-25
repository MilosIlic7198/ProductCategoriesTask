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
use Throwable;

class ProcessProductData implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $products;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $products)
    {
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
            Log::channel('jobs')->info('Starting product data processing', ['count' => count($this->products)]);            
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
            $jobId = $this->job ? $this->job->getJobId() : 'N/A';
            Log::channel('jobs')->info('Finished product data processing', ['jobId' => $jobId]);
        } catch (Throwable $e) {
            Log::channel('jobs')->error('Failed to process product data batch', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'batch_id' => $this->batchId ?? 'N/A',
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
        $existingRecords = DB::table($table)
        ->whereIn('name', $names)
        ->pluck('id', 'name')
        ->all();

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

        return DB::table($table)
        ->whereIn('name', $names)
        ->pluck('id', 'name')
        ->all();
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

        foreach ($this->products as $productData) {
            $productsToInsert[] = [
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
            ];
        }

        //Bulk insert products.
        DB::table('products')->upsert(
            $productsToInsert,
            ['product_number'], //Unique field that checks for duplicates.
            ['updated_at'] //Field to update if the duplicate exists.
        );
    }
}
