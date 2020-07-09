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

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT."/inc/includes.php");


$nagios_entity=new PluginNagiosEntity();
//Save entityi
if (isset ($_POST['save'])) {
 
     $data['satellites_id']=$_POST['plugin_nagios_satellites_id'];
     $data['entities_id']=$_POST['entities_id'];
     $data['id']=$_POST['id'];

     if ($_POST['id']=='') {
        unset($_POST['id']);
	$nagios_entity->add($_POST);
     } else
        $nagios_entity->update($_POST);

     Html::back();

} else if (isset($_POST['importer'])) {
	

	$entity_id=$_POST['entity_id'];
	$otype = $_POST['otype']; // type ex Computer, Printer , Network 
	if (isset($_FILES['filename'])) {
		$fd=fopen($_FILES['filename']['tmp_name'],"r");
		while(!feof($fd)) {
			$error=0;
			$buf=trim(fgets($fd));	
			if (!$buf)
				continue;
			$data=explode(";",$buf);

			$glpi_hostname=array_shift($data);
			$nagios_templatename=array_shift($data);
			

			$glpi_host=new $otype;
			$glpi_host->getFromDBByQuery("WHERE `". $glpi_host->getTable()."`.name='$glpi_hostname' and entities_id='$entity_id'  LIMIT 1");

			if ($glpi_host->getID()<=0 )  {
				Session::addMessageAfterRedirect(sprintf(__('%1$s: %2$s'), __('Item not found'),$glpi_hostname),false,ERROR);
				continue;
			} 
			
			$nagios_host=PluginNagiosObjectLink::getHostForItem($glpi_host->getID(),$otype);
	
			if ($nagios_templatename) {

				$nagios_template=new PluginNagiosHost;
				$nagios_template->getFromDBByQuery("WHERE glpi_plugin_nagios_objects.name='$nagios_templatename' and `type`='HT' and entities_id='$entity_id' LIMIT 1");

       			        if ($nagios_template->getID()<=0) {
                                	Session::addMessageAfterRedirect(sprintf(__('%1$s: %2$s'), __('HostTemplate not found'),$nagios_templatename),false,ERROR);
					$error++;
				} else {
					$nagios_host_data=array("id"=>$nagios_host->getID(),"parent_objects_id"=>$nagios_template->getID());
					$nagios_host->update($nagios_host_data);
				}
                        }



			//first remove all role 
			PluginNagiosObjectLink::removeRole($nagios_host->getID());
	
			//set roles
			foreach($data as $idx=>$role_name) {
		

			      $role  = new PluginNagiosObject();
			      $role->getFromDBByQuery("WHERE glpi_plugin_nagios_objects.name='$role_name' and `type`='RO' and entities_id='$entity_id' LIMIT 1");
            		      //create Link  
		
			      if ($role->getID()<=0) {
				Session::addMessageAfterRedirect(sprintf(__('%1$s: %2$s'), __('SupervisionTemplate not found'),$role_name),false,ERROR);
				$error++;
				continue;
			      }
			      PluginNagiosObjectLink::addRole($nagios_host->getID(),$role->getID());


			}
		
			if (!$error)
				Session::addMessageAfterRedirect(sprintf(__('%1$s: %2$s'), __('Item updated '),$glpi_hostname));
			else
				Session::addMessageAfterRedirect(sprintf(__('%1$s: %2$s %3$s'), __('Item updated '),$glpi_hostname," with $error errors"));

		}

	}


   Html::back();

}

?>

