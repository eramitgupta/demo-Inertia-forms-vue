<?php

namespace App\Forms;

use Erag\InertiaForms\Fields\Combobox;
use Erag\InertiaForms\Fields\DatePicker;
use Erag\InertiaForms\Fields\Field;
use Erag\InertiaForms\Fields\Fieldset;
use Erag\InertiaForms\Fields\Radio;
use Erag\InertiaForms\Fields\Submit;
use Erag\InertiaForms\Fields\TagsInput;
use Erag\InertiaForms\Fields\TextInput;
use Erag\InertiaForms\Form;

/**
 * Open a role: office location only matters when the role is not remote.
 */
class HiringPipelineForm extends Form
{
    /**
     * @return array<int, Field|Fieldset>
     */
    public function fields(): array
    {
        return [
            Fieldset::make('Role')->columns(2)->fields([
                TextInput::make('role_title')->required()->placeholder('Senior Laravel developer'),
                Combobox::make('department')->required()->options(['Engineering', 'Design', 'Product', 'Marketing', 'Support']),
                Radio::make('employment')->options(['full_time' => 'Full-time', 'part_time' => 'Part-time', 'contract' => 'Contract'])
                    ->buttons()->default('full_time')->columnSpan(2),
                TextInput::make('salary_min')->number()->min(0)->prefix('$')->suffix('/yr'),
                TextInput::make('salary_max')->number()->min(0)->prefix('$')->suffix('/yr'),
            ]),
            Fieldset::make('Location')->columns(2)->fields([
                Radio::make('workplace')->options([
                    ['value' => 'remote', 'label' => 'Remote', 'description' => 'Work from anywhere'],
                    ['value' => 'hybrid', 'label' => 'Hybrid', 'description' => 'Two days in the office'],
                    ['value' => 'onsite', 'label' => 'On-site', 'description' => 'Every day in the office'],
                ])->default('remote')->columns(3)->columnSpan(2),
                Combobox::make('office')->required()->searchable()->options(['Bengaluru', 'Berlin', 'London', 'New York', 'Singapore'])
                    ->visibleWhen('workplace', '!=', 'remote')->clearWhenHidden(),
                DatePicker::make('start_date')->minDate(now()->toDateString())->clearable(),
            ]),
            Fieldset::make('Pipeline')->fields([
                Combobox::make('skills')->multiple()->searchable()->clearable()
                    ->options(['PHP', 'Laravel', 'Inertia', 'React', 'Vue', 'SQL', 'Testing', 'AWS']),
                TagsInput::make('stages')->reorderable()->default(['Screening', 'Technical interview', 'Team interview', 'Offer'])
                    ->help('Drag the stages into order.'),
            ]),
            Submit::make('Open role')->processingLabel('Opening…'),
        ];
    }
}
