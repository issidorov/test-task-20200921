<?php


namespace app\tests\unit\tz\by_trait\MultiFieldTrait;


use app\tz\by_trait\MultiFieldTrait;
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

    public function afterFind()
    {
        parent::afterFind();
        $this->multiField_loadFromDatabase('myFieldData', 'my_fake_value');
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->multiField_saveToDatabase('myFieldData', 'my_fake_value');
    }
}