<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\UpdateCategoryRequest;
use App\Services\CategoryService;
use App\Models\Category;

class CategoryController extends Controller
{
    protected $categoryService;

    public function __construct(CategoryService $categoryService)
    {
        $this->categoryService = $categoryService;
    }
    /**
     * Get all categories.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCategories()
    {
        $response = $this->categoryService->getAllCategories();
        return response()->json($response, $this->getStatusCode($response));
    }

    /**
     * Update the specified category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Category  $category
     * @return \Illuminate\Http\Response
     */
    public function updateCategory(UpdateCategoryRequest $request, Category $category)
    /**
     * When we type-hint a FormRequest class in the method signature, laravel dependency injection system automatically resolves it and runs its validation logic before the controller method is executed.
     */
    /** 
     * Laravel route model binding automatically resolves Eloquent models from route parameters.
     * If no record is found, a 404 is returned before the method executes, simplifying ID validation.
     */
    {
        $response = $this->categoryService->updateCategory($category, $request->validated());
        return response()->json($response, $this->getStatusCode($response));
    }

    /**
     * Remove the specified category.
     *
     * @param  Category  $category
     * @return \Illuminate\Http\Response
     */
    public function deleteCategory(Category $category)
    {
        $response = $this->categoryService->deleteCategory($category);
        return response()->json($response, $this->getStatusCode($response));
    }

    /**
     * Status code resolver.
     *
     * @param  array  $response
     * @return int Status code.
     */
    private function getStatusCode(array $response): int
    {
        return $response['success'] ? 200 : 500;
    }
}
