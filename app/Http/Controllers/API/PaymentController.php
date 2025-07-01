<?php

namespace App\Http\Controllers\API;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\Add_To_Cart;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;

class PaymentController extends Controller
{

    

   public function stripePayment(Request $request, $totalprice)
{
    try {
        \Stripe\Stripe::setApiKey(env('STRIPE_SECRET_KEY'));
//    dd($totalprice);
        $amount = intval($totalprice * 100);

        if (!$request->payment_method) {
            return response()->json([
                'success' => false,
                'message' => 'Payment method is required.'
            ], 422);
        }

        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $amount,
            'currency' => 'usd',
            'payment_method' => $request->payment_method,
            'confirm' => true,
            'automatic_payment_methods' => [
                'enabled' => true,
                'allow_redirects' => 'never',
            ],
        ]);

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

                $order->payment_status = "Paid";
                $order->delivery_status = "Processing";

                $order->save();

                // Optionally send email
                // if ($order->price > 10000) {
                //     Mail::to($cartItem->email)->send(new OrderNotification($order));
                // }
            }

            Add_To_Cart::where('user_id', $userId)->delete();

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
