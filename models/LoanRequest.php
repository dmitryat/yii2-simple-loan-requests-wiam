<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "loan_request".
 *
 * @property int $id
 * @property int $user_id
 * @property int $amount
 * @property int $term
 * @property string $status
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class LoanRequest extends \yii\db\ActiveRecord
{

    const STATUS_PENDING      = 'pending';
    const STATUS_APPROVED     = 'approved';
    const STATUS_DECLINED     = 'declined';
    const STATUS_UNDER_REVIEW = 'under_review';

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'loan_request';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['status'], 'default', 'value' => self::STATUS_PENDING],
            [['user_id', 'amount', 'term'], 'required'],
            [['user_id', 'amount', 'term'], 'default', 'value' => null],
            [['user_id', 'amount', 'term'], 'integer'],
            ['user_id', 'integer', 'min' => 0],
            ['amount', 'integer', 'min' => 1],
            ['term', 'integer', 'min' => 0, 'max' => 365 * 10],
            [['created_at', 'updated_at'], 'safe'],
            [['status'], 'string', 'max' => 32],
            ['status', 'in', 'range' => [self::STATUS_PENDING, self::STATUS_APPROVED, self::STATUS_DECLINED, self::STATUS_UNDER_REVIEW]],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id'         => Yii::t('app', 'ID'),
            'user_id'    => Yii::t('app', 'User ID'),
            'amount'     => Yii::t('app', 'Amount'),
            'term'       => Yii::t('app', 'Term'),
            'status'     => Yii::t('app', 'Status'),
            'created_at' => Yii::t('app', 'Created At'),
            'updated_at' => Yii::t('app', 'Updated At'),
        ];
    }

    /**
     * {@inheritdoc}
     * @return LoanRequestQuery the active query used by this AR class.
     */
    public static function find()
    {
        return new LoanRequestQuery(get_called_class());
    }

    /**
     * @param $status
     * @param bool $needSaveToDb
     * @throws \yii\db\Exception
     */
    public function changeStatusTo($status, bool $needSaveToDb = true): bool
    {
        $this->status = $status;
        return $needSaveToDb ? $this->save() : true;
    }

    /**
     * @param bool $insert
     * @return bool
     */
    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->updated_at = date('Y-m-d H:i:s');

        return true;
    }
}
