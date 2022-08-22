<?php 
include 'include\database.php';
include_once 'callAPI.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); //IF SOMETHING is wrong with connection it is shown here
}

$apiKey = '0274a1bdf37094d86753207bd23bcc8d'; // API key for openweathermap.org 
$lokacije = [
    "1" => [
        "location_id" => "1",
        "lat" => "46.579352",
        "lon" => "16.1152577",
        "appid" => "0274a1bdf37094d86753207bd23bcc8d",
        "lokacija" => "Stara Nova vas",
        "lokacijaOkraj" => "Križevci pri Ljutomeru"
    ],
    "2" =>[
        "location_id" => "2",
        "lat" => "46.554650",
        "lon" => "15.645881",
        "appid" => "0274a1bdf37094d86753207bd23bcc8d",
        "lokacija" => "Maribor",
        "lokacijaOkraj" => "Maribor"
    ]
];

foreach($lokacije as $lokacija){

    //Calling API:
    $WeatherUrl = "https://api.openweathermap.org/data/2.5/onecall?lat=".$lokacije['1']['lat']."&lon=".$lokacije['1']['lon']."&units=metric&exclude=alerts,minutely&appid=".$apiKey;
    $WeatherCall=callAPI("GET", $WeatherUrl, false); 
    $WeatherAnswer=json_decode($WeatherCall, true); 

    //za preverit:
    echo "<pre>";
    print_r($WeatherAnswer);

    $idSTv = $lokacije['1']['location_id']; // id of Stara Nova vas 
    // zaganjaj ob 11.00 da bo se ujemalo s casom forecastov!!

    $current_weatherState =  $WeatherAnswer['current']['weather']['0']['main'];
    $current_temp = $WeatherAnswer['current']['temp'];
    $current_humidity = $WeatherAnswer['current']['humidity'];
    $current_pressure = $WeatherAnswer['current']['pressure'];
    $current_clouds = $WeatherAnswer['current']['clouds'];
    $current_visibility = $WeatherAnswer['current']['visibility'];
    $current_windspeed = $WeatherAnswer['current']['wind_speed'];
    $current_winddeg = $WeatherAnswer['current']['wind_deg'];

    // //Input in database
    $sqlCurrent = "INSERT INTO podatki (
            id_lokacija,
            current_cas,
            forecast,
            prediction_time,
            weatherstate,
            temperature,
            humidity,
            pressure,
            clouds,
            visibility,
            windspeed,
            winddeg,
            vir
        )
        VALUES (
            '$idSTv',
            CURRENT_TIMESTAMP(),
            'NE',
            CURRENT_TIMESTAMP(), 
            '$current_weatherState',
            '$current_temp',
            '$current_humidity',
            '$current_pressure',
            '$current_clouds',
            '$current_visibility',
            '$current_windspeed',
            '$current_winddeg',
            'openweathermap'
        );";

    if($conn->query($sqlCurrent)){
        echo "Current success";
    }
    else{
        echo "Current fail";
    }

    for($i=0; $i < 48; $i++){ 
        $sqlHourly = "INSERT INTO podatki(
            id_lokacija,
            current_cas,
            forecast,
            prediction_time,
            weatherstate,
            temperature,
            humidity,
            pressure,
            clouds,
            visibility,
            windspeed,
            winddeg,
            vir
        )
        VALUES (
            '$idSTv',
            CURRENT_TIMESTAMP(),
            'JA',
            date_add(CURRENT_TIMESTAMP(),  interval $i hour), 
            '".$WeatherAnswer['hourly'][$i]['weather']['0']['main']."',
            '".$WeatherAnswer['hourly'][$i]['temp']."',
            '".$WeatherAnswer['hourly'][$i]['humidity']."',
            '".$WeatherAnswer['hourly'][$i]['pressure']."',
            '".$WeatherAnswer['hourly'][$i]['clouds']."',
            '".$WeatherAnswer['hourly'][$i]['visibility']."',
            '".$WeatherAnswer['hourly'][$i]['wind_speed']."',
            '".$WeatherAnswer['hourly'][$i]['wind_deg']."',
            'openweathermap'
        );";
        if($conn->query($sqlHourly)){
            echo "Hourly ".$i." success";
        }
        else{
            echo "Hourly ".$i." fail";
        }
        // echo $sqlHourly;
    } 


    for($i=0; $i < 8; $i++){ 
        $sqlDaily = "INSERT INTO daily(
            id_lokacija,
            current_cas,
            forecast_time,
            weatherstate,
            temperature_day,
            temperature_night,
            temperature_eve,
            temperature_morn,
            temperature_min,
            temperature_max,
            humidity,
            pressure,
            clouds,
            windspeed,
            winddeg,
            vir
        )
        VALUES (
            '".$idSTv."',
            CURRENT_TIMESTAMP(),
            date_add(CURRENT_TIMESTAMP(),  interval $i day),
            '".$WeatherAnswer['daily'][$i]['weather']['0']['main']."',
            '".$WeatherAnswer['daily'][$i]['temp']['day']."',
            '".$WeatherAnswer['daily'][$i]['temp']['night']."',
            '".$WeatherAnswer['daily'][$i]['temp']['eve']."',
            '".$WeatherAnswer['daily'][$i]['temp']['morn']."',
            '".$WeatherAnswer['daily'][$i]['temp']['min']."',
            '".$WeatherAnswer['daily'][$i]['temp']['max']."',
            '".$WeatherAnswer['daily'][$i]['humidity']."',
            '".$WeatherAnswer['daily'][$i]['pressure']."',
            '".$WeatherAnswer['daily'][$i]['clouds']."',
            '".$WeatherAnswer['daily'][$i]['wind_speed']."',
            '".$WeatherAnswer['daily'][$i]['wind_deg']."',
            'openweathermap'
        );";
        echo $sqlDaily;
        if($conn->query($sqlDaily)){
            echo "Daily ".$i."success";
        }
        else{
            echo "Daily ".$i." fail";
        }
        //works
    }
    echo $sqlCurrent;
}

?>
