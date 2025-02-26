<?php

namespace App\Services;

use App\Jobs\ProcessProductData;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;
use Exception;
use Throwable;

class ProductImportService
{
    public function __construct( //This dependency will be injected into the class when an object is instantiated.
        private CsvService $csvService,
    ) {}
    
    /**
     * Process a CSV file and dispatch jobs.
     */
    public function processCsv(string $filePath): Batch
    {
        $lazyProducts = $this->csvService->read($filePath); //Read and return.

        $products = [];
        $jobs = [];
        $chunkSize = 50;

        $lazyProducts->chunk($chunkSize)->each(function ($chunk) use (&$jobs) {
            $chunkArray = $chunk->all(); //Convert chunk to array for validation/transformation.
            $jobs[] = new ProcessProductData($chunkArray);
        });

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
            ->then(fn(Batch $batch) => Log::channel('jobs')->info('Batch finished successfully!', ['batchId' => $batch->id]))
            ->catch(fn(Batch $batch, Throwable $e) => Log::channel('jobs')->info('Batch failed!', ['batchId' => $batch->id,'error' => $e->getMessage()]))
            ->name('Import CSV Products')
            ->dispatch();
    }
}
