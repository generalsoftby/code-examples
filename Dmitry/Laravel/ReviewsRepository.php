<?php

namespace App\Repositories;

use App\Classes\ReviewStatusEnum;
use App\Models;

class ReviewsRepository
{
    /**
     * @var AttachmentsRepository
     */
    protected $attachmentsRepository;

    /**
     * ReviewsRepository constructor.
     * @param AttachmentsRepository $attachmentsRepository
     */
    public function __construct(AttachmentsRepository $attachmentsRepository)
    {
        $this->attachmentsRepository = $attachmentsRepository;
    }

    /**
     * @param Models\Review $review
     * @param $data
     * @param Models\Buyer|null $buyer
     */
    public function saveReview(Models\Review $review, $data, Models\Buyer $buyer = null)
    {
        if (!array_has($data, 'review_id')) {
            $review->company_id = array_get($data, 'company_id');
            $review->showcase_id = array_get($data, 'showcase_id');
            $review->status = ReviewStatusEnum::NEW;
        }

        $review->language_id = array_get($data, 'language_id');
        $review->ip_address = array_get($data, 'ip');
        $review->customer_name = array_get($data, 'customer_name');
        $review->company_position_etc = array_get($data, 'company_position_etc');
        $review->rating = array_get($data, 'rating');
        $review->show_in_widgets = array_get($data, 'show_in_widgets') == 'on' ? 1 : 0;
        $review->reply = array_get($data, 'reply');
        $review->review = array_get($data, 'review');
        $review->avatar_id = array_get($data, 'attachment_id') == 'null' ? null : array_get($data, 'attachment_id');

        if (isset($buyer)) {
            $review->buyer()->associate($buyer);
        }

        if ($avatar = array_get($data, 'avatar'))
        {
            $review->avatar()->associate($this->attachmentsRepository->handleUploadedFile($review->showcase->company, $review->showcase, $buyer, $avatar));
        }

        $review->save();

        if (array_get($data, 'show_on') != null) {
            $arrProducts = array();
            $arrCategories = array();

            foreach (array_get($data, 'show_on') as $item) {
                $arr = json_decode($item);

                if ($arr[0] == Models\Product::class) {
                    $arrProducts[] = $arr[1];
                } else if ($arr[0] == Models\ProductCategory::class) {
                    $arrCategories[] = $arr[1];
                }
            }

            if (isset($arrProducts)) {
                $review->products()->sync($arrProducts);
            } else {
                $review->products()->detach();
            }

            if (isset($arrCategories)) {
                $review->categories()->sync($arrCategories);
            } else {
                $review->categories()->detach();
            }
        } else {
            $review->categories()->detach();
            $review->products()->detach();
        }
    }
}
