
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
$('.changeIncludeState_OLD').off('click').on('click', function () {

  var el = $(this);
  jeedom.config.save({
    plugin : 'ArubaIot',
    configuration: {autoDiscoverEqLogic: el.attr('data-state')},
    error: function (error) {
      $('#div_alert').showAlert({message: error.message, level: 'danger'});
    },
    success: function () {
      if (el.attr('data-state') == 1) {
        $.hideAlert();
        $('.changeIncludeState').attr('data-state', 0);
        $('.changeIncludeState.card span').text('{{Arrêter l\'inclusion}}');
        $('#div_inclusionAlert').showAlert({message: '{{Vous êtes en mode inclusion. Recliquez sur le bouton d\'inclusion pour sortir de ce mode}}', level: 'warning'});
      } else {
        $.hideAlert();
        $('.changeIncludeState').attr('data-state', 1);
        $('.changeIncludeState.card span').text('{{Mode inclusion}}');
        $('#div_inclusionAlert').hideAlert();
      }
    }
  });

  $.ajax({
    type: "POST",
    url: "plugins/ArubaIot/core/ajax/ArubaIot.ajax.php",
    data: {
      action: "changeIncludeState",
      state: $(this).attr('data-state')
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
});



$('.changeIncludeState').off('click').on('click', function () {

  var mode = $(this).attr('data-mode');
  var state = $(this).attr('data-state');
  if (mode != 1 || mode == 1  && state == 0) {
    changeIncludeState(state, mode);
  }
  else {
    var dialog_title = '';
    var dialog_message = '<form class="form-horizontal onsubmit="return false;"> ';
    dialog_title = '<label class="control-label" >{{Mode Inclusion}}</label>';
    dialog_message += '<label class="control-label" > {{Sélectionner le type d\'équipement à inclure :}} </label> <br><br>' +

    '<input type="checkbox" name="class_type" value="enoceanSwitch" checked /> {{enoceanSwitch}}<br>' +
    '<input type="checkbox" name="class_type" value="enoceanSensor" /> {{enoceanSensor}}<br>' +
    '<input type="checkbox" name="class_type" value="arubaTag" /> {{arubaTag}}<br>' +
    '<input type="checkbox" name="class_type" value="arubaBeacon" /> {{arubaBeacon}}<br>' +
    '<input type="checkbox" name="class_type" value="generic" /> {{generic}}<br>' +
    '<br>' +
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


            var my_array = [];
            $("input:checkbox[name=class_type]:checked").each(function() {
                my_array.push($(this).val());
            });
            var v_type = JSON.stringify(my_array);
            var type = $("input[name='type']:checked").val();
            //if (type == 0) {
              changeIncludeState(state, mode, v_type);
            //} else {
            //}
          }
        },
      }
    });
  }
});


function changeIncludeState(p_state,_mode,p_type='') {

      if (p_state == 1) {
        $.hideAlert();
        $('.changeIncludeState').attr('data-state', 0);
        $('.changeIncludeState.card span').text('{{Arrêter l\'inclusion}}');
        $('#div_inclusionAlert').showAlert({message: '{{Vous êtes en mode inclusion. Recliquez sur le bouton d\'inclusion pour sortir de ce mode}}', level: 'warning'});
      } else {
        $.hideAlert();
        $('.changeIncludeState').attr('data-state', 1);
        $('.changeIncludeState.card span').text('{{Mode inclusion}}');
        $('#div_inclusionAlert').hideAlert();
      }

  $.ajax({
    type: "POST",
    url: "plugins/ArubaIot/core/ajax/ArubaIot.ajax.php",
    data: {
      action: "changeIncludeState",
      state: p_state,
      type: p_type
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
$('.displayReporters').off('click').on('click', function () {

  $('#md_modal').dialog({title: "Reporters List"});
  $('#md_modal').load('index.php?v=d&plugin=ArubaIot&modal=modal.reporters&id=' + $('.eqLogicAttr[data-l1key=id]').value()).dialog('open');

});


