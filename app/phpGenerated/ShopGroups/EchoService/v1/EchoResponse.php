<?php

/**
 * This file is generated by architect.
 */

namespace ShopGroups\EchoService\v1;

/**
 * Generated from protobuf message <code>echo.v1.EchoResponse</code>
 */
class EchoResponse extends \Google\Protobuf\Internal\Message implements \JsonSerializable
{
	/** Generated from protobuf field <code>string Message = 1;</code> */
	private $Message = '';


	/**
	 * Constructor.
	 *
	 * @param array $data {
	 *     Optional. Data for populating the Message object.
	 *
	 *     //type string $Message
	 * }
	 */
	public function __construct($data = NULL)
	{
		Meta\EchoV1::initOnce();
		parent::__construct($data);
	}


	/**
	 * Generated from protobuf field <code>string Message = 1;</code>
	 * @return string
	 */
	public function getMessage()
	{
		return $this->Message;
	}


	/**
	 * Generated from protobuf field <code>string Message = 1;</code>
	 * @param string $var
	 * @return $this
	 */
	public function setMessage($var)
	{
		\Google\Protobuf\Internal\GPBUtil::checkString($var, True);
		$this->Message = $var;

		return $this;
	}


	/**
	 * This method created only for OA doc generation
	 */
	public function jsonSerialize(): mixed
	{
		$result = [];
		if (is_object($this->getMessage()) && method_exists($this->getMessage(), 'jsonSerialize')) {
		                        $result['Message'] = $this->getMessage()->jsonSerialize();
		                    } else {
		                        $result['Message'] = $this->getMessage();
		                    }return $result;
	}
}
