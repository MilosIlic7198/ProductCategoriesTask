<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Jobs\ProcessProductData;

use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Throwable;

class ProcessProductFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:import {file}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process and import products from a CSV file into the database!';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        //Get the file name from the argument.
        $file = $this->argument('file');
        $filePath = storage_path('app/' . $file); //Access file in storage/app.

        //Check if file exists.
        if (!file_exists($filePath)) {
            $this->error("File not found: $filePath");
            return 1;
        }

        //Open the CSV file.
        if (($handle = fopen($filePath, 'r')) !== false) { //Checks if the file was successfully opened. If the file cant be opened, the script wont continue processing the CSV.

            //Get the header row to map columns.
            $header = fgetcsv($handle);

            $this->info("Importing data from CSV...");

            //I hope you meant "department" in the file.
            //The category, manufacturers, and departments can maybe be processed separately in different files and with different services for example, so it will then be easier for product processing.

            $products = [];

            $chunkSize = 50;
            $counter = 0; //Row tracker.
            $jobs = [];

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
                if (++$counter % $chunkSize == 0) {
                    $jobs[] = new ProcessProductData($products);

                    $products = [];
                }
            }

            //Handle any remaining data.
            if (count($products) > 0) {
                $jobs[] = new ProcessProductData($products);
            }

            //Dispatch batch of jobs.
            $this->dispatchBatch($jobs);

            $this->info("Import completed successfully!");

            fclose($handle);
        } else {
            $this->error("Failed to open the file: $filePath");
            return 1;
        }

        return 0;
    }

    /**
     * Dispatch all job instances in a batch.
     *
     * @param array $jobs
     * @return void
     */
    protected function dispatchBatch(array $jobs)
    {
        //Using batches and jobs might not be the best solution for simple small database insertions.
        //However if we need to insert large datasets and perform additional processing batches and jobs are very useful.
        //This approach is intended to demonstrate possibilities for future and more complex workflows.

        Bus::batch($jobs)
        ->then(function (Batch $batch) {})
        ->catch(function (Batch $batch, Throwable $e) {})
        ->finally(function (Batch $batch) {})
        ->name("Import CSV!")
        ->dispatch();
    }
}
