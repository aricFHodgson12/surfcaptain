<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateRobotsFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'captain:robots';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate robots.txt file based on environment';

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
        $robots = fopen(public_path().'/robots.txt','w');

        $txt = '';
        if (config('app.env') === 'prod') {
            $txt = 'User-agent: *'."\r\n".
                'Allow: /'."\r\n".
                "\r\n".
                "Sitemap: https://surcaptain.com/sitemap.xml";


        } else {
            $txt = 'User-agent: *'."\r\n".
                'Disallow: /';
        }

        fwrite($robots,$txt);
    }
}
