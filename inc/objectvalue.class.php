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


class PluginNagiosObjectValue extends CommonDBRelation {

   static $rightname='plugin_nagios';

   // From CommonDBRelation
   // From CommonDBRelation
   static $itemtype_1                 = 'PluginNagiosObject';
   static $items_id_1                 = 'plugin_nagios_objects_id';

   static $itemtype_2                 = 'PluginNagiosField';
   static $items_id_2                 = 'plugin_nagios_fields_id';

   static public $logs_for_item_2     = false;
   static public $checkItem_1_Rights  = false;
   static public $checkItem_2_Rights  = false;	


   function getFromObjectIds($object_id,$field_id) {
	return $this->getFromDBByQuery(" where plugin_nagios_objects_id=$object_id and plugin_nagios_fields_id=$field_id");

   }

  
   /**
    * @param $users_id
    * @param $condition    (default '')
   **/
   static function getValuesForObject($nagios_object, $options='') {
      global $DB;

      $objectvalues = array();
      $query  = "SELECT `glpi_plugin_nagios_fields`.*,
                        `glpi_plugin_nagios_objectvalues`.`id` AS IDD,
                        `glpi_plugin_nagios_objectvalues`.`id` AS linkID,
                        `glpi_plugin_nagios_objectvalues`.`value` AS value,
			`glpi_plugin_nagios_objectvalues`.`flag` AS flag
                 FROM `glpi_plugin_nagios_objectvalues`
                 LEFT JOIN `glpi_plugin_nagios_fields` ON (`glpi_plugin_nagios_fields`.`id` = `glpi_plugin_nagios_objectvalues`.`plugin_nagios_fields_id`)
                 WHERE `glpi_plugin_nagios_objectvalues`.`plugin_nagios_objects_id` = '".$nagios_object->getID()."' ";

      if (isset($options['restrict_fields_name'])) 
	if (is_array($options['restrict_fields_name']))
	  $query .=" AND glpi_plugin_nagios_fields.`name` in ('".implode("','",$options['restrict_fields_name'])."')";
        else
	  $query .=" AND glpi_plugin_nagios_fields.`name` in ( ".$options['restrict_fields_name']." )" ;

      if (isset($options['restrict_fields_id'])) 
        if (is_array($options['restrict_fields_id']))
          $query .=" AND glpi_plugin_nagios_fields.`".self::$item_id_2."` in ('".implode("','",$options['restrict_fields_id'])."')";
        else
          $query .=" AND glpi_plugin_nagios_fields.`".self::$item_id_2."` in ( ".$options['restrict_fields_id']." )" ;

      $query.=" ORDER BY `glpi_plugin_nagios_fields`.`name`";

      foreach ($DB->request($query) as $data) {
         $objectvalues[$data['id']] = $data;
      }
      return $objectvalues;
   }


  static function setValueByFieldName($item_type,$item_id,$field_name,$value) {
	  global $DB; 

         $field=PluginNagiosField::getFieldsByName($item_type,$field_name);
         $field_id=$field->fields['id'];

	 $id=-1;
	 $sql="select id from glpi_plugin_nagios_objectvalues where plugin_nagios_fields_id='$field_id' and plugin_nagios_objects_id='$item_id'";
	 foreach($DB->request($sql) as $data)
	     $id=$data['id'];

	 
	 $pv=new PluginNagiosObjectValue;
	 if ($id)
		 $pv->getFromDB($id);

	 $input['plugin_nagios_objects_id']=$item_id;
         $input['plugin_nagios_fields_id']=$field_id;

	

	 if (!$value && isset($pv->fields['id']) ) {
                 $input['id']=$pv->fields['id'];
	         $pv->delete($input);
	 } else if ($value && isset($pv->fields['id']) ) {
		 $input['value']=$value;
		 $input['id']=$pv->fields['id'];
		$pv->update($input);
	 } else {
                $input['value']=$value;
                $pv->add($input);
         }

  }

  static function get_all_objects_for_object($nagios_obj_id,&$linked_object,$entity,$use='V') {

  	  global $DB;

	  static $all_object;

	  //first get all object 
	  if (!isset($all_object['plugin_nagios'])) {
		  
		  $all_object['plugin_nagios']=array();

		  $query="select * from glpi_plugin_nagios_objects where is_deleted=0  AND is_disabled=0 AND entities_id in (".implode(",",$entity).")";
		  foreach($DB->request($query) as $data) {
			  $o=PluginNagiosObject::getNagiosObject($data['type']);
			  $o->fields=$data;
			  $all_object['plugin_nagios'][$data['id']]=$o;
		  }
	  }


         $current_obj  = isset($all_object['plugin_nagios'][$nagios_obj_id]) ? $all_object['plugin_nagios'][$nagios_obj_id] : false;

          if (!$current_obj) {
	      $o=new PluginNagiosObject();
	      $o->getFromDB($nagios_obj_id);
	      echo "Item not found or disabled -> '{$o->fields['name']}'::$nagios_obj_id<br>";
	      return ;
          }

	  if (isset($linked_object[$use][$current_obj->getType()][$nagios_obj_id]))
		  return null; 

	  $current_obj  = $all_object['plugin_nagios'][$nagios_obj_id];

	  if (!$current_obj) {
		  echo "<font color=red> ERRROOOR ($nagios_obj_id)</font><br>";
                  return ;
          }
	   
	  //ajout de l objet à la liste des objets a exporter
	  $linked_object[$use][$current_obj->getType()][$nagios_obj_id]=$current_obj;
	  if ($current_obj->fields['parent_objects_id']) {
		self::get_all_objects_for_object($current_obj->fields['parent_objects_id'],$linked_object,$entity);  
          }


	  //TRAITEMENT DES ATTRIBUTS
          $query="select v.`value`,v.`plugin_nagios_objects_id`,f.id,f.field_value,f.name FROM".
                    " glpi_plugin_nagios_fields f,glpi_plugin_nagios_objectvalues v,glpi_plugin_nagios_objects o  where o.id=v.plugin_nagios_objects_id AND".
                    " v.plugin_nagios_fields_id=f.id and f.field_value like '_O:%' AND".
		    " o.id=$nagios_obj_id";

		    //echo $DB->request($query);
	  foreach($DB->request($query) as $data) {
	  	
		$className=substr($data['field_value'],3,strlen($data['field_value'])-3);
		
		//echo "(".$data['value'].")".$className."-".$data['value']."<br>";

	      if(strpos($data['value'], "$#$") !== false){
			$data_value=explode('$#$',$data['value']); 
	      }else{
			$data_value=explode(':',$data['value']);
	      }
		if (in_array($className,array('PluginNagiosService','PluginNagiosHost','PluginNagiosHostGroup','PluginNagiosServiceGroup','PluginNagiosRole'))) {
			foreach( $data_value as $nagios_id) {
				self::get_all_objects_for_object($nagios_id,$linked_object,$entity,'P');
                        }
		} else if ($className=="PluginNagiosCommand") {
		      $o=new $className;
		      $cmd_id=$data_value[0];
		      $o->getFromDB($cmd_id);
		      if ($o->getID()>0)
		         $linked_object['P'][$className][$cmd_id]=$o;	
		
		
		} else {
			foreach( $data_value as $glpi_id) {
		            $o=new $className;
			    $o->getFromDB($glpi_id);
			    if ($o->getID()>0)
			      $linked_object['P'][$className][$glpi_id]=$o;
                        }

		}

	  }


          //TRAITEMENT DES SERVICES ET DES ROLES 
          $query="select * from glpi_plugin_nagios_objectlinks where itemtype in ('PluginNagiosRole','PluginNagiosService') and plugin_nagios_objects_id='$nagios_obj_id'";
	  foreach($DB->request($query) as $data) {
	        self::get_all_objects_for_object($data['items_id'],$linked_object,$entity);
          }
           
  }







   /* return all object  */
   static function getAllValuesE($entities_list) {
      global $DB;
         /* first identify fields object */
         $fields=array();
         
         $query="select v.`value`,v.`plugin_nagios_objects_id`,f.id,f.field_value FROM".
                    " glpi_plugin_nagios_fields f,glpi_plugin_nagios_objectvalues v,glpi_plugin_nagios_objects o  where o.id=v.plugin_nagios_objects_id AND".
                    " v.plugin_nagios_fields_id=f.id and f.field_value like '_O:%' AND".
                    " o.entities_id in  ( ".implode(",",$entities_list)." ) ";

	 foreach($DB->request($query) as $data) {
		list($t,$item_type)=explode(":",$data['field_value']);
		$a=explode("$#$",$data['value']);
		if ($item_type=="PluginNagiosCommand")
			$a=array($data['value'][0]);
                foreach($a as $id)
		    $fields[$data['plugin_nagios_objects_id']][$item_type]['P']=$id;
        
         }

      


         $query="select parent_objects_id,id,type  FROM".
                    " glpi_plugin_nagios_objects ".
                    " WHERE entities_id in  ( ".implode(",",$entities_list)." )  ";

         foreach($DB->request($query) as $data) {
		if ($data['parent_objects_id']>0)
                    $fields[$data['id']][PluginNagiosObject::getClassForType($data['type'])]['T']=$data['parent_objects_id'];
	 }


         
         $query="select o.id,l.items_id FROM".
                    " glpi_plugin_nagios_objects o,glpi_plugin_nagios_objectlinks l ".
                    " WHERE l.plugin_nagios_objects_id=o.id and l.itemtype='PluginNagiosService'  and o.entities_id in  ( ".implode(",",$entities_list)." )  ";
	 
	 foreach($DB->request($query) as $data) {
		
                    $fields[$data['id']][PluginNagiosObject::getClassForType('ST')]['T']=$data['items_id'];
         }


        return $fields;


   }
   

   /*
    *  fields_id: array or integer of field to display
    *  fields_name
    */ 
   static function getHeritedValues($item,$options=array()) {
        
        $result=array();
        $all_avaiable_values=array();


	if (!isset($options['id2name']))
		$options['id2name']=true;

        /* first get parent item  */
        if (isset($options['from_parent'])) {
         $item_object=PluginNagiosObject::getNagiosObject($item->get_type());
         $item_object->getFromDB($item->get_parent_id());
        } else {
         $item_object=$item;
        }

        $parents=$item_object->getParents();
        $values=array();
	//get value for all
        foreach( $parents as $idx=>$no) {
	  $values[$idx]=PluginNagiosObjectValue::getValuesForObject($no, $options);           

          foreach ($values[$idx] as $id => $ov) {
	    $all_avaiable_values[$ov['id']]=$ov;
	  } 
	}


	foreach($all_avaiable_values as $field_id => $ov ) 
        {
	  if ($ov['field_flag']=='M')
	    $merge=true;
	  else
	    $merge=false;

	  $value=PluginNagiosObjectValue::resolveValue($field_id,$item_object->getID(),$parents,$values,$merge);
	  $old=$value;
	 
          if ($ov['field_type']=='LIST_ORDR' or $ov['field_type']=='LIST' or $ov['field_type']=='LIST_MULT' or $ov['field_type']=='SPECIAL') {
	    $mod="";
            
            if (strstr($value,"[m]"))
	      $mod='+';
	    else if (strstr($value,"[i]"))
	      $mod='!';
			
	    if ($options['id2name']) {				
	      if ($itemtype=str_replace("_O:","",strstr($ov['field_value'],"_O:"))) {
                if ($itemtype=='PluginNagiosCommand')
		  $only_first=1;
		else
		  $only_first=0;

	        $value="$mod".implode('$#$',PluginNagiosObject::convertIDtoName($itemtype,$value,$only_first))."";
	      } else {
	        $value="$mod".$value."";
              }
	    } else {
	      $value="$mod".$value."";
	    }
			
	  } 
 
	  $result[$field_id]=array('name'=>$ov['name'],'value'=>$value);
	}

	return $result;
   }

   static function showNagiosDef($item,$options=array()) {


        if (isset($options['html']) && $options['html']==1) {
                $start_tag="<font style='font-weight:bold;color:#888888'>";
                $end_tag="</font>";
        } else {
                $start_tag=$end_tag="";
        }


	$buf=array();
	$result="";
	$values=self::getHeritedValues($item,$options);
	foreach($values as $field_id => $ov) {
	  if ($ov['value']=="")
	    continue ;
	
	  $buf[$ov['name']]=$ov['value'];
	}


	foreach ($buf as $opt_name => $opt_value) { 
		//echo $opt_value."<br>";

		if($opt_name == "check_command"){
			$opt_value=str_replace("$#$","!",$opt_value);
		}else{
			$opt_value=str_replace("$#$",",",$opt_value);
		}

	  $result.=" $start_tag$opt_name$end_tag ".html_entity_decode($opt_value)."\n";
	}
        return $result;
   }

   static function cleanFlag($str) {
	$res=str_replace("[m]","",$str);
	$res=str_replace("[i]","",$res);
        return $res;
   }


   /* recursive function */
   static function resolveValue($field_id,$obj_id,$parents,$values,$merge=false) {
       $sep="$#$";
       $flag="";
	  if (isset($values[$obj_id][$field_id])) {
	      $value=$values[$obj_id][$field_id]['value'];
	      $flag =$values[$obj_id][$field_id]['flag'];
	      ($value) ? $sep='$#$' : $sep="";
	      ($flag)  ? $flag="[$flag]" : $flag='' ;
	      if ($merge && $parents[$obj_id]->get_parent_id()) {
		      $pv=PluginNagiosObjectValue::resolveValue($field_id,$parents[$obj_id]->get_parent_id(),$parents,$values,$merge);
		      if ($pv) { 
			 return $flag.$value.$sep.$pv;
		      } else {
			 return $flag.$value;
	 	      }
	      } else {
		      return $flag.$value;
	      }
          } else {
	      if ($parents[$obj_id]->get_parent_id())
	      	   return PluginNagiosObjectValue::resolveValue($field_id,$parents[$obj_id]->get_parent_id(),$parents,$values,$merge);	   	
              else
		   return null;
          }
  }

}
?>
