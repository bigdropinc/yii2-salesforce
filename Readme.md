Yii2 Salesforce Integration
================

This extension allow you to integrate your Yii2 application with SalesForce.com
via regular ActiveRecordInterface.

Installation
------------

Run

```
composer require --prefer-dist sokyrko/yii2-salesforce
```

How to use
----------

- Define `salesforce` component:

```php
<?php
return [
    'components' => [
        'salesforce' => [
            'class'          => '\sokyrko\yii\salesforce\components\SalesforceComponent',
            'consumerKey'    => 'applicationConsumerKey',
            'consumerSecret' => 'applicationConsumerSecret',
            'username'       => 'salesForceLogin',
            'password'       => 'salesForcePassword' . 'salesForceAccountSecretKey',
            'loginUrl'       => 'salesForceLoginUrl', // eg: https://login.salesforce.com/
        ],
    ],
];
```

- Define salesforce entity with public fields:

```php
<?php

namespace console\models\salesforce;

use sokyrko\yii\salesforce\data\ActiveRecord;

class Account extends ActiveRecord
{
    protected static $isCustom = false; // set false if is not custom model
    
    /** @var string */
    public $Id;
    
    /** @var string */
    public $Name;
}
```

- Use same as Yii2 ActiveRecord:

```php
<?php

$account = console\models\salesforce\Account::findOne(['Name' => 'My name']);

var_dump($account); // {Id: 'some-salesforce-id', Name: 'My name'}

```

You can see more examples for query builder in tests.

TBD
---

- Create, update, delete records
- Complete ActiveRecord tests
