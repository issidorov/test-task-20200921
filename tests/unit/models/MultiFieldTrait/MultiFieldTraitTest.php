<?php


namespace app\tests\unit\models\MultiFieldTrait;


use yii\db\Query;
use yii\db\Schema;

class MultiFieldTraitTest extends \Codeception\Test\Unit
{
    public function setUp(): void
    {
        parent::setUp();
        \Yii::$app->db->createCommand()
            ->createTable('my_fake', ['id' => Schema::TYPE_PK])
            ->execute();
        \Yii::$app->db->createCommand()
            ->createTable('my_fake_value', [
                'relation_id' => Schema::TYPE_INTEGER,
                'key' => Schema::TYPE_STRING,
                'value' => Schema::TYPE_STRING,
            ])
            ->execute();
    }

    public function testGetDataWithOneModel()
    {
        \Yii::$app->db->createCommand()
            ->insert('my_fake', ['id' => 1])
            ->execute();
        \Yii::$app->db->createCommand()
            ->batchInsert('my_fake_value', ['relation_id', 'key', 'value'], [
                [1, 'key1', 'value 1'],
                [1, 'key1', 'value 2'],
                [1, 'key2', 'value 3'],
            ])->execute();

        $model = MyFake::findOne(['id' => 1]);

        $excepted = [
            'key1' => ['value 1', 'value 2'],
            'key2' => ['value 3'],
        ];
        $actual = $model->myFieldData;
        $this->assertEquals($excepted, $actual);
    }

    public function testGetDataWithManyModels()
    {
        \Yii::$app->db->createCommand()
            ->insert('my_fake', ['id' => 1])
            ->execute();
        \Yii::$app->db->createCommand()
            ->batchInsert('my_fake_value', ['relation_id', 'key', 'value'], [
                [1, 'key1', 'value 1'],
                [1, 'key1', 'value 2'],
                [1, 'key2', 'value 3'],
            ])->execute();

        $models = MyFake::findAll(['id' => 1]);

        $excepted = [
            'key1' => ['value 1', 'value 2'],
            'key2' => ['value 3'],
        ];
        $actual = $models[0]->myFieldData;
        $this->assertEquals($excepted, $actual);
    }

    public function testCreate1()
    {
        $model = new MyFake();
        $model->id = 1;
        $model->myFieldData = [
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
            ->from('my_fake_value')
            ->all();
        $this->assertEquals($excepted, $actual);
    }

    public function testCreate2()
    {
        $model = new MyFake();
        $model->id = 1;
        $model->myFieldData['key1'] = ['value 1', 'value 2'];
        $model->myFieldData['key2'] = ['value 3'];
        $model->save();

        $excepted = [
            ['relation_id' => 1, 'key' => 'key1', 'value' => 'value 1'],
            ['relation_id' => 1, 'key' => 'key1', 'value' => 'value 2'],
            ['relation_id' => 1, 'key' => 'key2', 'value' => 'value 3'],
        ];
        $actual = (new Query())
            ->select(['relation_id', 'key', 'value'])
            ->from('my_fake_value')
            ->all();
        $this->assertEquals($excepted, $actual);
    }

    public function testDelete()
    {
        \Yii::$app->db->createCommand()
            ->insert('my_fake', ['id' => 1])
            ->execute();
        \Yii::$app->db->createCommand()
            ->batchInsert('my_fake_value', ['relation_id', 'key', 'value'], [
                [1, 'key1', 'value 1'],
                [1, 'key1', 'value 2'],
                [1, 'key2', 'value 3'],
            ])->execute();

        $model = MyFake::findOne(['id' => 1]);
        $model->myFieldData = [];
        $model->save();

        $excepted = [];
        $actual = (new Query())
            ->select(['relation_id', 'key', 'value'])
            ->from('my_fake_value')
            ->all();
        $this->assertEquals($excepted, $actual);
    }

    public function tearDown(): void
    {
        \Yii::$app->db->createCommand()
            ->dropTable('my_fake')
            ->execute();
        \Yii::$app->db->createCommand()
            ->dropTable('my_fake_value')
            ->execute();
        parent::tearDown();
    }
}