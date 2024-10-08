<?php

/**
 * This file is generated by architect.
 */

namespace ShopGroups\Shops\v1\AbstractEndpoints;

abstract class ShopGroupGetConsumerShopsAbstractEndpoint
{
	public const GRPC_INTERFACE_NAME = 'ShopGroups\Shops\v1\ShopGroupInterface';
	public const GRPC_INTERFACE_METHOD_NAME = 'GetConsumerShops';
	public const GRPC_INTERFACE_REALIZATION = '\ShopGroups\Shops\v1\InnerGrpcControllers\ShopGroupInnerController';
	public const GRPC_INPUT_TYPE = '\ShopGroups\Shops\v1\GetConsumerShopsRequest';
	public const GRPC_OUTPUT_TYPE = '\ShopGroups\Shops\v1\ShopGroupsResponse';
	public const GRPC_ROUTE = 'ecom.shop_group_service.v1.ShopGroup.GetConsumerShops';

	public array $context;
	private array $preInterceptors = [];
	private array $postInterceptors = [];


	public function addPreInterceptor(\Test\PhpServicesBundle\PreInterceptorInterface $interceptor)
	{
		$this->preInterceptors[] = $interceptor;
	}


	public function addPostInterceptor(\Test\PhpServicesBundle\PostInterceptorInterface $interceptor)
	{
		$this->postInterceptors[] = $interceptor;
	}


	public function __invoke(\ShopGroups\Shops\v1\GetConsumerShopsRequest $dto): \ShopGroups\Shops\v1\ShopGroupsResponse
	{
		foreach ($this->preInterceptors as $interceptor) {
		    /** @var \Test\PhpServicesBundle\PreInterceptorInterface $interceptor */
		      $interceptor->intercept($this->context, $dto);
		}
		$result = $this->run($dto);
		foreach ($this->postInterceptors as $interceptor) {
		    /** @var \Test\PhpServicesBundle\PostInterceptorInterface $interceptor */
		      $interceptor->intercept($this->context, $result, $result);
		}
		return $result;
	}


	abstract protected function run(
		\ShopGroups\Shops\v1\GetConsumerShopsRequest $dto,
	): \ShopGroups\Shops\v1\ShopGroupsResponse;
}
