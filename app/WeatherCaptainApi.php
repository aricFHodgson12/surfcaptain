<?php

namespace App;

use Illuminate\Support\Facades\Log;

class WeatherCaptainApi
{
    public function callApi($method,$parameters='',$timeout=false,$request='get') {
        $authorization = "Authorization: Bearer ".setting('site.weather_captain_api_key');

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if ($request == 'get') {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json' , $authorization ));
            curl_setopt($curl, CURLOPT_URL, 'https://api.weathercaptain.com/api/' . $method . '?' . $parameters);
        } else if ($request == 'post') {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded", $authorization));
            curl_setopt($curl, CURLOPT_URL,'https://api.weathercaptain.com/api/'.$method);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl,CURLOPT_POSTFIELDS, $parameters);
        }
        curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');

        if ($timeout)
            curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);

        $return = json_decode(curl_exec($curl),true);

        //print_r($return,true);

        if(curl_errno($curl)) {
            log::channel('wc_api')->info(curl_error($curl));
            $return['error'] = 'there was an api error';
        }
        curl_close($curl);

        if (!$return) //if nothing is returned, its a server error
            $return['error'] = 'There was a server error during the Weather Captain API request to: '.$method;

        return $return;
    }


    public function getForecast($locations)
    {
        return $this->callApi('surf',http_build_query($locations),false,'post');
    }

    public function getCurrentWeather($params)
    {
        return $this->callApi('weather',http_build_query($params));
    }

    public function getWeatherStations()
    {
        return $this->callApi('weather/stations');
    }

    public function getInactiveWeatherStations()
    {
        return $this->callApi('weather/inactive-stations');
    }

    public function getModelIds($params) {
        return $this->callApi('utility/model-ids',http_build_query($params));
    }

    public function condIntegerToGroup($int) {
        if ($int <= 3)
            $cond_group = 1;
        else if ($int <= 9)
            $cond_group = 2;
        else
            $cond_group = 3;

        return $cond_group;
    }

    public $noCondText = 'none';

    public function condIntegerToGroupText($int) {
        if (!$int)
            $cond_text = 'none';
        else if ($int <= 3)
            $cond_text = 'clean';
        else if ($int <= 9)
            $cond_text = 'fair';
        else
            $cond_text = 'choppy';

        return $cond_text;
    }

    public function condGroupIntegerToText($int) {
        if ($int == 1)
            $cond_text = 'clean';
        else if ($int == 2)
            $cond_text = 'fair';
        else if ($int == 3)
            $cond_text = 'choppy';
        else
            $cond_text = 'none';

        return $cond_text;
    }

    public function condTextToInteger($condText) {
        if ($condText == 'clean')
            return 1;
        else if ($condText == 'fair')
            return 2;
        else if ($condText == 'choppy')
            return 3;
        else
            return 0;
    }

    public function condIntegerToText($int)
    {
        if ($int == 1) return  'clean';
        else if ($int == 2) return 'glassy';
        else if ($int == 3) return 'fairly clean';
        else if ($int == 4) return 'semi clean/textured';
        else if ($int == 5) return 'semi clean w/ sideshore texture';
        else if ($int == 6) return 'fair w/ sideshore texture';
        else if ($int == 7) return 'semi glassy';
        else if ($int == 8) return 'light sideshore texture';
        else if ($int == 9) return 'semi glassy/bumpy';
        else if ($int == 10) return 'sideshore choppy';
        else if ($int == 11) return 'sideshore choppy';
        else if ($int == 12) return 'choppy w/ sideshore current';
        else if ($int == 13) return 'choppy w/ strong current';
        else if ($int == 14) return 'semi choppy';
        else if ($int == 15) return 'choppy';
        else if ($int == 16) return 'choppy and disorganized';
        else if ($int == 17) return 'bumpy/semi bumpy';
        else if ($int == 18) return 'choppy';
        else if ($int == 19) return 'blown out';
        else return false;
    }

    public function surfIntegerToText($int, $units='ft') {
        $wvht['int'][]= ''; $wvht['ft'][]='N/A' ; $wvht['m'][]='N/A';
        $wvht['int'][]= 0; $wvht['ft'][]='flat' ; $wvht['m'][]='flat';
        $wvht['int'][]= 1; $wvht['ft'][]='flat' ; $wvht['m'][]='flat';
        $wvht['int'][]= 2; $wvht['ft'][]='flat' ; $wvht['m'][]='flat';
        $wvht['int'][]= 3; $wvht['ft'][]='0-1 ft' ; $wvht['m'][]='0.3 m';
        $wvht['int'][]= 4; $wvht['ft'][]='0-1 ft' ; $wvht['m'][]='0.3 m';
        $wvht['int'][]= 5; $wvht['ft'][]='1 ft' ; $wvht['m'][]='0.3 m';
        $wvht['int'][]= 6; $wvht['ft'][]='1 ft' ; $wvht['m'][]='0.3 m';
        $wvht['int'][]= 7; $wvht['ft'][]='1 ft' ; $wvht['m'][]='0.4 m';
        $wvht['int'][]= 8; $wvht['ft'][]='1-2 ft' ; $wvht['m'][]='0.5 m';
        $wvht['int'][]= 9; $wvht['ft'][]='1-2 ft' ; $wvht['m'][]='0.5 m';
        $wvht['int'][]= 10; $wvht['ft'][]='1-2 ft' ; $wvht['m'][]='0.6 m';
        $wvht['int'][]= 11; $wvht['ft'][]='1-2 ft' ; $wvht['m'][]='0.6 m';
        $wvht['int'][]= 12; $wvht['ft'][]='1-2 ft' ; $wvht['m'][]='0.7 m';
        $wvht['int'][]= 13; $wvht['ft'][]='1-2 ft' ; $wvht['m'][]='0.7 m';
        $wvht['int'][]= 14; $wvht['ft'][]='2 ft' ; $wvht['m'][]='0.6 m';
        $wvht['int'][]= 15; $wvht['ft'][]='2+ ft' ; $wvht['m'][]='0.8 m';
        $wvht['int'][]= 16; $wvht['ft'][]='1-3 ft' ; $wvht['m'][]='0.9 m';
        $wvht['int'][]= 17; $wvht['ft'][]='1-3 ft' ; $wvht['m'][]='0.9 m';
        $wvht['int'][]= 18; $wvht['ft'][]='2-3 ft' ; $wvht['m'][]='0.9 m';
        $wvht['int'][]= 19; $wvht['ft'][]='2-3 ft' ; $wvht['m'][]='1.0 m';
        $wvht['int'][]= 20; $wvht['ft'][]='3 ft' ; $wvht['m'][]='1.0 m';
        $wvht['int'][]= 21; $wvht['ft'][]='2-3+ ft' ; $wvht['m'][]='1.0 m';
        $wvht['int'][]= 22; $wvht['ft'][]='2-3+ ft' ; $wvht['m'][]='1.0 m';
        $wvht['int'][]= 23; $wvht['ft'][]='3+ ft' ; $wvht['m'][]='1.1 m';
        $wvht['int'][]= 24; $wvht['ft'][]='3+ ft' ; $wvht['m'][]='1.1 m';
        $wvht['int'][]= 25; $wvht['ft'][]='3+ ft' ; $wvht['m'][]='1.1 m';
        $wvht['int'][]= 26; $wvht['ft'][]='2-4 ft' ; $wvht['m'][]='1.2 m';
        $wvht['int'][]= 27; $wvht['ft'][]='2-4 ft' ; $wvht['m'][]='1.2 m';
        $wvht['int'][]= 28; $wvht['ft'][]='2-4 ft' ; $wvht['m'][]='1.3 m';
        $wvht['int'][]= 29; $wvht['ft'][]='2-4 ft' ; $wvht['m'][]='1.3 m';
        $wvht['int'][]= 30; $wvht['ft'][]='2-4 ft' ; $wvht['m'][]='1.3 m';
        $wvht['int'][]= 31; $wvht['ft'][]='3-4 ft' ; $wvht['m'][]='1.3 m';
        $wvht['int'][]= 32; $wvht['ft'][]='3-4 ft' ; $wvht['m'][]='1.3 m';
        $wvht['int'][]= 33; $wvht['ft'][]='3-4+ ft' ; $wvht['m'][]='1.4 m';
        $wvht['int'][]= 34; $wvht['ft'][]='3-4+ ft' ; $wvht['m'][]='1.4 m';
        $wvht['int'][]= 35; $wvht['ft'][]='3-4+ ft' ; $wvht['m'][]='1.4 m';
        $wvht['int'][]= 36; $wvht['ft'][]='3-4+ ft' ; $wvht['m'][]='1.4 m';
        $wvht['int'][]= 37; $wvht['ft'][]='3-4+ ft' ; $wvht['m'][]='1.4 m';
        $wvht['int'][]= 38; $wvht['ft'][]='3-5 ft' ; $wvht['m'][]='1.5 m';
        $wvht['int'][]= 39; $wvht['ft'][]='3-5 ft' ; $wvht['m'][]='1.5 m';
        $wvht['int'][]= 40; $wvht['ft'][]='3-5 ft' ; $wvht['m'][]='1.5 m';
        $wvht['int'][]= 41; $wvht['ft'][]='3-5 ft' ; $wvht['m'][]='1.5 m';
        $wvht['int'][]= 42; $wvht['ft'][]='3-5 ft' ; $wvht['m'][]='1.6 m';
        $wvht['int'][]= 43; $wvht['ft'][]='3-5 ft' ; $wvht['m'][]='1.6 m';
        $wvht['int'][]= 44; $wvht['ft'][]='3-5 ft' ; $wvht['m'][]='1.6 m';
        $wvht['int'][]= 45; $wvht['ft'][]='4-5 ft' ; $wvht['m'][]='1.6 m';
        $wvht['int'][]= 46; $wvht['ft'][]='4-5 ft' ; $wvht['m'][]='1.6 m';
        $wvht['int'][]= 47; $wvht['ft'][]='4-5 ft' ; $wvht['m'][]='1.6 m';
        $wvht['int'][]= 48; $wvht['ft'][]='4-5 ft' ; $wvht['m'][]='1.6 m';
        $wvht['int'][]= 49; $wvht['ft'][]='4-5 ft' ; $wvht['m'][]='1.6 m';
        $wvht['int'][]= 50; $wvht['ft'][]='4-6 ft' ; $wvht['m'][]='1.7 m';
        $wvht['int'][]= 51; $wvht['ft'][]='4-6 ft' ; $wvht['m'][]='1.7 m';
        $wvht['int'][]= 52; $wvht['ft'][]='4-6+ ft' ; $wvht['m'][]='2.0 m';
        $wvht['int'][]= 53; $wvht['ft'][]='4-6+ ft' ; $wvht['m'][]='2.0 m';
        $wvht['int'][]= 54; $wvht['ft'][]='4-7 ft' ; $wvht['m'][]='2.1 m';
        $wvht['int'][]= 55; $wvht['ft'][]='4-7 ft' ; $wvht['m'][]='2.1 m';
        $wvht['int'][]= 56; $wvht['ft'][]='5-7 ft' ; $wvht['m'][]='2.2 m';
        $wvht['int'][]= 57; $wvht['ft'][]='5-7 ft' ; $wvht['m'][]='2.2 m';
        $wvht['int'][]= 58; $wvht['ft'][]='5-8 ft' ; $wvht['m'][]='2.3 m';
        $wvht['int'][]= 59; $wvht['ft'][]='5-8 ft' ; $wvht['m'][]='2.3 m';
        $wvht['int'][]= 60; $wvht['ft'][]='6-8 ft' ; $wvht['m'][]='2.4 m';
        $wvht['int'][]= 61; $wvht['ft'][]='6-8 ft' ; $wvht['m'][]='2.4 m';
        $wvht['int'][]= 62; $wvht['ft'][]='6-8+ ft' ; $wvht['m'][]='2.5 m';
        $wvht['int'][]= 63; $wvht['ft'][]='6-8+ ft' ; $wvht['m'][]='2.5 m';
        $wvht['int'][]= 64; $wvht['ft'][]='6-9 ft' ; $wvht['m'][]='2.7 m';
        $wvht['int'][]= 65; $wvht['ft'][]='6-9 ft' ; $wvht['m'][]='2.7 m';
        $wvht['int'][]= 66; $wvht['ft'][]='6-10 ft' ; $wvht['m'][]='2.9 m';
        $wvht['int'][]= 67; $wvht['ft'][]='8-10 ft' ; $wvht['m'][]='3.0 m';
        $wvht['int'][]= 68; $wvht['ft'][]='8-10+ ft' ; $wvht['m'][]='3.2 m';
        $wvht['int'][]= 69; $wvht['ft'][]='8-10+ ft' ; $wvht['m'][]='3.2 m';
        $wvht['int'][]= 70; $wvht['ft'][]='8-12 ft' ; $wvht['m'][]='3.4 m';
        $wvht['int'][]= 71; $wvht['ft'][]='9-12 ft' ; $wvht['m'][]='3.4 m';
        $wvht['int'][]= 72; $wvht['ft'][]='10-12+ ft' ; $wvht['m'][]='3.5 m';
        $wvht['int'][]= 73; $wvht['ft'][]='12-14 ft' ; $wvht['m'][]='3.5 m';
        $wvht['int'][]= 74; $wvht['ft'][]='10-15 ft' ; $wvht['m'][]='4.0 m';
        $wvht['int'][]= 75; $wvht['ft'][]='10-15+ ft' ; $wvht['m'][]='4.0 m';
        $wvht['int'][]= 76; $wvht['ft'][]='12-16 ft' ; $wvht['m'][]='4.5 m';
        $wvht['int'][]= 77; $wvht['ft'][]='12-16 ft' ; $wvht['m'][]='4.5 m';
        $wvht['int'][]= 78; $wvht['ft'][]='12-16+ ft' ; $wvht['m'][]='5.0 m';
        $wvht['int'][]= 79; $wvht['ft'][]='15-20 ft' ; $wvht['m'][]='5.0 m';
        $wvht['int'][]= 80; $wvht['ft'][]='15-25+ ft' ; $wvht['m'][]='5-7+ m';

        if ($int === '')
            return 'N/A';

        else if ($int === 0)
            return 'flat';

        else {
            $key = array_search((int)$int,$wvht['int']);
            return ($wvht[$units][$key]);
        }
    }

    public function surfIntegerToBodyHeight($int) {

        if ($int < 7)
            return 'flat';
        else if ($int < 16)
            return 'knee';
        else if ($int < 26)
            return 'waist';
        else if ($int < 32)
            return 'chest';
        else if ($int < 45)
            return 'shoulder';
        else if ($int < 54)
            return 'head';
        else if ($int < 66)
            return 'ohead';
        else if ($int < 73)
            return 'dohead';
        else
            return 'dohead';
    }

    public function convertWindSpeedRange($text,$units = 'mph') {
        if ($units == 'mph')
            return $text;

        $spdmph = array();
        $spdmph[0] = "less than 5mph";
        $spdmph[1] = "5-10mph";
        $spdmph[2] = "10-15mph";
        $spdmph[3] = "15-20mph";
        $spdmph[4] = "20-25mph";
        $spdmph[5] = "25-30mph";
        $spdmph[6] = "30-35mph";
        $spdmph[7] = "35-40mph";
        $spdmph[8] = "40-45mph";
        $spdmph[9] = "45-50mph";
        $spdmph[10] = "50+mph";

        $spdkph = array();
        $spdkph[0] = "less than 8kph";
        $spdkph[1] = "8-16kph";
        $spdkph[2] = "10-15kph";
        $spdkph[3] = "24-32kph";
        $spdkph[4] = "32-40kph";
        $spdkph[5] = "40-48kph";
        $spdkph[6] = "48-56kph";
        $spdkph[7] = "56-64kph";
        $spdkph[8] = "64-72kph";
        $spdkph[9] = "72-80kph";
        $spdkph[10] = "80+kph";

        $spdknt = array();
        $spdknt[0] = "less than 4kt";
        $spdknt[1] = "4-9kt";
        $spdknt[2] = "9-13kt";
        $spdknt[3] = "13-17kt";
        $spdknt[4] = "17-22kt";
        $spdknt[5] = "22-26kt";
        $spdknt[6] = "26-30kt";
        $spdknt[7] = "30-35kt";
        $spdknt[8] = "35-39kt";
        $spdknt[9] = "39-43kt";
        $spdknt[10] = "43+kt";

        foreach ($spdmph as $k => $spd) {
            if ($strpos = strpos($text,$spd))
                $text = ($units == 'kph') ? str_replace($spd,$spdkph[$k],$text) : str_replace($spd,$spdknt[$k],$text);
        }
        return $text;
    }

    public function feetToMeters($feet,$precision=1) {
        if (!is_numeric($feet))
            return false;

        return round($feet /  3.281,$precision);
    }

    public function convertHeight($feet,$newUnit,$precision=1)
    {
        if (!is_numeric($feet))
            return false;

        if ($newUnit == 'ft')
            return round($feet,$precision);
        else
            return $this->feetToMeters($feet);
    }

    public function convertWindSpeed($windSpd, $units, $precision=0)
    {
        if (!is_numeric($windSpd))
            return false;

        if ($units === 'kph')
            return round($windSpd * 1.60934, $precision);
        else if ($units === 'kt')
            return round ($windSpd * 0.868976, $precision);
        else
            return $windSpd;
    }

    public function convertTemp($fahrenheit,$newUnit,$precision=0)
    {
        if (!is_numeric($fahrenheit))
            return false;

        if ($newUnit == 'f')
            return round($fahrenheit,$precision);
        else
            return round(($fahrenheit - 32) / 1.8,$precision); //to Celsius
    }


    public function dirText($direction, $longText=false)
    {
        $dir[0] = 0;
        $dir[1] = 12;
        $dir[2] = 34;
        $dir[3] = 57;
        $dir[4] = 79;
        $dir[5] = 102;
        $dir[6] = 124;
        $dir[7] = 147;
        $dir[8] = 169;
        $dir[9] = 192;
        $dir[10] = 214;
        $dir[11] = 237;
        $dir[12] = 259;
        $dir[13] = 282;
        $dir[14] = 304;
        $dir[15] = 327;
        $dir[16] = 349;
        $dir[17] = 361;

        $dirtxt[0] = "N";
        $dirtxt[1] = "NNE";
        $dirtxt[2] = "NE";
        $dirtxt[3] = "ENE";
        $dirtxt[4] = "E";
        $dirtxt[5] = "ESE";
        $dirtxt[6] = "SE";
        $dirtxt[7] = "SSE";
        $dirtxt[8] = "S";
        $dirtxt[9] = "SSW";
        $dirtxt[10] = "SW";
        $dirtxt[11] = "WSW";
        $dirtxt[12] = "W";
        $dirtxt[13] = "WNW";
        $dirtxt[14] = "NW";
        $dirtxt[15] = "NNW";
        $dirtxt[16] = "N";

        $dirtxtLong[0] = "North";
        $dirtxtLong[1] = "North-North-East";
        $dirtxtLong[2] = "North-East";
        $dirtxtLong[3] = "East-North-East";
        $dirtxtLong[4] = "East";
        $dirtxtLong[5] = "East-South-East";
        $dirtxtLong[6] = "South-East";
        $dirtxtLong[7] = "South-South-East";
        $dirtxtLong[8] = "South";
        $dirtxtLong[9] = "South-South-West";
        $dirtxtLong[10] = "South-West";
        $dirtxtLong[11] = "West-South-West";
        $dirtxtLong[12] = "West";
        $dirtxtLong[13] = "West-North-West";
        $dirtxtLong[14] = "North-West";
        $dirtxtLong[15] = "North-North-West";
        $dirtxtLong[16] = "North";

        $directiontxt = '';

        for ($n = 0; $n <= 16; $n++) {
            if (($direction >= $dir[$n]) and ($direction < $dir[$n + 1])) {
                $directiontxt = ($longText) ? $dirtxtLong[$n] : $dirtxt[$n];
                break;
            }
        }
        return $directiontxt;
    }

    public function wxStationSkyToIcon($sky,$wind,$sriseTime,$ssetTime)
    {
        $now = time();

        $windy = ($wind > 18) ? true : false;
        $strongWind = ($wind > 26) ? true : false;

        //Get Weather Icon
        $day = ($now > $sriseTime and $now < $ssetTime) ? true : false;

        if (in_array(strtolower($sky),array('fair','clear','mostly clear','sunny')) or $sky == '') {
            if ($windy) {
                if ($strongWind)
                    $icon = 'strong-wind';
                else
                    $icon = ($day) ? 'day-wind' : 'windy';
            } else
                $icon = ($day) ? 'day-sunny' : 'night-clear';

        } else if (in_array(strtolower($sky), array('cloudy','considerable cloudiness')) or strpos(strtolower($sky), 'mostly cloudy') !== false or strpos(strtolower($sky),'overcast') !== false or strpos(strtolower($sky),'unknown precip') !== false ) {

            $icon = 'cloudy' . (($windy) ? '-windy' : '');

        } else if (in_array(strtolower($sky), array('partly cloudy','partly sunny','obscured','a few clouds'))) {
            $icon = ($day) ? 'day-cloudy' : 'night-alt-cloudy';
            $icon .= ($windy) ? '-windy' : '';

        } else if (strpos(strtolower($sky),'thunder') !== false)
            $icon = ($day) ? 'day-thunderstorm' : 'night-alt-thunderstorm';

        else if (strpos(strtolower($sky),'light rain') !== false or strpos(strtolower($sky),'drizzle') !== false)
            $icon = ($day) ? 'day-rain' : 'night-alt-rain';

        else if (strpos(strtolower($sky),'rain') !== false or strpos(strtolower($sky),'showers') !== false or strpos(strtolower($sky),'squalls') !== false)
            $icon = 'rain';

        else if (strpos(strtolower($sky),'snow') !== false)
            $icon = 'snow';

        else if (strpos(strtolower($sky),'ice') !== false or strpos(strtolower($sky),'hail') !== false)
            $icon = 'sleet';

        else if (strpos(strtolower($sky),'fog') !== false or strpos(strtolower($sky), 'mist') !== false)
            $icon = ($day) ? 'day-fog' : 'night-fog';

        else if (strpos(strtolower($sky), 'haze') !== false)
            $icon = 'day-haze';

        else if ($sky == 'smoke')
            $icon = 'smoke';

        else {
            if ($windy) {
                if ($strongWind)
                    $icon = 'strong-wind';
                else
                    $icon = ($day) ? 'day-wind' : 'windy';
            }
            else
                $icon = ($day) ? 'day-sunny' : 'night-clear';
        }

        return $icon;
    }
}
