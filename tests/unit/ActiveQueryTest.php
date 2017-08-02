<?php
/**
 * Created by PhpStorm.
 * User: bigdrop
 * Date: 02.08.17
 * Time: 14:05
 */

namespace sokyrko\yii\salesforce\tests\unit;

use Codeception\Test\Unit;
use PHPUnit\Framework\Assert;
use sokyrko\yii\salesforce\tests\Airport;

/**
 * Class ActiveQueryTest
 *
 * @package sokyrko\yii\salesforce\tests\unit
 */
class ActiveQueryTest extends Unit
{
    public function testSelect()
    {
        Assert::assertEquals('SELECT Id, Name FROM Airport__c', Airport::find()->getRawQuery());
        Assert::assertEquals('SELECT Name FROM Airport__c', Airport::find()->select(['Name'])->getRawQuery());
    }

    public function testWhere()
    {
        Assert::assertEquals(
            'SELECT Id, Name FROM Airport__c WHERE Name = \'Test\'',
            Airport::find()->where(['Name' => 'Test'])->getRawQuery()
        );

        Assert::assertEquals(
            'SELECT Id, Name FROM Airport__c WHERE Name != \'Test\'',
            Airport::find()->where(['!=', 'Name', 'Test'])->getRawQuery()
        );

        Assert::assertEquals(
            'SELECT Id, Name FROM Airport__c WHERE Name LIKE \'Test\'',
            Airport::find()->where(['like', 'Name', 'Test'])->getRawQuery()
        );

        Assert::assertEquals(
            'SELECT Id, Name FROM Airport__c WHERE Name = "Test"',
            Airport::find()->where('Name = "Test"')->getRawQuery()
        );
    }

    public function testAndWhere()
    {
        Assert::assertEquals(
            'SELECT Id, Name FROM Airport__c WHERE Name = \'Test\' AND Name != \'Not a Test\'',
            Airport::find()->where(['Name' => 'Test'])->andWhere(['!=', 'Name', 'Not a Test'])->getRawQuery()
        );
    }

    public function testWithOrderBy()
    {
        Assert::assertEquals(
            'SELECT Id, Name FROM Airport__c ORDER BY Id ASC',
            Airport::find()->orderBy(['Id' => SORT_ASC])->getRawQuery()
        );
    }

    public function testWhereAndOrderBy()
    {
        Assert::assertEquals(
            'SELECT Id, Name FROM Airport__c WHERE Name = \'Test\' ORDER BY Id ASC',
            Airport::find()->where(['Name' => 'Test'])->orderBy(['Id' => SORT_ASC])->getRawQuery()
        );
    }
}
