<?php

namespace Basilicom\DataQualityBundle\Model\Renderer;

use Basilicom\DataQualityBundle\Exception\DataQualityException;
use Basilicom\DataQualityBundle\Service\DataQualityService;
use Pimcore\Log\ApplicationLogger;
use Pimcore\Logger;
use Pimcore\Model\DataObject\AbstractObject;
use Symfony\Component\Translation\TranslatorInterface;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

class DataQualityRenderer
{
    public static function renderLayoutText(?AbstractObject $dataObject, $context): string
    {
        $oldInheritedValuesSetting = self::temporarilyEnableInheritance();

        $container = \Pimcore::getContainer();
        /** @var DataQualityService $dataQualityService */
        $dataQualityService = $container->get(DataQualityService::class);
        /** @var Environment $twig */
        $twig = $container->get('twig');
        /** @var TranslatorInterface $translator */
        $translator = $container->get('translator');

        try {
            $dataQualityConfig = $dataQualityService->getDataQualityConfig($dataObject);

            if ($dataQualityConfig === null) {
                return $translator->trans('dataQuality.error.missing_config', [],'admin');
            }

            $dataQualityRule = $dataQualityService->getDataQualityRule($dataQualityConfig);

            if ($dataQualityRule === null) {
                return $translator->trans('dataQuality.error.missing_rule', [],'admin');
            }

            $html = $twig->render('@DataQualityBundle/Resources/views/data-quality.html.twig', [
                'data' => $dataQualityService->getDataQualityData($dataObject, $dataQualityRule),
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
