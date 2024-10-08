<?php

/**
 * This file is generated by architect.
 */

namespace ShopGroups\Shops\v1;

/**
 * Generated from protobuf message <code>ecom.shop_group_service.v1.GetShopGroupByShopGroupCodeResponse</code>
 */
class GetShopGroupByShopGroupCodeResponse extends \Google\Protobuf\Internal\Message implements \JsonSerializable
{
	/** Generated from protobuf field <code>.ecom.shop_group_service.v1.ShopGroupModel item = 1;</code> */
	private $item = null;


	/**
	 * Constructor.
	 *
	 * @param array $data {
	 *     Optional. Data for populating the Message object.
	 *
	 *     //type \ShopGroups\Shops\v1\ShopGroupModel $item
	 * }
	 */
	public function __construct($data = NULL)
	{
		Meta\EcomShopGroupServiceV1::initOnce();
		parent::__construct($data);
	}


	/**
	 * Generated from protobuf field <code>.ecom.shop_group_service.v1.ShopGroupModel item = 1;</code>
	 * @return \ShopGroups\Shops\v1\ShopGroupModel
	 */
	public function getItem()
	{
		return $this->item;
	}


	/**
	 * Generated from protobuf field <code>.ecom.shop_group_service.v1.ShopGroupModel item = 1;</code>
	 * @param \ShopGroups\Shops\v1\ShopGroupModel $var
	 * @return $this
	 */
	public function setItem($var)
	{
		\Google\Protobuf\Internal\GPBUtil::checkMessage($var, ShopGroupModel::class);
		$this->item = $var;

		return $this;
	}


	/**
	 * This method created only for OA doc generation
	 */
	public function jsonSerialize(): mixed
	{
		$result = [];
		if (is_object($this->getItem()) && method_exists($this->getItem(), 'jsonSerialize')) {
		                        $result['item'] = $this->getItem()->jsonSerialize();
		                    } else {
		                        $result['item'] = $this->getItem();
		                    }return $result;
	}
}
