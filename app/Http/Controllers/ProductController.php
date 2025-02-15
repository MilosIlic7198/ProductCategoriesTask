<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Exception;

class ProductController extends Controller
{
    /**
     * Get all products.
     *
     * @return \Illuminate\Http\Response
     */
    public function getProducts()
    {
        try {
            $products = Product::all();
            return response()->json([
                'success' => true,
                'message' => 'Products fetched successfully.',
                'payload' => $products,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching products.',
                'payload' => null,
            ], 500);
        }
    }

    /**
     * Get all products of the specified category.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function getProductsOfCategory($id)
    {
        try {
            $category = Category::find($id);
            if (!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'The category with this id does not exist.',
                    'payload' => null,
                ], 404);
            }

            $products = $category->products;
            return response()->json([
                'success' => true,
                'message' => 'Products fetched successfully for the specified category.',
                'payload' => $products,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching products for the category.',
                'payload' => null,
            ], 500);
        }
    }

    /**
     * Update the specified product.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateProduct(Request $request, $id)
    {
        try {
            $product = Product::find($id);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'The product with this id does not exist.',
                    'payload' => null,
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'product_number' => 'nullable|string|max:255',
                'category_id' => 'nullable|exists:categories,id',
                'department_id' => 'nullable|exists:departments,id',
                'manufacturer_id' => 'nullable|exists:manufacturers,id',
                'upc' => 'nullable|string|max:255',
                'sku' => 'nullable|string|max:255',
                'regular_price' => 'nullable|numeric',
                'sale_price' => 'nullable|numeric',
                'description' => 'nullable|string',
            ], [
                'product_number.string' => 'The product number must be a string.',
                'product_number.max' => 'The product number may not be greater than 255 characters.',
                'category_id.exists' => 'The selected category id does not exist.',
                'department_id.exists' => 'The selected department id does not exist.',
                'manufacturer_id.exists' => 'The selected manufacturer id does not exist.',
                'upc.string' => 'The upc must be a string.',
                'upc.max' => 'The upc may not be greater than 255 characters.',
                'sku.string' => 'The sku must be a string.',
                'sku.max' => 'The sku may not be greater than 255 characters.',
                'regular_price.numeric' => 'The regular price must be a valid number.',
                'sale_price.numeric' => 'The sale price must be a valid number.',
                'description.string' => 'The description must be a string.',
            ]);

            if ($validator->fails()) {
                $firstFailedField = $validator->failed();
                $firstField = key($firstFailedField);
                $firstError = $validator->errors()->first($firstField);
                return response()->json([
                    'success' => false,
                    'message' => $firstError,
                    'payload' => null,
                ], 422);
            }

            $product->update($validator->validated());
            return response()->json([
                'success' => true,
                'message' => 'Product updated successfully.',
                'payload' => $product,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the product.',
                'payload' => null,
            ], 500);
        }
    }

    /**
     * Remove the specified product.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteProduct($id)
    {
        try {
            $product = Product::find($id);
            if (!$product) {
                return response()->json([
                    'success' => false,
                    'message' => 'The product with this id does not exist.',
                    'payload' => null,
                ], 404);
            }

            //Soft delete the product.
            $product->delete();
            return response()->json([
                'success' => true,
                'message' => 'Product deleted successfully.',
                'payload' => null,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the product.',
                'payload' => null,
            ], 500);
        }
    }
}
