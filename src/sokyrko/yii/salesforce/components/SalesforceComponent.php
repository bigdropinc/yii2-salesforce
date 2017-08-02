<?php
/**
 * Created by PhpStorm.
 * User: bigdrop
 * Date: 28.07.17
 * Time: 15:22
 */

namespace sokyrko\yii\salesforce\components;

use Akeneo\SalesForce\Authentification\AccessTokenGenerator;
use Akeneo\SalesForce\Connector\SalesForceClient;
use GuzzleHttp\Client;
use yii\base\Component;

/**
 * Class SalesforceComponent
 *
 * @package sokyrko\yii\salesforce\components
 */
class SalesforceComponent extends Component
{
    /** @var string */
    public $username;

    /** @var string */
    public $password;

    /** @var string */
    public $consumerKey;

    /** @var string */
    public $consumerSecret;

    /** @var string */
    public $loginUrl;

    /** @var SalesForceClient */
    protected $client;

    public function init()
    {
        $this->client = new SalesForceClient(
            $this->username,
            $this->password,
            $this->consumerKey,
            $this->consumerSecret,
            $this->loginUrl,
            new Client(),
            new AccessTokenGenerator()
        );
    }

    /**
     * @return SalesForceClient
     */
    public function getClient(): SalesForceClient
    {
        return $this->client;
    }
}
