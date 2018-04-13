<?php

namespace JK3Y\Xmrchant;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\ServiceProvider;
use JK3Y\Xmrchant\Console\Kernel;
use JK3Y\Xmrchant\RPC\JSONRPCWallet;

class XmrchantServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            realpath(__DIR__ . '/../config/xmrchant.php') => config_path('xmrchant.php')
        ]);

        $this->app->booted(function() {
            $config = config('xmrchant');
            if ($config['enable_check_transfers_schedule']) {
                $schedule = app(Schedule::class);
                $schedule->command('xmrchant:checktransfers')
                    ->everyMinute()
                    ->appendOutputTo(storage_path('logs/check_transfers.txt'));
            }
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            'JK3Y\Xmrchant\Console\CheckTransfers'
        ]);

        $this->app->bind('JK3Y-xmrchant', function() {
            $config = config('xmrchant');
            return new Xmrchant(new JSONRPCWallet(
                $config['wallet_rpc_protocol'],
                $config['wallet_rpc_host'],
                $config['wallet_rpc_port'],
                $config['wallet_rpc_user'],
                $config['wallet_rpc_pass']
            ));
        });
    }
}
