<?php


namespace app\models;


use yii\db\Query;
use yii\helpers\ArrayHelper;

trait MultiFieldTrait
{
    private array $_multiField_oldValues = [];

    private static function multiField_loadFromDatabase(array $models, string $modelField, string $relationTable)
    {
        $relationIds = array_filter(ArrayHelper::getColumn($models, 'id'));
        $rows = (new Query())
            ->select(['relation_id', 'key', 'value'])
            ->from($relationTable)
            ->where(['relation_id' => $relationIds])
            ->all();

        $allValues = [];
        foreach ($rows as $row) {
            $allValues[$row['relation_id']][$row['key']][] = $row['value'];
        }

        foreach ($models as $model) {
            $model->$modelField = $allValues[$model->id] ?? [];
            $model->_multiField_oldValues[$modelField] = $model->$modelField;
        }
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