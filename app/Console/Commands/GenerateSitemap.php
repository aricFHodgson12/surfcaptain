<?php

namespace App\Console\Commands;

use App\CanvasPost;
use App\Location;
use App\LocationBeach;
use Canvas\Canvas;
use Illuminate\Console\Command;

class GenerateSitemap extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'captain:sitemap';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create an XML sitemap for search engines.';

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
        $siteMap = fopen(public_path().'/sitemap.xml','w');

        $beaches = LocationBeach::all()->where('active',1);
        $articles = CanvasPost::all()->whereNull('deleted_at');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n".
                    "\t".'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\r\n";

        //stand alone pages
        $xml .= "\t\t".'<url><loc>'.config('app.url').'</loc></url>'."\r\n";
        $xml .= "\t\t".'<url><loc>'.config('app.url').'/faq</loc></url>'."\r\n";
        $xml .= "\t\t".'<url><loc>'.config('app.url').'/nearby</loc></url>'."\r\n";
        $xml .= "\t\t".'<url><loc>'.config('app.url').'/maps</loc></url>'."\r\n";
        $xml .= "\t\t".'<url><loc>'.config('app.url').'/about</loc></url>'."\r\n";
        $xml .= "\t\t".'<url><loc>'.config('app.url').'/blog</loc></url>'."\r\n";

        foreach ($beaches as $beach)
            $xml .= "\t\t".'<url><loc>'.config('app.url').'/forecast/'.$beach['slug'].'</loc></url>'."\r\n";

        foreach ($articles as $article)
            $xml .= "\t\t".'<url><loc>'.config('app.url').'/blog/1/'.$article['slug'].'</loc></url>'."\r\n";

        $xml .= '</urlset> ';

        fwrite($siteMap,$xml);
    }
}
