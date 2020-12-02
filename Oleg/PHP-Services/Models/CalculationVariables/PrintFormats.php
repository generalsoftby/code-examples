<?php

namespace App\Model\Calculator\CalculationVariables;

use App\Services\Calculators\Error;
use App\Services\Calculators\Errors;

/**
 * Contains a list of allowed print formats and/or rules for a sheet.
 */
class PrintFormats implements CalculationVariableEntity
{
    /**
     * A collection with sheet foramts.
     *
     * @var SheetFormatCollection|SheetFormat[]
     */
    protected $formats;

    /**
     * A SheetSize.
     *
     * @var SheetSize
     */
    protected $sheetSize;

    /**
     * A state of using of fixed formats.
     *
     * @var bool
     */
    protected $fixedFormats;

    /**
     * A min height for the sheet size.
     *
     * @var int
     */
    protected $minHeight;

    /**
     * A max height for the sheet size.
     *
     * @var int|null
     */
    protected $maxHeight;

    /**
     * A state of the visibility of limitation for the sheet height.
     *
     * @var bool
     */
    protected $visibleLimitationForHeight;

    /**
     * A min width of the sheet size.
     *
     * @var int
     */
    protected $minWidth;

    /**
     * A max width for the sheet size.
     *
     * @var int|null
     */
    protected $maxWidth = null;

    /**
     * A state of the visibility of limitation for the sheet width.
     *
     * @var bool
     */
    protected $visibleLimitationForWidth;

    /**
     * Errors of validation.
     *
     * @var Errors|Error[]
     */
    protected $errors;

    /**
     * Initializes an instance of the class.
     *
     * @param array|null $values
     */
    public function __construct(array $values = null)
    {
        $this->formats = new SheetFormatCollection;
        $this->sheetSize = new SheetSize;
        $this->errors = new Errors;

        $this->fillFromArray($values ?? []);
    }

    /**
     * Fills the instance from an array.
     *
     * @param  array $values
     * @return void
     */
    public function fillFromArray(array $values)
    {
        $this->formats->setSheetFormats(
            $this->makeSheetFormatsFromArray($values['formats'] ?? [])
        );
        $this->fixedFormats = (bool) ($values['rules']['fixed_print_formats'] ?? false);
        $this->minHeight = (int) ($values['rules']['height']['min'] ?? 1);
        $this->maxHeight = isset($values['rules']['height']['max']) ? (int) $values['rules']['height']['max'] : null;
        $this->visibleLimitationForHeight = (bool) ($values['rules']['height']['visible_limitation'] ?? false);
        $this->minWidth = (int) ($values['rules']['width']['min'] ?? 1);
        $this->maxWidth = isset($values['rules']['width']['max']) ? (int) $values['rules']['width']['max'] : null;
        $this->visibleLimitationForWidth = (bool) ($values['rules']['width']['visible_limitation'] ?? false);
    }

    /**
     * Returns a SheetFormatCollection.
     *
     * @return SheetFormatCollection
     */
    public function getSheetFormatCollection(): SheetFormatCollection
    {
        return $this->formats;
    }

    /**
     * Sets a state of using of fixed formats.
     *
     * @param  bool $state
     * @return void
     */
    public function useFixedFormats(bool $state = true): void
    {
        $this->fixedFormats = $state;
    }

    /**
     * Checks whether the instance uses fixed formats.
     *
     * @return bool
     */
    public function doesInstanceUseFixedFormats(): bool
    {
        return $this->fixedFormats;
    }

    /**
     * Returns a min height.
     *
     * @return int|null
     */
    public function getMinHeight(): ?int
    {
        return $this->minHeight;
    }

    /**
     * Returns a max height.
     * The null is unlimited.
     *
     * @return int|null
     */
    public function getMaxHeight(): ?int
    {
        return $this->maxHeight;
    }

    /**
     * Checks whether the height has the visible limitation.
     *
     * @return bool
     */
    public function hasHeightVisibleLimitation(): bool
    {
        return $this->visibleLimitationForHeight;
    }

    /**
     * Returns a min width.
     *
     * @return int|null
     */
    public function getMinWidth(): ?int
    {
        return $this->minWidth;
    }

    /**
     * Returns a max width.
     * The null is unlimited.
     *
     * @return int|null
     */
    public function getMaxWidth(): ?int
    {
        return $this->maxWidth;
    }

    /**
     * Checks whether the width has the visible limitation.
     *
     * @return bool
     */
    public function hasWidthVisibleLimitation(): bool
    {
        return $this->visibleLimitationForWidth;
    }

    /**
     * Fills user variables with user values.
     * Returns true whether user variables were filled with user values.
     *
     * @param  array $values
     * @return bool
     */
    public function fillWithUserValues(array $values): bool
    {
        if (! $this->validate($values)) {
            return false;
        }

        return $this->defineSheetFormat($values['format']['index'], $values['format']['name'], $values['height'], $values['width']);
    }

    /**
     * Validates given user data and returns result of the validation.
     *
     * @return bool
     */
    public function validate($data): bool
    {
        $state = true;

        if (! isset($data['width'], $data['height'])) {
            $this->errors->add(trans('calculator_errors.sheet_size_is_not_set'), Error::VARIABLE_OF_CALCULATION_ERROR);
            $state = false;
        } elseif (is_int($data['width'] && is_int($data['height']))) {
            $this->errors->add(
                trans('calculator_errors.sheet_sizes_are_not_integers'), Error::VARIABLE_OF_CALCULATION_ERROR
            );
            $state = false;
        } elseif ($data['height'] < $this->getMinHeight()) {
            $this->errors->add(
                trans('calculator_errors.height_of_sheet_less_than_min_height', ['value' => $this->getMinHeight()]),
                Error::VARIABLE_OF_CALCULATION_ERROR
            );
            $state = false;
        } elseif ($data['width'] < $this->getMinWidth()) {
            $this->errors->add(
                trans('calculator_errors.width_of_sheet_less_than_min_width', ['value' => $this->getMinWidth()]),
                Error::VARIABLE_OF_CALCULATION_ERROR
            );
            $state = false;
        } elseif (
            $this->doesInstanceUseFixedFormats()
            && isset($data['format']['index'])
            && $data['format']['index'] === 'custom'
        ) {
            $this->errors->add(trans('calculator_errors.select_sheet_format_in_allowed_formats'), Error::VARIABLE_OF_CALCULATION_ERROR);
            $state = false;
        } elseif (
            $this->doesInstanceUseFixedFormats()
            && ! $this->formats->find($data['format']['index'], $data['format']['name'], $data['height'], $data['width'])->count()
        ) {
            $this->errors->add(trans('calculator_errors.sheet_format_not_exists'), Error::VARIABLE_OF_CALCULATION_ERROR);
            $state = false;
        } elseif ($this->getMaxHeight() && $data['height'] > $this->getMaxHeight()) {
            $this->errors->add(
                trans('calculator_errors.height_of_sheet_greater_than_max_height', ['value' => $this->getMaxHeight()]),
                Error::VARIABLE_OF_CALCULATION_ERROR
            );
            $state = false;
        } elseif ($this->getMaxWidth() && $data['width'] > $this->getMaxWidth()) {
            $this->errors->add(
                trans('calculator_errors.width_of_sheet_greater_than_max_width'),
                Error::VARIABLE_OF_CALCULATION_ERROR
            );
            $state = false;
        }

        return $state;
    }

    /**
     * Defines a selected sheet format by the index and the name.
     * Returns true if a format were defined.
     *
     * @param  string $index
     * @param  string $name
     * @param  int    $height
     * @param  int    $width
     * @return bool
     */
    public function defineSheetFormat(string $index, string $name, int $height, int $width): bool
    {
        if ($index === 'custom' && $this->fixedFormats) {
            return false;
        }

        if ($index === 'custom') {
            $this->setSheetSize($height, $width);
            return true;
        }

        /** @var SheetFormatCollection $sheetFormats **/
        $sheetFormats = $this->formats->find($index, $name, $height, $width);

        if ($sheetFormats->count() !== 1) {
            return false;
        }

        /** @var SheetFormat $sheetFormat **/
        $sheetFormat = $sheetFormats->current();

        $this->sheetSize->setSheetSize($sheetFormat->getHeight(), $sheetFormat->getWidth());

        return true;
    }

    /**
     * Sets a size of the sheet.
     *
     * @param  int $height
     * @param  int $width
     * @return void
     */
    public function setSheetSize(int $height, int $width): void
    {
        $this->sheetSize->setSheetSize($height, $width);
    }

    /**
     * Returns the current sheet size.
     *
     * @return SheetSize
     */
    public function getCurrentSheetSize(): SheetSize
    {
        return $this->sheetSize;
    }

    /**
     * Sets a height of the sheet.
     *
     * @param  int $height
     * @return void
     */
    public function setHeight(int $height): void
    {
        $this->sheetSize->setHeight($height);
    }

    /**
     * Returns a height of the sheet.
     *
     * @return int
     */
    public function getHeight(): int
    {
        return $this->sheetSize->getHeight();
    }

    /**
     * Sets a width of the sheet.
     *
     * @param  int $width
     * @return void
     */
    public function setWidth(int $width): void
    {
        $this->sheetSize->setWidth($width);
    }

    /**
     * Returns a width of the sheet.
     *
     * @return int
     */
    public function getWidth(): int
    {
        return $this->sheetSize->getWidth();
    }

    /**
     * Returns an area of the sheet.
     *
     * @return int
     */
    public function getArea(): int
    {
        return $this->sheetSize->getArea();
    }

    /**
     * Returns errors of validation.
     *
     * @return Errors|Error[]
     */
    public function getErrors(): Errors
    {
        return $this->errors;
    }

    /**
     * Returns data of the current instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        $rules['fixed_print_formats'] = $this->fixedFormats;

        if (! $this->fixedFormats) {
            $rules += [
                'height' => [
                    'min' => $this->minHeight,
                    'max' => $this->maxHeight,
                    'visible_limitation' => $this->visibleLimitationForHeight,
                ],
                'width' => [
                    'min' => $this->minWidth,
                    'max' => $this->maxWidth,
                    'visible_limitation' => $this->visibleLimitationForWidth,
                ],
            ];
        }

        return [
            'formats' => $this->formats->toArray(),
            'rules' => $rules,
        ];
    }

    /**
     * Makes an array and returns its with sheet formats
     * from a custom array with formats.
     *
     * @param array $formats
     * @return array|SheetFormat[]
     */
    protected function makeSheetFormatsFromArray(array $formats): array
    {
        $formats = array_filter($formats, function ($format) {
            return isset($format['height'], $format['width'], $format['name']);
        });

        return array_map(function ($format) {
            return $this->newSheetFormat($format['height'], $format['width'], $format['name']);
        }, $formats);
    }

    /**
     * Initializes a new SheetFormat.
     *
     * @param  mixed $height
     * @param  mixed $width
     * @param  string $name
     * @return SheetFormat
     */
    protected function newSheetFormat($height, $width, string $name): SheetFormat
    {
        /** @var int $height */
        $height = is_numeric($height)
            ? (int) $height
            : 0
        ;

        /** @var int $width */
        $width = is_numeric($width)
            ? (int) $width
            : 0
        ;

        return new SheetFormat($height, $width, $name);
    }
}
