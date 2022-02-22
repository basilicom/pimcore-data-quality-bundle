<?php

namespace Basilicom\DataQualityBundle\View;

class DataQualityViewModel
{
    private string $title;
    private int $percentage;

    /** @var DataQualityGroupViewModel[] */
    private array $groups;

    public function __construct(string $title, int $percentage, array $groups)
    {
        $this->title      = $title;
        $this->percentage = $percentage;
        $this->groups     = $groups;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return int
     */
    public function getPercentage(): int
    {
        return $this->percentage;
    }

    /**
     * @return DataQualityGroupViewModel[]
     */
    public function getGroups(): array
    {
        return $this->groups;
    }
}
