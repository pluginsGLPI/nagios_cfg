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


class PluginNagiosHost extends PluginNagiosObject {

  static $nagios_type = PluginNagiosObject::NAGIOS_HOST_TYPE;
  static $computer_fields = array ( 'default'=> array(  'use','hostgroups','address') );
 
  /* define general fields */
  static $FIELDS_STD_VIEW=array(
                            array( "libel"=>'General options' ,
                                    "fields"=>array('use','hostgroups','notes') ),
                            array( "libel"=>'Host Check properties',
                                    "fields"=> array('check_period',
                                                    'check_command',
                                                    'max_check_attempts',
                                                    'check_interval', 
                                                    'retry_interval',
                                                    'active_checks_enabled',
                                                    'passive_checks_enabled'))
                               );

  static $FIELDS_NOTIF_VIEW=array(
                            array( "libel"=>'Notification options' ,
                                    "fields"=>array('contacts','contact_groups','notification_interval','notification_period','notification_options','notifications_enabled') ),
                               );



  static $FIELDS_COMPUTER_GENERAL_VIEW=array("libel"=>'Host Configuration',
                                            "fields"=> array('address','use','hostgroups'));
  static $FIELDS_COMPUTER_CHECK_VIEW=array("libel"=>'Host Check properties',
                                          "fields"=> array('check_period','check_command','max_check_attempts','check_interval','retry_interval','active_checks_enabled','passive_checks_enabled'));
 
  static function getTypeName($nb=0) {
   return _n("Host","Hosts",$nb, "nagios"); 
  }


  function getInterface() {
	global $DB;

      $address = array();

      $query="select a.name as ipaddr from glpi_ipaddresses a,glpi_plugin_nagios_objectlinks l  where l.items_id=a.items_id and plugin_nagios_objects_id='".$this->getID()."'";

      foreach ($DB->request($query) as $data) {
         $address[$data['ipaddr']] = $data['ipaddr'];
      }
      return $address;	 


  }


  
  static function getMenuName()
  {
    return static::getTypeName(2);
  }


  static function getMenuContent() {

        $menu['title']=__('Nagios - Host models','nagios');
        $menu['page']="/plugins/nagios/front/host.php";
        $menu['links']['search']="/plugins/nagios/front/host.php";
        $menu['links']['add']="/plugins/nagios/front/host.form.php";

        return $menu;
  }


  function getSearchOptions() {
      
      $tab=parent::getSearchOptions();
/*
      $tab[30001] = [
         'id'                 => '30001',
         'table'              => 'glpi_plugin_nagios_objects',
         'field'              => 'name',
         'name'               => __('Linked Supervision Templates'),
         'forcegroupby'       => true,
         'datatype'           => 'itemlink',
         'massiveaction'      => false,
         'joinparams'         => [
            'beforejoin'         => [
               'table'              => 'glpi_plugin_nagios_links',
               'joinparams'         => [
                  'jointype'           => 'child',
                  'linkfield'          => 'plugin_nagios_objects_id',
                  'condition'          => " AND NEWTABLE.itemtype='PluginNagiosRole'"
               ]
            ]
         ]
      ];
*/
/*

      $tab[30001]['table']     = 'glpi_plugin_nagios_objects';
      $tab[30001]['field']     = 'name';
      $tab[30001]['name']      = __('Linked Supervision Templates');
      $tab[30001]['linkfield'] = 'items_id';
      $tab[30001]['massiveaction'] = false;
      $tab[30001]['forcegroupby']         = true;
      $tab[30001]['datatype']             = 'itemlink';
      $tab[30001]['joinparams']         = array( 
                                            'beforejoin'  => array('table'   => 'glpi_plugin_nagios_links',
                                            'joinparams' => array('jointype'=>'child','linkfield'=>'plugin_nagios_objects_id','condition'=>" AND NEWTABLE.itemtype='PluginNagiosRole'")));
*/
/*
         $tab[30002] = [
         'id'                 => '30002',
         'table'              => 'glpi_plugin_nagios_objects',
         'field'              => 'name',
         'name'               => __('Linked Hostgroups'),
         'forcegroupby'       => true,
         'datatype'           => 'itemlink',
         'massiveaction'      => false,
         'joinparams'         => [
            'beforejoin'         => [
               'table'              => 'glpi_plugin_nagios_links',
               'joinparams'         => [
                  'jointype'           => 'child',
                  'linkfield'          => 'plugin_nagios_objects_id',
                  'condition'          => " AND  NEWTABLE.itemtype='PluginNagiosHostGroup'"
               ]
            ]
         ]
      ];
i*/
      $tab[30001] = [
         'id'                 => '30001',
         'table'              => 'glpi_plugin_nagios_roles_view',
         'field'              => 'name',
         'name'               => __('Linked Supervision Templates'),
         'comments'           => true,
         'forcegroupby'       => true,
         'datatype'           => 'itemlink',
         'itemlink_type'      => 'PluginNagiosRole',
      ];


     $tab[30002] = [
         'id'                 => '30002',
         'table'              => 'glpi_plugin_nagios_hostgroups_view',
         'field'              => 'name',
         'name'               => __('Linked Hostgroups'),
         'comments'           => true,
         'forcegroupby'       => true,
         'datatype'           => 'itemlink',
         'itemlink_type'      => 'PluginNagiosHostGroup',
      ]; 

      $tab[30003] = [
         'id'                 => '30003',
         'table'              => 'glpi_plugin_nagios_hosts_view',
         'field'              => 'name',
         'name'               => __('Linked Hosts'),
         'comments'           => true,
         'forcegroupby'       => true,
         'datatype'           => 'itemlink',
         'itemlink_type'      => 'PluginNagiosHost',
      ]; 

      $tab[30004] = [
         'id'                 => '30004',
         'table'              => 'glpi_plugin_nagios_services_view',
         'field'              => 'alias',
         'name'               => __('Linked Services'),
         'comments'           => true,
         'forcegroupby'       => true,
         'datatype'           => 'itemlink',
         'additionalfields'   => ['alias'],
         'itemlink_type'      => 'PluginNagiosService',
      ];





/*                                            
      $tab[30003]['table']     = 'glpi_plugin_nagios_objects';
      $tab[30003]['field']     = 'name';
      $tab[30003]['name']      = __('Linked Hosts');
      $tab[30003]['linkfield'] = 'items_id';
      $tab[30003]['massiveaction'] = false;
      $tab[30003]['forcegroupby']         = true;
      $tab[30003]['datatype']             = 'itemlink';
      $tab[30003]['joinparams']         = array(
                                            'beforejoin'  => array('table'   => 'glpi_plugin_nagios_links',
                                            'joinparams' => array('jointype'=>'child','linkfield'=>'plugin_nagios_objects_id','condition'=>" AND NEWTABLE.itemtype='PluginNagiosHost'")));
                                            
      $tab[30004]['table']     = 'glpi_plugin_nagios_objects';
      $tab[30004]['field']     = 'alias';
      $tab[30004]['name']      = __('Linked Services');
      $tab[30004]['linkfield'] = 'items_id';
      $tab[30004]['massiveaction'] = false;
      $tab[30004]['forcegroupby']         = true;
      $tab[30004]['datatype']             = 'itemlink';
      $tab[30004]['joinparams']         = array(
                                            'beforejoin'  => array('table'   => 'glpi_plugin_nagios_links',
                                            'joinparams' => array('jointype'=>'child','linkfield'=>'plugin_nagios_objects_id','condition'=>" AND NEWTABLE.itemtype='PluginNagiosService'")));
                                            
  */                                          
      return $tab;   
}  


  function showNagiosDef($options=array()) {

       

	if (isset($options['html']) && $options['html']==1) {
		$start_tag="<font style='font-weight:bold;color:#888888'>";
		$end_tag="</font>";
		$buf="<font style='font-weight:bold;color:orange'>define host {</font>\n";
	} else {
		$start_tag=$end_tag="";
		$buf="define host {\n";
		$options['html']=0;
	}
       
 
        if (isset($this->fields['is_model']) && $this->fields['is_model']) {
           $buf.=" {$start_tag}name{$end_tag} {$this->fields['name']}\n";
           $buf.=" {$start_tag}register{$end_tag} 0\n";
        } else {
           $buf.=" {$start_tag}host_name{$end_tag} {$this->fields['name']}\n";
        }
        if ( $this->fields['alias'] )  $buf.=" {$start_tag}alias{$end_tag} {$this->fields['alias']}\n";
        if ( $this->fields['desc'] )   $buf.=" {$start_tag}notes{$end_tag} {$this->fields['desc']}\n";

        $buf.=PluginNagiosObjectValue::showNagiosDef($this,$options);
        $buf.=PluginNagiosMacro::showNagiosDef($this,$options);

        $buf.="}";


	$services  = PluginNagiosObjectLink::getServicesForObject($this->getID());
	$used_service=array();
        foreach($services as $idd=>$service) {
                if ($service['parent_objects_id'])
		  $used_service[$service['parent_objects_id']]=1;

                $buf.="\n\n";
                $o=new PluginNagiosService();
                $o->getFromDB($service['id']);
                $buf.=$o->showNagiosDef(array("force_name"=>$this->fields['name'],'html'=>$options['html']));
         }



	$parents=$this->getParents();
        foreach($parents as $host_id=>$nagios_host) {
	 if ($host_id==$this->getID())
		continue ;
         $services  = PluginNagiosObjectLink::getServicesForObject($host_id);
	 foreach($services as $idd=>$service) {
		if (isset($used_service[$service['id']]))
			continue ;
                if ($service['parent_objects_id'])
                  $used_service[$service['parent_objects_id']]=1;
                $buf.="\n\n";
		$o=new PluginNagiosService();
		$o->getFromDB($service['id']);
		$buf.=$o->showNagiosDef(array("force_name"=>$this->fields['name'],'html'=>$options['html']));
	 }
	}

	reset($parents);
	foreach($parents as $host_id=>$nagios_host) {
	    $roles=PluginNagiosObjectLink::getRolesForObject($host_id);
	    foreach($roles as $link_id => $role_item) {
	
              $services  = PluginNagiosObjectLink::getServicesForObject($role_item['id']);
              foreach($services as $idd=>$service) {
                if (isset($used_service[$service['id']]))
                        continue ;
                $buf.="\n\n";
                $o=new PluginNagiosService();
                $o->getFromDB($service['id']);
                $buf.=$o->showNagiosDef(array("force_name"=>$this->fields['name'],'html'=>$options['html']));
             }

           }
 
        }

        

        return $buf;
  }



  function getTabNameForItem(CommonGLPI $item, $withtemplate=0) { 
      $ong=array();
      $ong=parent::getTabNameForItem($item, $withtemplate);    
      $ong[32]="Nagios - "._("TPL-Supervision");
      $ong[15]="Nagios - "._("Services");
      ksort($ong);
    
      return $ong;

  }

  static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {

        if (in_array($item->getType(),array('Computer','Printer','NetworkEquipment' ))) {

	    $nagios_host=PluginNagiosObjectLink::getHostForItem( $item->getID(),$item->getType() ) ;
            switch ($tabnum) {
                case 10:
                  static::showForComputer($nagios_host,$item);
		  break;
                case 11:
                  parent::displayTabContentForItem($nagios_host, $tabnum, $withtemplate);
		  break;
		case 12:
		    self::displayTabOther($nagios_host,$item);
		    break;
                case 32:
		  PluginNagiosObjectLink::showRolesForItem($nagios_host);
                  break;
                case 15:
                  PluginNagiosObjectLink::showServicesForItem($nagios_host);
                  break;
                case 20:
                  PluginNagiosMacro::showForObject($nagios_host);
                  break;
                default:
                  parent::displayTabContentForItem($nagios_host, $tabnum, $withtemplate);
                  break;
            }

        } else {

          switch ($tabnum) {
            case 32:
              PluginNagiosObjectLink::showRolesForItem($item);
              break;
            case 15:
	      PluginNagiosObjectLink::showServicesForItem($item);
              break;
	    default:
	      parent::displayTabContentForItem($item, $tabnum, $withtemplate);
              break;
         }

       }

  } /* end function displayTabContentForItem */



  static function displayServices($item) {
        $service=new PluginNagiosService;
  	$service->initForm('');
        $service->showFormHeader(array('colspan'=>2));	

        echo Html::hidden("plugin_nagios_objects_id",array('value'=>$item->getID()));
        echo "<table class='tab_cadre_fixe'>";
        echo  "<tr>";
        echo " <td>"._("Services").":&nbsp;";
        $service->dropdown();
        echo "</td>";
        echo "<td><input class='submit' type='submit' name='addToNagiosObject' value='".__('Add')."'></td>";
	echo "</tr>";
        echo "</table>";
        Html::closeForm();
	

  } 


  static function item_add($item) {

    global $DB;

    if (!in_array($item->getType(),array("Computer","Printer","NetworkEquipment"))){
      return true;
    }
	     

    /* create defaut nagios host */
    $nagios_host=new PluginNagiosHost();
    $data=array();
    $data['entities_id']=$item->fields['entities_id'];
    $data['type']='HT';
    $data['name']=$item->fields['name'];
    $data['is_model']=0;
    $data['is_disabled']=1;
    $nagios_host->add($data);

    /* create link item */
    $data=array();
    $nagios_link=new PluginNagiosObjectLink();
    $data['plugin_nagios_objects_id']=$nagios_host->getID();
    $data['items_id']=$item->getID();
    $data['itemtype']=$item->getType();
    $nagios_link->add($data);

  }

  static function item_update($item) {
    global $DB;

    if (!in_array($item->getType(),array("Computer","Printer","NetworkEquipment")))
        return true;

    $nagios_host=PluginNagiosObjectLink::getHostForItem( $item->getID(),$item->getType() ) ;
    $data['name']=$item->fields['name'];
    $data['entities_id']=$item->fields['entities_id'];
    $data['id']=$nagios_host->getID();
    $nagios_host->update($data);
  }

  static function item_purge($item) {
    global $DB;
    if (!in_array($item->getType(),array("Computer","Printer","NetworkEquipment")))
        return true;

    $nagios_host=PluginNagiosObjectLink::getHostForItem( $item->getID(),$item->getType() ) ;
    $nagios_host->delete( array('id'=>$nagios_host->getID()) );
  }

  static function network_update($item) {
    global $DB;

    $item_id=$item->fields['mainitems_id']; 
    $item_type=$item->fields['mainitemtype'];
    $item_table=getTableForItemType($item_type);

    //get ipaddr
    $sql="SELECT item.id,item.entities_id,item.name,  ( select ip.name as ipaddr from $item_table e LEFT JOIN glpi_networkports np ON (np.items_id=e.id AND np.itemtype='$item_type') LEFT JOIN glpi_networknames nn ON (nn.items_id=np.id) LEFT JOIN glpi_ipaddresses ip ON (nn.ID=ip.items_id) WHERE e.id=$item_id and e.id=item.id order by np.logical_number DESC LIMIT 1 ) ipaddr FROM $item_table item where item.id=$item_id ";
 

     foreach ($DB->request($sql) as $data) {
         $ipaddress = $data['ipaddr'];
     }

     $nagios_host=0;
     if (in_array($item_type,array("Computer","NetworkEquipment","Printer"))) {
                $nagios_host=PluginNagiosObjectLink::getHostForItem( $item_id, $item_type ) ;
     }

     PluginNagiosObjectValue::setValueByFieldName('HT',$nagios_host->getID(),'address',$ipaddress); 
  }





  static function showForComputerApercu($nagios_host) {
   
        /* get host for computer */
        echo "<div class='nagios-apercu'>";
        echo "<pre>";
        echo $nagios_host->showNagiosDef()."\n";
        echo "</pre>";
        echo "</div>";

  }

  static function showForComputer($nagios_host,$item) 
  {

      /* get fields object from FIELS_GENERAL_VIEW */
      $fields_gen_list     = PluginNagiosField::getFieldsByName(static::$nagios_type,static::$FIELDS_COMPUTER_GENERAL_VIEW['fields']);
      $fields_check_list     = PluginNagiosField::getFieldsByName(static::$nagios_type,static::$FIELDS_COMPUTER_CHECK_VIEW['fields']);

      /* get herited value for this object  */
      $herited_gen_values   = PluginNagiosObjectValue::getHeritedValues($nagios_host,array("from_parent"=>true,"restrict_fields_name"=>static::$FIELDS_COMPUTER_GENERAL_VIEW['fields']));
      
      $herited_check_values = PluginNagiosObjectValue::getHeritedValues($nagios_host,array("from_parent"=>true,"restrict_fields_name"=>static::$FIELDS_COMPUTER_CHECK_VIEW['fields']));
      
      /* get own values for this object */
      $own_gen_values      = PluginNagiosObjectValue::getValuesForObject($nagios_host,array("restrict_fields_name"=>static::$FIELDS_COMPUTER_GENERAL_VIEW['fields']));
      $own_check_values    = PluginNagiosObjectValue::getValuesForObject($nagios_host,array("restrict_fields_name"=>static::$FIELDS_COMPUTER_CHECK_VIEW['fields']));


      $rand=rand();
      echo "<form name='nagios_hosttemplate_form_$rand' id='nagios_hosttemplate_form$rand' method='post' action='".Toolbox::getItemTypeFormURL('PluginNagiosHost')."'>";
      echo Html::hidden("id"  ,array('value'=>$nagios_host->getID()));
      echo "<table class='tab_cadre_fixe' >";
      echo "<tr><th colspan='5' style='text-align:left;color:#f0431a'>"._(static::$FIELDS_COMPUTER_GENERAL_VIEW['libel']);
      
            
      echo "<div style='text-align:center;float:right;color:black'>";
      
      echo "<input type='radio' name='is_disabled' value='1' ";
      echo ($nagios_host->fields['is_disabled']) ? "checked" : "";
      echo ">Disable";
      
      echo "&nbsp;&nbsp;";
      echo "<input type='radio' name='is_disabled' value='0' ";
      echo (!$nagios_host->fields['is_disabled']) ? "checked" : "";
      echo ">Enable";
      
      echo "</div>";
      echo "</th></tr>";
      echo "<tr>";
      echo "<td width='170px'><b>"._('HostName')."</b></td>";
      echo "<td style='20px'></td><td >".Html::input("name",array('style'=>'color:#787878','disabled'=>true,'value'=>$nagios_host->fields['name']))."</td>";
      echo "<td></td>";
      echo "</tr>";

      echo "<tr>";
      echo " <td><b>"._("Primary template")."</b></td><td></td>";
      echo " <td>";
      $p['name']='parent_objects_id';
      $p['value']=$nagios_host->fields['parent_objects_id'];
      $p['condition']=" id<>'{$nagios_host->fields['id']}' and `type`='".static::$nagios_type."' and is_model=1";
      $p['entity']=PluginNagiosObject::getRecursiveEntities($nagios_host->fields['entities_id']);
      self::Dropdown($p,array('value'=>$nagios_host->fields['parent_objects_id']));

      //var_dump(PluginNagiosObject::getRecursiveEntities($nagios_host->fields['entities_id']));
      echo " </td>";
      echo "</tr>";

      echo "<tr><td><b>"._('Alias')."</b></td><td></td><td >".Html::input("alias",array('value'=>$nagios_host->fields['alias'],'size'=>'50'))."</td></tr>";
      
      foreach($fields_gen_list as $idx => $field)
      {
        
        $params=array();
        $params['entity']   = $nagios_host->fields['entities_id'];
	$params['item_glpi']=$item;
        $params['item_nagios']=$nagios_host;

        /* not display himself in the list */
        if ($field['field_type']=="LIST_ORDR" or $field['field_type']=="LIST") {
            if ($field['object_type']==static::$nagios_type && strstr($field['field_value'],get_called_class())) {
              $params['used'] = array($nagios_host->getID() );
            }
        }

        /* a specific value exist for this object */
        if (isset($own_gen_values[$field['id']])) {
          $params['value']  = $own_gen_values[$field['id']]['value'];
          $params['linkID'] = $own_gen_values[$field['id']]['linkID'];
          $params['flag']   = $own_gen_values[$field['id']]['flag'];
        }

        if (isset($herited_gen_values[$field['id']]))
          $params['herited_value']=$herited_gen_values[$field['id']]['value'];

          PluginNagiosField::showInput($field['id'],$params);
        
       }
 
 
      echo "<tr><th colspan='5' style='text-align:left;color:#f0431a'>".static::$FIELDS_COMPUTER_CHECK_VIEW['libel']."</th></tr>";

      foreach($fields_check_list as $idx => $field)
      {

        $params=array();
        $params['entity']   = $nagios_host->fields['entities_id'];
        //$params['vertical']=itrue;

	$params['item_glpi']=$item;
	$params['item_nagios']=$nagios_host;

        /* not display himself in the list */
        if ($field['field_type']=="LIST_ORDR" or $field['field_type']=="LIST") {
            if ($field['object_type']==static::$nagios_type && strstr($field['field_value'],get_called_class())) {
              $params['used'] = array($nagios_host->getID() );
            }
        }

        /* a specific value exist for this object */
        if (isset($own_check_values[$field['id']])) {
          $params['value']  = $own_check_values[$field['id']]['value'];
          $params['linkID'] = $own_check_values[$field['id']]['linkID'];
          $params['flag']   = $own_check_values[$field['id']]['flag'];
        } 
	
        if (isset($herited_check_values[$field['id']]))
          $params['herited_value']=$herited_check_values[$field['id']]['value'];
          PluginNagiosField::showInput($field['id'],$params);

       }
 
      if (Session::haveRightsOr("plugin_nagios",array(UPDATE,CREATE)))
	      echo "<tr><td colspan='5' style='text-align:center'><input type='submit' class='submit' name='save_opts' value='"._("Save")."'></td></tr>";
      echo "</table>"; 

      Html::closeForm();


  } //end of function

  

}

