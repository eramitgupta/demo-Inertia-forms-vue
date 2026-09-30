<?php

namespace App\Forms\Fields;

use Erag\InertiaForms\Fields\Field;

/**
 * A number with minus and plus buttons, rendered by the `QuantityStepper` component.
 */
class QuantityStepper extends Field
{
    protected int $min = 0;

    protected int $max = 99;

    protected int $step = 1;

    protected ?string $unit = null;

    public function component(): string
    {
        return 'QuantityStepper';
    }

    public function min(int $min): static
    {
        $this->min = $min;

        return $this;
    }

    public function max(int $max): static
    {
        $this->max = $max;

        return $this;
    }

    public function step(int $step): static
    {
        $this->step = max(1, $step);

        return $this;
    }

    /**
     * Text shown after the number, like "tickets".
     */
    public function unit(?string $unit): static
    {
        $this->unit = $unit;

        return $this;
    }

    public function emptyValue(): mixed
    {
        return $this->min;
    }

    /**
     * @return array<int, string>
     */
    protected function typeRules(): array
    {
        return ['integer', 'between:'.$this->min.','.$this->max];
    }

    /**
     * @return array{min: int, max: int, step: int, unit: string|null}
     */
    protected function props(): array
    {
        return ['min' => $this->min, 'max' => $this->max, 'step' => $this->step, 'unit' => $this->unit];
    }
}
