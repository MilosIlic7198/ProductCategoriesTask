<?php

namespace App\Services;

use Illuminate\Support\LazyCollection;
use Exception;

class CsvReaderService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    public function read(string $filePath): LazyCollection
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
        if ($header === false) { //Check the header.
            fclose($handle);
            throw new Exception("Failed to read header from file: $filePath");
        }

        //Laravel uses php generators in the background to implement LazyCollection.
        //Generators allow us to iterate over data without loading the entire file into memory at once, which is particularly useful for large datasets.
        return LazyCollection::make(function () use ($handle, $header) {
            while (($row = fgetcsv($handle)) !== false) {//Checks if there are more rows to read in the CSV file. If there are no more rows or an error occurs, the loop stops.
                yield array_combine($header, $row);
            }
            fclose($handle); //Ensure the file is closed after iteration.
        });
    }
}
