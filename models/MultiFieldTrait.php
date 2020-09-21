<?php


namespace app\models;


use yii\db\Query;

trait MultiFieldTrait
{
    private array $_multiField_oldValues = [];

    private function multiField_loadFromDatabase(string $modelField, string $relationTable)
    {
        $rows = (new Query())
            ->select(['relation_id', 'key', 'value'])
            ->from($relationTable)
            ->where(['relation_id' => $this->id])
            ->all();

        $data = [];
        foreach ($rows as $row) {
            $data[$row['key']][] = $row['value'];
        }

        $this->$modelField = $data;
        $this->_multiField_oldValues[$modelField] = $data;
    }

    private function multiField_saveToDatabase(string $modelField, string $relationTable)
    {
        $oldValue = $this->_multiField_oldValues[$modelField] ?? [];
        if ($this->$modelField != $oldValue) {
            \Yii::$app->db->createCommand()
                ->delete($relationTable, ['relation_id' => $this->id])
                ->execute();

            $batchData = [];
            foreach ($this->$modelField as $key => $values) {
                foreach ($values as $value) {
                    $batchData[] = [$this->id, $key, $value];
                }
            }
            \Yii::$app->db->createCommand()
                ->batchInsert($relationTable, ['relation_id', 'key', 'value'], $batchData)
                ->execute();
            $this->_multiField_oldValues[$modelField] = $this->$modelField;
        }
    }
}