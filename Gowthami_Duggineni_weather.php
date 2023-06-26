<!--  Server side code in using post call for all respective fetch at single place-->
<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST["pointData"]) && isset($_POST["zoneData"])) {

        // point data is the inital url data with coordinates 
        $pointData = $_POST["pointData"];

        // zone data contains all zones that do exist for this location 
        $zoneData = $_POST["zoneData"];

        // using the json data obtained from forecast and the initial coordinates
        $pointData = json_decode($pointData, true);
        $zoneData = json_decode($zoneData, true);

        // dynamically capturing the gridID, gridX, gridY for manipulation
        $id_grid = $pointData["properties"]["gridId"];
        $x_grid = $pointData["properties"]["gridX"];
        $y_grid = $pointData["properties"]["gridY"];

        // Generate HTML content for UI dashboard page
        $output = "<h2>Current Weather Data</h2>";
        $output .= "<p><strong>City:</strong> " . $pointData['properties']['relativeLocation']['properties']['city'] . "</p>";
        $output .= "<p><strong>State:</strong> " . $pointData['properties']['relativeLocation']['properties']['state'] . "</p>";
        $output .= "<p><strong>Time Zone:</strong> " . $pointData['properties']['timeZone'] . "</p>";
        $output .= "<p><strong>Radar Station:</strong> " . $pointData['properties']['radarStation'] . "</p>";
        $output .= "<p><strong>Choose a Specific Zone from below : </strong></p>";

        $output .= "<p><strong>Forecast Zone: </strong></p>";
        // Dropdown HTML
        $output .= '<select id="zoneDropdown" style="width: 200px; padding: 8px; background-color: #f2f2f2; border: 1px solid #ccc;" onchange="onZoneSelect(this.value, &quot;forecast&quot;)">';
        $output .= '<option value="">Select a zone</option>';
        foreach ($zoneData['features'] as $feature) {
            $zoneId = $feature['properties']['id'];
            $output .= '<option value="' . $zoneId . '">' . $zoneId . '</option>';
        }
        $output .= '</select>';

        $output .= "<p><strong>Fire Zone: </strong></p>";

        // Dropdown HTML
        $output .= '<select id="fireDropdown" style="width: 200px; padding: 8px; background-color: #f2f2f2; border: 1px solid #ccc;" onchange="onZoneSelect(this.value, &quot;fire&quot;)">';

        // Add default option
        $output .= '<option value="">Select a zone</option>';
        foreach ($zoneData['features'] as $feature) {
            $zoneId = $feature['properties']['id'];
            $output .= '<option value="' . $zoneId . '">' . $zoneId . '</option>';
        }
        $output .= '</select>';

        // button clicks for forecast and hourly forecast data
        $output .= '<div style="padding-top: 3%">';
        $output .= '<div class="forecast-container" id="forecastContainer1"> <button id="forecast1" onClick="getForecastData(\'' . $id_grid . '\', \'' . $x_grid . '\', \'' . $y_grid . '\', \'' . '' . '\')" style="background-color: #4CAF50; cursor: pointer; color: white; border: 1px solid #ccc; border-radius: 5px; padding: 12px;"> Forecast </button> </div>';
        $output .= '<div class="forecast-container" id="forecastContainer2"> <button id="forecast2" onClick="getForecastData(\'' . $id_grid . '\', \'' . $x_grid . '\', \'' . $y_grid . '\', \'' . 'hourly' . '\')" style="background-color: #4CAF50; cursor: pointer; color: white; border: 1px solid #ccc; border-radius: 5px; padding: 12px;"> Forecast Hourly </button> </div>';

        $output .= '</div>';

        // Display the output
        echo $output;
        exit;
    }
}
?>


<!DOCTYPE html>
<html>

<head>
    <title>Example Page</title>
    <style>
        body {
            margin: 20px;
            background-image: url('https://wallpapercave.com/wp/7rhrsIf.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: space-around;
        }

        /* css for dashboard */
        #output {
            background-color: rgba(255, 255, 255, 0.8);
            /*padding: 8%;*/
            padding-top: 5%;
            padding-bottom: 5%;
            border-radius: 5px;
            font-family: Arial, sans-serif;
            font-size: 16px;
            color: #333;
            text-align: center;
            margin-top: 8%;
            width: 50%;
            position: relative;
        }


        #cardContainer {
            background-color: rgba(255, 255, 255, 0.8);
            padding: 8%;
            padding-top: 5%;
            padding-bottom: 5%;
            border-radius: 5px;
            font-family: Arial, sans-serif;
            font-size: 16px;
            color: #333;
            text-align: center;
            margin-top: 20px;
            display: none;
            margin: 5%;
        }

        .card {
            padding: 20px;
            border-radius: 5px;
            text-align: center;
            margin: 10px;
        }

        /* css for dispalying map */

        #map {
            height: 400px;
            width: 50%;
            margin: 5%;
        }

        #data,
        #data1 {
            width: 100%;
            display: flex;
            justify-content: space-around;
            overflow: hidden;
            padding-top: 5%;
            text-align: -webkit-center;
        }

        /* to get forecast data on left sode */
        .forecast-card {
            background-color: rgba(255, 255, 255, 0.8);
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
            margin-bottom: 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            display: flex;
            width: 25%;
            padding: 3%;
            line-break: auto;
        }

        .forecast-icon {
            width: 45%;
            height: 45%;
            padding-bottom: 5%;
        }

        .forecast-container {
            display: inline-block;
            margin-right: 10px;
        }

        .forecast-name {
            font-size: 20px;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 10px;
        }

        .forecast-short {
            font-size: 18px;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        .forecast-details {
            font-size: 16px;
            margin-top: 5px;
            margin-bottom: 5px;
        }

        /* from here it is the css to get the station data */

        #stations {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            /* padding: 10px; */
            padding: 9%;
        }

        .station-card {
            width: 21%;
            height: inherit;
            border: 1px solid #ccc;
            background-color: rgba(255, 255, 255, 0.8);
            padding: 10px;
            box-sizing: border-box;
            margin-right: 3%;
            margin-bottom: 3%;
        }

        /* css for navigation bar */

        nav {
            position: absolute;
            top: 0;
            right: 0;
            padding: 10px;
        }

        nav button {
            background-color: #4CAF50;
            color: white;
            border: none;
            padding: 10px 20px;
            margin: 0;
            cursor: pointer;
            width: 150px;
        }

        nav button:hover {
            background-color: #45a049;
            animation: buttonHoverAnimation 0.3s;
        }

        /* animation for navbar item hovering */

        @keyframes buttonHoverAnimation {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }
    </style>

    <!-- Leaflet for map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
</head>

<body>

    <!--- HTML code for navbar -->
    <nav>
        <button onclick="location.reload()">Home</button>
        <button onclick="showContent('about')">About</button>
        <button onclick="getForecastData('CTP','95','81','')">Forecast</button>
        <button onclick="getForecastData('CTP','95','81','hourly')">Forecast Hourly</button>
        <button onclick="getStationData()">Stations</button>
    </nav>

    <!--- About -->
    <div id="about" style="display: none; text-align: center;">
        <div style="width: 81%; padding: 20px; background-color: rgba(255, 255, 255, 0.8); border-radius: 10px; box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2); margin: 8%">
            <div style="margin-bottom: 20px;">
                <h1>Weather Application</h1>
            </div>

            <div id="about-content" class="hidden" style="text-align: left;">
                <h2 style="margin-bottom: 10px;">About Our Application</h2>
                <p>
                    Our weather application provides accurate and up-to-date weather information. You can access the current weather data for specific cities and zones. The application offers interactive features, allowing you to explore forecast data and hourly forecasts for each weekday.
                </p>

                <h2 style="margin-bottom: 10px;">Current Weather Data</h2>
                <p>
                    Current coordinates, city, and timezone along with the radar station are displayed here.
                </p>

                <h2 style="margin-bottom: 10px;">Specific Zone Selection</h2>
                <p>
                    Forecast Zone:
                    <br>
                    - Dropdown menu to select a specific zone.
                    <br>
                    - The coordinates, city, and timezone of the selected zone are displayed.
                    <br>
                    - Radar station information is also shown.
                </p>

                <p>
                    Additionally, there is a map displaying all the respective coordinates of the zones.
                </p>

                <p>
                    Navigation options are available to access hourly forecast and forecast data and station data.
                </p>
            </div>
        </div>
    </div>


    <!--- Dashboard -->
    <div id="output"></div>

    <!--- forecast data display -->
    <div id="data1"></div>

    <!--- dropdown event handler for getting fire / forecast zone -->
    <div id="data">
        <div id="cardContainer"></div>

        <div id="map"></div>
    </div>

    <!--- Getting all stations data -->
    <div id="stations"></div>
    <div id="error_data">
        <div class="card">
            <p id="errorr" style="font-size: 20px;">Data is not available</p>
        </div>
    </div>

    <script>
        var map;

        var last_error = document.getElementById("error_data");
        last_error.style.display = "none";
        // about button event 
        function showContent(id) {

            var about_element = document.getElementById('about');
            about_element.style.display = "block";

            var data_section = document.getElementById("data1");
            data_section.style.display = 'none';

            var output_section = document.getElementById("output");
            output_section.style.display = 'none';

            var data_section = document.getElementById("data");
            data_section.style.display = 'none';

            var data_section = document.getElementById("stations");
            data_section.style.display = 'none';
        }


        // Request to fetch the weather data
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "https://api.weather.gov/points/41.25,-77.01", true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                var weatherData = xhr.responseText;

                // Sending the weather data to the PHP code for processing
                var phpXhr = new XMLHttpRequest();
                phpXhr.open("POST", "", true);
                phpXhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                phpXhr.onreadystatechange = function() {

                    // Handling the response from the PHP code
                    if (phpXhr.readyState === XMLHttpRequest.DONE && phpXhr.status === 200) {
                        document.getElementById("output").innerHTML = phpXhr.responseText;
                    }
                };
                var params = "weatherData=" + encodeURIComponent(weatherData);
                phpXhr.send(params);
            }
        };
        xhr.send();


        var about_element = document.getElementById('about');
        about_element.style.display = "none";

        var data_section = document.getElementById("data1");
        data_section.style.display = 'none';

        var data_section = document.getElementById("data");
        data_section.style.display = 'none';

        // to get forecast data of specific coordinates
        function fetchForecastData(id, val) {

            var data_section = document.getElementById("data");
            data_section.style.display = 'flex';

            var data_section = document.getElementById("data1");
            data_section.style.display = 'none';

            var output_section = document.getElementById("output");
            output_section.style.display = 'none';


            // used same function for both forecast and fire data calls
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "https://api.weather.gov/zones/" + val + "/" + id, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE && xhr.status === 200) {
                    // Handling the response from the API
                    var forecastData = JSON.parse(xhr.responseText);


                    // using respective cityname, timezone, radar station from the URL
                    var cityName = forecastData.properties.name;
                    var time = forecastData.properties.timeZone;
                    var radar = forecastData.properties.radarStation;


                    // Display and styling of a card
                    var card = document.createElement("div");
                    card.className = "card";
                    card.innerHTML = "<h3 style='font-size: 18px; font-weight: bold;'>City: " + cityName + "</h3>" +
                        "<p style='font-size: 16px; '>Time Zone: " + time + "</p>" +
                        "<p style='font-size: 16px;'>Radar: " + radar + "</p>" +
                        "<p id='error' style='font-size: 20px;'></p>";

                    // Condition to verify if map can be displayed
                    if (!radar) {
                        var cardContain = document.getElementById("cardContainer");
                        var mapContain = document.getElementById("map");
                        mapContain.style.display = "none";
                    }


                    var cardContainer = document.getElementById("cardContainer");
                    cardContainer.innerHTML = "";
                    cardContainer.appendChild(card);
                    cardContainer.style.display = "flex";

                    // Extracting the coordinates from the forecast data and creating the map
                    var coordinates = forecastData.geometry.coordinates;
                    createMap(coordinates, radar);

                } else {
                    var cardContainer = document.getElementById("cardContainer");
                    if (cardContainer.innerHTML == "") {}
                }
            };

            xhr.send();
        }

        // map creation function for each chosen data
        function createMap(coordinates, radar) {

            // Clear the previous map if it exists
            if (radar !== null) {
                if (map) {
                    map.remove();
                }


                var markers = [];

                for (var i = 0; i < coordinates.length; i++) {
                    var longitude = coordinates[i][0][0][0];
                    var latitude = coordinates[i][0][0][1];

                    // Check if latitude and longitude are defined
                    if (latitude !== undefined && longitude !== undefined) {
                        // Creating a marker at the coordinates and add it to the array
                        var marker = L.marker([latitude, longitude]);
                        markers.push(marker);
                    }
                }

                // Create a map centered on the first set of coordinates
                var initialLatitude = coordinates[0][0][0][1];
                var initialLongitude = coordinates[0][0][0][0];

                // Check if initialLatitude and initialLongitude are defined
                if (initialLatitude !== undefined && initialLongitude !== undefined) {
                    map = L.map("map").setView([initialLatitude, initialLongitude], 10);

                    // Add a tile layer to the map
                    L.tileLayer("https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png", {
                        attribution: "Map data &copy; <a href='https://www.openstreetmap.org/'>OpenStreetMap</a> contributors",
                        maxZoom: 18,
                    }).addTo(map);

                    // Add all markers from the array to the map
                    for (var j = 0; j < markers.length; j++) {
                        markers[j].addTo(map);
                    }
                } else {
                    map_ele = document.getElementById("error");
                    map_ele.innerHTML = "Coordinates are not defined for this instance"
                    map_ele = document.getElementById("map");
                    map_ele.style.display = "none";
                }

            }
        }

        // Function to handle zone selection from the dropdown
        function onZoneSelect(zoneId, val) {
            fetchForecastData(zoneId, val);
        }

        function fetchStationData(callback) {
            var xhr3 = new XMLHttpRequest();
            xhr3.open("GET", "https://api.weather.gov/gridpoints/CTP/95,81/stations", true);
            xhr3.onreadystatechange = function() {
                if (xhr3.readyState === XMLHttpRequest.DONE && xhr3.status === 200) {
                    var stationData = JSON.parse(xhr3.responseText);
                    // Call the callback function with the station data
                    callback(stationData);
                }
            };
            xhr3.send();
        }


        // modiyfing styles
        function getStationData() {

            var data_section = document.getElementById("data1");
            data_section.style.display = 'none';

            var output_section = document.getElementById("output");
            output_section.style.display = 'none';

            var data_section = document.getElementById("data");
            data_section.style.display = 'none';

            var station_section = document.getElementById("stations");
            station_section.style.display = 'flex';

            var about_section = document.getElementById("about");
            about_section.style.display = 'none';

            // getting specific station data on click
            fetchStationData(function(stationData) {
                var stationsDiv = document.getElementById("stations");
                // Clear previous data
                stationsDiv.innerHTML = "";

                stationData.features.forEach(function(station) {
                    var stationCard = createStationCard(station);
                    stationsDiv.appendChild(stationCard);
                });
            });
        }

        // making card dispaly for station data
        function createStationCard(station) {
            var stationCard = document.createElement("div");
            stationCard.classList.add("station-card");

            var stationName = document.createElement("h3");
            stationName.textContent = station.properties.name;
            stationCard.appendChild(stationName);

            var mapDiv = document.createElement("div");
            mapDiv.style.width = "100%";
            mapDiv.style.height = "150px";
            stationCard.appendChild(mapDiv);

            // adding map to the card
            var map = L.map(mapDiv).setView(station.geometry.coordinates.reverse(), 10);
            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/">OpenStreetMap</a> contributors'
            }).addTo(map);
            L.marker(station.geometry.coordinates).addTo(map);

            return stationCard;
        }



        // dispalying the obtained map with styles
        function showStationMap(station) {
            var mapDiv = document.createElement("div");
            mapDiv.style.width = "300px";
            mapDiv.style.height = "300px";
            mapDiv.style.border = "1px solid #ccc";
            mapDiv.textContent = "Map with coordinates: " + station.geometry.coordinates;

            var stationCard = document.getElementById(station.id);
            stationCard.appendChild(mapDiv);

            mapDiv.addEventListener("click", function(event) {
                event.stopPropagation();
                stationCard.removeChild(mapDiv);
            });
        }


        // again calling that code to get respective forcase code 
        function getForecastData(id, x, y, val) {

            var data_section = document.getElementById("data");
            data_section.style.display = 'none';

            var output_section = document.getElementById("output");
            output_section.style.display = 'none';

            var data1_section = document.getElementById("data1");
            data1_section.style.display = 'block';

            var data1_section = document.getElementById("about");
            data1_section.style.display = 'none';



            // dispalying all the periods (days) weather records in a sliding map
            var xhr = new XMLHttpRequest();
            var url = "https://api.weather.gov/gridpoints/" + id + "/" + x + "," + y + "/forecast/" + val;
            xhr.open("GET", url);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    if (xhr.status === 200) {

                        // To Handle the response from the API
                        var totalData = JSON.parse(xhr.responseText);

                        var periods = totalData.properties.periods;
                        var forecastContainer = document.getElementById("data1");
                        forecastContainer.innerHTML = "";

                        // Iterate over forecast periods and create forecast cards
                        for (var i = 0; i < periods.length; i++) {
                            var period = periods[i];
                            var card = createForecastCard(period);
                            forecastContainer.appendChild(card);

                            if (i > 0) {
                                card.style.display = "none";
                            }
                        }


                        // Animate forecast cards
                        var cards = forecastContainer.getElementsByClassName("forecast-card");
                        var currentCardIndex = 0;

                        function showNextCard() {
                            Array.from(cards).forEach(function(card) {
                                card.style.display = "none";
                            });
                            cards[currentCardIndex].style.display = "none";

                            currentCardIndex = (currentCardIndex + 1) % cards.length;

                            cards[currentCardIndex].style.display = "flex";
                            cards[currentCardIndex].classList.remove("slide-animation");
                        }

                        Array.from(cards).forEach(function(card) {
                            card.style.display = "none";
                        });

                        // Show the first card
                        cards[currentCardIndex].style.display = "flex";

                        // Start the animation
                        setInterval(function() {
                            showNextCard();
                        }, 2000);


                    }
                }
            }

            // Send the request
            xhr.send();
        }

        // Function to create a forecast card
        function createForecastCard(period) {
            // Create forecast card container
            var card = document.createElement("div");
            card.className = "forecast-card";

            var periodName = document.createElement("p");
            periodName.textContent = period.name;
            periodName.className = "forecast-name";
            card.appendChild(periodName);

            var icon = document.createElement("img");
            icon.src = period.icon;
            icon.className = "forecast-icon";
            card.appendChild(icon);

            var shortForecast = document.createElement("p");
            shortForecast.textContent = period.shortForecast;
            shortForecast.className = "forecast-short";
            card.appendChild(shortForecast);

            var details = document.createElement("p");
            details.textContent =
                "Temperature: " +
                period.temperature +
                " " +
                period.temperatureUnit +
                ", Humidity: " +
                period.relativeHumidity.value +
                " " +
                period.relativeHumidity.unitCode +
                ", Wind Speed: " +
                period.windSpeed +
                ", Wind Direction: " +
                period.windDirection;
            details.className = "forecast-details";
            card.appendChild(details);

            return card;
        }


        // getting chosen zone's data 
        function fetchStationData(callback) {
            var xhr3 = new XMLHttpRequest();
            xhr3.open("GET", "https://api.weather.gov/gridpoints/CTP/95,81/stations", true);
            xhr3.onreadystatechange = function() {
                if (xhr3.readyState === XMLHttpRequest.DONE && xhr3.status === 200) {
                    var stationData = JSON.parse(xhr3.responseText);
                    callback(stationData); // Call the callback function with the station data
                }
            };
            xhr3.send();
        }

        // Function to make a GET request and handle the response
        function fetchData() {
            var xhr1 = new XMLHttpRequest();
            xhr1.open("GET", "https://api.weather.gov/points/41.25,-77.01", true);
            xhr1.onreadystatechange = function() {
                if (xhr1.readyState === XMLHttpRequest.DONE && xhr1.status === 200) {
                    // Handle the response from the first API call
                    var pointData = JSON.parse(xhr1.responseText);

                    fetchStationData(function(stationData) {
                        fetch('', {
                                method: 'POST',
                                body: JSON.stringify({
                                    stationData: stationData
                                }),
                                headers: {
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(response => response.text())
                            .then(result => {

                            })
                            .catch(error => {

                            });
                    });

                    // Make a second GET request using the data from the first API call
                    var xhr2 = new XMLHttpRequest();
                    xhr2.open("GET", "https://api.weather.gov/zones/forecast", true);
                    xhr2.onreadystatechange = function() {
                        if (xhr2.readyState === XMLHttpRequest.DONE && xhr2.status === 200) {

                            // Handle the response from the second API call
                            var zoneData = JSON.parse(xhr2.responseText);

                            // Send the data to the PHP code for processing
                            var phpXhr = new XMLHttpRequest();
                            phpXhr.open("POST", "", true); // Empty URL to send the request to the same PHP file
                            phpXhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
                            phpXhr.onreadystatechange = function() {
                                if (phpXhr.readyState === XMLHttpRequest.DONE && phpXhr.status === 200) {


                                    // Appending the parameters from here to the above php code
                                    document.getElementById("output").innerHTML = phpXhr.responseText;
                                }
                            };
                            var params = "pointData=" + encodeURIComponent(JSON.stringify(pointData)) +
                                "&zoneData=" + encodeURIComponent(JSON.stringify(zoneData));
                            phpXhr.send(params);
                        }
                    };
                    xhr2.send();
                }
            };
            xhr1.send();
        }

        // Inital fetchData function
        fetchData();
    </script>
</body>

</html>