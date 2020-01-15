$(document).ready(function () {
    $('.view-map-container').each(function () {

        let id = $(this).find(".view-googlemap").attr("id");
        $(this).find(".view-googlemap").css({"height" : "300px"})
        let data = $(this).find(".gps-string");
        let gps_value = $(this).data("gps");

        $(data).html('<i class="fa fa-map-marker"></i> ' + gps_value);
        drawMap(id, gps_value, true)
    })
})
