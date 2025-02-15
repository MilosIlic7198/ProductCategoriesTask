<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

use App\Models\Category;
use App\Models\Department;
use App\Models\Manufacturer;

class ProcessProductData implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $categoriesToInsert;
    protected $departmentsToInsert;
    protected $manufacturersToInsert;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $categoryNames, array $departmentNames, array $manufacturerNames)
    {
        $this->categoriesToInsert = $categoryNames;
        $this->departmentsToInsert = $departmentNames;
        $this->manufacturersToInsert = $manufacturerNames;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //Remove duplicates.
        $categoryNames = array_unique($this->categoriesToInsert);
        $departmentNames = array_unique($this->departmentsToInsert);
        $manufacturerNames = array_unique($this->manufacturersToInsert);

        //Map the data for insertion.
        $categoriesToInsert = array_map(fn($name) => ['name' => $name, 'created_at' => now(), 'updated_at' => now()], $categoryNames);
        $departmentsToInsert = array_map(fn($name) => ['name' => $name, 'created_at' => now(), 'updated_at' => now()], $departmentNames);
        $manufacturersToInsert = array_map(fn($name) => ['name' => $name, 'created_at' => now(), 'updated_at' => now()], $manufacturerNames);

        //Insert the data upsert.
        Category::upsert($categoriesToInsert, ['name'], ['created_at', 'updated_at']);
        Department::upsert($departmentsToInsert, ['name'], ['created_at', 'updated_at']);
        Manufacturer::upsert($manufacturersToInsert, ['name'], ['created_at', 'updated_at']);
    }
}
