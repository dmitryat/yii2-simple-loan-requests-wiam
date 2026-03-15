<?php

use yii\db\Migration;

class m260314_123529_create_table_loan_request extends Migration
{
    const TABLE_LOAN_REQUEST = '{{%loan_request}}';

    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable(self::TABLE_LOAN_REQUEST, [
            'id'         => $this->primaryKey(11)->unsigned(),
            'user_id'    => $this->integer()->notNull(),
            'amount'     => $this->integer()->notNull(),
            'term'       => $this->integer()->notNull(),
            'status'     => $this->string(32)->notNull()->defaultValue('pending'),
            'created_at' => $this->dateTime()->defaultExpression('current_timestamp'),
            'updated_at' => $this->dateTime()->defaultExpression('current_timestamp')
        ]);

        $this->createIndex('idx_loan_request_user_id', self::TABLE_LOAN_REQUEST, 'user_id');
        $this->createIndex('idx_loan_request_status', self::TABLE_LOAN_REQUEST, 'status');

        // Частичный индекс - чтобы гарантировать не больше одной одобренной заявки на уровне БД
        $table = self::TABLE_LOAN_REQUEST;
        $status = 'approved';
        $this->execute("CREATE UNIQUE INDEX idx_unique_approved_per_user ON {$table} (user_id)  WHERE status = '{$status}'");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable(self::TABLE_LOAN_REQUEST);
    }

}
