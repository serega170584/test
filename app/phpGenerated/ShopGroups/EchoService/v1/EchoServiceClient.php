<?php
// GENERATED CODE -- DO NOT EDIT!

namespace ShopGroups\EchoService\v1;

/**
 */
class EchoServiceClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \ShopGroups\EchoService\v1\EchoRequest $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     * @return \Grpc\UnaryCall
     */
    public function EchoMethod(\ShopGroups\EchoService\v1\EchoRequest $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/echo.v1.EchoService/EchoMethod',
        $argument,
        ['\ShopGroups\EchoService\v1\EchoResponse', 'decode'],
        $metadata, $options);
    }

}
