<?php


namespace app\models;


use yii\db\Query;
use yii\helpers\ArrayHelper;

trait MultiFieldTrait
{
    private array $multiFieldCache = [];

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
        }
    }

    private function multiFieldUpdate($table, $relationId, $data)
    {
        \Yii::$app->db->createCommand()
            ->delete($table, ['relation_id' => $relationId])
            ->execute();

        $batchData = [];
        foreach ($data as $key => $values) {
            foreach ($values as $value) {
                $batchData[] = [$relationId, $key, $value];
            }
        }
        \Yii::$app->db->createCommand()
            ->batchInsert($table, ['relation_id', 'key', 'value'], $batchData)
            ->execute();
    }
}