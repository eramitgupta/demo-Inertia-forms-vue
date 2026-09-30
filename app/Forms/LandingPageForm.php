<?php

namespace App\Forms;

use Erag\InertiaForms\Fields\Block;
use Erag\InertiaForms\Fields\Blocks;
use Erag\InertiaForms\Fields\Callout;
use Erag\InertiaForms\Fields\Field;
use Erag\InertiaForms\Fields\Fieldset;
use Erag\InertiaForms\Fields\Heading;
use Erag\InertiaForms\Fields\Link;
use Erag\InertiaForms\Fields\Repeater;
use Erag\InertiaForms\Fields\Separator;
use Erag\InertiaForms\Fields\Slug;
use Erag\InertiaForms\Fields\Submit;
use Erag\InertiaForms\Fields\Text;
use Erag\InertiaForms\Fields\Textarea;
use Erag\InertiaForms\Fields\TextInput;
use Erag\InertiaForms\Form;

/**
 * A landing page editor: blocks, a repeater, links, display helpers
 * and two submit actions (save draft / publish).
 */
class LandingPageForm extends Form
{
    /**
     * @return array<int, Field|Fieldset>
     */
    public function fields(): array
    {
        return [
            Fieldset::make('Page')->columns(2)->fields([
                Heading::make('Page details')->level(3),
                Text::make('The URL follows the title until you change it.'),
                TextInput::make('title')->required()->placeholder('Launch faster with Inertia Forms'),
                Slug::make('slug')->from('title')->prefix('example.test/')->required(),
                Link::make('cta')->label('Call to action')->withLabel(placeholder: 'Get started')->withTarget()
                    ->requireScheme()->allowedSchemes('https')->columnSpan(2),
                Textarea::make('intro')->rows(4)->autoResize()->maxLength(600)->showCharacterCount()->columnSpan(2),
            ]),
            Fieldset::make('Content')->fields([
                Blocks::make('sections')->label('Sections')->addActionLabel('Add section')->blocks([
                    Block::make('hero')->description('Big headline with a summary')->titleFrom('headline')->fields([
                        TextInput::make('headline')->required(),
                        Textarea::make('summary')->rows(3),
                    ]),
                    Block::make('quote')->description('Customer quote')->icon('Q')->columns(2)->fields([
                        Textarea::make('quote')->required()->columnSpan(2),
                        TextInput::make('author')->required(),
                        TextInput::make('company'),
                    ]),
                ])->default([['type' => 'hero', 'data' => ['headline' => 'Launch faster', 'summary' => 'Build forms in PHP and render them in your frontend.']]]),
                Separator::make()->spacing('sm'),
                Repeater::make('faq')->label('FAQ')->itemLabel('Question')->titleFrom('question')->maxItems(8)->fields([
                    TextInput::make('question')->required(),
                    Textarea::make('answer')->rows(2)->required(),
                ])->default([['question' => 'Do I need a frontend library?', 'answer' => 'No. Every field is built in with Tailwind CSS.']]),
                Callout::make('Publishing is final', 'Published pages are visible to everyone right away.')->warning(),
            ]),
            Submit::make('Save draft')->secondary()->intent('draft'),
            Submit::make('Publish')->icon('send', 'right')->intent('publish')->processingLabel('Publishing…'),
        ];
    }
}
