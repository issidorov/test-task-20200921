<?php


namespace app\tests\unit\tz;


use yii\db\Query;
use yii\db\Schema;
use yii\helpers\ArrayHelper;
use app\tests\unit\tz\_model_by_behavior\MyFake as MyFakeByBehavior;
use app\tests\unit\tz\_model_by_trait\MyFake as MyFakeByTrait;

abstract class BaseTestUnit extends \Codeception\Test\Unit
{
    /**
     * @var MyFakeByBehavior|MyFakeByTrait|string
     */
    private string $fakeClassName;

    abstract protected function getFakeClassName(): string;

    public function setUp(): void
    {
        parent::setUp();
        $this->fakeClassName = $this->getFakeClassName();
        \Yii::$app->db->createCommand()
            ->createTable('my_fake', ['id' => Schema::TYPE_PK])
            ->execute();
        \Yii::$app->db->createCommand()
            ->createTable('my_fake_props', [
                'relation_id' => Schema::TYPE_INTEGER,
                'key' => Schema::TYPE_STRING,
                'value' => Schema::TYPE_STRING,
            ])
            ->execute();
    }

    public function tearDown(): void
    {
        \Yii::$app->db->createCommand()
            ->dropTable('my_fake')
            ->execute();
        \Yii::$app->db->createCommand()
            ->dropTable('my_fake_props')
            ->execute();
        parent::tearDown();
    }

    public function testRead()
    {
        \Yii::$app->db->createCommand()
            ->insert('my_fake', ['id' => 1])
            ->execute();
        \Yii::$app->db->createCommand()
            ->batchInsert('my_fake_props', ['relation_id', 'key', 'value'], [
                [1, 'key1', 'value 1'],
                [1, 'key1', 'value 2'],
                [1, 'key2', 'value 3'],
            ])->execute();

        $model = $this->fakeClassName::findOne(['id' => 1]);

        $excepted = [
            'key1' => ['value 1', 'value 2'],
            'key2' => ['value 3'],
        ];
        $actual = $model->custom_props;
        $this->assertEquals($excepted, $actual);
    }

    public function testInsert()
    {
        $model = new $this->fakeClassName();
        $model->id = 1;
        $model->custom_props = [
            'key1' => ['value 1', 'value 2'],
            'key2' => ['value 3'],
        ];
        $model->save();

        $excepted = [
            ['relation_id' => 1, 'key' => 'key1', 'value' => 'value 1'],
            ['relation_id' => 1, 'key' => 'key1', 'value' => 'value 2'],
            ['relation_id' => 1, 'key' => 'key2', 'value' => 'value 3'],
        ];
        $actual = (new Query())
            ->select(['relation_id', 'key', 'value'])
            ->from('my_fake_props')
            ->all();
        $this->assertEquals($excepted, $actual);
    }

    public function testUpdate()
    {
        \Yii::$app->db->createCommand()
            ->insert('my_fake', ['id' => 1])
            ->execute();
        \Yii::$app->db->createCommand()
            ->batchInsert('my_fake_props', ['relation_id', 'key', 'value'], [
                [1, 'key1', 'value 1'],
                [1, 'key1', 'value 2'],
                [1, 'key2', 'value 3'],
            ])->execute();

        $model = $this->fakeClassName::findOne(['id' => 1]);
        $model->custom_props = [
            'key7' => ['value 7'],
            'key8' => ['value 8', 'value 9'],
        ];
        $model->save();

        $excepted = [
            ['relation_id' => 1, 'key' => 'key7', 'value' => 'value 7'],
            ['relation_id' => 1, 'key' => 'key8', 'value' => 'value 8'],
            ['relation_id' => 1, 'key' => 'key8', 'value' => 'value 9'],
        ];
        $actual = (new Query())
            ->select(['relation_id', 'key', 'value'])
            ->from('my_fake_props')
            ->all();
        $this->assertEquals($excepted, $actual);
    }

    public function testDelete()
    {
        \Yii::$app->db->createCommand()
            ->insert('my_fake', ['id' => 1])
            ->execute();
        \Yii::$app->db->createCommand()
            ->batchInsert('my_fake_props', ['relation_id', 'key', 'value'], [
                [1, 'key1', 'value 1'],
                [1, 'key1', 'value 2'],
                [1, 'key2', 'value 3'],
            ])->execute();

        $model = $this->fakeClassName::findOne(['id' => 1]);
        $model->delete();

        $excepted = [];
        $actual = (new Query())
            ->select(['relation_id', 'key', 'value'])
            ->from('my_fake_props')
            ->all();
        $this->assertEquals($excepted, $actual);
    }

    /**
     * @param $params
     * @dataProvider dataSearch
     */
    public function testSearch($params)
    {
        \Yii::$app->db->createCommand()
            ->batchInsert('my_fake', ['id'], [
                [1],
                [2],
            ])
            ->execute();
        \Yii::$app->db->createCommand()
            ->batchInsert('my_fake_props', ['relation_id', 'key', 'value'], [
                [1, 'key1', 'value 1'],
                [1, 'key1', 'value 2'],
                [1, 'key2', 'value 3'],
                [2, 'key1', 'value 1'],
                [2, 'key2', 'value 4'],
                [2, 'key3', 'value 5'],
            ])->execute();

        $conditions = $params['conditions'];
        $expectedIds = $params['expectedIds'];

        $models = $this->fakeClassName::findByCustomProps($conditions)->all();

        $actualIds = ArrayHelper::getColumn($models, 'id');
        $this->assertEquals($expectedIds, $actualIds);
    }

    public function dataSearch()
    {
        return [
            'data_1' => [[
                'conditions' => [
                    'key1' => ['value 2']
                ],
                'expectedIds' => [1],
            ]],
            'data_2' => [[
                'conditions' => [
                    'key1' => ['value 1', 'value 2'],
                    'key2' => ['value 4'],
                ],
                'expectedIds' => [2],
            ]],
            'data_3' => [[
                'conditions' => [
                    'key2' => ['value 3', 'value 4']
                ],
                'expectedIds' => [1, 2],
            ]],
        ];
    }
}