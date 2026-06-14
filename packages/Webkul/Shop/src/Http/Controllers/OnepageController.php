<?php

namespace Webkul\Shop\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Webkul\Checkout\Facades\Cart;
use Webkul\MagicAI\Facades\MagicAI;
use Webkul\Payment\Payment\PaynectaHelper;
use Webkul\Sales\Repositories\InvoiceRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Webkul\Sales\Transformers\OrderResource;

class OnepageController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return View
     */
    public function index()
    {
        if (! core()->getConfigData('sales.checkout.shopping_cart.cart_page')) {
            abort(404);
        }

        Event::dispatch('checkout.load.index');

        /**
         * If guest checkout is not allowed then redirect back to the cart page.
         */
        if (
            ! auth()->guard('customer')->check()
            && ! core()->getConfigData('sales.checkout.shopping_cart.allow_guest_checkout')
        ) {
            return redirect()->route('shop.customer.session.index');
        }

        /**
         * If user is suspended then redirect back to the cart page.
         */
        if (auth()->guard('customer')->user()?->is_suspended) {
            session()->flash('warning', trans('shop::app.checkout.cart.suspended-account-message'));

            return redirect()->route('shop.checkout.cart.index');
        }

        /**
         * If cart has errors then redirect back to the cart page.
         */
        if (Cart::hasError()) {
            return redirect()->route('shop.checkout.cart.index');
        }

        $cart = Cart::getCart();

        /**
         * If cart is has downloadable items and customer is not logged in
         * then redirect back to the cart page.
         */
        if (
            ! auth()->guard('customer')->check()
            && (
                $cart->hasDownloadableItems()
                || ! $cart->hasGuestCheckoutItems()
            )
        ) {
            return redirect()->route('shop.customer.session.index');
        }

        return view('shop::checkout.onepage.index', compact('cart'));
    }

    /**
     * Order success page.
     *
     * @return View|RedirectResponse
     */
    public function success(OrderRepository $orderRepository)
    {
        if (! $order = $orderRepository->find(session('order_id'))) {
            return redirect()->route('shop.checkout.cart.index');
        }

        if (
            core()->getConfigData('magic_ai.general.settings.enabled')
            && core()->getConfigData('magic_ai.storefront_features.checkout_message.enabled')
        ) {
            try {
                $order->checkout_message = MagicAI::checkoutMessage($order);
            } catch (\Exception $e) {
            }
        }

        return view('shop::checkout.success', compact('order'));
    }

    /**
     * Redirect to Paynecta M-Pesa portal.
     *
     * @return RedirectResponse
     */
    public function mpesaRedirect(OrderRepository $orderRepository)
    {
        $cart = Cart::getCart();

        if (! $cart) {
            return redirect()->route('shop.checkout.cart.index');
        }

        // Get details to pass to Paynecta
        $amount = number_format($cart->grand_total, 2, '.', '');

        // Safely extract phone number from shipping/billing address
        $phone = '';
        if ($shippingAddress = $cart->shipping_address) {
            $phone = $shippingAddress->phone;
        } elseif ($billingAddress = $cart->billing_address) {
            $phone = $billingAddress->phone;
        }

        // Initialize M-Pesa STK Push via Paynecta
        $response = PaynectaHelper::initializePayment($phone, $amount);

        if (empty($response['success'])) {
            $message = $response['message'] ?? 'Unable to trigger M-Pesa STK push.';
            session()->flash('error', 'M-Pesa payment initiation failed: '.$message);

            return redirect()->route('shop.checkout.onepage.index');
        }

        $transactionReference = $response['data']['transaction_reference'] ?? null;
        $checkoutRequestId = $response['data']['CheckoutRequestID'] ?? null;

        // Create the order
        $data = (new OrderResource($cart))->jsonSerialize();
        $order = $orderRepository->create($data);

        // Deactivate cart
        Cart::deActivateCart();

        // Save transaction details on the order payment
        if ($order->payment) {
            $order->payment->additional = array_merge($order->payment->additional ?? [], [
                'transaction_reference' => $transactionReference,
                'CheckoutRequestID' => $checkoutRequestId,
                'phone' => PaynectaHelper::formatMobileNumber($phone),
            ]);
            $order->payment->save();
        }

        // Flash order ID
        session()->flash('order_id', $order->id);

        return redirect()->route('mpesa.status', ['order_id' => $order->id]);
    }

    /**
     * Show M-Pesa status page.
     *
     * @return View|RedirectResponse
     */
    public function mpesaStatus(int $orderId, OrderRepository $orderRepository)
    {
        $order = $orderRepository->find($orderId);

        if (! $order) {
            return redirect()->route('shop.checkout.cart.index');
        }

        if ($order->status === 'processing' || $order->status === 'completed') {
            session()->flash('order_id', $order->id);

            return redirect()->route('shop.checkout.onepage.success');
        }

        return view('shop::checkout.mpesa-status', compact('order'));
    }

    /**
     * Check M-Pesa payment status JSON response.
     *
     * @return JsonResponse
     */
    public function checkMpesaStatus(int $orderId, OrderRepository $orderRepository, InvoiceRepository $invoiceRepository)
    {
        $order = $orderRepository->find($orderId);

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        if ($order->status === 'processing' || $order->status === 'completed') {
            return response()->json(['status' => 'completed']);
        }

        $transactionReference = $order->payment->additional['transaction_reference'] ?? null;

        if (! $transactionReference) {
            return response()->json(['status' => 'failed', 'message' => 'No transaction reference found']);
        }

        $response = PaynectaHelper::queryStatus($transactionReference);

        if (empty($response['success'])) {
            return response()->json(['status' => 'pending', 'message' => 'Waiting for response']);
        }

        $status = $response['data']['status'] ?? 'pending';

        if ($status === 'completed') {
            // Update order status to processing/paid
            $order->status = 'processing';
            $order->save();

            // Create invoice to mark order as paid
            if (! $order->invoices->count()) {
                try {
                    $invoiceRepository->create([
                        'order_id' => $order->id,
                        'invoice' => [
                            'items' => collect($order->items)->mapWithKeys(fn ($item) => [$item->id => $item->qty_to_invoice])->toArray(),
                        ],
                    ]);
                } catch (\Exception $e) {
                    Log::error('Invoice creation failed for order '.$order->id.': '.$e->getMessage());
                }
            }

            return response()->json(['status' => 'completed']);
        }

        if ($status === 'failed' || $status === 'cancelled') {
            // Mark order as cancelled
            $order->status = 'canceled';
            $order->save();

            return response()->json([
                'status' => 'failed',
                'message' => $response['data']['failure_reason'] ?? 'Payment failed or cancelled.',
            ]);
        }

        return response()->json(['status' => 'pending']);
    }

    /**
     * Retry M-Pesa STK Push for an existing order.
     *
     * @return JsonResponse
     */
    public function mpesaRetry(int $orderId, OrderRepository $orderRepository)
    {
        $order = $orderRepository->find($orderId);

        if (! $order) {
            return response()->json(['success' => false, 'message' => 'Order not found'], 404);
        }

        // Only allow retrying if order is not completed/processing
        if ($order->status === 'processing' || $order->status === 'completed') {
            return response()->json(['success' => false, 'message' => 'Order is already processed.'], 400);
        }

        $phone = request()->input('phone');

        if (! $phone) {
            return response()->json(['success' => false, 'message' => 'Phone number is required.'], 400);
        }

        $amount = number_format($order->grand_total, 2, '.', '');

        // Trigger STK push
        $response = PaynectaHelper::initializePayment($phone, $amount);

        if (empty($response['success'])) {
            $message = $response['message'] ?? 'Unable to trigger M-Pesa STK push.';

            return response()->json(['success' => false, 'message' => $message], 400);
        }

        $transactionReference = $response['data']['transaction_reference'] ?? null;
        $checkoutRequestId = $response['data']['CheckoutRequestID'] ?? null;

        // Reset status to pending (in case it was marked as cancelled)
        $order->status = 'pending';
        $order->save();

        if ($order->payment) {
            $order->payment->additional = array_merge($order->payment->additional ?? [], [
                'transaction_reference' => $transactionReference,
                'CheckoutRequestID' => $checkoutRequestId,
                'phone' => PaynectaHelper::formatMobileNumber($phone),
            ]);
            $order->payment->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'STK push initialized successfully.',
            'phone' => PaynectaHelper::formatMobileNumber($phone),
        ]);
    }

    /**
     * Cancel payment, reactivate cart, and redirect back to cart.
     *
     * @return RedirectResponse
     */
    public function mpesaCancel(int $orderId, OrderRepository $orderRepository)
    {
        $order = $orderRepository->find($orderId);

        if ($order) {
            if ($order->status === 'pending') {
                $order->status = 'canceled';
                $order->save();
            }

            if ($order->cart_id) {
                Cart::activateCart($order->cart_id);
            }
        }

        return redirect()->route('shop.checkout.cart.index');
    }
}
