
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
 * Management of device inclusion
 */

function swapIncludeState() {

  // ----- data-state is used to store in the div the status of the include mode ...
  var state = $('.changeIncludeState').attr('data-state');

  if (state == 1) {
    // ----- Change include mode to disable
    changeIncludeState(0);
  }
  else {
    // ----- Open the modal to select the options to start include mode
    // see modal.include.php et modal.include.js for more details.
    $('#md_modal').dialog({title: "Include Mode"});
    $('#md_modal').load('index.php?v=d&plugin=ArubaIot&modal=modal.include&id=' + $('.eqLogicAttr[data-l1key=id]').value()).dialog('open');
  }
  
}

function displayIncludeState(p_state) {
  if (p_state == 1) {
    $.hideAlert();
    $('.changeIncludeState').attr('data-state', 1);
    $('.changeIncludeState.card i').removeClass('fa-sign-in-alt fa-rotate-90');
    $('.changeIncludeState.card i').addClass('fa-check');
    $('.changeIncludeState.card span').text('{{Arrêter l\'inclusion}}');
    $('#div_inclusionAlert').showAlert({message: '{{Vous êtes en mode inclusion. Recliquez sur le bouton d\'inclusion pour sortir de ce mode}}', level: 'warning'});
    startRefreshDeviceList();
  } else {
    $.hideAlert();
    $('.changeIncludeState').attr('data-state', 0);
    $('.changeIncludeState.card i').removeClass('fa-check');
    $('.changeIncludeState.card i').addClass('fa-sign-in-alt fa-rotate-90');
    $('.changeIncludeState.card span').text('{{Mode inclusion}}');
    $('#div_inclusionAlert').hideAlert();
    stopRefreshDeviceList();
  }
}

function changeIncludeState(p_state,p_type='',p_unclassified_with_local=0,p_unclassified_with_mac=0,p_unclassified_mac_prefix='',p_unclassified_max_devices=3) {

  // ----- Change the button display depending on state
  displayIncludeState(p_state);
  
  // ----- This will call a PHP script. The PHP will call the websocket.
  // The websocket can't be directly from the user browser
  $.ajax({
    type: "POST",
    url: "plugins/ArubaIot/core/ajax/ArubaIot.ajax.php",
    data: {
      action: "changeIncludeState",
      state: p_state,
      type: p_type,
      unclassified_with_local: p_unclassified_with_local,
      unclassified_with_mac: p_unclassified_with_mac,
      unclassified_mac_prefix: p_unclassified_mac_prefix,
      unclassified_max_devices: p_unclassified_max_devices
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


/*
 * Display reporters modal
 */
function modal_reporters_display() {
  $('#md_modal').dialog({title: "Reporters List"});
  $('#md_modal').load('index.php?v=d&plugin=ArubaIot&modal=modal.reporters&id=' + $('.eqLogicAttr[data-l1key=id]').value()).dialog('open');
}
