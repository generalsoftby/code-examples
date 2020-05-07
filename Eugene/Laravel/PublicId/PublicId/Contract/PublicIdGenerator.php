<?php

namespace App\Support\PublicId\Contract;

use Illuminate\Database\Eloquent\Model;

interface PublicIdGenerator
{
    /**
     * Generates random unique key
     *
     * @return string
     */
    public function generateUniquePublicId();


    /**
     * Set model from which data will be taken
     *
     * @return void
     */
    public function setModel(Model $model);
}
