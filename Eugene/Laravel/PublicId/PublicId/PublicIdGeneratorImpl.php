<?php

namespace App\Support\PublicId;

use App\Support\PublicId\Contract\PublicIdGenerator;
use Illuminate\Database\Eloquent\Model;

class PublicIdGeneratorImpl implements PublicIdGenerator
{
    /**
     * @var Model
     */
    protected $model;

    /**
     * Генерирует случайный ключ с проверкой уникальности
     *
     * @return string
     */
    public function generateUniquePublicId()
    {
        $alphabet = $this->model->getPublicIdAlphabet();
        $length = $this->model->getPublicIdLength();

        $unique = '';
        do {
            $unique = $this->generatePublicId($length, $alphabet);
        } while ($this->checkUniquePublicId($unique));

        return $unique;
    }

    /**
     * Устанавливает модель из которой брать данные для инициализации
     *
     * @return void
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Генерирует ключ в зависимости от длины и алфавита
     *
     * @param $length
     * @param $alphabet
     * @return string
     */
    protected function generatePublicId($length, $alphabet)
    {
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $key = rand(0, strlen($alphabet) - 1);
            $value = (substr($alphabet, $key, 1));
            $str .= $value;
        }
        return $str;
    }

    /**
     * Проверка на уникальность ключа
     *
     * @param $unique
     * @return mixed
     */
    protected function checkUniquePublicId($unique)
    {
        return $this->model->newQuery()->where($this->model->getPublicIdKeyName(), '=', $unique)->exists();
    }
}
