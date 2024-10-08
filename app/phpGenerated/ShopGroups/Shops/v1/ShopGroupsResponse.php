<?php

/**
 * This file is generated by architect.
 */

namespace ShopGroups\Shops\v1;

/**
 * Generated from protobuf message <code>ecom.shop_group_service.v1.ShopGroupsResponse</code>
 */
class ShopGroupsResponse extends \Google\Protobuf\Internal\Message implements \JsonSerializable
{
	/** @var \ShopGroups\Shops\v1\ShopGroupItem[]|\Google\Protobuf\Internal\RepeatedField Items */
	private $Items;


	/**
	 * Constructor.
	 *
	 * @param array $data {
	 *     Optional. Data for populating the Message object.
	 *
	 *     //type \ShopGroups\Shops\v1\ShopGroupItem[]|\Google\Protobuf\Internal\RepeatedField $Items
	 * }
	 */
	public function __construct($data = NULL)
	{
		Meta\EcomShopGroupServiceV1::initOnce();
		parent::__construct($data);
	}


	/**
	 * @return \ShopGroups\Shops\v1\ShopGroupItem[]|\Google\Protobuf\Internal\RepeatedField
	 */
	public function getItems(): array|\Google\Protobuf\Internal\RepeatedField
	{
		return $this->Items;
	}


	/**
	 * Generated from protobuf field <code>repeated .ecom.shop_group_service.v1.ShopGroupItem Items = 1;</code>
	 * @param \ShopGroups\Shops\v1\ShopGroupItem[]|\Google\Protobuf\Internal\RepeatedField $var
	 * @return $this
	 */
	public function setItems(array|\Google\Protobuf\Internal\RepeatedField $var)
	{
		$arr = \Google\Protobuf\Internal\GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::MESSAGE, ShopGroupItem::class);
		$this->Items = $arr;

		return $this;
	}


	/**
	 * This method created only for OA doc generation
	 */
	public function jsonSerialize(): mixed
	{
		$result = [];
		if (($this->getItems()->count())==0) {
		   $createdArray = [
		       new \ShopGroups\Shops\v1\ShopGroupItem(),
		       new \ShopGroups\Shops\v1\ShopGroupItem(),
		    ];
		$this->setItems($createdArray);
		}
		foreach ($this->getItems() as $item) {
		   if (is_object($item) && method_exists($item, 'jsonSerialize')) {
		        $result['Items'][] = $item->jsonSerialize();
		    } else {
		        $result['Items'][] = $item;
		    }
		}return $result;
	}
}
