<?php

namespace Basilicom\DataQualityBundle\GridOperator;

use Pimcore\DataObject\GridColumnConfig\Operator\AbstractOperator;
use stdClass;

class Quality extends AbstractOperator
{
    private $colorPalette = [
        '#FFA0A0',
        '#FFD098',
        '#ffff90',
        '#C8FF90',
        '#90ff90',
    ];

    public function __construct(stdClass $config, array $context = [])
    {
        parent::__construct($config, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function getLabeledValue($element)
    {
        $result        = new stdClass();
        $result->label = $this->label;

        $childs = $this->getChilds();

        if (!$childs) {
            return $result;
        } else {
            $child       = $childs[0];
            $childResult = $child->getLabeledValue($element);
            $childValue  = min(100, max(0, (int)$childResult->value));

            $colorIndex = (int)(($childValue / 100) * (count($this->colorPalette) - 1));

            $color = $this->colorPalette[$colorIndex];

            $result->value = '<div style="background-color:'.$color.'; text-align:center; font-weight: bold; margin: 0 -10px;">'
                . $childValue
                . '%</div>';

            $result->isArrayType = false;
        }

        return $result;
    }
}
