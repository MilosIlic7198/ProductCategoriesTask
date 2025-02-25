<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests\UpdateProductRequest;
use App\Services\ProductService;

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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getProductsOfCategory($id)
    {
        $response = $this->productService->getProductsOfCategory($id);
        return response()->json($response, $this->getStatusCode($response));
    }

    /**
     * Update the specified product.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateProduct(UpdateProductRequest $request, $id)
    {
        $response = $this->productService->updateProduct($id, $request->validated());
        return response()->json($response, $this->getStatusCode($response));
    }

    /**
     * Remove the specified product.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteProduct($id)
    {
        $response = $this->productService->deleteProduct($id);
        return response()->json($response, $this->getStatusCode($response));
    }

    /**
     * Generate a products csv file for the specified category.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function generateCsv($categoryId)
    {
        $response = $this->productService->generateCsv($categoryId);
        return response()->json($response, $this->getStatusCode($response));
    }

    /**
     * Determine the status code based on the response.
     */
    private function getStatusCode(array $response): int
    {
        if ($response['success']) {
            return 200;
        }
        return $response['isServerError'] ? 500 : 404;
    }
}
