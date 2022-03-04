
/* This file is part of Jeedom.
 *
 * Jeedom is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Jeedom is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
 */


$("#table_cmd").sortable({axis: "y", cursor: "move", items: ".cmd", placeholder: "ui-state-highlight", tolerance: "intersect", forcePlaceholderSize: true});
/*
 * Fonction pour l'ajout de commande, appellé automatiquement par plugin.template
 */
function addCmdToTable(_cmd) {
    if (!isset(_cmd)) {
        var _cmd = {configuration: {}};
    }
    if (!isset(_cmd.configuration)) {
        _cmd.configuration = {};
    }
    var tr = '<tr class="cmd" data-cmd_id="' + init(_cmd.id) + '">';
    tr += '<td>';
    tr += '<span class="cmdAttr" data-l1key="id" style="display:none;"></span>';
    tr += '<input class="cmdAttr form-control input-sm" data-l1key="name" style="width : 140px;" placeholder="{{Nom}}">';
    tr += '</td>';
    tr += '<td>';
    tr += '<span class="type" type="' + init(_cmd.type) + '">' + jeedom.cmd.availableType() + '</span>';
    tr += '<span class="subType" subType="' + init(_cmd.subType) + '"></span>';
    tr += '</td>';
    tr += '<td>';
    if (is_numeric(_cmd.id)) {
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="configure"><i class="fa fa-cogs"></i></a> ';
        tr += '<a class="btn btn-default btn-xs cmdAction" data-action="test"><i class="fa fa-rss"></i> {{Tester}}</a>';
    }
    tr += '<i class="fa fa-minus-circle pull-right cmdAction cursor" data-action="remove"></i>';
    tr += '</td>';
    tr += '</tr>';
    $('#table_cmd tbody').append(tr);
    $('#table_cmd tbody tr:last').setValues(_cmd, '.cmdAttr');
    if (isset(_cmd.type)) {
        $('#table_cmd tbody tr:last .cmdAttr[data-l1key=type]').value(init(_cmd.type));
    }
    jeedom.cmd.changeType($('#table_cmd tbody tr:last'), init(_cmd.subType));
}



/*
 * Management of inclusion
 */
$('.changeIncludeState_SAVE').off('click').on('click', function () {

  var mode = $(this).attr('data-mode');
  var state = $(this).attr('data-state');
  if (mode != 1 || mode == 1  && state == 0) {
    changeIncludeState(state, mode);
  }
  else {
    var dialog_title = '';
    var dialog_message = '<form class="form-horizontal onsubmit="return false;"> ';
    dialog_title = '<label class="control-label" >{{Mode Inclusion}}</label>';
    dialog_message += '<label class="control-label" > {{Sélectionner le type d\'équipement à inclure :}} </label> ' +

    '<br><blockquote>' +
    '<input type="checkbox" name="class_type" value="enoceanSwitch" checked /> {{enoceanSwitch}}<br>' +
    '<input type="checkbox" name="class_type" value="enoceanSensor" /> {{enoceanSensor}}<br>' +
    '<input type="checkbox" name="class_type" value="arubaTag" /> {{arubaTag}}<br>' +
    '<input type="checkbox" name="class_type" value="arubaBeacon" /> {{arubaBeacon}}<br>' +
    '<input type="checkbox" name="class_type" value="generic" /> {{generic}}<br>' +
    '<blockquote>' +
    '<input type="checkbox" name="generic_with_local" value="1" /> {{only with local info present}}<br>' +
    '<input type="checkbox" name="generic_with_mac" value="1" /> {{filtered by mac prefix}} : <input type="text" id="mac_prefix" name="mac_prefix" value="XX:XX:XX" /><br>' +
    '{{Limited to a maximum of}} <input type="text" id="max_devices" name="max_devices" value="3" style="width:50px;"/> {{generic devices}}<br>' +
    '</blockquote>' +
    '</blockquote>' +
        '';

    dialog_message += '</form>';
    bootbox.dialog({
      title: dialog_title,
      message: dialog_message,
      buttons: {
        "{{Annuler}}": {
          className: "btn-danger",
          callback: function () {
          }
        },
        success: {
          label: "{{Démarrer}}",
          className: "btn-success",
          callback: function () {

            var mac_prefix = '';
            var with_local = 0;
            var with_mac = 0;
            var my_array = [];
            $("input:checkbox[name=class_type]:checked").each(function() {
                var v_val = $(this).val();
                if (v_val == 'generic') {
                  $("input[type='checkbox'][name='generic_with_local']:checked").each(function() {with_local = 1;});
                  $("input[type='checkbox'][name='generic_with_mac']:checked").each(function() {with_mac = 1;mac_prefix = $("input[type='text'][name='mac_prefix']").val();});
                //alert('with_mac='+with_mac);
                //alert('mac_prefix='+mac_prefix);
                //alert('with_local='+with_local);
                }

                my_array.push(v_val);
            });
            var v_type = JSON.stringify(my_array);

            var max_devices = $("input[type='text'][name='max_devices']").val();

            // ----- Exemple to get values
            //var type = $("input[name='type']:checked").val();

            // ----- Call the websocket
            changeIncludeState(state, mode, v_type, with_local, with_mac, mac_prefix, max_devices);
          }
        },
      }
    });
  }
});


$('.changeIncludeState').off('click').on('click', display_changeIncludeState); 

function display_changeIncludeState() {

  var mode = $(this).attr('data-mode');
  var state = $(this).attr('data-state');
  if (mode != 1 || mode == 1  && state == 0) {
    changeIncludeState(state, mode);
  }
  else {
    var dialog_title = '';
    var dialog_message = '<form class="form-horizontal onsubmit="return false;"> ';
    dialog_title = '<label class="control-label" >{{Mode Inclusion}}</label>';
    dialog_message += '<label class="control-label" > {{Sélectionner le type d\'équipement à inclure :}} </label> ' +

    '<br><blockquote>' +
    '<input type="checkbox" name="class_type" value="enoceanSwitch" checked /> {{enoceanSwitch}}<br>' +
    '<input type="checkbox" name="class_type" value="enoceanSensor" /> {{enoceanSensor}}<br>' +
    '<input type="checkbox" name="class_type" value="arubaTag" /> {{arubaTag}}<br>' +
    '<input type="checkbox" name="class_type" value="arubaBeacon" /> {{arubaBeacon}}<br>' +
    '<input type="checkbox" name="class_type" value="generic" /> {{generic}}<br>' +
    '<blockquote>' +
    '<input type="checkbox" name="generic_with_local" value="1" /> {{only with local info present}}<br>' +
    '<input type="checkbox" name="generic_with_mac" value="1" /> {{filtered by mac prefix}} : <input type="text" id="mac_prefix" name="mac_prefix" value="XX:XX:XX" /><br>' +
    '{{Limited to a maximum of}} <input type="text" id="max_devices" name="max_devices" value="3" style="width:50px;"/> {{generic devices}}<br>' +
    '</blockquote>' +
    '</blockquote>' +
        '';

    dialog_message += '</form>';
    bootbox.dialog({
      title: dialog_title,
      message: dialog_message,
      buttons: {
        "{{Annuler}}": {
          className: "btn-danger",
          callback: function () {
          }
        },
        success: {
          label: "{{Démarrer}}",
          className: "btn-success",
          callback: function () {

            var mac_prefix = '';
            var with_local = 0;
            var with_mac = 0;
            var my_array = [];
            $("input:checkbox[name=class_type]:checked").each(function() {
                var v_val = $(this).val();
                if (v_val == 'generic') {
                  $("input[type='checkbox'][name='generic_with_local']:checked").each(function() {with_local = 1;});
                  $("input[type='checkbox'][name='generic_with_mac']:checked").each(function() {with_mac = 1;mac_prefix = $("input[type='text'][name='mac_prefix']").val();});
                //alert('with_mac='+with_mac);
                //alert('mac_prefix='+mac_prefix);
                //alert('with_local='+with_local);
                }

                my_array.push(v_val);
            });
            var v_type = JSON.stringify(my_array);

            var max_devices = $("input[type='text'][name='max_devices']").val();

            // ----- Exemple to get values
            //var type = $("input[name='type']:checked").val();

            // ----- Call the websocket
            changeIncludeState(state, mode, v_type, with_local, with_mac, mac_prefix, max_devices);
          }
        },
      }
    });
  }
}

$('.displayInclude').off('click').on('click', display_changeIncludeState_NEW); 

function display_changeIncludeState_NEW() {

  $('#md_modal').dialog({title: "Include Mode"});
  $('#md_modal').load('index.php?v=d&plugin=ArubaIot&modal=modal.include&id=' + $('.eqLogicAttr[data-l1key=id]').value()).dialog('open');

  return;


  var mode = $(this).attr('data-mode');
  var state = $(this).attr('data-state');
  if (mode != 1 || mode == 1  && state == 0) {
    changeIncludeState(state, mode);
  }
  else {
    var dialog_title = '';
    var dialog_message = '<form class="form-horizontal onsubmit="return false;"> ';
    dialog_title = '<label class="control-label" >{{Mode Inclusion}}</label>';
    dialog_message += '<label class="control-label" > {{Sélectionner le type d\'équipement à inclure :}} </label> ' +

    '<br><blockquote>' +
    '<input type="checkbox" name="class_type" value="enoceanSwitch" checked /> {{enoceanSwitch}}<br>' +
    '<input type="checkbox" name="class_type" value="enoceanSensor" /> {{enoceanSensor}}<br>' +
    '<input type="checkbox" name="class_type" value="arubaTag" /> {{arubaTag}}<br>' +
    '<input type="checkbox" name="class_type" value="arubaBeacon" /> {{arubaBeacon}}<br>' +
    '<input type="checkbox" name="class_type" value="generic" /> {{generic}}<br>' +
    '<blockquote>' +
    '<input type="checkbox" name="generic_with_local" value="1" /> {{only with local info present}}<br>' +
    '<input type="checkbox" name="generic_with_mac" value="1" /> {{filtered by mac prefix}} : <input type="text" id="mac_prefix" name="mac_prefix" value="XX:XX:XX" /><br>' +
    '{{Limited to a maximum of}} <input type="text" id="max_devices" name="max_devices" value="3" style="width:50px;"/> {{generic devices}}<br>' +
    '</blockquote>' +
    '</blockquote>' +
        '';

    dialog_message += '</form>';
    bootbox.dialog({
      title: dialog_title,
      message: dialog_message,
      buttons: {
        "{{Annuler}}": {
          className: "btn-danger",
          callback: function () {
          }
        },
        success: {
          label: "{{Démarrer}}",
          className: "btn-success",
          callback: function () {

            var mac_prefix = '';
            var with_local = 0;
            var with_mac = 0;
            var my_array = [];
            $("input:checkbox[name=class_type]:checked").each(function() {
                var v_val = $(this).val();
                if (v_val == 'generic') {
                  $("input[type='checkbox'][name='generic_with_local']:checked").each(function() {with_local = 1;});
                  $("input[type='checkbox'][name='generic_with_mac']:checked").each(function() {with_mac = 1;mac_prefix = $("input[type='text'][name='mac_prefix']").val();});
                //alert('with_mac='+with_mac);
                //alert('mac_prefix='+mac_prefix);
                //alert('with_local='+with_local);
                }

                my_array.push(v_val);
            });
            var v_type = JSON.stringify(my_array);

            var max_devices = $("input[type='text'][name='max_devices']").val();

            // ----- Exemple to get values
            //var type = $("input[name='type']:checked").val();

            // ----- Call the websocket
            changeIncludeState(state, mode, v_type, with_local, with_mac, mac_prefix, max_devices);
          }
        },
      }
    });
  }
}


function changeIncludeState_SAVE(p_state,_mode,p_type='',p_generic_with_local=0,p_generic_with_mac=0,p_generic_mac_prefix='',p_generic_max_devices=3) {

      if (p_state == 1) {
        $.hideAlert();
        $('.changeIncludeState').attr('data-state', 0);
        $('.changeIncludeState.card span').text('{{Arrêter l\'inclusion}}');
        $('#div_inclusionAlert').showAlert({message: '{{Vous êtes en mode inclusion. Recliquez sur le bouton d\'inclusion pour sortir de ce mode}}', level: 'warning'});
        startRefreshDeviceList();
      } else {
        $.hideAlert();
        $('.changeIncludeState').attr('data-state', 1);
        $('.changeIncludeState.card span').text('{{Mode inclusion}}');
        $('#div_inclusionAlert').hideAlert();
        stopRefreshDeviceList();
      }

  $.ajax({
    type: "POST",
    url: "plugins/ArubaIot/core/ajax/ArubaIot.ajax.php",
    data: {
      action: "changeIncludeState",
      state: p_state,
      type: p_type,
      generic_with_local: p_generic_with_local,
      generic_with_mac: p_generic_with_mac,
      generic_mac_prefix: p_generic_mac_prefix,
      generic_max_devices: p_generic_max_devices
    },
    dataType: 'json',
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }
    }
  });

}

function changeIncludeState(p_state,_mode,p_type='',p_generic_with_local=0,p_generic_with_mac=0,p_generic_mac_prefix='',p_generic_max_devices=3) {

      if (p_state == 1) {
        $.hideAlert();
        $('.changeIncludeState').attr('data-state', 0);
        $('.changeIncludeState.card span').text('{{Arrêter l\'inclusion}}');
        $('#div_inclusionAlert').showAlert({message: '{{Vous êtes en mode inclusion. Recliquez sur le bouton d\'inclusion pour sortir de ce mode}}', level: 'warning'});
        startRefreshDeviceList();
      } else {
        $.hideAlert();
        $('.changeIncludeState').attr('data-state', 1);
        $('.changeIncludeState.card span').text('{{Mode inclusion}}');
        $('#div_inclusionAlert').hideAlert();
        stopRefreshDeviceList();
      }

  $.ajax({
    type: "POST",
    url: "plugins/ArubaIot/core/ajax/ArubaIot.ajax.php",
    data: {
      action: "changeIncludeState",
      state: p_state,
      type: p_type,
      generic_with_local: p_generic_with_local,
      generic_with_mac: p_generic_with_mac,
      generic_mac_prefix: p_generic_mac_prefix,
      generic_max_devices: p_generic_max_devices
    },
    dataType: 'json',
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }
    }
  });

}

/*
 * Display reporters modal
 */
$('.displayReporters').off('click').on('click', modal_DisplayReporters);

function modal_DisplayReporters() {
  $('#md_modal').dialog({title: "Reporters List"});
  $('#md_modal').load('index.php?v=d&plugin=ArubaIot&modal=modal.reporters&id=' + $('.eqLogicAttr[data-l1key=id]').value()).dialog('open');
}


/*
 * Display  list of the equipements in the plugin dashboard list
 */
var refresh_timeout;

function refreshDeviceList() {

  $('#device_list').load('index.php?v=d&plugin=ArubaIot&modal=modal.device_list');
}

function startRefreshDeviceList() {
  $('#inclusion_message_container').show();
  document.getElementById("inclusion_message_count").innerHTML =   "0";

  refresh_timeout = setInterval(refreshDeviceCount, 3000);
}

function refreshDeviceCount() {

  $.ajax({
    type: "POST",
    url: "plugins/ArubaIot/core/ajax/ArubaIot.ajax.php",
    data: {
      action: "getIncludedDeviceCount",
      state: 'tbd'
    },
    dataType: 'json',
    error: function (request, status, error) {
      handleAjaxError(request, status, error);
    },
    success: function (data) {
      if (data.state != 'ok') {
        $('#div_alert').showAlert({message: data.result, level: 'danger'});
        return;
      }
      document.getElementById("inclusion_message_count").innerHTML = data.result.count;
    }
  });


  //$('#device_list').load('index.php?v=d&plugin=ArubaIot&modal=modal.device_list');
  //$('#inclusion_message_container').append('.');
  //document.getElementById("inclusion_message_count").innerHTML =   "1";
}

function stopRefreshDeviceList() {
  clearInterval(refresh_timeout);
  $('#device_list').load('index.php?v=d&plugin=ArubaIot&modal=modal.device_list');
  $('#inclusion_message_container').hide();
}


