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
            return $this->formatResponse(false, 'An error occurred while processing the request.');
        }
    }

    /**
     * Handle not found case.
     */
    private function confirmCategory(int $id, callable $operation, string $successMessage)
    {
        try {
            $category = Category::find($id);
            if (!$category) {
                return $this->formatResponse(false, 'The category with this id does not exist.', null);
            }
            $result = $operation($category);
            return $this->formatResponse(true, $successMessage, $result);
        } catch (Exception $e) {
            return $this->formatResponse(false, 'An error occurred while processing the category.');
        }
    }

    /**
     * Format response based on success or failure.
     */
    private function formatResponse(bool $success, string $message, $payload = null)
    {
        return [
            'success' => $success,
            'message' => $message,
            'payload' => $payload,
        ];
    }
}
