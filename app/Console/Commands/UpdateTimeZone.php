<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Variable;
use Illuminate\Support\Facades\DB;

class UpdateTimeZone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'captain:timezone';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command will check to see if the Timezone DB needs to be updated and update if necessary';

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
     * This command reads the latest timezonedb date.txt file to see if there has been an update to the database.
     * if this value is different than the value saved in the variable table under variable_name = timezonedb_lastupdate,
     * then update the tables
     *
     * @return mixed
     */
    public function handle()
    {

        $DATA_DIR = storage_path() . '/data';

        //read in last update
        $lastUpdateVariable = Variable::where('name','timezonedb_lastupdate')->first();
        $lastUpdate = $lastUpdateVariable->value;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_URL,'https://timezonedb.com/date.txt');
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
        $timezonedbDate = curl_exec($curl);

        if(curl_errno($curl)) {
            print_r(curl_error($curl));
            die('there was a problem retrieving data.txt');
        }
        curl_close($curl);

        if (trim($lastUpdate) != trim($timezonedbDate)) {

            echo 'timezone db has changed. lets update'."\r\n";

            //download latest timezonedb sql file
            system("wget https://timezonedb.com/files/timezonedb.sql.zip --no-check-certificate -P $DATA_DIR");
            system("unzip $DATA_DIR/timezonedb.sql.zip -d $DATA_DIR");

            //run sql command to re-populate table
            $sql = file_get_contents("$DATA_DIR/timezonedb.sql");
            DB::unprepared($sql);

            //now update the variable timezonedb_lastupdate
            $lastUpdateVariable->value = $timezonedbDate;
            $lastUpdateVariable->save();

            //remove timezone db files, so next update will download with proper name and not having to overwrite
            unlink("$DATA_DIR/timezonedb.sql.zip");
            unlink("$DATA_DIR/timezonedb.sql");
            unlink("$DATA_DIR/readme.txt");
        }
    }
}
