<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\Category;
use App\Models\Manufacturer;
use App\Models\Department;

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

            //I hope you meant "department".
            //The category, manufacturers, and departments can maybe be processed separately in different files and with different services for example, so it will then be easier for product processing.

            $categoryNames = [];
            $departmentNames = [];
            $manufacturerNames = [];

            //Read each line of the CSV.
            while (($row = fgetcsv($handle)) !== false) { //Checks if there are more rows to read in the CSV file. If there are no more rows or an error occurs, the loop stops.
                //Map the CSV columns.
                $productData = array_combine($header, $row);

                //Collect category, manufacturer, and department.
                $categoryNames[] = $productData['category_name'];
                $departmentNames[] = $productData['department_name'];
                $manufacturerNames[] = $productData['manufacturer_name'];
            }

            //Removing duplicates.
            $categoryNames = array_unique($categoryNames);
            $departmentNames = array_unique($departmentNames);
            $manufacturerNames = array_unique($manufacturerNames);

            //Map records.
            $categoriesToInsert = array_map(fn($name) => ['name' => $name, 'created_at' => now(), 'updated_at' => now()], $categoryNames);
            $departmentsToInsert = array_map(fn($name) => ['name' => $name, 'created_at' => now(), 'updated_at' => now()], $departmentNames);
            $manufacturersToInsert = array_map(fn($name) => ['name' => $name, 'created_at' => now(), 'updated_at' => now()], $manufacturerNames);

            //Inserting and checking for existing records.
            Category::upsert($categoriesToInsert, ['name'], ['created_at', 'updated_at']);
            Department::upsert($departmentsToInsert, ['name'], ['created_at', 'updated_at']);
            Manufacturer::upsert($manufacturersToInsert, ['name'], ['created_at', 'updated_at']);

            fclose($handle);
            $this->info("Data successfully imported from $filePath.");

        } else {
            $this->error("Failed to open the file: $filePath");
            return 1;
        }

        return 0;
    }
}
