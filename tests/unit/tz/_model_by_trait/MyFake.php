<?php


namespace app\tests\unit\tz\_model_by_trait;


use app\tz\CustomPropsTrait;
use yii\db\ActiveRecord;

/**
 * Class MyFake
 * @property $id
 * @property array $custom_props
 */
class MyFake extends ActiveRecord
{
    use CustomPropsTrait;

    public function init()
    {
        parent::init();
        $this->customProps_init();
    }
}