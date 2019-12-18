function drawMap(id, coordinates, view = false)
{
    let latLng = coordinates.split(',')
    let lat = parseFloat(latLng[0])
    let lng = parseFloat(latLng[1])

    var mapOptions = {
        zoom: 14,
        mapTypeId: google.maps.MapTypeId.ROADMAP,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false,
    }

    mapOptions.center = new google.maps.LatLng(lat, lng);

    var map = new google.maps.Map(document.getElementById(id), mapOptions);
    var myLatlng = { lat: lat, lng: lng };
    var marker = new google.maps.Marker({
        position: myLatlng,
        map: map
    });

    if (!view) {
        marker.setDraggable(true);
        $("#" + id).parent().prev().find(".modal_gps_value").text(coordinates);

        function updateGps(e)
        {
            $("#" + id).parent().prev().find(".modal_gps_value").text(e.latLng.lat() + ',' + e.latLng.lng());
            marker.setPosition(e.latLng);
        }

        map.addListener('click', updateGps);
        marker.addListener('drag', updateGps);
        marker.addListener('dragend', updateGps);
    }
}
