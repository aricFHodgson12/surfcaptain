<?php

namespace App\Console\Commands;

use App\Location;
use Illuminate\Console\Command;

class LatLonList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'captain:latlonlist';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will generate a simple list of lat/lon for each location id';

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
        $locations = Location::all();
        $list = fopen('./storage/data/latlonlist.txt','w');
        foreach ($locations as $location)
            fwrite($list,$location->id.' '.$location->lat.' '.$location->lon."\r\n");
        fclose($list);
    }
}
