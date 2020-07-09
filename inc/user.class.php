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


class PluginNagiosUser extends commonDBTM {

  static $rightname='plugin_nagios';

  static function getTypeName($nb=0) {
    return _n("Entity","Entities",$nb, "nagios"); 
  }

   static function getTable($classname = NULL)
  {
    return "glpi_plugin_nagios_users";
  }

 function getLinkedItems() {
         $linked_items=array();


        if ($this->fields['host_notification_period'])  {
		if ($this->fields['host_notification_period']) {
		  $o=new Calendar;
		  $o->getFromDB($this->fields['host_notification_period']);
		  $linked_items['Calendar'][$o->getID()]=$o;
	        }
        } 
        if ($this->fields['service_notification_period'] && $this->fields['service_notification_period']!=$this->fields['host_notification_period'])  {
                $o=new Calendar;
                $o->getFromDB($this->fields['service_notification_period']);
                $linked_items['Calendar'][$o->getID()]=$o;
        } 

        if ($this->fields['host_notification_commands']) {
		$command_list=explode(":",$this->fields['host_notification_commands']);
		foreach($command_list as  $command_id) {
			$o=new PluginNagiosCommand;
			$o->getFromDB($command_id);
			$linked_items['PluginNagiosCommand'][$o->getID()]=$o;
		}
	}
        if ($this->fields['service_notification_commands']) {
                $command_list=explode(":",$this->fields['service_notification_commands']);
                foreach($command_list as  $command_id) {
                        $o=new PluginNagiosCommand;
                        $o->getFromDB($command_id);
                        $linked_items['PluginNagiosCommand'][$o->getID()]=$o;
                }
        }

	return $linked_items;

 }



 function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {


    $ong=array();

    if ($item->getType() == 'User') {
      $ong[10]="Nagios";

      
    }
  
     return $ong;
  }

  function getFromUser($user_id) {
	return $this->getFromDBByQuery("WHERE users_id='$user_id' LIMIT 1");
  }



  static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

    $user=new Self;

    $user->getFromUser($item->getID());

    switch($tabnum) {

	case 10:
	  $user->getEmpty();
	  $user->getFromUser($item->getID());
	  $user->showForm($item->getID());
    	  break;
   }

    return true;
  } 


  function showForm($users_id,$options=array()) {
   

      echo "<div class='spaced'>";
      $canedit=self::canUpdate();

      if ($canedit) {
         echo "<form method='post' name='form_entity' action='".Toolbox::getItemTypeFormURL(__CLASS__)."'>";
      }

/*
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `users_id` int(11) DEFAULT NULL,
  `host_notifications_enabled` char(1) DEFAULT 0,
  `service_notifications_enabled` char(1) DEFAULT 0,
  `host_notification_period`  int(11) DEFAULT NULL,
  `service_notification_period` int(11) DEFAULT NULL,
  `host_notification_options` varchar(20) DEFAULT NULL,
  `service_notification_options` varchar(20) DEFAULT NULL,
  `host_notification_commands` int(11)  DEFAULT NULL,
  `service_notification_commands` int(11)  DEFAULT NULL,
 */
      $fields=PluginNagiosField::getFieldsByName("US",
	array('host_notifications_enabled',
	      'service_notifications_enabled',
               'host_notification_period',
               'service_notification_period',
               'host_notification_options',
               'service_notification_options',
               'host_notification_commands',
               'service_notification_commands'));

      echo Html::hidden("id",array("value"=>$this->getID()));
      echo Html::hidden("users_id",array("value"=>$users_id));
      echo "<table class='tab_cadre_fixe'>";




      foreach($fields as $field_id=>$field ) {
         echo PluginNagiosField::showInput($field_id,array("value"=>$this->fields[$field['name']]));
      }

      echo "<tr><td><input type='submit' name='save' value=\""._sx('button','Save')."\" class='submit'>";

      echo "</td></tr>";
      echo "</table>";
      Html::closeForm();
      echo "</div>";
       return true;


  }

  function showNagiosDef() {


      $user_glpi=new User;
      $user_glpi->getFromDB($this->fields['users_id']);

      $email=$user_glpi->getDefaultEmail();
      $buf="define contact {\n";
      $buf.=" contact_name ".$user_glpi->fields['name']."\n";
      if (isset($user_glpi->fields['realname']) && $user_glpi->fields['realname']) 
	      $buf.=" alias ".$user_glpi->fields['realname']."\n";
                                   
      if ($email) $buf.=" email ".$email."\n";

      if ($this->fields['host_notifications_enabled'])
	      $buf.=" host_notifications_enabled    {$this->fields['host_notifications_enabled']}\n";
      if ($this->fields['service_notifications_enabled'])
              $buf.=" service_notifications_enabled    {$this->fields['service_notifications_enabled']}\n";

      if ($this->fields['service_notification_period']) {
 	  $o=new Calendar;
          $o->getFromDB($this->fields['service_notification_period']);
          $buf.=" service_notification_period    {$o->fields['name']}\n";
      }

      if ($this->fields['host_notification_period']) {
          $o=new Calendar;
          $o->getFromDB($this->fields['host_notification_period']);
	  $buf.=" host_notification_period    {$o->fields['name']}\n";
      }

      if ($this->fields['service_notification_options'])
          $buf.=str_replace(":",","," service_notification_options  {$this->fields['service_notification_options']}\n");
      
      if ($this->fields['host_notification_options'])
          $buf.=str_replace(":",","," host_notification_options  {$this->fields['host_notification_options']}\n");
 
      if ($this->fields['host_notification_commands']) {
	      $command_list=explode(":",$this->fields['host_notification_commands']);
	      $list_cmd=array();
          foreach($command_list as  $command_id) {
             $o=new PluginNagiosCommand;
             $o->getFromDB($command_id);
               $list_cmd[]=$o->fields['name'];
	  }
	      if (count($list_cmd))
		      $buf.=" host_notification_commands ".implode(",",$list_cmd)."\n";
      }

      if ($this->fields['service_notification_commands']) {
              $command_list=explode(":",$this->fields['service_notification_commands']);
              $list_cmd=array();
          foreach($command_list as  $command_id) {
             $o=new PluginNagiosCommand;
             $o->getFromDB($command_id);
               $list_cmd[]=$o->fields['name'];
          }
              if (count($list_cmd))
                      $buf.=" service_notification_commands ".implode(",",$list_cmd)."\n";
      }
      $buf.="}\n";

      return $buf;
  }
}
?>
