<?php

namespace App\Forms;

use Erag\InertiaForms\Fields\Callout;
use Erag\InertiaForms\Fields\Checkbox;
use Erag\InertiaForms\Fields\CheckboxGroup;
use Erag\InertiaForms\Fields\ColorPicker;
use Erag\InertiaForms\Fields\Combobox;
use Erag\InertiaForms\Fields\DatePicker;
use Erag\InertiaForms\Fields\Field;
use Erag\InertiaForms\Fields\Fieldset;
use Erag\InertiaForms\Fields\FileUpload;
use Erag\InertiaForms\Fields\Hidden;
use Erag\InertiaForms\Fields\KeyValue;
use Erag\InertiaForms\Fields\Link;
use Erag\InertiaForms\Fields\OtpInput;
use Erag\InertiaForms\Fields\Radio;
use Erag\InertiaForms\Fields\Repeater;
use Erag\InertiaForms\Fields\Slider;
use Erag\InertiaForms\Fields\Slug;
use Erag\InertiaForms\Fields\Submit;
use Erag\InertiaForms\Fields\TagsInput;
use Erag\InertiaForms\Fields\Textarea;
use Erag\InertiaForms\Fields\TextInput;
use Erag\InertiaForms\Fields\TimePicker;
use Erag\InertiaForms\Fields\Toggle;
use Erag\InertiaForms\Form;

/**
 * Every field type and its main options on one page.
 */
class AllFieldsForm extends Form
{
    /**
     * @return array<int, Field|Fieldset>
     */
    public function fields(): array
    {
        return [
            Fieldset::make('Text inputs')->description('TextInput types, prefix / suffix and clear button.')->columns(2)->fields([
                TextInput::make('full_name')->required()->placeholder('Jane Doe')->clearable(),
                TextInput::make('email')->email()->placeholder('jane@example.com'),
                TextInput::make('password')->password()->minLength(8),
                TextInput::make('phone')->tel()->placeholder('+91 98765 43210'),
                TextInput::make('website')->url()->prefix('https://'),
                TextInput::make('price')->number()->min(0)->step('0.01')->prefix('$')->suffix('USD'),
                Textarea::make('about')->rows(3)->autoResize()->maxLength(200)->showCharacterCount()->columnSpan(2),
            ]),
            Fieldset::make('Selects and lists')->description('Custom dropdowns with descriptions, search and chips, key-value rows and tags.')->columns(2)->fields([
                Combobox::make('plan')->clearable()->options([
                    ['value' => 'free', 'label' => 'Free', 'description' => 'For side projects'],
                    ['value' => 'pro', 'label' => 'Pro', 'description' => 'For growing teams'],
                    ['value' => 'enterprise', 'label' => 'Enterprise', 'description' => 'SSO, audit log and support'],
                ]),
                Combobox::make('country')->searchable()->clearable()->options([
                    'IN' => 'India',
                    'US' => 'United States',
                    'GB' => 'United Kingdom',
                    'DE' => 'Germany',
                    'JP' => 'Japan',
                ]),
                Combobox::make('frameworks')->multiple()->searchable()->clearable()
                    ->options(['Laravel', 'Inertia', 'Vue', 'React', 'Svelte', 'Tailwind CSS'])->columnSpan(2),
                KeyValue::make('headers')->label('Request headers')->keyLabel('Header')->keyPlaceholder('Accept')
                    ->valuePlaceholder('application/json')->addActionLabel('Add header')->columnSpan(2),
                TagsInput::make('keywords')->suggestions(['laravel', 'inertia', 'forms', 'tailwind'])
                    ->maxTags(6)->reorderable()->columnSpan(2),
            ]),
            Fieldset::make('Choices')->description('Radio cards, segmented buttons, checkbox pills and toggles.')->columns(2)->fields([
                Radio::make('shipping')->options([
                    ['value' => 'standard', 'label' => 'Standard', 'description' => '3 to 5 business days'],
                    ['value' => 'express', 'label' => 'Express', 'description' => 'Next business day'],
                ])->default('standard')->columns(2)->columnSpan(2),
                Radio::make('size')->options(['S', 'M', 'L', 'XL'])->buttons()->default('M'),
                Radio::make('contact_by')->options(['email' => 'Email', 'phone' => 'Phone', 'none' => 'None'])->inline(),
                CheckboxGroup::make('channels')->options(['email' => 'Email', 'sms' => 'SMS', 'push' => 'Push'])->buttons(),
                CheckboxGroup::make('days')->options(['mon' => 'Mon', 'tue' => 'Tue', 'wed' => 'Wed', 'thu' => 'Thu', 'fri' => 'Fri'])->inline(),
                Toggle::make('notifications')->onLabel('On')->offLabel('Off')->default(true),
                Checkbox::make('newsletter')->label('Subscribe to the newsletter'),
            ]),
            Fieldset::make('Dates and times')->description('Calendar, range, date with time, and time columns.')->columns(2)->fields([
                DatePicker::make('start_date')->minDate(now()->toDateString())->clearable(),
                DatePicker::make('meeting_at')->label('Meeting')->withTime()->clearable(),
                DatePicker::make('stay')->range()->firstDayOfWeek(1)->columnSpan(2),
                TimePicker::make('opens_at')->minuteStep(15)->clearable(),
                TimePicker::make('duration')->withSeconds()->clearable(),
            ]),
            Fieldset::make('Colors, sliders and files')->columns(2)->fields([
                ColorPicker::make('brand_color')->swatches(['#4f46e5', '#0ea5e9', '#10b981', '#f59e0b', '#e11d48', '#9333ea'])
                    ->default('#7c3aed')->clearable(),
                Slider::make('volume')->min(0)->max(100)->step(5)->suffix('%')->default(40),
                FileUpload::make('photo')->image()->maxSize(2048),
                FileUpload::make('attachments')->multiple()->maxFiles(3)->accept(['pdf', 'docx'])->maxSize(4096),
            ]),
            Fieldset::make('Links, slugs and display')->description('Slugs, codes, links and repeating rows.')->columns(2)->fields([
                TextInput::make('page_title')->placeholder('Hello world'),
                Slug::make('page_slug')->from('page_title')->prefix('example.test/'),
                Link::make('homepage')->placeholder('example.test'),
                OtpInput::make('pin')->label('PIN')->length(4)->password(),
                Repeater::make('contacts')->itemLabel('Contact')->titleFrom('name')->columns(2)->columnSpan(2)->fields([
                    TextInput::make('name')->required(),
                    TextInput::make('email')->email(),
                ]),
                Callout::make('Heads up', 'Display helpers like this one have no value and are not submitted.')->info(),
            ]),
            Checkbox::make('terms')->label('I agree to the terms')->rule('accepted'),
            Hidden::make('source')->default('all-fields-demo'),
            Submit::make('Submit all fields')->processingLabel('Submitting…'),
        ];
    }
}
