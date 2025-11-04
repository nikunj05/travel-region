<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class GradientColorPicker extends Field
{
    protected string $view = 'filament.schemas.components.gradient-color-picker';

    protected int $maxColors = 5;
    protected string $direction = 'to right';

    public function maxColors(int $max): static
    {
        $this->maxColors = $max;
        return $this;
    }

    public function direction(string $direction): static
    {
        $this->direction = $direction;
        return $this;
    }

    public function getMaxColors(): int
    {
        return $this->maxColors;
    }

    public function getDirection(): string
    {
        return $this->direction;
    }
}
