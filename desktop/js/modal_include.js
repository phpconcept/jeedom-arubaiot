
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



function modal_include_apply() {
    
  var mac_prefix = '';
  var with_local = 0;
  var with_mac = 0;
  var my_array = [];
  $("input:checkbox[name=device_type]:checked").each(function() {
      var v_val = $(this).val();
      // ----- If unclassifed is checked, look for additional properties
      if (v_val == 'unclassified:unclassified') {
        $("input[type='checkbox'][name='include_with_local']:checked").each(function() {with_local = 1;});
        $("input[type='checkbox'][name='include_with_mac']:checked").each(function() {with_mac = 1;mac_prefix = $("input[type='text'][name='include_mac_prefix']").val();});
      //alert('with_mac='+with_mac);
      //alert('mac_prefix='+mac_prefix);
      //alert('with_local='+with_local);
      }

      my_array.push(v_val);
  });
  var v_type = JSON.stringify(my_array);
  
  var max_devices = $("input[type='text'][name='include_max']").val();

  // ----- Exemple to get values
  //var type = $("input[name='type']:checked").val();
  
  // ----- Call the websocket
  changeIncludeState(1, v_type, with_local, with_mac, mac_prefix, max_devices);

  $('#md_modal').dialog('close');
  
}

function modal_include_cancel() {
  $('#md_modal').dialog('close');
}


