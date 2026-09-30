<?php

namespace App\Forms\Fields;

use Erag\InertiaForms\Fields\Field;

/**
 * Star rating rendered by the `Rating` frontend component.
 */
class Rating extends Field
{
    protected int $stars = 5;

    public function component(): string
    {
        return 'Rating';
    }

    /**
     * Number of stars to show.
     */
    public function stars(int $stars): static
    {
        $this->stars = max(1, $stars);

        return $this;
    }

    public function emptyValue(): mixed
    {
        return null;
    }

    /**
     * @return array<int, string>
     */
    protected function typeRules(): array
    {
        return ['integer', 'between:1,'.$this->stars];
    }

    /**
     * @return array{stars: int}
     */
    protected function props(): array
    {
        return ['stars' => $this->stars];
    }
}
