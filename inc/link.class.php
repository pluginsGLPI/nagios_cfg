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


class PluginNagiosLink extends CommonDBTM {
 
  static $rightname='plugin_nagios';


   //id,parent_id,owner_id,itemtype,items_id
  //              PluginNagiosHostgroup  => from Host_fields or parent
  //              PluginNagiosService    => from Host_Links or Roles
  //              PluginNagiosServiceGroup => From Service_fields or parents
  //              PluginNagiosRoles => From Host_Link or Parents;



   static function canCreate() {
     return Session::haveRightsOr(self::$rightname, array(CREATE));
   }

 


   static function item_add($item) {
    global $DB;
	if (isset($item->fields['parent_objects_id']) and $item->fields['parent_objects_id']>0 )  {
	  $query="insert into glpi_plugin_nagios_links (plugin_nagios_objects_id,parent_id,owner_id,itemtype,items_id) values (".
	      $item->getID().",0,".$item->getID().",'".$item->getType()."',".$item->fields['parent_objects_id'].")";
	   $DB->query($query);
	


	  $query="insert into glpi_plugin_nagios_links (plugin_nagios_objects_id,parent_id,owner_id,itemtype,items_id) (".
	       "SELECT ".
		 $item->getID().",".$item->fields['parent_objects_id'].",owner_id,itemtype,items_id from glpi_plugin_nagios_links where plugin_nagios_objects_id=".$item->fields['parent_objects_id'].") ";	
	  $DB->query($query);
	}

   }

   static function item_update($item) {
      global $DB;     

        $query="delete from glpi_plugin_nagios_links where (plugin_nagios_objects_id='".$item->getID()."' and parent_id>0) or (parent_id='".$item->getID()."' and owner_id<>'".$item->getID()."') or (plugin_nagios_objects_id='".$item->getID()."' and parent_id=0 and itemtype='".$item->getType()."') " ;
        $DB->query($query);

        if ($item->fields['parent_objects_id']) {
	    $query="insert into glpi_plugin_nagios_links (plugin_nagios_objects_id,parent_id,owner_id,itemtype,items_id) values (".
              $item->getID().",0,".$item->getID().",'".$item->getType()."',".$item->fields['parent_objects_id'].")";
           $DB->query($query);
	 

 
   	    self::recurse_add_link_from_sql($item->getID(),$item->fields['parent_objects_id']," plugin_nagios_objects_id='".$item->fields['parent_objects_id']."'");
        }
		  


   }

  static function item_purge($item) {
     global $DB;
     
     $query="delete from glpi_plugin_nagios_links where items_id='".$item->getID()."' or parent_id='".$item->getID()."' or owner_id='".$item->getID()."' or plugin_nagios_objects_id='".$item->getID()."'" ;
     //echo $query; 
     $DB->query($query);
  }



  /* manage link */
  static function link_item_add($item) {
	global $DB;

	$input['owner_id']=$item->fields['plugin_nagios_objects_id'];
	$input['itemtype']=$item->fields['itemtype'];
        $input['items_id']=$item->fields['items_id'];
	self::recurse_add_link_from_array($item->fields['plugin_nagios_objects_id'],0,$input);

  }

  static function link_item_purge($item) {
    global $DB;	
	$id=$item->fields['plugin_nagios_objects_id'];
	$itemtype=$item->fields['itemtype'];
        $items_id=$item->fields['items_id'];

        $DB->query("delete from glpi_plugin_nagios_links where owner_id='$id' and itemtype='$itemtype' and items_id='$items_id'");

  }




 static function values_item_update($item) {
       self::values_item_purge($item);
       self::values_item_add($item);
 }

 static function values_item_purge($item) {
	global $DB;
	$field=new PluginNagiosField();
	$field->getFromDB($item->fields['plugin_nagios_fields_id']);
        if (!in_array($field->fields['name'],array("use","hostgroups","servicegroups","check_command")))
                return ;

        $itemtype=$field->getItemType();
        if (!$itemtype)
                return ;

        $id=$item->fields['plugin_nagios_objects_id'];

        $DB->query("delete from glpi_plugin_nagios_links where owner_id='$id' and itemtype='$itemtype'");


 }

 static function values_item_add($item) {

	$field=new PluginNagiosField();
	$field->getFromDB($item->fields['plugin_nagios_fields_id']);
	if (!in_array($field->fields['name'],array("use","hostgroups","servicegroups","check_command")))
		return ;
	
	$itemtype=$field->getItemType();
	if (!$itemtype)
		return ;

	$data=explode('$#$',$item->fields['value']);
	if ($field->fields['name']=="check_command")
		$data=array($data[0]);

	$id=$item->fields['plugin_nagios_objects_id'];
	$datx['itemtype']=$itemtype;
	$datx['owner_id']=$item->fields['plugin_nagios_objects_id'];
        
        foreach ($data as $item_id) {  
		$datx['items_id']=$item_id;
		self::recurse_add_link_from_array($id,0,$datx);
	}

 }

  /* manage fields */
 static function recurse_add_link_from_array($id,$parent_id,$input) {
  global $DB;


   $DB->query("insert into glpi_plugin_nagios_links (plugin_nagios_objects_id,parent_id,owner_id,itemtype,items_id) values  ".
              "($id,$parent_id,{$input['owner_id']},'{$input['itemtype']}',{$input['items_id']})");
    
   $query2="select id from glpi_plugin_nagios_objects where parent_objects_id='$id'";
   foreach($DB->request($query2) as $data) {
        self::recurse_add_link_from_array($data['id'],$id,$input);

   }

 }


 /* manage fields */
 static function recurse_add_link_from_sql($id,$parent_id,$where) {
  global $DB;
   //echo "insert into glpi_plugin_nagios_links (plugin_nagios_objects_id,parent_id,owner_id,itemtype,items_id)  ".
              "(select $id,$parent_id,owner_id,itemtype,items_id from glpi_plugin_nagios_links where $where)";


   $DB->query("insert into glpi_plugin_nagios_links (plugin_nagios_objects_id,parent_id,owner_id,itemtype,items_id)  ".
	      "(select $id,$parent_id,owner_id,itemtype,items_id from glpi_plugin_nagios_links where $where)");	
   $query2="select id from glpi_plugin_nagios_objects where parent_objects_id='$id'";
   foreach($DB->request($query2) as $data) {
	self::recurse_add_link_from_sql($data['id'],$id,$where);

   }
 
 }



}

