<?php

namespace App\Http\Controllers\Admin;

use App\Models;
use App\Classes\CreatorEnum;
use App\Models\Consumable;
use App\Models\ConsumableCategory;
use App\Models\ProductEntityTabProductionSetting;
use App\Models\ProductionPhase;
use App\Repositories\Product\ProductIntegrationsRepository;
use App\Repositories\Slug\SlugsRepository;
use App\Repositories\Widgets\WidgetRepository;
use App\Repositories\Widgets\WidgetsContainerTypesEnum;
use App\Scopes\SetLanguageScope;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use AdminConfigurationService;
use App\Classes\ProductKindEnum;
use App\Models\Admin;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductImage;
use App\Models\ProductContent;
use App\Models\ProductComponent;
use App\Repositories\Product\ProductStructureRepository;
use App\Repositories\Product\ProductRepository;
use App\Repositories\Product\ProductCategoryRepository;
use App\Repositories\Product\ProductComponentRepository;
use App\Repositories\AttachmentsRepository;

class ProductsController extends ControllerAdmin
{
    private $slugRepository;
    private $productIntegrationsRepository;

    public function __construct(SlugsRepository $slugRepository, ProductIntegrationsRepository $productIntegrationsRepository)
    {
        parent::__construct();

        $this->slugRepository = $slugRepository;
        $this->productIntegrationsRepository = $productIntegrationsRepository;
    }

    /**
     * @param Request $request
     * @param ProductStructureRepository $productStructureRepository
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function catalog(Request $request, ProductStructureRepository $productStructureRepository)
    {
        /** @var Admin $user */
        $user = $request->user('admin');
        $company = $user->currentCompany;
        $showcase = AdminConfigurationService::getCurrentShowcase();

        /** @var ProductCategory|null $category */
        $category = $request->has('categoryId') ?
            ProductCategory::query()
                ->where('company_id', $company->id)
                ->where('showcase_id', $showcase->id)
                ->findOrFail($request->input('categoryId')) :
            null;

        if ($request->ajax())
        {
            return response()
                ->json($productStructureRepository->getListByCategoryForFancytree($company, $showcase, $category));
        }

        return view($this->template_admin . '.products.catalog.index', compact('company', 'showcase'));
    }

    /**
     * @param Request $request
     * @param ProductCategoryRepository $productCategoryRepository
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addCategoryModal(Request $request, ProductCategoryRepository $productCategoryRepository)
    {
        $optionsTreeCategory = $productCategoryRepository->getShowcaseProductCategoryTreeSelect(AdminConfigurationService::getCurrentShowcase());

        return view('mainadmin.products.catalog.modals.add-category', compact('optionsTreeCategory'));
    }

    /**
     * @param Request $request
     * @param ProductStructureRepository $productStructureRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function addCategory(Request $request, ProductStructureRepository $productStructureRepository)
    {
        /** @var Admin $user */
        $user = $request->user('admin');
        $company = $user->currentCompany;
        $showcase = AdminConfigurationService::getCurrentShowcase();

        /** @var ProductCategory|null $parentCategory */
        $parentCategory = $request->has('categoryId') ?
            ProductCategory::query()
                ->where('company_id', $company->id)
                ->where('showcase_id', $showcase->id)
                ->findOrFail($request->input('categoryId')) :
            null;

        $this->validate($request,
            [
                'title' => 'required',
            ]);

        $productStructureRepository->addCategory($company, $showcase, $parentCategory, $request->input('title'));

        return response('');
    }

    /**
     * @param Request $request
     * @param ProductCategoryRepository $productCategoryRepository
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function addProductModal(Request $request, ProductCategoryRepository $productCategoryRepository)
    {
        $optionsTreeCategory = $productCategoryRepository->getShowcaseCategoryTreeMultiSelect(AdminConfigurationService::getCurrentShowcase());

        return view('mainadmin.products.catalog.modals.add-product', compact('optionsTreeCategory'));
    }

    /**
     * @param Request $request
     * @param ProductStructureRepository $productStructureRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function addProduct(Request $request, ProductStructureRepository $productStructureRepository)
    {
        /** @var Admin $user */
        $user = $request->user('admin');
        $company = $user->currentCompany;
        $showcase = AdminConfigurationService::getCurrentShowcase();

        $this->validate($request,
            [
                'kind' => 'in:' . implode(',', ProductKindEnum::lists()),
                'title' => 'required',
            ]);

        $productStructureRepository->addProduct($company, $showcase, $request->input('kind'), $request->input('title'), $request->input('categoriesIds'));

        return response('');
    }

    /**
     * @param Request $request
     * @param ProductStructureRepository $productStructureRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function remove(Request $request, ProductStructureRepository $productStructureRepository)
    {
        /** @var Admin $user */
        $user = $request->user('admin');
        $company = $user->currentCompany;
        $showcase = AdminConfigurationService::getCurrentShowcase();

        $productStructureRepository->remove($company, $showcase, $request->input('categoriesIds'), $request->input('productsIds'));

        return response('');
    }

    /**
     * @param Request $request
     * @param ProductStructureRepository $productStructureRepository
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function move(Request $request, ProductStructureRepository $productStructureRepository)
    {
        /** @var Admin $user */
        $user = $request->user('admin');
        $company = $user->currentCompany;
        $showcase = AdminConfigurationService::getCurrentShowcase();

        $this->validate($request,
            [
                'mode' => 'in:over,before,after',
            ]);

        $productStructureRepository->move(
            $company,
            $showcase,
            $request->input('mode'),
            $request->input('categoryId'),
            $request->input('productId'),
            $request->input('destinationCategoryId'),
            $request->input('destinationProductId'));

        return response('');
    }

    public function edit(Request $request, WidgetRepository $widgetRepository, ProductStructureRepository $productStructureRepository, ProductCategoryRepository $productCategoryRepository, Product $product, $tab = 'main')
    {
        $admin = $request->user('admin');
        $company = $admin->currentCompany;
        $showcase = AdminConfigurationService::getCurrentShowcase();
        if ( ! $product->company->is($company))
            abort(404);

        $category = $product->categories->first();

        $currentLang = $showcase->languages()->where('id', $request->input('language_id'))->first() ? : $showcase->defaultLanguage;
        $showcaseLangs = $showcase->languages;

        $productDescription = $product
            ->descriptionsL10n()
            ->withoutGlobalScopes([SetLanguageScope::class])
            ->where('language_id', $currentLang->id)
            ->first();

        $viewShareData = compact('product', 'productDescription', 'category', 'showcase', 'company', 'currentLang', 'showcaseLangs', 'tab');

        switch($tab){
            case 'main':
                $default_categories = [];
                foreach ($product->categories as $cat_item) {
                    $default_categories[] = $cat_item->id;
                }
                $optionsTreeCategory =  $productCategoryRepository->getShowcaseCategoryTreeMultiSelect($showcase, $default_categories);

                $productSettingsMain = $product->settings_main;
                $productSettingsMainLang = null;
                if($productSettingsMain)
                    $productSettingsMainLang = $productSettingsMain->langs()->where('product_settings_main_langs.language_id', $currentLang->id)->first();
                $viewShareData = array_merge($viewShareData,
                    compact('optionsTreeCategory', 'productSettingsMain', 'productSettingsMainLang')
                );
                break;
            case 'content':
                $default_categories = [];
                foreach ($product->categories as $cat_item) {
                    $default_categories[] = $cat_item->id;
                }
                $optionsTreeCategory =  $productCategoryRepository->getShowcaseCategoryTreeMultiSelect($showcase, $default_categories);

                $productSettingsMain = $product->settings_main;
                $productSettingsMainLang = null;
                if($productSettingsMain)
                    $productSettingsMainLang = $productSettingsMain->langs()->where('product_settings_main_langs.language_id', $currentLang->id)->first();
                $viewShareData = array_merge($viewShareData,
                    compact('optionsTreeCategory', 'productSettingsMain', 'productSettingsMainLang')
                );
                break;
            case 'page':
                $widgetContainer = $widgetRepository->getOrCreateWidgetContainer($product, WidgetsContainerTypesEnum::PRODUCT, $showcase);
                $allContainerWidgets = $widgetRepository->getWidgetsForContainer($widgetContainer);
                $activeWidgets = $widgetRepository->getContainerItemsMap($widgetContainer);

                $viewShareData = array_merge($viewShareData,
                    compact('widgetContainer', 'allContainerWidgets', 'activeWidgets')
                );

                break;
            case 'options':
                $options = $product->option()->orderBy('order')->get();

                $productionPhases = ProductionPhase::query()
                    ->where('company_id', $company->id)
                    ->get();

                $consumables = Consumable::query()
                    ->where('company_id', $company->id)
                    ->get();

                $viewShareData = array_merge($viewShareData,
                    compact('options', 'productionPhases', 'consumables'));

                break;
            case 'production':
                $productionPhases = ProductionPhase::query()
                    ->where('company_id', $company->id)
                    ->get();
                $entity_production_tab = $product->production;
                $viewShareData = array_merge($viewShareData, compact('productionPhases', 'entity_production_tab'));
                break;
            case 'consumables':
                $consumableCategories = ConsumableCategory::query()
                    ->where('company_id', $company->id)
                    ->get();
                $entity_consumable_tab = $product->consumables;
                $viewShareData = array_merge($viewShareData, compact('consumableCategories', 'entity_consumable_tab'));
                break;
            case 'integration':
                $integrations = $product->integrations()->orderBy('order')->get();

                $viewShareData = array_merge($viewShareData, compact('integrations'));

                break;
        }

        if ($request->ajax()) {
            return view($this->template_admin . '.products.edit.tabs.' . $tab, $viewShareData);
        } else {
            $cat = null;
            if ($category)
                $cat = clone $category;

            $catsTree = [];
            $catsTree[0] = $collectionFancytreeObjects = $productStructureRepository->getListByCategoryForFancytree($company, $showcase, null);;
            while ($cat){
                $collectionFancytreeObjects = $productStructureRepository->getListByCategoryForFancytree($company, $showcase, $cat);
                $catsTree[$cat->id] = $collectionFancytreeObjects;
                $cat = $cat->category;
            }

            $viewShareData = array_merge($viewShareData,
                compact('catsTree')
            );

            return view($this->template_admin . '.products.edit.index', $viewShareData);
        }

    }

    public function update(Request $request, ProductRepository $productRepository, Product $product, $tab = 'main')
    {
        $admin = $request->user('admin');
        $company = $admin->currentCompany;
        $showcase = AdminConfigurationService::getCurrentShowcase();
        if ( ! $product->company->is($company))
            abort(404);

        $currentLang = $showcase->languages()->where('id', $request->input('language_id'))->first() ? : $showcase->defaultLanguage;

        switch($tab){
            case 'main':
                $this->validate($request,
                    [
                        'name_for_list' => 'required',
                        'public_name' => 'required',
                        'slug' => ($product->getSlug($currentLang->id) !== $request->input('slug')) ? ('required|unique:slugs,slug,NULL,id,showcase_id,' . $showcase->id) : '',
                        'breadcrumbs_title' => 'required|max:30',
                        'quantity_from' => 'numeric',
                        'quantity_to' => 'numeric',
                        'quantity_step' => 'numeric',
                        'area_width_from' => 'numeric',
                        'area_width_to' => 'numeric',
                        'area_width_step' => 'numeric',
                        'area_height_from' => 'numeric',
                        'area_height_to' => 'numeric',
                        'area_height_step' => 'numeric',
                        'discount' => 'numeric',
                        'price_unit' => 'numeric',
                        'price_unit_area' => 'numeric',
                        'price_basic' => 'numeric',
                    ]);
                break;
            case 'seo':
                $this->validate($request,
                    [
                        'meta_title' => 'max:50',
                        'meta_description' => 'max:150',
                    ]);
                break;
        }

        $productRepository->save($product, $request, $currentLang, $tab);

        if ($request->has('slug')) {
            $this->slugRepository->updateSlug($product, $request->input('slug'), $currentLang->id);
        }

        return response([
            'show_message' => trans('admin/products.edit.page.save.message'),
            'productShowcaseUrl' => $product->getShowcaseUrl($showcase, $currentLang),
        ], 200);
    }

    public function available(Request $request, Product $product)
    {
        $admin = $request->user('admin');
        $company = $admin->currentCompany;
        if ( ! $product->company->is($company))
            abort(404);
        $product->is_available = $request->input('status');
        $product->save();
        return response(trans('admin/products.edit.page.block.info.status.available.' . ($product->is_available ? 'on' : 'off')));
    }

    public function visible(Request $request, Product $product)
    {
        $admin = $request->user('admin');
        $company = $admin->currentCompany;
        if ( ! $product->company->is($company))
            abort(404);
        $product->is_visible = $request->input('status');
        $product->save();
        return response(trans('admin/products.edit.page.block.info.status.visible.' . ($product->is_visible ? 'on' : 'off')));
    }

    public function isNew(Request $request, Product $product)
    {
        $admin = $request->user('admin');
        $company = $admin->currentCompany;
        if ( ! $product->company->is($company))
            abort(404);
        $product->is_new = $request->input('status');
        $product->save();
        return response(trans('admin/products.edit.page.block.info.status.new.' . ($product->is_new ? 'on' : 'off')));
    }

    public function removeEditPage(Request $request, Product $product)
    {
        $admin = $request->user('admin');
        $company = $admin->currentCompany;
        if ( ! $product->company->is($company))
            abort(404);
        $product->delete();
        return response([
            'is_deleted' => '1',
            'show_message' => '',
        ], 200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function productSearch(Request $request)
    {
        $model = Product::where('is_available', true);

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
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function saveCreatorSequence(Request $request)
    {
        $data = $request->all();

        $this->productIntegrationsRepository->saveSequence($data);

        return response([
            'message' => trans('admin/products.saveSuccess')
        ], 200);
    }

    /**
     * @param ProductEntityCreatorSetting $item
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function deleteCreator(Models\ProductEntityCreatorSetting $item)
    {
        $this->productIntegrationsRepository->deleteCreator($item);

        return response([
            'message' => trans('admin/products.removeSuccess')
        ]);
    }

    /**
     * @param ProductEntityCreatorSetting $item
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function enableCreator(Models\ProductEntityCreatorSetting $item)
    {
        $this->productIntegrationsRepository->enableCreator($item);

        return response(
                trans('admin/products.message.creator.' . ($item->enable ? 'enable' : 'disable'))
            );
    }

    /**
     * @param Request $request
     * @param $creator
     * @param Product $item
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function saveCreator(Request $request, $creator, Product $item)
    {
        $data = $request->all();

        $this->productIntegrationsRepository->saveCreator($creator, $item, $data);

        return response(
            trans('admin/products.saveSuccess')
        );
    }

    public function saveCreatorComponent(Request $request, $creator, ProductComponent $item)
    {
        $data = $request->all();

        $this->productIntegrationsRepository->saveCreatorComponent($creator, $item, $data);

        return response(
            trans('admin/products.saveSuccess')
        );
    }

    public function uploadImageAttachment(Request $request, Product $product, AttachmentsRepository $attachmentsRepository)
    {

        $showcase = AdminConfigurationService::getCurrentShowcase();
        $attachmentFile = $request->file('file_data');
        $attachment = $attachmentsRepository->handleUploadedFile($showcase->company, $showcase, null, $attachmentFile);

        $productImage = new ProductImage();
        $productImage->product_id = $product->id;
        $productImage->attachment_id = $attachment->id;
        $productImage->save();

        return response()->json(
            [
                'success' => true,
                'imageItemView' => view($this->template_admin . '.products.edit.tabs.content.images.item', compact('attachment'))->render(),
           ]);

    }

    // SETTING OPTION

    public function editSettingOption($language, Models\ProductEntityOptions $option)
    {
        $showcase = AdminConfigurationService::getCurrentShowcase();
        $currentLang = $showcase->languages()->where('id', $language)->first() ? : $showcase->defaultLanguage;

        $object = $option->optionable;
        $optionsExceptCurrent = $object->option()->where('id', '!=', $option->id)->get();

        return view($this->template_admin . '.products.edit.tabs.option.setting',
            compact('currentLang', 'option', 'object', 'optionsExceptCurrent'));
    }

    public function createSettingOption($language, $entity, $entity_id)
    {
        if($entity == 'component'){
            $object = ProductComponent::findOrFail($entity_id);
        }
        else{
            $object = Product::findOrFail($entity_id);
        }
        $showcase = AdminConfigurationService::getCurrentShowcase();
        $currentLang = $showcase->languages()->where('id', $language)->first() ? : $showcase->defaultLanguage;

        $optionsExceptCurrent = $object->option()->get();

        return view($this->template_admin . '.products.edit.tabs.option.setting',
            compact('currentLang', 'object', 'optionsExceptCurrent'));
    }

    public function saveSettingOption(Request $request, $entity, $entity_id, Models\ProductEntityOptions $option = null)
    {

        if($entity == 'component'){
            $object = ProductComponent::findOrFail($entity_id);
        }
        else{
            $object = Product::findOrFail($entity_id);
        }

        $showcase = AdminConfigurationService::getCurrentShowcase();
        $currentLang = $showcase
            ->languages()
            ->where('id', $request->get('language_id'))
            ->first() ? : $showcase->defaultLanguage;

        $setting = [
            'name' => $request->get('name'),
            'frontendName' => [],
            'viewType' => $request->get('view-type'),
            'costCalculationType' => $request->get('costCalculationType'),
            'quantityCalculationDependency' => $request->get('quantityCalculationDependency'),
            'selectionRequired' => $request->get('selection-required'),
            'depend' => $request->get('depend'),
            'excluded' => $request->get('excluded'),
            'creator' => $request->get('creator'),
        ];

        $resultSearch = null;

        if (is_null($option))
        {
            $option = new Models\ProductEntityOptions();
        }
        else
        {
            $setting['frontendName'] = $option->setting['frontendName'];
            $resultSearch = $this->search_array($setting['frontendName'], $currentLang->id);
        }

        if ($resultSearch)
        {
            $setting['frontendName'][$resultSearch-1] = [
                'name' => $request->get('frontend-name'),
                'language_id' => $currentLang->id,
            ];
        }
        else
        {
            $record['frontendName'][] = [
                'name' => $request->get('frontend-name'),
                'language_id' => $currentLang->id,
            ];

            if (isset($setting['frontendName'])) {
                $setting['frontendName'] = array_merge($setting['frontendName'], $record['frontendName']);
            }
            else {
                $setting['frontendName'] = $record['frontendName'];
            }
        }

        $option->setting = $setting;
        $object->option()->save($option);

        $optionsExceptCurrent = $object->option()->where('id', '!=', $option->id)->get();

        $row = view($this->template_admin . '.products.edit.tabs.option.row',
            compact('currentLang', 'object', 'option'))->render();

        $setting = view($this->template_admin . '.products.edit.tabs.option.setting',
            compact('currentLang', 'object', 'option', 'optionsExceptCurrent'))->render();

        return response()->json([
            'message' => trans('admin/products.saveSuccess'),
            'row' => $row,
            'option' => $option,
            'setting' => $setting,
        ]);
    }

    public function deleteSettingOption(Models\ProductEntityOptions $option)
    {
        $option->delete();

        return response()->json([
            'message' => trans('admin/products.saveSuccess'),
            'option' => $option,
        ]);
    }

    /**
     * @param $array
     * @param $login
     * @return bool|int|string
     */
    public function search_array($array, $login)
    {
        if (isset($array)) {
            foreach ($array as $key => $value) {
                if (array_search($login, $value)) {
                    return $key + 1;
                }
            }
        }

        return false;
    }

    public function changeEnableOption(Models\ProductEntityOptions $option)
    {
        $option->enable = ! $option->enable;
        $option->update();

        return response(
            trans('admin/products.saveSuccess')
        );
    }

    public function saveOptionSequence(Request $request)
    {
        $options = $request->get('options');
        $items = Models\ProductEntityOptions::whereIn('id', $options)->get();

        \DB::transaction(function () use ($items, $options) {
            foreach ($items as $item) {
                $item->order = array_search($item->id, $options);
                $item->save();
            }
        });

        return response([
            'message' => trans('admin/products.saveSuccess')
        ], 200);
    }

    // END SETTING OPTION

    public function editElementOption(Request $request, Models\ProductEntityOptions $option, $language)
    {
        $showcase = AdminConfigurationService::getCurrentShowcase();
        $currentLang = $showcase->languages()->where('id', $language)->first() ? : $showcase->defaultLanguage;

        $company = $request->user('admin')->currentCompany;

        $productionPhases = ProductionPhase::query()
            ->where('company_id', $company->id)
            ->get();

        $consumables = Consumable::query()
            ->where('company_id', $company->id)
            ->get();

        $elements = $option->element()->orderBy('order')->get();

        return view($this->template_admin . '.products.edit.tabs.option.element',
            compact('currentLang', 'productionPhases', 'consumables', 'elements', 'option', 'showcase'));
    }

    public function saveElementOption(Request $request, Models\ProductEntityOptions $option, $language)
    {
        $showcase = AdminConfigurationService::getCurrentShowcase();
        $currentLang = $showcase->languages()->where('id', $language)->first() ? : $showcase->defaultLanguage;

        $company = $request->user('admin')->currentCompany;

        $setting = [
            'name' => 'New element',
        ];

        $element = new Models\ProductEntityOptionElements();
        $element->setting = $setting;
        $option->element()->save($element);

        $productionPhases = ProductionPhase::query()
            ->where('company_id', $company->id)
            ->get();

        $consumables = Consumable::query()
            ->where('company_id', $company->id)
            ->get();

        $row = view($this->template_admin . '.products.edit.tabs.option.rowElement',
            compact('element', 'currentLang', 'productionPhases', 'consumables', 'showcase'))->render();

        return response()->json([
            'message' => trans('admin/products.saveSuccess'),
            'row' => $row,
            'element' => $element,
            'option' => $option,
        ]);
    }

    public function saveSettingElementOption(Request $request, Models\ProductEntityOptionElements $element, $language)
    {
        $setting = [
            'name' => $request->get('name'),
            'frontendName' => [],
            'price' => $request->get('price'),
            'description' => [],
            'productionPhases' => [],
            'consumables' => [],
            'image' => $request->has('image_url') ? $request->get('image_url') : 'none.jpeg',
        ];

        $resultSearch = null;

        if (isset($element->setting['frontendName'])) {
            $setting['frontendName'] = $element->setting['frontendName'];
            $resultSearch = $element->search_array($setting['frontendName'], $language, 'key');
        }

        if (!is_null($resultSearch))
        {
            $setting['frontendName'][$resultSearch] = [
                'name' => $request->get('frontend-name'),
                'language_id' => $language,
            ];
        }
        else
        {
            $record['frontendName'][] = [
                'name' => $request->get('frontend-name'),
                'language_id' => $language,
            ];

            if (isset($setting['frontendName'])) {
                $setting['frontendName'] = array_merge($setting['frontendName'], $record['frontendName']);
            }
            else {
                $setting['frontendName'] = $record['frontendName'];
            }
        }

        $resultSearch = null;

        if (isset($element->setting['description'])) {
            $setting['description'] = $element->setting['description'];
            $resultSearch = $element->search_array($setting['description'], $language, 'key');
        }

        if (!is_null($resultSearch))
        {
            $setting['description'][$resultSearch] = [
                'text' => $request->get('description'),
                'language_id' => $language,
            ];
        }
        else
        {
            $record['description'][] = [
                'text' => $request->get('description'),
                'language_id' => $language,
            ];

            if (isset($setting['description'])) {
                $setting['description'] = array_merge($setting['description'], $record['description']);
            }
            else {
                $setting['description'] = $record['description'];
            }
        }

        $arrPhases = $request->get('phases',[]);
        $arrMaxTimes = $request->get('max_time',[]);

        foreach($arrPhases as $key => $phase){
            $setting['productionPhases'][] = [
                'phase_id' => $phase,
                'max_time' => $arrMaxTimes[$key],
            ];
        }

        $arrConsumables = $request->get('consumables',[]);
        $arrQuantities = $request->get('quantities',[]);

        foreach($arrConsumables as $key => $consumable){
            $setting['consumables'][] = [
                'consumable_id' => $consumable,
                'quantity' => $arrQuantities[$key],
            ];
        }

        $option = $element->elementable;
        $element->setting = $setting;
        $option->element()->save($element);

        return response()->json([
            'message' => trans('admin/products.saveSuccess'),
            'element' => $element,
        ]);
    }

    public function saveElementsOptionSequence(Request $request)
    {

        $elements = $request->get('elements');
        $items = Models\ProductEntityOptionElements::whereIn('id', $elements)->get();

        \DB::transaction(function () use ($items, $elements) {
            foreach ($items as $item) {
                $item->order = array_search($item->id, $elements);
                $item->save();
            }
        });

        return response([
            'message' => trans('admin/products.saveSuccess')
        ], 200);
    }

    public function deleteSettingElementOption(Models\ProductEntityOptionElements $element)
    {
        $element->delete();
        $option = $element->elementable;

        return response()->json([
            'message' => trans('admin/products.saveSuccess'),
            'element' => $element,
            'option' => $option,
        ]);
    }

    public function addProductContent(Request $request, Product $product)
    {

        $productContent = new ProductContent();
        $productContent->product_id = $product->id;
        $productContent->type = $request->input('contentType');
        $productContent->save();
        return response()->json(
            [
                'show_message' => trans('admin/products.edit.page.tab.content.add.content.item'),
                //'imageItemView' => view($this->template_admin . '.products.edit.tabs.content.images.item', compact('attachment'))->render(),
            ]);

    }

    public function deleteProductContent(Request $request, ProductContent $productContent)
    {
        $productContent->delete();
        return response()->json(
            [
                'show_message' => trans('admin/products.edit.page.tab.content.delete.content.item'),
            ]);
    }

    public function isAvaliableProductContent(Request $request, ProductContent $productContent)
    {
        $productContent->is_avaliable = $request->input('status');
        $productContent->save();
        return response(trans('admin/products.edit.page.block.info.status.available.' . ($productContent->is_avaliable ? 'on' : 'off')));
    }

    public function uploadContentImageAttachment(Request $request, AttachmentsRepository $attachmentsRepository)
    {

        $showcase = AdminConfigurationService::getCurrentShowcase();
        $attachmentFile = $request->file('file_data');
        $attachment = $attachmentsRepository->handleUploadedFile($showcase->company, $showcase, null, $attachmentFile);

        return response()->json(
            [
                'success' => true,
                'attachment_id' => $attachment->id,
                'hash' => $attachment->hash,
                'url' => route('attachment.download', [$attachment->hash])
            ]);

    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function saveContentSequence(Request $request)
    {

        $contents = array_get($request, 'contents');
        $items = ProductContent::whereIn('id', $contents)->get();

        \DB::transaction(function () use ($items, $contents) {
            foreach ($items as $item) {
                $item->sort = array_search($item->id, $contents);
                $item->save();
            }
        });

        return response([
            'message' => trans('admin/products.saveSuccess')
        ], 200);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response|\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function saveComponentSequence(Request $request)
    {
        $components = array_get($request, 'components');
        $items = ProductComponent::whereIn('id', $components)->get();

        \DB::transaction(function () use ($items, $components) {
            foreach ($items as $item) {
                $item->sort = array_search($item->id, $components);
                $item->save();
            }
        });

        return response([
            'message' => trans('admin/products.saveSuccess')
        ], 200);
    }

    public function deleteProductComponent(Request $request, ProductComponent $productComponent)
    {
        $productComponent->delete();
        return response()->json(
            [
                'show_message' => trans('admin/products.edit.page.tab.component.delete.content.item'),
            ]);
    }

    public function isAvaliableProductComponent(Request $request, ProductComponent $productComponent)
    {
        $productComponent->is_avaliable = $request->input('status');
        $productComponent->save();
        return response(trans('admin/products.edit.page.tab.component.status.available.' . ($productComponent->is_avaliable ? 'on' : 'off')));
    }

    public function updateComponent(Request $request, ProductComponentRepository $productComponentRepository, Product $product, ProductComponent $productComponent, $tab = 'main')
    {
        $admin = $request->user('admin');
        $company = $admin->currentCompany;
        $showcase = AdminConfigurationService::getCurrentShowcase();
        $currentLang = $showcase->languages()->where('id', $request->input('language_id'))->first() ? : $showcase->defaultLanguage;

        $productComponent = $productComponentRepository->updateComponent($product, $productComponent, $request, $currentLang, $tab);

        return response([
            'show_message' => trans('admin/products.edit.page.tab.component.save.message'),
            'productComponent' => $productComponent
        ], 200);
    }

    public function addComponent(Request $request, ProductComponentRepository $productComponentRepository, Product $product)
    {
        $admin = $request->user('admin');
        $company = $admin->currentCompany;
        $showcase = AdminConfigurationService::getCurrentShowcase();
        $currentLang = $showcase->languages()->where('id', $request->input('language_id'))->first() ? : $showcase->defaultLanguage;

        $productComponent = $productComponentRepository->addComponent($product);

        return response([
            'show_message' => trans('admin/products.edit.page.tab.component.add.item'),
            'productComponent' => $productComponent,
            'productComponentView' => view($this->template_admin . '.products.edit.additional.area.item', compact('product', 'productComponent', 'currentLang'))->render(),
        ], 200);
    }

    public function viewComponentTab(Request $request, Product $product, ProductComponent $productComponent, $tab = 'options')
    {
        $admin = $request->user('admin');
        $company = $admin->currentCompany;
        $showcase = AdminConfigurationService::getCurrentShowcase();
        $currentLang = $showcase->languages()->where('id', $request->input('language_id'))->first() ? : $showcase->defaultLanguage;

        $viewShareData = compact('product', 'productComponent', 'showcase', 'company', 'currentLang', 'tab');

        switch($tab){
            case 'options':
                $options = $productComponent->option()->orderBy('order')->get();

                $productionPhases = ProductionPhase::query()
                    ->where('company_id', $company->id)
                    ->get();

                $consumables = Consumable::query()
                    ->where('company_id', $company->id)
                    ->get();

                $viewShareData = array_merge($viewShareData,
                    compact('options', 'productionPhases', 'consumables'));

                break;
            case 'production':
                $productionPhases = ProductionPhase::query()
                    ->where('company_id', $company->id)
                    ->get();
                $entity_production_tab = $productComponent->production;
                $viewShareData = array_merge($viewShareData, compact('productionPhases', 'entity_production_tab'));
                break;
            case 'consumables':
                $consumableCategories = ConsumableCategory::query()
                    ->where('company_id', $company->id)
                    ->get();
                $entity_consumable_tab = $productComponent->consumables;
                $viewShareData = array_merge($viewShareData, compact('consumableCategories', 'entity_consumable_tab'));
                break;
            case 'integration':
                $integrations = $productComponent->integrations()->orderBy('order')->get();
                $viewShareData = array_merge($viewShareData, compact('integrations'));
                break;
        }

        return response([
            'viewTab' => view($this->template_admin . '.products.edit.tabs.' . $tab, $viewShareData)->render()
        ], 200);


    }

    /**
     * @param Request $request
     * @param ProductRepository $productRepository
     * @param Product $product
     * @return \Illuminate\Http\JsonResponse
     */
    public function cloneEditPage(Request $request, ProductRepository $productRepository, Product $product)
    {
        $replica = $productRepository->cloneProduct($product);

        return response()->json(
        [
            'message' => trans('admin/products.clone.message'),
            'redirect_url' => route('admin.product.edit', $replica)
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cloneCatalog(Request $request, ProductRepository $productRepository)
    {
        $productsIds = $request->get('productsIds', []);

        if(count($productsIds) == 0)
        {
            return response()->json([
                'message' => trans('admin/products.modal.clone-to-other.submitted'),
            ]);
        }

        $products = Product::whereIn('id', $productsIds)->get();

        $showcase = Models\Showcase::find($request->get('showcase_id', null));

        \DB::transaction(function () use($productRepository, $products, $showcase)
        {
            $products->each(function (Product $product) use ($productRepository, $showcase)
            {
                $productRepository->cloneProduct($product, $showcase);
            });
        });

        return response()->json([
            'message' => trans('admin/products.modal.clone-to-other.submitted'),
        ]);
    }

}
