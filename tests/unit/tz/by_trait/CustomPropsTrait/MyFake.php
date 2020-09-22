<?php


namespace app\tests\unit\tz\by_trait\CustomPropsTrait;


use app\tz\by_trait\CustomPropsTrait;
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