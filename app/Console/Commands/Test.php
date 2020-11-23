<?php

namespace App\Console\Commands;

use App\Timezone;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Test extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'captain:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'For testing random code snippets';

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
        //get all distinct zone_ids from locations
        $zones = DB::table('locations')
            ->selectRaw('DISTINCT zone_id')
            ->where('locations.active',1)
            ->get();

        foreach ($zones as $zone) {
            $timezone = Timezone::where('zone_id',$zone->zone_id)->currentTimezone()->first();
            $hourString = '';
            for ($n=0; $n<=18; $n=$n+6) {
                $hour = $n - ($timezone->gmt_offset/3600);
                if ($hour >= 24)
                    $hour = $hour - 24;
                else if ($hour < 0)
                    $hour = 24 + $hour;
                $hourString .= $hour.(($n<18) ? ',' : '');
            }
            echo $zone->zone_id.' '.$hourString;
        }
    }
}
