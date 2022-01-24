<?php

namespace Basilicom\DataQualityBundle\Model\Renderer;

use Pimcore\Model\DataObject\ClassDefinition\Layout\DynamicTextLabelInterface;
use Symfony\Component\Translation\TranslatorInterface;

class DataQualityConfigRenderer implements DynamicTextLabelInterface
{
    public function renderLayoutText($data, $object, $params): string
    {
        $container = \Pimcore::getContainer();
        /** @var TranslatorInterface $translator */
        $translator = $container->get('translator');

        $fieldName = $params['fieldname'];
        switch ($fieldName) {
            case 'DataQualityInfo':
                $text = $translator->trans('dataQualityConfig.text.dataQualityDescriptionClass', [], 'admin');
                $text .= '<br/>' . $translator->trans('dataQualityConfig.text.dataQualityDescriptionField', [], 'admin');

                break;
            case 'DataQualityRulesInfo':
                $text = $translator->trans('dataQualityConfig.text.dataQualityRules', [], 'admin');

                break;
            default:
                $text = '';
        }

        return '<div class="alert alert-info">' . $text . '</div>';
    }
}
