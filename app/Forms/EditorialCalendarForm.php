<?php

namespace App\Forms;

use Erag\InertiaForms\Fields\Block;
use Erag\InertiaForms\Fields\Blocks;
use Erag\InertiaForms\Fields\ColorPicker;
use Erag\InertiaForms\Fields\Combobox;
use Erag\InertiaForms\Fields\DatePicker;
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
 * Schedule an article: the publish time is only needed once it is scheduled.
 */
class EditorialCalendarForm extends Form
{
    /**
     * Demo authors. In an app this would be a query, e.g.
     * `User::where('name', 'like', "%{$search}%")->limit(20)->get()`.
     *
     * @var array<int, array{id: int, name: string, email: string}>
     */
    public const AUTHORS = [
        ['id' => 1, 'name' => 'Avery Stone', 'email' => 'avery@example.test'],
        ['id' => 2, 'name' => 'Rowan Example', 'email' => 'rowan@example.test'],
        ['id' => 3, 'name' => 'Morgan Vale', 'email' => 'morgan@example.test'],
        ['id' => 4, 'name' => 'Casey Example', 'email' => 'casey@example.test'],
        ['id' => 5, 'name' => 'Skyler Example', 'email' => 'skyler@example.test'],
        ['id' => 6, 'name' => 'Aisha Khan', 'email' => 'aisha@example.test'],
        ['id' => 7, 'name' => 'Ben Carter', 'email' => 'ben@example.test'],
        ['id' => 8, 'name' => 'Chen Wei', 'email' => 'chen@example.test'],
        ['id' => 9, 'name' => 'Diego Ruiz', 'email' => 'diego@example.test'],
        ['id' => 10, 'name' => 'Emma Novak', 'email' => 'emma@example.test'],
    ];

    /**
     * @return array<int, Field|Fieldset>
     */
    public function fields(): array
    {
        return [
            Fieldset::make('Article')->columns(2)->fields([
                TextInput::make('headline')->required()->maxLength(90)->placeholder('Ten tips for faster forms')->columnSpan(2),
                Combobox::make('author')->required()->clearable()->placeholder('Search authors…')
                    ->searchUsing(fn (string $search): array => $this->authorOptions(
                        fn (array $author): bool => $search === '' || str_contains(strtolower($author['name'].' '.$author['email']), strtolower($search)),
                    ))
                    ->selectedOptionsUsing(fn (array $ids): array => $this->authorOptions(
                        fn (array $author): bool => in_array((string) $author['id'], array_map('strval', $ids), true),
                    )),
                Radio::make('section')->options(['news' => 'News', 'guides' => 'Guides', 'opinion' => 'Opinion'])->buttons()->default('guides'),
                Textarea::make('summary')->rows(3)->autoResize()->maxLength(200)->showCharacterCount()->columnSpan(2),
            ]),
            Fieldset::make('Body')->fields([
                Blocks::make('body')->label('Body outline')->addActionLabel('Add content block')->maxItems(12)
                    ->blocks([
                        Block::make('section')->description('Heading with notes')->titleFrom('heading')->fields([
                            TextInput::make('heading')->required()->maxLength(90),
                            Textarea::make('summary')->rows(2)->autoResize(),
                        ]),
                        Block::make('quote')->description('Pull quote')->icon('Q')->columns(2)->fields([
                            Textarea::make('text')->label('Quote')->required()->rows(2)->columnSpan(2),
                            TextInput::make('author')->required(),
                            TextInput::make('source')->placeholder('Book, talk or link'),
                        ]),
                        Block::make('image')->description('Picture with a caption')->icon('I')->fields([
                            FileUpload::make('file')->image()->maxSize(3072),
                            TextInput::make('caption'),
                        ]),
                    ])
                    ->default([
                        ['type' => 'section', 'data' => ['heading' => 'Why forms belong in PHP']],
                        ['type' => 'quote', 'data' => ['text' => 'One class drives the page and the validation.', 'author' => 'Aisha Khan']],
                    ]),
            ]),
            Fieldset::make('Publishing')->columns(2)->fields([
                Combobox::make('status')->options([
                    ['value' => 'draft', 'label' => 'Draft', 'description' => 'Still being written'],
                    ['value' => 'review', 'label' => 'In review', 'description' => 'Waiting for an editor'],
                    ['value' => 'scheduled', 'label' => 'Scheduled', 'description' => 'Goes live at a set time'],
                ])->default('draft'),
                DatePicker::make('publish_at')->withTime()->required()->minDate(now()->toDateString())
                    ->visibleWhen('status', 'scheduled')->clearWhenHidden(),
                TagsInput::make('tags')->suggestions(['laravel', 'inertia', 'tutorial', 'release'])->reorderable()->maxTags(5)->columnSpan(2),
                FileUpload::make('cover_image')->image()->maxSize(3072),
                ColorPicker::make('accent')->label('Accent color')->swatches(['#4f46e5', '#0f766e', '#b45309', '#be123c'])->default('#0f766e'),
                Slider::make('word_target')->label('Word count target')->min(300)->max(3000)->step(100)->default(1200)->columnSpan(2),
                Toggle::make('featured')->label('Feature on the home page')->onLabel('Yes')->offLabel('No'),
            ]),
            Submit::make('Save article')->processingLabel('Saving…'),
        ];
    }

    /**
     * Authors matching the filter, as select options with the email as description.
     *
     * @param  callable(array{id: int, name: string, email: string}): bool  $filter
     * @return array<int, array{value: int, label: string, description: string}>
     */
    protected function authorOptions(callable $filter): array
    {
        return array_values(array_map(
            fn (array $author): array => ['value' => $author['id'], 'label' => $author['name'], 'description' => $author['email']],
            array_filter(self::AUTHORS, $filter),
        ));
    }
}
