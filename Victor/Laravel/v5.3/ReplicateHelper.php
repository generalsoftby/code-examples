<?php

namespace App\Utils;

use Illuminate\Database\Eloquent\Model;

class ReplicateHelper
{
    /**
     * @param Model $original
     * @param Model $replica
     * @param array $relations
     *
     * @return array
     */
    public function replicateHasManyRelations(Model $original, Model $replica, array $relations)
    {
        $replicationsMap = [];

        foreach ($relations as $relationNameMixed => $entriesMixed) {
            if (is_string($entriesMixed)) {
                $relationName = $entriesMixed;
                $entries = $original->{$relationName}()->get();
            } else {
                $relationName = $relationNameMixed;
                $entries = $entriesMixed;
            }

            $replicationIdMap = [];

            $relationForeignKey = $replica->{$relationName}()->getForeignKeyName();
            $relationLocalKey = $replica->{$relationName}()->getParentKey();

            /** @var Model $entry */
            foreach ($entries as $entry) {
                $e = $entry->replicate(['id']);
                $e->{$relationForeignKey} = $relationLocalKey;
                $e->save();

                $replicationIdMap[$entry->id] = $e->id;
            }

            $replicationsMap[$relationName] = $replicationIdMap;
        }

        return $replicationsMap;
    }

    /**
     * @param Model $original
     * @param Model $replica
     * @param array $relations
     *
     * @return Model
     */
    public function replicateManyToManyRelations(Model $original, Model $replica, array $relations)
    {
        foreach ($relations as $relationNameMixed => $entriesMixed) {
            if (is_string($entriesMixed)) {
                $relationName = $entriesMixed;
                $entries = $original->{$relationName}()->get();
            } else {
                $relationName = $relationNameMixed;
                $entries = $entriesMixed;
            }

            foreach ($entries as $entry) {
                $replica->{$relationName}()->attach($entry->id);
            }
        }

        return $replica;
    }
}
