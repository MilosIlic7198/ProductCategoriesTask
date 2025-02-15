<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Category;
use Illuminate\Support\Facades\Validator;
use Exception;

class CategoryController extends Controller
{
    /**
     * Get all categories.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCategories()
    {
        try {
            $categories = Category::all();
            return response()->json([
                'success' => true,
                'message' => 'Categories fetched successfully.',
                'payload' => $categories,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching categories.',
                'payload' => null,
            ], 500);
        }
    }

    /**
     * Update the specified category.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateCategory(Request $request, $id)
    {
        try {
            $category = Category::find($id);
            if(!$category) {
                return response()->json([
                    'success' => false,
                    'message' => 'The category with this id does not exist.',
                    'payload' => null,
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed for field name.',
                    'payload' => null,
                ], 422);
            }

            $category->update($validator->validated());
            return response()->json([
                'success' => true,
                'message' => 'Category updated successfully.',
                'payload' => $category,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the category.',
                'payload' => null,
            ], 500);
        }
    }

    /**
     * Remove the specified category.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function deleteCategory($id)
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

            //Soft delete the category.
            $category->delete();
            return response()->json([
                'success' => true,
                'message' => 'Category deleted successfully.',
                'payload' => null,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the category.',
                'payload' => null,
            ], 500);
        }
    }
}
