<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlist = Wishlist::with('items.product')->firstOrCreate(['user_id' => Auth::id()]);
        return response()->json($wishlist);
    }

    public function store(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id'
        ]);

        $wishlist = Wishlist::firstOrCreate(['user_id' => Auth::id()]);
        
        $exists = $wishlist->items()->where('product_id', $request->product_id)->exists();

        if (!$exists) {
            $wishlist->items()->create(['product_id' => $request->product_id]);
        }

        return response()->json(['message' => 'Item added to wishlist', 'wishlist' => $wishlist->load('items.product')]);
    }

    public function destroy($id)
    {
        $wishlistItem = WishlistItem::findOrFail($id);
        $wishlist = $wishlistItem->wishlist;
        $wishlistItem->delete();

        return response()->json(['message' => 'Item removed from wishlist', 'wishlist' => $wishlist->load('items.product')]);
    }
}
