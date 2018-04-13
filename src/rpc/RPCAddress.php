<?php

namespace JK3Y\Xmrchant\RPC;

class RPCAddress
{
    public $address;
    public $address_index;
    public $label;
    public $used;

    public function __construct($address)
    {
        $this->address = $address['address'];
        $this->address_index = $address['address_index'] ?? null;
        $this->label = $address['label'] ?? null;
        $this->used = $address['used'] ?? null;
    }

    public function isUsed()
    {
        return $this->used;
    }
}