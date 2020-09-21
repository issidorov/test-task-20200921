<?php


namespace app\models;


use yii\db\Query;

trait MultiFieldTrait
{
    private array $multiFieldCache = [];

    private static function multiFieldGetData(string $table, array $relationIds)
    {
        $relationIds = array_filter($relationIds);

        $rows = (new Query())
            ->select(['relation_id', 'key', 'value'])
            ->from($table)
            ->where(['relation_id' => $relationIds])
            ->all();

        $res = [];
        foreach ($rows as $row) {
            $res[$row['relation_id']][$row['key']][] = $row['value'];
        }
        return $res;
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