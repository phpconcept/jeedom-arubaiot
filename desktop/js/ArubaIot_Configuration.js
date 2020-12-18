

function fct_arubaiot_display_mode() {


    var mode = $( "#arubaiot_display_mode" ).val();
    if(mode == "normal"){
        $('#show_debug').hide();
        $('#show_avance').hide();
    }
    else if(mode == "advanced"){
        $('#show_debug').hide();
        $('#show_avance').show();
    }
    else if(mode == "debug"){
        $('#show_debug').show();
        $('#show_avance').show();
    }
}


