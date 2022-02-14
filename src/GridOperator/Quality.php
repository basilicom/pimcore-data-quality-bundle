<?php

namespace Basilicom\DataQualityBundle\GridOperator;

use Pimcore\DataObject\GridColumnConfig\Operator\AbstractOperator;
use Pimcore\DataObject\GridColumnConfig\ResultContainer;

class Quality extends AbstractOperator
{
    private $additionalData;

    private $colorPalette = [
        '#FFA0A0',
        '#FFD098',
        '#ffff90',
        '#C8FF90',
        '#90ff90',
    ];

    public function __construct(\stdClass $config, $context = null)
    {
        parent::__construct($config, $context);

        $this->additionalData = $config->additionalData;
    }

    /**
     * {@inheritdoc}
     */
    public function getLabeledValue($element)
    {
        $result = new \stdClass();
        $result->label = $this->label;

        $childs = $this->getChilds();

        if (!$childs) {
            return $result;
        } else {
            $c = $childs[0];
            $childResult = $c->getLabeledValue($element);
            $childValue = min(100, max(0, (int)$childResult->value));

            $colorIndex = (int)(($childValue/100)*(count($this->colorPalette)-1));

//            $result->value = 'CI ' . $colorIndex;

            $color = $this->colorPalette[$colorIndex];

            $result->value = '<div style="background-color:'.$color.'; text-align:center;"><b>'
                . $childValue
                . '%</b></div>';

            $result->isArrayType = false;
        }

        return $result;
    }

}
