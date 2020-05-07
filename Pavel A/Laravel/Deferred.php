<?php

namespace App\Contracts;

use App\Models;

interface Deferred {

    public function add(Models\Model $item);

    public function remove(Models\Model $item);

    public function getModels();

    public function getCount();
}
