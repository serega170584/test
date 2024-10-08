<?php

/**
 * This file is generated by architect.
 */

namespace ShopGroups\Shops\v1;

/**
 * Generated from protobuf message <code>ecom.shop_group_service.v1.GetShopGroupByFiasIdRequest</code>
 */
class GetShopGroupByFiasIdRequest extends \Google\Protobuf\Internal\Message implements \JsonSerializable
{
	/** Generated from protobuf field <code>string FiasId = 1;</code> */
	private $FiasId = '';

	/** Generated from protobuf field <code>string ConsumerCode = 2;</code> */
	private $ConsumerCode = '';

	/** Generated from protobuf field <code>string ConsumerVersion = 3;</code> */
	private $ConsumerVersion = '';


	/**
	 * Constructor.
	 *
	 * @param array $data {
	 *     Optional. Data for populating the Message object.
	 *
	 *     //type string $FiasId
	 *     //type string $ConsumerCode
	 *     //type string $ConsumerVersion
	 * }
	 */
	public function __construct($data = NULL)
	{
		Meta\EcomShopGroupServiceV1::initOnce();
		parent::__construct($data);
	}


	/**
	 * Generated from protobuf field <code>string FiasId = 1;</code>
	 * @return string
	 */
	public function getFiasId()
	{
		return $this->FiasId;
	}


	/**
	 * Generated from protobuf field <code>string FiasId = 1;</code>
	 * @param string $var
	 * @return $this
	 */
	public function setFiasId($var)
	{
		\Google\Protobuf\Internal\GPBUtil::checkString($var, True);
		$this->FiasId = $var;

		return $this;
	}


	/**
	 * Generated from protobuf field <code>string ConsumerCode = 2;</code>
	 * @return string
	 */
	public function getConsumerCode()
	{
		return $this->ConsumerCode;
	}


	/**
	 * Generated from protobuf field <code>string ConsumerCode = 2;</code>
	 * @param string $var
	 * @return $this
	 */
	public function setConsumerCode($var)
	{
		\Google\Protobuf\Internal\GPBUtil::checkString($var, True);
		$this->ConsumerCode = $var;

		return $this;
	}


	/**
	 * Generated from protobuf field <code>string ConsumerVersion = 3;</code>
	 * @return string
	 */
	public function getConsumerVersion()
	{
		return $this->ConsumerVersion;
	}


	/**
	 * Generated from protobuf field <code>string ConsumerVersion = 3;</code>
	 * @param string $var
	 * @return $this
	 */
	public function setConsumerVersion($var)
	{
		\Google\Protobuf\Internal\GPBUtil::checkString($var, True);
		$this->ConsumerVersion = $var;

		return $this;
	}


	/**
	 * This method created only for OA doc generation
	 */
	public function jsonSerialize(): mixed
	{
		$result = [];
		if (is_object($this->getFiasId()) && method_exists($this->getFiasId(), 'jsonSerialize')) {
		                        $result['FiasId'] = $this->getFiasId()->jsonSerialize();
		                    } else {
		                        $result['FiasId'] = $this->getFiasId();
		                    }if (is_object($this->getConsumerCode()) && method_exists($this->getConsumerCode(), 'jsonSerialize')) {
		                        $result['ConsumerCode'] = $this->getConsumerCode()->jsonSerialize();
		                    } else {
		                        $result['ConsumerCode'] = $this->getConsumerCode();
		                    }if (is_object($this->getConsumerVersion()) && method_exists($this->getConsumerVersion(), 'jsonSerialize')) {
		                        $result['ConsumerVersion'] = $this->getConsumerVersion()->jsonSerialize();
		                    } else {
		                        $result['ConsumerVersion'] = $this->getConsumerVersion();
		                    }return $result;
	}
}
