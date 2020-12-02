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

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class ArubaIot extends eqLogic {
    /*     * *************************Attributs****************************** */



    /*     * ***********************Methode static*************************** */

    /*
     * Fonction exécutée automatiquement toutes les minutes par Jeedom
      public static function cron() {

      }
     */


    /*
     * Fonction exécutée automatiquement toutes les heures par Jeedom
      public static function cronHourly() {

      }
     */

    /*
     * Fonction exécutée automatiquement tous les jours par Jeedom
      public static function cronDaily() {

      }
     */


    /*     * *********************Méthodes d'instance************************* */

    public function preInsert() {
      $this->setConfiguration('mac_address', '00:00:00:00:00:00');
      $this->setConfiguration('class_type', 'auto');
      $this->setConfiguration('vendor_name', '');
      $this->setConfiguration('local_name', '');
      $this->setConfiguration('model', '');
    }

    public function postInsert() {
        
    }

    public function preSave() {
      //message::add('ArubaIot',  'PreSave ...' );

      // ----- Change to uppercase
      $v_val = $this->getConfiguration('mac_address');
	  $this->setConfiguration('mac_address', strtoupper($v_val));

    }

    public function postSave() {

      $v_class_type = $this->getConfiguration('class_type');

      // ----- Default cmd for all
      $info = $this->getCmd(null, 'rssi');
      if (!is_object($info)) {
        log::add('ArubaIot',  'debug', "Activate Cmd 'RSSI' for device.");
        $info = new arubacentralCmd();
        $info->setName(__('RSSI', __FILE__));

        $info->setLogicalId('rssi');
        $info->setEqLogic_id($this->getId());
        $info->setType('info');
        $info->setSubType('string');
        $info->save();
      }

      // ----- Look for commands to add depending of class_type
      if ($v_class_type == "enoceanSensor") {

        $info = $this->getCmd(null, 'illumination');
        if (!is_object($info)) {
          log::add('ArubaIot',  'debug', "Activate Cmd 'illumination' for device.");
          $info = new arubacentralCmd();
          $info->setName(__('Illumination', __FILE__));

          $info->setLogicalId('illumination');
          $info->setEqLogic_id($this->getId());
          $info->setType('info');
          $info->setSubType('numeric');
          $info->setIsHistorized(true);
          $info->save();
        }

        $info = $this->getCmd(null, 'occupancy');
        if (!is_object($info)) {
          log::add('ArubaIot',  'debug', "Activate Cmd 'occupancy' for device.");
          $info = new arubacentralCmd();
          $info->setName(__('Occupancy', __FILE__));

          $info->setLogicalId('occupancy');
          $info->setEqLogic_id($this->getId());
          $info->setType('info');
          $info->setSubType('numeric');
          $info->setIsHistorized(true);
          $info->save();
        }

      }

      // ----- Look for commands to add depending of class_type
      if (($v_class_type == "arubaTag") || ($v_class_type == "generic")) {

        $info = $this->getCmd(null, 'presence');
        if (!is_object($info)) {
          log::add('ArubaIot',  'debug', "Activate Cmd 'presence' for device.");
          $info = new arubacentralCmd();
          $info->setName(__('Presence', __FILE__));

          $info->setLogicalId('presence');
          $info->setEqLogic_id($this->getId());
          $info->setType('info');
          $info->setSubType('binary');
          $info->setIsHistorized(true);
          $info->save();
        }

      }

    }

    public function preUpdate() {
        
    }

    public function postUpdate() {
        
    }

    public function preRemove() {
        
    }

    public function postRemove() {
        
    }

    /*
     * Non obligatoire mais permet de modifier l'affichage du widget si vous en avez besoin
      public function toHtml($_version = 'dashboard') {

      }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action après modification de variable de configuration
    public static function postConfig_<Variable>() {
    }
     */

    /*
     * Non obligatoire mais ca permet de déclencher une action avant modification de variable de configuration
    public static function preConfig_<Variable>() {
    }
     */

    /*     * **********************Getteur Setteur*************************** */
}

class ArubaIotCmd extends cmd {
    /*     * *************************Attributs****************************** */


    /*     * ***********************Methode static*************************** */


    /*     * *********************Methode d'instance************************* */

    /*
     * Non obligatoire permet de demander de ne pas supprimer les commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
      public function dontRemoveCmd() {
      return true;
      }
     */

    public function execute($_options = array()) {
        
        log::add('ArubaIot', 'info',  "Commande reçue !");

        if ($this->getType() != 'action') {
			return;
		}
		$eqLogic = $this->getEqlogic();

    }

    /*     * **********************Getteur Setteur*************************** */
}


