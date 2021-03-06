<?php


namespace app\tz;


use yii\base\Behavior;
use yii\base\Event;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;

class CustomPropsBehavior extends Behavior
{
    public array $custom_props = [];
    public array $old_custom_props = [];

    /**
     * Example $propCondition:
     * ```
     * $propCondition = [
     *     'key1' => ['value 1', 'value 2'],
     *     'key2' => ['value 4'],
     * ];
     * ```
     * @param string|ActiveRecord $modelClassName
     * @param ActiveQuery $query
     * @param array $propConditions
     */
    public static function applyPropConditions(string $modelClassName, ActiveQuery $query, array $propConditions)
    {
        $modelTable = $modelClassName::tableName();
        $propTable = self::getPropTable($modelClassName);
        $i = 0;
        foreach ($propConditions as $key => $values) {
            $i++;
            $joinAlias = "prop{$i}";
            $query->innerJoin(
                "$propTable $joinAlias",
                "$modelTable.id = $joinAlias.relation_id AND $joinAlias.key=:prop_key{$i}",
                [":prop_key{$i}" => $key]
            );
            $query->andWhere(["$joinAlias.value" => $values]);
        }
    }

    /** @noinspection PhpParamsInspection */
    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_FIND =>
                fn(Event $event) => $this->onAfterFind($event->sender),
            ActiveRecord::EVENT_AFTER_REFRESH =>
                fn(Event $event) => $this->onAfterFind($event->sender),
            ActiveRecord::EVENT_AFTER_INSERT =>
                fn(Event $event) => $this->onAfterInsert($event->sender),
            ActiveRecord::EVENT_AFTER_UPDATE =>
                fn(Event $event) => $this->onAfterUpdate($event->sender),
            ActiveRecord::EVENT_AFTER_DELETE =>
                fn(Event $event) => $this->onAfterDelete($event->sender),
        ];
    }

    private function onAfterFind(ActiveRecord $model)
    {
        $table = $this->getPropTable(get_class($model));

        $rows = (new Query())
            ->select(['relation_id', 'key', 'value'])
            ->from($table)
            ->where(['relation_id' => $model->primaryKey])
            ->all();

        $data = [];
        foreach ($rows as $row) {
            $data[$row['key']][] = $row['value'];
        }

        $this->custom_props = $data;
        $this->old_custom_props = $data;
    }

    private function onAfterInsert(ActiveRecord $model)
    {
        $this->doInsertProps($model);
        $this->old_custom_props = $this->custom_props;
    }

    private function onAfterUpdate(ActiveRecord $model)
    {
        if ($this->custom_props != $this->old_custom_props) {
            $this->doClean($model);
            $this->doInsertProps($model);
            $this->old_custom_props = $this->custom_props;
        }
    }

    private function onAfterDelete(ActiveRecord $model)
    {
        $this->doClean($model);
    }

    private function doClean(ActiveRecord $model)
    {
        $table = $this->getPropTable(get_class($model));
        \Yii::$app->db->createCommand()
            ->delete($table, ['relation_id' => $model->primaryKey])
            ->execute();
    }

    private function doInsertProps(ActiveRecord $model)
    {
        $table = $this->getPropTable(get_class($model));
        $batchData = [];
        foreach ($this->custom_props as $key => $values) {
            foreach ($values as $value) {
                $batchData[] = [$model->primaryKey, $key, $value];
            }
        }
        \Yii::$app->db->createCommand()
            ->batchInsert($table, ['relation_id', 'key', 'value'], $batchData)
            ->execute();
    }

    /**
     * @param string|ActiveRecord $modelClassName
     * @return string
     */
    private static function getPropTable(string $modelClassName)
    {
        $modelTable = $modelClassName::tableName();
        return preg_replace('/(\w+)/', '$1_props', $modelTable);
    }
}