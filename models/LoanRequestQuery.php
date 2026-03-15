<?php

namespace app\models;

/**
 * This is the ActiveQuery class for [[LoanRequest]].
 *
 * @see LoanRequest
 */
class LoanRequestQuery extends \yii\db\ActiveQuery
{
    /**
     * {@inheritdoc}
     * @return LoanRequest[]|array
     */
    public function all($db = null)
    {
        return parent::all($db);
    }

    /**
     * @param $userId
     * @return LoanRequestQuery
     */
    public function byUserId($userId): static
    {
        return $this->andWhere(['user_id' => $userId]);
    }

    /**
     * @param $status
     * @return LoanRequestQuery
     */
    public function byStatus($status): static
    {
        return $this->andWhere(['status' => $status]);
    }

    /**
     * {@inheritdoc}
     * @return LoanRequest|array|null
     */
    public function one($db = null)
    {
        return parent::one($db);
    }
}
