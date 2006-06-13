<?php

define("kDefaultWeather",1);

$gWeatherUrl = "http://weather.noaa.gov/pub/data/observations/metar/decoded/EDDM.TXT";

$gWeatherType = array(
	 1 => "Sonne",
	 2 => "Wolken",
	 3 => "Sturm",
	 4 => "Regnen",
	 5 => "Nebel",
	 6 => "Gewitter",
	 7 => "bedeckt",
	 8 => "Mond",
	 9 => "Mond",
	10 => "Vollmond",
	11 => "Mond",
	12 => "Mond"
);

$gWeatherGfx = array(
	 1 => "wetter/sonne.png",
	 2 => "wetter/wolken.png",
	 3 => "wetter/sturm.png",
	 4 => "wetter/regen.gif",
	 5 => "wetter/nebel.png",
	 6 => "wetter/blitz.gif",
	 7 => "wetter/sonnewolken.png",
	 8 => "wetter/mondhalblinks.png",
	 9 => "wetter/mondmittelinks.png",
	10 => "wetter/vollmond.png",
	11 => "wetter/mondmitterechts.png",
	12 => "wetter/mondhalbrechts.png"
);

/*
$gWeatherCodeType = array(
	"light rain" => 4,
	"patches of fog" => 5,
	"fog" => 5,
	"mist" => 5,
	"patches of fog; mist" => 5,
	"light drizzle; fog" => 4,
	"light drizzle; mist" => 4,
	"haze" => 4,
	"shallow fog; mist" => 4,
	"shallow fog" => 4
);
*/


if(empty($gGlobal["weather"]))SetGlobal("weather",GetWeather($gWeatherUrl));
$gWeather = $gGlobal["weather"];

/*
//random weather
$t = array_keys($gWeatherType);
shuffle($t);
$gWeather = array_pop($t);
unset($t);
*/

$gTimeH = date("H");
$gTimeGfx = "zeit/t".floor($gTimeH / 2).".png";
$gTimeStr = date("H:i");


//gets a weather from weather.noaa.gov
function GetWeather($url){
	global $gWeatherCodeType,$gTimeH;
	$value = implode("",file($url));
	//echo "+++++++++++++++++++++++++<br>";
	//echo "<pre>$value</pre>";
	//echo "+++++++++++++++++++++++++<br>";
	
	if(!empty($value)){
		if(strpos($value,"fog") === false); else return 5;
		if(strpos($value,"mist") === false); else return 5;
		if(strpos($value,"cloud") === false); else return 2;
		if(strpos($value,"rain") === false); else return 4;
				
		$fp = fopen(BASEPATH."weathercode.txt","a");
		if($fp){
			fwrite($fp,"$value\n\n");
			fclose($fp);
		}
		
		$h = floor($gTimeH / 2);
		if($h < 5 || $h > 20){
			return 8+(date("j",time()-60*60*12)%5);
		} else return kDefaultWeather;
	} else return kDefaultWeather;
}

/*
$gWeather = GetWeather($gWeatherUrl);
echo "[code=$gWeather]";
*/

?>
