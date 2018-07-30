<?php

namespace App\Http\Controllers\Admin;

use App\Repositories\Product\ProductRepository;
use App\Repositories\Slug\SlugsRepository;
use App\Repositories\Widgets\WidgetsContainerTypesEnum;
use App\Scopes\SetLanguageScope;
use Illuminate\Http\Request;
use AdminConfigurationService;
use App\Models\ProductCategory;
use App\Repositories\Product\ProductCategoryRepository;
use App\Repositories\Product\ProductStructureRepository;
use App\Repositories\Widgets\WidgetRepository;

class ProductsCategoryController extends ControllerAdmin
{
    private $slugRepository;
    protected $productCategoryRepository;
    protected $productStructureRepository;

    public function __construct(ProductCategoryRepository $productCategoryRepository,
                                ProductStructureRepository $productStructureRepository,
                                SlugsRepository $slugRepository)
    {
        parent::__construct();

        $this->productCategoryRepository = $productCategoryRepository;
        $this->productStructureRepository = $productStructureRepository;
        $this->slugRepository = $slugRepository;
    }

    public function index(Request $request)
    {
        return response('');
    }

    /**
     * @param Request $request
     * @param WidgetRepository $widgetRepository
     * @param ProductCategory $category
     * @param string $tab
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request, WidgetRepository $widgetRepository, ProductCategory $category, $tab = 'main')
    {
        $admin = $request->user('admin');
        $company = $admin->currentCompany;
        $showcase = AdminConfigurationService::getCurrentShowcase();
        if ( ! $category->company->is($company))
            abort(404);

        $currentLang = $showcase->languages()->where('id', $request->input('language_id'))->first() ? : $showcase->defaultLanguage;
        $showcaseLangs = $showcase->languages;

        $categoryDescription = $category
            ->descriptionsL10n()
            ->withoutGlobalScopes([SetLanguageScope::class])
            ->where('language_id', $currentLang->id)
            ->first();

        $viewShareData = compact('category', 'categoryDescription', 'showcase', 'company', 'currentLang', 'showcaseLangs', 'tab');

        switch($tab){
            case 'main':
                $optionsTreeCategory =  $this->productCategoryRepository->getShowcaseProductCategoryTreeSelect($showcase, $category->category_id, [$category->id]);
                $viewShareData = array_merge($viewShareData,
                    compact('optionsTreeCategory')
                );
                break;
            case 'seo':
                break;
            case 'page':

                $widgetContainer = $widgetRepository->getOrCreateWidgetContainer($category, WidgetsContainerTypesEnum::PRODUCT_CATEGORY, $showcase);
                $allContainerWidgets = $widgetRepository->getWidgetsForContainer($widgetContainer);
                $activeWidgets = $widgetRepository->getContainerItemsMap($widgetContainer);

                $viewShareData = array_merge($viewShareData,
                    compact('widgetContainer', 'allContainerWidgets', 'activeWidgets')
                );
                break;
        }

        if ($request->ajax())
        {
            return view($this->template_admin . '.products.categories.edit.tabs.' . $tab, $viewShareData);
        } else {
            $cat = clone $category;
            $catsTree = [];
            $catsTree[0] = $collectionFancytreeObjects = $this->productStructureRepository->getListByCategoryForFancytree($company, $showcase, null);;

            while ($cat)
            {
                $collectionFancytreeObjects = $this->productStructureRepository->getListByCategoryForFancytree($company, $showcase, $cat);
                $catsTree[$cat->id] = $collectionFancytreeObjects;
                $cat = $cat->category;
            }

            $viewShareData = array_merge($viewShareData,
                compact('catsTree')
            );
            return view($this->template_admin . '.products.categories.edit.index', $viewShareData);
        }
    }

    public function update(Request $request, ProductCategory $category, $tab = 'main')
    {
        $admin = $request->user('admin');
        $company = $admin->currentCompany;
        $showcase = AdminConfigurationService::getCurrentShowcase();
        $currentLang = $showcase->languages()->where('id', $request->input('language_id'))->first() ? : $showcase->defaultLanguage;
        if ( ! $category->company->is($company))
            abort(404);

        switch($tab){
            case 'main':
                $this->validate($request,
                    [
                        'name_list' => 'required',
                        'public_name' => 'required',
                        'slug' => ($category->getSlug($currentLang->id) !== $request->input('slug')) ? ('required|unique:slugs,slug,NULL,id,showcase_id,' . $showcase->id) : '',
                        'breadcrumbs_title' => 'required',
                        'disсount' => 'numeric',
                    ]);
                break;
            case 'seo':
                break;
            case 'page':
                break;
        }

        $this->productCategoryRepository->save($category, $request, $currentLang, $tab);

        if ($request->has('slug')) {
            $this->slugRepository->updateSlug($category, $request->input('slug'), $currentLang->id);
        }

        return response([
            'show_message' => trans('admin/products_categories.category.save.message'),
            'category' => $category,
            'categoryShowcaseUrl' => $category->getShowcaseUrl($showcase, $currentLang),
        ], 200);
    }

    public function treeCategory(Request $request, ProductCategory $category)
    {
        $admin = $request->user('admin');
        $company = $admin->currentCompany;
        $showcase = AdminConfigurationService::getCurrentShowcase();
        if ( ! $category->company->is($company))
            abort(404);

        $collectionFancytreeObjects = $this->productStructureRepository->getListByCategoryForFancytree($company, $showcase, $category);
        $viewShareData = compact('collectionFancytreeObjects');
        return response(view($this->template_admin . '.products.categories.edit.blocks.collections', $viewShareData));
    }

    public function available(Request $request, ProductCategory $category)
    {
        $admin = $request->user('admin');
        $company = $admin->currentCompany;
        if ( ! $category->company->is($company))
            abort(404);
        $category->is_available = $request->input('status');
        $category->save();
        return response(trans('admin/products_categories.status.available.' . ($category->is_available ? 'on' : 'off')));
    }

    public function visible(Request $request, ProductCategory $category)
    {
        $admin = $request->user('admin');
        $company = $admin->currentCompany;
        if ( ! $category->company->is($company))
            abort(404);
        $category->is_visible = $request->input('status');
        $category->save();
        return response(trans('admin/products_categories.status.visible.' . ($category->is_visible ? 'on' : 'off')));
    }

    public function isNew(Request $request, ProductCategory $category)
    {
        $admin = $request->user('admin');
        $company = $admin->currentCompany;
        if ( ! $category->company->is($company))
            abort(404);
        $category->is_new = $request->input('status');
        $category->save();
        return response(trans('admin/products_categories.status.new.' . ($category->is_new ? 'on' : 'off')));
    }

    public function remove(Request $request, ProductCategory $category)
    {
        $admin = $request->user('admin');
        $company = $admin->currentCompany;
        if ( ! $category->company->is($company))
            abort(404);

        if($category->products->count() > 0){
            return response([
                'is_deleted' => '0',
                'show_message' => trans('admin/products_categories.remove.category.not.products'),
            ], 200);
        }
        if($category->categories->count() > 0){
            return response([
                'is_deleted' => '0',
                'show_message' => trans('admin/products_categories.remove.category.not.subcategory'),
            ], 200);
        }

        $category->delete();
        return response([
            'is_deleted' => '1',
            'show_message' => '',
        ], 200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function productCategorySearch(Request $request)
    {
        $model = ProductCategory::where('is_available', true);

        if($q = $request->get('q', false))
        {
            $model->where('title', 'LIKE', '%'.$q.'%');
        }

        if($showcaseId = $request->get('showcase'))
        {
            $model->where('showcase_id', $showcaseId);
        }

        return $model->paginate(20);
    }

    /**
     * @param Request $request
     * @param ProductRepository $productRepository
     * @param ProductCategoryRepository $categoryRepository
     * @param ProductCategory $category
     * @return \Illuminate\Http\JsonResponse
     */
    public function cloneCategory(Request $request, ProductRepository $productRepository, ProductCategoryRepository $categoryRepository, ProductCategory $category)
    {
        $replica = $categoryRepository->cloneCategory($category);

        return response()->json(
        [
            'message' => trans('admin/products_categories.clone.message'),
            'redirect_url' => route('admin.product.category.edit', $replica)
        ]);
    }
}
