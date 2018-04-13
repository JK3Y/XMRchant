<?php

namespace JK3Y\Xmrchant\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use JK3Y\Xmrchant\Models\Account;
use JK3Y\Xmrchant\Models\Payment;
use JK3Y\Xmrchant\Facades\Xmrchant;

class CheckTransfers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xmrchant:checktransfers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch transfers from monero wallet rpc and update database.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->check();
    }

    public function check()
    {
        $accounts = Account::all()->pluck('id')->toArray();

        foreach ($accounts as $account_id) {
            $transfers = Xmrchant::get_transfers($account_id, [
                'in'        => true,
                'out'       => true,
                'pending'   => true,
                'failed'    => true,
                'pool'      => true
            ]);
            // don't add accounts from list with no incoming transfers
//            if ($transfer) $pending_transfers[$account_id] =  $transfer;
            $this->store($transfers);
        }
    }

    public function store($transfers) {
        foreach ($transfers as $type => $transactions) {
            foreach ($transactions as $transaction) {
                $p = Payment::find($transaction['txid']);

                $data = [
                    'id'            => $transaction['txid'],
                    'account_id'    => $transaction['subaddr_index']['major'],
                    'subaddress_id' => $transaction['subaddr_index']['minor'],
                    'type'          => $transaction['type'],
                    'amount'        => $transaction['amount'],
                    'fee'           => $transaction['fee'],
                    'height'        => $transaction['height'],
                    'timestamp'     => $transaction['timestamp']
                ];

                if ($p) {
                    $p->update([
                        'type' => $data['type'],
                        'height' => $data['height']
                    ]);
                    continue;
                }

                Log::debug($data);

                Payment::create($data);
            }
        }
    }



//    public function addUnconfirmed()
//    {
//        $accounts = Account::all()->pluck('id')->toArray();
//        $pending = [];
//
//        foreach ($accounts as $account_id) {
//            $transfer = array_first(Xmrchant::get_transfers($account_id, ['pool' => true]));
//            // don't add accounts from list with no incoming transfers
//            if ($transfer) $pending[$account_id] =  $transfer;
//        }
//
//        Log::debug($pending);
//
//        foreach ($pending as $account_id => $transfers) {
//            foreach ($transfers as $transfer) {
//                $data = [
//                    'id'            => $transfer['txid'],
//                    'account_id'    => $account_id,
//                    'subaddress_id' => $transfer['subaddr_index']['minor'],
//                    'type'          => $transfer['type'],
//                    'amount'        => $transfer['amount'],
//                    'fee'           => $transfer['fee'],
//                    'height'        => $transfer['height'],
//                    'timestamp'     => $transfer['timestamp']
//                ];
//                $payment = Payment::firstOrCreate($data);
//                Log::debug($payment, $data);
//            }
//        }



//        $accounts = Account::all()->pluck('id')->toArray();
//        $pending = [];
//
//        echo "\n\n";
//        echo "**********************************\n";
//        echo 'Accounts: ' . implode("\n", $accounts) . "\n";
//
//        foreach ($accounts as $account_id) {
//            $transfer = array_first(Xmrchant::get_transfers($account_id, ['pool' => true]));
//            // don't add accounts from list with no incoming transfers
//            if ($transfer) $pending[$account_id] =  $transfer;
//        }
//
//        foreach ($pending as $account_id => $transfers) {
//            foreach ($transfers as $transfer) {
//                $data = array(
//                    'id'            => $transfer['txid'],
//                    'account_id'    => $account_id,
//                    'subaddress_id' => $transfer['subaddr_index']['minor'],
//                    'type'          => $transfer['type'],
//                    'amount'        => $transfer['amount'],
//                    'fee'           => $transfer['fee'],
//                    'height'        => $transfer['height']
//                );
//                $payment = Payment::firstOrCreate($data);
//                echo "\n" . 'New Unconfirmed Transaction: ' . $payment . "\n\n";
//            }
//        }
//    }

}