<?php

namespace App\Factory;

use App\Document\FieldSetting;
use App\Document\FieldSetting\Options\CurrencyOptions;
use App\Document\FieldSetting\Options\MultiSelectOptions;
use App\Document\FieldSetting\Options\SelectOptions;
use App\Document\LabelSetting;
use App\Enum\AddressType;
use App\Enum\FieldDataType;
use App\Enum\FieldTypeEnum;
use App\Generator\IncrementalIdGenerator;
use App\Model\ContextInterface;
use App\Repository\FieldSettingRepository;
use App\Repository\LabelSettingRepository;
use App\Service\ContextDataReferenceService;
use App\Service\FieldSettingService;
use App\Utils\MongoUtils;
use Doctrine\ODM\MongoDB\DocumentManager;
use Exception;
use Faker\Generator;
use MongoDB\BSON\ObjectId;
use Ramsey\Uuid\Uuid;

class ContactByContextRawFactory extends AbstractFactory
{
    private const REQUIRED_FIELD_DATA_TYPES = [
        FieldDataType::CONTACT_TYPE,
        FieldDataType::FIRST_NAME,
        FieldDataType::LAST_NAME,
        FieldDataType::ORGANIZATION,
        FieldDataType::EMAIL,
        FieldDataType::BIRTHDAY,
    ];

    /**
     * @var ContextDataReferenceService
     */
    private $contextDataReferenceService;

    /**
     * @var null|ContextInterface
     */
    private $context;

    /**
     * @var FieldSettingRepository
     */
    private $fieldSettingRepository;

    /**
     * @var LabelSettingRepository
     */
    private $labelSettingRepository;

    /**
     * @var IncrementalIdGenerator
     */
    private $idGenerator;

    /**
     * @var array
     */
    private $labelSettings = [];

    /**
     * @var array
     */
    private $fieldSettings = [];

    /**
     * @var bool
     */
    private $useAllLabels = false;

    /**
     * @var array
     */
    private $addressTypeFieldsPool = [];

    /**
     * RawContactFactory constructor.
     *
     * @param Generator                   $faker
     * @param ContextDataReferenceService $contextDataReferenceService Reference service to find known data in a context
     * @param DocumentManager             $documentManager
     * @param FieldSettingService         $fieldSettingService
     * @param IncrementalIdGenerator      $idGenerator
     */
    public function __construct(
        Generator $faker,
        ContextDataReferenceService $contextDataReferenceService,
        DocumentManager $documentManager,
        FieldSettingService $fieldSettingService,
        IncrementalIdGenerator $idGenerator
    ) {
        parent::__construct($faker);

        $this->fieldSettingRepository = $fieldSettingService->getRepository();

        $this->contextDataReferenceService = $contextDataReferenceService;
        $this->labelSettingRepository = $documentManager->getRepository(LabelSetting::class);
        $this->idGenerator = $idGenerator;
    }

    /**
     * @param ContextInterface $context
     *
     * @return ContactByContextRawFactory
     */
    public function setContext(ContextInterface $context): self
    {
        $this->context = $context;
        $this->fieldSettings = $this->fieldSettingRepository->findByContext($context->getId());
        $this->labelSettings = $this->labelSettingRepository->findByContext($context->getId());

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function createMany(int $count, array $defaults = [], bool $isContainedNumberId = false): array
    {
        if (isset($defaults['context']) && $defaults['context'] instanceof ContextInterface) {
            $this->setContext($defaults['context']);

            $defaults['context'] = new ObjectId($defaults['context']->getId());
        }

        if (isset($defaults['useAllLabels'])) {
            $this->useAllLabels = $defaults['useAllLabels'];
            unset($defaults['useAllLabels']);
        }

        if (null === $this->context) {
            throw new Exception('ContextContactFactory::createMany expect setContext before call');
        }

        return parent::createMany($count, $defaults, $isContainedNumberId);
    }

    /**
     * {@inheritdoc}
     */
    protected function create(array $defaults = [])
    {
        if (null === $this->context) {
            throw new Exception('ContextContactFactory::create expect setContext before call');
        }

        $now = new \MongoDB\BSON\UTCDateTime();

        $contact = [
            'keywords' => ['Businesspartner', $this->faker->text(15)],
            'targetGroups' => [],
            'labels' => [],
            'createdAt' => $now,
            'updatedAt' => $now,
            'context' => new ObjectId($this->context->getId()),
        ];

        foreach ($defaults as $key => $value) {
            $contact[$key] = $value;
        }

        if ($this->useAllLabels) {
            $contactLabelsSet = $this->labelSettings;
        } else {
            $requiredLabels = [];
            $customLabels = [];

            /** @var LabelSetting $labelSetting */
            foreach ($this->labelSettings as $labelSetting) {
                if ($labelSetting->isDefault()) {
                    $requiredLabels[] = $labelSetting;
                } else {
                    $customLabels[] = $labelSetting;
                }
            }

            $customLabelsCount = $this->faker->numberBetween(0, count($customLabels));
            $contactLabelsSet = array_merge($requiredLabels, $this->faker->randomElements($customLabels, $customLabelsCount, false));
        }

        /** @var LabelSetting $labelSetting */
        foreach ($contactLabelsSet as $labelSetting) {
            $contact['labels'][] = $this->generateLabel($this->context, $labelSetting);
        }

        return $contact;
    }

    /**
     * @param ContextInterface $context
     * @param LabelSetting     $labelSetting
     * @param FieldSetting     $fieldSetting
     *
     * @return null|string
     */
    protected function getFieldSettingDataType(ContextInterface $context, LabelSetting $labelSetting, FieldSetting $fieldSetting): ?string
    {
        foreach ($context->getFieldSettingsReferenceMap() as $referenceKey => $referenceData) {
            if ($referenceData['labelSetting'] === $labelSetting->getId() && $referenceData['fieldSetting'] === $fieldSetting->getId()) {
                return $referenceKey;
            }
        }

        return null;
    }

    /**
     * @param ContextInterface $context
     * @param LabelSetting     $labelSetting
     *
     * @return array
     *
     * @throws Exception
     */
    protected function generateLabel(ContextInterface $context, LabelSetting $labelSetting): array
    {
        $label = [
            'labelSetting' => new ObjectId($labelSetting->getId()),
            'fields' => [],
        ];

        $this->addressTypeFieldsPool = [];

        [$requiredFields, $customFields] = $this->allocateRequiredFieldSettings($context, $labelSetting);

        $customFieldsCount = $this->faker->numberBetween(1, count($customFields));
        $contactLabelFieldsSet = array_merge($requiredFields, $this->faker->randomElements($customFields, $customFieldsCount, false));

        /** @var FieldSetting $fieldSetting */
        foreach ($contactLabelFieldsSet as $fieldSetting) {
            /** @var FieldSetting $fieldSettingLoaded */
            $fieldSettingLoaded = $this->fieldSettings[$fieldSetting->getId()];

            /**
             * If this fieldSettings contains in context fieldSettingsReferenceMap
             * we will gets ReferenceMap key.
             */
            $fieldSettingDataType = $this->getFieldSettingDataType($context, $labelSetting, $fieldSettingLoaded);

            $field = $this->generateField($fieldSettingLoaded, $fieldSettingDataType);

            if (null === $field) {
                continue;
            }

            $label['fields'][] = $field;
        }

        return $label;
    }

    /**
     * @param FieldSetting $fieldSetting
     * @param null|string  $dataType
     *
     * @return null|array
     *
     * @throws Exception
     */
    protected function generateField(FieldSetting $fieldSetting, string $dataType = null): ?array
    {
        /** @var FieldSetting $fieldSettingLoaded */
        $fieldSettingLoaded = $this->fieldSettings[$fieldSetting->getId()];
        $fieldType = $fieldSettingLoaded->getOptions()->getType();

        $fieldValue = $this->generateValueForType($fieldType, $fieldSettingLoaded, null, $dataType);

        if ($fieldValue === []) {
            return null;
        }

        $fieldValue['fieldSetting'] = new ObjectId($fieldSettingLoaded->getId());

        return $fieldValue;
    }

    /**
     * @param ContextInterface $context
     * @param LabelSetting     $labelSetting
     * @param FieldSetting     $fieldSetting
     *
     * @return bool
     */
    protected function isRequiredContextFieldSetting(ContextInterface $context, LabelSetting $labelSetting, FieldSetting $fieldSetting)
    {
        if (!$labelSetting->isDefault()) {
            return false;
        }

        $contextReferenceMap = $context->getFieldSettingsReferenceMap();

        foreach (self::REQUIRED_FIELD_DATA_TYPES as $requiredType) {
            if (!isset($contextReferenceMap[$requiredType])) {
                return false;
            }

            $reference = $contextReferenceMap[$requiredType];

            if ($reference['labelSetting'] === $labelSetting->getId() && $reference['fieldSetting'] === $fieldSetting->getId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param ContextInterface $context
     * @param LabelSetting     $labelSetting
     *
     * @return array
     */
    protected function allocateRequiredFieldSettings(ContextInterface $context, LabelSetting $labelSetting): array
    {
        $requiredFieldsIds = [];

        foreach ($labelSetting->getRequiredGroups() as $requiredGroup) {
            $group = $requiredGroup->getGroup();
            if (0 === count(array_intersect($requiredFieldsIds, $group))) {
                $requiredFieldsIds[] = $this->faker->randomElement($group);
            }
        }

        $requiredFields = [];
        $customFields = [];

        foreach ($labelSetting->getFieldSettings() as $fieldSettingRef) {
            $fieldSetting = $fieldSettingRef->getSetting();
            if (
                $this->isRequiredContextFieldSetting($context, $labelSetting, $fieldSetting) ||
                in_array($fieldSetting->getId(), $requiredFieldsIds)
            ) {
                $requiredFields[] = $fieldSetting;
            } else {
                $customFields[] = $fieldSetting;
            }
        }

        return [$requiredFields, $customFields];
    }

    /**
     * @param string       $fieldType
     * @param FieldSetting $fieldSetting
     * @param null|mixed   $defaultValue
     * @param string       $dataType
     *
     * @return null|array
     *
     * @throws Exception
     */
    protected function generateValueForType(string $fieldType, FieldSetting $fieldSetting, $defaultValue = null, string $dataType = null): ?array
    {
        /*
         * If the field in the ReferenceMap generates less random data.
         */
        switch ($dataType) {
            case FieldDataType::FIRST_NAME:
                $value = $defaultValue ?? $this->faker->firstName;

                return $this->generateTextInputValue($fieldSetting, $value);
            case FieldDataType::LAST_NAME:
                $value = $defaultValue ?? $this->faker->lastName;

                return $this->generateTextInputValue($fieldSetting, $value);
            case FieldDataType::PHONE_NUMBER:
            case FieldDataType::MOBILE_PHONE_NUMBER:
                $value = $defaultValue ?? $this->faker->numerify('(###)###-##-##');

                return $this->generateTextInputValue($fieldSetting, $value);
            case FieldDataType::ORGANIZATION:
                $value = $defaultValue ?? $this->faker->company;

                return $this->generateTextInputValue($fieldSetting, $value);
            case FieldDataType::BIRTHDAY:
                // Hack to ensure there are no conversion issues afterwards, because birthday gets converted to a date without time.
                // TODO Calendar setting type must be extended to specify that no time shall be stored.
                $date = $this->faker->dateTimeBetween('-30 years', '-20 years');
                $date->setTime(0, 0);

                return $this->generateCalendarValue($fieldSetting, [[
                    'date' => new \MongoDB\BSON\UTCDateTime($date),
                    'type' => 'simple',
                ]]);
            default:
                if (!method_exists($this, $this->getMethodNameForType($fieldType))) {
                    throw new Exception("Unexpected field type for ContactByContextRawFactory ${fieldType}");
                }

                $methodName = $this->getMethodNameForType($fieldType);

                return $this->{$methodName}($fieldSetting, $defaultValue);
        }
    }

    /**
     * @param FieldSetting $fieldSetting
     * @param null|mixed   $defaultValue
     *
     * @return array
     */
    protected function generateTextInputValue(FieldSetting $fieldSetting, $defaultValue = null): array
    {
        $value = $defaultValue ?? $this->faker->word;

        return $this->createFieldSettingValueBlank(FieldTypeEnum::TEXT_INPUT, $value);
    }

    /**
     * @param FieldSetting $fieldSetting
     * @param null|mixed   $defaultValue
     *
     * @return array
     */
    protected function generateEmailValue(FieldSetting $fieldSetting, $defaultValue = null): array
    {
        $value = $defaultValue ?? [
            'email' => $this->faker->email,
            'permission' => 'unknown',
        ];

        return $this->createFieldSettingValueBlank(FieldTypeEnum::EMAIL, $value);
    }

    /**
     * @param FieldSetting $fieldSetting
     * @param null|mixed   $defaultValue
     *
     * @return array
     */
    protected function generateNumberInputValue(FieldSetting $fieldSetting, $defaultValue = null): array
    {
        $value = $defaultValue ?? $this->faker->randomNumber();

        return $this->createFieldSettingValueBlank(FieldTypeEnum::NUMBER_INPUT, $value);
    }

    /**
     * @param FieldSetting $fieldSetting
     * @param null|mixed   $defaultValue
     *
     * @return array
     */
    protected function generateTextAreaValue(FieldSetting $fieldSetting, $defaultValue = null): array
    {
        $value = $defaultValue ?? $this->faker->text();

        return $this->createFieldSettingValueBlank(FieldTypeEnum::TEXTAREA, $value);
    }

    /**
     * @param FieldSetting $fieldSetting
     * @param null|mixed   $defaultValue
     *
     * @return array
     *
     * @throws Exception
     */
    protected function generateCurrencyValue(FieldSetting $fieldSetting, $defaultValue = null): array
    {
        if (null === $defaultValue) {
            /** @var CurrencyOptions $options */
            $options = $fieldSetting->getOptions();

            if (!($options instanceof CurrencyOptions)) {
                throw new Exception('Unexpected fieldSetting options class ' . get_class($options));
            }

            $possibleValues = $options->getCurrencies();

            if (0 === count($possibleValues)) {
                return [];
            }

            $value = $this->faker->randomElement($possibleValues);
        } else {
            $value = $defaultValue;
        }

        return $this->createFieldSettingValueBlank(FieldTypeEnum::CURRENCY, $value);
    }

    /**
     * @param FieldSetting $fieldSetting
     * @param null|mixed   $defaultValue
     *
     * @return array
     */
    protected function generateAddressValue(FieldSetting $fieldSetting, $defaultValue = null): array
    {
        $value = $defaultValue ?? [
            'addressId' => MongoUtils::uuidToBinary(Uuid::uuid4()),
            'salutation' => $this->faker->word,
            'title' => $this->faker->sentence(3),
            'firstName' => $this->faker->firstName,
            'lastName' => $this->faker->lastName,
            'email' => $this->faker->email,
            'phoneNumber' => $this->faker->phoneNumber,
            'mobilePhoneNumber' => $this->faker->phoneNumber,
            'organisation' => $this->faker->company,
            'addressSuffix' => $this->faker->word,
            'addressType' => $this->faker->randomElement(AddressType::toArray()),
            'street' => $this->faker->streetName . ' ' . $this->faker->buildingNumber,
            'zipCode' => $this->faker->postcode,
            'city' => $this->faker->city,
            'state' => $this->faker->state,
            'country' => $this->faker->countryCode,
            'active' => false,
        ];

        $addressType = $value['addressType'];

        if (!isset($this->addressTypeFieldsPool[$addressType])) {
            $this->addressTypeFieldsPool[$addressType] = false;
        }

        if (!$this->addressTypeFieldsPool[$addressType]) {
            $value['active'] = true;
            $this->addressTypeFieldsPool[$addressType] = true;
        }

        return $this->createFieldSettingValueBlank(FieldTypeEnum::ADDRESS, $value);
    }

    /**
     * @param FieldSetting $fieldSetting
     * @param null|mixed   $defaultValue
     *
     * @return array
     */
    protected function generateBankAccountValue(FieldSetting $fieldSetting, $defaultValue = null): array
    {
        $value = $defaultValue ?? [
            'accountNumber' => $this->faker->bankAccountNumber,
            'bank' => $this->faker->company,
            'bankCode' => $this->faker->numberBetween(10000, 99999),
            'bic' => mb_strtoupper($this->faker->bothify('????DEFF###')),
            'iban' => null,
            'depositor' => $this->faker->lastName,
        ];

        return $this->createFieldSettingValueBlank(FieldTypeEnum::BANK_ACCOUNT, $value);
    }

    /**
     * @param FieldSetting $fieldSetting
     * @param null|mixed   $defaultValue
     *
     * @return array
     *
     * @throws Exception
     */
    protected function generateSelectValue(FieldSetting $fieldSetting, $defaultValue = null): array
    {
        if (null === $defaultValue) {
            /** @var SelectOptions $options */
            $options = $fieldSetting->getOptions();

            if (!($options instanceof SelectOptions)) {
                throw new Exception('Unexpected fieldSetting options class ' . get_class($options));
            }

            $possibleValues = $options->getDropdown();

            if (0 === count($possibleValues)) {
                return [];
            }

            $value = $this->faker->randomElement($possibleValues);
        } else {
            $value = $defaultValue;
        }

        return $this->createFieldSettingValueBlank(FieldTypeEnum::SELECT, $value);
    }

    /**
     * @param FieldSetting $fieldSetting
     * @param null|mixed   $defaultValue
     *
     * @return array
     *
     * @throws Exception
     */
    protected function generateMultiSelectValue(FieldSetting $fieldSetting, $defaultValue = null): array
    {
        if (null === $defaultValue) {
            /** @var MultiSelectOptions $options */
            $options = $fieldSetting->getOptions();

            if (!($options instanceof MultiSelectOptions)) {
                throw new Exception('Unexpected fieldSetting options class ' . get_class($options));
            }

            $possibleValues = $options->getDropdown();

            if (0 === count($possibleValues)) {
                return [];
            }

            $valuesCount = $this->faker->numberBetween(1, count($possibleValues));
            $value = $this->faker->randomElements($possibleValues, $valuesCount, false);
        } else {
            $value = $defaultValue;
        }

        return $this->createFieldSettingValueBlank(FieldTypeEnum::MULTISELECT, $value);
    }

    /**
     * @param FieldSetting $fieldSetting
     * @param null|mixed   $defaultValue
     *
     * @return array
     */
    protected function generateCalendarValue(FieldSetting $fieldSetting, $defaultValue = null): array
    {
        if (null === $defaultValue) {
            /** @var FieldSetting\Options\CalendarOptions $options */
            $options = $fieldSetting->getOptions();
            $date = $this->faker->dateTimeBetween('-40 years', '-18 years');
            $endDate = $options->isRange()
                ? $this->faker->dateTimeBetween($date, '-18 years')
                : false;

            $value = false !== $endDate ? [
                [
                    'start' => new \MongoDB\BSON\UTCDateTime($date),
                    'end' => new \MongoDB\BSON\UTCDateTime($endDate),
                    'type' => 'period',
                ],
            ] : [
                [
                    'date' => new \MongoDB\BSON\UTCDateTime($date),
                    'type' => 'simple',
                ],
            ];
        } else {
            $value = $defaultValue;
        }

        return $this->createFieldSettingValueBlank(FieldTypeEnum::CALENDAR, $value);
    }

    /**
     * @param FieldSetting $fieldSetting
     * @param null|mixed   $defaultValue
     *
     * @return array
     */
    protected function generateSourceIdValue(FieldSetting $fieldSetting, $defaultValue = null): array
    {
        /** @var FieldSetting\Options\SourceIdOptions $options */
        $options = $fieldSetting->getOptions();
        if ($options->isAutoIncrement()) {
            $value = [(string) $this->idGenerator->generate($fieldSetting)];
        } else {
            $value = $defaultValue ?? [$this->faker->randomNumber(7)];
        }

        return $this->createFieldSettingValueBlank(FieldTypeEnum::SOURCE_ID, $value);
    }

    /**
     * @param string $type
     *
     * @return string
     */
    protected function getMethodNameForType(string $type): string
    {
        return "generate${type}Value";
    }

    /**
     * @throws Exception
     *
     * @return \MongoDB\BSON\Binary
     */
    protected function generateUuid(): \MongoDB\BSON\Binary
    {
        return new \MongoDB\BSON\Binary(Uuid::uuid4()->getBytes(), \MongoDB\BSON\Binary::TYPE_UUID);
    }

    /**
     * @param string $type
     * @param mixed  $value
     *
     * @return array
     *
     * @throws Exception
     */
    protected function createFieldSettingValueBlank(string $type, $value): array
    {
        return [
            'uuid' => $this->generateUuid(),
            'type' => $type,
            'value' => $value,
        ];
    }
}
