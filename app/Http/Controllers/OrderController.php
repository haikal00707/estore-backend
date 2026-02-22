<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::where('user_id', $request->user()->id)
            ->with('items.product')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'address' => 'required|string',
            'payment_method' => 'required|string',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                $totalPrice = 0;
                $orderItems = [];

                foreach ($request->items as $item) {
                    $product = Product::lockForUpdate()->find($item['product_id']);

                    if ($product->stock < $item['quantity']) {
                        throw new \Exception("Stok {$product->name} tidak mencukupi.");
                    }

                    $totalPrice += $product->price * $item['quantity'];
                    $orderItems[] = [
                        'product_id' => $product->id,
                        'quantity' => $item['quantity'],
                        'price' => $product->price,
                    ];

                    // Reduce stock
                    $product->decrement('stock', $item['quantity']);
                }

                $order = Order::create([
                    'user_id' => $request->user()->id,
                    'total_price' => $totalPrice,
                    'address' => $request->address,
                    'payment_method' => $request->payment_method,
                    'status' => 'Menunggu Konfirmasi',
                ]);

                foreach ($orderItems as $orderItem) {
                    $orderItem['order_id'] = $order->id;
                    OrderItem::create($orderItem);
                }

                return response()->json([
                    'message' => 'Pesanan berhasil dibuat.',
                    'order' => $order->load('items.product'),
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function show($id, Request $request)
    {
        $order = Order::with('items.product')
            ->where('user_id', $request->user()->id)
            ->findOrFail($id);

        return response()->json($order);
    }

    public function confirmPayment($id)
    {
        $order = Order::findOrFail($id);
        
        if ($order->status !== 'Menunggu Konfirmasi') {
            return response()->json(['message' => 'Pesanan sudah dikonfirmasi atau dibatalkan.'], 400);
        }

        $order->update(['status' => 'Selesai']);

        return response()->json([
            'message' => 'Pembayaran berhasil dikonfirmasi.',
            'order' => $order
        ]);
    }
}
