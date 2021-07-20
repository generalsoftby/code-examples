<?php

namespace App\Model\Calculator\PricingRulesOfAssemblies;

use App\Model\Calculator\CalculatorPrintedSheet as PrintedSheet;

/**
 * Contains data about a sheet and contains rules for calculation.
 */
class SheetWithRules
{
    /**
     * An ID of sheet.
     *
     * @var int
     */
    protected $id;

    /**
     * A type of pricing rule.
     *
     * @var string
     */
    protected $type;

    /**
     * A PricingRule.
     *
     * @var PricingRule
     */
    protected $rule;

    /**
     * A PrintedSheet of the sheet.
     *
     * @var PrintedSheet
     */
    protected $printedSheet;

    /**
     * Initializes an instance of the class with an ID of the sheet,
     * a type of the sheet and its rules.
     *
     * @param int         $id    An ID of the sheet.
     * @param string      $type  A type of pricing rules.
     * @param PricingRule $rule  The pricing rule with settings.
     */
    function __construct(int $id, string $type, PricingRule $rule)
    {
        $this->id = $id;
        $this->type = $type;
        $this->rule = $rule;
    }

    /**
     * Returns an ID of the sheet.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Returns a type of the sheet.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Returns the pricing rule with settings.
     *
     * @return PricingRule
     */
    public function getPricingRule(): PricingRule
    {
        return $this->rule;
    }

    /**
     * Checks whether the sheet is ready for the calculation.
     *
     * @return bool
     */
    public function isReady(): bool
    {
        return $this->rule->isConfigured();
    }

    /**
     * Sets a model of the sheet.
     *
     * @param PrintedSheet $printedSheet
     */
    public function setPrintedSheet(PrintedSheet $printedSheet)
    {
        $this->printedSheet = $printedSheet;
    }

    /**
     * Returns a model of the sheet.
     *
     * @return PrintedSheet
     */
    public function getPrintedSheet(): PrintedSheet
    {
        return $this->printedSheet;
    }

    /**
     * Returns a name of the printed sheet.
     *
     * @return string
     */
    public function getNameOfPrintedSheet(): string
    {
        return $this->printedSheet->name;
    }

    /**
     * Checks whether the sheet has its printed sheet.
     *
     * @return bool
     */
    public function hasPrintedSheet(): bool
    {
        return isset($this->printedSheet);
    }

    /**
     * Returns an instance in the form if an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $array = [
            'id' => $this->id,
            'type' => $this->type,
            'rule' => $this->rule->toArray(),
        ];

        if (isset($this->printedSheet)) {
            $array['name'] = $this->printedSheet->title;
        }

        return $array;
    }
}
