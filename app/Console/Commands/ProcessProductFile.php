<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Services\ProductImportService;
use Exception;

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
    public function __construct(ProductImportService $importService)
    {
        parent::__construct();
        $this->importService = $importService;
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        //Get the file name from the argument.
        $file = $this->argument('file');
        $filePath = storage_path('app/' . $file); //Access file in storage/app.

        $this->info("Importing data from CSV: $filePath");

        try {
            $batch = $this->importService->processCsv($filePath);
            $this->info("Import started successfully! Batch ID: {$batch->id}");
            return 0;
        } catch (Exception $e) {
            $errMsg = $e->getMessage();
            $this->error("Error has occurred while processing csv flle: \n {$errMsg}");
            return 1;
        }
    }
}
