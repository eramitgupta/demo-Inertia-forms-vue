<?php

namespace App\Forms;

use Erag\InertiaForms\Fields\Checkbox;
use Erag\InertiaForms\Fields\Combobox;
use Erag\InertiaForms\Fields\Field;
use Erag\InertiaForms\Fields\Fieldset;
use Erag\InertiaForms\Fields\KeyValue;
use Erag\InertiaForms\Fields\Radio;
use Erag\InertiaForms\Fields\Slider;
use Erag\InertiaForms\Fields\Submit;
use Erag\InertiaForms\Fields\TextInput;
use Erag\InertiaForms\Fields\Toggle;
use Erag\InertiaForms\Form;

/**
 * Choose a plan: EU countries ask for a VAT number.
 */
class SubscriptionBillingForm extends Form
{
    /**
     * @return array<int, Field|Fieldset>
     */
    public function fields(): array
    {
        return [
            Fieldset::make('Plan')->columns(2)->fields([
                Radio::make('plan')->options([
                    ['value' => 'starter', 'label' => 'Starter', 'description' => '$9 per seat, up to 5 seats'],
                    ['value' => 'growth', 'label' => 'Growth', 'description' => '$19 per seat, priority support'],
                    ['value' => 'scale', 'label' => 'Scale', 'description' => '$39 per seat, SSO and audit log'],
                ])->default('growth')->columns(3)->columnSpan(2),
                Radio::make('cycle')->label('Billing cycle')->options(['monthly' => 'Monthly', 'yearly' => 'Yearly'])
                    ->buttons()->default('yearly')->help('Yearly billing gets two months free.'),
                Slider::make('seats')->min(1)->max(100)->default(5),
                TextInput::make('coupon')->placeholder('LAUNCH20')->clearable(),
            ]),
            Fieldset::make('Billing details')->columns(2)->fields([
                TextInput::make('billing_email')->email()->required(),
                Combobox::make('country')->searchable()->required()->options([
                    'IN' => 'India',
                    'US' => 'United States',
                    'GB' => 'United Kingdom',
                    'DE' => 'Germany',
                    'FR' => 'France',
                    'NL' => 'Netherlands',
                ]),
                TextInput::make('vat_number')->label('VAT number')->required()->placeholder('DE123456789')
                    ->visibleWhen('country', 'in', ['DE', 'FR', 'NL'])->clearWhenHidden()->columnSpan(2),
                KeyValue::make('invoice_metadata')->keyPlaceholder('region')->valuePlaceholder('EMEA')
                    ->addActionLabel('Add metadata')->maxItems(8)->default(['region' => 'EMEA', 'cost_center' => 'OPS-204'])
                    ->help('Shown on every invoice.')->columnSpan(2),
                Toggle::make('auto_renew')->onLabel('On')->offLabel('Off')->default(true),
                Checkbox::make('terms')->label('I agree to the subscription terms')->rule('accepted'),
            ]),
            Submit::make('Subscribe')->processingLabel('Subscribing…'),
        ];
    }
}
