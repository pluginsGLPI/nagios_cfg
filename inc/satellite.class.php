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


class PluginNagiosSatellite extends commonDropdown {

  static $rightname='plugin_nagios_admin';

  static function canCreate() {
    return Session::haveRight(self::$rightname, 1);
  }

  static function canUpdate() {
    return Session::haveRight(self::$rightname, 1);
  }
  

  static function canPurge() {
    return Session::haveRight(self::$rightname,1);
  }

  function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

    $ong=array();

    if ($item instanceof PluginNagiosSatellite  ) {

      $ong[10] =_("Export Tools");
      $ong[11] =_("Resource.cfg");
      $ong[12] =_("Nagios.cfg");
      return $ong;
    }
    return '';
   }

    static function getMenuName()
  {
    return static::getTypeName(2);
  }



   static function getMenuContent() {
   
    
    $menu=array();
    $menu['title']="Nagios-Satellite";
    $menu['page']=PluginNagiosSatellite::getSearchURL(false);
    $menu['links']['add']=PluginNagiosSatellite::getFormURL(false);
    $menu['links']['search']=PluginNagiosSatellite::getSearchURL(false);

    return $menu;
/*
     return array("title"=>"Nagios - Satellites",'page'=>"/plugins/nagios/front/satellite.php",
            'links'=>array('search'=> "/plugins/nagios/front/satellite.php",
                           'add'   => "/plugins/nagios/front/satellite.form.php"  ) );
*/ 
  }


   function defineTabs($options=array()) {
      $ong = array();
      $this->addDefaultFormTab($ong);
      $this->addStandardTab(get_class($this), $ong,$options);
      $this->addStandardTab('Log',$ong, $options);
      $ong['no_all_tab']=true;
      return $ong;
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
     if (!$item instanceof PluginNagiosSatellite) 
	return ;

     switch ($tabnum) {
       case 10:
         static::displayExportTools($item);
         break;
       case 11:
         static::displayFileResource($item);
         break;
       case 12:
         static::displayFileNagios($item);
         break;
       default:
         break;
     }
   }


   static function displayFileResource($item) {
	   $options=array();
	   $rand=mt_rand();
	   echo "<form method='post' name='formSatellite_$rand'  action='".Toolbox::getItemTypeFormURL('PluginNagiosSatellite')."'>";
	   echo "<input type='hidden' name='id' value='".$item->getId()."'/>";
	   echo "<input type='hidden' name='name' value='".$item->fields['name']."'/>";
	   echo "<div style='text-align:left'>";
	   echo "<h3>resource.cfg</h3>";
	   echo "<textarea data-autoresize class='nagios' name='file_resource' cols=150 rows='40'>".$item->fields['file_resource']."</textarea>";

	   echo "</div>";

	   echo "<input type='submit' name='update' value='".__('Update')."' class='submit'>";

	   Html::closeForm(); 

	   return true;

   }

   static function displayFileNagios($item) {
	   $options=array();
	   $rand=mt_rand();
	   echo "<form method='post' name='formSatellite_$rand'  action='".Toolbox::getItemTypeFormURL('PluginNagiosSatellite')."'>";
	   echo "<input type='hidden' name='id' value='".$item->getId()."'/>";
	   echo "<input type='hidden' name='name' value='".$item->fields['name']."'/>";
	   echo "<div style='text-align:left'>";
	   echo "<h3>Nagios.cfg</h3>";
	   echo "<textarea data-autoresize class='nagios' name='file_nagios' cols=150 rows='50'>".$item->fields['file_nagios']."</textarea>";

	   echo "</div>";

	   echo "<input type='submit' name='update' value='".__('Update')."' class='submit'>";

	   Html::closeForm();

	   return true;

   }

   static function displayExportTools($item) {
	   global $CFG_GLPI; 

	   $js_options=array('root_doc' => $CFG_GLPI['root_doc'],'entity_id'=>0);

	   echo "<script type='text/javascript'>";
	   echo "var nagios = $(document).nagios(".json_encode($js_options).");";
	   echo "</script>";

	   $onclick="nagios.run_export(".$item->getID().",'div_result');";


	   echo "<input type=button class='submit' value='"._('Run Export')."' onclick=\"$onclick\"/>";
	   echo "<div class='nagios-apercu' style='display:none;margin-top:5px' id='div_result' >";

	   //  $item->export("/tmp/nagios/".str_replace(" ","_",$item->fields['name'])."/");   
	   echo "</div>";
	   echo "<div class='spaced'></div>";
   }


   static function getTypeName($nb=0) {
	   return "Satellites";
   }

   function showForm($ID,$options=array()) {

	   $this->initForm($ID, $options);
	   $this->showFormHeader($options);
	   echo "<tr class='tab_bg_1'>";
	   echo " <td>" . __('Name') ."*</td>";
	   echo " <td>";
	   echo Html::hidden("id"  ,array('value'=>$this->fields['id']));

	   echo Html::input("name",array('value'=>$this->fields['name']));
	   echo " </td>";
	   echo "</tr>";
	   echo "<tr>";
	   echo " <td>".__('Description')."</td>";
	   echo " <td>".Html::input("desc",array('size'=>125, 'value'=>$this->fields['desc']))."</td>";
	   echo "</tr>";

	   echo "<tr>";
	   echo " <td>".__('ipaddr')."*</td>";
	   echo " <td>".Html::input("ipaddr",array('size'=>16,'value'=>$this->fields['ipaddr']))."</td>";
	   echo "</tr>";


	   echo "<tr>";
	   echo "<td>".__('Before script')."*</td>";
	   echo "<td colspan='3'>";
	   PluginNagiosScript::Dropdown(array('name'=>'before_scripts_id','value'=>$this->fields['before_scripts_id']));
	   echo " </td>";
	   echo "</tr>";


	   echo "<tr>";
	   echo "<td>".__('After script')."*</td>";
	   echo "<td colspan='3'>";
	   PluginNagiosScript::Dropdown(array('name'=>'after_scripts_id', 'value'=>$this->fields['after_scripts_id']));
	   echo " </td>";
	   echo "</tr>";

	   echo "<tr>";
	   echo "<td>".__('Arguments')."</td>";
	   echo "<td colspan='3'>";
	   echo Html::input("args",array('size'=>150,'value'=>$this->fields['args']));
	   echo " </td>";
	   echo "</tr>";

	   $this->showFormButtons($options);

	   return true;
   }


   function getSearchOptions() {
	   $tab = array();
	   $tab['common'] = static::getTypeName();

	   $tab[1]['table']     = 'glpi_plugin_nagios_satellites';
	   $tab[1]['field']     = 'name';
	   $tab[1]['name']      = __('Name');
	   $tab[1]['datatype']        = 'itemlink';
	   $tab[1]['itemlink_type'] = 'PluginNagiosSatellite';


	   $tab[2]['table']     = 'glpi_plugin_nagios_satellites';
	   $tab[2]['field']     = 'desc';
	   $tab[2]['name']      = __('Description');

	   $tab[3]['table']     = 'glpi_plugin_nagios_satellites';
	   $tab[3]['field']     = 'ipaddr';
	   $tab[3]['name']      = __('ipaddr');


	   $tab[4]['table']     = 'glpi_plugin_nagios_satellites';
	   $tab[4]['field']     = 'before_scripts_id';
	   $tab[4]['name']      = __('Before script');

	   $tab[5]['table']     = 'glpi_plugin_nagios_satellites';
	   $tab[5]['field']     = 'after_scripts_id';
	   $tab[5]['name']      = __('After script');

	   return $tab;
   }

   function export($dstdir) {
	   global $DB;

	   $all_entities=array();
	   $res_entities=array();

	   echo "<div class='nagios-apercu' style='font-family:Consolas,courier-new'>";

	   clearstatcache();
	   if (!file_exists($dstdir))  {
		   echo "<p style='color:green'>Directory $dstdir not exist</p>";

		   if (!mkdir( $dstdir, 0777, true )) {
			   echo "Can't create directory !</div>";
			   return ;
		   }
	   } 


	   @array_map('unlink', glob("$dstdir/*.cfg"));
	   @array_map('unlink', glob("$dstdir/hosts/*.cfg"));
	   @array_map('unlink', glob("$dstdir/hostgroups/*.cfg"));
	   @array_map('unlink', glob("$dstdir/services/*.cfg"));
	   @array_map('unlink', glob("$dstdir/servicegroups/*.cfg"));




	   $before_script=new PluginNagiosScript;
	   $before_script->getFromDB($this->fields['before_scripts_id']);

	   $after_script=new PluginNagiosScript;
	   $after_script->getFromDB($this->fields['after_scripts_id']);


	   if ($before_script->getID() && isset($before_script->fields['command'])) {


		   echo "<p style='color:yellow'>RUN SCRIPT</p>";

		   //construct argument
		   $args_string=$before_script->fields['args'];
		   $args_string.=$this->fields['args'];

		   $args_string=str_replace("_IPADDR_",$this->fields['ipaddr'],$args_string);
		   $args_string=str_replace("_SRCDIR_","$dstdir",$args_string);

		   echo "Execute: ".$before_script->fields['command']." $args_string<br>";

		   $output=array();
		   $errcode=0;
		   $res=exec($before_script->fields['command']." $args_string",$output,$errcode);

		   if ($errcode) {
			   echo "Execution error:$errcode\n";
		   } else {
			   foreach($output as $line) {
				   echo $line."<br>";

			   }
		   }

	   }

	   function title($txt) { echo "<p style='color:green;'>$txt</p>"; }

	   /****************************************************
	     first get all entities linked with this satellite 
	    ****************************************************/
	   $entities=PluginNagiosEntity::getEntitiesForSatellite($this->getID());
	   foreach($entities as $idx => $entity) {
		   $all_entities=array_merge($all_entities,PluginNagiosObject::getRecursiveEntities($entity['entities_id']));
		   $res_entities[]=$entity['entities_id'];
	   }
	   //$all_entities[]=0;


           /******************************************************
            *  GENRATION FICHIER DE CONF 
            *  ***************************************************/

           title("Generate nagios.cfg in $dstdir");

           $fd=fopen("$dstdir/nagios.cfg","w+");
           if (!$fd)
                   die("Can't write file $dstdir/nagios.cfg , check permission");
           fwrite($fd,htmlspecialchars_decode($this->fields['file_nagios']));
           fclose($fd);


           $global_macros="\n\n";
           foreach($res_entities as $entity_id) {
                   $macros=PluginNagiosMacro::getEntityMacro($entity_id);
                   foreach($macros as $macro_id=>$macro) {
                           $global_macros.="$NAGIOS_".$macro['name'].'='.$macro['value']."\n";
                   }
           }

           title("Generate resource.cfg in $dstdir");
           $fd=fopen("$dstdir/resource.cfg","w+");
           if (!$fd)
                   die("Can't write file $dstdir/resource.cfg , check permission");


           fwrite($fd,str_replace("\x0D","",$this->fields['file_resource']));
           fwrite($fd,$global_macros);
           fclose($fd);


	   /*******************************************************
	    * 	RECUPERATION DES HOTES
	    *  ******************************************************/
	   $host_to_export=PluginNagiosObject::getNagiosObjectToExport($res_entities);

	   $nbhost=0;
	   foreach($host_to_export as $item_list) {
		   $nbhost+=count($item_list);
	   }    
	   reset($host_to_export);

	   title("Number of items to export:".$nbhost); 

	   /******************************************************
	    *  RECUPERATION DES OBJECTS CHAINES
	    *  ***************************************************/
	   title("Estimate linked item");

	   $linked_object=array();
	   foreach ($host_to_export as $item_type=>$item_list) {
		   foreach ($item_list as $id=>$data) {
			   PluginNagiosObjectValue::get_all_objects_for_object($id,$linked_object,$all_entities,'P');
		   }
	   }

	   if (isset($linked_object['P']))
	   	$linked_object=$linked_object['P'];



	   /***************************************************
	    *  RECUPERATION DES USERS Lié aux groupe
	    *  ************************************************/

	   if(isset($linked_object['Group'])){
	    foreach($linked_object['Group'] as $group_id=>$gr) {
        $users=Group_User::getGroupUsers($group_id);
	   	foreach($users as $data) {
	   		$user = new User();
	   		if($user->getFromDB($data['id'])){
	   			$linked_object['User'][$data['id']] = $user;
	   		}
	   	}
	       }	   	
	   }






	   /***************************************************
	    *  RECUPERATION DES ITEMS LIES AUX CONTACTS
	    *  ************************************************/
	   if (isset($linked_object['User'])) {
           foreach($linked_object['User'] as $user_id=>$user) {
		$o=new PluginNagiosUser;
                if($o->getFromUser($user_id)){
                			$user_items=$o->getLinkedItems();
		if (isset($user_items['Calendar'])) 
			foreach($user_items['Calendar'] as $calendar_id=>$calendar)
				$linked_object['Calendar'][$calendar_id]=$calendar;
                if (isset($user_items['PluginNagiosCommand']))
                        foreach($user_items['PluginNagiosCommand'] as $command_id=>$command)
                                $linked_object['PluginNagiosCommand'][$command_id]=$command;
                }

           }	
           }


        //retrieve global command
        $comm = new PluginNagiosCommand();
        $data = $comm->find("is_global = 1");
        echo "&nbsp;&nbsp; GLOBAL PluginNagiosCommand => ".count($data)."<br>";
        foreach ($data as $key => $value) {
         	$command_get = new PluginNagiosCommand();
          	$command_get->getFromDB($key);

          	$linked_object['PluginNagiosCommand'][$key] = $command_get;
        }

	   
	   foreach($linked_object as $classp => $dat) 
		   echo "&nbsp;&nbsp; $classp => ".count($dat)."<br>";

	   /**********************************************************
	    * EXPORT DES ITEMS
	    * ********************************************************/    
	   if (!file_exists("$dstdir/hosts/")) {
		   mkdir("$dstdir/hosts/");
	   } 

	   foreach( $host_to_export  as $item_type => $item_list ) {

		   $count=0;

		   title("Export $item_type:");

		   $str_errors=array();
		   $str_infos=array();

		   foreach( $item_list as $item_id=>$item)  {
			   if ($item->getID()<0)
				   echo "Item not found $item_type::$item_id<br>";
			   else
				   echo "Export $item_type::{$item->fields['name']}::{$item->getID()}<br>";

			   /*$fd=fopen("$dstdir/hosts/".strtolower(str_replace(" ","-",$item->fields['name'])).".cfg","a+");
			     fwrite($fd,$item->showNagiosDef());
			     fclose($fd);*/
		   } 

	   }

	   title("Export Linked items");

	   foreach( $linked_object as $item_type=>$item_list) {
		   switch($item_type) {
			   case "PluginNagiosHost":
				   $prefix='hosts';$filename='';
				   break;
			   case "PluginNagiosHostGroup":
				   $prefix='hostgroups';$filename='';
				   break;
			   case "PluginNagiosCommand":
				   $prefix='';$filename='commands.cfg';
				   break;  
			   case "PluginNagiosServiceGroup":
				   $prefix='';$filename='service_group.cfg';
				   break;
			   case "Calendar":
				   $prefix='';$filename='timeperiod.cfg';
				   break;
			   case "PluginNagiosService":
				   $prefix='services';$filename='';
				   break;
			   case "User":
				   $prefix='';$filename='contacts.cfg';
				   break;
			   case "Group":
				   $prefix='';$filename='contact_groups.cfg';
				   break; 
		   }


		   if (!file_exists("$dstdir/$prefix/") && $prefix) {
			   mkdir("$dstdir/$prefix/");
		   }



		   foreach($item_list as $item_id => $item ) {

			   echo "Export $item_type::{$item->fields['name']}<br>";
		       //echo "Export $item_type::{$item->fields['name']} ({$item_id})<br>";
			    if (!$filename)
				   $fd=fopen("$dstdir/$prefix/".strtolower(str_replace(" ","-",$item->fields['name'])).".cfg","a+");
			   else 
				   $fd=fopen("$dstdir/$prefix/$filename","a+");

			   switch ($item_type) {


				   case "PluginNagiosHost":
				   case "PluginNagiosHostGroup":
				   case "PluginNagiosServiceGroup":
				   case "PluginNagiosService":
				   case "PluginNagiosCommand":

				   		if($item_type == "PluginNagiosService"){
				   			if($item->fields['is_disabled']){
				   				echo "  $item_type::{$item->fields['name']} is disabled, skip it<br>";
				   			}else{
				   				$buf=$item->showNagiosDef()."\n";
				   			}
				   		}else{
				   			$buf=$item->showNagiosDef()."\n";
				   		}
					   
					   break;
				   case "Group":

					   $buf="define contactgroup {\n";
					   $buf.=" contactgroup_name ".$item->fields['name']."\n";
					   $users=Group_User::getGroupUsers($item_id);
					   foreach($users as $data) {
						   if ($data['name'] && isset($linked_object['User'][$data['id']]))
							   $list_user[$data['name']]=$data['name'];
					   }
					   $buf.=" members ".implode(",",$list_user)."\n";
					   $buf.="}\n";
					   break; 
				   case  "User":
				   		$buf ="";
						$o=new PluginNagiosUser();
						if($o->getFromUser($item_id)){
						    $buf .= $o->showNagiosDef();
						}else{

							echo "Nagios user infos for {$item->fields['name']} not initialized -> create it with default value<br>";
							//create default values
							$o=new PluginNagiosUser();
							$o->fields['users_id'] = $item_id;
							$o->fields['host_notification_commands'] = '1785';
							$o->fields['service_notification_commands'] = '1786';
							$o_id = $o->add($o->fields);
							if($o_id){
								//reload from user
								$o->getFromUser($item_id);
								$buf .= $o->showNagiosDef();
							}

							

							
						}
					   break;
				   case "Calendar":
					   $calendar_name=$item->fields['name'];
					   $calendar_nagios=new PluginNagiosCalendar;
					   $calendar_nagios->getFromCalendar($item_id);

					   $query = "SELECT * FROM `glpi_calendarsegments` WHERE `calendars_id` = '$item_id'  ORDER BY `day`, `begin`, `end`";
					   $result = $DB->query($query);
					   $numrows = $DB->numrows($result);

					   $buf="define timeperiod {\n";
					   $buf.=" timeperiod_name $calendar_name\n";
					   if (isset($calendar_nagios->fields['alias']) && !empty($calendar_nagios->fields['alias'])) 
						   $buf.=" alias ".$calendar_nagios->fields['alias']."\n";
					   else
						   $buf.=" alias ".$calendar_name."\n";


					   $daysofweek[0] = "sunday";
					   $daysofweek[1] = "monday";
					   $daysofweek[2] = "tuesday";
					   $daysofweek[3] = "wednesday";
					   $daysofweek[4] = "thursday";
					   $daysofweek[5] = "friday";
					   $daysofweek[6] = "saturday";

					   if ($numrows) {
						   while ($data = $DB->fetch_assoc($result)) {

							   $buf.=" ".$daysofweek[$data['day']]." ".substr($data["begin"],0,5)."-".substr($data["end"],0,5)."\n"; 
						   }
					   }

					   	if (isset($calendar_nagios->fields['extras'])){
							$buf.= $calendar_nagios->fields['extras'];
						}

					   $buf.="\n}\n\n";

						if (isset($calendar_nagios->fields['extras'])){
							
							//try to retrive calendar from exclude command
							preg_match_all('/exclude (.+)/', $calendar_nagios->fields['extras'], $matches, PREG_SET_ORDER, 0);

							if($matches != null){
								echo "Found excluded calendar from configuration let's load it<br>";
								$calendars = explode(",", $matches[0][1]);

								foreach ($calendars as $value) {
									echo "Found calendar {$value}<br>";

									$cal = new Calendar();
									if(!$cal->getFromDBByCrit(["name" => $value])){
										echo "Can't found calendar with name {$value}<br>";
									}else{

										$calendar_name=$item->fields['name'];
									   $calendar_nagios_exclude=new PluginNagiosCalendar;
									   $calendar_nagios_exclude->getFromCalendar($cal->getID());


									   $query = "SELECT * FROM `glpi_calendarsegments` WHERE `calendars_id` = '".$cal->fields['id']."'  ORDER BY `day`, `begin`, `end`";
									   $result = $DB->query($query);
									   $numrows = $DB->numrows($result);

									   $buf .="define timeperiod {\n";
									   $buf .=" timeperiod_name $value\n";						  
									  	
									  	if (isset($calendar_nagios_exclude->fields['alias'])&& !empty($calendar_nagios_exclude->fields['alias'])) 
										   $buf.=" alias ".$calendar_nagios_exclude->fields['alias']."\n";
									   else
										   $buf.=" alias ".$value."\n";


									   $daysofweek[0] = "sunday";
									   $daysofweek[1] = "monday";
									   $daysofweek[2] = "tuesday";
									   $daysofweek[3] = "wednesday";
									   $daysofweek[4] = "thursday";
									   $daysofweek[5] = "friday";
									   $daysofweek[6] = "saturday";

									   if ($numrows) {
										   while ($data = $DB->fetch_assoc($result)) {

											   $buf.=" ".$daysofweek[$data['day']]." ".substr($data["begin"],0,5)."-".substr($data["end"],0,5)."\n"; 
										   }
									   }

									   //retrive from nagios extra params
										$buf .= $calendar_nagios_exclude->fields['extras'];


									    $buf.="\n}\n\n";

									}

								}
							}
							
						}

					   break; 

			   }

			   fwrite($fd,$buf);
			   fclose($fd);

		   }
	   }



	   /**********************************************************
	    * EXPORT LINKED ITEMS
	    * ********************************************************/
	   /* } else if ($itemtype=="Group") {
	      $o=new Group;
	      $o->getFromDB($item_id);

	      if ($o->getID()<0) {
	      $str_errors[]="Item not found [Group:$item_id]";
	      continue ;
	      } else {
	      $str_infos[]="Item exported [Group:".$o->fields['name']."]";
	      }


	      $buf="define contactgroup {\n";
	      $buf.=" contactgroup_name ".$o->fields['name']."\n";
	      $users=Group_User::getGroupUsers($o->getID());
	      foreach($users as $data) {
	      $list_user[]=$data['name'];
	      }
	      $buf.=" members ".implode(",",$list_user)."\n";
	      $buf.="}\n";

	      $fd=fopen("/$dstdir/contact_groups.cfg","a+");
	      fwrite($fd,$buf);
	      fclose($fd);


	      } else if ($itemtype=="User") {
	      $o=new User;
	      $o->getFromDB($item_id);

	      if ($o->getID()<0) {
	      $str_errors[]="Item not found [User:$item_id]";
	      continue ;
	      } else {
	      $str_infos[]="Item exported [User:".$o->fields['name']."]";
	      }


	      $email=$o->getDefaultEmail();
	      $buf="define contact {\n";
	      $buf.=" contact_name ".$o->fields['name']."\n";
	      if ($o->fields['realname']) $buf.=" alias ".$o->fields['realname']."\n";
	      if ($email) $buf.=" email ".$email."\n";
	      $buf.=str_replace("
","",$o->fields['comment'])."\n";
	      $buf.="}\n";

	      $fd=fopen("/$dstdir/contacts.cfg","a+");
	      fwrite($fd,$buf);
	      fclose($fd);

	      } else  if ($itemtype=="Calendar") {

	      $o=new Calendar;
	      $o->getFromDB($item_id);


	      if ($o->getID()<0) {
	      $str_errors[]="Item not found [Calendar:$item_id]";
	      continue;
	      } else {
	      $str_infos[]="Item exported [Calendar:".$o->fields['name']."]";
	      }


	      $calendar_name=$o->fields['name'];
	      $calendar_nagios=new PluginNagiosCalendar;
	      $calendar_nagios->getFromCalendar($o->getID());


	      $query = "SELECT * FROM `glpi_calendarsegments` WHERE `calendars_id` = '$item_id'  ORDER BY `day`, `begin`, `end`";
	      $result = $DB->query($query);
	   $numrows = $DB->numrows($result);

	   $buf="define timeperiod {\n";
	   $buf.=" timeperiod_name $calendar_name\n";
	   if (isset($calendar_nagios->fields['alias'])) $buf.=" alias ".$calendar_nagios->fields['alias']."\n";

	   $daysofweek[0] = "sunday";
	   $daysofweek[1] = "monday";
	   $daysofweek[2] = "tuesday";
	   $daysofweek[3] = "wednesday";
	   $daysofweek[4] = "thursday";
	   $daysofweek[5] = "friday";
	   $daysofweek[6] = "saturday";

	   if ($numrows) {
		   while ($data = $DB->fetch_assoc($result)) {

			   $buf.=" ".$daysofweek[$data['day']]." ".substr($data["begin"],0,5)."-".substr($data["end"],0,5)."\n"; 
		   }
	   }

	   if (isset($calendar_nagios->fields['extras'])) $buf.=$calendar_nagios->fields['extras'];

	   $buf.="\n}\n";

	   $fd=fopen("/$dstdir/timeperiod.cfg","a+");
	   fwrite($fd,$buf);
	   fclose($fd);


}
*/

if ($after_script->getID() && isset($after_script->fields['command'])) {


	echo "<p style='color:yellow'>RUN SCRIPT</p>";

	//construct argument
	$args_string=$after_script->fields['args'];
	$args_string.=$this->fields['args'];

	$args_string=str_replace("_IPADDR_",$this->fields['ipaddr'],$args_string);
	$args_string=str_replace("_SRCDIR_","$dstdir",$args_string);

	echo "Execute: ".$after_script->fields['command']." $args_string<br>";

	$output=array();
	$errcode=0;
	$res=exec($after_script->fields['command']." $args_string",$output,$errcode);

	if ($errcode) {
		echo "Execution error:$errcode\n"; 
	} else {
		foreach($output as $line) {
			echo $line."<br>";

		}
	}

} 



echo "</div>";



}

static function getChainedObject($itemType,$virtuel,$object_id,$all_values,$result=array() ) {

	echo $object_id."($virtuel):";

	if ($virtuel=='P')
		$result[$itemType][$object_id]=0;

	if (!isset($all_values[$object_id])) {
		echo "nothing<br>";
		return $result;
	} else {
		echo "suite<br>";
	}

	foreach($all_values[$object_id] as $item_type => $ids ) {

		if (in_array($item_type,array("PluginNagiosService","PluginNagiosHost","PluginNagiosServiceGroup","PluginNagiosHostGroup"))) {
			foreach($ids as $virtuel => $id ) {
				$result=self::getChainedOBject($item_type,$virtuel,$id,$all_values,$result); 
			}
		} else {
			foreach($ids as $id) 
			{
				$result[$item_type][$id]=0;

			}
		}

	}

	return $result;
}

}

