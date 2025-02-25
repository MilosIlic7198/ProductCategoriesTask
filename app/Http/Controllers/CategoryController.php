<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\UpdateCategoryRequest;
use App\Services\CategoryService;

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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateCategory(UpdateCategoryRequest $request, $id)
    /**
     * When we type-hint a FormRequest class in the method signature, laravel dependency injection system automatically resolves it and runs its validation logic before the controller method is executed.
     */
    {
        $response = $this->categoryService->updateCategory($id, $request->validated());
        return response()->json($response, $this->getStatusCode($response));
    }

    /**
     * Remove the specified category.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteCategory($id)
    {
        $response = $this->categoryService->deleteCategory($id);
        return response()->json($response, $this->getStatusCode($response));
    }

    /**
     * Status code resolver.
     *
     * @param  array  $response
     * @return int Status code.
     */
    private function getStatusCode(array $response)
    {
        if ($response['success']) {
            return 200;
        }
        return $response['isServerError'] ? 500 : 404;
    }
}
