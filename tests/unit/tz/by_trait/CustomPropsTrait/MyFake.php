<?php


namespace app\tests\unit\tz\by_trait\CustomPropsTrait;


use app\tz\by_trait\CustomPropsTrait;
use yii\db\ActiveRecord;

/**
 * Class MyFake
 * @package tests\unit\models\CustomPropsTrait
 * @property $id
 * @property array $myFieldData
 */
class MyFake extends ActiveRecord
{
    use CustomPropsTrait;

    public array $myFieldData = [];

    public function afterFind()
    {
        parent::afterFind();
        $this->multiField_loadFromDatabase('myFieldData', 'my_fake_props');
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $this->multiField_saveToDatabase('myFieldData', 'my_fake_props');
    }
}