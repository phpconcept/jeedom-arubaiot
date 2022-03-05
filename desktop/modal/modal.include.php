<?php
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

if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}

require_once dirname(__FILE__) . "/../../../../plugins/ArubaIot/core/php/ArubaIot.inc.php";

?>

<script>
    
  $(document).ready(function () {
    // ----- Actions when displaying the modal

  });

</script>


  <form class="form-horizontal onsubmit="return false;"> 
    
    <h4>{{Sélectionner le type d'équipement à inclure :}}&nbsp;&nbsp;</h4>
      
    <table class="table" >
        <tbody>
        
        <?php
          $v_new_row = 0;
          $v_list = ArubaIot::supportedDeviceType(true);
          foreach ($v_list as $v_index => $v_item) {
            if ($v_new_row == 0) {
              echo "<tr>";
            }
            $v_new_row++;
            echo '<td><input type="checkbox"  name="device_type" value="'.$v_index.'"><span >&nbsp;'.$v_item.'</span></td>';
            if ($v_new_row == 4) {
              echo "</tr>";
              $v_new_row=0;
            }
          }
          if ($v_new_row != 0) {
            echo "</tr>";
          }
        ?>

          <tr>
            <td colspan="5">
    
              <input type="checkbox" name="device_type" value="unclassified:unclassified" checked ><span >&nbsp; unclassified</span><br>          
             
              <blockquote>
                <input type="checkbox" name="include_with_local" value="1">
                <span > {{only unclassified devices with local info}}</span>   
                <br>
                <input type="checkbox" name="include_with_mac" value="1">
                <span> {{only unclassified devices with this MAC prefix}} :&nbsp;&nbsp;</span>
                <input class="w3-border w3-round-large" type="text" name="include_mac_prefix" value="XX:XX:XX" >       
                <br>
                <span> {{Stop after}} &nbsp;</span>
                <input class="w3-border w3-round-large" type="text" name="include_max" value="10" style="width:50px;"> 
                <span> {{devices included.}}</span>      
              </blockquote>                

            </td>
          </tr>
        </tbody>
    </table>
    
     
    <div style="text-align: center;">             
    <a id="btApply" class="btn btn-success " onclick="modal_include_apply();"><i class="far fa-check-circle icon-white"></i> Lancer</a>    
    &nbsp;&nbsp;
    <a id="btCancel" class="btn btn-danger " onclick="modal_include_cancel();"><i class="far fa-check-circle icon-white"></i> Annuler</a>
    </div>    
  </form>


<?php include_file('desktop', 'modal_include', 'js', 'ArubaIot'); ?>
