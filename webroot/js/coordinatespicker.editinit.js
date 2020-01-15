$(document).ready(function () {
    $('.coordinates_modal').each(function (event) {
        let gps_input = $(this).prev().find('input')
        let gps_value = $(gps_input).attr("value").length > 0 ? $(gps_input).attr("value") : $(gps_input).attr("default_coordinates")
        $(this).on('shown.bs.modal', function () {
            $(this).find(".modal-body").append('<div style="height:300px" id="map"></div>')
            drawMap("map", gps_value)
        })
        $(this).on('hidden.bs.modal', function () {
            $('#map').remove();
        })
        $(this).find("button.save_gps").on('click', function () {
            let coordinates = $('#map').parent().prev().find(".modal_gps_value").text();
            $(gps_input).val(coordinates)
        })
    })
})
