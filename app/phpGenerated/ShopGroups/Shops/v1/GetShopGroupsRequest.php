<?php

/**
 * This file is generated by architect.
 */

namespace ShopGroups\Shops\v1;

/**
 * Generated from protobuf message <code>ecom.shop_group_service.v1.GetShopGroupsRequest</code>
 */
class GetShopGroupsRequest extends \Google\Protobuf\Internal\Message implements \JsonSerializable
{
	/** Generated from protobuf field <code>string consumerCode = 1;</code> */
	private $consumerCode = '';

	/** @var string[]|\Google\Protobuf\Internal\RepeatedField shopGroupCodes */
	private $shopGroupCodes;

	/** @var string[]|\Google\Protobuf\Internal\RepeatedField fiasIds */
	private $fiasIds;

	/** Generated from protobuf field <code>string consumerVersion = 4;</code> */
	private $consumerVersion = '';


	/**
	 * Constructor.
	 *
	 * @param array $data {
	 *     Optional. Data for populating the Message object.
	 *
	 *     //type string $consumerCode
	 *     //type string[]|\Google\Protobuf\Internal\RepeatedField $shopGroupCodes
	 *     //type string[]|\Google\Protobuf\Internal\RepeatedField $fiasIds
	 *     //type string $consumerVersion
	 * }
	 */
	public function __construct($data = NULL)
	{
		Meta\EcomShopGroupServiceV1::initOnce();
		parent::__construct($data);
	}


	/**
	 * Generated from protobuf field <code>string consumerCode = 1;</code>
	 * @return string
	 */
	public function getConsumerCode()
	{
		return $this->consumerCode;
	}


	/**
	 * Generated from protobuf field <code>string consumerCode = 1;</code>
	 * @param string $var
	 * @return $this
	 */
	public function setConsumerCode($var)
	{
		\Google\Protobuf\Internal\GPBUtil::checkString($var, True);
		$this->consumerCode = $var;

		return $this;
	}


	/**
	 * @return string[]|\Google\Protobuf\Internal\RepeatedField
	 */
	public function getShopGroupCodes(): array|\Google\Protobuf\Internal\RepeatedField
	{
		return $this->shopGroupCodes;
	}


	/**
	 * Generated from protobuf field <code>repeated string shopGroupCodes = 2;</code>
	 * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
	 * @return $this
	 */
	public function setShopGroupCodes(array|\Google\Protobuf\Internal\RepeatedField $var)
	{
		$arr = \Google\Protobuf\Internal\GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
		$this->shopGroupCodes = $arr;

		return $this;
	}


	/**
	 * @return string[]|\Google\Protobuf\Internal\RepeatedField
	 */
	public function getFiasIds(): array|\Google\Protobuf\Internal\RepeatedField
	{
		return $this->fiasIds;
	}


	/**
	 * Generated from protobuf field <code>repeated string fiasIds = 3;</code>
	 * @param string[]|\Google\Protobuf\Internal\RepeatedField $var
	 * @return $this
	 */
	public function setFiasIds(array|\Google\Protobuf\Internal\RepeatedField $var)
	{
		$arr = \Google\Protobuf\Internal\GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
		$this->fiasIds = $arr;

		return $this;
	}


	/**
	 * Generated from protobuf field <code>string consumerVersion = 4;</code>
	 * @return string
	 */
	public function getConsumerVersion()
	{
		return $this->consumerVersion;
	}


	/**
	 * Generated from protobuf field <code>string consumerVersion = 4;</code>
	 * @param string $var
	 * @return $this
	 */
	public function setConsumerVersion($var)
	{
		\Google\Protobuf\Internal\GPBUtil::checkString($var, True);
		$this->consumerVersion = $var;

		return $this;
	}


	/**
	 * This method created only for OA doc generation
	 */
	public function jsonSerialize(): mixed
	{
		$result = [];
		if (is_object($this->getConsumerCode()) && method_exists($this->getConsumerCode(), 'jsonSerialize')) {
		                        $result['consumerCode'] = $this->getConsumerCode()->jsonSerialize();
		                    } else {
		                        $result['consumerCode'] = $this->getConsumerCode();
		                    }if (($this->getShopGroupCodes()->count())==0) {
		   $createdArray = [
		       "str1",
		       "str2"
		    ];
		$this->setshopGroupCodes($createdArray);
		}
		foreach ($this->getShopGroupCodes() as $item) {
		   if (is_object($item) && method_exists($item, 'jsonSerialize')) {
		        $result['shopGroupCodes'][] = $item->jsonSerialize();
		    } else {
		        $result['shopGroupCodes'][] = $item;
		    }
		}if (($this->getFiasIds()->count())==0) {
		   $createdArray = [
		       "str1",
		       "str2"
		    ];
		$this->setfiasIds($createdArray);
		}
		foreach ($this->getFiasIds() as $item) {
		   if (is_object($item) && method_exists($item, 'jsonSerialize')) {
		        $result['fiasIds'][] = $item->jsonSerialize();
		    } else {
		        $result['fiasIds'][] = $item;
		    }
		}if (is_object($this->getConsumerVersion()) && method_exists($this->getConsumerVersion(), 'jsonSerialize')) {
		                        $result['consumerVersion'] = $this->getConsumerVersion()->jsonSerialize();
		                    } else {
		                        $result['consumerVersion'] = $this->getConsumerVersion();
		                    }return $result;
	}
}
