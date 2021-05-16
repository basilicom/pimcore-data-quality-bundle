<?php

namespace Basilicom\DataQualityBundle\Model\DataObject\ClassDefinition\Layout;

use Pimcore\Model;

class DataQuality extends Model\DataObject\ClassDefinition\Layout
{
    /** @var string */
    public $fieldtype = 'dataQuality';

    /** @var string */
    public $html = '';

    /**
     * @param Model\DataObject\Concrete $object
     * @param array $context additional contextual data
     *
     * @return self
     */
    public function enrichLayoutDefinition($object, $context = [])
    {
        $renderer = Model\DataObject\ClassDefinition\Helper\DynamicTextResolver::resolveRenderingClass(
            'Basilicom\\DataQualityBundle\\Model\\Renderer\\DataQualityRenderer'
        );

        if (method_exists($renderer, 'renderLayoutText')) {
            $context['fieldname'] = $this->getName();
            $context['layout'] = $this;
            $result = call_user_func([$renderer, 'renderLayoutText'], $object, $context);
            $this->html = $result;
        } else {
            $this->html = '';
        }

        return $this;
    }
}
