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


class PluginNagiosCalendar extends commonDBTM {

  static $rightname='plugin_nagios';

  static function getTypeName($nb=0) {
    return _n("Entity","Entities",$nb, "nagios"); 
  }

   static function getTable($classname = NULL)
  {
    return "glpi_plugin_nagios_calendars";
  }


 function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {


    $ong=array();

    if ($item->getType() == 'Calendar') {
      $ong[10]="Nagios";

      
    }
  
     return $ong;
  }

  function getFromCalendar($calendars_id) {
	return $this->getFromDBByQuery("WHERE calendars_id='$calendars_id' LIMIT 1");
  }



  static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

    $calendar=new Self;

    $calendar->getFromCalendar($item->getID());

    switch($tabnum) {

	case 10:
	  $calendar->getEmpty();
	  $calendar->getFromCalendar($item->getID());
	  $calendar->showForm($item->getID());
    	  break;
   }

    return true;
  } 


  function showForm($calendars_id,$options=array()) {
   

      echo "<div class='spaced'>";
      $canedit=self::canUpdate();

      if ($canedit) {
         echo "<form method='post' name='form_entity' action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";
      }

      echo Html::hidden("id",array("value"=>$this->getID()));
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th>"._("Nagios alias")."</th></tr>";
      echo "<tr class='tab_bg_1'><td>";
      echo Html::input("alias",array('value'=>$this->fields['alias']));
      echo "</td></tr>";
      echo "<tr><th>"._("Nagios extra parameters")."</th></tr>";
      echo "<tr class='tab_bg_1'><td>";
      echo "<textarea cols=100 rows=10 name='extras'>";
      echo $this->fields['extras'];
      echo "</textarea>";
      echo "</td></tr>";

      echo "<tr>";
      echo "<td class='tab_bg_2 center' colspan='2'>";
      echo "<input type='hidden' name='calendars_id' value='$calendars_id'>";
      echo "<input type='submit' name='save' value=\""._sx('button','Save')."\" class='submit'>";

      echo "</td></tr>";
      echo "</table>";
      Html::closeForm();
      echo "</div>";
       return true;


  }



}
?>
