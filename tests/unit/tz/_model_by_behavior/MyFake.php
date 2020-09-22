<?php


namespace app\tests\unit\tz\_model_by_behavior;


use app\tz\CustomPropsBehavior;
use yii\db\ActiveRecord;

/**
 * Class MyFake
 * @property $id
 * @property array $custom_props
 */
class MyFake extends ActiveRecord
{
    public function behaviors()
    {
        return [
            CustomPropsBehavior::class
        ];
    }

    /**
     * @param $propConditions - see {@see CustomPropsBehavior::applyPropConditions}
     * @return \yii\db\ActiveQuery
     */
    public static function findByCustomProps($propConditions)
    {
        $query = static::find();
        CustomPropsBehavior::applyPropConditions(static::class, $query, $propConditions);
        return $query;
    }
}