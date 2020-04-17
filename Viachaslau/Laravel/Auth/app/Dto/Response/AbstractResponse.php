<?php

namespace App\Dto\Response;

use App\Services\ObjectToArray;
use JsonSerializable;

abstract class AbstractResponse implements JsonSerializable
{
    /**
     * @inheritDoc
     */
    public function jsonSerialize(): array
    {
        /** @var ObjectToArray $converter */
        $converter = app(ObjectToArray::class);

        return $converter->toArray($this);
    }
}
