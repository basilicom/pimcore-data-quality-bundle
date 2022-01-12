<?php

namespace Basilicom\DataQualityBundle\Controller;

use Basilicom\DataQualityBundle\Exception\DataQualityException;
use Basilicom\DataQualityBundle\Service\DataQualityService;
use Exception;
use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/data-quality")
 */
class DataQualityController extends FrontendController
{
    private DataQualityService $dataQualityService;

    public function __construct(DataQualityService $dataQualityService)
    {
        $this->dataQualityService = $dataQualityService;
    }

    /**
     * @Route("/index/{id}")
     *
     * @throws Exception
     */
    public function indexAction(int $id, Request $request): Response
    {
        $standalone          = $request->get('standalone') === 'true';
        $dataQualityConfigId = $request->get('configId');

        try {
            $translator = \Pimcore::getContainer()->get('translator');

            $dataObject = DataObject::getById($id, true);
            if (empty($dataObject)) {
                throw new DataQualityException($translator->trans('dataQuality.error.missing_data_object', [], 'admin'));
            }

            $dataQualityConfigs = $this->dataQualityService->getDataQualityConfig($dataObject);
            if (empty($dataQualityConfigs)) {
                throw new DataQualityException($translator->trans('dataQuality.error.missing_config', [], 'admin'));
            }

            if (isset($dataQualityConfigs[$dataQualityConfigId])) {
                $data[] = $this->dataQualityService->calculateDataQuality($dataObject, $dataQualityConfigs[$dataQualityConfigId]);
            } else {
                foreach ($dataQualityConfigs as $dataQualityConfig) {
                    $data[] = $this->dataQualityService->calculateDataQuality($dataObject, $dataQualityConfig);
                }
            }

            $error = '';
        } catch (Exception $exception) {
            $error = $exception->getMessage();
            $data  = [];
        }

        return $this->render('@DataQuality/data-quality.html.twig', [
            'data'       => $data,
            'standalone' => $standalone,
            'error'      => $error,
        ]);
    }

    /**
     * @Route("/check-class-has-data-quality/{id}")
     */
    public function checkClassHasDataQuality(int $id): JsonResponse
    {
        try {
            $dataObject = DataObject::getById($id, true);
            if (empty($dataObject)) {
                throw new DataQualityException('class has no data quality.');
            }

            $dataQualityConfig = $this->dataQualityService->getDataQualityConfig($dataObject);
            if (empty($dataQualityConfig)) {
                throw new DataQualityException('class has no data quality.');
            }

            return new JsonResponse(null, 200);
        } catch (Exception $exception) {
            return new JsonResponse($exception->getMessage(), 400);
        }
    }
}
