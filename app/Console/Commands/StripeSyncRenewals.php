<?php

namespace App\Console\Commands;

use Illuminate\Support\Facades\DB;
use Exception;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Laravel\Cashier\Subscription;

class StripeSyncRenewals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stripe:sync-renewals {customer?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the renews_at column on stripe subscriptions to reflect the end of the current billing period.';

    protected $updatedCount = 0;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        \Stripe\Stripe::setApiKey(config('services.stripe.stripe_secret'));
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $apiOptions = ['limit' => 100];
        if ($customer = $this->argument('customer'))
            $apiOptions['customer'] = $customer;

        while ($response = \Stripe\Subscription::all($apiOptions)) {

            foreach ($response->data as $subscription)
                $this->updateSubscription($subscription);

            if (! $response->has_more)
                break;

            $apiOptions['starting_after'] = $subscription->id;
        }

        //$this->info('Successfully updated ' . $this->updatedCount . ' subscriptions.');
    }

    protected function updateSubscription($subscription)
    {
        $updated = DB::table('subscriptions')
            ->where('stripe_id', $subscription->id)
            ->update([
                'renews_at' => Carbon::createFromTimestamp($subscription->current_period_end),
            ]);

        //if ($updated)
        //    $this->updatedCount++;

    }

}
