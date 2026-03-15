<?php

namespace app\services;


use app\models\LoanProcessingParams;
use app\models\LoanRequest;
use app\models\LoanRequestPayload;
use Yii;

class LoanService extends \yii\base\Component
{


    /**
     * Сохраняем заявку
     *
     * @param LoanRequestPayload $payload
     * @return int|null
     * @throws \yii\db\Exception
     */
    public function createRequest(LoanRequestPayload $payload): ?int
    {
        if ($this->hasApprovedRequest($payload->user_id)) {
            throw new \Exception('Has approved request');
        }

        $loanRequest = new LoanRequest();
        $loanRequest->user_id = $payload->user_id;
        $loanRequest->amount = $payload->amount;
        $loanRequest->term = $payload->term;
        $loanRequest->status = LoanRequest::STATUS_PENDING;

        if (!$loanRequest->save()) {
            throw new \Exception(implode(',', $loanRequest->getErrorSummary(true)));
        }

        return $loanRequest->id;
    }

    /**
     * Старт обработки заявок
     *
     * @param LoanProcessingParams $params
     * @throws \Exception
     */
    public function startProcessing(LoanProcessingParams $params): void
    {
        // вместо выборки всех заявок последовательно выбирается одна,
        // т.к. могут быть несколько обработчиков и одновременно с обработкой могут добавляться новые заявки

        while ($pendingRequest = $this->getNextPendingRequest()) {

            // обработка не прерывается, если падает обработка конкретной заявке
            try {
                $this->processLoanRequest($pendingRequest, $params);
            } catch (\Exception $e) {
                Yii::error($e->getMessage());
                // TODO отсылка уведомления с ошибкой
            }
        }
    }

    /**
     * Обработка одной заявки
     *
     * @param LoanRequest $loanRequest
     * @param LoanProcessingParams $params
     * @throws \yii\db\Exception
     */
    public function processLoanRequest(LoanRequest $loanRequest, LoanProcessingParams $params): void
    {
        // имитация длительной обработки заявки для наглядности
        sleep($params->delay);

        // может быть только один одобренный займ
        if ($this->hasApprovedRequest($loanRequest->user_id)) {
            $loanRequest->changeStatusTo(LoanRequest::STATUS_DECLINED);
            return;
        }

        $isApproved = (random_int(1, 100) <= 10);

        $status = $isApproved
            ? LoanRequest::STATUS_APPROVED
            : LoanRequest::STATUS_DECLINED;

        $loanRequest->changeStatusTo($status);
    }

    /**
     * Проверка на существование подтвержденной заявки у пользователя
     *
     * @param $userId
     * @return bool
     */
    public function hasApprovedRequest($userId): bool
    {
        return LoanRequest::find()
            ->byUserId($userId)
            ->byStatus(LoanRequest::STATUS_APPROVED)
            ->exists();
    }

    /**
     * Выборка одной (очередной) заявки из очереди
     * TODO можно в явном виде задействовать queue
     *
     * @return LoanRequest
     */
    public function getNextPendingRequest(): ?LoanRequest
    {
        $transaction = Yii::$app->db->beginTransaction();

        try {

            // делаем select ... for update, чтобы исключить гонку на время выставления статуса
            $loanRequestQuery = LoanRequest::find()
                ->byStatus(LoanRequest::STATUS_PENDING)
                ->orderBy(['id' => SORT_ASC])
                ->limit(1);

            $loanRequest = LoanRequest::findBySql($loanRequestQuery->createCommand()->getRawSql().' FOR UPDATE')->one();

            if ($loanRequest) {
                // заявка исключается из очереди на обработку
                $loanRequest->changeStatusTo(LoanRequest::STATUS_UNDER_REVIEW);
            }

            $transaction->commit();

        } catch (\Exception $e) {
            $transaction->rollBack();
            Yii::error($e->getMessage());
            throw $e;
        }

        return $loanRequest;
    }

}