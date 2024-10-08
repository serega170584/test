<?php
// GENERATED CODE -- DO NOT EDIT!

namespace ShopGroups\Shops\v1;

/**
 */
class ShopGroupClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \ShopGroups\Shops\v1\GetShopGroupByShopCodeRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function GetShopGroupByShopCode(\ShopGroups\Shops\v1\GetShopGroupByShopCodeRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/ecom.shop_group_service.v1.ShopGroup/GetShopGroupByShopCode',
        $argument,
        ['\ShopGroups\Shops\v1\GetShopGroupByShopCodeResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \ShopGroups\Shops\v1\GetShopGroupByFiasIdRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function GetShopGroupByFiasId(\ShopGroups\Shops\v1\GetShopGroupByFiasIdRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/ecom.shop_group_service.v1.ShopGroup/GetShopGroupByFiasId',
        $argument,
        ['\ShopGroups\Shops\v1\GetShopGroupByFiasIdResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \ShopGroups\Shops\v1\GetConsumerShopsRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function GetConsumerShops(\ShopGroups\Shops\v1\GetConsumerShopsRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/ecom.shop_group_service.v1.ShopGroup/GetConsumerShops',
        $argument,
        ['\ShopGroups\Shops\v1\ShopGroupsResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \ShopGroups\Shops\v1\ExportRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\ServerStreamingCall
     */
    public function Export(\ShopGroups\Shops\v1\ExportRequest $argument,
      $metadata = [], $options = []) {
        return $this->_serverStreamRequest('/ecom.shop_group_service.v1.ShopGroup/Export',
        $argument,
        ['\ShopGroups\Shops\v1\ExportResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \ShopGroups\Shops\v1\ImportFileRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function ImportFile(\ShopGroups\Shops\v1\ImportFileRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/ecom.shop_group_service.v1.ShopGroup/ImportFile',
        $argument,
        ['\ShopGroups\Shops\v1\ImportFileResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \ShopGroups\Shops\v1\GenerateVirtualGroupsRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function GenerateVirtualGroups(\ShopGroups\Shops\v1\GenerateVirtualGroupsRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/ecom.shop_group_service.v1.ShopGroup/GenerateVirtualGroups',
        $argument,
        ['\ShopGroups\Shops\v1\GenerateVirtualGroupsResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \ShopGroups\Shops\v1\ProcessFailedMessagesRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function ProcessFailedMessages(\ShopGroups\Shops\v1\ProcessFailedMessagesRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/ecom.shop_group_service.v1.ShopGroup/ProcessFailedMessages',
        $argument,
        ['\ShopGroups\Shops\v1\ProcessFailedMessagesResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \ShopGroups\Shops\v1\GetFailedMessagesRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function GetFailedMessages(\ShopGroups\Shops\v1\GetFailedMessagesRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/ecom.shop_group_service.v1.ShopGroup/GetFailedMessages',
        $argument,
        ['\ShopGroups\Shops\v1\GetFailedMessagesResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \ShopGroups\Shops\v1\GetShopGroupsRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function GetShopGroups(\ShopGroups\Shops\v1\GetShopGroupsRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/ecom.shop_group_service.v1.ShopGroup/GetShopGroups',
        $argument,
        ['\ShopGroups\Shops\v1\GetShopGroupsResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \ShopGroups\Shops\v1\GetAllShopGroupsRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function GetAllShopGroups(\ShopGroups\Shops\v1\GetAllShopGroupsRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/ecom.shop_group_service.v1.ShopGroup/GetAllShopGroups',
        $argument,
        ['\ShopGroups\Shops\v1\GetAllShopGroupsResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \ShopGroups\Shops\v1\GetShopsRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function GetShops(\ShopGroups\Shops\v1\GetShopsRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/ecom.shop_group_service.v1.ShopGroup/GetShops',
        $argument,
        ['\ShopGroups\Shops\v1\GetShopsResponse', 'decode'],
        $metadata, $options);
    }

    /**
     * @param \ShopGroups\Shops\v1\GetShopGroupByShopGroupCodeRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function GetShopGroupByShopGroupCode(\ShopGroups\Shops\v1\GetShopGroupByShopGroupCodeRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/ecom.shop_group_service.v1.ShopGroup/GetShopGroupByShopGroupCode',
        $argument,
        ['\ShopGroups\Shops\v1\GetShopGroupByShopGroupCodeResponse', 'decode'],
        $metadata, $options);
    }

}
