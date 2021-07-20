<?php

namespace App\Services\Calculators\Blocks;

use App\Model\Calculator\BookKeepers\BookKeeper;
use App\Model\Calculator\CalculationVariable;
use App\Model\Calculator\CalculationVariables\StatesOfVisibility;
use App\Model\Calculator\Calculator;
use App\Model\Calculator\CalculatorAssemblyCollection;
use App\Services\Calculators\Errors;
use App\Services\Calculators\Blocks\UserBlock;
use App\Services\Calculators\BookKeeperFactory;
use App\Services\Calculators\Error;
use App\Services\Calculators\ErrorGroups;
use App\Services\Calculators\Exceptions\BookKeeperNotFound;
use Illuminate\Contracts\Container\Container;

/**
 * Keeps user data of blocks and validates their.
 */
class UserBlocks implements \Countable, \Iterator
{
    /**
     * An instance of Container.
     *
     * @var Container
     */
    protected $container;

    /**
     * An instance of BookKeeperFactory.
     *
     * @var BookKeeperFactory
     */
    protected $bookKeeperFactory;

    /**
     * An instance of Calculator.
     *
     * @var Calculator|null
     */
    protected $calculator;

    /**
     * An instance of BookKeeper.
     *
     * @var BookKeeper|null
     */
    protected $bookKeeper;

    /**
     * An instance of BlockCollection.
     *
     * @var BlockCollection|null
     */
    protected $blocks;

    /**
     * An array with user data of blocks.
     *
     * @var array|null
     */
    protected $userDataOfBlocks;

    /**
     * An array with UserBlock.
     *
     * @var array|UserBlock[]
     */
    protected $userBlocks = [];

    /**
     * An instance of ErrorGroups.
     *
     * @var ErrorGroups|null
     */
    protected $errorGroups;

    public function __construct(
        Container $container,
        BookKeeperFactory $bookKeeperFactory
    ) {
        $this->container = $container;
        $this->bookKeeperFactory = $bookKeeperFactory;
    }

    /**
     * Counts user blocks.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->userBlocks);
    }

    /**
     * Rewinds the pointer.
     *
     * @return void
     */
    public function rewind(): void
    {
        reset($this->userBlocks);
    }

    /**
     * Returns the current user block.
     *
     * @return UserBlock
     */
    public function current(): UserBlock
    {
        return current($this->userBlocks);
    }

    /**
     * Returns a current key of the calculation variable.
     *
     * @return int|null
     */
    public function key(): ?int
    {
        return key($this->userBlocks);
    }

    /**
     * Moves the pointer to the next user block.
     *
     * @return void
     */
    public function next(): void
    {
        next($this->userBlocks);
    }

    /**
     * Returns keys of user blocks.
     *
     * @return array|string[]
     */
    public function keys(): array
    {
        return array_keys($this->userBlocks);
    }

    /**
     * Checks whether the current user block is valid.
     *
     * @return bool
     */
    public function valid(): bool
    {
        /** @var UserBlock|bool $userBlock */
        $userBlock = current($this->userBlocks);

        return $userBlock !== false;
    }

    /**
     * Returns a copy of the instance without user blocks.
     *
     * @return self
     */
    public function getCopyWithoutUserBlocks(): self
    {
        /** @var UserBlocks $copy */
        $copy = $this->container->make(self::class);
        $copy->setCalculator($this->calculator);

        return $copy;
    }

    /**
     * Defines a calculator by the given ID.
     *
     * @param  int $id
     * @return bool
     */
    public function defineCalculatorById(int $id): bool
    {
        /** @var Calculator|null $calculator */
        $calculator = Calculator::find($id);

        if ($calculator) {
            $this->setCalculator($calculator);
        }

        return isset($calculator);
    }

    /**
     * Sets the given calculator.
     *
     * @param  Calculator $calculator
     * @return void
     */
    public function setCalculator(Calculator $calculator): void
    {
        $this->calculator = $calculator;

        $this->loadRelationsOfCalculator($calculator);

        // The calculator must have a BookKeeper.
        if (!$this->defineEntitiesOfCalculator()) {
            throw new BookKeeperNotFound($calculator);
        }
    }

    /**
     * Loads missing relations of the given calculator that need blocks.
     *
     * @param  Calculator $calculator
     * @return void
     */
    public function loadRelationsOfCalculator(Calculator $calculator): void
    {
        $calculator->loadMissing(['calculationVariables']);
    }

    /**
     * Returns the current calculator.
     *
     * @return Calculator|null
     */
    public function getCalculator(): ?Calculator
    {
        return $this->calculator;
    }

    /**
     * Returns the current BookKeeper of the calculator.
     *
     * @return BookKeeper|null
     */
    public function getBookKeeper(): ?BookKeeper
    {
        return $this->bookKeeper;
    }

    /**
     * Fills the instance with user data of blocks.
     *
     * @param  array $userDataOfBlocks
     * @return void
     */
    public function fillFromArray(array $userDataOfBlocks): void
    {
        $this->userDataOfBlocks = $userDataOfBlocks;

        foreach ($userDataOfBlocks as $userDataOfBlock) {
            /** @var Block|null $block */
            $block = $this->blocks->get($userDataOfBlock['name']);

            if ($block) {
                $this->addUserBlock($block, $userDataOfBlock);
            }
        }

        // Defines visibility of blocks, because need to check blocks
        // using the current visibility of blocks by user data
        // and rules of visibility blocks.
        $this->defineVisibilityByUserBlocks();
    }

    /**
     * Returns user data of blocks.
     *
     * @return array|null
     */
    public function getUserDataOfBlocks(): ?array
    {
        return $this->userDataOfBlocks;
    }

    /**
     * Adds a new UserBlock with the given user data of the block.
     *
     * @param  Block $block
     * @param  array $userDataOfBlock
     * @return UserBlock
     */
    public function addUserBlock(Block $block, array $userDataOfBlock = []): UserBlock
    {
        $userBlock = $this->container->make(UserBlock::class, [
            'calculator' => $this->calculator,
            'bookKeeper' => $this->bookKeeper,
            'block' => $block,
        ]);
        $userBlock->fillFromArray($userDataOfBlock);
        $this->userBlocks[$userBlock->getName()] = $userBlock;

        return $userBlock;
    }

    /**
     * Returns an UserBlock by the given name.
     *
     * @param  string $name
     * @return UserBlock|null
     */
    public function getUserBlock(string $name): ?UserBlock
    {
        return $this->userBlocks[$name] ?? null;
    }

    /**
     * Checks whether the collection has a user block with the given name.
     *
     * @param  string $name
     * @return bool
     */
    public function hasUserBlock(string $name): bool
    {
        return isset($this->userBlocks[$name]);
    }

    /**
     * Sets user blocks.
     *
     * @param  array|UserBlock[] $userBlocks
     * @return void
     */
    public function setUserBlocks(array $userBlocks): void
    {
        $this->userBlocks = [];

        foreach ($userBlocks as $userBlock) {
            if ($userBlock instanceof UserBlock) {
                $this->userBlocks[$userBlock->getName()] = $userBlock;
            }
        }
    }

    /**
     * Returns user blocks.
     *
     * @return array|UserBlock[]
     */
    public function getUserBlocks(): array
    {
        return $this->userBlocks;
    }

    /**
     * Returns user blocks except the given user block.
     *
     * @param  UserBlock $exceptUserBlock
     * @return UserBlocks
     */
    public function getUserBlocksExceptUserBlock(UserBlock $exceptUserBlock): self
    {
        /** @var array|UserBlock[] $userBlocks */
        $userBlocks = array_filter($this->userBlocks, function (UserBlock $userBlock) use ($exceptUserBlock) {
            return $userBlock !== $exceptUserBlock;
        });

        $instance = $this->getCopyWithoutUserBlocks();
        $instance->setUserBlocks($userBlocks);

        return $instance;
    }

    /**
     * Returns visible user blocks.
     *
     * @return UserBlocks
     */
    public function getVisibleUserBlocks(): self
    {
        $userBlocks = array_filter($this->userBlocks, function (UserBlock $userBlock) {
            return $userBlock->isVisible();
        });

        $instance = $this->getCopyWithoutUserBlocks();
        $instance->setUserBlocks($userBlocks);

        return $instance;
    }

    /**
     * Returns an element of user block by the given permission.
     *
     * @param  PermissionForVisualization $permission
     * @return CalculationVariable|null
     */
    public function getElementOfUserBlockByPermission(PermissionForVisualization $permission)
    {
        /** @var UserBlock|null $userBlock */
        $userblock = $this->getUserBlock($permission->getBlockName());

        if ($userblock) {
            if ($permission->getComponentName() === 'calculationVariables') {
                return $userblock->getCalculationVariableEntityByName($permission->getElementName());
            }
        }

        return null;
    }

    /**
     * Returns an element of user block by the given permission.
     *
     * @param  PermissionForVisualization $permission
     * @return StatesOfVisibility|null
     */
    public function getElementOfVizualizationOfUserBlockByPermission(
        PermissionForVisualization $permission
    ): ?StatesOfVisibility {
        /** @var StatesOfVisibility|null $element */
        $element = $this->getElementOfUserBlockByPermission($permission);

        return $element instanceof StatesOfVisibility ? $element : null;
    }

    /**
     * Returns keys of user blocks.
     *
     * @return array|string[]
     */
    public function getKeysOfUserBlocks(): array
    {
        return array_keys($this->userBlocks);
    }

    /**
     * Returns keys of missing user blocks.
     *
     * @return array|string[]
     */
    public function getKeysOfMissingUserBlocks(): array
    {
        $keysOfRequiredBlocks = $this->blocks->getKeysOfRequiredBlocks();
        $keysOfUserBlocks = $this->getKeysOfUserBlocks();

        return array_values(array_diff($keysOfRequiredBlocks, $keysOfUserBlocks));
    }

    /**
     * Defines visibility of blocks by the current user blocks.
     *
     * @return void
     */
    public function defineVisibilityByUserBlocks(): void
    {
        /** @var UserBlock $userBlock */
        foreach ($this->userBlocks as $userBlock) {
            if ($userBlock->canBeChanged()) {
                $userBlock->defineVisibilityByOtherUserBlocks($this);
            }
        }
    }

    /**
     * Validates each user block.
     * If one of blocks has any error then returns false.
     *
     * @return bool
     */
    public function validate(): bool
    {
        if (!$this->isCalculatorReady() || !$this->validateRequiredUserBlock()) {
            return false;
        }

        $visibleUserBlocks = $this->getVisibleUserBlocks();

        /** @var UserBlock $userBlock */
        foreach ($visibleUserBlocks->getUserBlocks() as $userBlock) {
            $userBlock->validate();
            $this->errorGroups->updateOrSetErrors($userBlock->getName(), $userBlock->getErrors());
        }

        return !$this->errorGroups->hasErrors();
    }

    /**
     * Checks whether the calculator is ready.
     *
     * @return bool
     */
    public function isCalculatorReady(): bool
    {
        $errorsOfCalculator = new Errors;

        if (!isset($this->calculator)) {
            $errorsOfCalculator->add(trans('calculator_errors.calculator_not_found'), Error::CONFIGURATION_ERROR);
        } elseif (!$this->calculator->active) {
            $errorsOfCalculator->add(trans('calculator_errors.calculator_is_not_active'), Error::CONFIGURATION_ERROR);
        } elseif (!isset($this->bookKeeper)) {
            $errorsOfCalculator->add(trans('calculator_errors.bookkeeper_not_defined'), Error::SYSTEM_ERROR);
        } elseif (!$this->bookKeeper->getBlocks()->count()) {
            $errorsOfCalculator->add(trans('calculator_errors.bookkeeper_has_not_blocks'), Error::SYSTEM_ERROR);
        }

        // Sets errors of the calculator to the calculator group.
        $this->errorGroups->updateOrSetErrors('calculator', $errorsOfCalculator);

        return !$errorsOfCalculator->hasErrors();
    }

    /**
     * Validates required user blocks.
     *
     * @return bool
     */
    public function validateRequiredUserBlock(): bool
    {
        // The number of blocks must be equal or less then the number of required blocks.
        if (count($this->userBlocks) < $this->blocks->countRequiredBlocks()) {
            $keysOfMissingBlocks = $this->getKeysOfMissingUserBlocks();

            foreach ($keysOfMissingBlocks as $keyOfUserBlock) {
                $this->errorGroups->add(
                    $keyOfUserBlock,
                    trans('calculator_errors.required_block_was_not_given'),
                    Error::ATTRIBUTE_ERROR
                );
            }

            return false;
        }

        return true;
    }

    /**
     * Returns an instance of ErrorGroups.
     *
     * @return ErrorGroups
     */
    public function getErrorGroups(): ErrorGroups
    {
        return $this->errorGroups;
    }

    /**
     * Returns an instance of Errors with all errors of blocks.
     *
     * @return Errors
     */
    public function getErrors(): Errors
    {
        return $this->errorGroups->collapse();
    }

    /**
     * Returns an instance of Errors by the given name of an user block.
     *
     * @param  string $name
     * @return Errors|null
     */
    public function getErrorsByNameOfUserBlock(string $name): ?Errors
    {
        return isset($this->userBlocks[$name])
            ? $this->userBlocks[$name]->getErrors()
            : null
        ;
    }

    /**
     * Returns assemblies of user blocks.
     *
     * @return CalculatorAssemblyCollection
     */
    public function getAssemblies(): CalculatorAssemblyCollection
    {
        $assemblies = [];
        $visibleUserBlocks = $this->getVisibleUserBlocks();

        /** @var UserBlock $userBlock */
        foreach ($visibleUserBlocks as $userBlock) {
            if ($userBlock->getBlock()->mustHasAssembly()) {
                $assemblies[$userBlock->getName()] = $userBlock->getAssembly();
            }
        }

        return new CalculatorAssemblyCollection(array_filter($assemblies));
    }

    /**
     * Returns names of blocks that can has an assembly.
     *
     * @return array|string[]
     */
    public function getNamesOfBlocksWithAssemblies(): array
    {
        $names = [];

        /** @var UserBlock $userBlock */
        foreach ($this->userBlocks as $userBlock) {
            if ($userBlock->getBlock()->mustHasAssembly()) {
                $names[] = $userBlock->getName();
            }
        }

        return $names;
    }

    /**
     * Returns names of visible blocks with assembly.
     *
     * @return array|string[]
     */
    public function getNamesOfVisibleBlocksWithAssembly(): array
    {
        $names = [];
        $visibleUserBlocks = $this->getVisibleUserBlocks();

        /** @var UserBlock $userBlock */
        foreach ($visibleUserBlocks as $userBlock) {
            if ($userBlock->getBlock()->mustHasAssembly()) {
                $names[] = $userBlock->getName();
            }
        }

        return $names;
    }

    /**
     * Returns the first name of a visible block with an assembly.
     *
     * @return string|null
     */
    public function getFirstNameOfVisibleBlockWithAssembly(): ?string
    {
        $names = $this->getNamesOfVisibleBlocksWithAssembly();

        return $names[0] ?? null;
    }

    /**
     * Checks whether the user block use an assembly.
     *
     * @param  string $blockName
     * @return bool
     */
    public function doesUserBlockUseAssembly(string $blockName): bool
    {
        return $this->hasUserBlock($blockName)
            ? $this->getUserBlock($blockName)->doesUserBlockUseAssembly()
            : false
        ;
    }

    /**
     * Returns data of the instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_map(function (UserBlock $userBlock) {
            return $userBlock->toArray();
        }, $this->userBlocks);
    }

    /**
     * Defines entities of the current calculator: BookKeeper, Blocks, ErrorGroups.
     * Returns 'true' when entities were defined.
     *
     * @return bool
     */
    protected function defineEntitiesOfCalculator(): bool
    {
        $this->bookKeeper = $this->bookKeeperFactory->getBookKeeper($this->calculator);

        if (!$this->bookKeeper) {
            $this->blocks = null;
            $this->errorGroups = null;

            return false;
        }

        $this->blocks = $this->bookKeeper->getBlocks();
        $this->errorGroups = new ErrorGroups($this->blocks->keys());

        return true;
    }
}
