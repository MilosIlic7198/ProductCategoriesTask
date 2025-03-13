<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\UpdateProductRequest;
use App\Services\ProductService;
use App\Models\Category;
use App\Models\Product;

class ProductController extends Controller
{

    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }
    /**
     * Get all products.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProducts()
    {
        $response = $this->productService->getAllProducts();
        return response()->json($response, $this->getStatusCode($response));
    }

    /**
     * Get all products of the specified category.
     *
     * @param  Category  $category
     * @return \Illuminate\Http\Response
     */
    public function getProductsOfCategory(Category $category)
    {
        $response = $this->productService->getProductsOfCategory($category);
        return response()->json($response, $this->getStatusCode($response));
    }

    /**
     * Update the specified product.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Product  $product
     * @return \Illuminate\Http\Response
     */
    public function updateProduct(UpdateProductRequest $request, Product $product)
    /**
     * When we type-hint a FormRequest class in the method signature, laravel dependency injection system automatically resolves it and runs its validation logic before the controller method is executed.
     */
    /** 
     * Laravel route model binding automatically resolves Eloquent models from route parameters.
     * If no record is found, a 404 is returned before the method executes, simplifying ID validation.
     */
    {
        $response = $this->productService->updateProduct($product, $request->validated());
        return response()->json($response, $this->getStatusCode($response));
    }

    /**
     * Remove the specified product.
     *
     * @param  Product  $product
     * @return \Illuminate\Http\Response
     */
    public function deleteProduct(Product $product)
    {
        $response = $this->productService->deleteProduct($product);
        return response()->json($response, $this->getStatusCode($response));
    }

    /**
     * Generate a products csv file for the specified category.
     *
     * @param  Category  $category
     * @return \Illuminate\Http\Response
     */
    public function generateCsv(Category $category)
    {
        $response = $this->productService->generateCsv($category);
        return response()->json($response, $this->getStatusCode($response));
    }

    /**
     * Determine the status code based on the response.
     */
    private function getStatusCode(array $response): int
    {
        return $response['success'] ? 200 : 500;
    }
}
