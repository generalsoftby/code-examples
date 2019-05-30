<?php

namespace App\Http\Controllers\Admin;

use App\Models;
use App\Repositories\ReviewsRepository;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;

class ReviewsController extends ControllerAdmin
{
    private $repository;

    /**
     * ReviewsController constructor.
     * @param ReviewsRepository $repository
     */
    public function __construct(ReviewsRepository $repository)
    {
        parent::__construct();

        $this->repository = $repository;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $user = $request->user('admin');
        $company = $user->currentCompany;
        $showcase = \AdminConfigurationService::getCurrentShowcase();

        $reviews = Models\Review::where('company_id', $company->id)
            ->where('showcase_id', $showcase->id);

        if ($request->get('by_status') != null) {
            $reviews->where('status', $request->get('by_status'));
        }

        if ($request->get('by_lang') != null) {
            $reviews->where('language_id', $request->get('by_lang'));
        }

        if ($request->get('by_rating') != null) {
            switch ($request->get('by_rating')) {
                case 'positive' :
                    $reviews->where('rating', '>', 3);
                    break;
                case 'negative' :
                    $reviews->where('rating', '<=', 3);
                    break;
            }
        }

        if ($request->get('by_date') != null) {
            $filterByDate = explode('-', $request->get('by_date'));

            $reviews->whereDate('reviews.created_at', '>=', new \DateTime($filterByDate[0]));
            $reviews->whereDate('reviews.created_at', '<=', new \DateTime($filterByDate[1]));
        }

        if ($byQuery = $request->input('by_query')) {
            switch ($request->input('by_query_type')) {
                case 'customer' :
                    $reviews->where('customer_name', 'like', '%' . $byQuery . '%');
                    break;
                case 'review' :
                    $reviews->where('review', 'like', '%' . $byQuery . '%');
                    break;
            }
        }

        $counters =
            [
                'positive_count' => (clone $reviews)->where('rating', '>', 3)->count(),
                'negative_count' => (clone $reviews)->where('rating', '<=', 3)->count(),
                'average_rating' => (clone $reviews)->avg('rating'),
            ];

        if ($request->ajax()) {
            return Datatables::of($reviews)
                ->with(compact('counters'))
                ->make(true);
        }

        $products = Models\Product::where('company_id', $company->id)
            ->where('showcase_id', $showcase->id)->get();

        $categories = Models\ProductCategory::where('company_id', $company->id)
            ->where('showcase_id', $showcase->id)->get();

        return view('mainadmin.reviews.index',
            compact('company', 'showcase', 'reviews', 'counters', 'products', 'categories')
        );
    }

    /**
     * @param Models\Review $review
     * @return \Illuminate\Http\JsonResponse
     * @throws \Exception
     */
    public function delete(Models\Review $review)
    {
        $review->delete();

        return response()->json([
            'message' => trans('admin/reviews.message.removed')
        ]);
    }

    /**
     * @param $status
     * @param Models\Review $review
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeStatus($status, Models\Review $review)
    {
        $review->status = $status;
        $review->update();

        return response()->json([
            'message' => trans('admin/reviews.message.status_changed')
        ]);
    }

    /**
     * @param Request $request
     * @param Models\Review|null $review
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function save(Request $request, Models\Review $review = null)
    {
        $this->validate($request,
            [
                'customer_name' => 'required',
                'company_position_etc' => 'required',
                'review' => 'required',
                'language_id' => 'required',
                'rating' => 'required',
            ]);

        $admin = $request->user('admin');
        $company = $admin->currentCompany;
        $showcase = \AdminConfigurationService::getCurrentShowcase();

        $data = $request->all();
        $data['company_id'] = $company->id;
        $data['showcase_id'] = $showcase->id;
        $data['ip'] = $request->ip();

        $review = isset($review) ? $review : new Models\Review();

        $this->repository->saveReview($review, $data);

        return response('');
    }

    /**
     * @param Request $request
     * @param Models\Review $review
     * @return \Illuminate\Http\JsonResponse
     * @throws \Throwable
     */
    public function edit(Request $request, Models\Review $review)
    {
        $admin = $request->user('admin');
        $company = $admin->currentCompany;
        $showcase = \AdminConfigurationService::getCurrentShowcase();

        $products = Models\Product::where('company_id', $company->id)
            ->where('showcase_id', $showcase->id)->get();

        $categories = Models\ProductCategory::where('company_id', $company->id)
            ->where('showcase_id', $showcase->id)->get();

        $arrProducts = $review->products->pluck('id')->toArray();
        $arrCategories = $review->categories->pluck('id')->toArray();

        return response()->json([
            'view' => view('mainadmin.reviews.modals.edit',
                compact('review', 'showcase', 'categories', 'products', 'arrProducts', 'arrCategories'))->render(),
        ]);
    }
}
