<?php

namespace App\Forms;

use Erag\InertiaForms\Fields\Callout;
use Erag\InertiaForms\Fields\Field;
use Erag\InertiaForms\Fields\Fieldset;
use Erag\InertiaForms\Fields\OtpInput;
use Erag\InertiaForms\Fields\Radio;
use Erag\InertiaForms\Fields\Slug;
use Erag\InertiaForms\Fields\Submit;
use Erag\InertiaForms\Fields\TextInput;
use Erag\InertiaForms\Form;

/**
 * A three-step wizard: each fieldset is a step, checked on the server before moving on.
 */
class OnboardingWizardForm extends Form
{
    protected bool $wizard = true;

    /**
     * @return array<int, Field|Fieldset>
     */
    public function fields(): array
    {
        return [
            Fieldset::make('Account')->description('Create your login')->icon('user')->fields([
                TextInput::make('name')->required()->placeholder('Jane Example'),
                TextInput::make('email')->email()->required()->placeholder('jane@example.test'),
                TextInput::make('password')->password()->required()->minLength(8),
            ]),
            Fieldset::make('Workspace')->description('Tell us about your team')->icon('briefcase')->columns(2)->fields([
                TextInput::make('workspace')->label('Workspace name')->required()->placeholder('Acme Studio'),
                Slug::make('workspace_url')->label('Workspace URL')->from('workspace')->prefix('app.example.test/')->required(),
                Radio::make('team_size')->options(['1' => 'Just me', '2-10' => '2–10', '11-50' => '11–50', '50+' => '50+'])
                    ->buttons()->default('2-10')->columnSpan(2),
            ]),
            Fieldset::make('Verify')->description('Confirm your email')->icon('shield')->fields([
                Callout::make('Check your inbox', 'We sent a 6-digit code to your email. Use 123456 in this demo.')->info(),
                OtpInput::make('code')->label('Verification code')->length(6)->groupSize(3)->required()
                    ->rule('in:123456'),
            ]),
            Submit::make('Create account')->icon('check')->processingLabel('Creating…'),
        ];
    }

    public function messages(): array
    {
        return ['code.in' => 'That code is not right. Use 123456 in this demo.'];
    }
}
