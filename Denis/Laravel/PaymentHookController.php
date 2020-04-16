<?php

namespace App\Http\Controllers\Front;

use App\Repositories\Payment\PaymentTypesEnum;
use App\Services\Payment\PaymentService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class PaymentHookController extends Controller
{
    public function receiveAdyenHook(Request $request)
    {
        $params = $request->all();
        app(PaymentService::class)->proceedPaymentCallback($this->showcase, PaymentTypesEnum::ADYEN, $params['merchantReference'], $params['success'] === 'true', $params);

        return response(['[accepted]']);
    }

    public function receiveWayforpayHook(Request $request)
    {
        $params = $request->all();
        foreach ($params as $key => $value) {
            $params = $key;
        }
        $params = json_decode(str_replace('_', '.', stripcslashes($params)), true);
        $paymentOutcome = app(PaymentService::class)->proceedPaymentCallback($this->showcase, PaymentTypesEnum::WAYFORPAY, $params['orderReference'], $params['transactionStatus'] === 'Approved', $params);

        $order = $paymentOutcome->getOrder();
        $responseParams = [
            'orderReference' => $params['orderReference'],
            'status' => 'accept',
            'time' => (new Carbon())->timestamp,
        ];
        $responseParams['signature'] = hash_hmac('md5',
            implode(';', [
                $responseParams['orderReference'],
                $responseParams['status'],
                $responseParams['time'],
            ]),
            $order->paymentSystem->getAdditionalDataField('merchant_secret_key')
        );

        return response($responseParams);
    }
}
