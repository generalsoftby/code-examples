<?php

namespace App\Support\PublicId\Contract;

use Illuminate\Database\Eloquent\Model;

interface PublicIdGenerator
{
    /**
     * Генерирует случайный ключ с проверкой уникальности
     *
     * @return string
     */
    public function generateUniquePublicId();


    /**
     * Устанавливает модель из которой брать данные для инициализации
     *
     * @return void
     */
    public function setModel(Model $model);
}
