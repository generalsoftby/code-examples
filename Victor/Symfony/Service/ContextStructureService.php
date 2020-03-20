<?php

namespace App\Service;

use App\Document\Contact;
use App\Document\Context;
use App\Document\FieldSetting\FieldSettingRef;
use App\Document\LabelSetting;
use App\Enum\ContextType;
use App\Factory\DummyDataFactory;
use App\Utils\ContextStructureService\ContextStructure;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\DocumentManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Yaml\Yaml;

class ContextStructureService
{
    /** @var string File with context configuration */
    public const BASE_CONTEXT_STRUCTURE_FOLDER = '/config/context_structure';

    /** @var string Default type for context structure file */
    private const DEFAULT_CONTEXT_STRUCTURE_TYPE = ContextType::DEFAULT;

    /** @var int The level where you switch to inline YAML */
    private const YAML_DEPTH_OF_EXPANSION = 10;

    /** @var FieldSettingService */
    private $fieldSettingService;

    /** @var ContactService */
    private $contactService;

    /**
     * @var LabelSettingService
     */
    private $labelSettingService;

    /**
     * @var NormalizerInterface
     */
    private $normalizer;

    /**
     * @var string
     */
    private $kernelProjectDir;

    /**
     * @var ContextStructureValidateService
     */
    private $contextStructureValidateService;

    /**
     * @var DummyDataFactory
     */
    private $dummyDataFactory;

    /**
     * @var LocaleService
     */
    private $localeService;

    /**
     * ContextStructureService constructor.
     *
     * @param FieldSettingService             $fieldSettingService
     * @param LabelSettingService             $labelSettingService
     * @param ContextStructureValidateService $contextStructureValidateService
     * @param ContactService                  $contactService
     * @param NormalizerInterface             $normalizer
     * @param DummyDataFactory                $dummyDataFactory
     * @param LocaleService                   $localeService
     * @param string                          $kernelProjectDir
     */
    public function __construct(
        FieldSettingService $fieldSettingService,
        LabelSettingService $labelSettingService,
        ContextStructureValidateService $contextStructureValidateService,
        ContactService $contactService,
        NormalizerInterface $normalizer,
        DummyDataFactory $dummyDataFactory,
        LocaleService $localeService,
        string $kernelProjectDir
    ) {
        $this->fieldSettingService = $fieldSettingService;
        $this->labelSettingService = $labelSettingService;
        $this->contactService = $contactService;
        $this->contextStructureValidateService = $contextStructureValidateService;
        $this->normalizer = $normalizer;
        $this->kernelProjectDir = $kernelProjectDir;
        $this->dummyDataFactory = $dummyDataFactory;
        $this->localeService = $localeService;
    }

    /**
     * @param Context $context
     *
     * @return ContextStructure
     *
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     * @throws \Exception
     */
    public function createStructureFromContext(Context $context): ContextStructure
    {
        $contextStructure = new ContextStructure($this->normalizer);

        $fieldSettings = $this->fieldSettingService->getRepository()->findByContext($context->getId());
        $labelSettings = $this->labelSettingService->getRepository()->findByContext($context->getId());

        $contextStructure
            ->setLanguage($context->getLanguage())
            ->addFieldSettingCollection($fieldSettings)
            ->addLabelSettingCollection($labelSettings)
            ->setFieldSettingReferences($context->getFieldSettingsReferenceMap())
        ;

        return $contextStructure;
    }

    /**
     * @param string $filePath
     *
     * @return ContextStructure
     *
     * @throws \Exception
     */
    public function createStructureFromFile(string $filePath): ContextStructure
    {
        $contextStructure = new ContextStructure($this->normalizer);

        $extension = pathinfo($filePath, PATHINFO_EXTENSION);

        switch ($extension) {
            case 'yaml':
            case 'yml':
                $fileContent = Yaml::parseFile($filePath);

                $contextStructure->setStructure($fileContent);

                break;
            default:
                throw new \Exception("Unsupported file extension '${extension}'");
        }

        return $contextStructure;
    }

    /**
     * @param ContextStructure $contextStructure
     * @param string           $filePath
     */
    public function saveContextStructureToFile(ContextStructure $contextStructure, string $filePath)
    {
        $filesystem = new Filesystem();

        $filesystem->dumpFile($filePath, Yaml::dump($contextStructure->getStructure(), self::YAML_DEPTH_OF_EXPANSION));
    }

    /**
     * @param ContextStructure $contextStructure
     * @param Context          $context
     * @param bool             $validateStructure
     *
     * @return Context
     *
     * @throws \Exception
     */
    public function applyStructureToContext(ContextStructure $contextStructure, Context $context, $validateStructure = true): Context
    {
        if ($validateStructure) {
            // validate context structure
            $validationErrors = $this->contextStructureValidateService->validateStructure($contextStructure);

            if (count($validationErrors)) {
                throw new \Exception(implode(";\n", $validationErrors));
            }

            $this->contextStructureValidateService->validateStructureContextCompatibility($context, $contextStructure);
        }

        // create field settings
        $fieldSettingsMap = $this->createFieldSettingsFromStructure($contextStructure, $context);

        // create label settings
        $labelSettingsMap = $this->createLabelSettingsFromStructure($contextStructure, $context, $fieldSettingsMap);

        // save fieldSettingsReferenceMap
        $this->createFieldSettingReferenceFromStructure($contextStructure, $context, $fieldSettingsMap, $labelSettingsMap);

        // create contacts
        $this->createContactsFromStructure($contextStructure, $context, $fieldSettingsMap, $labelSettingsMap);

        // set language
        $this->setContextLanguage($context, $contextStructure->getLanguage());

        return $context;
    }

    /**
     * Get config file for context structure by context type.
     *
     * @param string $type
     *
     * @return string
     *
     * @throws \Exception
     */
    public function getBaseContextStructureFileForType(string $type): string
    {
        $filesystem = new Filesystem();

        $fileName = strtolower($type);

        $filePath = $this->kernelProjectDir . self::BASE_CONTEXT_STRUCTURE_FOLDER . "/${fileName}.yaml";

        if ($filesystem->exists($filePath)) {
            return $filePath;
        }

        if (self::DEFAULT_CONTEXT_STRUCTURE_TYPE !== $type) {
            return $this->getBaseContextStructureFileForType(self::DEFAULT_CONTEXT_STRUCTURE_TYPE);
        }

        throw new \Exception("Default context structure '${$filePath}' file not found");
    }

    /**
     * Apply base context structure to context.
     *
     * @param Context $context
     *
     * @return Context
     *
     * @throws \Exception
     */
    public function fillBaseContextStructure(Context $context): Context
    {
        if (\App\Enum\ContextType::EMPTY()->getValue() === $context->getType()) {
            return $context;
        }

        $contextStructureFile = $this->getBaseContextStructureFileForType($context->getType());

        $contextStructure = $this->createStructureFromFile($contextStructureFile);

        $this->applyStructureToContext($contextStructure, $context);

        return $context;
    }

    /**
     * @param ContextStructure $contextStructure
     * @param Context          $context
     *
     * @return array
     */
    private function createFieldSettingsFromStructure(ContextStructure $contextStructure, Context $context): array
    {
        $structure = $contextStructure->getStructure();
        $dm = $this->getDocumentManager();

        $externalFieldSettings = $structure['field_settings'] ?? [];

        $fieldSettingsMap = [];

        foreach ($externalFieldSettings as $fieldSettingIdentifier => $fieldSettingData) {
            $fieldSetting = $this->fieldSettingService->create($fieldSettingData, $context);
            $fieldSettingsMap[$fieldSettingIdentifier] = $fieldSetting;
            $dm->persist($fieldSetting);
        }
        $dm->flush();

        return $fieldSettingsMap;
    }

    /**
     * @param ContextStructure $contextStructure
     * @param Context          $context
     * @param array            $fieldSettingsMap
     *
     * @return array
     */
    private function createLabelSettingsFromStructure(ContextStructure $contextStructure, Context $context, array $fieldSettingsMap): array
    {
        $structure = $contextStructure->getStructure();

        $externalLabelSettings = $structure['label_settings'] ?? [];

        $labelSettingsMap = [];

        $dm = $this->getDocumentManager();
        foreach ($externalLabelSettings as $labelSettingIdentifier => $labelSettingData) {
            $labelSetting = new LabelSetting();

            // replace fieldSettings data to FieldSettingRef objects
            $labelSettingData['fieldSettings'] = array_map(function ($fieldSettingIdentifier) use ($fieldSettingsMap, $labelSettingData) {
                $labelFieldSettingData = $labelSettingData['fieldSettings'][$fieldSettingIdentifier];

                return new FieldSettingRef($fieldSettingsMap[$fieldSettingIdentifier], $labelFieldSettingData['deletable']);
            }, array_keys($labelSettingData['fieldSettings']));

            if (isset($labelSettingData['requiredGroups'])) {
                $groups = array_map(function ($group) use ($fieldSettingsMap) {
                    $requiredGroup = array_map(function ($fieldSettingIdentifier) use ($fieldSettingsMap) {
                        return $fieldSettingsMap[$fieldSettingIdentifier]->getId();
                    }, $group['group']);

                    return new LabelSetting\RequiredGroup($requiredGroup);
                }, $labelSettingData['requiredGroups']);

                $labelSettingData['requiredGroups'] = new ArrayCollection($groups);
            }

            $labelSettingData['context'] = $context;

            $this->labelSettingService->fillFromArray($labelSetting, $labelSettingData);
            $dm->persist($labelSetting);

            $labelSettingsMap[$labelSettingIdentifier] = $labelSetting;
        }
        $dm->flush();

        return $labelSettingsMap;
    }

    /**
     * @param ContextStructure $contextStructure
     * @param Context          $context
     * @param array            $fieldSettingsMap
     * @param array            $labelSettingsMap
     *
     * @return Context
     */
    private function createFieldSettingReferenceFromStructure(ContextStructure $contextStructure, Context $context, array $fieldSettingsMap, array $labelSettingsMap): Context
    {
        $externalFieldSettingsReferenceMap = $contextStructure->getStructureReferenceMap();

        foreach ($externalFieldSettingsReferenceMap as $key => $fieldSettingData) {
            $realFieldSettingId = $fieldSettingsMap[$fieldSettingData['fieldSetting']]->getId();
            $realLabelSettingId = $labelSettingsMap[$fieldSettingData['labelSetting']]->getId();

            $context->addFieldSettingReference($key, $realLabelSettingId, $realFieldSettingId);
        }

        return $context;
    }

    /**
     * @param ContextStructure $contextStructure
     * @param Context          $context
     * @param array            $fieldSettingsMap
     * @param array            $labelSettingsMap
     *
     * @return Context
     *
     * @throws \Doctrine\ODM\MongoDB\MongoDBException
     */
    private function createContactsFromStructure(ContextStructure $contextStructure, Context $context, array $fieldSettingsMap, array $labelSettingsMap): Context
    {
        $structure = $contextStructure->getStructure();

        $externalContacts = $structure['contacts'] ?? [];
        $externalContacts = $this->dummyDataFactory->create($externalContacts);
        $dm = $this->getDocumentManager();

        $contactsMap = [];
        $contactRelations = [];

        foreach ($externalContacts as $contactIdentifier => $contactData) {
            foreach ($contactData['labels'] as $labelIdentifier => &$labelData) {
                $labelData['labelSetting'] = [
                    'id' => $labelSettingsMap[$labelIdentifier]->getId(),
                ];

                foreach ($labelData['fields'] as &$fieldData) {
                    $fieldSettingIdentifier = $fieldData['fieldSetting'];
                    $fieldData['fieldSetting'] = $fieldSettingsMap[$fieldSettingIdentifier]->getId();
                    $fieldData['type'] = $structure['field_settings'][$fieldSettingIdentifier]['options']['type'];
                }
            }

            if (is_array($contactData['relations']) && count($contactData['relations'])) {
                $contactRelations[$contactIdentifier] = $contactData['relations'];
            }

            // remove relations,
            $contactData['relations'] = [];

            $contact = $this->contactService->create($contactData, $context);
            $dm->persist($contact);

            $contactsMap[$contactIdentifier] = $contact;
        }

        // save contact relations because we cannot save them until all contacts are saved
        foreach ($contactRelations as $contactIdentifier => $relationsData) {
            /** @var Contact $contact */
            $contact = $contactsMap[$contactIdentifier];
            foreach ($relationsData as $relatedContactIdentifier => $contactData) {
                /** @var Contact $relatedContact */
                $relatedContact = $contactsMap[$relatedContactIdentifier];
                $contact->addRelation($relatedContact->getId(), $contactData['roles']);
            }
        }
        $dm->flush();

        return $context;
    }

    /**
     * @param Context $context
     * @param string  $language
     *
     * @return Context
     */
    private function setContextLanguage(Context $context, string $language): Context
    {
        if (!in_array($language, $this->localeService->getAvailableLocales(), true)) {
            throw new \InvalidArgumentException(sprintf('Language "%s" is not available', $language));
        }
        $context->setLanguage($language);

        return $context;
    }

    private function getDocumentManager(): DocumentManager
    {
        return $this->labelSettingService->getDocumentManager();
    }
}
