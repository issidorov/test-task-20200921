<?php


namespace app\tests\unit\tz;

require_once __DIR__ . '/BaseTestUnit.php';
use app\tests\unit\tz\_model_by_trait\MyFake;


class CustomPropsTraitTest extends BaseTestUnit
{
    protected function getFakeClassName(): string
    {
        return MyFake::class;
    }
}