<?php

namespace App\Services;

use App\Jobs\ProcessProductData;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Exception;
use Throwable;

class ProductImportService
{
    /**
     * Process a CSV file and dispatch jobs.
     */
    public function processCsv(string $filePath): Batch
    {
        //Check if file exists.
        if (!file_exists($filePath)) {
            throw new Exception("File not found: $filePath");
        }

        //Open the CSV file.
        $handle = fopen($filePath, 'r');
        if ($handle === false) { //Checks if the file was successfully opened. If the file cant be opened, the script wont continue processing the CSV.
            throw new Exception("Failed to open the file: $filePath");
        }

        //Get the header row to map columns.
        $header = fgetcsv($handle);

        $products = [];
        $jobs = [];
        $chunkSize = 50;

        //Read each line of the CSV.
        while (($row = fgetcsv($handle)) !== false) { //Checks if there are more rows to read in the CSV file. If there are no more rows or an error occurs, the loop stops.
            //Map the CSV columns.
            $productData = array_combine($header, $row);

            //Collect products.
            $products[] = [
                'product_number' => $productData['product_number'],
                'category_name' => $productData['category_name'],
                'department_name' => $productData['department_name'],
                'manufacturer_name' => $productData['manufacturer_name'],
                'upc' => $productData['upc'],
                'sku' => $productData['sku'],
                'regular_price' => $productData['regular_price'],
                'sale_price' => $productData['sale_price'],
                'description' => $productData['description'],
            ];

            //If the chunk size is reached, create a job.
            if (count($products) === $chunkSize) {
                $jobs[] = new ProcessProductData($products);
                $products = [];
            }
        }

        //Handle any remaining data.
        if (!empty($products)) {
            $jobs[] = new ProcessProductData($products);
        }

        fclose($handle);

        //Dispatch batch of jobs and return batch.
        return $this->dispatchBatch($jobs);
    }

    /**
     * Dispatch all job instances in a batch.
     *
     * @param array $jobs
     * @return Batch The batch object.
     */
    private function dispatchBatch(array $jobs): Batch
    {
        //Using batches and jobs might not be the best solution for simple small database insertions.
        //However if we need to insert large datasets and perform additional processing batches and jobs are very useful.
        //This approach is intended to demonstrate possibilities for future and more complex workflows.

        return Bus::batch($jobs)
        ->then(function (Batch $batch) {
            //
        })->catch(function (Batch $batch, Throwable $e) {
            //
        })->finally(function (Batch $batch) {
            //
        })
        ->name('Import CSV Products')
        ->dispatch();
    }
}
