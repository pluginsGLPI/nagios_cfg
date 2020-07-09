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


class PluginNagiosRole extends PluginNagiosObject {

  static $nagios_type = PluginNagiosObject::NAGIOS_ROLE_TYPE;

    /* define general fields */
  static $FIELDS_STD_VIEW=array(
                            array( "libel"=>'General options' ,
                                    "fields"=>array('') ),
                               );

  static $FIELDS_NOTIF_VIEW=array(
                            array( "libel"=>'Notification options' ,
                                    "fields"=>array() ),
                               );


  static function getMenuContent() {
        return array("title"=>"Nagios - Modèles de supervision",'page'=>"/plugins/nagios/front/role.php",
            'links'=>array('search'=> "/plugins/nagios/front/role.php",
                           'add'   => "/plugins/nagios/front/role.form.php"  ) );
  }



  static function getTypeName($nb=0) {
    return _n("Supervision Template","Supervision Templates",$nb, "nagios"); 
  }

  
  function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      $ong=array();
      $ong=parent::getTabNameForItem($item, $withtemplate);
      $ong[15]=_("Nagios - Services");
      ksort($ong);

      return $ong;

  }

  static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

          switch ($tabnum) {
            case 15:
              PluginNagiosObjectLink::showServicesForItem($item);
              break;
            default:
              parent::displayTabContentForItem($item, $tabnum, $withtemplate);
              break;
         }


  } /* end function displayTabContentForItem */


 static function addMassiveRole($role_id,$ids) {
    global $DB;

      if (!is_array($ids))
	return false;

      $query  = "SELECT `glpi_plugin_nagios_objectlinks`.plugin_nagios_objects_id as item_id
                 FROM `glpi_plugin_nagios_objectlinks` WHERE `glpi_plugin_nagios_objectlinks`.items_id='$role_id'"
		 ." and `glpi_plugin_nagios_objectlinks`.plugin_nagios_objects_id in (".implode(",",$ids).") and itemtype='PluginNagiosRole'";

      $item_already_linked=array();
      $item_to_link=array();
	 
      foreach ($DB->request($query) as $data) {
         $item_already_linked[$data['item_id']] = true;
      }

      foreach ($ids as $idx=>$item_id) {
	if (!isset($item_already_linked[$item_id]))
		$item_to_link[$item_id]=$item_id;
      }

      
      $query="insert into `glpi_plugin_nagios_objectlinks` (plugin_nagios_objects_id,items_id,itemtype)  ".
	     " (select id,$role_id,'PluginNagiosRole' from glpi_plugin_nagios_objects where id in (".implode(",",$item_to_link)." ) )"; 

      return $DB->query($query); 
      

 }

 static function removeMassiveRole($role_id,$ids) {
   global $DB;
      if (!is_array($ids))
        return false;


      $query  = "delete from `glpi_plugin_nagios_objectlinks` where " 
                 ." `glpi_plugin_nagios_objectlinks`.plugin_nagios_objects_id in (".implode(",",$ids).") and itemtype='PluginNagiosRole' and items_id='$role_id'";


      return $DB->query($query) ;



 }

   function getSearchOptions() {
      
      $tab=parent::getSearchOptions();


      $tab[30004] = [
         'id'                 => '30004',
         'table'              => 'glpi_plugin_nagios_services_view',
         'field'              => 'alias',
         'name'               => __('Linked Services'),
         'comments'           => true,
         'forcegroupby'       => true,
         'datatype'           => 'itemlink',
         'additionalfields'   => ['alias'],
         'itemlink_type'      => 'PluginNagiosService',
      ];


        return $tab;   
} 
 


 function showFormForItem($item,$options=array() ) {

      $rand    = mt_rand();
      $ID=$item->getID();

      $this->initForm(-1, $options);
      echo "<div id='searchcriterias' style='width:40%' >";
      echo "<form name='hosttemplate_roles_form$rand' id='hosttemplate_roles_form$rand' method='post'";
      echo " action='".Toolbox::getItemTypeFormURL(self::getClassForType($item->fields['type']))."'>";
      echo "<input type='hidden' name='plugin_nagios_objects_id' value='$ID'>";
      echo "<input type='hidden' name='itemtype' value='PluginNagiosRole'>";
      echo "<input type='hidden' name='entities_id' value='{$item->fields['entities_id']}'>";
      echo "<table class='tab_cadre_fixe' width='20%'>";
      echo "<tr class='tab_bg_1'>";
      echo "<th class='left'>"._("Associate a Supervision Template")."</th>";
      echo " <td>";
      $data['entity']=PluginNagiosObject::getRecursiveEntities($item->fields['entities_id']);
      $data['name']='items_id';
            PluginNagiosRole::Dropdown($data);
      echo "</td>";
      echo "<td class='left'>";
      echo "<input type='submit' name='addroletoitem' value=\"".__('Add')."\"class='submit'>";
      echo "</td>";
      echo "</tr>";
      echo "</table>";
      Html::closeForm();
        echo "</div>";
      return true;

  }


 
  function showNagiosDef() {
                        


        return $buf;
  }


}

