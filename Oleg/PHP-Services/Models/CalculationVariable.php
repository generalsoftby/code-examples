<?php

namespace App\Model\Calculator;

use App\Model\Calculator\CalculationVariables\CalculationVariableEntity;
use Dios\System\Multicasting\AttributeMulticasting;
use Dios\System\Multicasting\ReadwriteInstance;
use Dios\System\Multicasting\Interfaces\SimpleArrayEntity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

/**
 * Calculation variables of calculators. Keeps settings of calculation variables.
 *
 * @property int $id An ID of the calculation variables.
 * @property string|null $settings Settings of the components of calculation variables.
 * @property string $type A type of the calculation variable.
 * @property string $name A name of the calculation variable of the calculator.
 */
class CalculationVariable extends Model
{
    use AttributeMulticasting, ReadwriteInstance;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'settings' => 'array',
    ];

    /**
     * The source that contains an entity type.
     * When set second value, then may to use caching of a result of the search
     * entity key.
     *
     * @var string
     */
    protected $sourceWithEntityType = 'type';

    /**
     * Type mapping of entity types and their handlers.
     *
     * @var array
     */
    protected $entityTypeMapping = [
        'print_formats' => \App\Model\Calculator\CalculationVariables\PrintFormats::class,
        'number_of_products' => \App\Model\Calculator\CalculationVariables\NumberOfProducts::class,
        'stitching_type' => \App\Model\Calculator\CalculationVariables\StitchingType::class,
        'gluing' => \App\Model\Calculator\CalculationVariables\Gluing::class,
        'eyelets' => \App\Model\Calculator\CalculationVariables\Eyelets::class,
        'sides' => \App\Model\Calculator\CalculationVariables\Sides::class,
    ];

    /**
     * The property that contains values for entities.
     *
     * @var string
     */
    protected $propertyForEntity = 'settings';

    /**
     * The instance type of entities.
     *
     * @var string
     */
    protected $interfaceType = SimpleArrayEntity::class;

    /**
     * Returns the calculator.
     *
     * @return BelongsTo
     */
    public function calculator(): BelongsTo
    {
        return $this->belongsTo(Calculator::class);
    }

    /**
     * Returns variables by a type.
     *
     * @param  Builder $query
     * @param  string $type
     * @return Builder
     */
    public function scopeType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    /**
     * Returns an entity with settings of the calculation variable.
     *
     * @return CalculationVariableEntity|null
     */
    public function getSettings(): ?CalculationVariableEntity
    {
        return $this->getInstance();
    }

    /**
     * Returns a type of calculation variable by the given class name
     * of an instance.
     *
     * @param  string $className
     * @return string|null
     */
    public function getTypeByHandlerClassName(string $className): ?string
    {
        /** @var string|bool $type */
        $type = array_search($className, $this->entityTypeMapping);

        return $type ? $type : null;
    }

    /**
     * Initializes a collection with calculation variables.
     *
     * @param  array|CalculationVariable[] $models
     * @return CalculationVariableCollection|CalculationVariable[]
     */
    public function newCollection(array $models = []): CalculationVariableCollection
    {
        return new CalculationVariableCollection($models);
    }
}
