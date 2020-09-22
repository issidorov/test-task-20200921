<?php


namespace app\tz;


use yii\base\Event;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Query;

trait CustomPropsTrait
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
     * @param array $propConditions
     * @return ActiveQuery
     */
    public static function findByCustomProps(array $propConditions)
    {
        $query = static::find();
        $modelTable = static::tableName();
        $propTable = self::customProps_getPropTable();
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
        return $query;
    }

    public function customProps_init()
    {
        $this->on(ActiveRecord::EVENT_AFTER_FIND, fn(Event $event) => $this->customProps_onAfterFind());
        $this->on(ActiveRecord::EVENT_AFTER_REFRESH, fn(Event $event) => $this->customProps_onAfterFind());
        $this->on(ActiveRecord::EVENT_AFTER_INSERT, fn(Event $event) => $this->customProps_onAfterInsert());
        $this->on(ActiveRecord::EVENT_AFTER_UPDATE, fn(Event $event) => $this->customProps_onAfterUpdate());
        $this->on(ActiveRecord::EVENT_AFTER_DELETE, fn(Event $event) => $this->customProps_onAfterDelete());
    }

    private function customProps_onAfterFind()
    {
        $table = $this->customProps_getPropTable();

        $rows = (new Query())
            ->select(['relation_id', 'key', 'value'])
            ->from($table)
            ->where(['relation_id' => $this->primaryKey])
            ->all();

        $data = [];
        foreach ($rows as $row) {
            $data[$row['key']][] = $row['value'];
        }

        $this->custom_props = $data;
        $this->old_custom_props = $data;
    }

    private function customProps_onAfterInsert()
    {
        $this->customProps_doInsertProps();
        $this->old_custom_props = $this->custom_props;
    }

    private function customProps_onAfterUpdate()
    {
        if ($this->custom_props != $this->old_custom_props) {
            $this->customProps_doClean();
            $this->customProps_doInsertProps();
            $this->old_custom_props = $this->custom_props;
        }
    }

    private function customProps_onAfterDelete()
    {
        $this->customProps_doClean();
    }

    private function customProps_doClean()
    {
        $table = $this->customProps_getPropTable();
        \Yii::$app->db->createCommand()
            ->delete($table, ['relation_id' => $this->primaryKey])
            ->execute();
    }

    private function customProps_doInsertProps()
    {
        $table = $this->customProps_getPropTable();
        $batchData = [];
        foreach ($this->custom_props as $key => $values) {
            foreach ($values as $value) {
                $batchData[] = [$this->primaryKey, $key, $value];
            }
        }
        \Yii::$app->db->createCommand()
            ->batchInsert($table, ['relation_id', 'key', 'value'], $batchData)
            ->execute();
    }

    private static function customProps_getPropTable()
    {
        $modelClassName = static::class;
        /** @noinspection PhpUndefinedMethodInspection */
        return preg_replace('/(\w+)/', '$1_props', $modelClassName::tableName());
    }
}