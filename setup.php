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


function plugin_init_nagios() {
  global $PLUGIN_HOOKS,$CFG_GLPI;


  $CFG_GLPI['glpiitemtypetables']['glpi_plugin_nagios_hostgroups_view']="PluginNagiosHostGroup";
  $CFG_GLPI['glpiitemtypetables']['glpi_plugin_nagios_hosts_view']="PluginNagiosHost";
  $CFG_GLPI['glpiitemtypetables']['glpi_plugin_nagios_roles_view']="PluginNagiosRole";
  $CFG_GLPI['glpiitemtypetables']['glpi_plugin_nagios_services_view']="PluginNagiosService";
  $CFG_GLPI['glpiitemtypetables']['glpi_plugin_nagios_servicegroups_view']="PluginNagiosServiceGroup";


  $PLUGIN_HOOKS['csrf_compliant']['nagios'] = true;
  $PLUGIN_HOOKS['add_javascript']['nagios'][] = 'nagios.js';
  $PLUGIN_HOOKS['add_css']['nagios'][] = 'nagios.css';
  $PLUGIN_HOOKS['use_massive_action']['nagios'] = 1;
  $nagios_object=array('PluginNagiosHost',
                       'PluginNagiosHostGroup',
                       'PluginNagiosServiceGroup',
                       'PluginNagiosService',
                       'PluginNagiosRole',
                       );

  Plugin::registerClass('PluginNagiosObjectValue');
  Plugin::registerClass('PluginNagiosObject');
  Plugin::registerClass('PluginNagiosField');
  Plugin::registerClass('PluginNagiosMacro');
  Plugin::registerClass('PluginNagiosCommand');
  Plugin::registerClass('PluginNagiosSatellite');
  Plugin::registerClass('PluginNagiosLink');
  Plugin::registerClass('PluginNagiosLinkInjection');
  Plugin::registerClass('PluginNagiosScript');
  Plugin::registerClass('PluginNagiosEntity', array('addtabon' => array('Entity')));
  Plugin::registerClass('PluginNagiosCalendar', array('addtabon' => array('Calendar')));
  Plugin::registerClass('PluginNagiosUser', array('addtabon' => array('User')));
  Plugin::registerClass('PluginNagiosHost', array('addtabon' => array('Computer','Printer','NetworkEquipment' )));

  $plugin = new Plugin();
  if ($plugin->isActivated("nagios")) {
    $CFG_GLPI['glpiitemtypetables']['glpi_plugin_nagios_view_computer_hostgroups']="PluginNagiosObject";
    $CFG_GLPI['glpiitemtypetables']['glpi_plugin_nagios_hosts']="PluginNagiosHost";

    foreach ( $nagios_object as $idx => $val) {
	$CFG_GLPI['glpitablesitemtype'][$val] = "glpi_plugin_nagios_objects";
    }   


    if (Session::haveRight("plugin_nagios_admin",READ)) {
      $PLUGIN_HOOKS['menu_toadd']['PluginNagiosSatellite'] = array ('plugins' => "PluginNagiosSatellite");
      $PLUGIN_HOOKS['menu_toadd']['PluginNagiosScript']    = array ('plugins' => "PluginNagiosScript");
    }

    if (Session::haveRight("plugin_nagios",array(READ,CREATE,UPDATE,PURGE))) {
      $PLUGIN_HOOKS['menu_toadd']['PluginNagiosCommand']=array('plugins' => "PluginNagiosCommand");

      reset($nagios_object);
      foreach ( $nagios_object as $idx => $val) {
        $PLUGIN_HOOKS['menu_toadd'][$val]=array('plugins' => $val ) ;
      }

    }



    $PLUGIN_HOOKS['item_add']['PluginNagios']    = array(
                                                     'Computer'         => array('PluginNagiosHost'  , 'item_add'),
                                                     'Printer'         => array('PluginNagiosHost'  , 'item_add'),
						     'NetworkEquipment'         => array('PluginNagiosHost'  , 'item_add'),
						     'IPAddress'        => array('PluginNagiosHost'  , 'network_update')
                                                     );
    $PLUGIN_HOOKS['item_purge']['PluginNagios'] = array('Computer'         => array('PluginNagiosHost'  , 'item_purge'),
							 'Printer'         => array('PluginNagiosHost'  , 'item_purge'),
							 'NetworkEquipment'         => array('PluginNagiosHost'  , 'item_purge'),
							 'IPAddress'        => array('PluginNagiosHost'  , 'network_update')
                                                        );


    $PLUGIN_HOOKS['item_update']['PluginNagios'] = array('Computer'         => array('PluginNagiosHost'  , 'item_update'),
						         'Printer'         => array('PluginNagiosHost'  , 'item_update'),
							 'NetworkEquipment'         => array('PluginNagiosHost'  , 'item_update'),
							 'IPAddress'        => array('PluginNagiosHost'  , 'network_update')
                                                        
						 );

    /* record hook for update link table */
    reset($nagios_object);
    foreach($nagios_object as $no) {    
      $PLUGIN_HOOKS['item_add']['PluginNagios'][$no]   = array('PluginNagiosLink', 'item_add');
      $PLUGIN_HOOKS['item_update']['PluginNagios'][$no]= array('PluginNagiosLink', 'item_update');
      $PLUGIN_HOOKS['item_purge']['PluginNagios'][$no]= array('PluginNagiosLink', 'item_purge');
    }

   $PLUGIN_HOOKS['item_add']['PluginNagios']['PluginNagiosObject']   = array('PluginNagiosLink', 'item_add');
   $PLUGIN_HOOKS['item_update']['PluginNagios']['PluginNagiosObject']= array('PluginNagiosLink', 'item_update');
   $PLUGIN_HOOKS['item_purge']['PluginNagios']['PluginNagiosObject']= array('PluginNagiosLink', 'item_purge');



    $PLUGIN_HOOKS['item_add']['PluginNagios']['PluginNagiosObjectLink']= array('PluginNagiosLink', 'link_item_add');
    $PLUGIN_HOOKS['item_purge']['PluginNagios']['PluginNagiosObjectLink']= array('PluginNagiosLink', 'link_item_purge');

    $PLUGIN_HOOKS['item_add']['PluginNagios']['PluginNagiosObjectValue']   = array('PluginNagiosLink', 'values_item_add');
    $PLUGIN_HOOKS['item_purge']['PluginNagios']['PluginNagiosObjectValue'] = array('PluginNagiosLink', 'values_item_purge');
    $PLUGIN_HOOKS['item_update']['PluginNagios']['PluginNagiosObjectValue'] = array('PluginNagiosLink', 'values_item_update');


    $PLUGIN_HOOKS['plugin_datainjection_populate']['PluginNagiosLinkInjection'] = array("PluginNagiosLinkInjection","data_populate");


  }
  Plugin::registerClass('PluginNagiosProfile',  array('addtabon' => array('Profile')));  
}

function plugin_version_nagios() {
  return array(
                'name'                          =>"nagios",
                'version'                       =>"0.0.11",
                'author'                         =>"fabien.granjon@simia.fr",
                'license'                       =>"GPLv2+",
                'homepage'                      =>"http://simia.fr",
                'minGlpiVersion'        =>"0.90"
  );
}

function plugin_nagios_check_prerequisites() {

  if (GLPI_VERSION >= 0.90 )
    return true;

  echo "A besoin de la version 0.90";
    return false;

}

function plugin_nagios_check_config() {
        if (true) {
                return true;
        } else {
                return false;
        }
}



?>
