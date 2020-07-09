<?php

/**
 *  @package Nagios_plugin
 -------------------------------------------------------------------------
 Nagios plugin for GLPI
 Copyright (C) 2016-2017 by SIMIA
 Author fabien.granjon@simia.fr
 https://github.com/pluginsGLPI/
 -------------------------------------------------------------------------
 LICENSE
      
 This file is part of Nagios Plugin.
 Nagios Plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.
 Nagios Plugin  is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 You should have received a copy of the GNU General Public License
 along with Nagios Plugin. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
  
*/

if (!defined('GLPI_ROOT')) {
  die("Sorry. You can't access directly to this file");
}


class PluginNagiosScript extends commonDropdown {

  static $rightname='plugin_nagios_admin';

  static function canCreate() {
    return Session::haveRight(self::$rightname, 1);
  }

  static function canUpdate() {
    return Session::haveRight(self::$rightname, 1);
  }

  static function canPurge() {
    return Session::haveRight(self::$rightname,1);
  }


  static function getMenuContent() {
        return array("title"=>"Nagios - Scripts",'page'=>"/plugins/nagios/front/script.php",
            'links'=>array('search'=> "/plugins/nagios/front/script.php",
                           'add'   => "/plugins/nagios/front/script.form.php"  ) );
  }




  static function getTypeName($nb=0) {
    return "Scripts";
  }

  function showForm($ID,$options=array()) {

      $this->initForm($ID, $options);
      $this->showFormHeader($options);
      echo "<tr class='tab_bg_1'>";
      echo " <td>" . __('Name') ."*</td>";
      echo " <td>";
      echo Html::hidden("id"  ,array('value'=>$this->fields['id']));

      echo Html::input("name",array('value'=>$this->fields['name']));
      echo " </td>";
      echo "</tr>";
      echo "<tr>";
      echo " <td>".__('Commande')."</td>";
      echo " <td>".Html::input("command",array('size'=>100, 'value'=>$this->fields['command']))."</td>";
      echo "</tr>";

      echo "<tr>";
      echo " <td>".__('Arguments')."*</td>";
      echo " <td>".Html::input("args",array('size'=>100,'value'=>$this->fields['args']))."</td>";
      echo "</tr>";

      $this->showFormButtons($options);
      return true;


  }


  function getSearchOptions() {
      $tab = array();
      $tab['common'] = static::getTypeName();
 
      $tab[1]['table']           =    $this->getTable();
      $tab[1]['field']           = 'name';
      $tab[1]['name']            = __('Name');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['itemlink_type']   = 'PluginNagiosScript';

      $tab[2]['table']           = $this->getTable();
      $tab[2]['field']           = 'id';
      $tab[2]['name']            = __('ID');
      $tab[2]['massiveaction']   = false; // implicit field is id
      $tab[2]['datatype']        = 'number';

      $tab[3]['table']           = $this->getTable();
      $tab[3]['field']           = 'command';
      $tab[3]['name']            = __('Command');

      $tab[4]['table']           = $this->getTable();
      $tab[4]['field']           = 'args';
      $tab[4]['name']            = __('Arguments');


      return $tab;
  }

}

