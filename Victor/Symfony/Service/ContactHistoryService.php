<?php

namespace App\Service;

use App\Document\Contact;
use App\Document\ContactHistory\Events\AbstractEvent;
use App\Document\ContactHistory\Events\ContactCreated;
use App\Document\ContactHistory\Events\FieldAdded;
use App\Document\ContactHistory\Events\FieldRemoved;
use App\Document\ContactHistory\Events\FieldUpdated;
use App\Document\ContactHistory\Events\Interfaces\FieldSettingEventInterface;
use App\Document\ContactHistory\Events\Interfaces\LabelSettingEventInterface;
use App\Document\ContactHistory\Events\KeywordsAdded;
use App\Document\ContactHistory\Events\KeywordsRemoved;
use App\Document\ContactHistory\Events\LabelAdded;
use App\Document\ContactHistory\Events\LabelRemoved;
use App\Document\ContactHistory\FieldDiff\AbstractFieldDiff;
use App\Document\Field\AbstractField;
use App\Document\FieldSetting;
use App\Document\LabelSetting;
use App\Document\User;
use App\Model\ContextInterface;
use App\Repository\ContactHistoryRecordRepository;
use App\Repository\FieldSettingRepository;
use App\Repository\LabelSettingRepository;
use App\Service\ContactHistory\ContactFieldValueComparator;
use App\Utils\ArrayUtils;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ODM\MongoDB\Mapping\Annotations as ODM;
use Doctrine\ODM\MongoDB\UnitOfWork;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

/**
 * Class ContactHistoryService.
 *
 * @method ContactHistoryRecordRepository getRepository()
 * @method AbstractEvent                  get(string $id, ContextInterface $context = null)
 */
class ContactHistoryService extends AbstractDocumentService
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var LabelSettingRepository
     */
    private $labelSettingRepository;

    /**
     * @var FieldSettingRepository
     */
    private $fieldSettingRepository;

    /**
     * @var ContactFieldValueComparator
     */
    private $contactFieldValueComparator;

    /**
     * @var bool
     */
    private $changeSetSaveMode = false;

    /**
     * @var UnitOfWork
     */
    private $unitOfWork;

    /**
     * @var \Doctrine\ODM\MongoDB\Mapping\ClassMetadata
     */
    private $classMetadata;

    /**
     * @var null|string
     */
    private static $originalUser = null;

    /**
     * ContactHistoryService constructor.
     *
     * @param ContactHistoryRecordRepository $contactHistoryRecordRepository
     * @param UserService                    $userService
     * @param LabelSettingRepository         $labelSettingRepository
     * @param FieldSettingRepository         $fieldSettingRepository
     * @param ContactFieldValueComparator    $contactFieldValueComparator
     */
    public function __construct(
        ContactHistoryRecordRepository $contactHistoryRecordRepository,
        UserService $userService,
        LabelSettingRepository $labelSettingRepository,
        FieldSettingRepository $fieldSettingRepository,
        ContactFieldValueComparator $contactFieldValueComparator
    ) {
        parent::__construct($contactHistoryRecordRepository);

        $this->userService = $userService;
        $this->labelSettingRepository = $labelSettingRepository;
        $this->fieldSettingRepository = $fieldSettingRepository;
        $this->contactFieldValueComparator = $contactFieldValueComparator;

        $this->classMetadata = $this->getDocumentManager()->getClassMetadata(\App\Document\ContactHistory\Events\AbstractEvent::class);
    }

    /**
     * Enable special save mode which allows you to create
     * objects inside onFlush event handlers (flush through the unitOfWork).
     *
     * @param UnitOfWork $unitOfWork
     */
    public function enableChangeSetSaveMode(UnitOfWork $unitOfWork): void
    {
        $this->changeSetSaveMode = true;
        $this->unitOfWork = $unitOfWork;
    }

    /**
     * Disable flush through the unitOfWork.
     */
    public function disableChangeSetSaveMode(): void
    {
        $this->changeSetSaveMode = false;
    }

    /**
     * @param Contact   $contact
     * @param null|User $user
     *
     * @throws \Exception
     */
    public function emitContactCreatedEvent(Contact $contact, ?User $user = null): void
    {
        $eventUser = $user ?: $this->getCurrentUser();

        $eventRecord = new ContactCreated();

        $this->fillBaseEventData($eventRecord, $contact, $eventUser);

        $this->save($eventRecord);

        foreach ($contact->getLabels() as $label) {
            $this->emitLabelAddedEvent($contact, $label->getLabelSetting(), $label->getFields()->toArray(), $eventUser);
        }

        $this->emitKeywordsAddedEvent($contact, $contact->getKeywords(), $eventUser);
    }

    /**
     * @param Contact   $contact
     * @param string    $labelSetting
     * @param array     $fields
     * @param null|User $user
     *
     * @throws \Exception
     */
    public function emitLabelAddedEvent(Contact $contact, string $labelSetting, array $fields, ?User $user): void
    {
        $eventUser = $user ?: $this->getCurrentUser();

        $eventRecord = new LabelAdded();

        $this->fillBaseEventData($eventRecord, $contact, $eventUser);

        $eventRecord->setLabelSetting($labelSetting);
        $this->save($eventRecord);

        foreach ($fields as $field) {
            $this->emitFieldAddedEvent($contact, $labelSetting, $field, $eventUser);
        }
    }

    /**
     * @param Contact   $contact
     * @param string    $labelSetting
     * @param array     $fields
     * @param null|User $user
     *
     * @throws \Exception
     */
    public function emitLabelRemovedEvent(Contact $contact, string $labelSetting, array $fields, ?User $user): void
    {
        $eventUser = $user ?: $this->getCurrentUser();

        $eventRecord = new LabelRemoved();

        $this->fillBaseEventData($eventRecord, $contact, $eventUser);

        $eventRecord->setLabelSetting($labelSetting);
        $this->save($eventRecord);

        foreach ($fields as $field) {
            $this->emitFieldRemovedEvent($contact, $labelSetting, $field, $eventUser);
        }
    }

    /**
     * @param Contact   $contact
     * @param array     $keywords
     * @param null|User $user
     */
    public function emitKeywordsAddedEvent(Contact $contact, array $keywords, ?User $user): void
    {
        if (empty($keywords)) {
            return;
        }

        $eventUser = $user ?: $this->getCurrentUser();

        $eventRecord = new KeywordsAdded();

        $this->fillBaseEventData($eventRecord, $contact, $eventUser);

        $eventRecord->setKeyword($keywords);

        $this->save($eventRecord);
    }

    /**
     * @param Contact   $contact
     * @param array     $keywords
     * @param null|User $user
     */
    public function emitKeywordsRemovedEvent(Contact $contact, array $keywords, ?User $user): void
    {
        if (empty($keywords)) {
            return;
        }

        $eventUser = $user ?: $this->getCurrentUser();

        $eventRecord = new KeywordsRemoved();

        $this->fillBaseEventData($eventRecord, $contact, $eventUser);

        $eventRecord->setKeyword($keywords);

        $this->save($eventRecord);
    }

    /**
     * @param Contact       $contact
     * @param string        $labelSetting
     * @param AbstractField $field
     * @param null|User     $user
     *
     * @throws \Exception
     */
    public function emitFieldAddedEvent(Contact $contact, string $labelSetting, AbstractField $field, ?User $user): void
    {
        $eventUser = $user ?: $this->getCurrentUser();

        $eventRecord = new FieldAdded();

        $this->fillBaseEventData($eventRecord, $contact, $eventUser);

        $eventRecord->setLabelSetting($labelSetting);
        $eventRecord->setFieldSetting($field->getFieldSetting());
        $eventRecord->setUuid($field->getUuid());

        $fieldDiffInstance = $this->getFieldDiffInstance($field->getType());

        $fieldDiffInstance->setNewValue($field->getValue());
        $fieldDiffInstance->setOldValue(null);

        $eventRecord->setFieldDiff($fieldDiffInstance);

        $this->save($eventRecord);
    }

    /**
     * @param Contact       $contact
     * @param string        $labelSetting
     * @param AbstractField $field
     * @param null|User     $user
     *
     * @throws \Exception
     */
    public function emitFieldRemovedEvent(Contact $contact, string $labelSetting, AbstractField $field, ?User $user): void
    {
        $eventUser = $user ?: $this->getCurrentUser();

        $eventRecord = new FieldRemoved();

        $this->fillBaseEventData($eventRecord, $contact, $eventUser);

        $eventRecord->setLabelSetting($labelSetting);
        $eventRecord->setFieldSetting($field->getFieldSetting());
        $eventRecord->setUuid($field->getUuid());

        $fieldDiffInstance = $this->getFieldDiffInstance($field->getType());

        $fieldDiffInstance->setOldValue($field->getValue());
        $fieldDiffInstance->setNewValue(null);

        $eventRecord->setFieldDiff($fieldDiffInstance);

        $this->save($eventRecord);
    }

    /**
     * @param Contact       $contact
     * @param string        $labelSetting
     * @param AbstractField $fieldOld
     * @param AbstractField $fieldNew
     * @param null|User     $user
     *
     * @throws \Exception
     */
    public function emitFieldUpdatedEvent(Contact $contact, string $labelSetting, AbstractField $fieldOld, AbstractField $fieldNew, ?User $user): void
    {
        $eventUser = $user ?: $this->getCurrentUser();

        $eventRecord = new FieldUpdated();

        $this->fillBaseEventData($eventRecord, $contact, $eventUser);

        $eventRecord->setLabelSetting($labelSetting);
        $eventRecord->setFieldSetting($fieldOld->getFieldSetting());
        $eventRecord->setUuid($fieldOld->getUuid());

        $fieldDiffInstance = $this->getFieldDiffInstance($fieldOld->getType());

        $fieldDiffInstance->setOldValue($fieldOld->getValue());
        $fieldDiffInstance->setNewValue($fieldNew->getValue());

        $eventRecord->setFieldDiff($fieldDiffInstance);

        $this->save($eventRecord);
    }

    /**
     * @param AbstractEvent[] $events
     *
     * @return LabelSetting[]
     */
    public function getLabelSettingsForEvents(array $events): array
    {
        $eventsWithLabelSetting = $this->filterEventsByInterface($events, LabelSettingEventInterface::class);

        $labelSettingIds = ArrayUtils::pluck($eventsWithLabelSetting, 'labelSetting');

        $labelSettingIds = array_filter(array_unique($labelSettingIds));

        return $this->labelSettingRepository->getManyByIds($labelSettingIds);
    }

    /**
     * @param AbstractEvent[] $events
     *
     * @return FieldSetting[]
     */
    public function getFieldSettingsForEvents(array $events): array
    {
        $eventsWithFieldSetting = $this->filterEventsByInterface($events, FieldSettingEventInterface::class);

        $fieldSettingIds = ArrayUtils::pluck($eventsWithFieldSetting, 'fieldSetting');

        $fieldSettingIds = array_filter(array_unique($fieldSettingIds));

        return $this->fieldSettingRepository->getManyByIds($fieldSettingIds);
    }

    /**
     * Save a document. Function flushes immediately.
     *
     * @param mixed $object object to save
     */
    public function save($object): void
    {
        $this->documentManager->persist($object);

        if ($this->changeSetSaveMode) {
            $this->unitOfWork->computeChangeSet($this->classMetadata, $object);
        } else {
            $this->documentManager->flush();
        }
    }

    /**
     * @var string
     */
    public static function setOriginalUser(string $originalUser): void
    {
        static::$originalUser = $originalUser;
    }

    /**
     * @return null|string
     */
    public static function getOriginalUser(): ?string
    {
        return static::$originalUser;
    }

    /**
     * @param Contact $contactOld
     * @param Contact $contact
     */
    public function emitContactUpdatedEvent(Contact $contactOld, Contact $contact): void
    {
        $this->proceedContactKeywordsUpdate($contactOld, $contact);

        $this->proceedContactLabelsUpdate($contactOld, $contact);
    }

    /**
     * @param Contact $contactOld
     * @param Contact $contact
     */
    private function proceedContactKeywordsUpdate(Contact $contactOld, Contact $contact): void
    {
        $keywordsOld = $contactOld->getKeywords();
        $keywordsNew = $contact->getKeywords();

        $addedKeywords = array_diff($keywordsNew, $keywordsOld);
        $this->emitKeywordsAddedEvent($contact, $addedKeywords, $this->getCurrentUser());

        $removedKeywords = array_diff($keywordsOld, $keywordsNew);
        $this->emitKeywordsRemovedEvent($contact, $removedKeywords, $this->getCurrentUser());
    }

    /**
     * @param Contact $contactOld
     * @param Contact $contact
     */
    private function proceedContactLabelsUpdate(Contact $contactOld, Contact $contact): void
    {
        $labelsCollectionOld = ArrayUtils::keyBy($contactOld->getLabels(), 'getLabelSetting');
        $labelsCollectionNew = ArrayUtils::keyBy($contact->getLabels(), 'getLabelSetting');

        [
            $removedLabels,
            $addedLabels,
            $updatedLabels
        ] = $this->spreadEntitySetsByEvent($labelsCollectionOld, $labelsCollectionNew);

        /** @var \App\Document\Label $contactLabel */
        foreach ($removedLabels as $labelSetting => $contactLabel) {
            $this->emitLabelRemovedEvent($contact, $labelSetting, $this->expandСollection($contactLabel->getFields()), $this->getCurrentUser());
        }

        /** @var \App\Document\Label $contactLabel */
        foreach ($addedLabels as $labelSetting => $contactLabel) {
            $this->emitLabelAddedEvent($contact, $labelSetting, $this->expandСollection($contactLabel->getFields()), $this->getCurrentUser());
        }

        foreach ($updatedLabels as $labelSetting => $contactLabel) {
            $fieldsOld = ArrayUtils::keyBy($labelsCollectionOld[$labelSetting]->getFields(), 'getUuid');
            $fieldsNew = ArrayUtils::keyBy($contactLabel->getFields(), 'getUuid');

            [
                $removedFields,
                $addedFields,
                $updatedFields
            ] = $this->spreadEntitySetsByEvent($fieldsOld, $fieldsNew);

            foreach ($removedFields as $fieldUuid => $contactField) {
                $this->emitFieldRemovedEvent($contact, $labelSetting, $contactField, $this->getCurrentUser());
            }

            foreach ($addedFields as $fieldUuid => $contactField) {
                $this->emitFieldAddedEvent($contact, $labelSetting, $contactField, $this->getCurrentUser());
            }

            foreach ($updatedFields as $fieldUuid => $contactField) {
                $fieldOld = $fieldsOld[$fieldUuid];

                $fieldValueOld = $this->expandСollection($fieldOld->getValue());
                $fieldValueNew = $this->expandСollection($contactField->getValue());

                if (!$this->contactFieldValueComparator->isEqual($fieldOld->getType(), $fieldValueOld, $fieldValueNew)) {
                    $this->emitFieldUpdatedEvent($contact, $labelSetting, $fieldOld, $contactField, $this->getCurrentUser());
                }
            }
        }
    }

    /**
     * @return array
     */
    private function spreadEntitySetsByEvent(array $entitiesOld, array $entitiesNew): array
    {
        $keysOld = array_keys($entitiesOld);
        $keysNew = array_keys($entitiesNew);

        $removedKeys = array_diff($keysOld, $keysNew);
        $addedKeys = array_diff($keysNew, $keysOld);
        $updatedKeys = array_intersect($keysOld, $keysNew);

        return [
            ArrayUtils::only($entitiesOld, $removedKeys),
            ArrayUtils::only($entitiesNew, $addedKeys),
            ArrayUtils::only($entitiesNew, $updatedKeys),
        ];
    }

    /**
     * @param mixed $collection
     *
     * @return mixed
     */
    private function expandСollection($collection)
    {
        if (
            $collection instanceof \Doctrine\ODM\MongoDB\PersistentCollection
            || $collection instanceof ArrayCollection
        ) {
            return $collection->toArray();
        }

        return $collection;
    }

    /**
     * @param AbstractEvent[] $events
     * @param string          $interface
     *
     * @return AbstractEvent[]
     */
    private function filterEventsByInterface(array $events, string $interface): array
    {
        return array_filter($events, static function (AbstractEvent $eventRecord) use ($interface) {
            return $eventRecord instanceof $interface;
        });
    }

    /**
     * @return null|User
     */
    private function getCurrentUser(): ?User
    {
        try {
            return $this->userService->getCurrentUser();
        } catch (UnauthorizedHttpException $e) {
            return null;
        }
    }

    /**
     * @param AbstractEvent $historyRecord
     * @param Contact       $contact
     * @param null|User     $user
     *
     * @return AbstractEvent
     */
    private function fillBaseEventData(AbstractEvent $historyRecord, Contact $contact, ?User $user): AbstractEvent
    {
        $historyRecord->setContext($contact->getContext());
        $historyRecord->setContact($contact->getId());
        $historyRecord->setUser($user);

        if (!empty(static::$originalUser)) {
            $historyRecord->setOriginalUser(static::$originalUser);
        }

        return $historyRecord;
    }

    /**
     * @param string $fieldType
     *
     * @return AbstractFieldDiff
     *
     * @throws \Exception
     */
    private function getFieldDiffInstance(string $fieldType): AbstractFieldDiff
    {
        $annotationReader = new AnnotationReader();

        $reflectionClass = new \ReflectionClass(AbstractFieldDiff::class);

        /** @var null| ODM\DiscriminatorMap $discriminatorMapAnnotation */
        $discriminatorMapAnnotation = $annotationReader->getClassAnnotation($reflectionClass, ODM\DiscriminatorMap::class);
        /** @var null| ODM\DefaultDiscriminatorValue $defaultDiscriminatorValueAnnotation */
        $defaultDiscriminatorValueAnnotation = $annotationReader->getClassAnnotation($reflectionClass, ODM\DefaultDiscriminatorValue::class);

        if (null === $discriminatorMapAnnotation || null === $defaultDiscriminatorValueAnnotation) {
            throw new \Exception('Discriminator annotations is missed');
        }

        /** @var array $discriminatorMap */
        $discriminatorMap = $discriminatorMapAnnotation->value;

        /** @var string $defaultDiscriminatorValue */
        $defaultDiscriminatorValue = $defaultDiscriminatorValueAnnotation->value;

        $fieldDiffInstanceClass = $discriminatorMap[$fieldType] ?? $discriminatorMap[$defaultDiscriminatorValue];

        $instance = (new \ReflectionClass($fieldDiffInstanceClass))->newInstance();

        if (!($instance instanceof AbstractFieldDiff)) {
            throw new \Exception('Unexpected FieldDiff instance class');
        }

        return $instance;
    }
}
