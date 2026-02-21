<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        return response()->json(Product::all());
    }

    public function show($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        return response()->json($product);
    }

    public function store(Request $request)
    {
        \Illuminate\Support\Facades\Log::info('Store Product Request:', $request->all());

        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'stock' => 'required|integer',
            'category_id' => 'required',
            'image' => 'nullable', // Could be string (Base64) or file
        ]);

        $data = $request->all();

        // Handle category_id if it's passed as a name
        if (!is_numeric($data['category_id'])) {
            $category = \App\Models\Category::where('name', $data['category_id'])->first();
            if ($category) {
                $data['category_id'] = $category->id;
            } else {
                return response()->json(['message' => 'Category not found: ' . $data['category_id']], 422);
            }
        }

        // Handle Base64 Image
        if ($request->filled('image') && is_string($request->image) && str_starts_with($request->image, 'data:image')) {
            try {
                $image_64 = $request->image;
                $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
                $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
                $image = str_replace($replace, '', $image_64);
                $image = str_replace(' ', '+', $image);
                $imageName = time() . '.' . $extension;
                Storage::disk('public')->put('products/' . $imageName, base64_decode($image));
                $data['image'] = url('/storage/products/' . $imageName);
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Base64 upload failed: ' . $e->getMessage());
            }
        } 
        // Handle Actual File Upload
        elseif ($request->hasFile('image')) {
            try {
                $path = $request->file('image')->store('products', 'public');
                $data['image'] = url(Storage::url($path));
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('File upload failed: ' . $e->getMessage());
            }
        } else {
            // Final safeguard: if image is present but not recognized as Base64/File, 
            // remove it from data to prevent SQL errors (column size).
            unset($data['image']);
        }

        try {
            $product = Product::create($data);
            return response()->json($product, 201);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Product creation failed: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to create product', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'price' => 'sometimes|required|numeric',
            'category_id' => 'sometimes|required|exists:categories,id',
            'image' => 'nullable|string',
        ]);

        $data = $request->all();

        if ($request->filled('image') && str_starts_with($request->image, 'data:image')) {
            $image_64 = $request->image;
            $extension = explode('/', explode(':', substr($image_64, 0, strpos($image_64, ';')))[1])[1];
            $replace = substr($image_64, 0, strpos($image_64, ',') + 1);
            $image = str_replace($replace, '', $image_64);
            $image = str_replace(' ', '+', $image);
            $imageName = time() . '.' . $extension;
            Storage::disk('public')->put('products/' . $imageName, base64_decode($image));
            $data['image'] = url('/storage/products/' . $imageName);
        }

        $product->update($data);
        return response()->json($product);
    }

    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) {
            return response()->json(['message' => 'Product not found'], 404);
        }
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }
}
