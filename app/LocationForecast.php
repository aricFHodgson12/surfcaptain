<?php

namespace App;

use App\Notifications\ApiError;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class LocationForecast extends Model
{
    protected $table = 'location_forecasts';
    private $weatherCaptainApi;

    public $forecast3days = 88;

    public function location() {
        return $this->belongsTo('App\Location');
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->weatherCaptainApi =  new WeatherCaptainApi;
    }

    public function todaysSummary($units) {

        $today = array();
        $forecast = json_decode($this->forecast_3day,true);
        $summary = $forecast['days'][0];

        $condAmInt = $this->weatherCaptainApi->condTextToInteger($summary['amcond']);
        $condPmInt = $this->weatherCaptainApi->condTextToInteger($summary['amcond']);

        //$today['timestamp'] = $summary->cond[1]->timestamp;
        $today['condAm'] = $summary['amcond'];
        $today['condPm'] = $summary['pmcond'];
        $today['condText'] = $summary['condTxt'];
        $today['condInt'] = ($condAmInt < $condPmInt) ? $condAmInt : $condPmInt;
        $today['surfInt'] = ($summary['amsurf'] > $summary['pmsurf']) ? $summary['amsurf'] : $summary['pmsurf'];
        $today['surfAm'] = $this->weatherCaptainApi->surfIntegerToText($summary['amsurf'],$units->wvht_unit);
        $today['surfPm'] = $this->weatherCaptainApi->surfIntegerToText($summary['pmsurf'],$units->wvht_unit);
        $today['surfText'] = $summary['surfTxt'];

        return $today;
    }

    public function getUnits($user)
    {

        if ($user) {

            $userId = $user->id;
            //$userId = 35;
            $userSettings = UserSetting::where('user_id', $userId)->first();

        } else {

            $userSettings = new UserSetting();

            if ($units = Cookie::get('sc_units') and $units == 2) {
                $userSettings->wvht = 2; //meters
                $userSettings->wind = 6; //kph
                $userSettings->tide = 4; //meter
                $userSettings->temp = 9; //celcius
            } else {
                $userSettings->wvht = 1; //feet
                $userSettings->wind = 5; //mph
                $userSettings->tide = 3; //feet
                $userSettings->temp = 8; //fehrenheit
            }
        }
        $units = $userSettings->allUnits();

        return $units;
    }

    public function createTimelineBaseGraph($timeline,$sunriseTime,$sunsetTime) {

        $nPoints = count($timeline['time']);

        //initialize the svg element strings
        $midnightLines = array();
        $afternoonLines = array();
        $nightTimeRectangles = array();
        $days = array();
        $dates = array();
        //$sunriseX = array();
        $nightTimeRectangleWidth = false;
        $sunsetSet = false;

        $sunsetHour = (strpos($sunsetTime,'.')) ? substr($sunsetTime,0,strpos($sunsetTime,'.')) : $sunsetTime;
        $sunsetHourFraction = (strpos($sunsetTime,'.')) ? substr($sunsetTime,strpos($sunsetTime,'.')) : 0;

        $sunriseHour = (strpos($sunriseTime,'.')) ? substr($sunriseTime,0,strpos($sunriseTime,'.')) : $sunriseTime;
        $sunriseHourFraction = (strpos($sunriseTime,'.')) ? substr($sunriseTime,strpos($sunriseTime,'.')) : 0;

        for ($n=0; $n<$nPoints; $n++) {

            //create midnight lines
            if ($timeline['time'][$n] == '12am')
                $midnightLines[] = $n / ($nPoints - 1) * 100;

            if ($timeline['time'][$n] == '12pm') {
                $afternoonLines[] = $n / ($nPoints - 1) * 100;
                $dateNum = strtoupper(substr($timeline['date'][$n],strpos($timeline['date'][$n],'/') + 1));
                $dates[] = (($n > 9 and $n < ($nPoints - 9)) ? strtoupper($timeline['day'][$n]).' '.$dateNum : false);
            }

            if (!$sunsetSet and $timeline['time'][$n] == $sunsetHour.'pm') {
                $nightTimeRectangles[] = ($n + $sunsetHourFraction) / ($nPoints - 1) * 100;
                $sunsetN = $n;
            }

            if ($timeline['time'][$n] == $sunriseHour.'am' and isset($sunsetN)) {
                //$sunriseX[] = $n / ($nPoints - 1) * 100;
                if (!$nightTimeRectangleWidth)
                    $nightTimeRectangleWidth = ($n + $sunriseHourFraction - $sunsetN) / $nPoints * 100;
            }
        }

        $html = '<div class="graph-base">';
        //day header
        $html .= '<div class="day-header"></div>';
        //$svg = '<rect x="0" y="0" width="'.$width.'" height="'.$dayHeight.'" class="day-header" />';

        //main graph
        $html .= '<div class="graph-bg">';
        //$svg .= '<rect x="0" y="'.$dayHeight.'" width="'.$width.'" height="'.$height.'" class="graph-bg" />';

        foreach ($nightTimeRectangles as $n => $nightTimeRectangle) {
            $html .= '<div class="night" style="left:'.$nightTimeRectangle.'%;width:'.$nightTimeRectangleWidth.'%">';
                $html .= '<div class="sunset-icon"></div>';
                $html .= '<div class="sunrise-icon"></div>';
            $html .= '</div>';
            //$svg .= '<rect x="'.$nightTimeRectangle.'" y="'.$dayHeight.'" width="' . $nightTimeRectangleWidth . '" height="' . $height . '" class="night" />';
            //$svg .= '<image x="'.$nightTimeRectangle.'" y="'.$graphHeight.'" width="11" height="7" transform="translate(-5.5,-7)" xlink:href="/images/icon/sunset-icon.svg" />';
            //$svg .= '<image x="'.$sunriseX[$n].'" y="'.$graphHeight.'" width="11" height="7" transform="translate(-5.5,-7)" xlink:href="/images/icon/sunrise-icon.svg" />';
        }

        foreach ($midnightLines as $n => $midnightLine)
            $html .= '<div class="midnight" style="left:'.$midnightLine.'%"></div>';
            //$svg .= '<line x1="'.$midnightLine.'" y1="0" x2="'.$midnightLine.'" y2="'.($height+$dayHeight+9).'" class="midnight" />';

        foreach ($afternoonLines as $n => $afternoonLine) {
            $html .= '<div class="afternoon" style="left:'.$afternoonLine.'%"></div>';
            //$svg .= '<line x1="' . $afternoonLine . '" y1="' . $dayHeight . '" x2="' . $afternoonLine . '" y2="' . ($dayHeight + $height + 9) . '" class="afternoon" />';
            if ($dates[$n]) {
                $html .= '<div class="graph-day-date" style="left:'.$afternoonLine.'%">'.$dates[$n].'</div>';
                //$svg .= '<text x="' . $afternoonLine . '" y="17" text-anchor="middle"><tspan class="graph-day">' . $days[$n] . '</tspan> <tspan class="graph-date">' . $dates[$n] . '</tspan></text>';
            }
        }
        $html .= '</div></div>';

        //tick marks
        /*
        foreach ($timeline['time'] as $n => $time) {
            $x = $n / $nPoints * $width;
            $thirdHour = (intval(substr($time,0,-2) % 3) == 0) ? true : false;
            $longTick = (intval(substr($time,0,-2) % 12) == 0) ? true : false;
            if (!$longTick)
                $svg .= '<line x1="'.$x.'" y1="'.($height+$dayHeight-3).'" x2="'.$x.'" y2="'.($height+$dayHeight+3).'" class="hour-tick '.(($thirdHour)?'third-hour':'').'" />';
        }
        */

        return $html;
    }

    public function createTimelineSurfSvg($wnTot,$cond, $width,$height) {

        $nPoints = count($wnTot);
        $maxSurf = 80;

        $xInt = $width / ($nPoints - 1);
        $yRatio = $height / $maxSurf;

        //initialize the svg element strings
        $color = array();
        $nPath = 0;

        /*
        $path[0] = '0,'.($height);
        for ($n=0; $n<$nPoints; $n++) {
            //convert surf int to graph coord
            $x = $n * $xInt;
            $y = $height - $wnTot[$n] * $yRatio;
            $path[$nPath] .= " $x,$y";

            //assign first fill color
            if ($n == 0)
                $color[$nPath] =  ($cond[$n]) ? $this->weatherCaptainApi->condGroupIntegerToText($cond[$n]) : 'none';

            //conditions have changed between data points
            else if ($cond[$n] != $cond[$n-1]) {
                //complete old path
                $path[$nPath] .= " $x,".($height);
                //new path
                $nPath++;
                $path[$nPath] = $x.','.($height);
                $path[$nPath] .= " $x,$y";
                $color[$nPath] = ($cond[$n]) ? $this->weatherCaptainApi->condGroupIntegerToText($cond[$n]) : 'none';
            }
        }

        $path[$nPath] .= " $width,$height";

        $svg = '<svg viewBox="0 0 '.$width.' '.$height.'" preserveAspectRatio="none">';
            foreach ($path as $n => $points)
                $svg .= '<polyline points="' . $points . '" class="' . $color[$n] . '" />';

        $svg .= '</svg>';
        */

        $prevX = 0;
        $prevY = 0;
        $k = 0;
        $path = array();
        $increase = false;
        $decrease = false;
        $lastN = 0;

        for ($n=0; $n<$nPoints; $n=$n+1) {

            if ($nPoints > $this->forecast3days and $n > 0 and $cond[$n] == $cond[$n-1] and ($n - $lastN) < 3)
                continue;

            //convert surf int to graph coord
            $x = round($n * $xInt,1);
            $y = round($height - $wnTot[$n] * $yRatio,1);

            $nextDecrease = false;
            $nextIncrease = false;

            if (isset($wnTot[$n+1])) {
                $nextKey = $n+1;
                if ($nPoints > $this->forecast3days and $n > 0) {
                    if (isset($cond[$n+2]) and $cond[$n + 1] == $cond[$n]) {
                        if (isset($cond[$n+3]) and $cond[$n + 2] == $cond[$n])
                            $nextKey = $n + 3;
                        else
                            $nextKey = $n + 2;
                    }
                }

                $nextY = round($height - $wnTot[$nextKey] * $yRatio,1);
                if ($nextY > $y) {
                    $nextDecrease = true;
                    $nextIncrease = false;
                } else if ($nextY < $y) {
                    $nextDecrease = false;
                    $nextIncrease = true;
                }
            }

            $cx1 = round($prevX + ($x - $prevX) / 2, 1);
            //$cx1 = $prevX;
            $cx2 = $cx1;
            //$cx2 = round($prevX + ($x - $prevX) / 2, 1);

            $cy1 = $prevY;
            $cy2 = $y;

            //assign first fill color
            if ($n == 0) {
                $path[$nPath] = 'M0 ' . $height . ' L' . $x . ' ' . $y;
                $color[$nPath] = ($cond[$n]) ? $this->weatherCaptainApi->condGroupIntegerToText($cond[$n]) : 'none';

            //conditions have changed between data points
            } else if ($cond[$n] != $cond[$n-1]) {
                //complete old path
                $path[$nPath] .= "C $cx1 $cy1 $cx2 $cy2 $x $y L $x $height";

                //new path
                $nPath++;
                //$k=0;
                $path[$nPath] = "M$x $height L $x $y";
                $color[$nPath] = ($cond[$n]) ? $this->weatherCaptainApi->condGroupIntegerToText($cond[$n]) : 'none';

            } else {

                //use smooth bezier curve for points that aren't increasing/decreasing to remove sharp edges
                if ( (($increase == false and $decrease == false) and ($y > $prevY or $y < $prevY) and ($nextIncrease == false and $nextDecrease == false)) //flat to increase/decrease to flat
                    or ( ($increase == true and $y > $prevY) or ($decrease == true and $y < $prevY) ) //increase to decrease or vice versa
                    or ( ($increase == true and $y == $prevY and $nextIncrease == true) or ($decrease == true and $y == $prevY and $nextDecrease == true) )
                    or ( ($increase == true and ($prevY - $y) > 7) or ($decrease == true and ($y - $prevY) > 7) )
                ) {
                    $path[$nPath] .= ' C' . $cx1 . ' ' . $cy1 . ' ' . $cx2 . ' ' . $cy2 . ' ' . $x . ' ' . $y;

                } else if ( ($increase == false and $decrease == false) and ( ($y > $prevY and $nextDecrease == true)  or ($y < $prevY and $nextIncrease == true)) ) {
                    //was flat, now increasing or decreasing and continuing to increase/decrease afterwards
                    $path[$nPath] .= ' C' . $cx1 . ' ' . $cy1 . ' ' . $x . ' ' . $cy2 . ' ' . $x . ' ' . $y;

                } else if ( (($increase == true or $decrease == true) and ($y == $prevY) and ($nextIncrease == false and $nextDecrease == false)) //was increasing/decreasing, now flat, and next step flat
                    or ($increase == true and $y < $prevY and $nextDecrease == false and $nextIncrease == false) //increase, increase, flat
                    or ($decrease == true and $y > $prevY and $nextDecrease == false and $nextIncrease == false) //decrease, decrease, false
                    or ($increase == true and $y < $prevY and $nextDecrease == true) //increase to increase to decrease
                    or ($decrease == true and $y > $prevY and $nextIncrease == true) //decrease to decrease to increase
                ) {
                    $path[$nPath] .= ' C' . $prevX . ' ' . $cy1 . ' ' . $cx2 . ' ' . $cy2 . ' ' . $x . ' ' . $y;

                } else {
                    $path[$nPath] .= ' L' . $x . ' ' . $y;
                }


                if ($y > $prevY) {
                    $decrease = true;
                    $increase = false;
                } else if ($y < $prevY) {
                    $increase = true;
                    $decrease = false;
                } else {
                    $increase = false;
                    $decrease = false;
                }
            }

            $lastN = $n;
            $prevX = $x;
            $prevY = $y;
            //$k++;
        }

        $path[$nPath] .= " L $x $height";

        $svg = '<svg viewBox="0 0 '.$width.' '.$height.'" preserveAspectRatio="none">';
            foreach ($path as $n => $points)
                $svg .= '<path d="'.$points.'" class="'.$color[$n].'"/>';

        $svg .= '</svg>';

        return $svg;
    }

    public function createTimelineSwellSvg($swell,$width,$height) {
        //$dayHeight = 24;

        //find max height
        $maxHt = 0;
        foreach ($swell as $ht) {
            if (max($ht) > $maxHt)
                $maxHt = ceil(max($ht));
        }
        $maxHtMeters = ceil($maxHt / 3.281);

        //find tick marks
        // We put tick options into an array as the whole thing is done programmatically
        $tickOptions = array(3, 4, 5);

        $units = array('ft','m');
        // Get the correct scales
        foreach ($units as $unit) {
            $scale = false;
            $max = ($unit == 'ft') ? $maxHt : $maxHtMeters;
            while ($scale == false) {
                $max = $max + 1; //add a little breathing room at top of the graph
                for ($i = 0; $i < count($tickOptions); $i++) {
                    if ($max % $tickOptions[$i] == 0) {
                        $nTicks = $tickOptions[$i];
                        $scale = true;
                        if ($unit == 'ft')
                            $maxHt = $max;
                        break;
                    }
                }
            }
            if (isset($nTicks)) {
                // Once we have the scale draw the y axis labels
                $tickInt = $max / $nTicks;

                // Tick labels are all of a common format. We create an array containing the ticklabels
                // in reverse order as that is how they will be drawn.
                for ($i = $nTicks - 1; $i > 0; $i--)
                    $return['ticks'][$unit][$i] = $i * $tickInt.$unit;
            }
        }

        $nPoints = count($swell);

        $xInt = $width / ($nPoints - 1);
        $yRatio = $height / $maxHt;

        $swellName[0] = 'swell1';
        $swellName[1] = 'swell2';
        $swellName[2] = 'swell3';
        $swellName[3] = 'swell4';
        $swellName[4] = 'swell5';
        $swellName[5] = 'swell6';

        //create a line for reach swell
        $path = array();
        $nPath = 0;
        for ($i=0; $i<6; $i++) {
            for ($n=0; $n<$nPoints; $n++) {

                if ($swell[$n][$i] > 0) {
                    $x = round($n * $xInt,1);
                    $y = round($height - $swell[$n][$i] * $yRatio,1);

                    //echo 'height: '.$height.' data ht: '.$data['ht'][$i][$n]. ' yratio: '.$yRatio."\r\n";
                    if (!isset($path[$nPath]))
                        $path[$nPath] = '';
                    $path[$nPath] .= "$x,$y ";
                    $class[$nPath] = $swellName[$i];
                } else {
                    $nPath++;
                }
            }
            $nPath++;
        }

        $svg = '<svg viewBox="0 0 '.$width.' '.$height.'" preserveAspectRatio="none">';
        foreach ($path as $n => $points)
            $svg .= '<polyline points="'.$points.'" class="'.$class[$n].'" />';

        $svg .= '</svg>';
        $return['svg'] = $svg;

        return $return;
    }

    public function tideLevels($tide, $nextTide, $timestamp)
    {
        $return = array(
            'tideLevel',
            'tideLevelDesc'
        );

        $tideHourInt = ($nextTide['time'] - $tide['time']) / 3600;
        $tideLevelInt = ($nextTide['level'] - $tide['level']) / $tideHourInt;
        $tideTimeDiff = $timestamp - $tide['time'];
        $tideLowHighFraction = $tideTimeDiff / ($nextTide['time'] - $tide['time']);
        $return['tideLevel'] = round($tide['level'] + $tideTimeDiff * $tideLevelInt / 3600,1);

        if ($tide['type'] == 'low') {

            if ($tideLowHighFraction <= 1/8)
                $return['tideLevelDesc'] = 'Low Tide';
            else if ($tideLowHighFraction < 3/8)
                $return['tideLevelDesc'] = 'Incoming Low Tide';
            else if ($tideLowHighFraction < 5/8)
                $return['tideLevelDesc'] = 'Incoming Mid Tide';
            else if ($tideLowHighFraction < 7/8)
                $return['tideLevelDesc'] = 'Incoming High Tide';
            else
                $return['tideLevelDesc'] = 'High Tide';

        } else {

            if ($tideLowHighFraction <= 1/8)
                $return['tideLevelDesc'] = 'High Tide';
            else if ($tideLowHighFraction < 3/8)
                $return['tideLevelDesc']= 'Outgoing High Tide';
            else if ($tideLowHighFraction < 5/8)
                $return['tideLevelDesc']= 'Outgoing Mid Tide';
            else if ($tideLowHighFraction < 7/8)
                $return['tideLevelDesc'] = 'Outgoing Low Tide';
            else
                $return['tideLevelDesc'] = 'Low Tide';
        }
        return $return;
    }

    public function createTideSvg($tidePoints, $width, $height)
    {
        //$svg = '<svg viewBox="0 0 '.$width.' '.$height.'" preserveAspectRatio="none">';
        $svg = '';

        $maxLevel = 0;
        $minLevel = 0;
        foreach ($tidePoints as $k => $tidePoint) {
            if ($tidePoint['level'] > $maxLevel)
                $maxLevel = $tidePoint['level'];

            if ($tidePoint['level'] < $minLevel)
                $minLevel = $tidePoint['level'];
        }
        $levelRange = $maxLevel - $minLevel;
        $tidePoints = array_values($tidePoints); //reset array to start at 0

        $nPoints = count($tidePoints);
        $timeRange = $tidePoints[$nPoints-1]['time'] - $tidePoints[0]['time'];

        $prevX = false;
        $prevY = false;
        $svg .= '<defs>
                    <linearGradient id="tide-grad" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="50%" stop-color="#C3c8c8" />
                        <stop offset="90%" stop-color="#E4E6E6" />
                    </linearGradient>
                </defs>';
        $svg .= '<path d="';
        foreach ($tidePoints as $k => $tidePoint) {
            $timeFraction = ($tidePoints[$nPoints-1]['time'] - $tidePoint['time']) / $timeRange;
            $x = $width - round($width * $timeFraction,1);

            $heightFraction = ($tidePoint['level'] - $minLevel) / $levelRange; //if minLevel is below 0, it will be normalized by subtract minLevel (adding)
            $y = $height - round($height * $heightFraction,1);

            if ($k == 0)
                $svg .= 'M0 '.$height.' L'.$x.' '.$y;
            else {

                $cx1 = round($prevX + ($x - $prevX) / 2, 1);
                $cx2 = $cx1;

                $cy1 = $prevY;
                $cy2 = $y;

                //if ($k == 1)
                    $svg .= ' C'.$cx1.' '.$cy1.' '. $cx2 .' '.$cy2.' '.$x.' '.$y;
                //else
                //    $svg .= ' S'.$cx2.' '.$cy2.' '.$x.' '.$y;
            }

            $prevX = $x;
            $prevY = $y;
        }

        $svg .= 'L'.$width.' '.$height;
        $svg .= '"></path>';

        //create now point
        /*
        $timeFraction = ($tidePoints[$nPoints-1]['time'] - $timestamp) / $timeRange;
        $x = $width - round($width * $timeFraction,1);

        $heightFraction = $tideLevel / $levelRange;
        $y = round($height * $heightFraction,1);
        $svg .= '<line x1="'.$x.'" y1="0" x2="'.$x.'" y2="'.$height.'" />';
        */

        //$svg .= '</svg>';
        //Log::info('svg: '.$svg);

        return $svg;
    }

    public function fcstData($data)
    {

        $test = false;

        $fcstData = array(
            'days' => array(),
            'timeline' => array(),
            'sst' => false
        );
        $fcstData3 = $fcstData;

        $summary = $data['forecast'];
        $timeline = $data['timeline'];
        $tideSunrise = $data['tide_sunrise'];

        $surfSummaryTimestamp = $summary['surf']['timestamp'];
        $condSummaryTimestamp = $summary['cond']['timestamp'];
        $weatherSummaryTimestamp = $summary['weather']['timestamp'];

        //find difference in timestamps between first days of surf and cond
        $surfCondDayDiff = ($test) ? 0 : (strtotime(date('Y-m-d 00:00:00',$surfSummaryTimestamp)) - strtotime(date('Y-m-d 00:00:00',$condSummaryTimestamp))) / (3600*24);

        //find difference in timestamps between first days of surf and weather
        $surfWeatherDayDiff = ($test) ? 0 : (strtotime(date('Y-m-d 00:00:00',$surfSummaryTimestamp)) - strtotime(date('Y-m-d 00:00:00',$weatherSummaryTimestamp))) / (3600*24);

        //find day diff between tides and surf summary - should be 1 day diff
        if (isset($tideSunrise[0]['high_1_time']))
            $firstTide = $tideSunrise[0]['high_1_time'];
        else
            $firstTide = $tideSunrise[0]['low_1_time'];

        $surfTideDayDiff = ($test) ? 0 : (strtotime(date('Y-m-d 00:00:00',$surfSummaryTimestamp)) - strtotime(date('Y-m-d 00:00:00',strtotime($firstTide)))) / (3600*24);

        //find hour difference between surf and cond timeline start hours
        $surfCondHourDiff = ($test) ? 0 : ($timeline['surf']['timestamp'] - $timeline['cond']['timestamp']) / (60 * 60);

        //find hour difference between surf and weather start hour
        $surfWeatherHourDiff = ($test) ? 0 : ($timeline['surf']['timestamp'] - $timeline['weather']['timestamp']) / (60 * 60);

        //find starting surf timeline hour to start
        $timelineDayDiff = ($test) ? 0 : (strtotime(date('Y-m-d 00:00:00',$timeline['surf']['timestamp'])) - strtotime(date('Y-m-d 00:00:00',$surfSummaryTimestamp))) / (3600*24);
        $timelineStartHour = date('G',$timeline['surf']['timestamp']);

        if ($timelineDayDiff == 0) {
            //midnight would be 0, negative values mean there is no timeline data starting at midnight, -7, means timeline starts at 7am.
            $surfTimelineStartKey = $timelineStartHour * -1;
        } else {
            //daydiff would be negative -1, ie. timeline started at 8pm
            $surfTimelineStartKey = 24 - $timelineStartHour;  //ie. 8pm, key = 24 - 20 -> 4
        }

        foreach ($summary['surf']['text'] as $k => $surfText) {

            $summaryDays[$k]['day'] = date('l',$surfSummaryTimestamp + ($k * 3600 * 24));
            $summaryDays[$k]['date'] = date('l M j',$surfSummaryTimestamp + ($k * 3600 * 24));
            $summaryDays[$k]['dateNum'] = date('j',$surfSummaryTimestamp + ($k * 3600 * 24));
            $summaryDays[$k]['surfTxt'] = $surfText;
            //$summaryDays[$k]['amsurf'] = $this->weatherCaptainApi->surfIntegerToText($summary['surf']['am'][$k],$units->wvht_unit);
            //$summaryDays[$k]['pmsurf'] = $this->weatherCaptainApi->surfIntegerToText($summary['surf']['pm'][$k],$units->wvht_unit);
            $summaryDays[$k]['amsurf'] = $summary['surf']['am'][$k];
            $summaryDays[$k]['pmsurf'] = $summary['surf']['pm'][$k];

            $condK = $k + $surfCondDayDiff;
            $summaryDays[$k]['condTxt'] = ($condK < 0 || !isset($summary['cond']['text'][$condK])) ? 'none' : $summary['cond']['text'][$condK];
            $summaryDays[$k]['amcond'] = ($condK < 0 || !isset($summary['cond']['text'][$condK])) ? 'none' : $this->weatherCaptainApi->condIntegerToGroupText($summary['cond']['am'][$condK]);
            $summaryDays[$k]['pmcond'] = ($condK < 0 || !isset($summary['cond']['text'][$condK])) ? 'none' : $this->weatherCaptainApi->condIntegerToGroupText($summary['cond']['pm'][$condK]);

            $wxk = $k + $surfWeatherDayDiff;
            $summaryDays[$k]['wxicon'] = (isset($summary['weather']['wxicon'][$wxk])) ? $summary['weather']['wxicon'][$wxk] : '';

            $tk = $k + $surfTideDayDiff;

            $surfTimelineKey = $surfTimelineStartKey + (24 * $k);
            for ($n=0; $n<24; $n++) {
                $stk = $surfTimelineKey + $n;
                $ctk = $stk + $surfCondHourDiff;
                $wtk = $stk + $surfWeatherHourDiff;

                $summaryDays[$k]['hourly'][$n]['cond'] = (isset($timeline['cond']['cond'][$ctk]))
                    ? $this->weatherCaptainApi->condIntegerToGroupText($timeline['cond']['cond'][$ctk])
                    : $this->weatherCaptainApi->noCondText;

                $summaryDays[$k]['hourly'][$n]['wind'] = (isset($timeline['cond']['wind_dir'][$ctk]))
                    ? sprintf('%3s <u>%2d</u>',
                        $timeline['cond']['wind_dir'][$ctk],
                        $timeline['cond']['wind_spd'][$ctk]
                    )
                    : '';

                $summaryDays[$k]['hourly'][$n]['swell1'] = (isset($timeline['surf']['swell1']['waveHeight'][$stk]))
                    ? sprintf('%3s <u>%4.1f</u> @ %2d sec',
                        $this->weatherCaptainApi->dirText($timeline['surf']['swell1']['directionTextDeg'][$stk]),
                        //$this->weatherCaptainApi->convertHeight($timeline['surf']['swell1']['waveHeight'][$stk],$units->wvht_unit),
                        $timeline['surf']['swell1']['waveHeight'][$stk],
                        $timeline['surf']['swell1']['wavePeriod'][$stk]
                    )
                    : '';

                $summaryDays[$k]['hourly'][$n]['swell2'] = (isset($timeline['surf']['swell2']['waveHeight'][$stk]) and (float)$timeline['surf']['swell2']['waveHeight'][$stk] > 0 )
                    ? sprintf('%3s <u>%4.1f</u> @ %2d sec',
                        $this->weatherCaptainApi->dirText($timeline['surf']['swell2']['directionTextDeg'][$stk]),
                        //$this->weatherCaptainApi->convertHeight($timeline['surf']['swell2']['waveHeight'][$stk],$units->wvht_unit),
                        $timeline['surf']['swell2']['waveHeight'][$stk],
                        $timeline['surf']['swell2']['wavePeriod'][$stk]
                    )
                    : '';

                $summaryDays[$k]['hourly'][$n]['temp'] = (isset($timeline['weather']['temp'][$wtk]))
                    //? $this->weatherCaptainApi->convertTemp($timeline['weather']['temp'][$wtk],$units->temp_unit)."&deg;".strtoupper($units->temp_unit)
                    ? '<u>'.$timeline['weather']['temp'][$wtk].'</u>'
                    : '';

                $summaryDays[$k]['hourly'][$n]['wxicon'] = (isset($timeline['weather']['wxicon'][$wtk]))
                    ? $timeline['weather']['wxicon'][$wtk]
                    : '';

                //some times swell 2 is the same as swell 1. In this case, remove swell2
                if ($summaryDays[$k]['hourly'][$n]['swell2'] == $summaryDays[$k]['hourly'][$n]['swell1'])
                    $summaryDays[$k]['hourly'][$n]['swell2'] = '';
            }

            //include tides and sun data
            //some locations, like texas, may not have a low or high tide during a given day, thus logic below to handle getting from previous/next day
            $summaryDays[$k]['low_am'] = (isset($tideSunrise[$tk]['low_1_time']))
                ? date('g:ia',strtotime($tideSunrise[$tk]['low_1_time'])).' @ <u>'.$tideSunrise[$tk]['low_1_height'].'</u>'
                : ((isset($tideSunrise[$tk - 1]))
                    ? date('g:ia',strtotime($tideSunrise[$tk - 1]['low_1_time'])).' @ <u>'.$tideSunrise[$tk - 1]['low_1_height'].'</u>'
                    : ((isset($tideSunrise[$tk + 1]))
                        ? date('g:ia',strtotime($tideSunrise[$tk + 1]['low_1_time'])).' @ <u>'.$tideSunrise[$tk + 1]['low_1_height'].'</u>'
                        : ''
                    )
                );
            $summaryDays[$k]['high_am'] = (isset($tideSunrise[$tk]['high_1_time']))
                ? date('g:ia',strtotime($tideSunrise[$tk]['high_1_time'])).' @ <u>'.$tideSunrise[$tk]['high_1_height'].'</u>'
                : ((isset($tideSunrise[$tk - 1]))
                    ? date('g:ia',strtotime($tideSunrise[$tk - 1]['high_1_time'])).' @ <u>'.$tideSunrise[$tk - 1]['high_1_height'].'</u>'
                    : ((isset($tideSunrise[$tk + 1]))
                        ? date('g:ia',strtotime($tideSunrise[$tk + 1]['high_1_time'])).' @ <u>'.$tideSunrise[$tk + 1]['high_1_height'].'</u>'
                        : ''
                    )
                );
            $summaryDays[$k]['low_pm'] = (isset($tideSunrise[$tk]['low_2_time']))
                ? date('g:ia',strtotime($tideSunrise[$tk]['low_2_time'])).' @ <u>'.$tideSunrise[$tk]['low_2_height'].'</u>'
                : '';
            $summaryDays[$k]['high_pm'] = (isset($tideSunrise[$tk]['high_2_time']))
                ? date('g:ia',strtotime($tideSunrise[$tk]['high_2_time'])).' @ <u>'.$tideSunrise[$tk]['high_2_height'].'</u>'
                : '';
            $summaryDays[$k]['sunrise'] = date('g:ia', strtotime($tideSunrise[$tk]['sunrise_time']));
            $summaryDays[$k]['firstlight'] = date('g:ia', strtotime($tideSunrise[$tk]['firstlight_time']));
            $summaryDays[$k]['sunset'] = date('g:ia', strtotime($tideSunrise[$tk]['sunset_time']));
            $summaryDays[$k]['lastlight'] = date('g:ia', strtotime($tideSunrise[$tk]['lastlight_time']));

            if ($k+1 == 3)
                $fcstData3['days'] = $summaryDays;
        }
        //Log::info('summary days: '.print_r($summaryDays,true));

        //sort tide data, so to calculate each hour tide data
        $tideArray = array();
        foreach ($tideSunrise as $k => $tide) {
            if (isset($tide['high_1_time']) and isset($tide['low_1_time'])) {

                if ($tide['high_1_time'] < $tide['low_1_time']) {
                    $tideArray[] = array(
                        'level' => $tide['high_1_height'],
                        'time' => strtotime($tide['high_1_time']),
                        'type' => 'high'
                    );

                    $tideArray[] = array(
                        'level' => $tide['low_1_height'],
                        'time' => strtotime($tide['low_1_time']),
                        'type' => 'low'
                    );

                    if (isset($tide['high_2_time'])) {
                        $tideArray[] = array(
                            'level' => $tide['high_2_height'],
                            'time' => strtotime($tide['high_2_time']),
                            'type' => 'high'
                        );
                    }

                    if (isset($tide['low_2_time'])) {
                        $tideArray[] = array(
                            'level' => $tide['low_2_height'],
                            'time' => strtotime($tide['low_2_time']),
                            'type' => 'low'
                        );
                    }

                } else {
                    $tideArray[] = array(
                        'level' => $tide['low_1_height'],
                        'time' => strtotime($tide['low_1_time']),
                        'type' => 'low'
                    );

                    $tideArray[] = array(
                        'level' => $tide['high_1_height'],
                        'time' => strtotime($tide['high_1_time']),
                        'type' => 'high'
                    );

                    if (isset($tide['low_2_time'])) {
                        $tideArray[] = array(
                            'level' => $tide['low_2_height'],
                            'time' => strtotime($tide['low_2_time']),
                            'type' => 'low'
                        );
                    }

                    if (isset($tide['high_2_time'])) {
                        $tideArray[] = array(
                            'level' => $tide['high_2_height'],
                            'time' => strtotime($tide['high_2_time']),
                            'type' => 'high'
                        );
                    }
                }

            } else if (isset($tide['high_1_time'])) {
                $tideArray[] = array(
                    'level' => $tide['high_1_height'],
                    'time' => strtotime($tide['high_1_time']),
                    'type' => 'high'
                );

            } else if (isset($tide['low_1_time'])) {
                $tideArray[] = array(
                    'level' => $tide['low_1_height'],
                    'time' => strtotime($tide['low_1_time']),
                    'type' => 'low'
                );
            }
        }
        //find which tide value to start with in timeline
        if ($test) {         //adjust tide dates for testing to match
            $surfTideTimeDiff = ($surfSummaryTimestamp - strtotime($tideSunrise[0]['high_1_time']) - 86400);
            foreach ($tideArray as $k => $tide)
                    $tideArray[$k]['time'] = $tide['time'] + $surfTideTimeDiff;
        }
        foreach ($tideArray as $k => $tide) {
             if ($k > 0 and $tide['time'] > $timeline['surf']['timestamp']) {
                $tidek = $k - 1;
                break;
            }
        }

        //now put together all timeline data
        $timelineData = array();
        $timelineData3 = array();

        $timestamps = array();
        $timestamp3day = false;

        $condGroup = array();
        $day3Hours = $this->forecast3days; //number of hours to include in 3 day timeline
        foreach ($timeline['surf']['wn_tot'] as $k => $wnTot) {
            $timestamp = $timeline['surf']['timestamp'] + ($k * 3600);
            if ($k == 0)
                $timelineData['timestamp'] = $timestamp;
            $timestamps[$k] = $timestamp;
            $timelineData['time'][$k] = date('ga',$timestamp);
            $timelineData['day'][$k] = date('D', $timestamp);
            $timelineData['date'][$k] = date('n/j', $timestamp);
            $timelineData['wvhtBodySize'][$k] = $this->weatherCaptainApi->surfIntegerToBodyHeight($wnTot);
            //$timelineData['wvhtText'][$k] = $this->weatherCaptainApi->surfIntegerToText($wnTot,$units->wvht_unit);
            $timelineData['wvhtText'][$k] = $wnTot;

            $ck = $k + $surfCondHourDiff;
            $condGroup[$k] = (isset($timeline['cond']['cond'][$ck])) ? $this->weatherCaptainApi->condIntegerToGroup($timeline['cond']['cond'][$ck]) : 0;
            $timelineData['condDesc'][$k] = (isset($timeline['cond']['cond'][$ck])) ? $this->weatherCaptainApi->condIntegerToText($timeline['cond']['cond'][$ck]) : false;
            $timelineData['condText'][$k] = $this->weatherCaptainApi->condGroupIntegerToText($condGroup[$k]);

            $timelineData['windDir'][$k] = (isset($timeline['cond']['wind_dir'][$ck])) ? strtolower($timeline['cond']['wind_dir'][$ck]) : false;
            $timelineData['wind'][$k] = (isset($timeline['cond']['wind_dir'][$ck])) ? $timeline['cond']['wind_dir'][$ck].' <u>'.$timeline['cond']['wind_spd'][$ck].'</u>': '';

            $timelineData['swell1Dir'][$k] = strtolower($this->weatherCaptainApi->dirText($timeline['surf']['swell1']['directionTextDeg'][$k]));
            $timelineData['swell1'][$k] = $this->weatherCaptainApi->dirText($timeline['surf']['swell1']['directionTextDeg'][$k]).' ('.
                        $timeline['surf']['swell1']['directionTextDeg'][$k].') '.
                        '<u>'.$timeline['surf']['swell1']['waveHeight'][$k].'</u> @ '.
                        $timeline['surf']['swell1']['wavePeriod'][$k].' sec';

            $timelineData['swell2Dir'][$k] = strtolower($this->weatherCaptainApi->dirText($timeline['surf']['swell2']['directionTextDeg'][$k]));
            $timelineData['swell2'][$k] = $this->weatherCaptainApi->dirText($timeline['surf']['swell2']['directionTextDeg'][$k]).' ('.
                $timeline['surf']['swell2']['directionTextDeg'][$k].') '.
                '<u>'.$timeline['surf']['swell2']['waveHeight'][$k].'</u> @ '.
                $timeline['surf']['swell2']['wavePeriod'][$k].' sec';

            if ($timeline['surf']['swell2']['waveHeight'][$k] == 0 or $timelineData['swell2'][$k] == $timelineData['swell1'][$k]) {
                $timelineData['swell2'][$k] = '';
                $timelineData['swell2Dir'][$k] = '';
            }

            //tide levels
            if (isset($tideArray[$tidek+1]) and $timestamp > $tideArray[$tidek+1]['time'])
                $tidek++;

            if (isset($tideArray[$tidek+1])) {
                $tideLevels = $this->tideLevels($tideArray[$tidek], $tideArray[$tidek + 1], $timestamp);
                $timelineData['tideLevel'][$k] = $tideLevels['tideLevel'];
                $timelineData['tideLevelDesc'][$k] = $tideLevels['tideLevelDesc'];
            } else {
                $timelineData['tideLevel'][$k] = 'N/A';
                $timelineData['tideLevelDesc'][$k] = 'N/A';
            }

            //put together swell data
            $timelineData['swell']['swell1'][$k] = ($timeline['swell']['wvht'][$k][0] > 0)
                ? '<u>'.$timeline['swell']['wvht'][$k][0].'</u> @ '.
                  $timeline['swell']['wvper'][$k][0].'sec'.' - '.$this->weatherCaptainApi->dirText($timeline['swell']['wvdir'][$k][0]).
                  ' '.$timeline['swell']['wvdir'][$k][0].'&deg;'
                  : '';
            $timelineData['swell']['swell2'][$k] = ($timeline['swell']['wvht'][$k][1] > 0)
                ? '<u>'.$timeline['swell']['wvht'][$k][1].'</u> @ '.
                $timeline['swell']['wvper'][$k][1].'sec'.' - '.$this->weatherCaptainApi->dirText($timeline['swell']['wvdir'][$k][1]).
                ' '.$timeline['swell']['wvdir'][$k][1].'&deg;'
                : '';
            $timelineData['swell']['swell3'][$k] = ($timeline['swell']['wvht'][$k][2] > 0)
                ? '<u>'.$timeline['swell']['wvht'][$k][2].'</u> @ '.
                $timeline['swell']['wvper'][$k][2].'sec'.' - '.$this->weatherCaptainApi->dirText($timeline['swell']['wvdir'][$k][2]).
                ' '.$timeline['swell']['wvdir'][$k][2].'&deg;'
                : '';
            $timelineData['swell']['swell4'][$k] = ($timeline['swell']['wvht'][$k][3] > 0)
                ? '<u>'.$timeline['swell']['wvht'][$k][3].'</u> @ '.
                $timeline['swell']['wvper'][$k][3].'sec'.' - '.$this->weatherCaptainApi->dirText($timeline['swell']['wvdir'][$k][3]).
                ' '.$timeline['swell']['wvdir'][$k][3].'&deg;'
                : '';
            $timelineData['swell']['swell5'][$k] = ($timeline['swell']['wvht'][$k][4] > 0)
                ? '<u>'.$timeline['swell']['wvht'][$k][4].'</u> @ '.
                $timeline['swell']['wvper'][$k][4].'sec'.' - '.$this->weatherCaptainApi->dirText($timeline['swell']['wvdir'][$k][4]).
                ' '.$timeline['swell']['wvdir'][$k][4].'&deg;'
                : '';
            $timelineData['swell']['swell6'][$k] = ($timeline['swell']['wvht'][$k][5] > 0)
                ? '<u>'.$timeline['swell']['wvht'][$k][5].'</u> @ '.
                $timeline['swell']['wvper'][$k][5].'sec'.' - '.$this->weatherCaptainApi->dirText($timeline['swell']['wvdir'][$k][5]).
                ' '.$timeline['swell']['wvdir'][$k][5].'&deg;'
                : '';

            if ($k+1 == $day3Hours) {
                $timelineData3 = $timelineData;
                $timestamp3day = $timestamp;
            }
        }

        //tide svg
        //use tide arrays, but replace first and last tide with the computed time/level, so it matches timeline start/end
        $firstTide = false;
        $tideArray3 = array();
        foreach ($tideArray as $k => $tide) {

            //if before first timeline time
            if ($tide['time'] < $timeline['surf']['timestamp']) {
                unset($tideArray[$k]);

            } else if ($firstTide === false) {
                array_unshift($tideArray,
                    array(
                        'level' => $timelineData['tideLevel'][0],
                        'time' => $timeline['surf']['timestamp'])
                );
                $firstTide = $k;
            }

            //if after timeline time
            if (!$tideArray3 and isset($timestamp3day) and $tide['time'] > $timestamp3day) {
                $tideArray3 = array_slice($tideArray, 0, ($k - $firstTide + 1));
                array_push($tideArray3,
                    array(
                        'level' => $timelineData['tideLevel'][count($timelineData3['tideLevel']) - 1],
                        'time' => $timestamp3day
                    )
                );

            } else if ($tide['time'] > $timestamp) {
                $tideArray = array_slice($tideArray,0,($k - $firstTide + 1));
                array_push($tideArray,
                    array(
                        'level' => $timelineData['tideLevel'][count($timelineData['tideLevel'])-1],
                        'time' => $timestamp
                    )
                );
                break;
            }
        }

        $timelineData['tideSvg'] = $this->createTideSvg($tideArray,954,70);
        if ($timestamp3day)
            $timelineData3['tideSvg'] = $this->createTideSvg($tideArray3,954,70);

        //create timeline svg images
        $sunriseHour = date('G',strtotime($tideSunrise[0]['sunrise_time']));
        $sunriseMinutes = date('i',strtotime($tideSunrise[0]['sunrise_time']));
        $sunriseHour = $sunriseHour + ($sunriseMinutes / 60);

        $sunsetHour = date('G',strtotime($tideSunrise[0]['sunset_time'])) - 12; //convert from military time
        $sunsetMinutes = date('i',strtotime($tideSunrise[0]['sunset_time']));
        $sunsetHour = $sunsetHour + ($sunsetMinutes / 60);

        $timelineData['baseGraph'] = $this->createTimelineBaseGraph($timelineData,$sunriseHour,$sunsetHour);
        $timelineData['surfSvg'] = $this->createTimelineSurfSvg($timeline['surf']['wn_tot'],$condGroup, 954,126);
        $swellSvg = $this->createTimelineSwellSvg($timeline['swell']['wvht'],954,126);
        $timelineData['swellSvg'] = $swellSvg['svg'];
        $timelineData['swellLevels'] = $swellSvg['ticks']['ft'];
        $timelineData['swellLevelsMeters'] = $swellSvg['ticks']['m'];
        $fcstData['timeline'] = $timelineData;
        $fcstData['days'] = $summaryDays;
        $fcstData['sst'] = $data['sst'];
        $fcstData['wetsuit'] = $this->fetchBestWetsuit($data['sst']);
        $fcstData['expires'] = time() + (3600 * 6);

        if ($timestamp3day) {
            $timelineData3['baseGraph'] = $this->createTimelineBaseGraph($timelineData3, $sunriseHour, $sunsetHour);
            $timelineData3['surfSvg'] = $this->createTimelineSurfSvg(array_slice($timeline['surf']['wn_tot'], 0, $day3Hours), array_slice($condGroup, 0, $day3Hours), 954, 126);
            $swellSvg = $this->createTimelineSwellSvg(array_slice($timeline['swell']['wvht'],0,$day3Hours),954,126);
            $timelineData3['swellSvg'] = $swellSvg['svg'];
            $timelineData3['swellLevels'] = $swellSvg['ticks']['ft'];
            $timelineData3['swellLevelsMeters'] = $swellSvg['ticks']['m'];
            $fcstData3['timeline'] = $timelineData3;
            $fcstData3['sst'] = $data['sst'];
            $fcstData3['wetsuit'] = $fcstData['wetsuit'];
            $fcstData3['expires'] = $fcstData['expires'];
        }

        return array(
            json_encode($fcstData, JSON_UNESCAPED_SLASHES),
            json_encode($fcstData3, JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Which low tide are we closer to, the am or pm low tide
     *
     * @param $low_am string
     * @param $low_pm string
     * @param $gmtOffset integer
     * @return string
     */
    public function closestLowTide($low_am, $low_pm, $gmtOffset)
    {
        $lowAmTime = substr($low_am,0,strpos($low_am,'@'));

        if (! $low_pm)
            return $lowAmTime;
        else
            $lowPmTime = substr($low_pm,0,strpos($low_pm,'@'));

        $now = time() + $gmtOffset;

        return (abs($now - strtotime($lowAmTime)) < abs($now - strtotime($lowPmTime))) ? $lowAmTime : $lowPmTime;
    }

    /**
     * Which high tide are we closer to the, the am or pm high tide.
     *
     * @param $high_am string
     * @param $high_pm string
     * @param $gmtOffset integer
     * @return string
     */
    public function closestHighTide($high_am, $high_pm, $gmtOffset)
    {
        $highAmTime = substr($high_am,0,strpos($high_am,'@'));

        if (! $high_pm)
            return $highAmTime;
        else
            $highPmTime = substr($high_pm,0,strpos($high_pm,'@'));

        $now = time() + $gmtOffset;

        return (abs($now - strtotime($highAmTime)) < abs($now - strtotime($highPmTime))) ? $highAmTime : $highPmTime;
    }

    public function getWeather($location)
    {
        $params = array(
            'atmp_id' => $location->atmp_station_id,
            'sky_id' => $location->sky_station_id,
            'wind_id' => $location->wind_station_id,
            'wave_id' => $location->buoy_station_id
        );

        $weatherData = $this->weatherCaptainApi->getCurrentWeather($params);

        if (! $weatherData['error'] and $weatherData['data']) {
            $this->weather = json_encode($weatherData['data']);
            $this->save();

            //update station ids if any station comes back as inactive
            $updatedStations = array();
            foreach ($weatherData['data'] as $k => $var) {
                if ($var == false) {
                    if ($k == 'atmp' and $params['atmp_id'] and !in_array($params['atmp_id'], $updatedStations)) {
                        WxStation::where('station_id', $params['atmp_id'])->update(['active' => 0]);
                        $updatedStations[] = $params['atmp_id'];
                    } else if ($k == 'sky' and $params['sky_id'] and !in_array($params['sky_id'], $updatedStations)) {
                        WxStation::where('station_id', $params['sky_id'])->update(['active' => 0]);
                        $updatedStations[] = $params['sky_id'];
                    } else if ($k == 'wind' and $params['wind_id'] and !in_array($params['wind_id'], $updatedStations)) {
                        WxStation::where('station_id', $params['wind_id'])->update(['active' => 0]);
                        $updatedStations[] = $params['wind_id'];
                    } else if ($k == 'wvht' and $params['wave_id'] and !in_array($params['wind_id'], $updatedStations)) {
                        WxStation::where('station_id', $params['wave_id'])->update(['active' => 0]);
                        $updatedStations[] = $params['wave_id'];
                    }
                }
            }
            if ($updatedStations)
                $location->updateLocationStations();

            return $weatherData['data'];

        } else if ($weatherData['error']){

                log::channel('wc_api')->error($weatherData['error']);
                $users = User::where('role_id',1)->get();
                $notification = array(
                    'subject' => 'Fetch Weather API error',
                    'error' => $weatherData['error']
                );
                Notification::send($users, new ApiError($notification));
        }

        return array(
            'wind_dir' => '',
            'wind_spd' => '',
            'wind_gst' => '',
            'atmp' => '',
            'sky' => '',
            'wvht' => '',
            'wvper' => ''
        );
    }

    public function convertHeight($data,$unit,$addUnitString=true) {
        return preg_replace_callback(
            '^<u>(.*)</u>^',
            function ($matches) use ($unit,$addUnitString) {
                return $this->weatherCaptainApi->convertHeight($matches[1],trim($unit)) . (($addUnitString) ? $unit : '');
            },
            $data
        );
    }

    public function convertTemp($data,$unit,$addUnitString=true) {
        return preg_replace_callback(
            '^<u>(.*)</u>^',
            function ($matches) use ($unit,$addUnitString) {
                return $this->weatherCaptainApi->convertTemp($matches[1],trim($unit)) .'&deg;'. (($addUnitString) ? $unit : '');
            },
            $data
        );
    }

    public function convertWindSpeed($data,$unit,$addUnitString=true) {
        return preg_replace_callback(
            '^<u>(.*)</u>^',
            function ($matches) use ($unit,$addUnitString) {
                return $this->weatherCaptainApi->convertWindSpeed($matches[1],trim($unit)) . (($addUnitString) ? $unit : '');
            },
            $data
        );
    }

    /**
     * This function takes the entire location forecast data arrays, and converts necessary units
     *
     * @param array $data
     * @return array
     */

    public function convertUnits($data,$units) {

        //string replace all fields that need to be converted
        //$this->weatherCaptainApi->surfIntegerToText($summary['surf']['am'][$k],$units->wvht_unit);

        if ($units->wvht_unit == 'm')
            $data['timeline']['swellLevels'] = $data['timeline']['swellLevelsMeters'];

        foreach ($data['days'] as $k => $day) {

            $data['days'][$k]['condTxt'] = $this->weatherCaptainApi->convertWindSpeedRange($day['condTxt'],$units->wind_unit);
            $data['days'][$k]['amsurf'] = $this->weatherCaptainApi->surfIntegerToText($day['amsurf'],$units->wvht_unit);
            $data['days'][$k]['pmsurf'] = $this->weatherCaptainApi->surfIntegerToText($day['pmsurf'],$units->wvht_unit);

            foreach ($day['hourly'] as $h => $hour) {
                $data['days'][$k]['hourly'][$h]['swell1'] = $this->convertHeight($hour['swell1'], ' '.$units->wvht_unit);
                $data['days'][$k]['hourly'][$h]['swell2'] = $this->convertHeight($hour['swell2'], ' '.$units->wvht_unit);
                $data['days'][$k]['hourly'][$h]['temp'] = $this->convertTemp($hour['temp'], $units->temp_unit,false);
                $data['days'][$k]['hourly'][$h]['wind'] = $this->convertWindSpeed($hour['wind'], ' '.$units->wind_unit);
            }

            $data['days'][$k]['low_am'] = $this->convertHeight($day['low_am'],$units->tide_unit);
            $data['days'][$k]['low_pm'] = $this->convertHeight($day['low_pm'],$units->tide_unit);
            $data['days'][$k]['high_am'] = $this->convertHeight($day['high_am'],$units->tide_unit);
            $data['days'][$k]['high_pm'] = $this->convertHeight($day['high_pm'],$units->tide_unit);
        }

        $wvhtText = array();
        $tideLevel = array();
        foreach ($data['timeline']['wvhtText'] as $k => $text) {
            $wvhtText[] = $this->weatherCaptainApi->surfIntegerToText($text, $units->wvht_unit);
            $tideLevel[] = $this->weatherCaptainApi->convertHeight($data['timeline']['tideLevel'][$k], $units->tide_unit).' '.$units->tide_unit;
        }
        $data['timeline']['wvhtText'] = $wvhtText;
        $data['timeline']['tideLevel'] = $tideLevel;

        $data['timeline']['swell1'] = $this->convertHeight($data['timeline']['swell1'],' '.$units->wvht_unit);
        $data['timeline']['swell2'] = $this->convertHeight($data['timeline']['swell2'],' '.$units->wvht_unit);
        $data['timeline']['wind'] = $this->convertWindSpeed($data['timeline']['wind'],' '.$units->wind_unit);
        $data['timeline']['swell']['swell1'] = $this->convertHeight($data['timeline']['swell']['swell1'],' '.$units->wvht_unit);
        $data['timeline']['swell']['swell2'] = $this->convertHeight($data['timeline']['swell']['swell2'],' '.$units->wvht_unit);
        $data['timeline']['swell']['swell3'] = $this->convertHeight($data['timeline']['swell']['swell3'],' '.$units->wvht_unit);
        $data['timeline']['swell']['swell4'] = $this->convertHeight($data['timeline']['swell']['swell4'],' '.$units->wvht_unit);
        $data['timeline']['swell']['swell5'] = $this->convertHeight($data['timeline']['swell']['swell5'],' '.$units->wvht_unit);
        $data['timeline']['swell']['swell6'] = $this->convertHeight($data['timeline']['swell']['swell6'],' '.$units->wvht_unit);

        return $data;
    }

    public function convertWeatherUnits($weather,$units,$wind_gust=true) {

        if ($weather['wind_spd'] !== false) {
            $weather['wind_spd'] = $this->weatherCaptainApi->convertWindSpeed($weather['wind_spd'],$units->wind_unit);
            if ($wind_gust and $weather['wind_gst'] != $weather['wind_spd'] and $weather['wind_gst'] != '0')
                $weather['wind_spd'] .= ' &rarr; '.$this->weatherCaptainApi->convertWindSpeed($weather['wind_gst'],$units->wind_unit);
            $weather['wind_spd'] .= $units->wind_unit;
        }

        if ($weather['atmp'])
            $weather['atmp'] = $this->weatherCaptainApi->convertTemp($weather['atmp'],$units->temp_unit);

        if ($weather['wvht'])
            $weather['wvht'] = $this->weatherCaptainApi->convertHeight($weather['wvht'],$units->wvht_unit).' '.$units->wvht_unit;

        //if ($weather['sst'])
        //    $weather['sst'] = $this->weatherCaptainApi->convertTemp($weather['sst'],$units->temp_unit);

        return $weather;
    }

    public function fetchBestWetsuit($wtmp) {
        if ($wtmp > 79 )
            return 'Trunks';
        else if ($wtmp > 73)
            return 'Trunks';
        else if ($wtmp > 69)
            return 'Spring Suit';
        else if ($wtmp > 65)
            return 'Long Arm Spring';
        else if ($wtmp > 59)
            return '3/2 Wetsuit';
        else if ($wtmp > 56)
            return '3/2 Wetsuit, Boots';
        else if ($wtmp > 52)
            return '4/3, Boots/Gloves(3)';
        else if ($wtmp > 49)
            return '4/3, Boots/Gloves(3+)';
        else if ($wtmp > 43)
            return '5/3, Boots/Gloves(5)';
        else
            return '5/4+, Boots/Gloves(5+)';
    }
}
