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


class PluginNagiosMacro extends CommonDBTM {

   static $rightname='plugin_nagios';


   static function getTable($classname=null) {
	return "glpi_plugin_nagios_macros";
   }   



   static function getTypeName($nb=0) {
      return _n("Macro","Macros",$nb, "nagios");
   }

   static function getObjectMacro($object_id,$condition="") {
      global $DB;

      $macros = array();
      $query  = "SELECT `".self::getTable()."`.*
                 FROM `glpi_plugin_nagios_macros` WHERE is_global='0' ";

      if (!is_array($object_id)) 
	 $query.=" AND `".self::getTable()."`.`plugin_nagios_objects_id` in ( '$object_id' )";
      else
	 $query.=" AND `".self::getTable()."`.`plugin_nagios_objects_id` in ('".implode("','", $object_id )."')";

      if (!empty($condition)) {
         $query .= " AND $condition ";
      }
      $query.=" ORDER BY `".self::getTable()."`.`name`";
      foreach ($DB->request($query) as $data) {
         $macros[$data['id']] = $data;
      }
      return $macros;

   }


   static function getEntityMacro($entity_id) {
      global $DB;

      $macros = array();
      $query  = "SELECT `".self::getTable()."`.*
                 FROM `glpi_plugin_nagios_macros` WHERE is_global='1' ";

      if (!is_array($entity_id))
         $query.=" AND `".self::getTable()."`.`plugin_nagios_objects_id` in ( '$entity_id' )";
      else
         $query.=" AND `".self::getTable()."`.`plugin_nagios_objects_id` in ('".implode("','", $entity_id )."')";

      if (!empty($condition)) {
         $query .= " AND $condition ";
      }
      $query.=" ORDER BY `".self::getTable()."`.`name`";
      foreach ($DB->request($query) as $data) {
         $macros[$data['id']] = $data;
      }
      return $macros;



   }





   static function showNagiosDef($item,$options=array()) {


    if (isset($options['html']) && $options['html']==1) {
         $start_tag="<font style='font-weight:bold;color:#FF8888'>";
         $end_tag="</font>";
     } else {
         $start_tag=$end_tag="";
     }



     $buf="";
     $macros=self::getHeritedMacro($item);
     
     $canView=Session::haveRight("plugin_nagios_admin", READ);
     foreach ($macros as $macro_name => $macro) {
        if ($canView or !$macro['is_secure'] ) 
    $buf.=" {$start_tag}_".$macro['name']."$end_tag ".html_entity_decode($macro['value'])."\n";
	else
	  $buf.=" {$start_tag}_".$macro['name']."$end_tag ********\n";
		
     }

     return $buf;
   }


   static function showForEntity($entity_id,$options=array()) {

      $IDNO = $entity_id;
      $rand=mt_rand();
      if (self::canCreate()) {
        $macro   = new PluginNagiosMacro();
        $macro->showForm("",array('with_object_id'=>$entity_id,'is_global'=>'1'));
      }

      $macros=self::getEntityMacro($entity_id);


      $canedit = true;
      if ($canedit && count($macro)) {
         Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
         $massiveactionparams = ['container' => 'mass'.__CLASS__.$rand];
         Html::showMassiveActions($massiveactionparams);
      }

      echo "<table class='tab_cadre_fixe' >";
      
      $header_begin  = "<tr>";
      $header_top    = '';
      $header_bottom = '';
      $header_end    = '';
      if ($canedit && count($macro)) {
         $header_top    .= "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
         $header_top    .= "</th>";
         $header_bottom .= "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
         $header_bottom .= "</th>";
      }
      $header_end .= "<th>".__('Name')."</th>";
      $header_end .= "<th>".__('Value')."</th>";
      $header_end .= "<th>".__('Secure')."</th>";
      $header_end .=" <th>".__('Comments')."</th>";

      echo "<tr>";
      echo $header_begin.$header_top.$header_end;

      foreach ($macros as $macro_name => $macro) { 

        $srand=mt_rand();
        echo "<tr>";

        if ($canedit) {
          echo "<td width='10'>";
          Html::showMassiveActionCheckBox(__CLASS__, $macro["id"]);
          echo "</td>";
        } else{
          echo "<td width='10'>";
          echo "</td>";
        }


        if ($macro['plugin_nagios_objects_id']==$IDNO) {
            echo "<td>";
            if (!$macro['is_secure'] or Session::haveRight("plugin_nagios_admin",1)) {
              $url=Toolbox::getItemTypeFormURL('PluginNagiosMacro',1)."?id=".$macro['id']."&plugin_nagios_objects_id=$IDNO";
              Ajax::createIframeModalWindow("plugin_nagios_macro_modal_$srand", $url ,array("reloadonclose"=>true,'width'=>1250) ) ;
              echo " <a href='#' onclick=\"$('#plugin_nagios_macro_modal_$srand').dialog('open');\">{$macro['name']}</a>";
              echo "</td>";
            } else {
             echo "{$macro['name']}</td>";
            }

          } else {
            echo "<td></td>";
            echo "<td>";
            $url=Toolbox::getItemTypeFormURL("PluginNagiosHost",1)."?id=".$macro['plugin_nagios_objects_id']."&with_tab=1&tabnum=20";
                  Ajax::createIframeModalWindow("plugin_nagios_host_modal_$srand", $url ,array("reloadonclose"=>true,'width'=>1250) ) ;
                  echo "<a href='#' onclick=\"$('#plugin_nagios_host_modal_$srand').dialog('open');\">{$macro['name']}</a>";
            echo "</td>";
          }

          if (Session::haveRight("plugin_nagios_admin", READ) or !$macro['is_secure'] ) {
            echo " <td>".$macro['value']."</td>";
          } else {
            echo " <td>*********</td>";
          }
            echo " <td>";
            Dropdown::showYesNo('is_secure[]',$macro['is_secure'],-1,array('readonly'=>1));
            echo "</td>";

            echo "<td>";
            echo $macro["comments"];
            echo "</td>";


            echo "</tr>";
      }

      if (count($macro)) {
         echo $header_begin.$header_bottom.$header_end;
      }

      echo "</table>";
      if ($canedit && count($macro)) {
         $massiveactionparams['ontop'] = false;
         Html::showMassiveActions($massiveactionparams);
         //Html::closeForm();
      }


   }

   /*show macro for PluginNagiosObject */
   static function showForObject(PluginNagiosObject $item,$options=array()) {
  
      $IDNO = $item->fields['id'];
     $rand=mt_rand();
     if (self::canCreate()) {
       $macro   = new PluginNagiosMacro();
       $macro->showForm("",array('with_object_id'=>$IDNO));
     }
     //$macros  = self::getObjectMacro($IDNO);
     //$used    = array();

     //echo "<form method='post' id='massiveaction_form$rand' action='".self::getFormUrl()."' >";
     echo Html::hidden("plugin_nagios_objects_id",array('value'=>$IDNO));

     $canedit = true;
     if ($canedit && count($macro)) {
        Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
        $massiveactionparams = ['container' => 'mass'.__CLASS__.$rand];
        Html::showMassiveActions($massiveactionparams);
     }

     echo "<table class='tab_cadre_fixe' >";
     
     $header_begin  = "<tr>";
     $header_top    = '';
     $header_bottom = '';
     $header_end    = '';
     if ($canedit && count($macro)) {
        $header_top    .= "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
        $header_top    .= "</th>";
        $header_bottom .= "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
        $header_bottom .= "</th>";
     }
     $header_end .= "<th>".__('Name')."</th>";
     $header_end .= "<th>".__('Value')."</th>";
     $header_end .= "<th>".__('Secure')."</th>";
     $header_end .= "<th>".__('Comment')."</th>";

     echo "<tr>";
     echo $header_begin.$header_top.$header_end;

     $macros=self::getHeritedMacro($item);
     foreach ($macros as $macro_name => $macro) { 

       $srand=mt_rand();
       echo "<tr>";

       if ($canedit && $macro['plugin_nagios_objects_id']==$IDNO) {
         echo "<td width='10'>";
         Html::showMassiveActionCheckBox(__CLASS__, $macro["id"]);
         echo "</td>";
       } else{
         echo "<td width='10'>";
         echo "</td>";
       }


       if ($macro['plugin_nagios_objects_id']==$IDNO) {
           echo "<td>";
            if (!$macro['is_secure'] or Session::haveRight("plugin_nagios_admin",1)) {
              $url=Toolbox::getItemTypeFormURL('PluginNagiosMacro',1)."?id=".$macro['id']."&plugin_nagios_objects_id=$IDNO";
              Ajax::createIframeModalWindow("plugin_nagios_macro_modal_$srand", $url ,array("reloadonclose"=>true,'width'=>1250) ) ;
             echo " <a href='#' onclick=\"$('#plugin_nagios_macro_modal_$srand').dialog('open');\">{$macro['name']}</a>";
              echo "</td>";
           } else {
             echo "{$macro['name']}</td>";
           }

         } else {
            echo "<td>";
            $url=Toolbox::getItemTypeFormURL("PluginNagiosHost",1)."?id=".$macro['plugin_nagios_objects_id']."&with_tab=1&tabnum=20";
                 Ajax::createIframeModalWindow("plugin_nagios_host_modal_$srand", $url ,array("reloadonclose"=>true,'width'=>1250) ) ;
                 echo "<a href='#' onclick=\"$('#plugin_nagios_host_modal_$srand').dialog('open');\">{$macro['name']}</a>";
            echo "</td>";
          }

         if (Session::haveRight("plugin_nagios_admin", READ) or !$macro['is_secure'] ) {
             echo " <td>".html_entity_decode($macro['value'])."</td>";
         } else {
           echo " <td>*********</td>";
         }
           echo " <td>";
           Dropdown::showYesNo('is_secure[]',$macro['is_secure'],-1,array('readonly'=>1));
           echo "</td>";

                               echo " <td>";
         echo $macro['comments'];
         echo "</td>";
         
           echo "</tr>";
     }

     if (count($macro)) {
        echo $header_begin.$header_bottom.$header_end;
     }

     echo "</table>";
     if ($canedit && count($macro)) {
        $massiveactionparams['ontop'] = false;
        Html::showMassiveActions($massiveactionparams);
        //Html::closeForm();
     }


}
 

   function showForm($ID, $options = array() ) {

      $object_id=$options['with_object_id'];

      if (isset($options['is_global']) && $options['is_global']=='1')
	      $is_global=$options['is_global'];
      else
	      $is_global='0';

      $options['colspan']=7;
      $this->initForm($ID, $options);
      $this->showFormHeader($options);
      echo "<tr class='tab_bg_1'>";
      echo " <td>" . _('Name') ."*</td>";
      echo " <td>";
      echo Html::hidden("plugin_nagios_objects_id" ,array('value'=>$object_id));
      echo Html::hidden("is_global" ,array('value'=>$is_global));
      echo Html::hidden("id"  ,array('value'=>$this->fields['id']));
      echo Html::input("name",array('value'=>$this->fields['name']));
      echo " </td>";

	
      echo " <td>"._('Value')."</td>";
      echo " <td>";
      echo Html::input("value"  ,array('value'=>$this->fields['value']));
      echo " </td>";

      echo "<td>".__('Comments')."</td>";
      echo "<td><textarea cols='70' rows='15' name='comments' >".$this->fields["comments"];
      echo "</textarea></td>\n";
  


      echo " <td>"._('Private')."</td>";
      echo " <td>"; 
      Dropdown::showYesNo('is_secure',$this->fields['is_secure']);
      echo " </td>";
      if ($ID)
      	echo " <td><input class='submit' type='submit' name='update' value='"._('Update')."'></td>";
      else
	echo " <td><input class='submit' type='submit' name='add' value='"._('Add')."'></td>";
      echo "</tr>";
      echo "</table>"; 
      Html::closeForm();
      return true;

   }


   static function getHeritedMacro($item,$options=array()) {

        $result=array();
        $all_macros=array();
        $resolved_macros=array();

        if (isset($options['from_parent'])) {
         /* get parent item */
         $item_object=PluginNagiosObject::getNagiosObject($item->get_type());
         $item_object->getFromDB($item->get_parent_id());
        } else {
         $item_object=$item;
        }

        //get all parent items
        $parents=$item_object->getParents();
	
        foreach($parents as $idx=>$items) 
	   $items_id[]=$idx;

        $data=self::getObjectMacro($items_id);	

	/* create array with all macro from parent */
	foreach($data as $idx=>$macro) {
		$resolved_macros[$macro['name']]=array();
		$all_macros[$macro['plugin_nagios_objects_id']][$macro['name']]=$macro;

	}

	
	foreach($resolved_macros as $macro_name => $macro_value) {
		$resolved_macros[$macro_name]=self::resolveMacro($macro_name,$item_object->getID(),$parents,$all_macros);
	}
	
        return $resolved_macros;
   }


   /* recursive function */
   static function resolveMacro($macro_name,$obj_id,$parents,$all_values) {
          if (isset($all_values[$obj_id][$macro_name])) {
              return $all_values[$obj_id][$macro_name];

          } else {
              if ($parents[$obj_id]->get_parent_id())
                   return self::resolveMacro($macro_name,$parents[$obj_id]->get_parent_id(),$parents,$all_values);
              else
                   return null;
          }
  }



   function getSearchOptions() {
      $tab = array();
      $tab['common'] = static::getTypeName();
      $tab[1]['table']     = $this->getTable();
      $tab[1]['field']     = 'name';
      $tab[1]['name']      = _('Name');
      $tab[1]['datatype']        = 'itemlink';
      $tab[1]['itemlink_type'] = 'PluginNagiosMacro';

      $tab[2]['table']     = $this->getTable();
      $tab[2]['field']     = 'value';
      $tab[2]['name']      = _('Value');
   
      $tab[3]['table']     = $this->getTable();
      $tab[3]['field']     = 'is_secure';
      $tab[3]['datatype']     = 'bool';
      $tab[3]['name']      = _('Private');

      $tab[4]['table']     = $this->getTable();
      $tab[4]['field']     = 'comments';
      $tab[4]['datatype']     = 'text';
      $tab[4]['name']      = _('Comments');



      return $tab;

  }
}
