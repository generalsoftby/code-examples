<?php

declare(strict_types=1);

namespace App\Annotations\ContactHistory;

use App\Annotations\MiddlewareAnnotationInterface;
use App\Service\ContactHistoryService;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Annotation
 * @Target({"METHOD"})
 */
final class HistoryOriginalUser implements MiddlewareAnnotationInterface
{
    /** @var string */
    public $argumentName;

    /**
     * {@inheritdoc}
     */
    public function handle(Request $request): void
    {
        $originalUser = $request->get($this->argumentName);

        if (!empty($originalUser)) {
            ContactHistoryService::setOriginalUser($originalUser);
        }
    }
}
