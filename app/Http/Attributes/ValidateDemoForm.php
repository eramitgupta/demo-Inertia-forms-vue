<?php

namespace App\Http\Attributes;

use App\Http\Controllers\FormsDemoController;
use Attribute;
use Erag\InertiaForms\Form;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Container\ContextualAttribute;

/**
 * Resolve the form of the `{demo}` route parameter and validate the current
 * request with it, like the package's `#[Validate]` does for one form class.
 *
 *     public function store(#[ValidateDemoForm] Form $form)
 */
#[Attribute(Attribute::TARGET_PARAMETER)]
final class ValidateDemoForm implements ContextualAttribute
{
    public function resolve(self $attribute, Container $container): Form
    {
        $request = $container->make('request');

        /** @var Form $form */
        $form = $container->make(FormsDemoController::formClass((string) $request->route('demo')));
        $form->validate($request);

        return $form;
    }
}
