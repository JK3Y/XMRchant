<?php

namespace JK3Y\Xmrchant\RPC;

class RPCAccount
{
    private $wallet_rpc;
    public  $index;
    public  $addresses;
    public  $balances;
    public  $active_address;
//    public  $incoming;
//    public  $outgoing;

    public function __construct(JSONRPCWallet $wallet_rpc, $index)
    {
        $this->wallet_rpc = $wallet_rpc;
        $this->index = $index;
        $this->addresses = $this->addresses();
        $this->balances = $this->balances();
        $this->active_address = $this->address();
//        $this->incoming = $this->incoming();
//        $this->outgoing = (new RPCPaymentManager($wallet_rpc, $index, 'out'))->fetch();
    }

    public function balances()
    {
        return $this->wallet_rpc->balances($this->index);
    }

    public function balance($unlocked = false)
    {
        return $unlocked ? $this->wallet_rpc->balances($this->index)['unlocked_balance'] : $this->wallet_rpc->balances($this->index)['balance'];
    }

    public function address()
    {
        return array_last($this->addresses);
    }

    public function addresses()
    {
        return $this->wallet_rpc->addresses($this->index);
    }

    public function new_address($label = null)
    {
        return $this->wallet_rpc->new_address($this->index, $label);
    }

    public function incoming()
    {
        $pm = new RPCPaymentManager($this->wallet_rpc, $this->index, 'in');
        return $pm->fetch();
    }

    public function outgoing()
    {
        $pm = new RPCPaymentManager($this->wallet_rpc, $this->index, 'out');
        return $pm->fetch();
    }

//    public function transfer($address, $amount, $priority = 'normal', $ringsize = 5, $payment_id = null, $unlock_time = 0, $relay = true)
//    {
//        // TODO: Add Prio class
//
//        return $this->wallet_rpc->transfer(
//            [$address, $amount],
//            $priority,
//            $ringsize,
//            $payment_id,
//            $unlock_time,
//            $this->index,
//            $relay
//        );
//    }
//
//    public function transfer_multiple(Array $destinations, $priority = 'normal', $ringsize = 5, $payment_id = null, $unlock_time = 0, $relay = true)
//    {
//        // TODO: Add Prio class
//
//        return $this->wallet_rpc->transfer(
//            $destinations,
//            $priority,
//            $ringsize,
//            $payment_id,
//            $unlock_time,
//            $this->index,
//            $relay
//        );
//    }
}