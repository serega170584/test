<?php

/**
 * This file is generated by architect.
 */

namespace ShopGroups\EchoService\v1;

use ShopGroups\HttpClientErrorObjects\UnprocessableEntityException;
use ShopGroups\HttpClientErrorObjects\UnresolvedRequestException;
use Spiral\RoadRunner\GRPC\ContextInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class EchoServiceHttpClient implements EchoServiceInterface
{
    public const ERROR_CODES_MATCHES_CLASSES = [422 => 'ShopGroups\HttpClientErrorObjects\UnprocessableEntityException'];

    private HttpClientInterface $client;
    private string $host;


    public function __construct(HttpClientInterface $client, string $host)
    {
        if ($client instanceof \ApiPlatform\Symfony\Bundle\Test\Client) {
            putenv('RR_MODE=http');
        }
        $this->client = $client;

        $this->host = rtrim($host, "/");
    }


    public function EchoMethod(ContextInterface $ctx, EchoRequest $in): EchoResponse
    {
        $result = $this->client->request("POST", $this->host."/api/v1/echo", [
        "body"=>$in->serializeToJsonString(),
        "headers"=>array_merge($ctx->getValues(), [
        "Accept"=>"application/json",
        "Content-type"=>"application/json"
        ])
        ]);

        try {
            $resultString = $result->getContent();
        } catch (\Throwable $e) {
            $this->throwError(json_decode($e->getResponse()->getContent(false), true), $e->getCode());
        }
        if ($result->getStatusCode()===200) {
            return new \ShopGroups\EchoService\v1\EchoResponse(json_decode($resultString, true));
        } else {
            $this->throwError(json_decode($resultString, true), $result->getStatusCode());
        }
    }


    private function throwError(array $response, int $httpCode)
    {
        if (isset(self::ERROR_CODES_MATCHES_CLASSES[$httpCode])) {
            $exceptionClass = self::ERROR_CODES_MATCHES_CLASSES[$httpCode];
            $exception = new $exceptionClass();
            foreach ($response as $key=>$value) {
                if (property_exists($exception, $key)) {
                    $exception->$key = $value;
                }
            }
            throw $exception;
        } else {
            $exception = new UnresolvedRequestException();
            $exception->httpCode = $httpCode;
            $exception->error = $response;
            throw $exception;
        }
    }
}
