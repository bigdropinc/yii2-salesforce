<?php
/**
 * Created by PhpStorm.
 * User: bigdrop
 * Date: 02.08.17
 * Time: 13:47
 */

namespace sokyrko\yii\salesforce\tests;

use sokyrko\yii\salesforce\data\ActiveRecord;

/**
 * Class Airport
 *
 * @package _support
 */
class Airport extends ActiveRecord
{
    /** @var string */
    public $Id;

    /** @var string */
    public $Name;
}
