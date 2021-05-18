<?php

namespace Basilicom\DataQualityBundle\Model\Renderer;

use Basilicom\DataQualityBundle\Exception\DataQualityException;
use Basilicom\DataQualityBundle\Service\DataQualityService;
use Pimcore\Model\DataObject;
use Pimcore\Model\DataObject\AbstractObject;
use Pimcore\Model\DataObject\DataQualityConfig;
use Pimcore\Model\DataObject\Objectbrick\Data\ObjectCompletion;
use Symfony\Component\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class DataQualityRenderer
{
    public static function renderLayoutText(DataObject $dataObject, $context): string
    {
        $oldInheritedValuesSetting = self::temporarilyEnableInheritance();

        $container = \Pimcore::getContainer();
        /** @var DataQualityService $productQualityService */
        $dataQualityService = $container->get(DataQualityService::class);
        /** @var Environment $twig */
        $twig = $container->get('twig');
        /** @var TranslatorInterface $translator */
        $translator = $container->get('translator');

        try {
            $currentDataQualityConfig = null;
            $dataQualityConfigList = new DataQualityConfig\Listing();

            foreach ($dataQualityConfigList as $dataQualityConfig) {
                $dataQualityType = $dataQualityConfig->getDataQualityType();
                if ($dataObject->getClassId() === $dataQualityType) {
                    $currentDataQualityConfig = $dataQualityConfig;
                }
            }

            if ($currentDataQualityConfig === null) {
                return $translator->trans('dataQuality.error.missing_config');
            }

            /** @var DataQualityConfig $currentDataQualityConfig */
            $dataQualityRule = $currentDataQualityConfig->getDataQulalityRule();

            if ($dataQualityRule === null) {
                return $translator->trans('dataQuality.error.missing_rule');
            }

            foreach ($dataQualityRule->getItems() as $dataQualityRuleItem) {
                if ($dataQualityRuleItem instanceof ObjectCompletion) {
                    $status = [];
                    foreach ($dataQualityRuleItem->getArea() as $area) {
                        $status[] = [
                            'name' => $area['AreaName']->getData(),
                            'fields' => $dataQualityService->getDataQualityStatus($dataObject, $area['AreaFields']->getData())
                        ];
                    }
                }
            }

            $html = $twig->render('@DataQualityBundle/Resources/views/data-quality.html.twig', [
                'status' => $status,
            ]);
        } catch (LoaderError | RuntimeError | SyntaxError | DataQualityException $e) {
            $html = $e->getMessage();
        }

        self::restoreInheritance($oldInheritedValuesSetting);

        return $html;
    }

    /**
     * @return bool
     */
    protected static function temporarilyEnableInheritance()
    {
        $oldInheritedValuesSetting = AbstractObject::getGetInheritedValues();
        AbstractObject::setGetInheritedValues(true);

        return $oldInheritedValuesSetting;
    }

    protected static function restoreInheritance(bool $oldInheritedValuesSetting)
    {
        AbstractObject::setGetInheritedValues($oldInheritedValuesSetting);
    }
}
