<?php

namespace App\Forms\Fields;

use Erag\InertiaForms\Fields\Field;

/**
 * One box per digit, like a verification code, rendered by the `CodeInput` component.
 */
class CodeInput extends Field
{
    protected int $length = 6;

    public function component(): string
    {
        return 'CodeInput';
    }

    /**
     * Number of digits in the code.
     */
    public function length(int $length): static
    {
        $this->length = min(max($length, 2), 10);

        return $this;
    }

    /**
     * @return array<int, string>
     */
    protected function typeRules(): array
    {
        return ['digits:'.$this->length];
    }

    /**
     * @return array{length: int}
     */
    protected function props(): array
    {
        return ['length' => $this->length];
    }
}
