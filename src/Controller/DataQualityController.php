<?php

namespace Basilicom\DataQualityBundle\Controller;

use Basilicom\DataQualityBundle\Exception\DataQualityException;
use Basilicom\DataQualityBundle\Service\DataQualityService;
use Exception;
use Pimcore\Controller\FrontendController;
use Pimcore\Model\DataObject;
use Pimcore\Translation\Translator;
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
    private Translator $translator;

    public function __construct(DataQualityService $dataQualityService, Translator $translator)
    {
        $this->dataQualityService = $dataQualityService;
        $this->translator = $translator;
    }

    /**
     * @Route("/index/{id}")
     *
     * @throws Exception
     */
    public function indexAction(int $id, Request $request): Response
    {
        $standalone = $request->get('standalone') === 'true';
        $dataQualityConfigId = $request->get('configId');
        $data = [];

        try {
            $dataObject = DataObject::getById($id, ['force' => true]);
            if (empty($dataObject)) {
                throw new DataQualityException($this->translator->trans('dataQuality.error.missing_data_object', [], 'admin'));
            }

            $dataQualityConfigs = $this->dataQualityService->getDataQualityConfigs($dataObject);
            if (empty($dataQualityConfigs)) {
                throw new DataQualityException($this->translator->trans('dataQuality.error.missing_config', [], 'admin'));
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
            $data = [];
        }

        return $this->render('@DataQuality/data-quality.html.twig', [
            'data' => $data,
            'standalone' => $standalone,
            'error' => $error,
        ]);
    }

    /**
     * @Route("/check-class-has-data-quality/{id}")
     */
    public function checkClassHasDataQuality(int $id): JsonResponse
    {
        try {
            $dataObject = DataObject::getById($id, ['force' => true]);
            if (!$dataObject instanceof DataObject\Concrete) {
                throw new DataQualityException('This is not a concrete data object.');
            }

            $dataQualityConfigs = $this->dataQualityService->getDataQualityConfigs($dataObject);
            if (empty($dataQualityConfigs)) {
                throw new DataQualityException('class has no data quality.');
            }

            return new JsonResponse([
                'result' => ['message' => 'ok'],
                'error' => null,
            ], 200);

        } catch (Exception $exception) {

            if ($exception instanceof DataQualityException) {
                return new JsonResponse([
                    'result' => null,
                    'error' => [
                        'message' => $exception->getMessage(),
                        'code' => $exception->getCode()
                    ],
                ], 200);
            }

            return new JsonResponse(['message' => $exception->getMessage()], 500);
        }
    }
}
