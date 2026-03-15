<?php

namespace app\controllers;

use app\models\LoanProcessingParams;
use app\models\LoanRequestPayload;
use Yii;
use yii\filters\ContentNegotiator;
use yii\web\Response;

class ApiController extends \yii\rest\Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        $behaviors['contentNegotiator'] = [
            'class'   => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];

        return $behaviors;
    }

    /**
     * POST /requests
     * Подача заявки на займ
     */
    public function actionRequests()
    {
        $code = 201;
        $result = true;
        $response = [];

        try {

            if (!Yii::$app->request->isPost) {
                throw new \Exception('Non-POST request');
            }
            $payload = new LoanRequestPayload();
            $payload->load(Yii::$app->request->getBodyParams(), '');

            if (!$payload->validate()) {
                $errors = implode(', ', $payload->getErrorSummary(true));
                throw new \Exception('Error in request payload: ' . $errors);
            }

            $id = Yii::$app->loanService->createRequest($payload);

            if ($id === null) {
                throw new \Exception('Request id are empty');
            }

            $response['id'] = $id;

        } catch (\Exception $e) {
            $code = 400;
            $result = false;
            Yii::error("Exception:{$e->getFile()}:{$e->getLine()}:".$e->getMessage());
        }

        $response['result'] = $result;
        Yii::$app->response->statusCode = $code;

        return $response;
    }

    /**
     * GET /processor?delay=5
     * Обработка заявок на займ
     */
    public function actionProcessor()
    {
        try {
            $params = new LoanProcessingParams(
                delay: (int)Yii::$app->request->get('delay', 0),
            );

            Yii::$app->loanService->startProcessing($params);

        } catch (\Exception $e) {
            Yii::error("Exception:{$e->getFile()}:{$e->getLine()}:".$e->getMessage());
        }

        Yii::$app->response->statusCode = 200;
        return ['result' => true];
    }

}