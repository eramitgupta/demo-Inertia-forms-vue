<?php

namespace App\Forms;

use Erag\InertiaForms\Fields\Combobox;
use Erag\InertiaForms\Fields\Field;
use Erag\InertiaForms\Fields\Fieldset;
use Erag\InertiaForms\Fields\FileUpload;
use Erag\InertiaForms\Fields\Radio;
use Erag\InertiaForms\Fields\Slider;
use Erag\InertiaForms\Fields\Submit;
use Erag\InertiaForms\Fields\TagsInput;
use Erag\InertiaForms\Fields\Textarea;
use Erag\InertiaForms\Fields\TextInput;
use Erag\InertiaForms\Fields\Toggle;
use Erag\InertiaForms\Form;

/**
 * Triage a support ticket: critical issues ask for an on-call phone number.
 */
class SupportTriageForm extends Form
{
    /**
     * @return array<int, Field|Fieldset>
     */
    public function fields(): array
    {
        return [
            Fieldset::make('Ticket')->columns(2)->fields([
                TextInput::make('requester_email')->email()->required()->placeholder('customer@example.com'),
                Combobox::make('area')->label('Product area')->required()->clearable()->options([
                    ['value' => 'billing', 'label' => 'Billing', 'description' => 'Invoices, cards and refunds'],
                    ['value' => 'auth', 'label' => 'Login & access', 'description' => 'Passwords, SSO and 2FA'],
                    ['value' => 'api', 'label' => 'API', 'description' => 'Tokens, limits and webhooks'],
                    ['value' => 'other', 'label' => 'Something else', 'description' => 'Anything not listed'],
                ]),
                Radio::make('severity')->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'])
                    ->buttons()->default('medium')->columnSpan(2),
                TextInput::make('on_call_phone')->tel()->required()->placeholder('+91 98765 43210')
                    ->visibleWhen('severity', 'critical')->clearWhenHidden()->columnSpan(2),
                Slider::make('affected_users')->min(1)->max(500)->step(1)->default(10)->columnSpan(2),
            ]),
            Fieldset::make('Details')->fields([
                Textarea::make('steps')->label('Steps to reproduce')->required()->rows(4)->autoResize(),
                TagsInput::make('labels')->suggestions(['bug', 'regression', 'billing', 'login', 'performance'])->maxTags(5),
                FileUpload::make('screenshots')->image()->multiple()->maxFiles(3)->maxSize(2048),
                Toggle::make('escalate')->label('Escalate to engineering')->onLabel('Yes')->offLabel('No'),
            ]),
            Submit::make('Create ticket')->processingLabel('Creating…'),
        ];
    }
}
