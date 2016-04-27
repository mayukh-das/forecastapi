<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Credentials: true ");
header("Access-Control-Allow-Methods: OPTIONS, GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Depth, User-Agent, X-File-Size, 
    X-Requested-With, If-Modified-Since, X-File-Name, Cache-Control");

    $street=$_GET["street"];
    $city=$_GET["city"];
    $state=$_GET["state"];
    $degreeValue=$_GET["degreeValue"];

    $unitsValue       = ($degreeValue == "Celsius")?("si"):("us");
    $degreeUnit       = ($degreeValue == "Celsius")?("°C"):("°F");
    $windSpeedUnit    = ($degreeValue == "Celsius")?(" mps"):(" mph");
    $visibilityUnit   = ($degreeValue == "Celsius")?(" km"):(" mi");
    $pressureUnit     = ($degreeValue == "Celsius")?(" hPa"):(" mb");

    $FORECAST_API_KEY = "forecast_api_key";
    $GOOGLE_API_KEY = "google_api_key";

    $geoCodeAddress = $street.",".$city.",".$state;
    
    $urlGeoCode = "https://maps.googleapis.com/maps/api/geocode/xml?address=".urlencode($geoCodeAddress)."&key=".$GOOGLE_API_KEY;

    $resultGeoCode = file_get_contents($urlGeoCode);

    $xmlGeoCode = simplexml_load_string($resultGeoCode) or die("Error: Cannot create object");

    $latitude = $xmlGeoCode->result->geometry->location->lat;
    $longitude = $xmlGeoCode->result->geometry->location->lng;

    $addressForecastCode = $FORECAST_API_KEY."/".$latitude.",".$longitude."?units=".urlencode($unitsValue)."&exclude=flags";

    $urlForecastCode="https://api.forecast.io/forecast/".$addressForecastCode;

    $resultForecastCode = file_get_contents($urlForecastCode);

    $arrayForecastCode = json_decode($resultForecastCode,true);

    //echo "$resultForecastCode";
    $currentLatitude  = $arrayForecastCode['latitude']."";
    $currentLongitude = $arrayForecastCode['longitude']."";

    if(!isset($arrayForecastCode['timezone']) || empty($arrayForecastCode['timezone']))
    {
        date_default_timezone_set('America/Los_Angeles');
    }
    else
    {
        date_default_timezone_set($arrayForecastCode['timezone']);
    }

    /*
    
            OBTAIN DATA FOR RIGHT NOW
    
    */

    // ---------------------- OBTAIN VALUES for Others ----------------------
    $currentlySummary       = $arrayForecastCode['currently']['summary'];
    $currentlyTemperature   = intval(round($arrayForecastCode['currently']['temperature']));
    $currentlyIcon          = $arrayForecastCode['currently']['icon'];
    $currentlyIntensity     = $arrayForecastCode['currently']['precipIntensity'];
    $currentlyProbablity    = $arrayForecastCode['currently']['precipProbability'];
    $currentlyWindSpeed     = $arrayForecastCode['currently']['windSpeed'];
    $currentlyDewPoint      = $arrayForecastCode['currently']['dewPoint'];
    $currentlyHumidity      = $arrayForecastCode['currently']['humidity'];
    $currentlyVisibility    = $arrayForecastCode['currently']['visibility'];

    // ---------------------- OBTAIN SUNRISE and SUNSET Times ----------------------
    $sunriseTime    = $arrayForecastCode['daily']['data'][0]['sunriseTime'];
    $sunsetTime     = $arrayForecastCode['daily']['data'][0]['sunsetTime'];
    
    $currentlyTemperatureMax  = intval($arrayForecastCode['daily']['data'][0]['temperatureMax']);
    $currentlyTemperatureMin  = intval($arrayForecastCode['daily']['data'][0]['temperatureMin']);
    
    // Appending °F || °C to currentlyTemperature,currentlyDewPoint,currentlyTemperatureMax,currentlyTemperatureMin
    $currentlyTemperature    = $currentlyTemperature."";
    $currentlyDewPoint       = $currentlyDewPoint.$degreeUnit;
    $currentlyTemperatureMax = $currentlyTemperatureMax."º";
    $currentlyTemperatureMin = $currentlyTemperatureMin."º";

    // Converting $currentlyIntensityValue to "us" for easier calculation
    if(strcmp($unitsValue,"us") !== 0)
    {
        $currentlyIntensity = $currentlyIntensity/25.4;
    }

    if($currentlyIntensity >=0 && $currentlyIntensity < 0.002)
        $currentlyIntensityMessage = "None";

    else if($currentlyIntensity >=0.002 && $currentlyIntensity < 0.017)
        $currentlyIntensityMessage = "Very Light";

    else if($currentlyIntensity >=0.017 && $currentlyIntensity < 0.1)
        $currentlyIntensityMessage = "Light";

    else if($currentlyIntensity >=0.1 && $currentlyIntensity < 0.4)
        $currentlyIntensityMessage = "Moderate";

    else if($currentlyIntensity >= 0.4)
        $currentlyIntensityMessage = "Heavy";
    else
        $currentlyIntensityMessage = "Incorect value";

    // Change $currentlyProbablity to Percentage
    $currentlyProbablity = $currentlyProbablity*100;
    $currentlyProbablity = $currentlyProbablity."%";

    // Append mph/mps to WindSpeed
    $currentlyWindSpeed = $currentlyWindSpeed.$windSpeedUnit;

    // Change $currentlyHumidity to Percentage
    $currentlyHumidity = $currentlyHumidity*100;
    $currentlyHumidity = $currentlyHumidity."%";

    // Append mi/km to Visibilty
    $currentlyVisibility = $currentlyVisibility.$visibilityUnit;

    // Changing time to Correct Format
    $sunriseTime = date("h:i A",$sunriseTime);
    $sunsetTime  = date("h:i A",$sunsetTime);

    $currentlyImage = "pics/";

    function getImage($currentlyIcon,$currentlyImage)
    {
        if($currentlyIcon == "clear-day")
        {
            $currentlyImage .= "clear.png";
        }
        else if($currentlyIcon == "clear-night")
        {
            $currentlyImage .= "clear_night.png";
        }
        else if($currentlyIcon == "rain")
        {
            $currentlyImage .= "rain.png";
        }
        else if($currentlyIcon == "snow")
        {
            $currentlyImage .= "snow.png";
        }
        else if($currentlyIcon == "sleet")
        {
            $currentlyImage .= "sleet.png";
        }
        else if($currentlyIcon == "wind")
        {
            $currentlyImage .= "wind.png";
        }    
        else if($currentlyIcon == "fog")
        {
            $currentlyImage .= "fog.png";
        }
        else if($currentlyIcon == "cloudy")
        {
            $currentlyImage .= "cloudy.png";
        }
        else if($currentlyIcon == "partly-cloudy-day")
        {
            $currentlyImage .= "cloud_day.png";
        }
        else if($currentlyIcon == "partly-cloudy-night")
        {
            $currentlyImage .= "cloud_night.png";
        }
        else if(preg_match('/storm/',$currentlyIcon) == 1)
        {
            $currentlyImage .= "Storm.png";
        }
        else
        {
            $currentlyImage = "Incorrect";
        }
        return $currentlyImage;
    }

    // Set Appropriate Image
    $currentlyImage = getImage($currentlyIcon,$currentlyImage);

    $currentWeather = array(
        
        "weatherSummary"        => $currentlySummary,
        "currentTemperature"    => $currentlyTemperature,
        "currentIconAltText"    => $currentlyIcon,
        "currentImage"          => $currentlyImage,
        "lowestTemperature"     => $currentlyTemperatureMin,
        "highestTemperature"    => $currentlyTemperatureMax,
        "Precipitation"         => $currentlyIntensityMessage,
        "Chance of Rain"        => $currentlyProbablity,
        "Wind Speed"            => $currentlyWindSpeed,
        "Dew Point"             => $currentlyDewPoint,
        "Humidity"              => $currentlyHumidity,
        "Visibility"            => $currentlyVisibility,
        "Sunrise"               => $sunriseTime,
        "Sunset"                => $sunsetTime,
        "Latitude"              => $currentLatitude,
        "Longitude"             => $currentLongitude
    );

    /*
    
            OBTAIN-ED DATA FOR RIGHT NOW
    
    */
//--------------------------------------------------------------------
    /*
    
            OBTAIN DATA FOR NEXT 24 HOURS
    
    */

    $nextTwentyFourHours = array();
    for( $i=0 ; $i<48 ; $i++ )
    {
        
        $nextTwentyFourHours[$i] = array();
        
        $nextTime           = $arrayForecastCode["hourly"]["data"][$i]["time"];
        $nextTime           = date("h:i A",$nextTime);
        
        $nextSummary        = $arrayForecastCode["hourly"]["data"][$i]["summary"];

        $nextSummaryImage   = $arrayForecastCode["hourly"]["data"][$i]["icon"];
        $nextImageLocation  = "pics/";
        $nextImageLocation  = getImage($nextSummaryImage,$nextImageLocation);

        $nextCloudCover     = $arrayForecastCode["hourly"]["data"][$i]["cloudCover"]*100;
        $nextCloudCover    .=  "%";

        $nextTemp           = $arrayForecastCode["hourly"]["data"][$i]["temperature"];
        $nextTemp          .= "";

        $nextWind           = $arrayForecastCode["hourly"]["data"][$i]["windSpeed"];
        $nextWind          .= $windSpeedUnit;
        
        $nextHumidity       = $arrayForecastCode["hourly"]["data"][$i]["humidity"]*100;
        $nextHumidity      .= "%";
        
        $nextVisibility     = $arrayForecastCode["hourly"]["data"][$i]["visibility"];
        $nextVisibility    .= $visibilityUnit;
        
        $nextPressure       = $arrayForecastCode["hourly"]["data"][$i]["pressure"];
        $nextPressure      .= $pressureUnit;
        
        $nextTwentyFourHours[$i]["Time"]                = $nextTime;
        $nextTwentyFourHours[$i]["weatherSummary"]      = $nextSummary;
        $nextTwentyFourHours[$i]["nextSummaryImage"]    = $nextSummaryImage;
        $nextTwentyFourHours[$i]["Summary"]             = $nextImageLocation;
        $nextTwentyFourHours[$i]["Cloud Cover"]         = $nextCloudCover;
        $nextTwentyFourHours[$i]["Temp"]                = $nextTemp;
        $nextTwentyFourHours[$i]["Wind"]                = $nextWind;
        $nextTwentyFourHours[$i]["Humidity"]            = $nextHumidity;
        $nextTwentyFourHours[$i]["Visibility"]          = $nextVisibility;
        $nextTwentyFourHours[$i]["Pressure"]            = $nextPressure;
    }

    /*
    
            OBTAIN-ED DATA FOR NEXT 24 HOURS
    
    */
//--------------------------------------------------------------------

//--------------------------------------------------------------------

    /*
    
            OBTAIN FOR NEXT 7 DAYS
    
    */

    $nextSevenDays = array();

    for( $i=0 ; $i<7 ; $i++ )
    {
        $nextSevenDays[$i] = array();
        $time = $arrayForecastCode['daily']['data'][1+$i]['time'];

        $day     = date("l",$time);
        $month   = date("M",$time);
        $date    = date("d",$time);
        
        $sevenDaysSummaryImage  = $arrayForecastCode['daily']['data'][1+$i]['icon'];
        $sevenDaysSummary       = $arrayForecastCode['daily']['data'][1+$i]['summary'];
        $sevenDaysImageLocation = "pics/";
        
        $sevenDaysImageLocation = getImage($sevenDaysSummaryImage,$sevenDaysImageLocation);
        
        $minTemp = intval($arrayForecastCode['daily']['data'][1+$i]['temperatureMin']);
        $maxTemp = intval($arrayForecastCode['daily']['data'][1+$i]['temperatureMax']);
        
        $sevenDaysSunriseTime = $arrayForecastCode['daily']['data'][1+$i]['sunriseTime'];
        $sevenDaysSunsetTime  = $arrayForecastCode['daily']['data'][1+$i]['sunsetTime'];
        $sevenDaysSunriseTime = date("h:i A",$sevenDaysSunriseTime);
        $sevenDaysSunsetTime  = date("h:i A",$sevenDaysSunsetTime);
        
        $sevenDaysHumidity   = $arrayForecastCode['daily']['data'][1+$i]['humidity']*100;
        $sevenDaysHumidity  .= "%";
        
        $sevenDaysWindSpeed  = $arrayForecastCode['daily']['data'][1+$i]['windSpeed'];
        $sevenDaysWindSpeed .= $windSpeedUnit;

        $sevenDaysVisibility = "";//  = $arrayForecastCode['daily']['data'][1+$i]['visibility'];

        if(isset($arrayForecastCode['daily']['data'][1+$i]['visibility']))
        {
            $sevenDaysVisibility = $arrayForecastCode['daily']['data'][1+$i]['visibility'] . $visibilityUnit;
        }
        else
        {
            $sevenDaysVisibility  = "N/A";
        }
        
        
        $sevenDaysPressure   = $arrayForecastCode['daily']['data'][1+$i]['pressure'];
        $sevenDaysPressure  .= $pressureUnit;
        
        $nextSevenDays[$i]["day"]       = $day;
        $nextSevenDays[$i]["month"]     = $month;
        $nextSevenDays[$i]["date"]      = $date;
        
        $nextSevenDays[$i]["sevenDaysSummaryImage"]  = $sevenDaysSummaryImage;
        $nextSevenDays[$i]["sevenDaysSummary"]       = $sevenDaysSummary;
        $nextSevenDays[$i]["sevenDaysImageLocation"] = $sevenDaysImageLocation;
        
        $nextSevenDays[$i]["minTemp"]   = $minTemp."º";
        $nextSevenDays[$i]["maxTemp"]   = $maxTemp."º";
        
        $nextSevenDays[$i]["sunriseTime"]   = $sevenDaysSunriseTime;
        $nextSevenDays[$i]["sunsetTime"]    = $sevenDaysSunsetTime;
        $nextSevenDays[$i]["humidity"]      = $sevenDaysHumidity;
        $nextSevenDays[$i]["windSpeed"]     = $sevenDaysWindSpeed;
        $nextSevenDays[$i]["visibility"]    = $sevenDaysVisibility;
        $nextSevenDays[$i]["pressure"]      = $sevenDaysPressure;
        
    }

    /*
    
            OBTAIN-ED FOR NEXT 7 DAYS
    
    */
//--------------------------------------------------------------------

    $result = array(
        
        "currentWeather"      => $currentWeather,
        "nextTwentyFourHours" => $nextTwentyFourHours,
        "nextSevenDays"       => $nextSevenDays
    
    );

    echo json_encode($result);

/*    echo "<table>";
    echo "<tr><td rowspan=\"15\" id=\"extend\"></td><td></td><td></td>";
    echo "<tr><th><p style=\"font-size:30px\">$currentlySummary</p></th></th></tr>";
    echo "<tr><th><p style=\"font-size:30px\">$currentlyTemperature</p></th></tr>";
    echo "<tr><th><img src=\"$currentlyImage\" alt=\"$currentlyIcon\" title=\"$currentlySummary\" /></th</tr>";
    echo "<tr><td>Precipitation:</td><td>$currentlyIntensityMessage</td></tr>";
    echo "<tr><td>Chance of Rain:</td><td>$currentlyProbablity</td></tr>";
    echo "<tr><td>Wind Speed:</td><td>$currentlyWindSpeed</td></tr>";
    echo "<tr><td>Dew Point:</td><td>$currentlyDewPoint</td></tr>";
    echo "<tr><td>Humidity:</td><td>$currentlyHumidity</td></tr>";
    echo "<tr><td>Visibility:</td><td>$currentlyVisibility</td></tr>";
    echo "<tr><td>Sunrise:</td><td>$sunriseTime</td></tr>";
    echo "<tr><td>Sunset:</td><td>$sunsetTime</td></tr>";
    echo "<tr></td><td></td><td></td><td rowspan=\"15\" id=\"extend\">";
    echo"</table>";*/
    //------------------------------------------------------------------------------
?>