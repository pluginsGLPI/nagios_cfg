<?php
/*
 -------------------------------------------------------------------------
 Printercounters plugin for GLPI
 Copyright (C) 2014 by the Printercounters Development Team.
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Printercounters.

 Printercounters is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Printercounters is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Printercounters. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------  */

include ("../../../inc/includes.php");

Session::checkLoginUser();
Html::header_nocache();

switch($_POST['action']){
   case 'getFieldForm':
      getFieldForm();
      break;
   case 'saveObjectValues':
      saveObjectValues();
      break;
   case 'run_export':
      run_export();
      break;
   case 'get_check_command_form':
	   get_check_command_form();
      break;
   case 'disabled_service':
	   disabled_service();
	   break; 
   default:
     break;

}

function get_check_command_form() {
   global $_POST;

   if (!$_POST['cmd_id'])
	die(" ") ;

   $co=new PluginNagiosCommand;
   $co->getFromDB($_POST['cmd_id']);
   $nbargs=$co->getNbArgs();
   $params['size']=50;
   for ($i=0;$i<$nbargs;$i++)
       echo "&nbsp;ARG$i:&nbsp;".Html::input("field_".$_POST['field_id'].'[]',$params)."<br>";
     
   

}


function disabled_service() {
 global $_POST;

 if (!$_POST['id'])
        die("Service id missing");

 $service=new PluginNagiosService();
 $service->getFromDB($_POST['id']);
 $data['id']=$_POST['id'];
 if ($_POST['disabled']=='true')
	 $_POST['disabled']=1;
 else
	 $_POST['disabled']=0;

 $data['is_disabled']=$_POST['disabled'];
 $service->update($data);

 if ($_POST['disabled'])
	 echo "Service disabled";
 else
	 echo "Service enabled";

}

function run_export() {
 global $_POST;

 if (!Session::haveRight("plugin_nagios_admin",READ))
	die("Unauthorized");
 
 if (!$_POST['id'])
	die("Poller missing");
 
 $satellite=new PluginNagiosSatellite();
 $satellite->getFromDB($_POST['id']);
 $satellite->export("/var/nagios/".str_replace(" ","_",$satellite->fields['name'])."/"); 

}

function saveObjectValues() {
  global $_POST;
 if (!$_POST['id'])
	die("500");

  $nagiosItem=new PluginNagiosObject();
  $nagiosItem->getFromDB($_POST['id']);
 
  $nagiosItem->processForm($_POST);
  

}




function getFieldForm() {
  global $_POST;

  $id=$_POST['id'];
  if (!$id)
    return "";

  
  PluginNagiosField::showInput($id,array('entity'=>$_POST['entity_id']));

}


?>
