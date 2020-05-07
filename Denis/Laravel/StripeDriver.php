<?php


namespace App\Services\Payment\Drivers;


use App\Models\Order;
use App\Models\PaymentSystem;
use App\Models\Showcase;
use App\Models\StripePayment;
use App\Repositories\BuyersRepository;
use App\Repositories\Order\OrderRepository;
use App\Repositories\Payment\PaymentTypesEnum;
use App\Services\Payment\Drivers\Stripe\StripeStatusesEnum;
use App\Services\Payment\Outcomes as PaymentOutcomes;
use Validator;

class StripeDriver extends AbstractPaymentDriver
{

    protected $additionalDataFields =
        [
            'public_key',
            'secret_key',
        ];

    public function getPaymentType()
    {
        return PaymentTypesEnum::STRIPE;
    }

    /**
     * @param array $validatedData
     *
     * @return Validator or null
     */
    public function getAdminValidator($validatedData)
    {
        $parentValidator = parent::getAdminValidator($validatedData);

        $rules =
            [
                'additional:public_key' => 'required',
                'additional:secret_key' => 'required',
            ];

        $attributes =
            [
                'additional:public_key' => trans('admin/settings.payments.custom_attributes.stripe.public_key'),
                'additional:secret_key' => trans('admin/settings.payments.custom_attributes.stripe.secret_key'),
            ];

        $parentValidator->setRules(array_merge($parentValidator->getRules(), $rules));
        $parentValidator->addCustomAttributes($attributes);

        return $parentValidator;
    }

    /**
     * @param string $template
     *
     * @return string
     */
    public function getAdminPaymentsSettingBlockTmlpPath($template = null)
    {
        return $template . '.settings.payments.systems.stripe';
    }

    /**
     * @param array $validatedData
     *
     * @return \Illuminate\Validation\Validator|Validator
     */
    public function getFrontValidator($validatedData)
    {
        return Validator::make(
            $validatedData,
            [
                'payment-form-tax' => 'max:40',
                'payment-form-vat' => 'numeric|required_if:payment-form-is-vat-payer,vat',
                'payment-form-name' => 'required|max:40',
                'payment-form-address' => 'required|max:40',
                'payment-form-phone' => 'max:40',
            ]);
    }

    /**
     * @param PaymentSystem $paymentSystem
     * @param Order $order
     * @param $paymentValue
     * @return mixed
     */
    public function makePayment(PaymentSystem $paymentSystem, Order $order, $paymentValue): PaymentOutcomes\AbstractPaymentOutcome
    {
        return \DB::transaction(function () use ($paymentSystem, $order, $paymentValue) {
            \Stripe\Stripe::setApiKey($paymentSystem->getAdditionalDataField('secret_key'));

            $sessionLineItems = [];
            $currency = strtolower($order->currency->code_alphabetic);
            foreach ($order->items as $orderItem) {
                $lineItem = [
                    'name' => $orderItem->compilation->getName(),
                    'amount' => (integer)($orderItem->price * 100),
                    'currency' => $currency,
                    'quantity' => 1,
                ];
                $sessionLineItems[] = $lineItem;
            }
            $sessionLineItems[] = [
                'name' => trans('site-' . $order->showcase->id . '/checkout.payment_step.table_delivery_price'),
                'amount' => (integer)($order->getPriceDetailsField('delivery_price')* 100),
                'currency' => $currency,
                'quantity' => 1,
            ];

            $session = \Stripe\Checkout\Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [array_values($sessionLineItems)],
                'success_url' => str_replace('CHECKOUT_SESSION_ID', '{CHECKOUT_SESSION_ID}', route('front.payments.endpoint',
                    [
                        'paymentsystem' => PaymentTypesEnum::STRIPE,
                        'paymentid' => 'CHECKOUT_SESSION_ID',
                        'status' => 'success',
                        'orderId' => $order->id,
                        'paymentSystemId' => $paymentSystem->id,
                    ])),
                'cancel_url' => str_replace('CHECKOUT_SESSION_ID', '{CHECKOUT_SESSION_ID}', route('front.payments.endpoint',
                    [
                        'paymentsystem' => PaymentTypesEnum::STRIPE,
                        'paymentid' => 'CHECKOUT_SESSION_ID',
                        'status' => 'fail',
                        'orderId' => $order->id,
                        'paymentSystemId' => $paymentSystem->id,
                    ])),
            ]);

            return new PaymentOutcomes\ClientRedirectPaymentOutcome($order, ['session' => $session, 'public_key' => $paymentSystem->getAdditionalDataField('public_key')]);
        });
    }

    public function proceedPaymentCallback(Showcase $showcase, $paymentId, $paymentStatus, $data) : PaymentOutcomes\AbstractPaymentOutcome
    {
        $order = Order::find(array_get($data, 'orderId'));
        $payment = new StripePayment();
        $payment->company_id = $showcase->company->id;
        $payment->showcase_id = $showcase->id;
        $payment->order_id = $order->id;
        $payment->payment_system_id = array_get($data, 'paymentSystemId');
        $payment->session_id = $paymentId;

        if( ! $paymentStatus)
        {
            $payment->status = StripeStatusesEnum::FAILED;
            $payment->save();

            return new PaymentOutcomes\CanceledPaymentOutcome($order);
        }

        $payment->status = StripeStatusesEnum::SUCCESS;
        $payment->value = $order->price;
        $payment->save();

        return \DB::transaction(function() use($order, $payment)
        {
            $buyersRepository = app(BuyersRepository::class);
            $orderRepository = app(OrderRepository::class);

            $orderBuyer = $order->user;

            $buyersRepository->saveBalanceTransaction($orderBuyer, $order->price, $payment, $order ? $order->number : null);

            $orderPayment = $orderRepository->createPayment($order, $order->price, $orderBuyer);

            $buyersRepository->saveBalanceTransaction($orderBuyer, - 1 * $order->price, $orderPayment, $order ? $order->number : null);

            return new PaymentOutcomes\OrderPaidPaymentOutcome($order);
        });


    }
}