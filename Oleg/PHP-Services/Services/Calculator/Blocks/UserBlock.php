<?php

namespace App\Services\Calculators\Blocks;

use App\Model\Calculator\BookKeepers\BookKeeper;
use App\Model\Calculator\CalculationVariables\CalculationVariableEntity;
use App\Model\Calculator\CalculationVariables\CalculationVariableEntityCollection;
use App\Model\Calculator\CalculationVariables\StatesOfVisibility;
use App\Model\Calculator\Calculator;
use App\Model\Calculator\CalculatorAssembly;
use App\Model\Calculator\OptionCollection;
use App\Repositories\CalculatorOptionRepository;
use App\Services\Calculators\AttributeKeeper;
use App\Services\Calculators\CalculationVariable\ValueCollection;
use App\Services\Calculators\CalculationVariableKeeper;
use App\Services\Calculators\CalculationVariableService;
use App\Services\Calculators\Error;
use App\Services\Calculators\Errors;
use App\Services\Calculators\ErrorSources\LinkOfOption;
use App\Services\Calculators\OptionKeeper;

/**
 * Keeps user data of a block and validates their.
 */
class UserBlock
{
    /**
     * An instance of Calculator.
     *
     * @var Calculator
     */
    protected $calculator;

    /**
     * An instance of Block.
     *
     * @var Block
     */
    protected $block;

    /**
     * An instance of CalculatorOptionRepository.
     *
     * @var CalculatorOptionRepository
     */
    protected $optionRepository;

    /**
     * An instance of Errors.
     *
     * @var Errors
     */
    protected $errors;

    /**
     * Data of user attributes.
     *
     * @var array
     */
    protected $attributes;

    /**
     * An instance of AttributeKeeper.
     *
     * @var AttributeKeeper
     */
    protected $attributeKeeper;

    /**
     * User data of options.
     *
     * @var array
     */
    protected $userDataOfOptions;

    /**
     * An instance of OptionCollection.
     *
     * @var OptionCollection|null
     */
    protected $optionCollection;

    /**
     * An instance of OptionKeeper.
     *
     * @var OptionKeeper
     */
    protected $optionKeeper;

    /**
     * User data of calculation variables.
     *
     * @var array
     */
    protected $userDataOfCalculationVariables;

    /**
     * An instance of ValueCollection.
     *
     * @var ValueCollection
     */
    protected $valueCollection;

    /**
     * An instance of CalculationVariableKeeper.
     *
     * @var CalculationVariableKeeper
     */
    protected $calculationVariableKeeper;

    /**
     * An instance of CalculationVariableService.
     *
     * @var CalculationVariableService
     */
    protected $calculationVariableService;

    /**
     * A state of block visibility.
     *
     * @var bool
     */
    protected $visibilityOfBlock;

    /**
     * Initializes an instance of the UserBlock.
     *
     * @param CalculatorOptionRepository $optionRepository
     * @param CalculationVariableService $calculationVariableService
     * @param Calculator $calculator
     * @param BookKeeper $bookKeeper
     * @param Block $block
     */
    public function __construct(
        CalculatorOptionRepository $optionRepository,
        CalculationVariableService $calculationVariableService,
        Calculator $calculator,
        BookKeeper $bookKeeper,
        Block $block
    ) {
        $this->optionRepository = $optionRepository;
        $this->calculationVariableService = $calculationVariableService;
        $this->calculationVariableService->setCalculatorAndBookKeeper($calculator, $bookKeeper);

        $this->calculator = $calculator;

        $this->block = $block;
        // Default visibility of the block
        $this->visibilityOfBlock = $this->block->getVisibility();
        $this->errors = new Errors;
    }

    /**
     * Fills the instance from the given array with user data of the block.
     *
     * @param  array $userDataOfBlock
     * @return void
     */
    public function fillFromArray(array $userDataOfBlock): void
    {
        if ($this->block->mustHasAssembly()) {
            $this->setUserDataOfAttributes($userDataOfBlock['attributes'] ?? []);
            $this->setUserDataOfOptions($userDataOfBlock['options'] ?? []);
        }

        if ($this->block->mustHasCalculationVariables()) {
            $this->setUserDataOfCalculationVariables($userDataOfBlock['calculationVariables'] ?? []);
        }
    }

    /**
     * Returns an instance of Calculator.
     *
     * @return Calculator
     */
    public function getCalculator(): Calculator
    {
        return $this->calculator;
    }

    /**
     * Returns a block of the user block.
     *
     * @return Block
     */
    public function getBlock(): Block
    {
        return $this->block;
    }

    /**
     * Returns a name of the user block.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->block->getName();
    }

    /**
     * Sets user data of attributes.
     *
     * @param  array $attributes
     * @return void
     */
    public function setUserDataOfAttributes($attributes = []): void
    {
        $this->attributes = $attributes;
        /** @var array|int[] $attributeIDs */
        $attributeIDs = array_pluck($attributes, 'valueId');
        $this->attributeKeeper = new AttributeKeeper();
        $this->attributeKeeper->setIDsOfAttributeValues($attributeIDs);
        $this->attributeKeeper->setCalculator($this->calculator);
        $this->attributeKeeper->setAttributeGroup($this->block->getNameOfAttributeGroup());
        $this->assembly = $this->attributeKeeper->getAssemblyWithoutUsingExceptions();
    }

    /**
     * Returns user data of attributes.
     *
     * @return array
     */
    public function getUserDataOfAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Returns an instance of AttributeKeeper.
     *
     * @return AttributeKeeper|null
     */
    public function getAttributeKeeper(): ?AttributeKeeper
    {
        return $this->attributeKeeper;
    }

    /**
     * Returns an instance of Assembly.
     *
     * @return CalculatorAssembly|null
     */
    public function getAssembly(): ?CalculatorAssembly
    {
        return isset($this->attributeKeeper)
            ? $this->attributeKeeper->getAssembly()
            : null
        ;
    }

    /**
     * Checks whether the user block use an assembly.
     *
     * @return bool
     */
    public function doesUserBlockUseAssembly(): bool
    {
        return isset($this->attributeKeeper) && $this->isVisible();
    }

    /**
     * Validates the current AttributeKeeper.
     *
     * @return bool
     */
    public function validateAttributeKeeper(): bool
    {
        // Checks attributes and their assembly when they are required
        if (!$this->block->mustHasAssembly()) {
            return true;
        }

        if (!$this->assembly) {
            if (!$this->attributeKeeper->isCalculatorConfigured()) {
                $this->errors->add(trans('calculator_errors.attribute_does_not_have_values'), Error::CONFIGURATION_ERROR);
            } elseif (!$this->attributeKeeper->doAttributeValuesCoincideWithAttributeGroup()) {
                $this->errors->add(trans('calculator_errors.given_attribute_values_ids_do_not_coincide_with_group'), Error::ATTRIBUTE_ERROR);
            } elseif (!$this->attributeKeeper->areEnoughIDsOfAttributes()) {
                $this->errors->add(trans('calculator_errors.not_enough_ids_of_attribute_values'), Error::ATTRIBUTE_ERROR);
            } elseif (!$this->attributeKeeper->areIDsOfAttributesCorrect()) {
                $this->errors->add(trans('calculator_errors.wrong_ids_of_attribute_values'), Error::ATTRIBUTE_ERROR);
            } elseif (!$this->attributeKeeper->isAssemblyActive()) {
                $this->errors->add(trans('calculator_errors.inactive_assembly'), Error::CONFIGURATION_ERROR);
            } elseif (!$this->attributeKeeper->isAssemblyConfigured()) {
                $this->errors->add(trans('calculator_errors.assembly_is_not_configured'), Error::CONFIGURATION_ERROR);
            } else {
                $this->errors->add(trans('calculator_errors.assembly_not_found'), Error::CONFIGURATION_ERROR);
            }
        } elseif (!$this->attributeKeeper->hasInstanceOfPricingRule()) {
            $this->errors->add(trans('calculator_errors.assembly_has_not_instance_of_pricing_rule'), Error::SYSTEM_ERROR);
        }

        return isset($this->assembly) && $this->attributeKeeper->hasInstanceOfPricingRule();
    }

    /**
     * Sets user data of options.
     *
     * @param  array $userDataOfOptions
     * @return void
     */
    public function setUserDataOfOptions(array $userDataOfOptions = []): void
    {
        $this->userDataOfOptions = $userDataOfOptions;
        $this->optionKeeper = new OptionKeeper($userDataOfOptions);
        $this->optionCollection = $this->getOptionCollectionOfAssembly();

        if ($this->optionCollection) {
            $this->optionKeeper->setOptions($this->optionCollection);
        }
    }

    /**
     * Returns user data of options.
     *
     * @return array
     */
    public function getUserDataOfOptions(): array
    {
        return $this->options;
    }

    /**
     * Returns an instance of OptionCollection with options of the current assembly.
     *
     * @return OptionCollection|null
     */
    public function getOptionCollection(): ?OptionCollection
    {
        return $this->optionCollection;
    }

    /**
     * Returns an instance of OptionKeeper.
     *
     * @return OptionKeeper|null
     */
    public function getOptionKeeper(): ?OptionKeeper
    {
        return $this->optionKeeper;
    }

    /**
     * Returns used options.
     * Used options are options of selected values and dependent values
     * of options of the selected values.
     *
     * @return OptionCollection|null
     */
    public function getUsedOptions(): ?OptionCollection
    {
        return $this->optionKeeper ? $this->optionKeeper->getOptionsUsingIds() : null;
    }

    /**
     * Validates the current OptionKeeper.
     *
     * @return bool
     */
    public function validateOptionKeeper(): bool
    {
        // Checks options when they are required
        if (!$this->block->mustHasAssembly()) {
            return true;
        }

        // Checks options of the current assembly
        if (!isset($this->optionCollection)) {
            $this->errors->add(trans('calculator_errors.options_not_got_and_assembly_not_found'), Error::SYSTEM_ERROR);
            return false;
        }

        if (($errorCode = $this->optionKeeper->validate()) !== null) {
            switch ($errorCode) {
                case OptionKeeper::OPTIONS_HAVE_NOT_VALUES:
                    $this->errors->add(trans('calculator_errors.options_have_not_values'), Error::CONFIGURATION_ERROR);
                    // TODO Выдлать список опций, которые не имеют активные значения
                    // $this->optionKeeper->getOptions()->getOptionsWithoutValues()
                    break;
                case OptionKeeper::HIDDEN_OPTIONS_HAVE_NOT_DEFAULT_VALUES:
                    $this->errors->add(
                        trans('calculator_errors.hidden_options_have_not_default_values'),
                        Error::CONFIGURATION_ERROR
                    );
                    // TODO Список скрытых опций, которые не имеют значений по умолчанию.
                    // $this->optionKeeper->getOptions()->getHiddenOptionsWithoutDefaultValues()
                    break;
                case OptionKeeper::REQUIRED_OPTIONS_ARE_NOT_USED:
                    $this->errors->add(trans('calculator_errors.required_options_are_not_used'), Error::OPTION_ERROR);
                    $this->fillErrorsWithUnusedRequiredOptions($this->optionKeeper->getUnusedRequiredOptions());
                    break;
                case OptionKeeper::UNKNOWN_VALUES_ARE_USED:
                    $this->errors->add(trans('calculator_errors.unknown_values_are_used'), Error::CONFIGURATION_ERROR);
                    // NOTE: Use $this->optionKeeper->getIncorrectIds() to get incorrect IDs of values
                    break;
                case OptionKeeper::UNACCEPTABLE_VALUES_ARE_USED:
                    $this->errors->add(
                        trans('calculator_errors.unacceptable_values_are_used'),
                        Error::CONFIGURATION_ERROR
                    );
                    // NOTE: Use $this->optionKeeper->getIncorrectIds() to get incorrect IDs of values
                    break;
            }

            return false;
        }

        return true;
    }

    /**
     * Sets user data of calculation variables.
     *
     * @param  array $userDataOfCalculationVariables
     * @return void
     */
    public function setUserDataOfCalculationVariables(array $userDataOfCalculationVariables = []): void
    {
        $this->userDataOfCalculationVariables = $userDataOfCalculationVariables;
        $this->valueCollection = new ValueCollection;
        $this->valueCollection->fillFromArrayWithTypes($userDataOfCalculationVariables);
        $this->calculationVariableKeeper = new CalculationVariableKeeper();
        $this->calculationVariableKeeper->setValueCollection($this->valueCollection);

        $this->calculationVariableKeeper->setEntityCollection($this->getCalculationVariablesEntitiesOfCalculator());
        $this->calculationVariableKeeper->fillInstancesUsingValues();
    }

    /**
     * Returns user data of calculation variables.
     *
     * @return array
     */
    public function getUserDataCalculationVariables(): array
    {
        return $this->userDataOfCalculationVariables;
    }

    /**
     * Returns an instance of ValueCollection.
     *
     * @return ValueCollection|null
     */
    public function getValueCollection(): ?ValueCollection
    {
        return $this->valueCollection;
    }

    /**
     * Returns an instance of CalculationVariableKeeper.
     *
     * @return CalculationVariableKeeper|null
     */
    public function getCalculationVariableKeeper(): ?CalculationVariableKeeper
    {
        return $this->calculationVariableKeeper;
    }

    /**
     * Returns a calculation variable entity by the given name.
     *
     * @param  string $name
     * @return CalculationVariableEntity|null
     */
    public function getCalculationVariableEntityByName(string $name): ?CalculationVariableEntity
    {
        return $this->calculationVariableKeeper->getInstanceOfVariable($name);
    }

    /**
     * Validates calculation variables.
     *
     * @return bool
     */
    public function validateCalculationVariables(): bool
    {
        if (!$this->block->mustHasCalculationVariables()) {
            return true;
        }

        if ($this->calculationVariableKeeper->hasErrors()) {
            $this->errors->unite($this->calculationVariableKeeper->getErrors());
            return false;
        }

        return true;
    }

    /**
     * Checks whether the block can be changed by other blocks.
     *
     * @return bool
     */
    public function canBeChanged(): bool
    {
        return $this->block->getPermissionsForVisualization()->count() > 0;
    }

    /**
     * Returns the current visibility of the block.
     *
     * @return bool
     */
    public function isVisible(): bool
    {
        return $this->visibilityOfBlock;
    }

    /**
     * Defines visibility of the block by the other user blocks.
     *
     * @param  UserBlocks $userBlocks
     * @return void
     */
    public function defineVisibilityByOtherUserBlocks(UserBlocks $userBlocks): void
    {
        $userBlocks = $userBlocks->getUserBlocksExceptUserBlock($this);

        foreach ($this->getBlock()->getPermissionsForVisualization() as $permission) {
            /** @var StatesOfVisibility|null $element */
            $element = $userBlocks->getElementOfVizualizationOfUserBlockByPermission($permission);
            /** @var bool|null $stateOfBlockVisibility */
            $stateOfBlockVisibility = $element->getStatesOfVisibility()[$this->getName()] ?? null;

            if (isset($stateOfBlockVisibility)) {
                $this->visibilityOfBlock = $stateOfBlockVisibility;
            }
        }
    }

    /**
     * Validates user data of the block.
     *
     * @return bool
     */
    public function validate(): bool
    {
        $this->errors->clean();
        $state = true;

        if (!$this->validateAttributeKeeper()) {
            $state = false;
        } elseif (
            $this->block->mustHasAssembly()
            && $this->validateAttributeKeeper()
            && !$this->validateOptionKeeper()
        ) {
            $state = false;
        }

        if (!$this->validateCalculationVariables()) {
            $state = false;
        }

        return $state;
    }

    /**
     * Returns an instance of Errors.
     *
     * @return Errors
     */
    public function getErrors(): Errors
    {
        return $this->errors;
    }

    /**
     * Returns a OptionCollection of the current assembly.
     *
     * @return OptionCollection|null
     */
    protected function getOptionCollectionOfAssembly(): ?OptionCollection
    {
        return isset($this->assembly)
            ? $this->optionRepository->getOptionCollectionOfAssemblyUsingAssembly($this->assembly)
            : null
        ;
    }

    /**
     * Fills errors with unused required options from OptionCollection.
     *
     * @param  OptionCollection $collection
     * @return void
     */
    protected function fillErrorsWithUnusedRequiredOptions(OptionCollection $collection)
    {
        foreach ($collection as $option) {
            $this->errors->add(
                trans('calculator_errors.required_option_is_not_used', [
                    'name' => $option->title_user . ' [' . $option->id . ']',
                ]),
                Error::OPTION_ERROR,
                new LinkOfOption($option)
             );
        }
    }

    /**
     * Returns an instance of the collection with entities of calculation variables.
     *
     * @return CalculationVariableEntityCollection
     */
    protected function getCalculationVariablesEntitiesOfCalculator(): CalculationVariableEntityCollection
    {
        /** @var array|string[] $names */
        $names = $this->block->getCalculationVariablesOfContents()->getNames();

        // Filters calculation veriables for the current block
        return $this->calculationVariableService
            ->getCalculationVariableContainers()
            ->getCalculationVariableEntityCollection()
            ->filterByTypes($names)
        ;
    }

    /**
     * Returns data of the instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->block->getName(),
            'attributes' => $this->attributes,
            'calculationVariables' => $this->calculationVariables,
            'options' => $this->options,
        ];
    }
}
