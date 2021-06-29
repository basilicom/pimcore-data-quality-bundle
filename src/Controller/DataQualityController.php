<?php

namespace Basilicom\DataQualityBundle\Controller;

use Basilicom\DataQualityBundle\Service\DataQualityService;
use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Route("/data-quality")
 */
class DataQualityController extends FrontendController
{
    /** @var DataQualityService */
    private $dataQualityService;

    public function __construct(DataQualityService $dataQualityService)
    {
        $this->dataQualityService = $dataQualityService;
    }

    /**
     * @Route("/index/{id}")
     *
     * @param int $id
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function indexAction(int $id, Request $request)
    {
        /** @var TranslatorInterface $translator */
        $translator = $this->container->get('translator');
        $dataObject = DataObject::getById($id);
        $dataQualityConfig = $this->dataQualityService->getDataQualityConfig($dataObject);

        if ($dataQualityConfig === null) {
            return $translator->trans('dataQuality.error.missing_config', [],'admin');
        }

        $dataQualityRule = $this->dataQualityService->getDataQualityRule($dataQualityConfig);

        if ($dataQualityRule === null) {
            return $translator->trans('dataQuality.error.missing_rule', [],'admin');
        }

        return $this->render('@DataQualityBundle/Resources/views/data-quality.html.twig', [
            'data' => $this->dataQualityService->getDataQualityData($dataObject, $dataQualityRule),
            'standalone' => true
        ]);
    }
}
