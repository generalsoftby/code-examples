<?php

namespace App\Services\Calculators\Blocks;

/**
 * Keeps and handles permissions for visualization of a block.
 */
class PermissionForVisualizationCollection implements \Countable, \Iterator
{
    /**
     * A current position of the pointer.
     *
     * @var int
     */
    protected $position;

    /**
     * An array with permissions.
     *
     * @var array|PermissionForVisualization[]
     */
    protected $permissions;

    public function __construct(array $permissions = [])
    {
        $this->position = 0;
        $this->permissions = $this->filterArrayWithPermissions($permissions);
    }

    /**
     * Initializes an instance using an array with permissions.
     *
     * @param  array|array[] $array
     * @return self
     */
    public static function createFromArray(array $array): self
    {
        /** @var array|PermissionForVisualization[] $permissions */
        $permissions = array_map(function (array $arrayWithPermission) {
            return PermissionForVisualization::createFromArray($arrayWithPermission);;
        }, $array);

        return new self($permissions);
    }

    /**
     * Counts permissions and returns their number.
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->permissions);
    }

    /**
     * Rewinds the pointer.
     *
     * @return void
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * Returns the current permission.
     *
     * @return PermissionForVisualization
     */
    public function current(): PermissionForVisualization
    {
        return $this->permissions[$this->position];
    }

    /**
     * Returns the current key (position).
     *
     * @return int
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Moves the pointer to the next position.
     *
     * @return void
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * Checks whether the permission is valid.
     *
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->permissions[$this->position]);
    }

    /**
     * Pushes the given permission into the collection.
     *
     * @param  PermissionForVisualization $permission
     * @return void
     */
    public function push(PermissionForVisualization $permission): void
    {
        $this->permissions[] = $permission;
    }

    /**
     * Filters an array with permissions and returns an array only with permissions.
     *
     * @param  array $permissions
     * @return array|PermissionForVisualization[]
     */
    public function filterArrayWithPermissions(array $permissions): array
    {
        $permissions = array_filter($permissions, function ($permission) {
            return $permission instanceof PermissionForVisualization;
        });

        return array_values($permissions);
    }

    /**
     * Filters a collection with permissions by the given name.
     *
     * @param  string $name
     * @return PermissionForVisualizationCollection
     */
    public function filterByName(string $name): PermissionForVisualizationCollection
    {
        $permissions = array_filter(
            $this->permissions,
            function (PermissionForVisualization $permission) use ($name) {
                return $permission->getBlockName() === $name;
            }
        );

        return new PermissionForVisualizationCollection($permissions);
    }

    /**
     * Returns an array with permissions.
     *
     * @return array|PermissionForVisualization[]
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * Returns data of the instance in the form of an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return array_map(function (PermissionForVisualization $permission) {
            return $permission->toArray();
        }, $this->permissions);
    }
}
