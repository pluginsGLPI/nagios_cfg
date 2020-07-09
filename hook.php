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


function vdump($txt) {
  echo "<pre>";
  var_dump($txt);
  echo "</pre>";

}


// Define Dropdown tables to be manage in GLPI :
function plugin_nagios_getDropdown() {

   $plugin = new Plugin();
   if ($plugin->isActivated("nagios"))
   	return array("PluginNagiosField"=> PluginNagiosField::getTypeName(2));   
}

// Define dropdown relations
function plugin_nagios_getDatabaseRelations() {
  $plugin = new Plugin();
  if ($plugin->isActivated("nagios"))
	return array(
			"glpi_plugin_nagios_fields" => array("glpi_plugin_nagios_objectvalues" => "plugin_nagios_fields_id"),
			"glpi_plugin_nagios_objects" => array("glpi_plugin_nagios_objectvalues" => "plugin_nagios_objects_id"),
                        "glpi_plugin_nagios_objects" => array("glpi_plugin_nagios_objects" => "parent_objects_id"),
                        "glpi_plugin_nagios_objects"  => array("glpi_plugin_nagios_objectlinks" => "plugin_nagios_objects_id"),
			"glpi_plugin_nagios_objects"  => array("glpi_plugin_nagios_macros" => "plugin_nagios_objects_id"));
                        
                        

//                        "glpi_computers"  => array( "glpi_plugin_objectlinks" => "items_id" ) );

}


function plugin_nagios_addDefaultJoin($itemtype, $ref_table,$already_link_tables) {

}


function plugin_nagios_addDefaultWhere($itemtype) {
  $plugin = new Plugin();
  if ($plugin->isActivated("nagios"))
    if (in_array($itemtype,
         array( "PluginNagiosHost",
      		"PluginNagiosHostGroup",
		"PluginNagiosService",
		"PluginNagiosServiceGroup",
		"PluginNagiosRole"
              )
        )) {
  	return "glpi_plugin_nagios_objects.`type`='".$itemtype::$nagios_type."'  AND  glpi_plugin_nagios_objects.`is_model`=1  ";

   }
}


function plugin_nagios_addLeftJoin($itemtype, $ref_table, $new_table, $linkfield, $already_link_tables) {
  $refu1=mt_rand();
  $refu2=mt_rand();
   $plugin = new Plugin();


   if ($plugin->isActivated("nagios")) {
         switch($new_table) {
          case "glpi_plugin_nagios_hostgroups_view":
		$out=" LEFT JOIN glpi_plugin_nagios_links AS glpi_plugin_nagios_links_hostgroups ON ( glpi_plugin_nagios_links_hostgroups.plugin_nagios_objects_id = $ref_table.id AND  glpi_plugin_nagios_links_hostgroups.itemtype='PluginNagiosHostGroup' )";
		$out .= " LEFT JOIN glpi_plugin_nagios_hostgroups_view ON ( glpi_plugin_nagios_hostgroups_view.id = glpi_plugin_nagios_links_hostgroups.items_id )";
		return $out;
	       break;
          case "glpi_plugin_nagios_hosts_view":
                $out=" LEFT JOIN glpi_plugin_nagios_links AS glpi_plugin_nagios_links_hosts ON ( glpi_plugin_nagios_links_hosts.plugin_nagios_objects_id = $ref_table.id AND glpi_plugin_nagios_links_hosts.itemtype='PluginNagiosHost'  )";
                $out .= " LEFT JOIN glpi_plugin_nagios_hosts_view ON ( glpi_plugin_nagios_hosts_view.id = glpi_plugin_nagios_links_hosts.items_id  )";
                return $out;
               break;
          case "glpi_plugin_nagios_roles_view":
                $out=" LEFT JOIN glpi_plugin_nagios_links AS glpi_plugin_nagios_links_roles ON ( glpi_plugin_nagios_links_roles.plugin_nagios_objects_id = $ref_table.id AND glpi_plugin_nagios_links_roles.itemtype='PluginNagiosRole'  )";
                $out .= " LEFT JOIN glpi_plugin_nagios_roles_view ON ( glpi_plugin_nagios_roles_view.id = glpi_plugin_nagios_links_roles.items_id )";
                return $out;
               break;
          case "glpi_plugin_nagios_services_view":
                $out=" LEFT JOIN glpi_plugin_nagios_links AS glpi_plugin_nagios_links_services ON ( glpi_plugin_nagios_links_services.plugin_nagios_objects_id = $ref_table.id AND glpi_plugin_nagios_links_services.itemtype='PluginNagiosService'  )";
                $out .= " LEFT JOIN glpi_plugin_nagios_services_view ON ( glpi_plugin_nagios_services_view.id = glpi_plugin_nagios_links_services.items_id )";
                return $out;
               break;
          case "glpi_plugin_nagios_servicegroups_view":
                $out=" LEFT JOIN glpi_plugin_nagios_links AS glpi_plugin_nagios_links_servicegroups ON ( glpi_plugin_nagios_links_servicegroups.plugin_nagios_objects_id = $ref_table.id AND  glpi_plugin_nagios_links_servicegroups.itemtype='PluginNagiosServiceGroup' )";
                $out .= " LEFT JOIN glpi_plugin_nagios_servicegroups_view ON ( glpi_plugin_nagios_servicegroups_view.id = glpi_plugin_nagios_links_servicegroups.items_id )";
                return $out;
               break;


	 }


    }

 
}

function plugin_nagios_MassiveActions($itemtype)
{
   switch ($itemtype) {
      case 'Computer' :
      case 'Printer':
      case 'NetworkEquipment':
         if (Session::haveRight("plugin_nagios",UPDATE))
            return array(// Specific one
               'PluginNagiosHost' . MassiveAction::CLASS_ACTION_SEPARATOR . "plugin_nagios_host_enabledisable" => __('Nagios - Enable/Disable Monitoring'),
               'PluginNagiosHost' . MassiveAction::CLASS_ACTION_SEPARATOR . "plugin_nagios_host_setparent" => __('Nagios - Set As Child Of'),
               'PluginNagiosHost' . MassiveAction::CLASS_ACTION_SEPARATOR . "plugin_nagios_host_setfield" => __('Nagios - Set Fields'),
	       'PluginNagiosHost' . MassiveAction::CLASS_ACTION_SEPARATOR . "plugin_nagios_host_addfield" => __('Nagios - Add Field'),
               'PluginNagiosHost' . MassiveAction::CLASS_ACTION_SEPARATOR . "plugin_nagios_host_removefield" => __('Nagios - Remove Field'),
               'PluginNagiosHost' . MassiveAction::CLASS_ACTION_SEPARATOR . "plugin_nagios_host_setrole" => __('Nagios - Add/Remove Supervision Template'),
	       'PluginNagiosHost' . MassiveAction::CLASS_ACTION_SEPARATOR . "plugin_nagios_host_import" => __('Nagios - Import'));
    	 break;
      default:
         return array();  
   }
}

function plugin_nagios_install()  {
  global $DB;

  if (!$DB->tableExists("glpi_plugin_nagios_configs")) { // not installed
      $DB->runFile(GLPI_ROOT . '/plugins/nagios/sql/empty-0.0.1.sql');
  }

  $migration = new Migration(100);

  $migration->executeMigration();


    include_once(GLPI_ROOT."/plugins/nagios/inc/object.class.php");
    include_once(GLPI_ROOT."/plugins/nagios/inc/host.class.php");
    include_once(GLPI_ROOT."/plugins/nagios/inc/objectlink.class.php");
    include_once(GLPI_ROOT."/plugins/nagios/inc/field.class.php");
    include_once(GLPI_ROOT."/plugins/nagios/inc/objectvalue.class.php");


/*    $field_addr=PluginNagiosField::getFieldsByName('HT','address');




    $query="SELECT item.id,item.entities_id,item.name,(SELECT ip.name as ipaddr 
	                  			       FROM glpi_computers e 
	    						LEFT JOIN glpi_networkports np ON (np.items_id=e.id AND np.itemtype='Computer')
							LEFT JOIN glpi_networknames nn ON (nn.items_id=np.id)
							LEFT JOIN glpi_ipaddresses ip ON (nn.ID=ip.items_id)
						       WHERE e.id=item.id order by np.logical_number DESC LIMIT 1 ) ipaddr
            FROM glpi_computers item";



    foreach ($DB->request($query) as $data) {
   
         $nagios_host=new PluginNagiosHost();
         $dat=array();
         $dat['entities_id']=$data['entities_id'];
         $dat['type']='HT';
         $dat['name']=$data['name'];
         $dat['is_model']=0;
         $dat['is_disabled']=1;
         $nagios_host->add($dat);

         $dat=array();
         $nagios_link=new PluginNagiosObjectLink();
         $dat['plugin_nagios_objects_id']=$nagios_host->getID();
         $dat['items_id']=$data['id'];
         $dat['itemtype']='Computer';
         $nagios_link->add($dat);

	 if (isset($data['ipaddr']) && $data['ipaddr']) {

		$ov=new PluginNagiosObjectValue();
		$dat=array();
		$dat['plugin_nagios_objects_id']=$nagios_host->getID();
		$dat['plugin_nagios_fields_id']=$field_addr->getID();
		$dat['value']=$data['ipaddr'];
		$ov->add($dat);
	 }

    }


    $query="SELECT item.id,item.entities_id,item.name,(SELECT ip.name as ipaddr 
                                                       FROM glpi_printers e 
                                                        LEFT JOIN glpi_networkports np ON (np.items_id=e.id AND np.itemtype='Printer')
                                                        LEFT JOIN glpi_networknames nn ON (nn.items_id=np.id)
                                                        LEFT JOIN glpi_ipaddresses ip ON (nn.ID=ip.items_id)
                                                       WHERE e.id=item.id order by np.logical_number DESC LIMIT 1 ) ipaddr
            FROM glpi_printers item";

    foreach ($DB->request($query) as $data) {
         $nagios_host=new PluginNagiosHost();
         $dat=array();
         $dat['entities_id']=$data['entities_id'];
         $dat['type']='HT';
         $dat['name']=$data['name'];
         $dat['is_model']=0;
         $dat['is_disabled']=1;
         $nagios_host->add($dat);

         $dat=array();
         $nagios_link=new PluginNagiosObjectLink();
         $dat['plugin_nagios_objects_id']=$nagios_host->getID();
         $dat['items_id']=$data['id'];
         $dat['itemtype']='Printer';
         $nagios_link->add($dat);

         if (isset($data['ipaddr']) && $data['ipaddr']) {

                $ov=new PluginNagiosObjectValue();
                $dat=array();
                $dat['plugin_nagios_objects_id']=$nagios_host->getID();
                $dat['plugin_nagios_fields_id']=$field_addr->getID();
                $dat['value']=$data['ipaddr'];
                $ov->add($dat);
         }



    }


    $query="SELECT item.id,item.entities_id,item.name,(SELECT ip.name as ipaddr 
                                                       FROM glpi_networkequipments e 
                                                        LEFT JOIN glpi_networkports np ON (np.items_id=e.id AND np.itemtype='NetworkEquipment')
                                                        LEFT JOIN glpi_networknames nn ON (nn.items_id=np.id)
                                                        LEFT JOIN glpi_ipaddresses ip ON (nn.ID=ip.items_id)
                                                       WHERE e.id=item.id order by np.logical_number DESC LIMIT 1 ) ipaddr
            FROM glpi_networkequipments item";



    foreach ($DB->request($query) as $data) {
         $nagios_host=new PluginNagiosHost();
         $dat=array();
         $dat['entities_id']=$data['entities_id'];
         $dat['type']='HT';
         $dat['name']=$data['name'];
         $dat['is_model']=0;
         $dat['is_disabled']=1;
         $nagios_host->add($dat);

         $dat=array();
         $nagios_link=new PluginNagiosObjectLink();
         $dat['plugin_nagios_objects_id']=$nagios_host->getID();
         $dat['items_id']=$data['id'];
         $dat['itemtype']='NetworkEquipment';
         $nagios_link->add($dat);
	
         if (isset($data['ipaddr']) && $data['ipaddr']) {

                $ov=new PluginNagiosObjectValue();
                $dat=array();
                $dat['plugin_nagios_objects_id']=$nagios_host->getID();
                $dat['plugin_nagios_fields_id']=$field_addr->getID();
                $dat['value']=$data['ipaddr'];
                $ov->add($dat);
         }



    }




    if (!$DB->tableExists("glpi_plugin_nagios_profiles")) {
    // requete de création de la table    
    $query = "CREATE TABLE `glpi_plugin_nagios_profiles` (
               `id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
               `right` char(1) collate utf8_unicode_ci default NULL,
               PRIMARY KEY  (`id`)
             ) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";

    $DB->queryOrDie($query, $DB->error());

    //creation du premier accès nécessaire lors de l'installation du plugin
    include_once(GLPI_ROOT."/plugins/nagios/inc/profile.class.php");
    PluginNagiosProfile::createAdminAccess($_SESSION['glpiactiveprofile']['id']);
  }
*/
    if (!$DB->fieldExists("glpi_plugin_nagios_macros", "comments", false)) {
      $query = "ALTER TABLE `glpi_plugin_nagios_macros`
                ADD `comments` TEXT NULL ";
      $DB->queryOrDie($query, $DB->error());
    }

    if (!$DB->fieldExists("glpi_plugin_nagios_commands", "is_global", false)) {
      $query = "ALTER TABLE `glpi_plugin_nagios_commands`
              ADD `is_global` TINYINT(1) NOT NULL DEFAULT '0' ";
              $DB->queryOrDie($query, $DB->error());
    }




  return true;
}


function plugin_nagios_getAddSearchOptions($itemtype)
{

   $sopt = array();

   if (in_array($itemtype,array('Computer','NetworkEquipment','Printer')) ) {
      if (Session::haveRight("plugin_nagios", READ)) {

         $sopt[10002]['table'] = 'glpi_plugin_nagios_objects';
         $sopt[10002]['field'] = 'is_disabled';
         $sopt[10002]['name'] = _('Nagios - Is disabled');
         $sopt[10002]['datatype'] = 'number';
         $sopt[10002]['massiveaction'] = false;
         $sopt[10002]['forcegroupby'] = true;
         $sopt[10002]['joinparams']  = array('beforejoin' => 
                                                array ('table'=>'glpi_plugin_nagios_objectlinks',
                                                       'joinparams' => array(
                                                                     'jointype' => 'child','linkfield'=>'items_id',
                                                                     'condition'=>" AND NEWTABLE.itemtype='$itemtype' ")));
//         $sopt[10003]['joinparams']   = array('jointype' => 'child','linkfield'=>'id',
  //                                          'beforejoin'  => array('table'   => 'glpi_plugin_nagios_objectlinks',
    //                                        'joinparams' => array('jointype'=>'itemtype_item')));


         $sopt[10006]['table']     = 'glpi_plugin_nagios_objects';
         $sopt[10006]['field']     = 'name';
         $sopt[10006]['name']      = __('Supervision Templates');
         $sopt[10006]['linkfield'] = 'items_id';
         $sopt[10006]['massiveaction'] = false;
         $sopt[10006]['forcegroupby']         = true;
         $sopt[10006]['datatype']             = 'itemlink';
         $sopt[10006]['joinparams']         = array(
               'beforejoin'  => array(
		array('table'   => 'glpi_plugin_nagios_objectlinks','joinparams' => array('jointype'=>'itemtype_item')),
                array('table'   => 'glpi_plugin_nagios_objects', 'joinparams' => array('linkfield'=>'plugin_nagios_objects_id')),
                array('table'   => 'glpi_plugin_nagios_links',      'joinparams' => array('jointype'=>'child','linkfield'=>'plugin_nagios_objects_id','condition'=>" AND NEWTABLE.itemtype='PluginNagiosRole'"))));




         $sopt[10007]['table']     = 'glpi_plugin_nagios_objects';
         $sopt[10007]['field']     = 'name';
         $sopt[10007]['name']      = __('Hostgroups');
         $sopt[10007]['linkfield'] = 'items_id';
         $sopt[10007]['massiveaction'] = false;
         $sopt[10007]['forcegroupby']         = true;
         $sopt[10007]['datatype']             = 'itemlink';
         $sopt[10007]['joinparams']         = array(
               'beforejoin'  => array(
                array('table'   => 'glpi_plugin_nagios_objectlinks','joinparams' => array('jointype'=>'itemtype_item')),
		array('table'   => 'glpi_plugin_nagios_objects', 'joinparams' => array('linkfield'=>'plugin_nagios_objects_id')),
                array('table'   => 'glpi_plugin_nagios_links',      'joinparams' => array('jointype'=>'child','linkfield'=>'plugin_nagios_objects_id','condition'=>" AND NEWTABLE.itemtype='PluginNagiosHostgroup'"))));

//'jointype'=>'child','linkfield'=>'plugin_nagios_objects_id','condition'=>" AND NEWTABLE.itemtype='PluginNagiosHostgroup'"), 


// array('table'   => 'glpi_plugin_nagios_links',      'joinparams' => array('jointype'=>'child','linkfield'=>'plugin_nagios_objects_id','condition'=>" AND NEWTABLE.itemtype='PluginNagiosHostgroup'"))


         $sopt[10008]['table']     = 'glpi_plugin_nagios_objects';
         $sopt[10008]['field']     = 'name';
         $sopt[10008]['name']      = __('HostTemplates');
         $sopt[10008]['linkfield'] = 'items_id';
         $sopt[10008]['massiveaction'] = false;
         $sopt[10008]['forcegroupby']         = true;
         $sopt[10008]['datatype']             = 'itemlink';
         $sopt[10008]['joinparams']         = array(
               'beforejoin'  => array(
		array('table'   => 'glpi_plugin_nagios_objectlinks','joinparams' => array('jointype'=>'itemtype_item')),
                array('table'   => 'glpi_plugin_nagios_objects', 'joinparams' => array('linkfield'=>'plugin_nagios_objects_id')),
                array('table'   => 'glpi_plugin_nagios_links',      'joinparams' => array('jointype'=>'child','linkfield'=>'plugin_nagios_objects_id','condition'=>" AND NEWTABLE.itemtype='PluginNagiosHost'"))));






         $sopt[10003]['table'] = 'glpi_plugin_nagios_objects';
         $sopt[10003]['field'] = 'name';
         $sopt[10003]['name'] = _('Nagios - Primary Template');
         $sopt[10003]['massiveaction'] = false;
         $sopt[10003]['forcegroupby'] = true;
         $sopt[10003]['linkfield']='parent_objects_id';
         $sopt[10003]['datatype']        = 'itemlink';
         $sopt[10003]['itemtype']        = 'PluginNagiosHost';
         $sopt[10003]['joinparams']  = array('jointype'=>'item', 
                                             'beforejoin' =>  
                                                array( 'table'=>'glpi_plugin_nagios_objects','jointype'=>'item_item',
                                                       'joinparams' => array( 'linkfield'=>'parent_objects_id',
                                                'beforejoin' =>
                                                array ('table'=>'glpi_plugin_nagios_objectlinks',
                                                       'joinparams' => array(
                                                                     'jointype' => 'child','linkfield'=>'items_id',
                                                                     'condition'=>" AND NEWTABLE.itemtype='$itemtype' ")))));

 //        $sopt[10003]['joinparams']      = array('jointype' => 'item',
 //                                           'beforejoin'  => array('table'   => 'glpi_plugin_nagios_objects',
   //                                         'joinparams' => array('jointype'=>'child','linkfield'=>'parent_objects_id')));


         

      }
      return $sopt;
   } 

}




function plugin_nagios_uninstall() {
  global $DB;

  $tables = array( 
              "glpi_plugin_nagios_commands",
	      "glpi_plugin_nagios_calendars",
              "glpi_plugin_nagios_configs",
              "glpi_plugin_nagios_entities",
              "glpi_plugin_nagios_fields",
              "glpi_plugin_nagios_macros",
              "glpi_plugin_nagios_objectlinks",
              "glpi_plugin_nagios_objects",
              "glpi_plugin_nagios_objectvalues",
              "glpi_plugin_nagios_profiles",
              "glpi_plugin_nagios_satellites",
	      "glpi_plugin_nagios_links",
	      "glpi_plugin_nagios_users",
              "glpi_plugin_nagios_scripts");


  $views = array(
		"glpi_plugin_nagios_hostgroups_view",
		"glpi_plugin_nagios_hosts_view",
		"glpi_plugin_nagios_roles_view",
		"glpi_plugin_nagios_servicegroups_view",
		"glpi_plugin_nagios_services_view" );


  foreach($tables as $table) {
    $DB->query("DROP TABLE IF EXISTS `$table`;");
  }



  foreach($views as $view) {
    $DB->query("DROP VIEW IF EXISTS `$view`;");
  }


  return true;

}

