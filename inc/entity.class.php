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


class PluginNagiosEntity extends commonDBTM {

  static $rightname='profile';

  static function getTypeName($nb=0) {
    return _n("Entity","Entities",$nb, "nagios"); 
  }

   static function getTable($classname = NULL)
  {
    return "glpi_plugin_nagios_entities";
  }


 function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {


    $ong=array();

    if ($item->getType() == 'Entity') {
      $ong[10]="Nagios - Satellite";
      $ong[11]="Nagios - Macros";
      $ong[12]="Nagios - Import";

      $nagios_entity=new Self;
      $nagios_entity->getEmpty();
      $nagios_entity->getFromEntity($item->getID());
      if ($nagios_entity->fields['plugin_nagios_satellites_id'])
	      $ong[13]="Nagios - Export";
      
    }
  
     return $ong;
  }

  function getFromEntity($entity_id) {
	return $this->getFromDBByQuery("WHERE entities_id='$entity_id' LIMIT 1");
  }


  static function getEntitiesForSatellite($satellite_id,$condition="") {
      global $DB;

      $entities = array();
      $query  = "SELECT `".self::getTable()."`.*
                 FROM `".self::getTable()."` WHERE ";

      $query.="`".self::getTable()."`.`plugin_nagios_satellites_id` in ( '$satellite_id' )";

      if (!empty($condition)) {
         $query .= " AND $condition ";
      }
      foreach ($DB->request($query) as $data) {
         $entities[$data['id']] = $data;
      }
      return $entities;

   }


  static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

    $nagios_entity=new Self;

    switch($tabnum) {

	case 10:
	  $nagios_entity->getEmpty();
	  $nagios_entity->getFromEntity($item->getID());
	  $nagios_entity->showForm($item->getID());
    	  break;
	case 11:
	  $nagios_entity->getEmpty();
	  $nagios_entity->getFromEntity($item->getID());
          $nagios_entity->showFormMacro($item->getID());
	  break;
        case 12:
	  self::showFormImport($item->getID());
	  break;
	case 13:
	  $nagios_entity->getEmpty();
          $nagios_entity->getFromEntity($item->getID());
	  self::showFormExport($nagios_entity);
	  break;
   }

    return true;
  } 


  static function showFormExport($nagios_entity) {
	global $CFG_GLPI;


	$js_options=array('root_doc' => $CFG_GLPI['root_doc'],'entity_id'=>0);

   	echo "<script type='text/javascript'>";
   	echo "var nagios = $(document).nagios(".json_encode($js_options).");";
   	echo "</script>";

   	$onclick="nagios.run_export(".$nagios_entity->fields['plugin_nagios_satellites_id'].",'div_result');";


   	echo "<input type=button class='submit' value='"._('Run Export')."' onclick=\"$onclick\"/>";
   	echo "<div class='nagios-apercu' style='display:none;margin-top:5px' id='div_result' >";

    //  $item->export("/tmp/nagios/".str_replace(" ","_",$item->fields['name'])."/");   
      	echo "</div>";
   	echo "<div class='spaced'></div>";



	


  }

  static function showFormImport($entity_id) {


	echo "<form method='post' enctype=\"multipart/form-data\" action='".Toolbox::getItemTypeFormURL(__CLASS__)."'  >";
	echo "<table class='tab_cadre_fixe'>";
	echo "<tr>";
	echo "<th>";
	echo "<input type='hidden' name='entity_id' value='$entity_id'>";
	echo "<select name='otype'>"
		."<option value='Computer'>"._("Computer")."</option>"
		."<option value='Printer'>"._("Printer")."</option>"
		."<option value='Network'>"._("Network")."</option>"
	     ."</select>";
	echo "</th>";

	echo " <th>"._('File to import').":</th>";
	echo " <td><input type='file' name='filename'></td><td><input type='submit' class='submit' name='importer' value='Importer'></td>";
	echo "</tr>";
	echo "<tr>";
	echo "<td></td><td colspan='2'><b>CSV Format:</b><i>Name;HostTemplate;SupervisionTemplate1;SupervisionTemplate2...</i></td>";
	echo "</tr>";
	echo "</table>";
	Html::closeForm();

  }



  function showFormMacro($entity_id) {
	
	PluginNagiosMacro::showForEntity($entity_id);


  }



  function showForm($entity_id,$options=array()) {
   

      echo "<div class='spaced'>";
      $canedit=self::canUpdate();

      if ($canedit) {
         echo "<form method='post' name='form_entity' action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";
      }

      echo Html::hidden("id",array("value"=>$this->getID()));
      echo "<table class='tab_cadre_fixe'>";
      echo "<tr><th>"._("Satellite")."</th></tr>";
      echo "<tr class='tab_bg_1'><td>";
      PluginNagiosSatellite::Dropdown(array("value"=>$this->fields['plugin_nagios_satellites_id']));
      echo "</td></tr>";

     if ($canedit) {
         echo "<tr>";
         echo "<td class='tab_bg_2 center' colspan='2'>";
         echo "<input type='hidden' name='entities_id' value='$entity_id'>";
         echo "<input type='submit' name='save' value=\""._sx('button','Save')."\" class='submit'>";

         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
      } else {
           echo "</table>"; 
     
      }
      echo "</div>";
       return true;


  }



}
?>
