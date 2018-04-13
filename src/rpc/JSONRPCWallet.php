<?php
// JSON RPC backend for Monero Wallet

namespace JK3Y\Xmrchant\RPC;

class JSONRPCWallet {
    private $url;
    private $client;
    private $_master_address = null;
    private $_addresses = null;

    public function __construct($protocol = 'http', $host = '127.0.0.1', $port, $user = '', $pass = '')
    {
        $this->url = $protocol . '://' . $host . ':' . $port . '/json_rpc';
        $this->user = $user;
        $this->password = $pass;
        $this->client = new \jsonRPCClient($this->url, $this->user, $this->password);
    }

    private function _run($method, $params = null)
    {
        return $this->client->_run($method, $params);
    }

    public function _transform($amount)
    {
        return $amount * 1e+12;
    }

    public function _print($json)
    {
        echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public function height()
    {
        return $this->_run('getheight');
    }

    public function accounts()
    {
        $accounts = [];
        $_accounts = $this->_run('get_accounts');

        $this->_master_address = array_first($_accounts['subaddress_accounts'])['base_address'];

        $idx = 0;
        foreach ($_accounts['subaddress_accounts'] as $_account) {
            assert($idx == $_account['account_index']);
            array_push($accounts, new RPCAccount($this, $_account['account_index']));
            $idx++;
        }

        return $accounts;
    }

    public function new_account($label = null)
    {
        $_account = $this->_run('create_account', array('label' => $label));
        // create subaddress on new account
        $this->new_address($_account['account_index']);
        return new RPCAccount($this, $_account['account_index']);
    }

    public function addresses($account_index = 0)
    {
        $this->_addresses = $this->_run('getaddress', array('account_index' => $account_index));
        $addresses = [];

//        if (empty($this->_addresses['addresses'])) return $addresses;

        foreach ($this->_addresses['addresses'] as $address) {
            array_push($addresses, new RPCAddress($address));
        }
//
//        foreach ($this->_addresses as $_addr) {
//            $addresses[$_addr['address_index']] = $_addr;
//        }
        return $addresses;
    }

    public function new_address($account_index = 0, $label = null)
    {
        return $this->_run('create_address', array('account_index' => $account_index, 'label' => $label));
    }

    public function balances($account_index = 0)
    {
        return $this->_run('getbalance', array('account_index' => $account_index));
    }

    public function get_transfers($in = false, $out = false, $pending = false, $failed = false, $pool = false, $filter_by_height = true, $account_index = 0)
    {
        return $this->_run('get_transfers', array(
            'in' => $in,
            'out' => $out,
            'pending' => $pending,
            'failed' => $failed,
            'pool' => $pool,
            'filter_by_height' => $filter_by_height,
            'account_index' => $account_index
        ));
    }

    public function get_transfer_by_txid($txid)
    {
        return $this->_run('get_transfer_by_txid', array(
            'txid' => $txid
        ));
    }

    public function get_bulk_payments(Array $txids, $min_block_height = null)
    {
        return $this->_run('get_bulk_payments', array(
            'payment_id' => $txids,
            'min_block_height' => $min_block_height
        ));
    }



//    public function transfers_in($account_index, RPCPaymentFilter $pmtFilter)
//    {
//        $params = [
//            'account_index' => $account_index,
//            'pending' => false
//        ];
//        $method = 'get_transfers';
//
//        if ($pmtFilter->unconfirmed) {
//            $params['in'] = $pmtFilter->confirmed;
//            $params['out'] = false;
//            $params['pool'] = true;
//        } else {
//            if ($pmtFilter->payment_ids) {
//                $method = 'get_bulk_payments';
//                $params['payment_ids'] = $pmtFilter->payment_ids;
//            } else {
//                $params['in'] = $pmtFilter->confirmed;
//                $params['out'] = false;
//                $params['pool'] = false;
//            }
//        }
//
//        if ($method == 'get_transfers') {
//            $arg = 'in';
//            if ($pmtFilter->min_height) {
//                $params['min_height'] = $pmtFilter->min_height - 1;
//                $params['filter_by_height'] = true;
//            }
//
//            if ($pmtFilter->max_height) {
//                $params['max_height'] = $pmtFilter->max_height;
//                $params['filter_by_height'] = true;
//            }
//            $params['max_height'] = array_get($params, 'max_height', 500000000);
//        } else {
//            $arg = 'payments';
//            $params['min_block_height'] = ($pmtFilter->min_height || 1) - 1;
//        }
//
//        $_pmts = $this->_run($method, $params);
//        $pmts = array_get($_pmts, $arg, []);
//
//        if ($pmtFilter->unconfirmed) {
//            array_push($pmts, array_get($_pmts, 'pool', []));
//        }
//
////        return array_map(function($data){
////            return $this->_paymentdict($data);
////        }, $pmts);
//        $res = [];
//        foreach ($pmts as $pm) {
//            array_push($res, $pm);
//        }
//
//        dd($res);
//    }
//
//    public function transfers_out($account, $pmtFilter)
//    {
//        //
//    }
//
//    public function _paymentdict(Array $data)
//    {
//        dd($data);
//
//        $payment_id = array_get($data, 'payment_id', null);
//        $local_address = array_get($data, 'address', null);
//
//        if ($local_address) $local_address = new RPCAddress([
//            'address' => $local_address,
//            'address_index' => null,
//            'label' => null,
//            'used' => false,
//        ]);
//
//        return [
//            'payment_id' => $payment_id,
//            'amount' => $data['amount'],
//            'timestamp' => $data['timestamp'],
//            'note' => $data['note'],
//            'transaction' => $this->_tx($data),
//            'local_address' => $local_address
//        ];
//    }
//
//    public function _tx($data)
//    {
//        $txs = [];
//        if (is_array($data)) {
//            foreach ($data as $tx) {
//                array_push($txs, new RPCTransaction($tx));
//            }
//            return $txs;
//        }
//        return new RPCTransaction($data);
//    }

//    public function spend_key()
//    {
//        return $this->helpers->raw_request('query_key', array('key_type' => 'spend_key'))['key'];
//    }
//    public function view_key()
//    {
//        return $this->helpers->raw_request('query_key', array('key_type' => 'view_key'))['key'];
//    }
//    public function seed()
//    {
////        return $this->helpers->raw_request('query_key', array('key_type' => 'mnemonic'))['key'];
//    }
//
//    public function accounts()
//    {
//        $accounts = [];
//        try {
//            $_accounts = $this->raw_request('get_accounts');
//        } catch (MethodNotFoundException $e) {
//            $this->_master_address = array_first($this->_addresses());
//            return [new Account($this, 0)];
//        }
//
//        $idx = 0;
//        $this->_master_address = new Address($_accounts['subaddress_accounts'][0]['base_address']);
//
//        foreach ($_accounts['subaddress_accounts'] as $_acc) {
//            assert($idx == $_acc['account_index']);
//            array_push($accounts, new Account($this, $_acc['account_index']));
//            $idx++;
//        }
//        return $accounts;
//    }
//
//    public function new_account($label = null)
//    {
//        $_account = $this->raw_request('create_account', $label);
//        return array(
//            new Account($this, $_account['account_index']),
//            new SubAddress($_account['address'])
//        );
//    }

//    public function addresses($account = 0)
//    {
//        $_addresses = $this->raw_request('getaddress', array('account_index' => $account));
//        if (!in_array('addresses', $_addresses)) return [new Address($_addresses['address'])];
//
//        $addresses =
//
//    }

//    public function new_address($account = 0, $label = null)
//    {
//        $_address = $this->raw_request('create_address', array('account_index' => $account, 'label' => $label));
//        return new SubAddress($_address['address']);
//    }
//
//    public function balances($account = 0)
//    {
//        //
//    }
//

//
//    public function _paymentdict($data)
//    {
//        $pid = $data['payment_id'] || null;
//        $laddr = $data['address'] || null;
//
////        if ($laddr) $laddr = a
//    }
//
//    public function _inpayment($data)
//    {
//        //
//    }
//
//    public function _outpayment($data)
//    {
//        //
//    }
//
//    public function _tx($data)
//    {
//        //
//    }
//
//    public function transfer(Array $destinations, $priority, $ringsize, $payment_id = null, $unlock_time = 0, $account = 0, $relay = true)
//    {
//        $data = [
//            'account_index' => $account,
//            'destinations' => list(array_map())
//        ];
//    }

}