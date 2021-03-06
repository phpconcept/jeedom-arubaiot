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


$plugin = plugin::byId('ArubaIot');
sendVarToJS('eqType', $plugin->getId());
$eqLogics = eqLogic::byType($plugin->getId());


?>

<div class="eqLogicThumbnailContainer"></div>


  <legend><i class="fas fa-table"></i> {{Equipements Enocean}}</legend>
<div class="eqLogicThumbnailContainer">
    <?php
foreach ($eqLogics as $eqLogic) {
    $v_class = $eqLogic->getConfiguration('class_type', '');
    if (($v_class == 'enoceanSensor') || ($v_class == 'enoceanSwitch')) {
    	$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
    	echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
    	echo '<img src="' . $eqLogic->getImage() . '"/>';
    	echo '<br>';
    	echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
    	echo '</div>';
    }
}
?>
</div>


  <legend><i class="fas fa-table"></i> {{Etiquettes Aruba}}</legend>
<div class="eqLogicThumbnailContainer">
    <?php
foreach ($eqLogics as $eqLogic) {
    $v_class = $eqLogic->getConfiguration('class_type', '');
    if ($v_class == 'arubaTag') {
    	$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
    	echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
    	echo '<img src="' . $eqLogic->getImage() . '"/>';
    	echo '<br>';
    	echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
    	echo '</div>';
    }
}
?>
</div>


  <legend><i class="fas fa-table"></i> {{Autres Equipements IoT}}</legend>
<div class="eqLogicThumbnailContainer">
    <?php
foreach ($eqLogics as $eqLogic) {
    $v_class = $eqLogic->getConfiguration('class_type', '');
    if (($v_class != 'arubaTag') && ($v_class != 'enoceanSensor') && ($v_class != 'enoceanSwitch')) {
    	$opacity = ($eqLogic->getIsEnable()) ? '' : 'disableCard';
    	echo '<div class="eqLogicDisplayCard cursor '.$opacity.'" data-eqLogic_id="' . $eqLogic->getId() . '">';
    	echo '<img src="' . $plugin->getPathImgIcon() . '"/>';
    	echo '<br>';
    	echo '<span class="name">' . $eqLogic->getHumanName(true, true) . '</span>';
    	echo '</div>';
    }
}
?>
</div>



<?php include_file('desktop', 'ArubaIot', 'js', 'ArubaIot');?>
<?php include_file('core', 'plugin.template', 'js');?>
