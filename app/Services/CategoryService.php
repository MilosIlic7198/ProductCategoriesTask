<?php

namespace App\Services;

use App\Models\Category;
use Exception;

class CategoryService
{
    /**
     * Get all categories.
     */
    public function getAllCategories()
    {
        return $this->confirm(function () {
            return Category::all();
        }, 'Categories fetched successfully.');
    }

    /**
     * Update a category.
     */
    public function updateCategory(int $id, array $data)
    {
        return $this->confirmCategory($id, function ($category) use ($data) {
            $category->update($data);
            return $category;
        }, 'Category updated successfully.');
    }

    /**
     * Delete a category.
     */
    public function deleteCategory(int $id)
    {
        return $this->confirmCategory($id, function ($category) {
            $category->delete();
            return null;
        }, 'Category deleted successfully.');
    }

    /**
     * For straight forward operations execution.
     */
    private function confirm(callable $operation, string $successMessage)
    {
        try {
            $result = $operation();
            return $this->formatResponse(true, $successMessage, $result);
        } catch (Exception $e) {
            return $this->formatResponse(false, 'An error occurred while processing the request.', null, true);
        }
    }

    /**
     * Handle not found case.
     *
     * Executes a given operation on a category if it exists, handling errors and formatting the response.
     *
     * @param int $id The id of the category.
     * @param callable $operation A function to execute on the found category, receiving the category as an argument.
     * @param string $successMessage The message to return on success.
     * @return array The formatted response.
     */
    private function confirmCategory(int $id, callable $operation, string $successMessage)
    /**
     * The callable is a way to pass a function as an argument that the method can execute.
     */
    {
        try {
            $category = Category::find($id);
            if (!$category) {
                return $this->formatResponse(false, 'The category with this id does not exist.', null, false);
            }
            $result = $operation($category);
            return $this->formatResponse(true, $successMessage, $result);
        } catch (Exception $e) {
            return $this->formatResponse(false, 'An error occurred while processing the category.', null, true);
        }
    }

    /**
     * Format response based on success or failure.
     */
    private function formatResponse(bool $success, string $message, $payload = null, bool $isServerError = false)
    {
        return [
            'success' => $success,
            'message' => $message,
            'payload' => $payload,
            'isServerError' => $isServerError, //Added this to differentiate.
        ];
    }
}
