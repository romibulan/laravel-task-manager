<?php

namespace App\Enums;

enum TaskStatus: string
{
    case Pending = 'pending';
    case Completed = 'completed';
    case InProgress = 'in_progress';

    public function options()
    {
        $options = [];
        foreach ($this->cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }

    public function label()
    {
        return match ($this) {
            self::Pending => 'Pending',
            self::Completed => 'Completed',
            self::InProgress => 'In Progress'
        };
    }

    public function color()
    {
        return match ($this) {
            self::Pending => 'gray',
            self::InProgress => 'yellow',
            self::Completed => 'green',
        };
    }

    public function transitions()
    {
        return match ($this) {
            self::Pending => [['value' => self::InProgress->value,   'label' => self::InProgress->label(), 'color' => self::InProgress->color()]],
            self::InProgress => [['value' => self::Completed->value, 'label' => self::Completed->label(),  'color' => self::Completed->color()]],
            self::Completed => [],
        };
    }
}
