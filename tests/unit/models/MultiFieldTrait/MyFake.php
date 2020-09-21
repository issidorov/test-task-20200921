<?php


namespace app\tests\unit\models\MultiFieldTrait;


use app\models\MultiFieldTrait;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

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
        $ids = [$model->id];
        $model->myFieldData = self::multiFieldGetData('my_fake_value', $ids)[$model->id];
        return $model;
    }

    public static function findAll($condition)
    {
        $models = parent::findAll($condition);
        $ids = ArrayHelper::getColumn($models, 'id');
        $data = self::multiFieldGetData('my_fake_value', $ids);
        foreach ($models as $model) {
            $model->myFieldData = $data[$model->id] ?? [];
        }
        return $models;
    }

    public function saveMyFieldData()
    {
        $this->multiFieldUpdate('my_fake_value', $this->id, $this->myFieldData);
    }
}