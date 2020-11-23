<?php

namespace App\Console;

use App\Timezone;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        //get all distinct zone_ids from locations, then figure out which hours to run for each timezone
        //the hours will align up to 6am,12pm,6pm,12am local time
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
            $schedule->command('captain:forecast '.$zone->zone_id)->cron('5 '.$hourString.' * * *');
        }

        $schedule->command('captain:weather-stations-inactive')->hourlyAt('10'); //update the wx_station table
        $schedule->command('captain:weather-stations')->dailyAt('03:00'); //update the wx_station table
        $schedule->command('captain:sitemap')->weeklyOn(0, '6:00');; //update the wx_station table
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
