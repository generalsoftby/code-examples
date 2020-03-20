<?php

namespace App\Support;


use App\Models\User;
use App\Support\PublicId\Contract\PublicIdGenerator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

trait PublicId
{
    /**
     * Поле в таблице
     *
     * @var string
     */
    protected $publicId = 'public_id';

    /**
     * Длина ключа
     *
     * @var int
     */
    protected $publicIdLength = 9;

    /**
     * Инициализация реакции приложения на событие создания
     */
    public static function bootPublicId()
    {
        static::creating(function (Model $model) {
            $idGenerator = app()->make(PublicIdGenerator::class);
            $idGenerator->setModel($model);
            $model->{$model->getPublicIdKeyName()} = $idGenerator->generateUniquePublicId();
        });
    }

    /**
     * Получение поля, которое будет хранить сгенерированный ключ
     *
     * @return string
     */
    public function getPublicIdKeyName()
    {
        return $this->publicId;
    }

    /**
     * Алфавит генерации
     *
     * @return string
     */
    public function getPublicIdAlphabet()
    {
        return '0123456789';
    }

    /**
     * Длина ключа
     *
     * @return int
     */
    public function getPublicIdLength()
    {
        return $this->publicIdLength;
    }

    /**
     * Get the value of the model's route key.
     *
     * @return mixed
     */
    public function getRouteKey()
    {
        return $this->{$this->publicId};
    }

    /**
     * Retrieve the model for a bound value.
     *
     * @param mixed $value
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function resolveRouteBinding($value)
    {
        return $this->where($this->publicId, '=', $value)->first();
    }

}
