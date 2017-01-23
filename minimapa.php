<?php
// ubicacion prueba

$geo_ubicacion[0] = "-33.4420004";
$geo_ubicacion[1] = "-70.6636011";

echo "
<!DOCTYPE html>
<html>
    <head>
        <title>Maps</title>
        <meta name='viewport' content='initial-scale=1.0, user-scalable=no'>
        <meta charset='utf-8'>
        <style>
            html, body {
                height: 100%;
                margin: 0;
                padding: 0;
            }
            #map {
                height: 100%;
            }
            .controls {
                margin-top: 10px;
                border: 1px solid transparent;
                border-radius: 2px 0 0 2px;
                box-sizing: border-box;
                -moz-box-sizing: border-box;
                height: 32px;
                outline: none;
                box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
            }

            #origin-input,
            #destination-input {
                background-color: #fff;
                font-family: Roboto;
                font-size: 15px;
                font-weight: 300;
                margin-left: 12px;
                padding: 0 11px 0 13px;
                text-overflow: ellipsis;
                width: 200px;
            }

            #origin-input:focus,
            #destination-input:focus {
                border-color: #4d90fe;
            }

            #mode-selector {
                color: #fff;
                background-color: #4d90fe;
                margin-left: 12px;
                padding: 5px 11px 0px 11px;
            }

            #mode-selector label {
                font-family: Roboto;
                font-size: 13px;
                font-weight: 300;
            }

            #go-input {
                margin-left: 12px;
            }
        </style>

        <script type='text/javascript' src='https://code.jquery.com/jquery-3.1.1.min.js'></script>

        <script type='text/javascript'>
            var geocoder;
            var map;
            var infowindow;
            var marker;
            var travel_mode;
            var directionsService;
            var directionsDisplay;
            var latitud;
            var longitud;
            var coordenadas = new Array();

            navigator.geolocation.getCurrentPosition(showPosition);

            function initMap() {
                    geocoder = new google.maps.Geocoder();
                    infowindow = new google.maps.InfoWindow();
                    travel_mode = google.maps.TravelMode.WALKING;
                    map = new google.maps.Map(document.getElementById('map'), {
                    //mapTypeControl: false,
                    center: {lat: $geo_ubicacion[0], lng: $geo_ubicacion[1]},
                    navigationControl: true,
                    streetViewControl: true,
                    mapTypeControl: true,
                    rotateControl: true,
                    scaleControl: true,
                    zoomControl: true,
                    scrollwheel: true,
                    zoom: 13
                });
                    marker = new google.maps.Marker({
                        position: {lat:$geo_ubicacion[0], lng:$geo_ubicacion[1]},
                        map: map
                    });
                    coordenadas['des_lat'] = $geo_ubicacion[0];
                    coordenadas['des_log'] = $geo_ubicacion[1];

                google.maps.event.addListener(map, 'click', function(){
                    closeInfoWindow();
                });
                    directionsService = new google.maps.DirectionsService;
                    directionsDisplay = new google.maps.DirectionsRenderer;
                directionsDisplay.setMap(map);

                var go_input = document.getElementById('go-input');
                var modes = document.getElementById('mode-selector');

                map.controls[google.maps.ControlPosition.TOP_LEFT].push(modes);
                map.controls[google.maps.ControlPosition.TOP_LEFT].push(go_input);

                // Establece un elemento para cada 'radio button' y asi cambiar el filtro de modo de viaje
                function setupClickListener(id, mode) {
                    var radioButton = document.getElementById(id);
                    radioButton.addEventListener('click', function() {
                        travel_mode = mode;
                    });
                }
                setupClickListener('changemode-walking', google.maps.TravelMode.WALKING);
                setupClickListener('changemode-transit', google.maps.TravelMode.TRANSIT);
                setupClickListener('changemode-driving', google.maps.TravelMode.DRIVING);

                function expandViewportToFitPlace(map, place) {
                    if (place.geometry.viewport) {
                        map.fitBounds(place.geometry.viewport);
                    } else {
                        map.setCenter(place.geometry.location);
                        map.setZoom(17);
                    }
                }

                function closeInfoWindow()
                {
                    infowindow.close();
                }
            }

            /*
                Funcion que crea la ruta entre dos puntos
            */
            function route(coordenadas, travel_mode,
                directionsService, directionsDisplay)
            {
                directionsService.route({
                    origin: new google.maps.LatLng(coordenadas['ori_lat'], coordenadas['ori_log']),
                    destination: new google.maps.LatLng(coordenadas['des_lat'], coordenadas['des_log']),
                    travelMode: travel_mode
                }, function(response, status) {
                    if (status === google.maps.DirectionsStatus.OK) {
                        directionsDisplay.setDirections(response);
                    } else {
                        window.alert('Directions request failed due to ' + status);
                    }
                });
            }

            /*
                Funcion que asigna las coordenadas de la posision en variables
            */
            function showPosition(position)
            {
                latitud = position.coords.latitude;
                longitud = position.coords.longitude;
                coordenadas['ori_lat'] = latitud;
                coordenadas['ori_log'] = longitud;
            }           

            /*
                Funcion que solicita las coordenadas de origen y enruta a destino
            */
            function show()
            {
                //var ubicacion = document.getElementById('ubicacion');
                navigator.geolocation.getCurrentPosition(showPosition);
                route(coordenadas, travel_mode, directionsService, directionsDisplay);
            }

            /*
                Funcion que obtiene la direccion a partir de las coordenadas
                Parametros de entrada latitud , longitud
                Retorno Void
            */
            function codeLatLng(latitud, longitud)
            {
                var latlng = new google.maps.LatLng(latitud, longitud);
                geocoder.geocode({'latLng': latlng}, function(results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                  if (results[0]) {
                    map.fitBounds(results[0].geometry.viewport);
                            marker.setMap(map);
                            marker.setPosition(latlng);
                    //$('#'+input).val(results[0].formatted_address);
                    infowindow.setContent(results[0].formatted_address);
                    infowindow.open(map, marker);
                    google.maps.event.addListener(marker, 'click', function(){
                        infowindow.setContent(results[0].formatted_address);
                        infowindow.open(map, marker);
                    });
                  } else {
                    alert('No results found');
                  }
                } else {
                  alert('Geocoder failed due to: ' + status);
                }
                });
            }
        </script>

        <script type='text/javascript' src='https://maps.googleapis.com/maps/api/js?key=AIzaSyD_AHXCSBqaEmh1CtsPcEMlLLeYkoLE4F8&libraries=places&callback=initMap'
    async defer></script>
    </head>
    <body>
        <div id='mode-selector' class='controls'>
            <input type='radio' name='type' id='changemode-walking' checked='checked'>
            <label for='changemode-walking'>Caminado</label>

            <input type='radio' name='type' id='changemode-transit'>
            <label for='changemode-transit'>Transporte Publico</label>

            <input type='radio' name='type' id='changemode-driving'>
            <label for='changemode-driving'>Manejando</label>
        </div>

        <input id='go-input' class='controls' type=image src='llegar.png' width='32' height='32' onclick='show()'>

        <div id='map' style='width:100%;height:400px'></div>
    </body>
</html>
";
