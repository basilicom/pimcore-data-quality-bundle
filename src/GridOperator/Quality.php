<?php

namespace Basilicom\DataQualityBundle\GridOperator;

use Pimcore\Bundle\AdminBundle\DataObject\GridColumnConfig\Operator\AbstractOperator;
use Pimcore\Bundle\AdminBundle\DataObject\GridColumnConfig\ResultContainer;
use stdClass;

class Quality extends AbstractOperator
{
    private array $colorPalette = ['#FFA0A0', '#FFD098', '#ffff90', '#C8FF90', '#90ff90'];

    public function __construct(stdClass $config, array $context = [])
    {
        parent::__construct($config, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function getLabeledValue($element): ResultContainer|stdClass|null
    {
        $result = new stdClass();
        $result->label = $this->label;

        $children = $this->getChildren();

        if (!$children) {
            return $result;
        }

        $child = $children[0];
        $childResult = $child->getLabeledValue($element);

        $childValue = min(100, max(0, (int)$childResult->value));
        $colorIndex = (int)(($childValue / 100) * (count($this->colorPalette) - 1));

        $result->value = sprintf(
            '<div style="background-color:%s; text-align:center; font-weight: bold; margin: 0 -10px;">%s%%</div>',
            $this->colorPalette[$colorIndex],
            $childValue
        );

        $result->isArrayType = false;

        return $result;
    }
}
