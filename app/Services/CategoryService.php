<?php

namespace App\Services;

use App\Models\Category;
use Exception;

class CategoryService
{
    /**
     * Get all categories.
     */
    public function getAllCategories(): array
    {
        return $this->confirm(function () {
            return Category::all();
        }, 'Categories fetched successfully.');
    }

    /**
     * Update a category.
     */
    public function updateCategory(Category $category, array $data): array
    {
        return $this->confirm(function () use ($category, $data) {
            //Set updated_at to current CET time.
            $data['updated_at'] = now('CET');
            $category->update($data);
            return $category;
        }, 'Category updated successfully.');
    }

    /**
     * Delete a category.
     */
    public function deleteCategory(Category $category): array
    {
        return $this->confirm(function () use ($category) {
            $category->delete();
            return null;
        }, 'Category deleted successfully.');
    }

    /**
     * For straight forward operations execution.
     */
    private function confirm(callable $operation, string $successMessage): array
    /**
     * The callable is a way to pass a function as an argument that the method can execute.
     */
    {
        try {
            $result = $operation();
            return $this->formatResponse(true, $successMessage, $result);
        } catch (Exception $e) {
            return $this->formatResponse(false, 'An error occurred while processing the request.', null);
        }
    }

    /**
     * Format response based on success or failure.
     */
    private function formatResponse(bool $success, string $message, $payload = null): array
    {
        return [
            'success' => $success,
            'message' => $message,
            'payload' => $payload,
        ];
    }
}
