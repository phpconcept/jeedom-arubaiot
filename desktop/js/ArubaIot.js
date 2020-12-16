
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
$('.changeIncludeState').off('click').on('click', function () {

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


/*
 * Display reporters modal
 */
$('.displayReporters').off('click').on('click', function () {

  $('#md_modal').dialog({title: "Reporters List"});
  $('#md_modal').load('index.php?v=d&plugin=ArubaIot&modal=modal.reporters&id=' + $('.eqLogicAttr[data-l1key=id]').value()).dialog('open');


  return;

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


