<?php

namespace App\Enums\Tenant;

enum ResourceLibraryItemType: int
{
    case BOOK = 1;
    case PODCAST = 2;
    case VIDEO = 3;
    case COURSE = 4;
    case ARTICLE = 5;

    public function label(): string
    {
        return match ($this) {
            self::BOOK => 'Book',
            self::PODCAST => 'Podcast',
            self::VIDEO => 'Video',
            self::COURSE => 'Course',
            self::ARTICLE => 'Article',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::BOOK => 'BookOpen',
            self::PODCAST => 'Podcast',
            self::VIDEO => 'Video',
            self::COURSE => 'GraduationCap',
            self::ARTICLE => 'FileText',
        };
    }
}