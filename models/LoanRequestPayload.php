<?php

namespace app\models;

class LoanRequestPayload extends \yii\base\Model
{

    public int $user_id = 0;
    public int $amount = 0;
    public int $term = 0;

    public function rules()
    {
        return [
            [['user_id', 'amount', 'term'], 'required'],
            [['user_id', 'amount', 'term'], 'integer'],
            ['user_id', 'integer', 'min' => 0],
            ['amount', 'integer', 'min' => 0],
            ['term', 'integer', 'min' => 0],
        ];
    }

    public function attributeLabels()
    {
        return [
            'user_id' => 'ID пользователя',
            'amount'  => 'Сумма займа',
            'term'    => 'Срок займа (дней)',
        ];
    }

}