<?php

namespace App\Http\Controllers\API;

use App\Models\Order;
use App\Models\ProductImage;
use App\Models\Product;
use App\Models\Add_To_Cart;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
   public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'product_title' => 'required|string',
        'description' => 'required|string',
        'price' => 'required|numeric',
        'quantity' => 'required|numeric',
        'category_id' => 'required|exists:categories,id',
        'discount' => 'nullable|numeric',
        'image.*' => 'required|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'success' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    // Create Product
    $product = new Product();
    $product->product_title = $request->product_title;
    $product->description = $request->description;
    $product->price = $request->price;
    $product->quantity = $request->quantity;
    $product->category_id = $request->category_id;
    $product->discount = $request->discount ?? 0;
    $product->image = ''; // Placeholder
    $product->save();

    // Handle images
    if ($request->hasFile('image')) {
        foreach ($request->file('image') as $index => $imgFile) {
            $imagename = time() . '_' . uniqid() . '.' . $imgFile->getClientOriginalExtension();
            $imgFile->move(public_path('product_images'), $imagename);

            ProductImage::create([
                'product_id' => $product->id,
                'image' => $imagename,
            ]);

            // Save first image as preview
            if ($index === 0) {
                $product->image = $imagename;
                $product->save();
            }
        }
    }

    return response()->json([
        'success' => true,
        'message' => 'Product created successfully',
        'data' => $product->load('images')
    ], 201);
   }

   public function update(Request $request, $id)
{
    // Find product or return 404
    $product = Product::findOrFail($id);

    // Validate request
    $request->validate([
        'product_title' => 'required|string|max:255',
        'description' => 'required|string',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        'price' => 'required|numeric',
        'quantity' => 'required|integer',
        'category_id' => 'required|integer|exists:categories,id',
        'discount' => 'nullable|numeric',
    ]);

    // Update product fields
    $product->product_title = $request->product_title;
    $product->description = $request->description;

    // Handle image update
    if ($request->hasFile('image')) {
        $image = $request->file('image');
        $imagename = time() . '.' . $image->getClientOriginalExtension();
        $image->move(public_path('product_images'), $imagename);
        $product->image = $imagename;
    }

    $product->price = $request->price;
    $product->quantity = $request->quantity;
    $product->category_id = $request->category_id;
    $product->discount = $request->discount;

    // Save updates
    $product->save();

    return response()->json([
        'message' => 'Product updated successfully.',
        'product' => $product
    ], 200);
}
      public function destroy($id)
{
    
        $products = Product::findOrFail($id);
        $products->delete();

        return response()->json([
            'message' => 'Product deleted successfully.'
        ], 200);

    }

    public function showProducts()
    {
        $products = Product::paginate(10);

        return response()->json([
            'success' => true,
            'data' => $products
        ], 200);

    }

    public function addToCart(Request $request, $id)
{  

    // dd($request->all());
    if (Auth::check()) {
        $user = Auth::user();
        $product = Product::find($id);
        // dd($product);

        if (!$product) {
            return response()->json(['error' => 'Product not found.'], 404);
        }

        $requested_quantity = $request->input('quantity');

        if ($requested_quantity > $product->quantity) {
            return response()->json(['error' => 'Requested quantity exceeds available stock.'], 400);
        }

        $cart = Add_To_Cart::where('user_id', $user->id)
            ->where('product_id', $product->id)
            ->first();

        if ($cart) {
            $new_cart_quantity = $cart->quantity + $requested_quantity;

            if ($new_cart_quantity > $product->quantity) {
                return response()->json(['error' => 'Requested quantity exceeds available stock.'], 400);
            }

            $cart->quantity += $requested_quantity;
            $cart->total_price = $cart->quantity * $cart->price;
            $cart->save();

            return response()->json(['message' => 'Product quantity updated in cart.', 'cart' => $cart], 200);
        } else {
            $cart = new Add_To_Cart;

            $cart->name = $user->name;
            $cart->email = $user->email;
            $cart->phone = $user->phone;
            $cart->address = $user->address;
            $cart->user_id = $user->id;

            $cart->product_title = $product->product_title;
            $cart->price = $product->discount != null ? $product->discount : $product->price;
            $cart->image = $product->image;
            $cart->product_id = $product->id;

            $cart->quantity = $requested_quantity;
            $cart->total_price = $cart->quantity * $cart->price;

            $cart->save();

            return response()->json(['message' => 'Product added to cart successfully.', 'cart' => $cart], 201);
        }
    } else {
        return response()->json(['error' => 'Unauthorized. Please login.'], 401);
    }
}
           
       public function showCart()
{
    if (Auth::check()) {
        $userId = Auth::id();
        $cart = Add_To_Cart::where('user_id', $userId)->get();

        if ($cart->isEmpty()) {
            return response()->json([
                'message' => 'Your cart is empty.',
                'cart' => []
            ], 200);
        }

        return response()->json([
            'message' => 'Cart retrieved successfully.',
            'cart' => $cart
        ], 200);
    } else {
        return response()->json([
            'error' => 'Unauthorized. Please login.'
        ], 401);
    }
}  
    
      public function remove_cart($id)
{
    $cartItem = Add_To_Cart::find($id);

    if ($cartItem) {
        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Product removed from cart.'
        ], 200);
    } else {
        return response()->json([
            'success' => false,
            'message' => 'Cart item not found.'
        ], 404);
    }
}
     
         public function cash_order()
{
    if (!Auth::check()) {
        return response()->json(['error' => 'Unauthorized. Please login first.'], 401);
    }

    $user = Auth::user();
    $userId = $user->id;

    $cartItems = Add_To_Cart::where('user_id', $userId)->get();

    if ($cartItems->isEmpty()) {
        return response()->json(['message' => 'Cart is empty.'], 404);
    }

    foreach ($cartItems as $cartItem) {
        $order = new Order();

        $order->name = $cartItem->name;
        $order->email = $cartItem->email;
        $order->phone = $cartItem->phone;
        $order->address = $cartItem->address;
        $order->user_id = $cartItem->user_id;

        $order->product_title = $cartItem->product_title;
        $order->price = $cartItem->price * $cartItem->quantity;
        $order->quantity = $cartItem->quantity;
        $order->image = $cartItem->image;
        $order->product_id = $cartItem->product_id;

        $order->payment_status = "Cash On Delivery";
        $order->delivery_status = "Processing";

        $order->save();

        $product = Product::find($cartItem->product_id);
        if ($product) {
            if ($product->quantity >= $cartItem->quantity) {
                $product->quantity -= $cartItem->quantity;
                $product->save();
            } else {
                return response()->json(['error' => 'Ordered quantity exceeds available stock.'], 400);
            }
        }

        // if ($order->price > 10000) {
        //     Mail::to($cartItem->email)->send(new OrderNotification($order));
        // }
    }

    // Clear cart after placing order
    Add_To_Cart::where('user_id', $userId)->delete();

    return response()->json(['message' => 'Order placed successfully!'], 201);
}

   public function productDetail($id)
{
    $product = Product::with('images')->findorfail($id);

    if (!$product) {
        return response()->json([
            'success' => false,
            'message' => 'Product not found.'
        ], 404);
    }

    return response()->json([
        'success' => true,
        'data' => $product
    ], 200);
}

       public function search_product(Request $request)
{
    $searchText = $request->input('search');

    if ($searchText) {
        $products = Product::where('product_title', 'LIKE', "%$searchText%")
            ->orWhere('price', 'LIKE', "%$searchText%")
            ->orWhereHas('category', function ($query) use ($searchText) {
                $query->where('title', 'LIKE', "%$searchText%");
            })
            ->paginate(6);
    } else {
        $products = Product::paginate(6);
    }

    return response()->json([
        'success' => true,
        'data' => $products
    ], 200);
}
}
