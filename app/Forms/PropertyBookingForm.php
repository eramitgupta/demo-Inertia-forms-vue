<?php

namespace App\Forms;

use Erag\InertiaForms\Fields\CheckboxGroup;
use Erag\InertiaForms\Fields\Combobox;
use Erag\InertiaForms\Fields\DatePicker;
use Erag\InertiaForms\Fields\Field;
use Erag\InertiaForms\Fields\Fieldset;
use Erag\InertiaForms\Fields\Radio;
use Erag\InertiaForms\Fields\Slider;
use Erag\InertiaForms\Fields\Submit;
use Erag\InertiaForms\Fields\Textarea;
use Erag\InertiaForms\Fields\TextInput;
use Erag\InertiaForms\Fields\TimePicker;
use Erag\InertiaForms\Fields\Toggle;
use Erag\InertiaForms\Form;

/**
 * Book a stay: pet details show up when pets are coming along.
 */
class PropertyBookingForm extends Form
{
    /**
     * @return array<int, Field|Fieldset>
     */
    public function fields(): array
    {
        return [
            Fieldset::make('Stay')->columns(2)->fields([
                Combobox::make('property')->required()->options([
                    ['value' => 'lake_house', 'label' => 'Lake house', 'description' => '3 bedrooms, private dock'],
                    ['value' => 'city_loft', 'label' => 'City loft', 'description' => '1 bedroom, walk to everything'],
                    ['value' => 'forest_cabin', 'label' => 'Forest cabin', 'description' => '2 bedrooms, wood stove'],
                ])->columnSpan(2),
                DatePicker::make('stay')->label('Check-in and check-out')->range()->required()->minDate(now()->toDateString())->columnSpan(2),
                TimePicker::make('arrival')->label('Arrival time')->minuteStep(30)->minTime('14:00')->maxTime('22:00')->clearable(),
                Slider::make('guests')->min(1)->max(8)->default(2),
            ]),
            Fieldset::make('Extras')->columns(2)->fields([
                Radio::make('bed_setup')->options(['double' => 'Double bed', 'twin' => 'Twin beds'])->buttons()->default('double'),
                CheckboxGroup::make('extras')->options([
                    'breakfast' => 'Breakfast',
                    'pickup' => 'Airport pickup',
                    'late_checkout' => 'Late checkout',
                ])->buttons(),
                Toggle::make('pets')->label('Travelling with pets')->onLabel('Yes')->offLabel('No'),
                TextInput::make('pet_details')->placeholder('One small dog')->required()
                    ->visibleWhen('pets', 'truthy')->clearWhenHidden(),
                Textarea::make('requests')->label('Special requests')->rows(3)->maxLength(300)->showCharacterCount()->columnSpan(2),
            ]),
            Submit::make('Request booking')->processingLabel('Requesting…'),
        ];
    }
}
