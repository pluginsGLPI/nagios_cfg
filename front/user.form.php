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


$nagios_user=new PluginNagiosUser();
//Save entityi
if (isset ($_POST['save'])) {
 
	$data['users_id']=$_POST['users_id'];
       
	$data['id']=$_POST['id'];

        foreach($_POST['field_id'] as $idx=>$field_id) {

		$o=new PluginNagiosField;
                $o->getFromDB($field_id);
		$field_name=$o->fields['name'];
          
		if (is_array($_POST['field_'.$field_id])) {
			$field_value=implode(":",$_POST['field_'.$field_id]);
		} else {
			if ($_POST['field_'.$field_id]=='[:EMPTY:]')
				$_POST['field_'.$field_id]='';
			$field_value=$_POST['field_'.$field_id];

		}
                $data[$field_name]=$field_value;
        }

	//var_dump($data);
	if ($_POST['id']=='') {
		unset($data['id']);
		$nagios_user->add($data);
	} else
		$nagios_user->update($data);
	Html::back();

} 

?>
