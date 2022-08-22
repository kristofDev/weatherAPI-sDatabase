<?php
// from: https://www.weatherapi.com/
//api key: eab9a0c9f1ca4d969da85224221902
// Stara Nova vas coordinates ------> lat: 46.579352, lon: 16.1152577
// usefull: https://www.weatherapi.com/api-explorer.aspx#forecast

include_once 'include\database.php'; 
include_once 'callAPI.php';

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error); //IF SOMETHING is wrong with connection it is shown here
}
$api_key= "eab9a0c9f1ca4d969da85224221902";
//here it is calling data just for 2 days:
    
    
$lokacije = [
    "1" => [
        "location_id" => "1",
        "lat" => "46.579352",
        "lon" => "16.1152577",
        "appid" => "eab9a0c9f1ca4d969da85224221902",
        "lokacija" => "Stara Nova vas",
        "lokacijaOkraj" => "Križevci pri Ljutomeru"
    ],
    "2" =>[
        "location_id" => "2",
        "lat" => "46.554650",
        "lon" => "15.645881",
        "appid" => "eab9a0c9f1ca4d969da85224221902",
        "lokacija" => "Maribor",
        "lokacijaOkraj" => "Maribor"
    ]
];

foreach($lokacije as $lokacija){

    $WeatherUrl = "http://api.weatherapi.com/v1/forecast.json?key=".$api_key."&q=".$lokacije['1']['lat'].",".$lokacije['1']['lon']."&days=3&aqi=no&alerts=no";
    $WeatherCall=callAPI("GET", $WeatherUrl, false); //why false?
    $WeatherAnswer=json_decode($WeatherCall, true); //what does this true do?

    echo "<pre>";
    print_r($WeatherAnswer);


    $current_weatherState = $WeatherAnswer['current']['condition']['text'];
    $current_temp = $WeatherAnswer['current']['temp_c'];
    $current_humidity = $WeatherAnswer['current']['humidity'];
    $current_pressure = $WeatherAnswer['current']['pressure_mb'];
    $current_clouds = $WeatherAnswer['current']['cloud'];
    $current_visibility = $WeatherAnswer['current']['vis_km'];
    $current_windspeed = $WeatherAnswer['current']['wind_kph'];
    $current_winddeg = $WeatherAnswer['current']['wind_degree'];

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
        '".$lokacija['location_id']."',
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
        'weatherapi'
    );";

    echo $sqlCurrent;
    if($conn->query($sqlCurrent)){
        echo "Current success";
    }
    else{
        echo "Current fail";
    }

    for($i=1; $i < 3; $i++){ // how do i access to data on hour (it doesn't have any numbers)
        for($j=0; $j < 24; $j++){
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
                '".$lokacija['location_id']."',
                CURRENT_TIMESTAMP(),
                'JA',
                '".$WeatherAnswer['forecast']['forecastday'][$i]['hour'][$j]['time']."',
                '".$WeatherAnswer['forecast']['forecastday'][$i]['hour'][$j]['condition']['text']."',
                '".$WeatherAnswer['forecast']['forecastday'][$i]['hour'][$j]['temp_c']."',
                '".$WeatherAnswer['forecast']['forecastday'][$i]['hour'][$j]['humidity']."',
                '".$WeatherAnswer['forecast']['forecastday'][$i]['hour'][$j]['pressure_mb']."',
                '".$WeatherAnswer['forecast']['forecastday'][$i]['hour'][$j]['cloud']."',
                '".$WeatherAnswer['forecast']['forecastday'][$i]['hour'][$j]['vis_km']."',
                '".$WeatherAnswer['forecast']['forecastday'][$i]['hour'][$j]['wind_kph']."',
                '".$WeatherAnswer['forecast']['forecastday'][$i]['hour'][$j]['wind_degree']."',
                'weatherapi'
            );";

            if($conn->query($sqlHourly)){
                echo "Hourly ".$i." success";
            }
            else{
                echo "Hourly ".$i." fail";
            }
            // echo $sqlHourly;
        }
    } 

    for($i=0; $i < 3; $i++){  // ta dostopa lahko le do treh dni
        $sqlDaily = "INSERT INTO daily(
            id_lokacija,
            current_cas,
            forecast_time,
            weatherstate,
            temperature_min,
            temperature_max,
            temperature_average,
            humidity,
            vir
        )
        VALUES (
            '".$lokacija['location_id']."',
            CURRENT_TIMESTAMP(),
            date_add(CURRENT_TIMESTAMP(),  interval $i day),
            '".$WeatherAnswer['forecast']['forecastday'][$i]['day']['condition']['text']."',
            '".$WeatherAnswer['forecast']['forecastday'][$i]['day']['mintemp_c']."',
            '".$WeatherAnswer['forecast']['forecastday'][$i]['day']['maxtemp_c']."',
            '".$WeatherAnswer['forecast']['forecastday'][$i]['day']['avgtemp_c']."',
            '".$WeatherAnswer['forecast']['forecastday'][$i]['day']['avghumidity']."',
            'weatherapi'
        );";
        // echo $sqlDaily;
        if($conn->query($sqlDaily)){
            echo "Daily ".$i."success";
        }
        else{
            echo "Daily ".$i." fail";
        }
    }
}

