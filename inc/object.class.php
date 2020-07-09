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
  die("Sorry. You can't  access directly to this file");
}


class PluginNagiosObject extends CommonDropdown {

  static $rightname='plugin_nagios';
  static $nagios_type='';

  const NAGIOS_HOSTGROUP_TYPE       = 'HG';
  const NAGIOS_HOST_TYPE    = 'HT';
  const NAGIOS_SERVICE_TYPE = 'ST';
  const NAGIOS_SERVICEGROUP_TYPE    = 'SG';
  const NAGIOS_COMMAND_TYPE         = 'CO' ;
  const NAGIOS_ROLE_TYPE         = 'RO' ;

  private $_value;
  function get_type() { return static::$nagios_type; }
  function get_parent_id() { if (isset($this->fields['parent_objects_id'])) return $this->fields['parent_objects_id']; else return null; }
   

  static function Dropdown($params=array())
  {
    if (static::$nagios_type)  {
     if (!isset($params['condition']))
      $params['condition']=" `type`='".static::$nagios_type."'";
     else
      $params['condition'].=" AND `type`='".static::$nagios_type."'";
     }
    parent::Dropdown($params);
  }

  static function getTable($className=null)
  {
    return "glpi_plugin_nagios_objects";
  }

  static function getTypeName($nb=0) 
  {
    return "NagiosObject";
  }

  /**
   * @see CommonGLPI::getMenuName()
  **/
  static function getMenuName()
  {
    return static::getTypeName(2);
  }

  static function getObjects($options=array()) {
      global $DB;

      $nagios_objects = array();
      $query  = "SELECT `glpi_plugin_nagios_objects`.*
                 FROM `glpi_plugin_nagios_objects`
                 WHERE 1=1 ";

      if (isset($options['restrict_entitites'])) {
         if (is_array($options['restrict_entitites']))
		$query .= " AND entities_id in ( ".implode(',',$options['restrict_entities'])." ) ";
	 else
		$query .= " AND entities_id = ".$options['restrict_entitites'] ; 
      }

      $query.=" ORDER BY `glpi_plugin_nagios_objects`.`name`";



      foreach ($DB->request($query) as $data) {
         if (isset($options['only_id_type'])) {
		$nagios_objects[$data['id']]=$data['type'];
         } else {
	   $o=new PluginNagiosObject;
           $o->fields=$data;
           $nagios_objects[$data['id']] = $o;
         }
      }
      return $nagios_objects;

  }

  static function getNagiosObjectToExport($entities_list) {
    global $DB;

    $nagios_objects=array();

    $query="select o.*,l.itemtype  FROM".
             " glpi_plugin_nagios_objects o,glpi_plugin_nagios_objectlinks l  WHERE l.plugin_nagios_objects_id=o.id AND o.entities_id in ( ".implode(",",$entities_list)." ) AND o.is_deleted=0 AND o.is_disabled=0 and o.type='HT' and l.itemtype in ('Computer','NetworkEquipment','Printer') ";
    foreach($DB->request($query) as $data) {
	    
	 $o=new PluginNagiosHost;
	 $o->fields=$data;
         $nagios_objects[$data['itemtype']][$data['id']]=$o;
    }
    
    return $nagios_objects;

  }

   


  static function cloneItem($item_id,$suffix='_clone',$prefix='',$overload_name='') {
     if (!$item_id)
                return null;

     $item=new PluginNagiosObject;
     $item->getFromDB($item_id);
     if (!$item->fields['id'])
        return  null;

     $cloned_item=new PluginNagiosObject;
     $input['entities_id']=$item->fields['entities_id'];
     $input['alias']=$item->fields['alias'];
     $input['type']=$item->fields['type'];
     $input['is_model']=$item->fields['is_model'];
     $input['desc']=$item->fields['desc'];
     if ($overload_name)
	     $input['name']=$prefix.$item->fields['name'].$suffix;
     else
	     $input['name']=$prefix.$item->fields['name'].$suffix;

     $input['parent_objects_id']=$item->fields['parent_objects_id'];


     $cloned_item->add($input);
     if (!$cloned_item->getID())
        return null;


     // record option value
     $object_value=PluginNagiosObjectValue::getValuesForObject($item);
     foreach($object_value as $field_id => $data) {
                $tmpo=new PluginNagiosObjectValue();
                $input=array();
                $input['plugin_nagios_fields_id']=$field_id;
                $input['value']= Toolbox::addslashes_deep($data['value']);
                if (isset($data['flag']) && $data['flag'])
			$input['flag']=$data['flag'];
                $input['plugin_nagios_objects_id']=$cloned_item->getID();
                $tmpo->add($input);
     }

     // record macro 
     $object_macros=PluginNagiosMacro::getObjectMacro($item->getID());
     foreach($object_macros as $field_id => $data) {
                $tmpo=new PluginNagiosMacro();
                $input=array();
                $input['name']=$data['name'];
                $input['value']= Toolbox::addslashes_deep($data['value']);
                $input['is_secure']=$data['is_secure'];
                $input['plugin_nagios_objects_id']=$cloned_item->getID();
                $tmpo->add($input);
     }


     if ($item->fields['type']=='HT' or $item->fields['type']=='RO') {
       $services  = PluginNagiosObjectLink::getServicesForObject($item->getID());
       foreach($services as $idx => $data ) {
	   //echo "clone service => ".$data['id']."<br>";
           $cloned_service=PluginNagiosService::cloneItem($data['id'],$suffix,$prefix,$prefix.$item->fields['name'].$suffix);
           //echo "cloned service:".$cloned_service->fields['id']."<br>";
           if ($cloned_service && $cloned_service->fields['id'] ) {
                //create Link  
                $tmpo=new PluginNagiosObjectLink();
                $dat['plugin_nagios_objects_id']=$cloned_item->getID();
                $dat['items_id']=$cloned_service->getID();
                $dat['itemtype']='PluginNagiosService';
                $tmpo->add($dat);

           }
       }
     }

     if ($item->fields['type']=='HT' ) {
       $roles  = PluginNagiosObjectLink::getRolesForObject($item->getID());
       foreach($roles as $idx => $data ) {
            //create Link  
            $tmpo=new PluginNagiosObjectLink();
            $dat['plugin_nagios_objects_id']=$cloned_item->getID();
            $dat['items_id']=$data['id'];
            $dat['itemtype']='PluginNagiosRole';
            $tmpo->add($dat);

       }
     }



    return $cloned_item;
  }


  
  static function getMenuContent() {

	$menu=array();
	$menu['is_multi_entries']=true;
	$menu['PluginNagiosHost']['title']="HostTemplate";
	$menu['PluginNagiosHost']['page']=PluginNagiosHost::getSearchURL(false);
        $menu['PluginNagiosHost']['links']['add']=PluginNagiosHost::getFormURL(false);
	$menu['PluginNagiosHost']['links']['search']=PluginNagiosHost::getSearchURL(false);

        $menu['PluginNagiosHostGroup']['title']="HostGroup";
        $menu['PluginNagiosHostGroup']['page']=PluginNagiosHostGroup::getSearchURL(false);
        $menu['PluginNagiosHostGroup']['links']['add']=PluginNagiosHostGroup::getFormURL(false);
        $menu['PluginNagiosHostGroup']['links']['search']=PluginNagiosHostGroup::getSearchURL(false);



	return $menu;


   }

  

   static function getNagiosObject($nagios_type) {
        switch ($nagios_type) {
          case 'HT':
             $o=new PluginNagiosHost();
             break;
          case 'HG':
             $o=new PluginNagiosHostGroup();
             break;
          case 'SG':
             $o=new PluginNagiosServiceGroup();
             break;
          case 'ST':
             $o=new PluginNagiosService();
             break;
          case 'CO':
             $o=new PluginNagiosCommand();
             break;
          case 'RO':
	     $o=new PluginNagiosRole();
             break;
          default:
             return null;
	     break;
       }
       return $o;
   }

   static function getClassForType($nagios_type) {
      switch ($nagios_type) {
          case 'HT':
             return "PluginNagiosHost";
             break;
          case 'HG':
             return "PluginNagiosHostGroup";
             break;
          case 'SG':
             return "PluginNagiosServiceGroup";
             break;
          case 'ST':
             return "PluginNagiosService";
             break;
          case 'CO':
             return "PluginNagiosCommand";
             break;
          case 'RO':
             return "PluginNagiosRole";
             break;
          default:
             return null;
             break;
       }

   }

   static function getTypeForClass($className) {
      switch ($className) {
          case 'PluginNagiosHost':
             return "HT";
             break;
          case 'PluginNagiosHostGroup':
             return "HG";
             break;
          case 'PluginNagiosServiceGroup':
             return "SG";
             break;
          case 'PluginNagiosService':
             return "ST";
             break;
          case 'PluginNagiosCommand':
             return "CO";
             break;
          case 'PluginNagiosRole':
             return "RO";
             break;
          default:
             return null;
             break;
       }

   }



   function defineTabs($options=array()) {
      $ong = array();
      $this->addDefaultFormTab($ong);
      $this->addStandardTab(get_class($this), $ong, $options);
      $this->addStandardTab('Log',$ong, $options);
      $ong['no_all_tab']=true;
      return $ong;
   }

   function getParents($parents=array()) {
	
	$parents[$this->getID()]=$this;

	if (!$this->get_parent_id())
		return $parents;
	else {
	   $o=PluginNagiosObject::getNagiosObject($this->get_type());
	   $o->getFromDB($this->get_parent_id());
	   return $o->getParents($parents);
        }	
   } 

   function getValue($field_id) {
	if (!isset($this->_values[$field_id]))
		return null;

	return $this->_values[$field_id]['value'];
   }

   function loadObjectValue() {
	
	if (!is_array($this->_value))
   	  $this->_values=PluginNagiosObjectValue::getValuesForObject($this);	

   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

    $ong=array();

    if ($item instanceof PluginNagiosObject ) {

      if (!$item instanceof PluginNagiosRole) {
        $ong[10] =_("Standard Options");
	    
        if ($item instanceof PluginNagiosHost or $item instanceof PluginNagiosService )
	      $ong[11] =_("Notification Options");

        $ong[12] =_("Extra Options");
        $ong[20] =_("Custom Macros");
        $ong[50] =_("Overview");

      } 
      
      if ($item instanceof PluginNagiosCommand)
        return array();
      return $ong;
    } else if (in_array($item->getType(),array('Computer','Printer','NetworkEquipment'))) {
      $ong[10] ="Nagios - "._("Host configuration");
      $ong[11] ="Nagios - "._("Notification Options");
      $ong[20] ="Nagios - "._("Custom Macros");
      $ong[12] ="Nagios - "._("Extra Options");
      $ong[50] ="Nagios - "._("Overview");

      return $ong;
    }
     return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
     if ($item instanceof PluginNagiosObject) {
	
	switch ($tabnum) {
		case 10:
			static::displayOptions($item,static::$FIELDS_STD_VIEW);
			break;
		case 11:
			static::displayOptions($item,static::$FIELDS_NOTIF_VIEW);
                        break;
                case 20:
			PluginNagiosMacro::showForObject($item);
			break;
		case 12:
			static::displayTabOther($item);
			break;
		case 50:
			static::displayTabApercu($item);
			break;
	}
     } 

   }

  static function displayOptions($item,$fields_to_display) {
	global $CFG_GLPI;

        $js_options=array('root_doc' => $CFG_GLPI['root_doc'],'entity_id'=>$item->fields['entities_id']);

        echo "<script type='text/javascript'>";
        echo "var nagios = $(document).nagios(".json_encode($js_options).");";
        echo "</script>";
	

        /* get nagios object instance */
        $nagios_object=self::getNagiosObject(static::$nagios_type);


        $rand=rand();
        echo "<form name='nagios_hosttemplate_form_$rand' id='nagios_object_form$rand' method='post' action='".Toolbox::getItemTypeFormURL(get_called_class())."'>";
//        echo "<form method='post' id='formObjectValue_$rand' onsubmit=\"return nagios.saveFields('formObjectValue_$rand','');\" >";
        echo Html::hidden("id"  ,array('value'=>$item->fields['id']));
        echo "<table class='tab_cadre_fixe' >";
        echo "<tr><th style='width:100px'>".__('FieldName')."</th><th style='width:40px'>".__('Inheritance')."</th><th>".__('Value')."</th></tr>";
 
        foreach ($fields_to_display as $id_group=>$fields_group )
        {
          echo "<tr><th colspan='3' style='text-align:left;color:#f0431a' >"._($fields_group['libel'])."</th></tr>";
	
          $fields_list     = PluginNagiosField::getFieldsByName(static::$nagios_type,$fields_group['fields']);
         
          /* get herited value for this object  */
          $herited_values  = PluginNagiosObjectValue::getHeritedValues($item,array("from_parent"=>true,"restrict_fields_name"=>$fields_group['fields']));

          $own_values      = PluginNagiosObjectValue::getValuesForObject($item,array("restrict_fields_name"=>$fields_group['fields']));



  	  foreach($fields_list as $idx => $field) 
          {

            $params=array();
            $params['entity']=$item->fields['entities_id'];
            $params['vertical']=false;
          
            /* not display himself in the list */
	    if ($field['field_type']=="LIST_ORDR" or $field['field_type']=="LIST") {
	      if ($field['object_type']==static::$nagios_type && strstr($field['field_value'],get_called_class())) {
	        $params['used'] = array($item->getID() );
	      }
	    }
	
            /* a specific value exist for this object */ 
            if (isset($own_values[$field['id']])) {
	      $params['value']  = $own_values[$field['id']]['value'];
	      $params['linkID'] = $own_values[$field['id']]['linkID'];
	      $params['flag']   = $own_values[$field['id']]['flag'];
	    } 

	    if (isset($herited_values[$field['id']])) 
	      $params['herited_value']=$herited_values[$field['id']]['value'];
            
	    PluginNagiosField::showInput($field['id'],$params);	
	  }	

        }

        if (Session::haveRightsOr("plugin_nagios",array(UPDATE,CREATE)))
          echo "<tr><td colspan='3' style='text-align:center'><input type='submit' class='submit' name='save_opts' value='"._("Save")."'></td></tr>";
        echo "</table>";

        Html::closeForm();

	
  }	


  static function getAvaiableObjectForEntity($nagios_type,$entity_id) {
	//get all entities
	$entities=PluginNagiosObject::get_recursive_entities($entity_id);

	$no=PluginNagiosObject::getNagiosObject($nagios_type);
	return $no->find("entities_id in (".implode(',',$entities).") and `type`='$nagios_type' ");
	
  }
 
  /* return all parent entities included current entity */
  static function getRecursiveEntities($entity_id,$entities=array()) {
  global $DB;
  static $entities_list=array();

        $entities[]=(int)$entity_id;   
     
        if ($entity_id==0)
                return $entities;

  if (!count($entities_list)) {
    if ($result=$DB->query("select id,entities_id from glpi_entities")) {
             while ($line = $DB->fetch_assoc($result)) {
               $entities_list[$line['id']] = $line['entities_id'];
             }
    }
        }
      return self::getRecursiveEntities($entities_list[$entity_id],$entities);

  }


  /* return all children entities included current entity */
  static function getChildrenEntities($entity_id,$entities=array()) {
        global $DB;
        static $entities_list=array();
        static $entities=array();  
        
	$entities[$entity_id]=$entity_id;


        if (!count($entities_list)) {
          if ($result=$DB->query("select id,entities_id from glpi_entities")) {
             while ($line = $DB->fetch_assoc($result)) {
               $entities_list[(int)$line['entities_id']][] = $line['id'];
             }
          }
        }
        if (isset($entities_list[$entity_id])) { 
		foreach($entities_list[$entity_id] as $parent_entity => $child_entity ) {
			self::getChildrenEntities($child_entity,$entities);
		}
        } 

      return $entities;

  }


  static function displayTabApercu($item) {

	echo "<div class='nagios-apercu'>";
	echo "<pre>";
	echo $item->showNagiosDef(array("html"=>1))."\n";
	echo "</pre>";
	echo "</div>";

  }


  /* 
   * nagios_type : HT, ST
  /* $ids: array of id or string id1:id2:id3 
   * return array of name 
   */
  static function convertIDtoName($itemtype,$ids,$only_first) {
    global $DB;
    $item = getItemForItemtype($itemtype);

    $a_ids=array();
    $id_to_parse=array();

    if (!is_array($ids)) {
      if ($ids=="") {
        return array();	 
      }

              if(strpos(PluginNagiosObjectValue::cleanFlag($ids), "$#$") !== false){
      $a_ids=explode("$#$",PluginNagiosObjectValue::cleanFlag($ids));
        }else{
$a_ids=explode(":",PluginNagiosObjectValue::cleanFlag($ids));
        }


      
      	
    } else {
      $a_ids=$ids;
    }

    while(count($a_ids)) {
      $id_to_parse[]=array_shift($a_ids);
      if ($only_first)
	break;
    }


    $result=array();
    $result_ordr=array();
    $query="select id,`name` from ".$item->getTable()." where id in (".implode(",",$id_to_parse).")";	
    foreach ($DB->request($query) as $data) {
         $result[$data['id']] = $data;
    }
    // preserve order
    foreach ($id_to_parse as $idx => $id) {
	if (isset($result[$id]))
		$result_ordr[]=$result[$id]['name'];
    }
    
    while(count($a_ids))
	$result_ordr[]=array_shift($a_ids);

    return $result_ordr;

  }

  static function displayTabOther($item,$glpi_item=0) {
     global $CFG_GLPI;
	
     $excluded_fields=array();

     $js_options=array('root_doc' => $CFG_GLPI['root_doc'],'entity_id'=>$item->fields['entities_id']);

     echo "<script type='text/javascript'>";
     echo "var nagios = $(document).nagios(".json_encode($js_options).");";
     echo "</script>";



     /* exclude fields displayed in other Tab */
     foreach(static::$FIELDS_STD_VIEW as $opt_group_id=>$opt_group) {
       foreach($opt_group['fields'] as $idx=>$field) {
         $excluded_fields[]=$field;
       }
     }

          /* exclude fields displayed in other Tab */
     foreach(static::$FIELDS_NOTIF_VIEW as $opt_group_id=>$opt_group) {
       foreach($opt_group['fields'] as $idx=>$field) {
         $excluded_fields[]=$field;
       }
     }

     if ($glpi_item) {
         foreach(static::$FIELDS_COMPUTER_GENERAL_VIEW['fields']  as $idx=>$field) {
			             $excluded_fields[]=$field;
         }

	               /* exclude fields displayed in other Tab */
          foreach(static::$FIELDS_COMPUTER_CHECK_VIEW['fields']  as $idx=>$field) {
				          $excluded_fields[]=$field;
         

     }
    }


    $fields_list = PluginNagiosField::getFieldsByName(static::$nagios_type,$excluded_fields);
    $used_fields = array();
    foreach ($fields_list as $idx => $field)
      $used_fields[]=$field['id'];
	

    PluginNagiosField::Dropdown(array("comments"=>false,"used"=>$used_fields,"name"=>"addField","condition"=>"object_type='".static::$nagios_type."'") );

    $onclick="nagios.getFieldForm($('input[name*=addField]').val(),'list_fields');";
    echo "&nbsp;<input type='button' class='submit' value='".__('Add')."' onclick=\"$onclick\" />";
    $rand=rand();
    echo "<div class='spaced'></div>";

    echo "<form name='nagios_hosttemplate_form_$rand' id='nagios_hosttemplate_form$rand' method='post' action='".Toolbox::getItemTypeFormURL(get_called_class())."'>";
    echo Html::hidden("id"  ,array('value'=>$item->fields['id']));
/*
        echo "<form method='post' id='formObjectValue_$rand' onsubmit=\"return nagios.saveFields('formObjectValue_$rand','');\" >";
	echo "<form method='post' id='formObjectValue_$rand'  onsubmit=\"return nagios.saveFields('formObjectValue_$rand','');\">";
        echo Html::hidden("id"  ,array('value'=>$item->fields['id']));
*/
    echo "<table class='tab_cadre_fixe' id='list_fields'>";
    echo "<tr>";
    echo "<th width='170px' style='text-align:left;color:#f0431a' >"._("Parameters")."</th>";
    echo "<th width='15px'></th><th  style='text-align:left;color:#f0431a' >"._("Values")."</th>";
    echo "</tr>";

    $objectvalues=PluginNagiosObjectValue::getValuesForObject($item);
    $parent_values=PluginNagiosObjectValue::getHeritedValues($item,array('id2name'=>false));


    $p['entity']=$item->fields['entities_id'];

    foreach ($objectvalues as $idx => $objectvalue) {
      if (!in_array($objectvalue['id'],$used_fields)) {
        $p['value']=$objectvalue['value'];
        $p['flag']=$objectvalue['flag'];
        PluginNagiosField::showInput($objectvalue['id'],$p);	
      }
    }
  
    foreach ($parent_values as $idx => $field) {
	if (!isset($objectvalues[$idx]) && !in_array($idx,$used_fields)      ) {
		$p['herited_value']=$field['value'];
		$p['flag']='';
		PluginNagiosField::showInput($idx,$p);
	}


    }

   
    echo "</table>";



    if (Session::haveRightsOr("plugin_nagios",array(UPDATE,CREATE)))
      echo "<div><input type='submit' class='submit' name='save_opts' value='"._("Save")."'></div>";
    Html::closeForm();
  }
  

   function getSearchOptions() {
      $tab = array();
      $tab['common'] = static::getTypeName();



      $tab[1]['table']     = 'glpi_plugin_nagios_objects';
      $tab[1]['field']     = 'name';
      $tab[1]['name']      = _('Name');
      $tab[1]['datatype']  = 'itemlink';
      $tab[1]['searchtype'] ='contains';

      $tab[2]['table']     = 'glpi_plugin_nagios_objects';
      $tab[2]['field']     = 'alias';
      $tab[2]['name']      = _('Alias');
      $tab[2]['datatype']  = 'text';

      $tab[3]['table']     = 'glpi_plugin_nagios_objects';
      $tab[3]['field']     = 'desc';
      $tab[3]['name']      = _('Description');
      $tab[3]['datatype']  = 'text';



      $tab[4]['table']     = 'glpi_plugin_nagios_objects';
      $tab[4]['field']     = 'is_model';
      $tab[4]['name']      = _('Is template');
      $tab[4]['massiveaction'] = false;
      $tab[4]['datatype']       = 'dropdown';

      /*$tab[5]['table']     = 'glpi_plugin_nagios_objects';
      $tab[5]['field']     = 'is_disabled';
      $tab[5]['name']      = _('Is disabled');
      $tab[5]['massiveaction'] = yes;
      $tab[5]['datatype']       = 'dropdown';*/

 
      $tab[80]['table']          = 'glpi_entities';
      $tab[80]['field']          = 'completename';
      $tab[80]['name']           = __('Entity');
      $tab[80]['datatype']       = 'itemlink';
      $tab[80]['itemlink_type']       = 'Entity';
      
      $tab[81]['table']       = 'glpi_entities';
      $tab[81]['field']       = 'entities_id';
      $tab[81]['name']        = __('Entity')."-".__('ID');


      return $tab;
  }

  function post_updateItem($history = 1) {
    global $CFG_GLPI;

    if(get_class($this) == PluginNagiosRole::getType()){
      if(in_array('name', $this->updates)){
        $newname = $this->input['name'];
        //update all service_view
        $link = new PluginNagiosObjectLink();
        $data = $link->find("plugin_nagios_objects_id = ". $this->getID(). " AND itemtype = '".PluginNagiosService::getType()."'");
        foreach ($data as $key => $value) {
          $service = new PluginNagiosService();
          $service->getFromDB($value['items_id']);
          $service->fields['name'] = $newname;
          $service->update($service->fields);
        }
      }
    }
  }


   static function getAdditionalMenuLinks() {
      global $CFG_GLPI;
      $links = array();
      if (Session::haveRightsOr("plugin_nagios",array(CREATE)))
	      $links['add'] = '/plugins/nagios/front/'.strtolower(static::getTypeName(1)).'.form.php';
      return $links; //$links;
   }

  static function canView() {
   return true;
   return Session::haveRightsOr(self::$rightname, array(CREATE, UPDATE, PURGE));
  }

  static function canCreate() {
   return Session::haveRightsOr(self::$rightname, array(CREATE));
  }

  static function canUpdate() {
   return Session::haveRightsOr(self::$rightname, array(CREATE, UPDATE, PURGE));
  }

  static function canPurge() {
   return true;
   return Session::haveRightsOr(self::$rightname, array( PURGE));
  }


 
  function getForbiddenStandardMassiveAction()
  {

      $forbidden = parent::getForbiddenStandardMassiveAction();
      $forbidden[] = 'update';
      return $forbidden;
  }


 
  function getSpecificMassiveActions($checkitem=NULL) {
    $class=get_called_class();
    $actions = parent::getSpecificMassiveActions($checkitem);
    if (self::canUpdate())   {
        //$actions['PluginNagiosHost' . MassiveAction::CLASS_ACTION_SEPARATOR . "plugin_nagios_host_enabledisable"]='Nagios - '.__('Enable/Disable Monitoring');
        $actions[$class. MassiveAction::CLASS_ACTION_SEPARATOR . "plugin_nagios_host_trash"]='Nagios - '.__('Put in dustbin');
        $actions[$class. MassiveAction::CLASS_ACTION_SEPARATOR . "plugin_nagios_host_purge"]='Nagios - '.__('Delete permanently');
        $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . "plugin_nagios_host_clone"]='Nagios - '.__('Clone');
	if (static::$nagios_type!='RO') {
         $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . "plugin_nagios_host_setparent"]='Nagios - '.__('Set As Child as');
         $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . "plugin_nagios_host_setfield"]='Nagios - '.__('Set Fields');
	}
	if (static::$nagios_type=='HT') {
	 $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . "plugin_nagios_host_setrole"]='Nagios - '.__('Set/Unset Supervision Template');
	 $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . "plugin_nagios_host_addfield"]='Nagios - '.__('Remove Fields');
         $actions[$class . MassiveAction::CLASS_ACTION_SEPARATOR . "plugin_nagios_host_removefield"]='Nagios - '.__('Add Fields');

	}
    }


    return $actions;
  }



  static function showMassiveActionsSubForm(MassiveAction $ma) {
    global $CFG_GLPI;
    $entity_id='';

    foreach($_SESSION['glpiactiveentities'] as $id => $idx) {
	$entity_id=$id;
        break;
    }
      switch ($ma->getAction()) {
      case 'plugin_nagios_host_setparent':
        $p=array();
        $p['name']="parent_objects_id";
        $p['condition']=" `type`='".self::getTypeForClass(get_called_class())."' and is_model=1";
        $p['addicon']=false;
        $p['comments']=false;
        $p['entity']=PluginNagiosObject::getRecursiveEntities($entity_id);

	
	echo "<div class='borderdiv'>";
	echo "<table class='tab_cadre_fixe' style='width:250px'>";
        echo "<tr><td>";
        echo __("As child as").":";
        echo "</td><td>";
        self::Dropdown($p);
        echo "</td></tr></table>";
        echo "</div>";
        
        break;
      case 'plugin_nagios_host_enabledisable':
        echo "<div class='borderdiv'>";
        echo "<table class='tab_cadre_fixe' style='width:350px'>";
        echo "<tr><th>";
         echo __('Enable/Disable monitoring:');
        echo "</th><td>";
        Dropdown::showFromArray("is_disabled",array("0"=>_("Enable"),"1"=>_("Disable")), array('value'=>'0')) ;
        echo "</td></tr></table>";
        echo "</div>";
        echo "<br>";
  
         break;
      case 'plugin_nagios_host_setfield':
         echo "&nbsp;";
	 $used_fields=array();

         $js_options=array('root_doc' => $CFG_GLPI['root_doc'],'entity_id'=>$entity_id);

         echo "<script type='text/javascript'>";
         echo "var nagios = $(document).nagios(".json_encode($js_options).");";
         echo "</script>";

         PluginNagiosField::Dropdown(array("comments"=>false,"used"=>$used_fields,"name"=>"addField","condition"=>"object_type='HT'") );

         $onclick="nagios.getFieldForm($('input[name*=addField]').val(),'list_fields');";
         echo "&nbsp;<input type='button' class='submit' value='".__('Add')."' onclick=\"$onclick\" />";
         $rand=rand();
         echo "<div class='borderdiv'>";

         echo "<table class='tab_cadre_fixe' id='list_fields'>";
         echo "<tr>";
         echo "<th width='170px' style='text-align:left;color:#f0431a' >"._("Parameters")."</th>";
         echo "<th width='15px'></th><th  style='text-align:left;color:#f0431a' >"._("Values")."</th>";
         echo "</tr>";
         echo "</table>";
	 echo "</div>";
 
         break;
      case 'plugin_nagios_host_purge':
        echo "<div class='borderdiv'>";
        echo "<table class='tab_cadre_fixe' style='width:250px'>";
        echo "<tr><th>";
         echo __('Confirm the final deletion?');
        echo "</th><td>";
        Dropdown::showFromArray("confirm_delete",array("1"=>__("Yes"),"0"=>__("No")), array('value'=>'0')) ;
        echo "</td></tr></table>";
        echo "</div>";
        echo "<br>";
         break;
      case 'plugin_nagios_host_clone':
	 echo "<div class='borderdiv'>";
         echo "<table class='tab_cadre_fixe' style='width:300px'>";
	 echo "<tr class='tab_bg_1'><th colspan=3>Indicate Prefix and Suffix:</th></tr>";
	 echo "<tr>";
	 echo "<td><input type='text' size=6 name='prefix' value=''></td>";
	 echo "<td>".__('ItemName')."</td>";
	 echo "<td><input type='text' size=6 name='suffix' value='_clone'></td>";
	 echo "</tr></table>";
         echo "</div>";
         echo "<br>";
         break;
      case 'plugin_nagios_host_setrole':
        $p=array();
        $p['name']="role_id";
        $p['condition']=" `type`='RO' and is_model=1";
        $p['entity']=PluginNagiosObject::getRecursiveEntities($entity_id);
        $p['addicon']=false;
        $p['comments']=false;
	echo "<div class='borderdiv'>";
	echo "<table class='tab_cadre_fixe' style='width:150px'>";
        echo "<tr><td>";
	PluginNagiosRole::Dropdown($p);
	echo "</td><td>";
	Dropdown::showFromArray("action_link",array("link"=>__("Add"),"unlink"=>__("Remove")), array('value'=>'link')) ;
	echo "</td></tr></table>";
	echo "</div>";
        break;
       case 'plugin_nagios_host_addfield':
       case 'plugin_nagios_host_removefield':
	 $used_fields=array();
         echo "&nbsp;";

         $js_options=array('root_doc' => $CFG_GLPI['root_doc'],'entity_id'=>$entity_id);

         echo "<script type='text/javascript'>";
         echo "var nagios = $(document).nagios(".json_encode($js_options).");";
         echo "</script>";

         PluginNagiosField::Dropdown(array("comments"=>false,"used"=>$used_fields,"name"=>"addField","condition"=>"object_type='HT' and field_flag='M'") );

         $onclick="nagios.getFieldForm($('input[name*=addField]').val(),'list_fields');";
         echo "&nbsp;<input type='button' class='submit' value='".__('Add')."' onclick=\"$onclick\" />";
         $rand=rand();
         echo "<div class='borderdiv'>";

         echo "<table class='tab_cadre_fixe' id='list_fields'>";
         echo "<tr>";
         echo "<th width='170px' style='text-align:left;color:#f0431a' >"._("Parameters")."</th>";
         echo "<th width='15px'></th><th  style='text-align:left;color:#f0431a' >"._("Values")."</th>";
         echo "</tr>";
         echo "</table>";
         echo "</div>";


	  break;
       case 'plugin_nagios_host_import':
	 echo "<div class='borderdiv'>";
         echo "<table class='tab_cadre_fixe' style='width:150px'>";
         echo "<tr><td><input type='file' name='filetoimport'></td></tr>";
	 echo "</table>";
	 echo "</div>";


      
   }

   return parent::showMassiveActionsSubForm($ma);




}

 function setInconsistentService($host) {

	

 }


  static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,array $ids) {
   
   $input = $ma->getInput();
   switch ($ma->getAction()) 
    {
      case 'plugin_nagios_host_enabledisable':
      case 'plugin_nagios_host_setparent':
              
        $input = $ma->getInput();
         
        foreach ($ids as $id) {
                            
	
              $canUpdate=1;

              if (in_array($item->getType(),array("Computer","NetworkEquipment","Printer"))) {
		$nagios_host=PluginNagiosObjectLink::getHostForItem( $id, $item->getType() ) ;
              } else {
                $nagios_host=new PluginNagiosObject;
                $nagios_host->getFromDB($id);
              }
      
              $input['id']=$nagios_host->fields['id'];

              if ($ma->getAction()=='plugin_nagios_host_setparent') {
			$services=PluginNagiosObjectLink::getInconsistentServiceForHost($nagios_host,$input['parent_objects_id']);
			if (count($services)) {
				$ma->addMessage("Item ".$nagios_host->fields['name'].":Can't update herited service ");
				$canUpdate=0;
			}
	      }


              if ($canUpdate && $nagios_host->update($input)) {
                 $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
                 //$ma->itemDone(self::getClassForType($nagios_host->fields['type']), $id, MassiveAction::ACTION_OK);
              } else {
                 $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
              }
          }
         break;
       case 'plugin_nagios_host_setfield':
          $input = $ma->getInput();
          foreach ($ids as $id) {

            if (in_array($item->getType(),array("Computer","NetworkEquipment","Printer"))) {
                $nagios_host=PluginNagiosObjectLink::getHostForItem( $id, $item->getType() ) ;
              } else {
                $nagios_host=new PluginNagiosObject;
                $nagios_host->getFromDB($id);
              }


            foreach($input['field_id'] as $idx => $field_id ) {

              if (!isset($_POST["field_$field_id"]))
                $input["field_$field_id"]='';

               $field_value=$input["field_$field_id"];
               (is_array($field_value)) ? $input['value']=implode('$#$',$field_value) : $input['value']=$field_value;
               $input['plugin_nagios_fields_id']=$field_id;
               $input['plugin_nagios_objects_id']=$nagios_host->getID();

               if (isset($input['flag_'.$field_id]) && $input['flag_'.$field_id]!='')
                 $input['flag']=$_POST['flag_'.$field_id];

               $pv=new PluginNagiosObjectValue;
               $pv->getFromObjectIds($nagios_host->getID(),$field_id);

               if (isset($pv->fields['id'])) {
                 $input['id']=$pv->getID();

                 if (!$input['value']) {
                   $pv->delete($input);
                 } else {
                   $pv->update($input);
                 }
               } else {
                unset($input['id']);
                if ($input['value']) {
                  $pv->add($input);
                }
               }
           }
	   $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
        }
        break;
       case 'plugin_nagios_host_removefield':
       case 'plugin_nagios_host_addfield':
	  $input = $ma->getInput();
          foreach ($ids as $id) {

            if (in_array($item->getType(),array("Computer","NetworkEquipment","Printer"))) {
                $nagios_host=PluginNagiosObjectLink::getHostForItem( $id, $item->getType() ) ;
              } else {
                $nagios_host=new PluginNagiosObject;
                $nagios_host->getFromDB($id);
              }


            foreach($input['field_id'] as $idx => $field_id ) {

              if (!isset($_POST["field_$field_id"]))
                $input["field_$field_id"]='';

               $field_value=$input["field_$field_id"];
               
	       $input['plugin_nagios_fields_id']=$field_id;
               $input['plugin_nagios_objects_id']=$nagios_host->getID();

               if (isset($input['flag_'.$field_id]) && $input['flag_'.$field_id]!='')
                 $input['flag']=$_POST['flag_'.$field_id];

               $pv=new PluginNagiosObjectValue;
               $pv->getFromObjectIds($nagios_host->getID(),$field_id);

	       if (isset($pv->fields['value']))
		   $Avalue=explode('$#$', $pv->fields['value']);
 	       else
		 $Avalue=array();

	       if ($ma->getAction()=='plugin_nagios_host_addfield') {
  	         foreach($field_value as $idx=>$val) {
		   if (!in_array($val,$Avalue))
		     $Avalue[]=$val;
	       	 }
	       } else {
		      $ANewvalue=array();
		      foreach($Avalue as $idx=>$val) {
			if (!in_array($val,$field_value)) {
				$ANewvalue[]=$val;	

			}


		      }
		      $Avalue=$ANewvalue;
	       }

	       $input['value']=implode('$#$',$Avalue);

               if (isset($pv->fields['id'])) {
                 $input['id']=$pv->getID();

                 if (!$input['value']) {
                   $pv->delete($input);
                 } else {
                   $pv->update($input);
                 }
               } else {
                unset($input['id']);
                if ($input['value']) {
                  $pv->add($input);
                }
               }
           }
           $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
        }

	 break;


       case 'plugin_nagios_host_clone':
	 foreach ($ids as $id) {
           $cloned=self::cloneItem($id,$_POST['suffix'],$_POST['prefix']);
	   if ($cloned)
		$ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
	   else
		$ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);
         }
         break;
       case 'plugin_nagios_host_setrole':
	     if (!$input['role_id'])
		return ;
           
            $use_glpi_id=false;
           
            if (in_array($item->getType(),array("Computer","NetworkEquipment","Printer"))) {
		$list_ids=PluginNagiosObjectLink::getNagiosIDForType($ids,$item->getType() );
		$use_glpi_id=true;
	    } else {
		$list_ids=$ids;
		$use_glpi_id=false;
	    }
 
            foreach($list_ids as $glpi_id=>$nagios_id) {
              if ($input['action_link']=='link') {
                $res=PluginNagiosObjectLink::addRole($nagios_id,$input['role_id']);
                //$ma->addMessage("Item or ParentItem already use this Supervision Template" );
                                              
              } else {
                $res=PluginNagiosObjectLink::removeRole($nagios_id,$input['role_id']); 
              }
              
              if ($res) {
                if ($use_glpi_id)
                    $ma->itemDone($item->getType(),$glpi_id,MassiveAction::ACTION_OK);
                else 
                    $ma->itemDone($item->getType(),$nagios_id,MassiveAction::ACTION_OK);                   
              } else {
                if ($use_glpi_id)
                    $ma->itemDone($item->getType(),$glpi_id,MassiveAction::ACTION_KO);
                else 
                    $ma->itemDone($item->getType(),$nagios_id,MassiveAction::ACTION_KO);  
              }
             
                         
            }
 	   break;
       case 'plugin_nagios_host_purge':
         foreach ($ids as $id) {
           $o=new PluginNagiosObject();
           if ($input['confirm_delete']) {
           	$o->delete(array("id"=>$id),1);
 		$ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
           } else {
                $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_KO);

	   }
         }
         break;
       case 'plugin_nagios_host_trash':
         foreach ($ids as $id) {
           $o=new PluginNagiosObject();
           $o->delete(array("id"=>$id),0);
           $ma->itemDone($item->getType(), $id, MassiveAction::ACTION_OK);
         }
         break;


       default:
         parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
         break;
       }

  }





  function showForm($ID,$options=array()) {
     
 
      $this->initForm($ID, $options);
      $this->showFormHeader($options);
     
 
      echo "<tr class='tab_bg_1'>";
      if ($this->getID() and static::$nagios_type=='ST' and (!$this->fields['is_model']))
        echo " <td>" . _("ServiceName") ."</td><td >".Html::input("name",array('readOnly'=>true,'style'=>'color:#565656','value'=>$this->fields['name']))."</td>";
      else 
	echo " <td>" . _("Name") ."</td><td>".Html::input("name",array('value'=>$this->fields['name']))."</td>";

      echo Html::hidden("type",array('value'=>static::$nagios_type));
      echo Html::hidden("id"  ,array('value'=>$this->fields['id']));

      if ($ID>1)
        echo Html::hidden("is_model"  ,array('value'=>$this->fields['is_model']));
      else
	echo Html::hidden("is_model"  ,array('value'=>1));


      if (!isset($options['with_entity']))
	$options['with_entity']=-1;
 
      if (!$this->fields['is_model'])
	echo "<td>".Html::hidden("entities_id",array("value"=>$this->fields['entities_id']))."</td>";
      else { 
        echo " <td>"._("Entity")."*</td><td>";
        Entity::Dropdown(array('entity'=>$options['with_entity'],'value'=>$this->fields['entities_id']));
        echo " </td>";
      }
      echo "</tr>";

      if (static::$nagios_type!='RO') {
        echo "<tr>";
        if (static::$nagios_type=='ST' and $this->fields['parent_objects_id'] ) {
	  $parent=new PluginNagiosService();
	  $parent->getFromDB($this->fields['parent_objects_id']);
       
          if ($parent->fields['is_model']==0) {
            echo "<td colspan=2'>";
	    echo _("Duplicated service")." [".Html::link($parent->fields['alias'],Toolbox::getItemTypeFormURL("PluginNagiosService",1)."?_in_modal=1&from_host=0&id=".$parent->fields['id'])."]";
	    echo "</td>";
          } else {

           echo " <td>"._("Primary template")."</td>";
           echo " <td>";
           $p['name']='parent_objects_id';
           $p['value']=$this->fields['parent_objects_id'];
           $p['condition']=" id<>'{$this->fields['id']}' and `type`='".static::$nagios_type."' and is_model=1";
           $p['entity']=PluginNagiosObject::getRecursiveEntities($this->fields['entities_id']);
           self::Dropdown($p);
           echo " </td>";

	  }
        } else {

          if(!get_class($this) == "PluginNagiosServiceGroup"){
            
          echo " <td>"._("Primary template")."</td>";
          echo " <td>";
          $p['name']='parent_objects_id';
          $p['value']=$this->fields['parent_objects_id'];
          $p['condition']=" id<>'{$this->fields['id']}' and `type`='".static::$nagios_type."' and is_model=1";  
          $p['entity']=PluginNagiosObject::getRecursiveEntities($this->fields['entities_id']);
          self::Dropdown($p);
          echo " </td>";
          }

        }
        echo "</tr>";
      } 
      echo "<tr>";
      echo " <td>"._("Alias")."</td>";
      echo " <td>";
      echo Html::input("alias",array('value'=>$this->fields['alias'],'size'=>50));
      echo " </td>";
      echo "</tr>";

      echo "<tr>";
      echo " <td>"._("Description")."</td>";
      echo " <td>";
      echo Html::input("desc",array('value'=>$this->fields['desc'],"size"=>"80"));
      echo " </td>";
      echo "</tr>";	

      if ($this->canUpdate()) 
	 $this->showFormButtons($options);
      return true;
  }   
 

  function cleanDBonPurge() {

     global $DB;
     if (!isset($this->fields['id']) or !$this->fields['id'])
	return ;

     $id=$this->fields['id'];

     /* for all object */
     $DB->query("delete from glpi_plugin_nagios_macros where plugin_nagios_objects_id='$id'");
     $DB->query("delete from glpi_plugin_nagios_objectvalues where plugin_nagios_objects_id='$id'");
 
     /* delete custom services Host and Role */
     $DB->query("update glpi_plugin_nagios_objects set parent_objects_id=0 where parent_objects_id in (select items_id from glpi_plugin_nagios_objectlinks where plugin_nagios_objects_id='$id' and itemtype='PluginNagiosService') and is_model=0");
     $DB->query("delete from glpi_plugin_nagios_objects where id in (select items_id from glpi_plugin_nagios_objectlinks where plugin_nagios_objects_id='$id' and itemtype='PluginNagiosService') and is_model=0");
     $DB->query("delete from glpi_plugin_nagios_objectlinks where (plugin_nagios_objects_id='$id' or items_id='$id') and itemtype like 'PluginNagios%' ");
     $DB->query("update glpi_plugin_nagios_objects set parent_objects_id=null where  parent_objects_id=$id ");
  }
 
  function processForm($params) {

    if (isset($_POST['add'])) {
          unset($params['id']);
	  $this->add($params);
    } else if (isset($_POST['update'])) {
	  $this->update($params);
	
    } else if (isset($_POST['delete']) ) {
          $this->delete($params);

    } else if (isset($_POST['purge'])) {
          $this->delete($params,1);
    } else if  (isset($_POST['save_opts'])) {
 
      /* save host general information */
      $this->update($_POST);

      /* save field */ 
      foreach($_POST['field_id'] as $idx => $field_id ) {
    
        if (!isset($_POST["field_$field_id"]))
          $_POST["field_$field_id"]='';
   
        $field_value=$_POST["field_$field_id"];
        
        if (is_array($field_value)) {
          if ($field_value[0]){
            $input['value']=implode($field_value,'$#$');
          } else {
            $input['value']='';
          }
        } else {
          $input['value']=$field_value;
        }
                  
 
        $input['plugin_nagios_fields_id']=$field_id;
        $input['plugin_nagios_objects_id']=$_POST['id'];
    
        if (isset($_POST['flag_'.$field_id]) && $_POST['flag_'.$field_id]!='')
          $input['flag']=$_POST['flag_'.$field_id];
    
        $pv=new PluginNagiosObjectValue;
        $pv->getFromObjectIds($_POST['id'],$field_id);

        if (isset($pv->fields['id'])) {
          $input['id']=$pv->getID();
    
          if ((!$input['value'] && strlen($input['value'])==0) or ($input['value']=='[:EMPTY:]') ) {
            $pv->delete($input);
          } else {
            $pv->update($input);
          }
        } else {
          unset($input['id']);
          
          if (strlen($input['value'])>0 and $input['value']!='[:EMPTY:]' ) {
            $pv->add($input);
          }
        }
      } //end foreach
  } //end save_opts
} //end process form

} //end of class
