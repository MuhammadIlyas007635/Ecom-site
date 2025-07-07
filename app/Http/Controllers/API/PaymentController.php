<?php



namespace App\Http\Controllers\API;

use App\Models\Order;
use App\Models\Product;
use App\Models\Add_To_Cart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Stripe\PaymentIntent;
use Stripe\Stripe;

class PaymentController extends Controller
{
    public function stripePayment(Request $request, $totalprice)
    {
        // dd($request->all(), $totalprice);
        try {
            Stripe::setApiKey(env('STRIPE_SECRET_KEY'));

            $amount = intval($totalprice * 100); // cents

            if (!$request->payment_method) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment method is required.'
                ], 422);
            }

            $paymentIntent = PaymentIntent::create([
                'amount' => $amount,
                'currency' => 'usd',
                 'payment_method' => $request->payment_method,
                'confirm' => true,
                'automatic_payment_methods' => [
                    'enabled' => true,
                    'allow_redirects' => 'never',
                ],
            ]);

    //    dd($paymentIntent);
            if ($paymentIntent->status === 'succeeded') {
                $user = Auth::user();
                $userId = $user->id;

                $cartItems = Add_To_Cart::where('user_id', $userId)->get();

                foreach ($cartItems as $cartItem) {
                    $order = new Order();

                    $order->name = $cartItem->name;
                    $order->email = $cartItem->email;
                    $order->phone = $cartItem->phone;
                    $order->address = $cartItem->address;
                    $order->user_id = $cartItem->user_id;

                    $order->product_title = $cartItem->product_title;
                    $order->price = $cartItem->price;
                    $order->quantity = $cartItem->quantity;
                    $order->image = $cartItem->image;
                    $order->product_id = $cartItem->product_id;

                    $order->payment_status = 'Paid';
                    $order->delivery_status = 'Processing';

                    $order->save();

                    $product = Product::find($cartItem->product_id);
                    if ($product) {
                        if ($product->quantity >= $cartItem->quantity) {
                            $product->quantity -= $cartItem->quantity;
                            $product->save();
                        } else {
                            return response()->json([
                                'success' => false,
                                'message' => 'Ordered quantity exceeds available stock.'
                            ], 400);
                        }
                    }
                }

                // Empty the cart
                Add_To_Cart::where('user_id', $userId)->delete();
                // dd( $paymentIntent);
                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful and order placed.',
                    'payment_id' => $paymentIntent->id,
                ]);

            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment not completed.',
                    'status' => $paymentIntent->status
                ], 402);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
} 
