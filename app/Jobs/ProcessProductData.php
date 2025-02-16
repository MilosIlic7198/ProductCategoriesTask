<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use Illuminate\Support\Facades\DB;

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
    public function handle()
    {
        try {
            //Collect unique category, department, and manufacturer names.
            $categoriesCol = array_column($this->products, 'category_name');
            $departmentsCol =  array_column($this->products, 'department_name');
            $manufacturersCol =  array_column($this->products, 'manufacturer_name');
            //
            $categories = array_unique($categoriesCol);
            $departments = array_unique($departmentsCol);
            $manufacturers = array_unique($manufacturersCol);

            //Database transaction.
            DB::beginTransaction();
            $this->process($categories, $departments, $manufacturers);
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            dd($e->getMessage(), $e->getLine());
        }
    }

    /**
     * Process the job.
     *
     * @return void
     */
    private function process($categories, $departments, $manufacturers) {
        //Bulk insert and get ids.
        $categories = $this->insertAndGetIds('categories', $categories);
        $departments = $this->insertAndGetIds('departments', $departments);
        $manufacturers = $this->insertAndGetIds('manufacturers', $manufacturers);

        $this->insertProducts($categories, $departments, $manufacturers);
    }

    /**
     * Insert missing records and retrieve their IDs.
     *
     * @param  string  $table
     * @param  array  $arr
     * @return array
     */
    private function insertAndGetIds($table, $arr)
    {
        //Get existing records.
        $existingRecords = DB::table($table)
        ->whereIn('name', $arr)
        ->pluck('id', 'name')
        ->toArray();

        //Insert missing records if any.
        $missing = array_diff($arr, array_keys($existingRecords));
        if ($missing) {
            $currentTimestamp = now('CET');
            $insertData = array_map(fn($name) => ['name' => $name, 'created_at' => $currentTimestamp, 'updated_at' => $currentTimestamp], $missing);
            DB::table($table)->insert($insertData);
        }

        //Return records.
        return DB::table($table)
        ->whereIn('name', $arr)
        ->pluck('id', 'name')
        ->toArray();
    }

    /**
     * Insert products into the database.
     *
     * @param  array  $categories
     * @param  array  $departments
     * @param  array  $manufacturers
     * @return void
     */
    private function insertProducts($categories, $departments, $manufacturers) {
        $productsToInsert = [];
        $currentTimestamp = now('CET');

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
                'created_at' => $currentTimestamp,
                'updated_at' => $currentTimestamp,
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
