<?php

namespace App\Services;

use Illuminate\Support\LazyCollection;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Exception;

class CsvService
{
    /**
     * Create a new class instance.
     */
    public function __construct(
        private readonly ProductValidatorService $validator,
        private readonly ProductTransformerService $transformer
    ) {}

    public function read(string $filePath): LazyCollection
    {
        //Laravel uses php generators in the background to implement LazyCollection.
        //Generators allow us to iterate over data without loading the entire file into memory at once, which is particularly useful for large datasets.
        return LazyCollection::make(function () use ($filePath) {
            //Check if file exists.
            $this->ensureFileExists($filePath);

            //Open the CSV file.
            $handle = $this->openFile($filePath);
            
            //Get the header row to map columns.
            $header = $this->getHeader($handle, $filePath);

            //Checks if there are more rows to read in the CSV file. If there are no more rows or an error occurs, the loop stops.
            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($header, $row);
                //Validates row.
                if (!$this->isValidRow($data)) {
                    continue;
                }
                //Transform the row and yield the result.
                $transformedData = $this->transformRow($data);
                if ($transformedData === null) {
                    continue;
                }
                yield $transformedData;
            }
            //Ensure the file is closed after iteration.
            fclose($handle);
        });
    }

    private function ensureFileExists(string $filePath): void
    {
        //Check if file exists.
        if (!file_exists($filePath)) {
            throw new Exception("File not found: $filePath");
        }
    }

    private function openFile(string $filePath): mixed
    {
        $handle = fopen($filePath, 'r');
        //Checks if the file was successfully opened. If the file cant be opened, the script wont continue processing the CSV.
        if ($handle === false) {
            throw new Exception("Failed to open the file: $filePath");
        }
        return $handle;
    }

    private function getHeader(mixed $handle, string $filePath): array
    {
        $header = fgetcsv($handle);
        //Check the header.
        if ($header === false) {
            fclose($handle);
            throw new Exception("Failed to read header from file: $filePath");
        }
        return $header;
    }

    /**
     * Validates row using the ProductValidatorService.
     *
     * @param array $row The row to validate.
     * @return bool True if the row is valid, false otherwise.
     */
    private function isValidRow(array $row): bool
    {
        try {
            $this->validator->validate($row);
            return true;
        } catch (InvalidArgumentException $e) {
            Log::channel('csvImport')->info('Skipped invalid row', ['row' => $row, 'error' => $e->getMessage()]);   
            return false;
        }
    }

    /**
     * Transforms a row using the ProductTransformerService.
     *
     * @param array $row The row to transform.
     * @return array|null The transformed data or null if transformation fails.
     */
    private function transformRow(array $row): ?array
    {
        try {
            $transformedData = $this->transformer->transform($row);
            return $transformedData;
        } catch (InvalidArgumentException $e) {
            Log::channel('csvImport')->info('Skipped row due to transformation error', ['row' => $row,'error' => $e->getMessage()]);
            return null;
        }
    }
}
