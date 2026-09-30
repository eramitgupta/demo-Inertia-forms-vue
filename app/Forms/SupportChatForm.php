<?php

namespace App\Forms;

use Erag\InertiaForms\Fields\Composer;
use Erag\InertiaForms\Fields\Field;
use Erag\InertiaForms\Fields\Fieldset;
use Erag\InertiaForms\Fields\Text;
use Erag\InertiaForms\Form;

/**
 * A chat-style reply box: Enter sends, Shift+Enter adds a line, files can be attached.
 */
class SupportChatForm extends Form
{
    protected bool $resetOnSuccess = true;

    /**
     * @return array<int, Field|Fieldset>
     */
    public function fields(): array
    {
        return [
            Text::make('Reply to the customer. Attach screenshots or invoices if they help.'),
            Composer::make('reply')->label('First reply')->required()->placeholder('Write a reply…')
                ->accept(['png', 'jpg', 'pdf'])->maxFiles(3)->maxSize(4096)->maxLength(2000)
                ->quickReplies(['Thanks for reaching out!', 'Could you share a screenshot?', 'This is fixed now.']),
        ];
    }
}
