<?php

namespace App\Services\Calculators\FrontConverter;

use App\Services\Calculators\Estimate;
use App\Services\Calculators\EstimateGroup;
use App\Services\Calculators\EstimateSubgroup;
use App\Services\Calculators\EstimateValue;

/**
 * Implements functions to describe a specification by an estimate.
 */
class SpecificationDescriber
{
    public const GROUP_TYPE_OF_ATTRIBUTES = 'attributes';

    public const GROUP_TYPE_OF_OPTIONS = 'options';

    public const STANDARD_GROUP_NAME_OF_ATTRIBUTES = self::GROUP_TYPE_OF_ATTRIBUTES;

    public const STANDARD_GROUP_NAME_OF_OPTIONS = self::GROUP_TYPE_OF_OPTIONS;

    /**
     * An instance of Estimate.
     *
     * @var Estimate
     */
    protected $estimate;

    /**
     * Prefixes of groups that have parents.
     *
     * @var array|string[]
     */
    protected $prefixesOfGroups = [
        'attributes_of_',
        'options_of_',
    ];

    /**
     * Initializes an instance of the class.
     *
     * @param Estimate $estimate
     */
    public function __construct(Estimate $estimate)
    {
        $this->estimate = $estimate;
    }

    /**
     * Returns an instance of Estimate.
     *
     * @return Estimate
     */
    public function getEstimate(): Estimate
    {
        return $this->estimate;
    }

    /**
     * Returns a description of the estimate for a specification.
     *
     * @return array|string[]
     */
    public function describe(): array
    {
        return array_merge(
            $this->getDefaultDescription(),
            $this->getDescriptionOfGroups()
        );
    }

    /**
     * Returns a default description from a default group by the given name.
     *
     * @param  string $groupName A group name of the default group.
     * @return array|string[]
     */
    public function getDefaultDescription(string $groupName = 'default'): array
    {
        $estimate = $this->getEstimate();
        $defaultGroup = $estimate ? $estimate->getGroup($groupName) : null;
        $description = [];

        if ($defaultGroup) {
            $description = $this->getDescriptionOfProductSize($defaultGroup);
        }

        return $description;
    }

    /**
     * Returns a description of a product size from the given group.
     *
     * @param  EstimateGroup $group A group with sizes of the product.
     * @return array|string[]
     */
    public function getDescriptionOfProductSize(EstimateGroup $group): array
    {
        $description = [];

        if ($group->has('product_height') && $group->has('product_width')) {
            $height = $group->get('product_height')->getValue();
            $width = $group->get('product_width')->getValue();

            $description[] = trans('estimate.product_format') . ': '
                . $width . ' x ' . $height . ' ' . trans('units.mm')
            ;
        }

        return $description;
    }

    /**
     * Returns a description of groups.
     *
     * @return array|string[]
     */
    public function getDescriptionOfGroups(): array
    {
        // Groups can have related groups,
        // for example substrate and options_of_substrate, etc.
        // If groups have related groups then need to handle groups as
        // related groups else need to handle all groups.
        return $this->hasEstimateManyAssemblies()
            ? $this->getDescriptionsOfRelatedGroups()
            : $this->getDescriptionOfUnrelatedGroups()
        ;
    }

    /**
     * Checks whether the estimate has groups with description
     * of some assemblies or only one assembly.
     * Returns true when the estimate has description of some assemblies.
     *
     * @return bool
     */
    public function hasEstimateManyAssemblies(): bool
    {
        foreach ($this->estimate as $groupName => $group) {
            $groupType = $this->detectGroupTypeByGroup($group);

            if ($groupType && $this->getParentGroupOfGroupName($groupName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns group names of assemblies.
     *
     * @return array|string[]
     */
    public function getGroupNamesOfAssemblies(): array
    {
        $keys = $this->estimate->keys();
        $groupNames = [];

        foreach ($keys as $groupName) {
            if ($this->hasGroupWithPrefixByGroupName($groupName)) {
                $groupNames[] = $groupName;
            }
        }

        return $groupNames;
    }

    /**
     * Checks whether the estimate has a group with a prefix
     * and the given group name.
     *
     * @return bool
     */
    public function hasGroupWithPrefixByGroupName(string $groupName): bool
    {
        $names = $this->getPrefixesWithName($groupName);

        foreach ($this->estimate as $group) {
            if (array_search($group->getName(), $names) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Makes and returns prefixes with the given name.
     *
     * @param  string $name
     * @return array|string[]
     */
    public function getPrefixesWithName(string $name): array
    {
        return array_map(function ($prefix) use ($name) {
            return $prefix.$name;
        }, $this->prefixesOfGroups);
    }

    /**
     * Returns descriptions of related groups.
     * All related groups belong to different pools.
     *
     * @return array|string[]
     */
    public function getDescriptionsOfRelatedGroups(): array
    {
        $groupNames = $this->getGroupNamesOfAssemblies();
        $description = [];

        foreach ($groupNames as $groupName) {
            // Adds a title of the group.
            $description[] = trans("estimate.$groupName");
            array_push($description, ...$this->getDescriptionByGroup($this->estimate->getGroup($groupName)));
            array_push($description, ...$this->getDescriptionOfRelatedGroups($groupName));
        }

        return $description;
    }

    /**
     * Returns a description of related groups.
     * All related groups belong to one pool.
     *
     * @param string $groupName
     * @return array
     */
    public function getDescriptionOfRelatedGroups(string $groupName): array
    {
        $groupNames = $this->getPrefixesWithName($groupName);
        $description = [];

        foreach ($groupNames as $relatedGroupNames) {
            $group = $this->estimate->getGroup($relatedGroupNames);

            if ($group) {
                array_push($description, ...$this->getDescriptionByGroup($group));
            }
        }

        return $description;
    }

    /**
     * Returns a description of unrelated groups.
     *
     * @return array|string[]
     */
    public function getDescriptionOfUnrelatedGroups(): array
    {
        $description = [];

        foreach ($this->estimate as $group) {
            array_push($description, ...$this->getDescriptionByGroup($group));
        }

        return $description;
    }

    /**
     * Returns a description by the given group.
     * Tries to get any description.
     *
     * @param  EstimateGroup $group
     * @return array|string[]
     */
    public function getDescriptionByGroup(EstimateGroup $group): array
    {
        $groupType = $this->detectGroupTypeByGroup($group);
        $description = [];

        switch ($groupType) {
            case static::GROUP_TYPE_OF_ATTRIBUTES:
                return $this->getDescriptionOfAttributes($group);

            case static::GROUP_TYPE_OF_OPTIONS:
                return $this->getDescriptionOfOptions($group);
        }

        // NOTE: The value will be used in another column of the specification.
        // if ($this->hasGroupPrintedSheet($group)) {
        //     array_push(
        //         $description,
        //         ...$this->getDescriptionOfPrintedSheet($group->get('printed_sheet')->getValue())
        //     );
        // }

        return array_merge($description, $this->getDescriptionOfProductSize($group));
    }

    /**
     * Detects a group type by the given estimate group.
     *
     * @param  EstimateGroup $group
     * @return string|null
     */
    public function detectGroupTypeByGroup(EstimateGroup $group): ?string
    {
        if (
            mb_strpos($group->getName(), static::STANDARD_GROUP_NAME_OF_OPTIONS) === 0
            && $this->hasOptions($group)
        ) {
            return static::GROUP_TYPE_OF_OPTIONS;
        } elseif (mb_strpos($group->getName(), static::STANDARD_GROUP_NAME_OF_ATTRIBUTES) === 0) {
            return static::GROUP_TYPE_OF_ATTRIBUTES;
        }

        return null;
    }

    /**
     * Checks whether the given group has options.
     *
     * @param  EstimateGroup $group
     * @return bool
     */
    public function hasOptions(EstimateGroup $group): bool
    {
        foreach ($group as $item) {
            $value = $item->getValue();

            if (!$this->isValueOption($value)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Checks whether the given value is an option.
     *
     * @param  EstimateValue|mixed $value
     * @return bool
     */
    public function isValueOption($value): bool
    {
        return $value instanceof EstimateSubgroup && $value->getType() === 'option';
    }

    /**
     * Checks whether the group has a printed sheet.
     *
     * @param  EstimateGroup $group
     * @return bool
     */
    public function hasGroupPrintedSheet(EstimateGroup $group): bool
    {
        return array_search('printed_sheet', $group->keys()) !== false;
    }

    /**
     * Returns a description of a printed sheet by the given subgroup of printed sheet.
     *
     * @param  EstimateSubgroup $subgroup
     * @return array|string[]
     */
    public function getDescriptionOfPrintedSheet(EstimateSubgroup $subgroup): array
    {
        return [
            trans('estimate.name_of_printed_sheet') . ': '
                . $subgroup->get('name_of_printed_sheet')->getValue(),
        ];
    }

    /**
     * Checks whether the given group name belongs to some group.
     *
     * @param  string $groupName
     * @return bool
     */
    public function doesGroupNameBelongToAnyGroup(string $groupName): bool
    {
        foreach ($this->prefixesOfGroups as $prefix) {
            if (mb_strpos($groupName, $prefix) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns a parent group name by the given group name.
     *
     * @param  string $groupName
     * @return string|null
     */
    public function getParentGroupOfGroupName(string $groupName): ?string
    {
        foreach ($this->prefixesOfGroups as $prefix) {
            if (mb_strpos($groupName, $prefix) === 0) {
                return mb_substr($groupName, mb_strlen($prefix));
            }
        }

        return null;
    }

    /**
     * Returns a description of attributes by the given estimate group.
     *
     * @param  EstimateGroup $group
     * @return array|string[]
     */
    public function getDescriptionOfAttributes(EstimateGroup $group): array
    {
        $description = [];

        foreach ($group as $itemKey => $item) {
            /** @var EstimateSubgroup $value */
            $value = $item->getValue();
            $description[] = $itemKey . ': ' . $value->get('value_of_attribute')->getValue();
        }

        return $description;
    }

    /**
     * Returns a description of options by the given estimate group.
     *
     * @param  EstimateGroup $group
     * @return array|string[]
     */
    public function getDescriptionOfOptions(EstimateGroup $group): array
    {
        $description = [];

        foreach ($group as $item) {
            $value = $item->getValue();

            if ($this->isValueOption($value)) {
                $description[] = $this->getDescriptionOfOption($value);
            }
        }

        return $description;
    }

    /**
     * Returns a description of the given option by the given estimate subgroup.
     *
     * @param  EstimateSubgroup $option
     * @return string
     */
    public function getDescriptionOfOption(EstimateSubgroup $option): string
    {
        return $option->get('name_of_option')->getValue() . ': '
            . $option->get('name_of_option_value')->getValue()
        ;
    }
}
