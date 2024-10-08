<?php

/**
 * This file is generated by architect.
 */

namespace ShopGroups\Shops\v1;

/**
 * Generated from protobuf message <code>ecom.shop_group_service.v1.GetShopGroupByShopCodeResponse</code>
 */
class GetShopGroupByShopCodeResponse extends \Google\Protobuf\Internal\Message implements \JsonSerializable
{
	/** Generated from protobuf field <code>string ShopGroupCode = 1;</code> */
	private $ShopGroupCode = '';

	/** Generated from protobuf field <code>string Title = 2;</code> */
	private $Title = '';


	/**
	 * Constructor.
	 *
	 * @param array $data {
	 *     Optional. Data for populating the Message object.
	 *
	 *     //type string $ShopGroupCode
	 *     //type string $Title
	 * }
	 */
	public function __construct($data = NULL)
	{
		Meta\EcomShopGroupServiceV1::initOnce();
		parent::__construct($data);
	}


	/**
	 * Generated from protobuf field <code>string ShopGroupCode = 1;</code>
	 * @return string
	 */
	public function getShopGroupCode()
	{
		return $this->ShopGroupCode;
	}


	/**
	 * Generated from protobuf field <code>string ShopGroupCode = 1;</code>
	 * @param string $var
	 * @return $this
	 */
	public function setShopGroupCode($var)
	{
		\Google\Protobuf\Internal\GPBUtil::checkString($var, True);
		$this->ShopGroupCode = $var;

		return $this;
	}


	/**
	 * Generated from protobuf field <code>string Title = 2;</code>
	 * @return string
	 */
	public function getTitle()
	{
		return $this->Title;
	}


	/**
	 * Generated from protobuf field <code>string Title = 2;</code>
	 * @param string $var
	 * @return $this
	 */
	public function setTitle($var)
	{
		\Google\Protobuf\Internal\GPBUtil::checkString($var, True);
		$this->Title = $var;

		return $this;
	}


	/**
	 * This method created only for OA doc generation
	 */
	public function jsonSerialize(): mixed
	{
		$result = [];
		if (is_object($this->getShopGroupCode()) && method_exists($this->getShopGroupCode(), 'jsonSerialize')) {
		                        $result['ShopGroupCode'] = $this->getShopGroupCode()->jsonSerialize();
		                    } else {
		                        $result['ShopGroupCode'] = $this->getShopGroupCode();
		                    }if (is_object($this->getTitle()) && method_exists($this->getTitle(), 'jsonSerialize')) {
		                        $result['Title'] = $this->getTitle()->jsonSerialize();
		                    } else {
		                        $result['Title'] = $this->getTitle();
		                    }return $result;
	}
}
