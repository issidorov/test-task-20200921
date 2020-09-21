<?php


namespace app\tests\unit\models\MultiFieldTrait;


use app\models\MultiFieldTrait;
use yii\db\ActiveRecord;

/**
 * Class MyFake
 * @package tests\unit\models\MultiFieldTrait
 * @property $id
 * @property array $myFieldData
 */
class MyFake extends ActiveRecord
{
    use MultiFieldTrait;

    public array $myFieldData = [];

    public static function findOne($condition)
    {
        $model = parent::findOne($condition);
        self::multiField_loadFromDatabase([$model], 'myFieldData', 'my_fake_value');
        return $model;
    }

    public static function findAll($condition)
    {
        $models = parent::findAll($condition);
        self::multiField_loadFromDatabase($models, 'myFieldData', 'my_fake_value');
        return $models;
    }

    public function saveMyFieldData()
    {
        $this->multiFieldUpdate('my_fake_value', $this->id, $this->myFieldData);
    }
}