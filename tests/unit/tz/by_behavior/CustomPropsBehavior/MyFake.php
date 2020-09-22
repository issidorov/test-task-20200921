<?php


namespace app\tests\unit\tz\by_behavior\CustomPropsBehavior;


use app\tz\by_behavior\CustomPropsBehavior;
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