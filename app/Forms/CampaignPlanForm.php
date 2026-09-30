<?php

namespace App\Forms;

use Erag\InertiaForms\Fields\CheckboxGroup;
use Erag\InertiaForms\Fields\ColorPicker;
use Erag\InertiaForms\Fields\Combobox;
use Erag\InertiaForms\Fields\DatePicker;
use Erag\InertiaForms\Fields\Field;
use Erag\InertiaForms\Fields\Fieldset;
use Erag\InertiaForms\Fields\Radio;
use Erag\InertiaForms\Fields\Slider;
use Erag\InertiaForms\Fields\Submit;
use Erag\InertiaForms\Fields\TagsInput;
use Erag\InertiaForms\Fields\TextInput;
use Erag\InertiaForms\Form;

/**
 * Plan a marketing campaign: audiences, flight dates, channels and UTM tags.
 */
class CampaignPlanForm extends Form
{
    /**
     * @return array<int, Field|Fieldset>
     */
    public function fields(): array
    {
        return [
            Fieldset::make('Campaign')->columns(2)->fields([
                TextInput::make('campaign_name')->required()->placeholder('Autumn sale')->clearable(),
                Radio::make('goal')->options(['awareness' => 'Awareness', 'leads' => 'Leads', 'sales' => 'Sales'])->buttons()->default('leads'),
                Combobox::make('audiences')->multiple()->searchable()->clearable()->required()
                    ->options(['New visitors', 'Returning customers', 'Newsletter subscribers', 'Trial users', 'Churned users'])->columnSpan(2),
                DatePicker::make('flight')->label('Flight dates')->range()->required()->columnSpan(2),
            ]),
            Fieldset::make('Budget')->columns(2)->fields([
                TextInput::make('budget')->number()->min(0)->prefix('$')->suffix('USD')->required(),
                Slider::make('daily_cap')->label('Daily cap')->min(0)->max(100)->step(5)->suffix('%')->default(25),
                CheckboxGroup::make('channels')->options([
                    'search' => 'Search',
                    'social' => 'Social',
                    'display' => 'Display',
                    'email' => 'Email',
                    'video' => 'Video',
                ])->buttons()->columnSpan(2),
                TagsInput::make('utm_tags')->label('UTM tags')->suggestions(['autumn', 'sale', 'retargeting', 'brand'])
                    ->maxTags(6)->columnSpan(2),
                ColorPicker::make('creative_color')->swatches(['#e11d48', '#f59e0b', '#10b981', '#0ea5e9', '#7c3aed'])->default('#e11d48'),
            ]),
            Submit::make('Save plan')->processingLabel('Saving…'),
        ];
    }
}
