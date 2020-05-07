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
     * Generates random unique key
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
     * Set model from which data will be taken
     *
     * @return void
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Generates key with given length and alphabet
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
     * Unique check
     *
     * @param $unique
     * @return mixed
     */
    protected function checkUniquePublicId($unique)
    {
        return $this->model->newQuery()->where($this->model->getPublicIdKeyName(), '=', $unique)->exists();
    }
}
