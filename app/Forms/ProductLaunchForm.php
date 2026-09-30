<?php

namespace App\Forms;

use Erag\InertiaForms\Fields\CheckboxGroup;
use Erag\InertiaForms\Fields\ColorPicker;
use Erag\InertiaForms\Fields\DatePicker;
use Erag\InertiaForms\Fields\Field;
use Erag\InertiaForms\Fields\Fieldset;
use Erag\InertiaForms\Fields\FileUpload;
use Erag\InertiaForms\Fields\Radio;
use Erag\InertiaForms\Fields\Submit;
use Erag\InertiaForms\Fields\Textarea;
use Erag\InertiaForms\Fields\TextInput;
use Erag\InertiaForms\Fields\Toggle;
use Erag\InertiaForms\Form;

/**
 * Launch a product: pricing tier, channels and a press kit when press is picked.
 */
class ProductLaunchForm extends Form
{
    /**
     * @return array<int, Field|Fieldset>
     */
    public function fields(): array
    {
        return [
            Fieldset::make('Product')->columns(2)->fields([
                TextInput::make('product_name')->required()->placeholder('Aurora headphones')->clearable(),
                TextInput::make('price')->number()->min(0)->step('0.01')->prefix('$')->required(),
                Radio::make('tier')->options(['free' => 'Free', 'pro' => 'Pro', 'enterprise' => 'Enterprise'])->buttons()->default('pro'),
                DatePicker::make('launch_date')->required()->minDate(now()->toDateString())->clearable(),
                Textarea::make('tagline')->rows(2)->maxLength(120)->showCharacterCount()->columnSpan(2),
            ]),
            Fieldset::make('Go to market')->columns(2)->fields([
                CheckboxGroup::make('channels')->options([
                    'email' => 'Email',
                    'blog' => 'Blog',
                    'social' => 'Social',
                    'press' => 'Press',
                ])->buttons()->required()->columnSpan(2),
                FileUpload::make('press_kit')->accept(['pdf', 'zip'])->maxSize(5120)
                    ->visibleWhen('channels', 'contains', 'press')->columnSpan(2),
                ColorPicker::make('brand_color')->swatches(['#4f46e5', '#0ea5e9', '#10b981', '#f59e0b', '#e11d48'])->default('#4f46e5'),
                Toggle::make('announce')->label('Announce on launch day')->onLabel('Yes')->offLabel('No')->default(true),
            ]),
            Submit::make('Schedule launch')->processingLabel('Scheduling…'),
        ];
    }
}
