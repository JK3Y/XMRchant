<?php

namespace JK3Y\Xmrchant;

use JK3Y\Xmrchant\Models\Account;
use JK3Y\Xmrchant\RPC\JSONRPCWallet;
use JK3Y\Xmrchant\RPC\RPCAccount;

class Xmrchant
{
    protected $wallet_rpc;
    private $accounts = [];

    public function __construct(JSONRPCWallet $wallet_rpc)
    {
        $this->wallet_rpc = $wallet_rpc;
        $this->refresh();
    }

    public function refresh()
    {
        $this->accounts = $this->accounts ?? [];
        $index = 0;
        foreach ($this->accounts() as $account) {
            if (!array_has($this->accounts, $index)) {
                array_push($this->accounts, $account);
            }
            $index++;
        }
    }

    public function wallet()
    {
        return $this->wallet_rpc;
    }

    public function height()
    {
        return $this->wallet_rpc->height();
    }

    public function account($account_index = 0)
    {
        return new RPCAccount($this->wallet_rpc, $account_index);
    }

    public function accounts()
    {
        return $this->wallet_rpc->accounts();
    }

    public function new_account($user_id, $label = null)
    {
        $acct = $this->wallet_rpc->new_account($label);
        return Account::create([
            'id' => $acct->index,
            'user_id' => $user_id
        ]);
    }

    public function address()
    {
        return $this->wallet_rpc->addresses();
    }

    public function addresses($account_index = 0)
    {
        return $this->wallet_rpc->addresses($account_index);
    }

    public function balance($account_index = 0)
    {
        return $this->wallet_rpc->balances($account_index);
    }

    public function balances()
    {
        return $this->wallet_rpc->balances();
    }

    public function get_bulk_payments(Array $txids, $min_block_height = null)
    {
        return $this->wallet_rpc->get_bulk_payments($txids, $min_block_height);
    }

    public function get_transfers($account_index = 0, $filterOptions = [])
    {
        return $this->wallet_rpc->get_transfers(
            $filterOptions['in']                ?? false,
            $filterOptions['out']               ?? false,
            $filterOptions['pending']           ?? false,
            $filterOptions['failed']            ?? false,
            $filterOptions['pool']              ?? false,
            $filterOptions['filter_by_height']  ?? true,
            $account_index
        );
    }

    public function get_transfer_by_txid($txid)
    {
        return $this->wallet_rpc->get_transfer_by_txid($txid);
    }

    public function transfer($address, $amount, $priority = 2, $ringsize = 5, $payment_id = null, $unlock_time = 0, $relay = true)
    {
        return $this->accounts[0].$this->transfer(
            $address,
            $amount,
            $priority,
            $ringsize,
            $payment_id,
            $unlock_time,
            $relay
        );
    }

    public function userHasAccount($user_id)
    {
        $query = Account::where('user_id', '=', $user_id)->first();

        if (!$query) return false;

        $account = $this->account($query->id);

        if ($account->active_address->isUsed()) {
            $account->new_address();
        }

        return $this->account($query->id);
    }

    public function fromAtomic($amount)
    {
        return $amount * 1e-12;
    }

    public function toAtomic($amount)
    {
        return $amount * 1e12;
    }
}