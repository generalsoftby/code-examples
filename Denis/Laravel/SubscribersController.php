<?php


namespace App\Http\Controllers\Front;


use App\Jobs\SubscriberSendMessageJob;
use App\Models\Discount;
use App\Models\ShowcaseWidget;
use App\Models\Subscriber;
use App\Repositories\Discount\DiscountCouponStatusesEnum;
use Illuminate\Http\Request;

class SubscribersController extends Controller
{
    public function saveSubscriber(Request $request, ShowcaseWidget $widget)
    {
        $this->validate($request, [
            'name' => 'required',
            'surname' => 'required',
            'email' => 'required|email',
        ]);
        $widgetSettings = $widget->showcaseWidgetSettings->setting;
        if (array_key_exists('issue_discount', $widgetSettings) && $widgetSettings['issue_discount']) {
            $discount = Discount::query()->find(array_key_exists('discount', $widgetSettings) ? $widgetSettings['discount'] : 0);
            $coupon = $discount ? $discount->coupons()->whereNull('date_first_active')->where('status', DiscountCouponStatusesEnum::PENDING)->first() : null;
        }

        $subscriber = new Subscriber();
        $subscriber->firstname = $request->get('name');
        $subscriber->surname = $request->get('surname');
        $subscriber->email = $request->get('email');
        if (isset($discount) && $discount) {
            $subscriber->discount()->associate($discount);
        }
        if (isset($coupon) && $coupon) {
            $subscriber->coupon()->associate($coupon);
        }
        $subscriber->save();

        if (isset($coupon) && $coupon) {
            $coupon->status = DiscountCouponStatusesEnum::SENT;
            $coupon->save();
        }

        if (array_key_exists('send_message', $widgetSettings) && $widgetSettings['send_message']) {
            $this->dispatch(new SubscriberSendMessageJob($widget->container->showcase, $subscriber, array_key_exists('email_subject', $widgetSettings) ? $widgetSettings['email_subject'] : '', array_key_exists('text', $widgetSettings) ? $widgetSettings['text'] : ''));
        }

        return [
            'status' => 200,
        ];
    }

    public function checkEmail(Request $request)
    {
        $this->validate($request,[
            'widget-subscribe-form-email' => 'required|unique:subscribers,email',
        ]);

        return ['status' => 'ok'];
    }

}