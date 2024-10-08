<?php

namespace App\Tests\EchoClient;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use Test\PhpServicesBundle\TestHelpers\BaseTestClass;
use ShopGroups\EchoService\v1\EchoRequest;
use ShopGroups\EchoService\v1\EchoServiceHttpClient;
use Spiral\RoadRunner\GRPC\Context;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EchoHttpClientTest extends ApiTestCase
{


    /**
     * @group ignore
     */
    public function testSampleEchoClientTest() {

        $httpClient = static::createClient();

        $client = new EchoServiceHttpClient($httpClient, '');
        $requestMessageString = 'valeravaleravaleravalera';
        $result = $client->EchoMethod(new Context([]),
            (new EchoRequest())->setMessage($requestMessageString)
        );
        $this->assertEquals('Echoed!!!!!  :::  ' . $requestMessageString, $result->getMessage());

    }

}
