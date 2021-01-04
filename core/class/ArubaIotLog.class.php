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
require_once dirname(__FILE__) . '/../../../../plugins/ArubaIot/core/php/ArubaIot.inc.php';

class ArubaIotLog extends eqLogic {

    /**---------------------------------------------------------------------------
     * Method : log()
     * Description :
     *   A placeholder to encapsulate log message, and be able do some
     *   troubleshooting locally.
     * ---------------------------------------------------------------------------
     */
    public function log($p_level, $p_message) {
      global $argv;

      if (!isset($argv[1]) || (($argv[1] != 'console-debug') && ($argv[1] != 'console-trace'))) {
        if ($p_level != 'trace') {
          log::add('ArubaIot', $p_level, $p_message);
        }
        return;
      }

      if ($argv[1] == 'console-debug') {
        if ($p_level != 'trace') echo '['.$p_level.']:'.$p_message."\n";
        return;
      }

      if ($argv[1] == 'console-trace') {
        if ($p_level != 'trace')
          echo '['.$p_level.']:'.$p_message."\n";
        else
          var_dump($p_message);
        return;
      }

    }
    /* -------------------------------------------------------------------------*/

}
