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


class PluginNagiosField extends CommonDropdown {

   static $rightname='plugin_nagios';


   static function getTable($classname=null) {
	return "glpi_plugin_nagios_fields";
   }   

   static function canView() {
	return true;
   }
 
   static function canUpdate() {
        return true;
   }
 
   static function canCreate() {
        return true;
   }

   static function canPurge() {
        return true;
   }


   static function getTypeName($nb=0) {
	 return _n("Field","Fields",$nb, "nagios");
   }

   function getItemType() {
	if (!isset($this->fields['field_value']))
		return null;

	list($data_type,$data_itemtype)=explode(':',$this->fields['field_value']);
	
	return $data_itemtype;
   }



   static function getMergedFields($nagios_type) {
      global $DB;
        $f=new PluginNagiosField;

        return $f->find("field_flag='M' and object_type='$nagios_type'");
	

   }     

 
   static function getFieldsByName($nagios_type,$names=array()) {
	global $DB;
	$f=new PluginNagiosField;	


	if (!is_array($names)) {
		$f->getFromDBByQuery("WHERE object_type='".$nagios_type."' and name='$names' LIMIT 1");

		return $f;
	} else {

        	return $f->find(" object_type='".$nagios_type."' and name in ('".implode("','",$names) ."') ");
        }

   }
 
   static function showInput($id,$options=array()) {
	
	$field=new PluginNagiosField;	
	if (!isset($options['linkID']))
		$options['linkID']='';

	$field->getFromDB($id);
	$options['name']="field_$id";
	echo "<tr class='tr_disp_1'><td style=\"border-top:1px solid #e2e2fa;text-align:left;vertical-align:top;width:170px\"><b>";

        /* display name or altname */
        echo $field->fields['display_name'] ? $field->fields['display_name'] : $field->fields['name'] ;

	echo "</td><td style=\"border-top:1px solid #e2e2fa;\">";
        /* display inheritance option */
        if ($field->fields['field_flag']!='N') {
          array(''=>'','m'=>'+','i'=>'!');
          echo "<div style='display:inline;'><select style='-webkit-appearance: none;border:none;box-shadow: none;' name='flag_{$id}'>";
          foreach(array(''=>'','m'=>'+','i'=>'!') as $idx => $val) {
             if (isset($options['flag']) && $options['flag']==$idx)
                 $selected="selected";
             else
                $selected="";
             echo "<option $selected value='$idx'>$val</option>";
          }
          echo "</select></div>&nbsp;";
        }

	echo Html::hidden("field_id[]",array("value"=>$id)).Html::hidden("linkID[]",array("value"=>$options['linkID']));
        
        echo "</td>";

	echo "<td style=\"border-top:1px solid #e2e2fa;\">";
	switch ($field->fields['field_type']) {
	
		case 'TEXT':
			$field->showInputText($options);
			break;
		case 'NUMBER':
			$field->showInputNumber($options);
                        break;
		case 'LIST':
			$field->showInputList($options);
			break;
                case 'LIST_MULT':
                        $field->showInputListMultiple($options);
                        break;
		case 'LIST_ORDR':
			$field->showInputListOrdr($options);
			break;
		case 'CHOICE':
			$field->showInputChoice($options);
			break;
                case 'SPECIAL':
		    switch ($field->fields['name']) {
		       case 'check_command':
			  $field->showInputCheckCommand($options);
			  break;
		       case 'address':
			  $field->showInputAddresses($options);
			  break;
                    } 
  	}                 
      

       $herited="";

 

        if (isset($options['herited_value']))
                $herited="&nbsp;&nbsp;<div class='div_result' style='display:inline;float:right;width:350px;padding:2px' ><b>From Parent(s):<br><span style='color:#bd4504'><i>{$options['herited_value']}</i></span></div>";
	echo "$herited</td></tr>";
   }



   function showInputAddresses($options) {
       global $DB;


       $query="SELECT ip.name from glpi_ipaddresses ip where ip.mainitems_id=".$options['item_glpi']->getID()." and ip.mainitemtype='".$options['item_glpi']->getType()."' and ip.is_deleted=0";

       echo "<select name='{$options['name']}'>";
       echo "<option value=''>----</option>";
       foreach ($DB->request($query) as $data) {
	    ( isset($options['value']) && $options['value']==$data['name']) ? $selected="selected":$selected="";

	    echo "<option $selected value='".$data['name']."'>".$data['name']."</option>";
       }

	echo "</select>";


   }



   function showInputCheckCommand($options) {
        global $CFG_GLPI; 
       $saved_options=$options;
       $cmd_id=0;
       $cmd_args=""; 
       $rand=mt_rand();

       if (isset($options['value']) && $options['value']) {
	  $a=explode('$#$',$options['value'].'$#$');
          $cmd_id=$a[0];
          $options['value']=$cmd_id;
       }

       $js_options=array('root_doc' => $CFG_GLPI['root_doc'],'entity_id'=>0);
       echo "<script type='text/javascript'>";
       echo "var nagios= $(document).nagios(".json_encode($js_options).");";
       echo "</script>";



       echo "<div id='check_command_$rand'>";
	//display command list
       $dropdown=new PluginNagiosCommand();
       if (isset($options['entity']))
             $options['entity']=PluginNagiosObject::getRecursiveEntities($options['entity']);
       $options['comments']=true;
       $options['on_change']="nagios.get_check_command_form(this.value,'".$this->fields['id']."','$cmd_args','check_command_args_$rand');";
       $options['name'].='[]';

       $dropdown->dropdown($options) ;
//       Dropdown::showFromArray("dddd",array(10=>"MACRO1",11=>"MACRO2"),array("other"=>"13","size"=>5)) ;
       echo "<div class='div_nagios' id='check_command_args_$rand'>";
       if ($cmd_id) {
	  $co=new PluginNagiosCommand;
	  $co->getFromDB($cmd_id);
	  $nbargs=$co->getNbArgs();
	  $params['cols']=100;
	  $params['rows']=1 ;
	  for ($i=1;$i<=$nbargs;$i++)  {
		if (isset($a[$i]))
			$params['value']=$a[$i];
		else
			$params['']="";
		$params['name']=$options['name'];
		echo "&nbsp;ARGS$i<br>";
		Html::textarea($params);
		echo "<br>";

	  }
	
       } 
	 
	
       echo "</div></div>"; 

   }


   function showInputText($options) {
        
        if ($this->fields['field_style']) {          
         $a=explode("::",$this->fields['field_style']);
         foreach($a as $val) {
	 	list($key,$attr)=explode("=",$val);
                $options[$key]=$attr;
  	 }
        } 
	echo Html::input( $options['name'] , $options );
   }

   function showInputNumber($options) {
                  
	$options['size']=4;
	$options['item_glpi']='';
        $options['item_nagios']='';
        echo Html::input( $options['name'] , $options );
   }

   function showInputListMultiple($options) {
   
        if (substr($this->fields['field_value'],0,2)!='_O') {
                $data=explode(':',$this->fields['field_value']);
                $selected="";
                foreach($data as $val)
                  $elmts[$val]=$val;
                  
                $options['multiple']=true;
		if (isset($options['value']))
                	$options['values']=explode("$#$",$options['value']);
                
		$options['width']='220px';
                
                unset($options['value']);
                echo "<div class='borderdiv'>";
                Dropdown::showFromArray($options['name'],$elmts,$options);
                echo "</div>";
                
        }
   
   
   }
   

   function showInputList($options) {
	global $DB;

	

	if (substr($this->fields['field_value'],0,2)!='_O') {
		$data=explode(':',$this->fields['field_value']);
		$selected="";
	        echo "<select name='{$options['name']}'>";
	        echo " <option value=''>----</option>";
        	foreach($data as $idx => $val) {
                	if (isset($options['value']))
                        ($options['value']==$val) ? $selected="selected":$selected="";
                	echo "<option $selected value='$val'>$val</option>";    
       		 }
        	echo "</select>";

        } else if (substr($this->fields['field_value'],0,2)=='_O')  {
	 	if (count($this->fields['field_value'])>=3)
			list($data_type,$data_itemtype,$data_cond)=@explode(':',$this->fields['field_value']);
		else
			list($data_type,$data_itemtype)=@explode(':',$this->fields['field_value']);
		$dropdown=getItemForItemType($data_itemtype);
   	        if ($dropdown instanceof PluginNagiosObject) { 
                    $options['condition']="type='".$dropdown::$nagios_type."' and is_model=1";
		} 
		

		if (isset($options['entity']))
                        $options['entity']=PluginNagiosObject::getRecursiveEntities($options['entity']);
                $options['others']=true;
                $dropdown->dropdown($options) ; 		
		
        } 

	
	
   }

   function showInputListOrdr($options) {
	list($data_type,$data_itemtype)=explode(':',$this->fields['field_value']);

	$dropdown=getItemForItemType($data_itemtype);
	if ($dropdown instanceof PluginNagiosObject) {
	    $options['condition']="type='".$dropdown::$nagios_type."' ";

	    if ($this->fields['name']!='parents')
		$options['condition'].=" AND is_model=1";
	    else
		$options['condition'].=" AND is_model=0";
  	    //$options['comments']=false; 
	    $options['entity_sons']=false;
            if ($options['entity']) 
	      $options['entity']=PluginNagiosObject::getRecursiveEntities($options['entity']);
	}	
	if ($dropdown instanceof User) {
	  $options['all']='0';
	  $options['right']='all';
	} 

	$used=array();
	$aused=array();
	if (isset($options['value'])){
    if ($options['value']) {
      if(strpos($options['value'], "$#$") !== false){
        $aused=explode("$#$",$options['value']); 
        $used=$dropdown->find(' id in ('.str_replace('$#$',',',$options['value']).')');
        unset($options['value']);    
      }else{
        $aused=explode(":",$options['value']); 
        $used=$dropdown->find(' id in ('.str_replace(':',',',$options['value']).')');
        unset($options['value']);    
      }

    }
  }

	
       	
	$suffix='field_'.$this->fields['id'];

	echo "<div id='div_$suffix'   style='padding-left:-20px;' class='borderdiv'>";
	echo "<script>var $suffix=$(document).listordr('{$this->fields['id']}');</script>";
	unset($options['name']);
	$dropdown->dropdown($options) ;
	echo "<input type='button' class='submit' value='"._('Add')."' onclick=\"$suffix.add();\" />";
	echo "</td></tr>";
	echo "<tr><td colspan=2></td><td  >";
	/* avaiable object */
	
	echo "<table id='tbl_$suffix' class='div_result' >";
        while (list($idx,$id)=each($aused)) {
		echo "<tr id='tr_{$suffix}_{$id}'>";
		echo "<td><div class='move-del' onclick=\"$suffix.remove('{$id}');\" /></td>";	
		echo "<td><input type='hidden' name='{$suffix}[]' value='{$id}'/>{$used[$id]['name']}</td>";
                echo "<td><div class='move-up'  onclick=\"$suffix.up('{$id}');\" /></td>";
                echo "<td><div class='move-down'  onclick=\"$suffix.down('{$id}');\" /></td>";
		echo "</tr>";
	}	
	echo "</table>";

	echo "</div>";
   }

   function showInputChoice($options) {
       /*
        if (!isset($options['value'])) 
	  $options['value']=$this->fields['field_value'];
        */
        $options['emptychoice']=true;      
        Dropdown::showFromArray($options['name'],array('[:EMPTY:]'=>'-----','0'=>__('No'),'1'=>__("Yes")),$options);
        

   }

   function showForm($ID, $options = array() ) {

       $this->initForm($ID, $options);
       $this->showFormHeader($options);
  
      echo "<tr class='tab_bg_1'>";
      echo " <td>" . _("Name") ."*</td>";
      echo " <td>";
      echo Html::hidden("id"  ,array('value'=>$this->fields['id']));
      echo Html::input("name",array('value'=>$this->fields['name']));
      echo " </td>";

	
      echo " <td>"._("Libel")."</td>";
      echo " <td>";
      echo Html::input("display_name"  ,array('value'=>$this->fields['display_name']));
      echo " </td>";
      echo "</tr>";

      echo "<tr>";
      echo " <td>"._("Nagios Type")."*</td>";
      echo " <td>"; 
      echo Html::input("object_type",array('value'=>$this->fields['object_type']));
      echo " </td>";
      echo " <td>"._("HTML Options")."</td>";
      echo " <td>";
      echo Html::input("field_style",array('value'=>$this->fields['field_style']));
      echo " </td>";

      echo "</tr>";

      echo "<tr>";
      echo " <td>"._("Field Type")."*</td>";
      echo " <td>";
      echo Html::input("field_type",array('value'=>$this->fields['field_type']));
      echo " </td>";
      echo "</tr>";
  
      echo "<tr>";
      echo " <td>"._("Value")."</td>";
      echo " <td>";
      echo Html::input("field_value",array('value'=>$this->fields['field_value']));
      echo " </td>";
      echo "</tr>";

      echo "<tr>";
      echo " <td>"._('Flag')."</td>";
      echo " <td>";
      echo Html::input("field_flag",array('value'=>$this->fields['field_flag']));
      echo " </td>";
      echo "</tr>";

      $this->showFormButtons($options);
      return true;


 

   }


   function getSearchOptions() {
      $tab = array();
      $tab['common'] = static::getTypeName();
      $tab[1]['table']     = $this->getTable();
      $tab[1]['field']     = 'name';
      $tab[1]['name']      = _('Name');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['itemlink_type'] = 'PluginNagiosField';

      $tab[2]['table']     = $this->getTable();
      $tab[2]['field']     = 'display_name';
      $tab[2]['name']      = _("Libel");
   
      $tab[3]['table']     = $this->getTable();
      $tab[3]['field']     = 'object_type';
      $tab[3]['name']      = _("Nagios Type");

      $tab[4]['table']     = $this->getTable();
      $tab[4]['field']     = 'field_type';
      $tab[4]['name']      = _("Type");
 
  
      $tab[5]['table']     = $this->getTable();
      $tab[5]['field']     = 'field_value';
      $tab[5]['name']      = _("Value");

      $tab[6]['table']     = $this->getTable();
      $tab[6]['field']     = 'field_flag';
      $tab[6]['name']      = _("Flags");


      return $tab;

  }
}
