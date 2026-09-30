<?php

namespace App\Forms;

use Erag\InertiaForms\Fields\Checkbox;
use Erag\InertiaForms\Fields\CheckboxGroup;
use Erag\InertiaForms\Fields\Combobox;
use Erag\InertiaForms\Fields\DatePicker;
use Erag\InertiaForms\Fields\Field;
use Erag\InertiaForms\Fields\Fieldset;
use Erag\InertiaForms\Fields\Slider;
use Erag\InertiaForms\Fields\Submit;
use Erag\InertiaForms\Fields\TagsInput;
use Erag\InertiaForms\Fields\TextInput;
use Erag\InertiaForms\Fields\Toggle;
use Erag\InertiaForms\Form;

/**
 * Patient intake: insurance details appear only when the patient is insured.
 */
class ClinicIntakeForm extends Form
{
    /**
     * @return array<int, Field|Fieldset>
     */
    public function fields(): array
    {
        return [
            Fieldset::make('Patient')->columns(2)->fields([
                TextInput::make('patient_name')->required()->placeholder('Jane Doe'),
                DatePicker::make('birthday')->label('Date of birth')->required()->maxDate(today()->toDateString()),
                TextInput::make('phone')->tel()->required(),
                Combobox::make('reason')->label('Reason for visit')->required()->options([
                    ['value' => 'checkup', 'label' => 'General check-up', 'description' => 'Routine visit'],
                    ['value' => 'follow_up', 'label' => 'Follow-up', 'description' => 'After a previous visit'],
                    ['value' => 'new_issue', 'label' => 'New problem', 'description' => 'Something new to look at'],
                ]),
            ]),
            Fieldset::make('Symptoms')->columns(2)->fields([
                CheckboxGroup::make('symptoms')->options([
                    'fever' => 'Fever',
                    'cough' => 'Cough',
                    'headache' => 'Headache',
                    'fatigue' => 'Fatigue',
                    'nausea' => 'Nausea',
                ])->inline()->columnSpan(2),
                Slider::make('pain_level')->min(0)->max(10)->default(0)->columnSpan(2),
                TagsInput::make('allergies')->suggestions(['penicillin', 'peanuts', 'latex', 'pollen'])->columnSpan(2),
                DatePicker::make('appointment_at')->label('Preferred appointment')->withTime()->minDate(now()->toDateString())->clearable(),
                Toggle::make('insured')->label('I have insurance')->onLabel('Yes')->offLabel('No'),
                TextInput::make('insurance_number')->required()->visibleWhen('insured', 'truthy')->clearWhenHidden()->columnSpan(2),
            ]),
            Checkbox::make('consent')->label('I consent to the clinic storing this information')->rule('accepted'),
            Submit::make('Send intake form')->processingLabel('Sending…'),
        ];
    }
}
