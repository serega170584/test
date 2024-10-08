<?php

/**
 * This file is generated by architect.
 */

namespace App\Endpoints\EchoService\v1;

use App\Bus\BusManager;
use App\UseCase\Query\EchoQueries\EchoQuery;
use Test\PhpServicesBundle\GrpcAttributes\GrpcService;
use Test\PhpServicesBundle\MessageValidationInterceptor;
use OpenApi\Attributes\JsonContent;
use OpenApi\Attributes\RequestBody;
use OpenApi\Attributes\Response;
use ShopGroups\EchoService\v1\AbstractEndpoints\EchoServiceEchoMethodAbstractEndpoint;
use ShopGroups\EchoService\v1\EchoRequest;
use ShopGroups\EchoService\v1\EchoResponse;
use Symfony\Component\Routing\Annotation\Route;

class EchoServiceEchoMethodEndpoint extends EchoServiceEchoMethodAbstractEndpoint
{
    private BusManager $bus;

    public function __construct(BusManager $bus)
    {
        $this->bus = $bus;
    }

    #[Route('/api/v1/echo', name: 'echo', methods: ['POST'])]
    #[GrpcService]
    #[MessageValidationInterceptor]
    #[RequestBody(content: new JsonContent(example: '{"Message":""}'))]
    #[Response(response: 200, description: 'OK', content: new JsonContent(example: '{"Message":""}'))]
    #[Response(
        response: 422,
        description: '',
        content: new JsonContent(example: '{"title":"Validation Error","detail":["some field is incorrect","some field is incorrect also"]}'),
    )]
    protected function run(EchoRequest $dto): EchoResponse
    {
        $result = $this->bus->askEchoQuery(new EchoQuery($dto->getMessage()));
        $response = new EchoResponse();
        $response->setMessage($result);

        return $response;
    }
}
