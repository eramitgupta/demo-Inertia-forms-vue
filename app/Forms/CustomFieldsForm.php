<?php

namespace App\Forms;

use App\Forms\Fields\CodeInput;
use App\Forms\Fields\QuantityStepper;
use App\Forms\Fields\Rating;
use Erag\InertiaForms\Fields\Field;
use Erag\InertiaForms\Fields\Fieldset;
use Erag\InertiaForms\Fields\Radio;
use Erag\InertiaForms\Fields\Submit;
use Erag\InertiaForms\Fields\Textarea;
use Erag\InertiaForms\Fields\TextInput;
use Erag\InertiaForms\Form;

/**
 * Three custom fields (Rating, CodeInput, QuantityStepper) mixed with built-in ones.
 */
class CustomFieldsForm extends Form
{
    /**
     * @return array<int, Field|Fieldset>
     */
    public function fields(): array
    {
        return [
            Fieldset::make('Order')->description('QuantityStepper is a custom field with min, max and a unit.')->columns(2)->fields([
                Radio::make('ticket')->options([
                    ['value' => 'standard', 'label' => 'Standard', 'description' => '$49 per ticket'],
                    ['value' => 'vip', 'label' => 'VIP', 'description' => '$129, front rows and lounge'],
                ])->default('standard')->columns(2)->columnSpan(2),
                QuantityStepper::make('tickets')->min(1)->max(10)->unit('tickets')->default(2)->required(),
                QuantityStepper::make('parking')->label('Parking spots')->min(0)->max(4)->unit('spots'),
            ]),
            Fieldset::make('Verify')->description('CodeInput is a custom field: one box per digit, with paste support.')->fields([
                TextInput::make('email')->email()->required()->placeholder('jane@example.com'),
                CodeInput::make('code')->label('Verification code')->length(6)->required()
                    ->help('Use 123456 in this demo.'),
            ]),
            Fieldset::make('Feedback')->description('Rating is a custom field with keyboard support.')->fields([
                Rating::make('rating')->label('How was the booking experience?')->stars(5)->required(),
                Textarea::make('comment')->rows(3)->maxLength(200)->showCharacterCount()
                    ->visibleWhen('rating', '<=', 3)->help('Shown when the rating is 3 stars or less.'),
            ]),
            Submit::make('Book tickets')->processingLabel('Booking…'),
        ];
    }
}
