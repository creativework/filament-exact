<?php

namespace CreativeWork\FilamentExact\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum QueuePriorityEnum: int implements HasColor, HasLabel
{
    case VERY_LOW = 0;
    case LOW = 1;
    case NORMAL = 2;
    case HIGH = 3;
    case URGENT = 4;

    public function getLabel(): ?string
    {
        return match ($this) {
            self::VERY_LOW => __('Very low'),
            self::LOW => __('Low'),
            self::NORMAL => __('Normal'),
            self::HIGH => __('High'),
            self::URGENT => __('Urgent'),
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::VERY_LOW => 'gray',
            self::LOW => 'info',
            self::NORMAL => 'success',
            self::HIGH => 'warning',
            self::URGENT => 'danger'
        };
    }
}
