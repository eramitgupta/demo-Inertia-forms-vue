<?php

namespace App\Forms;

use Erag\InertiaForms\Fields\Combobox;
use Erag\InertiaForms\Fields\DatePicker;
use Erag\InertiaForms\Fields\Field;
use Erag\InertiaForms\Fields\Fieldset;
use Erag\InertiaForms\Fields\Radio;
use Erag\InertiaForms\Fields\Slider;
use Erag\InertiaForms\Fields\Submit;
use Erag\InertiaForms\Fields\TagsInput;
use Erag\InertiaForms\Fields\TextInput;
use Erag\InertiaForms\Fields\TimePicker;
use Erag\InertiaForms\Fields\Toggle;
use Erag\InertiaForms\Form;

/**
 * Plan an event session: room and meeting link depend on the format.
 */
class EventSessionForm extends Form
{
    /**
     * @return array<int, Field|Fieldset>
     */
    public function fields(): array
    {
        return [
            Fieldset::make('Session')->columns(2)->fields([
                TextInput::make('title')->required()->placeholder('Building forms with Inertia')->columnSpan(2),
                DatePicker::make('dates')->label('Session dates')->range()->required()->columnSpan(2),
                TimePicker::make('starts_at')->label('Start time')->minuteStep(15)->required()->clearable(),
                TimePicker::make('ends_at')->label('End time')->minuteStep(15)->clearable(),
            ]),
            Fieldset::make('Format')->columns(2)->fields([
                Radio::make('format')->options([
                    ['value' => 'in_person', 'label' => 'In person', 'description' => 'At the venue'],
                    ['value' => 'online', 'label' => 'Online', 'description' => 'Live stream only'],
                    ['value' => 'hybrid', 'label' => 'Hybrid', 'description' => 'Venue and stream'],
                ])->default('in_person')->columns(3)->columnSpan(2),
                Combobox::make('room')->options(['Main hall', 'Workshop A', 'Workshop B', 'Studio'])->required()
                    ->visibleWhen('format', 'in', ['in_person', 'hybrid'])->clearWhenHidden(),
                TextInput::make('meeting_link')->url()->required()->placeholder('https://meet.example.com/…')
                    ->visibleWhen('format', 'in', ['online', 'hybrid'])->clearWhenHidden(),
                Slider::make('capacity')->min(10)->max(300)->step(10)->default(50)->columnSpan(2),
                TagsInput::make('speakers')->placeholder('Add a speaker and press Enter')->reorderable()->columnSpan(2),
                Toggle::make('recorded')->label('Record this session')->onLabel('Yes')->offLabel('No'),
            ]),
            Submit::make('Save session')->processingLabel('Saving…'),
        ];
    }
}
