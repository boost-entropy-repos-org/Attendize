<?php

namespace Services\PaymentGateway;


use Illuminate\Support\Facades\Log;

class StripeSCA
{

    CONST GATEWAY_NAME = 'Stripe\PaymentIntents';

    private $transaction_data;

    private $gateway;

    private $extra_params = ['paymentMethod','payment_intent'];

    public function __construct($gateway)
    {
        $this->gateway = $gateway;
        $this->options = [];
    }

    private function createTransactionData($order_total, $order_email, $event)
    {

        $returnUrl = route('showEventCheckoutPaymentReturn', [
            'event_id' => $event->id,
            'is_payment_successful' => 1,
        ]);

        $this->transaction_data = [
            'amount' => $order_total,
            'currency' => $event->currency->code,
            'description' => 'Order for customer: ' . $order_email,
            'paymentMethod' => $this->options['paymentMethod'],
            'receipt_email' => $order_email,
            'returnUrl' => $returnUrl,
            'confirm' => true
        ];

        return $this->transaction_data;
    }

    public function startTransaction($order_total, $order_email, $event)
    {

        $this->createTransactionData($order_total, $order_email, $event);
        $response = $this->gateway->authorize($this->transaction_data)->send();

        return $response;
    }

    public function getTransactionData() {
        return $this->transaction_data;
    }

    public function extractRequestParameters($request)
    {
        foreach ($this->extra_params as $param) {
            if (!empty($request->get($param))) {
                $this->options[$param] = $request->get($param);
            }
        }
    }

    public function completeTransaction($transactionId = '') {

        $intentData = array(
            'paymentIntentReference' => $this->options['payment_intent'],
        );

        $paymentIntent = $this->gateway->fetchPaymentIntent($intentData);
        $response = $paymentIntent->send();
        if ($response->requiresConfirmation()) {
            $response = $this->gateway->confirm($intentData)->send();
        }


        return $response;
    }
    
}