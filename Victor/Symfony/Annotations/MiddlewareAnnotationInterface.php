<?php

declare(strict_types=1);

namespace App\Annotations;

use Symfony\Component\HttpFoundation\Request;

interface MiddlewareAnnotationInterface
{
    /**
     * Method containing annotation logic.
     *
     * @param Request $request
     */
    public function handle(Request $request): void;
}
