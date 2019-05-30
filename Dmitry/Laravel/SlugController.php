<?php

namespace App\Http\Controllers\Front;

use AdminConfigurationService;
use App\Models;
use App\Repositories\CustomPages\StaticPageTypesEnum;
use App\Repositories\Slug\SlugsRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SlugController extends Controller
{
    private $slugRepository;

    /**
     * SlugController constructor.
     * @param SlugsRepository $slugRepository
     */
    public function __construct(SlugsRepository $slugRepository)
    {
        parent::__construct();

        $this->slugRepository = $slugRepository;
    }

    /**
     * @param Request $request
     * @param $slug
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|Response|\Illuminate\View\View
     * @throws \Throwable
     */
    public function index(Request $request, $slug)
    {
        $language = AdminConfigurationService::getCurrentLanguage();

        $obj = $this->slugRepository->getSlug($this->showcase, $slug, $language);

        if (is_null($obj)) {
            abort(Response::HTTP_NOT_FOUND );
        }

        if ($obj->slug != $slug) {
            return redirect()->route('slug.index', $obj->slug);
        }

        $entity = $obj->entity;

        switch (true)
        {
            case $entity instanceof Models\Product :
                return app(ProductController::class)->product($entity);

            case $entity instanceof Models\ProductCategory:
                return app(ProductController::class)->category($entity);

            case $entity instanceof Models\Page:
                switch ($entity->static_page_type) {
                    case StaticPageTypesEnum::BLOG_PAGE :
                        return app(BlogController::class)->index($request);
                    case StaticPageTypesEnum::FAQ_PAGE :
                        return app(FaqController::class)->index();
                    case StaticPageTypesEnum::CONTACTS_PAGE :
                        return app(PageController::class)->contacts();
                }

                return app(CustomPageController::class)->single($entity);

            case $entity instanceof Models\PageCategory:
                return app(CustomPageController::class)->filter($entity);

            case $entity instanceof Models\Blog:
                return app(BlogController::class)->single($entity);

            case $entity instanceof Models\BlogCategory:
                return app(BlogController::class)->category($entity);

            case $entity instanceof Models\Faq:
                return app(FaqController::class)->single($entity);

            case $entity instanceof Models\FaqCategory:
                return app(FaqController::class)->filter($entity);

        }

        return abort(Response::HTTP_NOT_FOUND );
    }
}
