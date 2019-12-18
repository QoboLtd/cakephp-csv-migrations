$(document).ready(function () {
    $('.view-map-container').each(function () {
        let id = $(this).find(".view-googlemap").attr("id");
        let data = $(this).find(".gps-string");
        let gps_value = $(this).data("gps");

        $(data).text(gps_value)
        drawMap(id, gps_value, true)
    })
})
