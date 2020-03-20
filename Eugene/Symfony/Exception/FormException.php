<?php

namespace App\Exception;

use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;

/**
 * Class FormException.
 */
class FormException extends ApiException
{
    /**
     * @var FormInterface
     */
    private $form;

    /**
     * FormException constructor.
     *
     * @param FormInterface $form    Form with errors
     * @param string        $message Message
     */
    public function __construct(FormInterface $form, string $message = '')
    {
        parent::__construct([], $message);

        $this->form = $form;
        $this->errorData = $this->getFlatErrors();
    }

    /**
     * @return FormInterface
     */
    public function getForm(): FormInterface
    {
        return $this->form;
    }

    /**
     * @return array
     */
    public function getFormErrors(): array
    {
        return $this->collectFormErrors($this->form);
    }

    /**
     * @return array
     */
    public function getFlatErrors(): array
    {
        $formErrors = $this->getFormErrors();

        return $this->collectFlatErrors($formErrors);
    }

    /**
     * @param FormInterface $baseForm
     * @param array         $basePathArray
     * @param array         $parameters
     *
     * @return array
     */
    private function collectFormErrors(FormInterface $baseForm, $basePathArray = [], array $parameters = []): array
    {
        $errors = [];

        /** @var FormError $error */
        foreach ($baseForm->getErrors() as $error) {
            $errorItem = array_merge([
                'errorMessage' => $error->getMessage(),
            ], array_merge($error->getMessageParameters(), $parameters));

            if (!empty($basePathArray)) {
                $errorItem['path'] = implode('.', $basePathArray);
            }

            $errors['valueErrors'][] = $errorItem;
        }

        foreach ($baseForm->all() as $childForm) {
            $mergedParameters = array_merge($parameters, $childForm->getConfig()->getOption('invalid_message_parameters', []));
            $elementPathArray = array_merge($basePathArray, [$childForm->getName()]);

            $childFormErrors = $this->collectFormErrors($childForm, $elementPathArray, $mergedParameters);
            if (!empty($childFormErrors)) {
                $errors['childErrors'][$childForm->getName()] = $childFormErrors;
            }
        }

        return $errors;
    }

    /**
     * @param array $formErrors
     *
     * @return array
     */
    private function collectFlatErrors(array $formErrors): array
    {
        $flatErrorsArray = [];

        if (isset($formErrors['valueErrors'])) {
            foreach ($formErrors['valueErrors'] as $valueErrors) {
                $flatErrorsArray[] = $valueErrors;
            }
        }

        if (isset($formErrors['childErrors'])) {
            foreach ($formErrors['childErrors'] as $childName => $childErrors) {
                $childFlatErrors = $this->collectFlatErrors($childErrors);
                foreach ($childFlatErrors as $error) {
                    $flatErrorsArray[] = $error;
                }
            }
        }

        return $flatErrorsArray;
    }
}
