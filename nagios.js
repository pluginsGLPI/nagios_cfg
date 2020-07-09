

(function ($) {





$.fn.listordr = function (field_id) {
	var object = this;
	init();

	function init() {
	  object.field_id=field_id;
	  object.suffix='field_'+object.field_id;	
	}

	this.up = function (item_id) {
          var list_tr=$('#tbl_'+object.suffix+' tr').toArray();
          var idx=0;
          for ( var i = 0; i < list_tr.length; i++ ) {
                if ('tr_'+object.suffix+'_'+item_id == list_tr[i].id)
                        idx=i;
          }

          if (idx==0)
            return 0;
        
          var a=new Array();
          for ( var i = 0; i < list_tr.length; i++ ) {
                if ( (idx - 1) == i ) {
                        a.push(list_tr[idx].outerHTML);
                        a.push(list_tr[i].outerHTML);
                        i=idx;
                } else
                        a.push(list_tr[i].outerHTML);
          }

          $('#tbl_'+object.suffix).html(a.join(" "));

	}

	this.down = function (item_id) {

	   /*get all tr*/
	   var list_tr=$('#tbl_'+object.suffix+' tr').toArray();
           var idx=0;
           for ( var i = 0; i < list_tr.length; i++ ) {
                if ('tr_'+object.suffix+'_'+item_id == list_tr[i].id)
                        idx=i;
           }

           if (idx==list_tr.length)
             return 0;
        
           var a=new Array();
           for ( var i = 0; i < list_tr.length; i++ ) {
                if ( (idx ) == i ) {
                        a.push(list_tr[idx+1].outerHTML);
                        a.push(list_tr[idx].outerHTML);
                        i=idx+1;
                } else
                        a.push(list_tr[i].outerHTML);
           }

           $('#tbl_'+object.suffix).html(a.join(" "));

        }

	this.add = function () {
	  var dropdown_id=$('#div_'+object.suffix).find('input[id*=dropdown]').attr('id');
	  var val  = $('#'+dropdown_id).val();
	  if (val==0)
		return 0;
          var text = $('#div_'+object.suffix+' .select2-chosen').text();

          if (document.getElementById('tr_'+object.suffix+'_'+val))
            return 0;


         var content ="<tr id='tr_"+object.suffix+'_'+val+"' >";
             content+="<td ><div class='move-del' onclick=\""+object.suffix+".remove('"+val+"');\"/></td>";
             content+="<td width='100px'><input type='hidden' name='"+object.suffix+"[]' value='"+val+"' />"+text+"</td>";
             content+="<td><div class='move-up'   onclick=\""+object.suffix+".up('"+val+"');\"     /></td>";
             content+="<td><div class='move-down' onclick=\""+object.suffix+".down('"+val+"');\" /></td>";
             content+="</tr>";

         $('#tbl_'+object.suffix).append(content);

	}

	this.remove = function (item_id) {
           $('#tr_'+object.suffix+'_'+item_id).remove();
	}
	

	return this;
}
}(jQuery));

(function ($) {

$.fn.nagios = function (options) {

  var object = this;
  init();

  // Start the plugin
  function init() {
      object.params = new Array();
      object.params['root_doc'] = '';

      if (options != undefined) {
         $.each(options, function (index, val) {
            if (val != undefined && val != null) {
                object.params[index] = val;
            }
         });
      }
  }


    this.get_check_command_form = function (cmd_id,field_id,cmd_args,toupdate) {
    $('#'+toupdate).show();
    $.ajax({
        type: "POST",
        url: object.params['root_doc'] + '/plugins/nagios/ajax/request.php',
        data : {
            'cmd_id': cmd_id,
            'field_id': field_id,
            'cmd_args': cmd_args,
            'action': 'get_check_command_form'
        },
        success:
                function (data) {
                  if (data)
                            $('#'+toupdate).html(data);
                }
    });


  }

  
  this.disabled_service = function (id,disabled) {
	  $.ajax({
        type: "POST",
        url: object.params['root_doc'] + '/plugins/nagios/ajax/request.php',
        data : {
            'id': id,
	    'disabled': disabled,
            'action': 'disabled_service'
        },
        success:
                function (data) {
                  if (data) {
                            alert(data);
	          }
                }
    });

  }



  this.run_export = function (id,toupdate) {
    $('#'+toupdate).show();
    $.ajax({
        type: "POST",
        url: object.params['root_doc'] + '/plugins/nagios/ajax/request.php',
        data : {
            'id': id,
            'action': 'run_export'
        },
        success:
                function (data) {
                  if (data)
                            $('#'+toupdate).html(data);
                }
    });


  }


  this.getFieldForm = function (id,toupdate) {
    $.ajax({
	type: "POST",
	url: object.params['root_doc'] + '/plugins/nagios/ajax/request.php',
        data : {
	    'id': id,
	    'entity_id': object.params['entity_id'],
            'action': 'getFieldForm'
        },
        success: 
		function (data) {
			if (data)
			    $('#'+toupdate).append(data);
		 }
    }); 
  }



  this.saveFields = function ( toobserve ) {
       var formInput = object.getFormData(toobserve);
       $.ajax({
          url: object.params['root_doc'] + '/plugins/nagios/ajax/request.php',
          type: "POST",
          data: 'action=saveObjectValues&' +  formInput,
          success: function (data) {
	    if (data=='500')
		alert("Erreur lors de la mise à jour.");
            else
		alert("Modification enregistrée.");
          }
       });

       return false;
   }

  

   /** 
    *  Get the form values and construct data url
    * 
    * @param object form
    */
   this.getFormData = function(form) {
       if (typeof (form) !== 'object') {
           var form = $('#' + form);
       }

       return object.encodeParameters(form[0]);
   }




  /** 
   * Encode form parameters for URL
   * 
   * @param array elements
  */
  this.encodeParameters = function(elements) {
      var kvpairs = [];

      $.each(elements, function (index, e) {
          if (e.name != '') {
              switch (e.type) {
                  case 'radio':
                  case 'checkbox':
                      if (e.checked) {
                          kvpairs.push(encodeURIComponent(e.name) + "=" + encodeURIComponent(e.value));
                      }
                      break;
                  case 'select-multiple':
                      var name = e.name.replace("[", "").replace("]", "");
                      $.each(e.selectedOptions, function (index, option) {
                          kvpairs.push(encodeURIComponent(name + '[' + option.index + ']') + '=' + encodeURIComponent(option.value));
                      });
                      break;
                  default:
                      kvpairs.push(encodeURIComponent(e.name) + "=" + encodeURIComponent(e.value));
                      break;
              }
          }
      });

      return kvpairs.join("&");
  }

  //end     

  return this;
}
}(jQuery));
