<?php

namespace App\Http\Controllers\API;

use App\Models\Order;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class OrderController extends Controller
{
    public function orderSearchList(Request $request)
{
    $search = $request->input('search');

    if ($search) {
        $orders = Order::where('name', 'LIKE', "%$search%")
                      ->orWhere('email', 'LIKE', "%$search%")
                      ->orWhere('phone', 'LIKE', "%$search%")
                      ->orWhere('product_title', 'LIKE', "%$search%")
                      ->paginate(10);
    } else {
        $orders = Order::paginate(10); 
    }

    return response()->json([
        'success' => true,
        'data' => $orders
    ], 200);
}    
    
      public function delivered($id)
{
    $order = Order::find($id);

    if (!$order) {
        return response()->json([
            'success' => false,
            'message' => 'Order not found.'
        ], 404);
    }

    $order->delivery_status = 'delivered';
    $order->payment_status = 'paid';
    $order->save();

    return response()->json([
        'success' => true,
        'message' => 'Order marked as delivered successfully.',
        'order' => $order
    ], 200);
}  
      
    public function cancelOrder($id)
{
    $order = Order::find($id);

    if (!$order) {
        return response()->json([
            'success' => false,
            'message' => 'Order not found.'
        ], 404);
    }

    $order->delivery_status = "You Cancel The Order";
    $order->save();

    return response()->json([
        'success' => true,
        'message' => 'Order cancelled successfully.',
        'order' => $order
    ], 200);
}
}
