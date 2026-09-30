<?php

namespace App\Forms;

use Erag\InertiaForms\Fields\CheckboxGroup;
use Erag\InertiaForms\Fields\Combobox;
use Erag\InertiaForms\Fields\DatePicker;
use Erag\InertiaForms\Fields\Field;
use Erag\InertiaForms\Fields\Fieldset;
use Erag\InertiaForms\Fields\FileUpload;
use Erag\InertiaForms\Fields\Radio;
use Erag\InertiaForms\Fields\Submit;
use Erag\InertiaForms\Fields\TextInput;
use Erag\InertiaForms\Form;

/**
 * Kick off a client project: business details only show up for business clients.
 */
class ProjectKickoffForm extends Form
{
    /**
     * @return array<int, Field|Fieldset>
     */
    public function fields(): array
    {
        return [
            Fieldset::make('Project')->columns(2)->fields([
                TextInput::make('project_name')->required()->maxLength(80)->placeholder('Acme redesign'),
                TextInput::make('contact_email')->email()->required(),
                Combobox::make('client_type')->options(['personal' => 'Personal', 'business' => 'Business'])->default('personal'),
                TextInput::make('company')->required()->visibleWhen('client_type', 'business')->clearWhenHidden(),
                TextInput::make('budget')->number()->min(100)->prefix('$'),
                DatePicker::make('deadline')->minDate(now()->toDateString())->clearable(),
            ]),
            Fieldset::make('Scope')->fields([
                Radio::make('priority')->options([
                    ['value' => 'low', 'label' => 'Low', 'description' => 'Whenever there is time.'],
                    ['value' => 'high', 'label' => 'High', 'description' => 'Needs attention this week.'],
                ])->default('low')->columns(2),
                Combobox::make('stack')->label('Tech stack')->multiple()->searchable()->clearable()
                    ->options(['Laravel', 'Inertia', 'React', 'Vue', 'Svelte', 'Tailwind CSS']),
                CheckboxGroup::make('services')->options(['design' => 'Design', 'build' => 'Build', 'hosting' => 'Hosting'])->buttons(),
                FileUpload::make('brief')->accept(['pdf', 'docx'])->maxSize(2048)->help('Optional project brief.'),
            ]),
            Submit::make('Start project')->processingLabel('Starting…'),
        ];
    }
}
