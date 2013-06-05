/*================================================================================
    OMOP - Cloud Research Lab
 
    Observational Medical Outcomes Partnership
    15 December 2009
 
    Application specific JavaScript code.
 
    (c)2009 Foundation for the National Institutes of Health (FNIH)
 
    Licensed under the Apache License, Version 2.0 (the "License"), you may not
    use this file except in compliance with the License. You may obtain a copy
    of the License at http://omop.fnih.org/publiclicense.
 
    Unless required by applicable law or agreed to in writing, software
    distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
    WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. Any
    redistributions of this work or any derivative work or modification based on
    this work should be accompanied by the following source attribution: "This
    work is based on work by the Observational Medical Outcomes Partnership
    (OMOP) and used under license from the FNIH at
    http://omop.fnih.org/publiclicense.
 
    Any scientific publication that is based on this work should include a
    reference to http://omop.fnih.org.
 
================================================================================*/

if (!Array.prototype.indexOf)
{
  Array.prototype.indexOf = function(elt /*, from*/)
  {
    var len = this.length;

    var from = Number(arguments[1]) || 0;
    from = (from < 0)
         ? Math.ceil(from)
         : Math.floor(from);
    if (from < 0)
      from += len;

    for (; from < len; from++)
    {
      if (from in this &&
          this[from] === elt)
        return from;
    }
    return -1;
  };
}

/**
 * Enables or disables any matching elements.
 */
jQuery.fn.readonly = function(b) {
	if (b == undefined) b = true;
	return this.each(function() {
		if (b) {
			$(this).attr('readonly', 'readonly')
				.addClass('text_disable');
			
		} else {
			$(this).attr('readonly', '')
				.removeClass('text_disable');
		}
	});
};

function isNumber(x) 
{ 
  return ( (null !== x) && isFinite(x) );
}

function adjust_menu()
{
	$("div#container ul.navigation li a.right")
        .removeClass('right')
        .parent().addClass('right');
}

function adjust_height()
{
	change_size();
	$(window).resize(change_size);
}

function number_format(number, decimals, dec_point, thousands_sep) {
    // Formats a number with grouped thousands  
    // 
    // version: 1004.1212
    // discuss at: http://phpjs.org/functions/number_format
    // +   original by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +     bugfix by: Michael White (http://getsprink.com)
    // +     bugfix by: Benjamin Lupton
    // +     bugfix by: Allan Jensen (http://www.winternet.no)
    // +    revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
    // +     bugfix by: Howard Yeend
    // +    revised by: Luke Smith (http://lucassmith.name)
    // +     bugfix by: Diogo Resende
    // +     bugfix by: Rival
    // +      input by: Kheang Hok Chin (http://www.distantia.ca/)
    // +   improved by: davook
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Jay Klehr
    // +   improved by: Brett Zamir (http://brett-zamir.me)
    // +      input by: Amir Habibi (http://www.residence-mixte.com/)
    // +     bugfix by: Brett Zamir (http://brett-zamir.me)
    // +   improved by: Theriault
    // *     example 1: number_format(1234.56);
    // *     returns 1: '1,235'
    // *     example 2: number_format(1234.56, 2, ',', ' ');
    // *     returns 2: '1 234,56'
    // *     example 3: number_format(1234.5678, 2, '.', '');
    // *     returns 3: '1234.57'
    // *     example 4: number_format(67, 2, ',', '.');
    // *     returns 4: '67,00'
    // *     example 5: number_format(1000);
    // *     returns 5: '1,000'
    // *     example 6: number_format(67.311, 2);
    // *     returns 6: '67.31'
    // *     example 7: number_format(1000.55, 1);
    // *     returns 7: '1,000.6'
    // *     example 8: number_format(67000, 5, ',', '.');
    // *     returns 8: '67.000,00000'
    // *     example 9: number_format(0.9, 0);
    // *     returns 9: '1'
    // *    example 10: number_format('1.20', 2);
    // *    returns 10: '1.20'
    // *    example 11: number_format('1.20', 4);
    // *    returns 11: '1.2000'
    // *    example 12: number_format('1.2000', 3);
    // *    returns 12: '1.200'
    var n = !isFinite(+number) ? 0 : +number, 
        prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
        sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
        dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
        s = '',
        toFixedFix = function (n, prec) {
            var k = Math.pow(10, prec);
            return '' + Math.round(n * k) / k;
        };
    // Fix for IE parseFloat(0.55).toFixed(0) = 0;
    s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.');
    if (s[0].length > 3) {
        s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
    }
    if ((s[1] || '').length < prec) {
        s[1] = s[1] || '';
        s[1] += new Array(prec - s[1].length + 1).join('0');
    }
    return s.join(dec);
}

function change_size()
{
	var height = window.innerHeight;
	if (height == undefined)
	{
		height = document.body.clientHeight;
	}
	$("#container").css("min-height", height - 110);
    if ($("#container").height() < height - 110)
        $("#container").height(height - 110);
    else
	{
		/*if (jQuery.browser.msie && jQuery.browser.version > 7)
		{
			$("#container").css("height", 'auto !important');
		}*/
	}    
}

/*
Function that initialize all user interaction within /create_user page.
*/

function setup_create_user()
{
	init_validator();

	var validator = $(".form2").validate({
		rules: {
			user_id: {
				required: true,
				minlength: 2,
				maxlength: 128,
				unique_login: true
			},
			first_name:
			{
				required: true,
				maxlength: 50
			},
			last_name:
			{
				required: true,
				maxlength: 50
			},
			email:
			{
				required: true,
				email: true,
				unique_email: true,
				//remote: "/api.php",
				maxlength: 128
			},
			phone:
			{
				required: true,
				//phone: true,
				maxlength: 20
			},
			title:
			{
				required: false,
				maxlength: 100
			},
			password:
			{
				required: true
			},
			money_limit:
			{
				required: true,
				number: true,
				positiveNumber: true,
				naturalNumber: true
			},
			user_volume_size:
			{
				required: true,
				number: true,
				positiveNumber: true,
				naturalNumber: true,
				range: [1, 1024]
			},
			num_instances:
			{
				required: true,
				number: true,
				positiveNumber: true,
				naturalNumber: true
			},
			"database_type[]":

			{
				required: true,
				minlength: 1
			}
		},
		submitHandler: function(form) {
			form.submit();
		},
		messages: {
		  user_id: {
			required: "Login ID is required",
			minlength: "Login ID should be at least 2 characters long."
		  },
		  first_name: {
			required: "First name is required"
		  },
		  last_name: {
			required: "Last name is required"
		  },
		  email: {
			required: "Email is required",
			remote: "Duplicate emails are not allowed"
		  },
		  phone: {
			required: "Phone is required"
		  },
		  money_limit: {
			required: "Charge Limit is required"
		  },
			user_volume_size:
			{
				required: "Personal storage is required",
				range: "Storage size should be between 1GB and 1TB"
			},
		  num_instances: {
			required: "Max Instances is required"
		  },
		  password: {
			required: "Password is required"
		  },
			 
		  "database_type[]": {
			required: "At least one dataset must be selected",
			minlength: "At least one dataset must be selected"
		  },
			 
		  "software_type[]": {
			required: "At least one software must be selected",
			minlength: "At least one software must be selected"
		  }
		},
		errorElement: 'span',
		errorPlacement: function(error, element) {
			var er = element.attr("name");
			if (er != "database_type[]" && er != "software_type[]")
			{
				error.insertAfter( element );
				$("<br/>").insertAfter( element );
			}
			else
			{
				error.insertAfter( element.parent().parent() );
				//$("<br/>").insertAfter( element ),			
			}
		}
	});
	update_user_admin_label();
	if (validation_errors)
	{
		validator.showErrors(validation_errors);
	}
}

/*
Function that initialize all user interaction within /edit_user page.
*/
function setup_edit_user()
{
	init_validator();
	$("input[type='submit']")
		.mouseover(function(){
			this.style.backgroundPosition='bottom';
		})
		.mouseout(function(){
			this.style.backgroundPosition='top';
		});	
	
	var validator = $(".form2").validate({
		rules: {
			user_id: {
				required: true,
				minlength: 2,
				unique_login: true,
				maxlength: 128
			},
			first_name:
			{
				required: true,
				maxlength: 50
			},
			last_name:
			{
				required: true,
				maxlength: 50
			},
			email:
			{
				required: true,
				email: true,
				unique_email: true,
				//remote: "/api.php",
				maxlength: 128
			},
			phone:
			{
				required: true,
				//phone: true,
				maxlength: 20
			},
			title:
			{
				required: false,
				maxlength: 100
			},
			password:
			{
				required: false
			},
			confirmpassword:
			{
				required: false,
				equalTo: "#idPassword"
			},					
			money_limit:
			{
				required: true,
				number: true,
				positiveNumber: true,
				naturalNumber: true
			},
			user_volume_size:
			{
				required: true,
				number: true,
				positiveNumber: true,
				naturalNumber: true,
				range: [1, 1000]
			},
			num_instances:
			{
				required: true,
				number: true,
				positiveNumber: true,
				naturalNumber: true
			},
			"database_type[]":
			{
				required: true,
				minlength: 1
			}
		},
		submitHandler: function(form) {
			//$(form).ajaxSubmit(),
			form.submit();
		},
		messages: {
		  user_id: {
			required: "Please specify login id",
			minlength: "Login id should be at least 2 characters long."
		  },
		  first_name: {
			required: "First name is required"
		  },
		  last_name: {
			required: "Last name is required"
		  },
		  email: {
			required: "Email is required",
			remote: "Duplicate emails are not allowed"
		  },
		  phone: {
			required: "Phone is required"
		  },
		  money_limit: {
			required: "Charge Limit is required"
		  },
			user_volume_size:
			{
				required: "Personal storage is required",
				range: "Storage size should be between 1GB and 1TB"
			},
		  num_instances: {
			required: "Max Instances is required"
		  },
			 
		  "database_type[]": {
			required: "At least one dataset must be selected",
			minlength: "At least one dataset must be selected"
		  },
			 
		  "software_type[]": {
			required: "At least one software must be selected",
			minlength: "At least one software must be selected"
		  }
		},
		errorElement: 'span',
		errorPlacement: function(error, element) {
			var er = element.attr("name");
			if (er != "database_type[]" && er != "software_type[]")
			{
				error.insertAfter( element );
				$("<br/>").insertAfter( element );
			}
			else
			{
				error.insertAfter( element.parent().parent() );
				//$("<br/>").insertAfter( element ),			
			}
		}
	});
	update_user_admin_label();
	if (validation_errors)
	{
		validator.showErrors(validation_errors);
	}
}

function update_user_admin_label()
{
	var worker = function()
	{
		var val = $("select#idOrganization").val();
		var adminLabelFlag = $("label[for='idAdminFlag']");
		if (val == 0)
		{
			adminLabelFlag.html("Is Sys Admin?:");
		}
		else
		{
			adminLabelFlag.html("Is Org Admin?:");
		};
	};
	$("select#idOrganization")
	.change(worker)
	.keydown(worker);
}
/*
Function that initialize all user interaction within /edit_user page.
*/
function setup_site_setup()
{
	init_validator();

	$(".form2").validate({
		rules: {
			admin_email: {
				required: true,
				email: true,
				maxlength: 128
			},
			replyto_email:
			{
				required: true,
				email: true,
				maxlength: 128
			},
			time_zone:
			{
				required: true
			},
			default_money_limit:
			{
				required: true,
				number: true,
				positiveNumber: true,
				maxlength: 15
			},
			password_expiration_period:
			{
				required: true,
				number: true,
				nonNegativeNumber: true,
				maxlength: 4
			},
			default_date_format:
			{
				required: true
			},
			methods_instance_path:
			{
				required: true
			},
			methods_path:
			{
				required: true
			}
		},
		submitHandler: function(form) {
			//$(form).ajaxSubmit(),
			form.submit();
		},
		messages: {
		  user_id: {
			required: "Please specify login id",
			minlength: "Login id should be at least 2 characters long."
		  }
		},
		errorElement: 'span',
		errorPlacement: function(error, element) {
			var er = element.attr("name");
			if (er != "database_type[]")
			{
				error.insertAfter( element );
				$("<br/>").insertAfter( element );
			}
			else
			{
				error.insertAfter( element.parent().parent() );
				//$("<br/>").insertAfter( element ),			
			}
		}
	});

	$(".form3_").validate({
		rules: {
			mail_subject: {
				required: true
			},
			mail_body:
			{
				required: true
			}
		},
		submitHandler: function(form) {
			//$(form).ajaxSubmit(),
			form.submit();
		},
		messages: {
		  user_id: {
			required: "Please specify login id",
			minlength: "Login id should be at least 2 characters long."
		  },
		  first_name: {
			required: "First name is required"
		  },
		  last_name: {
			required: "Last name is required"
		  },
		  email: {
			required: "Email is required",
			remote: "Duplicate emails are not allowed"
		  },
		  phone: {
			required: "Phone is required"
		  },
		  money_limit: {
			required: "Charge Limit is required"
		  },
		  num_instances: {
			required: "Max Instances is required to create user"
		  },
			 
		  "database_type[]": {
			required: "At least one dataset must be selected",
			minlength: "At least one dataset must be selected"
		  }
		},
		errorElement: 'span',
		errorPlacement: function(error, element) {
			var er = element.attr("name");
			if (er != "database_type[]")
			{
				error.insertAfter( element );
				$("<br/>").insertAfter( element );
			}
			else
			{
				error.insertAfter( element.parent().parent() );
				//$("<br/>").insertAfter( element ),			
			}
		}
	});
}


/*
Function that initialize all user interaction within /send_password page.
*/
function setup_send_password()
{
    $(".form6").validate({
        rules: {
            email:
            {
                required:true,
                email:true,
                maxlength:128
            }      
        },
        messages: {
          email: {
            required: "Email is required"
          }  
        },
        errorElement: 'span',
        errorPlacement: function(error, element) {
            var er = element.attr("name");
            error.insertAfter( element );
            $("<br/>").insertAfter( element );
        }
    });
    
}

/*
Function that initialize all user interaction within /edit_account page.
*/

function setup_edit_account()
{
	init_validator();
	
	minpasswordlength = 5;
	minpassworderror = minpasswordlength.toString() + ' characters password or more required';
	validator = $(".form2").validate({
        rules: {
            email:
            {
                required:true,
                email:true,
				unique_email: true,
                maxlength:128
            },
            first_name:
            {
                required:true,
                maxlength:50
            },
            last_name:
            {
                required:true,
                maxlength:50
            },
            phone:
            {
                required:true,
                maxlength:20
            },
            password:
            {
                required:false,
                minlength:minpasswordlength
            },
            confirmation:
            {
                required:false,
                equalTo:"#idPassword"
            }           
        },
        messages: {
          first_name: {
            required: "First name is required"
          },
          last_name: {
            required: "Last name is required"
          },
          email: {
            required: "Email is required"
          },
          phone: {
            required: "Phone is required"
          },
          password: {
        	  minlength: minpassworderror
          },
		  confirmation: {
			  equalTo: "Please enter same value for password again"
		  }
        },
        errorElement: 'span',
        errorPlacement: function(error, element) {
            var er = element.attr("name");
            error.insertAfter( element );
            $("<br/>").insertAfter( element );
        }
    });
	if (validation_errors)
	{
		validator.showErrors(validation_errors);
	}
}

function setup_launch()
{
	init_validator();

	$("#idTool").change(function()
	{	
		update_instance_types();
		$('#idNumber').trigger('change');
	});
	$("#idDatabase input").change(function()
	{	
		;$('#idNumber').trigger('change');
	});
	$("#idDatabase input").click(function()
	{	
		$('#idNumber').trigger('change');
	});
	$("#idTool").keydown(function()
	{
		update_instance_types();
		$('#idNumber').trigger('change');
	});
	$("#idTempEBS").keydown(function()
	{
		$('#calculating').show();
		setTimeout( function()
		{
			$('#calculating').hide();
			calculateEstimate();
		}, 2000);
	});
	$("#idTempEBS").change(function()
	{
		calculateEstimate();
	});

	$("#idInstancetype").change(function()
	{
		update_instance_description();
		calculateEstimate();
	});
	$("#idInstancetype").keydown(function()
	{
		update_instance_description();
		$('#calculating').show();
		setTimeout( function()
		{
			$('#calculating').hide();
			calculateEstimate();
		}, 2000);
	});
	$("#idNumber").change(function () {
		$(this).parent().next().html('');
		var number_value = $(this).val();
		if (number_value > 0)
		{
			//var db_name = $("#idDatabase").html().toUpperCase();
			var selected_datasets = $("#idDatabase p :checked");
			//alert(selected_datasets.size());
			var db_name = "";
			for (i=0; i<selected_datasets.size(); i++)
			{
				ds = const_dataset_types[selected_datasets[i].value].description.toUpperCase();
				db_name += ds + "_";
			}
			db_name = db_name.substr(0, db_name.length - 1);
			var tool_name = $("#idTool option:selected").html().toUpperCase();

			for (i=1, j=1; i <= number_value; i++, j++)
			{
				name = assigned_instance_names[j-1] ? assigned_instance_names[j-1] : tool_name + '_' + db_name + '_' + j;
                result = check_instance_name(name);
				if (result != true)
				{
					j = result.available_index;
					name = result.available_name;
				}
				$(this).parent().next().append("<div>"
				+ "<label for=\"idInstancename"+i+"\">Instance Name "+i+"<strong>*</strong>:</label>"
				+ "<input type=\"text\" id=\"idInstancename"+i+"\" name=\"instance_name[]\" class=\"text\" maxlength=\"256\" value=\""+name+"\" />"
				+ "</div>");
			}
			calculateEstimate();
		}
	});
    
	var validation_rules = {
            estimate: {
                number:true
            },
            number_of_instances: {
                digits:true,
                positiveNumber: true,
                required: true,
				instances_limit: true, 
                maxlength:3
            },
			"instance_name[]": {
				instances_name_unique: true,
                required: true
			}
        };
	
	pasword_inputs = $("#idDatabase input[type='text']");
	for(i=0;i<pasword_inputs.length;i++)
	{
		var name = pasword_inputs[i].name;
		validation_rules[name] = {
				valid_dataset_password: true
		};
	}
	validator = $(".form2").validate({
		//debug: true,
		onkeyup: false,
		onclick: false,
		rules: validation_rules,
        messages: {
          "instance_name[]": {
            required: "Instance name is required to launch instance(s)"
          },
          estimate: {
            number: "Insufficient funds to launch instances"
          },
          number_of_instances: {
            digits: "Number of instances should be positive number",
            positiveNumber: "Number of instances should be positive number",
            required: "Number of instances is required to launch instance(s)",
            maxlength: "You want to lunch too many instances"
          }  
        },
        errorElement: 'span',
        errorPlacement: function(error, element) {
            var er = element.attr("name");
            if (er != "database_type[]")
            {
                error.insertAfter( element );
                $("<br/>").insertAfter( element );
            }
			else
			{
				error.insertAfter( element.parent().parent() );
				//$("<br/>").insertAfter( element ),			
			}
        }
    });
    
	$("#idTool").trigger('change');
	$("#idNumber").trigger('change'); 
	if (typeof  selected_instance_types != "undefined")
	{
		$("#idInstancetype").val(selected_instance_types);
	}
	else
	{
		$("#idInstancetype").trigger('change');
	}

	if (validation_errors)
	{
			validator.showErrors(validation_errors);
	}
}    

function update_instance_types()
{
	$("#idInstancetype").html('');
	software_type_id = $("#idTool").val();
	supported_platforms = new Array();
	ebs_supported = false;
	cluster_supported = false;
	gpu_required = false;
	os_family = '';
	for(i in const_software_types)
	{
		software_type = const_software_types[i];
		if ((software_type.id == software_type_id) 
			&& (supported_platforms.indexOf(software_type.platform) == -1))
		{
			//alert(dataset_type_id + ' ' + software_type_id + ' ' + image.platform);
			supported_platforms.push(software_type.platform);
		}
		if (software_type.id == software_type_id)
		{
			if (software_type.ebs_flag == 'Y')
			{
				ebs_supported = true;
			}
			if (software_type.cluster_flag == 'Y')
			{
				cluster_supported = true;
			}
			if (software_type.gpu_required_flag == 'Y')
			{
				gpu_required = true;
			}
			os_family = software_type.os_family.toLowerCase();
		}
	}
	for(i in const_instance_sizes)
	{
		size = const_instance_sizes[i];
		if (size.ebs_required_flag == 'Y' && !ebs_supported)
			continue;
		
		if (size.cluster_flag == 'Y' && !cluster_supported)
			continue;

		if (size.cluster_flag != 'Y' && cluster_supported)
			continue;

		// Hide images that does not have GPU from our images that prepared specifically 
		// for running on GPU instances. 
		if (size.gpu_flag == 'Y' && !gpu_required)
			continue;

		if (size.gpu_flag != 'Y' && gpu_required)
			continue;
		if (size.os_family != os_family)
			continue;
		
		// Non-windows AMIs with a virtualization type of 'hvm' may not be used with instances of type 't1.micro'.
		// Cluster AMI has virtualization type 'hvm'.
		if (size.aws_instance_size_name == 't1.micro' && os_family != 'windows' && cluster_supported)
			continue;
		
		if (supported_platforms.indexOf(size.platform) != -1)
		{
			optionItem = "<option value=\""+size.id+"\">"+size.name+"</option>";
			$("#idInstancetype").append(optionItem);
		}
	}
	$("#idInstancetype").trigger('change'); 
}

function check_instance_name(name)
{
	validation_result = false;
	$.ajax({
		async: false,
		cache: false,
		type: "POST",
		url: '/api.php?method=check_instance_name',
		data: 
			{
			instance_name:name,
			user_id:current_user_id
			},
		success: function(response){ 
			//alert(response);
				if (response == "true")
				{
					validation_result = true;
				}
				else
				{
					validation_result = response;
				}
			},
		dataType: 'json'
	});
	//alert(validation_result);
	return validation_result;
}

function check_system_instance_name(name)
{
	validation_result = false;
	instance_id_value = $("#internal_id").val();
	$.ajax({
		async: false,
		cache: false,
		type: "POST",
		url: '/api.php?method=check_system_instance_name',
		data:
			{
			instance_name:name,
			instance_id:instance_id_value
			},
		success: function(response){
//			alert(response);
				if (response == "true")
				{
					validation_result = true;
				}
				else
				{
					validation_result = response;
				}
			},
		dataType: 'json'
	});
	//alert(validation_result);
	return validation_result;

}

function check_system_instance_unique_host(name)
{
	validation_result = false;
	instance_id_value = $("#internal_id").val();
	$.ajax({
		async: false,
		cache: false,
		type: "POST",
		url: 'api.php?method=check_system_instance_unique_host',
		data:
			{
			instance_host:name,
			instance_id:instance_id_value
			},
		success: function(response){
			//alert(response);
				if (response == "true")
				{
					validation_result = true;
				}
				else
				{
					validation_result = response;
				}
			},
		dataType: 'json'
	});
	//alert(validation_result);
	return validation_result;

}

function check_instance_host(host) {
	validation_result = false;
	instance_id_value = $("#internal_id").val();
	$.ajax({
		async: false,
		cache: false,
		type: "POST",
		url: 'api.php?method=check_system_instance_host',
		data:
			{
			instance_host:host
			},
		success: function(response){
			//alert(response);
				if (response == "false")
				{
					validation_result = false;
				}
				else
				{
					validation_result = true;
				}
			},
		dataType: 'json'
	});
	//alert(validation_result);
	return validation_result;

}

function checkSvnFolder(element) { 
    validation_result = false;
    folder = $("#idSVNFolder").val()
    if (!folder) {
        return true;
    }
    $.ajax({
		async: false,
		cache: false,
		type: "POST",
		url: 'api.php?method=check_svn_folder',
		data:
			{
			svnFolder: folder
			},
		success: function(response){
			//alert(response);
				if (response == "false")
				{
					validation_result = false;
				}
				else
				{
					validation_result = true;
				}
			},
		dataType: 'json'
    });
    
    return validation_result;
}

function checkOrgName(name) { 
    validation_result = false;
    $.ajax({
		async: false,
		cache: false,
		type: "POST",
		url: 'api.php?method=check_org_name',
		data:
			{
			name: name,
            orgId: $("#organization_id").val()
			},
		success: function(response){
			//alert(response);
				if (response == "false")
				{
					validation_result = false;
				}
				else
				{
					validation_result = true;
				}
			},
		dataType: 'json'
    });
    
    return validation_result;
}

function setup_login()
{
	update_screen_resolution();
	$("#button_login").attr("disabled","");
    if ($("#idLogin").val())
        $("#idPassword").focus();
    else
        $("#idLogin").focus();
}

function update_screen_resolution()
{
	width = (screen.width) ? screen.width:'';
	height = (screen.height) ? screen.height:'';
	// check for windows off standard dpi screen res
	if (typeof(screen.deviceXDPI) == 'number') {
		width *= screen.deviceXDPI/screen.logicalXDPI;
		height *= screen.deviceYDPI/screen.logicalYDPI;
	} 
	$("#idScreenWidth").val(width);
	$("#idScreenHeight").val(height);		
}
function setup_loginadmin()
{
	width = (screen.width) ? screen.width:'';
	height = (screen.height) ? screen.height:'';
	// check for windows off standard dpi screen res
	if (typeof(screen.deviceXDPI) == 'number') {
		width *= screen.deviceXDPI/screen.logicalXDPI;
		height *= screen.deviceYDPI/screen.logicalYDPI;
	} 
	$("#idScreenWidth").val(width);
	$("#idScreenHeight").val(height);
	$("#button_login").attr("disabled","");
    if ($("#idLogin").val())
        $("#idPassword").focus();
    else
        $("#idLogin").focus();
}

function setup_instances()
{
	attach_autoupdate_timer();
}

function attach_autoupdate_timer()
{
	$("tr.booting").everyTime(10000, 'booting', function(index) {
		row_id = $(this).attr('id');
		instance_id = row_id.substring('instance_row_'.length);
		update_public_dns($(this), instance_id);
	});
}

function update_public_dns(jelement, instance_id)
{
    $.ajax({
		url: "/api.php?method=get_instance",
        data: {instance_id : instance_id},
        dataType:"json",
		type: 'POST',
		success: function(data){
			if (data == "error")
            {
				return;
			}
            if (data.public_dns == null)
            {
				return;
			}
			$("tr.booting").stopTime('booting');
			if (data.status_flag == 'X')
			{
                // TODO To base on the columns number is not too good idea
				if (jelement.children("td").size() == 9)
                {
					jelement.children("td:eq(1)").html('Instance is preparing');
				}
				else
				{
					jelement.children("td:eq(2)").html('Instance is preparing');
				}
			}
			else if (data.status_flag == 'A')
			{
                $("#instance_row_" + instance_id).removeClass('booting');
			    if (jelement.children("td").size() == 9)
                {
				    jelement.children("td:eq(1)").html(data.public_dns);
				}
				else
				{
					x_title = "XWindows";
					mac_re = /mac/i;
					if (mac_re.test(navigator.userAgent)) {
						show_mac = false;
					}
					else {
						show_mac = true;
					}
                    ssh_title = "SSH";
					jelement.children("td:eq(2)").html(data.public_dns);
					jelement.children("td:eq(1)").html(
							"<a href=\"omop://"+data.token+"@"+window.location.host+"/"+data.assigned_name+"\" title=\""+ssh_title+" connect to "+data.assigned_name+"\"><img src=\"/images/putty.jpg\" alt=\"Connect\" /></a>"+
				            (show_mac ? "<a href=\"omopf://"+data.token+"@"+window.location.host+"/"+data.assigned_name+"\" title=\"WinSCP connect to "+data.assigned_name+"\"><img src=\"/images/WinSCP.png\" alt=\"Transfer files\" /></a>" : "") +
				            "<a href=\"omopx://"+data.token+"@"+window.location.host+"/"+data.assigned_name+"\" title=\""+x_title+" connect to "+data.assigned_name+"\"><img src=\"/images/xming.jpg\" alt=\"X-Window session\" /></a>"
								);
				}
			}
            attach_autoupdate_timer();
        }
	});
}

function setup_edit_software_type()
{
	$(".form2").validate({
		rules: {
			description: {
				required: true,
				maxlength: 50
			},
			image:
			{
				required: true,
				maxlength: 50
			},
			platform:
			{
				required: true,
				maxlength: 50
			}
		},
		submitHandler: function(form) {
			form.submit();
		},
		messages: {
		  description: {
			required: "Description is required",
			minlength: "Description should be at least 2 characters long."
		  },
		  image: {
			required: "AMI is required"
		  },
		  platform: {
			required: "Platform code is required"
		  }
		},
		errorElement: 'span',
		errorPlacement: function(error, element) {
			var er = element.attr("name");
			if (er != "database_type[]" && er != "software_type[]")
			{
				error.insertAfter( element );
				$("<br/>").insertAfter( element );
			}
			else
			{
				error.insertAfter( element.parent().parent() );
				//$("<br/>").insertAfter( element ),			
			}
		}
	});
}

function setup_edit_dataset_type()
{
	$(".form2").validate({
		rules: {
			description: {
				required: true,
				maxlength: 50
			},
			image:
			{
				required: true,
				maxlength: 50
			},
			platform:
			{
				required: true,
				maxlength: 50
			},
			method_name:
			{
				required: true
			},
			password:
			{
				dataset_password: true
			}
		},
		submitHandler: function(form) {
			form.submit();
		},
		messages: {
		  description: {
			required: "Description is required",
			minlength: "Description should be at least 2 characters long."
		  },
		  image: {
			required: "AMI is required"
		  },
		  platform: {
			required: "Platform code is required"
		  },
			method_name:
			{
				required: "Method name is required"
			}
		},
		errorElement: 'span',
		errorPlacement: function(error, element) {
			var er = element.attr("name");
			if (er != "database_type[]" && er != "software_type[]")
			{
				error.insertAfter( element );
				$("<br/>").insertAfter( element );
			}
			else
			{
				error.insertAfter( element.parent().parent() );
				//$("<br/>").insertAfter( element ),			
			}
		}
	});


	$("#idEncryptedFlag").change(function(){
		password_element = $("#idPasswordWrap");
		if ($("#idEncryptedFlag:checked").length == 1)
		{
			password_element.show();
		}
		else
		{
			password_element.hide();
		}
	});
	$("#idDatasetMode").change(function(){
		var method = $("#idDatasetMode").val();
		update_dataset_mode(method);
	});
	$("#idDatasetMode").keypress(function(){
		var method = $("#idDatasetMode").val();
		update_dataset_mode(method);
	});
}

function setup_reset()
{
	update_screen_resolution();
	validation_errors = false;
	validator = $(".form6").validate({
        rules: {
            password:
            {
                required:true
            },
            confirmpassword:
            {
                required:true,
                equalTo:"#idPassword"
            }           
        },
        
		submitHandler: function(form) {
			form.submit();
		},
		messages: {
		  confirmation: {
			  equalTo: "Please enter same value for password again"
		  }
        },
        errorElement: 'span',
        errorPlacement: function(error, element) {
            var er = element.attr("name");
            error.insertAfter( element );
            $("<br/>").insertAfter( element );
        }
    });
	if (validation_errors)
	{
		validator.showErrors(validation_errors);
	}
}

function setup_launch_method()
{
	var validation_rules = {
		instance: {
			required: true
		},
		method_name:
		{
			required: true
		},
		parameter:
		{
			required: true
		}
	};
	
	pasword_inputs = $("#idDatabase input[type='text']");
	for(i=0;i<pasword_inputs.length;i++)
	{
		var name = pasword_inputs[i].name;
		validation_rules[name] = {
				valid_dataset_password: true
		};
	}
	$(".form2").validate({
		rules: validation_rules,
		submitHandler: function(form) {
			form.submit();
		},
		messages: {
		  instance: {
			required: "Instance is required"
		  },
		  method_name: {
			required: "Method is required"
		  },
		  parameter: {
			required: "Method parameter is required"
		  }
		},
		errorElement: 'span',
		errorPlacement: function(error, element) {
			var er = element.attr("name");
			if (er != "database_type[]" && er != "software_type[]")
			{
				error.insertAfter( element );
				$("<br/>").insertAfter( element );
			}
			else
			{
				error.insertAfter( element.parent().parent() );
				//$("<br/>").insertAfter( element ),			
			}
		}
	});
	$("#idOverrideMethodParameters").change(function()
	{	
		$("#idMethodReplacement_wrap").toggle();
	});
	$("#idTool").change(function()
	{	
		update_instance_types();
	});
	$("#idTool").keydown(function()
	{
		update_instance_types();
	});

	$("#idDatabase").change(function()
	{	
		calculateMethodLaunchEstimate();
	});
	$("#idDatabase").keydown(function()
	{	
		calculateMethodLaunchEstimate();
	});
	$("#idInstancetype").change(function()
	{
		update_instance_description();
		calculateMethodLaunchEstimate();
	});
	$("#idInstancetype").keydown(function()
	{
		update_instance_description();
		$('#calculating').show();
		setTimeout( function()
		{
			$('#calculating').hide();
			calculateMethodLaunchEstimate();
		}, 2000);
	});
	$("#idMethod").change(function(){
		updateMethodParameters(); 
	});
	$("#idMethod").keypress(function(){
		updateMethodParameters(); 
	});
	$("#idMethod").trigger('change');
	update_instance_types(); 
	if (typeof  selected_instance_types != "undefined")
	{
		$("#idInstancetype").val(selected_instance_types);
	}
	else
	{
		$("#idInstancetype").trigger('change');
	}

	$("#idParameter option:first-child").attr('selected', 'selected');
	updateLaunchOptions();
}

function setup_create_dataset_type()
{
	$(".form2").validate({
		rules: {
			description: 
			{
				required: true
			},
			method_name:
			{
				required: true
			},
			password:
			{
				dataset_password: true
			}
		},
		submitHandler: function(form) 
		{
			form.submit();
		},
		messages: {
			description: {
			required: "Description is required"
		  },
		  ebs: {
			required: "EBS snapshot is required"
		  },
			method_name:
			{
				required: "Method name is required"
			}
		},
		errorElement: 'span',
		errorPlacement: function(error, element) {
			var er = element.attr("name");
			if (er != "database_type[]" && er != "software_type[]")
			{
				error.insertAfter( element );
				$("<br/>").insertAfter( element );
			}
			else
			{
				error.insertAfter( element.parent().parent() );
				//$("<br/>").insertAfter( element ),			
			}
		}
	});
	$("#idEncryptedFlag").change(function(){
		password_element = $("#idPasswordWrap");
		if ($("#idEncryptedFlag:checked").length == 1)
		{
			password_element.show();
		}
		else
		{
			password_element.hide();
		}
	});
	$("#idDatasetMode").change(function(){
		var method = $("#idDatasetMode").val();
		update_dataset_mode(method);
	});
	$("#idDatasetMode").keypress(function(){
		var method = $("#idDatasetMode").val();
		update_dataset_mode(method);
	});
}

function setup_create_software_type()
{
	$(".form2").validate({
		rules: {
			description: 
			{
				required: true
			},
			image:
			{
				required: true
			},
			platform:
			{
				required: true
			}
		},
		submitHandler: function(form) 
		{
			form.submit();
		},
		messages: {
			description: {
			required: "Description is required"
		  },
		  image: {
			required: "AMI is required"
		  },
		  platform: {
				required: "Platform is required"
			  }
		},
		errorElement: 'span',
		errorPlacement: function(error, element) {
			var er = element.attr("name");
			if (er != "database_type[]" && er != "software_type[]")
			{
				error.insertAfter( element );
				$("<br/>").insertAfter( element );
			}
			else
			{
				error.insertAfter( element.parent().parent() );
				//$("<br/>").insertAfter( element ),			
			}
		}
	});
}

function update_dataset_mode(dataset_type)
{
	if (dataset_type == 0)
	{
		$("#idEBS_wrap").show();
		$("#idEBS").addClass('required');
		$("#idBucket_wrap").hide();
		$("#idBucket").removeClass('required');
		$("#idBucket").removeClass('error');
	}
	else
	{
		$("#idEBS_wrap").hide();
		$("#idEBS").removeClass('required');
		$("#idEBS").removeClass('error');
		$("#idBucket_wrap").show();
		$("#idBucket").addClass('required');
	}
}


function updateMethodParameters()
{
    var method_name = $('#idMethod').val();
$.ajax({
					async: false,
					cache: false,
					type: "POST",
					url: '/api.php?method=get_method_parameters',
					data:  {method : method_name},
					success: function(response){ 
$('#idParameter_wrap').show();
    	$('#idParameter').html('');
    	var data = response;

        $('#idParameter').append('<option value="">---Run All Parameters---</option>');
        
        if (data.status === undefined  || data.status != "error")
        {
	        var data_length = data.length;
	        for(i=0;i<data_length;i++)
	        {
	        	$('#idParameter').append('<option value="'+data[i]+'">'+data[i]+'</option>');
	        }
        }
        updateLaunchOptions();		
			},
		dataType: 'json'
	});
}

function updateLaunchOptions()
{
    if ($('#idParameter option:first-child').attr('selected'))
    {   
         $('#idParameter option:gt(0)').removeAttr('selected');
    }
    
    all_parameters_selected = $('#idParameter option:first-child').attr('selected');
    more_then_one_parameter_selected = $('#idParameter option:selected').length > 1;
	if (more_then_one_parameter_selected || all_parameters_selected)
    {
    	$('#idMethodReplacement_wrap').hide();
    	$('#launch_method_wrap').show();
    	
    	$('#idOverrideMethodParameters').removeAttr('checked');
    	$('#idOverrideMethodParameters_wrap').hide();
    }
    else
    {
    	$('#launch_method_wrap').hide();
    	$('#idOverrideMethodParameters_wrap').show();
    }
    calculateMethodLaunchEstimate();
}

function updateDatasetPassword()
{
	dataset_ebs_items = $('#idDatabase option').each(function(index) {
		dataset_type_id = $(this).val();
		var password_element_name = '#dataset_type_' + dataset_type_id.toString() + '_password';
		var password_element_label_name = '#dataset_type_label_' + dataset_type_id.toString() + '_password';
		password_element = $(password_element_name);
		password_element_label = $(password_element_label_name);
	
		if ($(this)[0].selected)
		{		
			password_element.show();
			password_element_label.show();
			password_element.addClass('required');
		}
		else
		{ 
			password_element.hide();
			password_element_label.hide();
			password_element.removeClass('required');
		}
	});	
	dataset_ebs_items = $('#idDatabase input').each(function(index) {
		dataset_type_id = $(this).val();
		var password_element_name = '#dataset_type_' + dataset_type_id.toString() + '_password';
		var password_element_label_name = '#dataset_type_label_' + dataset_type_id.toString() + '_password';
		password_element = $(password_element_name);
		password_element_label = $(password_element_label_name);
	
		if ($(this)[0].checked)
		{		
			password_element.show();
			password_element_label.show();
			password_element.addClass('required');
		}
		else
		{ 
			password_element.hide();
			password_element_label.hide();
			password_element.removeClass('required');
		}
	});
}
function update_instance_description()
{
    type = $('#idInstancetype').val();
    if (type != null)
    {
        description = const_instance_sizes[type];
        description = description.description.replace(/, /g,'\n');
        $('#idInstanceTypeDescription').val(description);
    }
}

function hide_launch_button()
{
	if (!$(".form2").valid())
		return false;
	$("#button_submit").hide(); 
	$("#message").html("Instance submitted.").show(); 
	return true;
}

function get_instance_hour_price(type)
{
    for(i in const_instance_sizes)
	{
		var cur_size = const_instance_sizes[i];
		if (type == cur_size.id)
		{
			return cur_size.price;
		}
	}
	return 0;
}

function calculateEstimate()
{
	num = $('#idNumber').val();
    if (!isNumber(num))
    	num = 0;
    type = $('#idInstancetype').val();
    cost = get_instance_hour_price(type);
	
    instance_charge = cost*num;
    money_left = user_money - instance_charge;
    temporary_ebs_id = $('#idTempEBS').val();
    has_user_ebs = $('#idCreateUserEBS:checked').length;
    dataset_size = 0;
    dataset_ebs_items = $('#idDatabase input').each(function(index) {
    	dataset_type_id = $(this).val();
    	if ($(this)[0].checked)
    	{
        	size = const_dataset_types[dataset_type_id].size;
		    dataset_size += size;
    	}
	});
	updateDatasetPassword();
	if (temporary_ebs_id != '')
	{
		temp_size = const_temporary_ebs_entries[temporary_ebs_id].size;
	}
	else
	{
		temp_size = 0;
	}
	if (has_user_ebs != 0)
	{
		user_ebs_size = 20*num;
	}
	else
	{
		user_ebs_size = 0;
	}
    
    total_storage_size = (dataset_size + temp_size + user_ebs_size)*num;
    storageCharge = Math.ceil((total_storage_size * 0.1 / 730) * 1000) / 1000;
    totalCharge = Math.ceil((instance_charge + storageCharge) * 1000) / 1000;
    
    instance_charge = Math.round(1000 * instance_charge * (100 + admin_factor) / 100) / 1000;
    storageCharge = Math.round(1000 * storageCharge * (100 + admin_factor) / 100) / 1000;
    totalCharge = Math.round(1000 * totalCharge * (100 + admin_factor) / 100) / 1000;
    
    if (money_left < 0)
    {
    	$('#idLimit').css('color', 'red');
    	$('#idStorageCharge').css('color', 'red');
    	$('#idTotalCharge').css('color', 'red');
    }
    else
    {
    	$('#idLimit').css('color', '#fff');
    	$('#idStorageCharge').css('color', '#fff');
    	$('#idTotalCharge').css('color', '#fff');
    }

    $('#idLimit').val(instance_charge);
    $('#idStorageCharge').val(storageCharge);
    $('#idTotalCharge').val(totalCharge);
    
    $('.form2').validate().element('#idLimit');
}

function calculateMethodLaunchEstimate()
{
	method_name = $('#idMethod :selected').val();
	if (method_name != undefined)
	{
        separate_instances = ($('#idLaunchMethod:checked').length == 1);
        parameters_count = $('#idParameter option').length - 1;
        all_instances = $('#idParameter :checked').val() == '';
        if (separate_instances && all_instances)
        {
        	num = parameters_count;
        }
        else
        {
        	num = 1;
        }
	}
	else
	{
		num = 0;
	}
    type = $('#idInstancetype').val();
    cost = get_instance_hour_price(type);
	
    instance_charge = cost*num;
    money_left = user_money - instance_charge;
    temporary_ebs_id = $('#idTempEBS').val();
    dataset_size = 0;
    dataset_ebs_items = $('#idDatabase input').each(function(index) {
    	dataset_type_id = $(this).val();
    	if ($(this)[0].checked)
    	{
        	size = const_dataset_types[dataset_type_id].size;
		    dataset_size += size;
    	}
	});
	updateDatasetPassword();

	if (temporary_ebs_id != '')
	{
		temp_size = const_temporary_ebs_entries[temporary_ebs_id].size;
	}
	else
	{
		temp_size = 0;
	}
    	        
    user_ebs_size = 20*num;
    
    total_storage_size = (dataset_size + temp_size + user_ebs_size)*num;
    storageCharge = Math.ceil((total_storage_size * 0.1 / 730) * 1000) / 1000;
    totalCharge = Math.ceil((instance_charge + storageCharge) * 1000) / 1000;
    if (money_left < 0)
    {
    	$('#idLimit').css('color', 'red');
    	$('#idStorageCharge').css('color', 'red');
    	$('#idTotalCharge').css('color', 'red');
    }
    else
    {
    	$('#idLimit').css('color', '#fff');
    	$('#idStorageCharge').css('color', '#fff');
    	$('#idTotalCharge').css('color', '#fff');
    }

    instance_charge = Math.round(1000 * instance_charge * (100 + admin_factor) / 100) / 1000;
    storageCharge = Math.round(1000 * storageCharge * (100 + admin_factor) / 100) / 1000;
    totalCharge = Math.round(1000 * totalCharge * (100 + admin_factor) / 100) / 1000;
        
    $('#idLimit').val(instance_charge);
    $('#idStorageCharge').val(storageCharge);
    $('#idTotalCharge').val(totalCharge);
    
    $('.form2').validate().element('#idLimit');
}

function init_validator()
{
	$.validator.addMethod('positiveNumber',    
			function (value) {         
				return Number(value) > 0;    
				}, 'Enter a positive number.');
	$.validator.addMethod('nonNegativeNumber',    
			function (value) {         
				return Number(value) >= 0;    
				}, 'Enter a non-negative number.');
		$.validator.addMethod('naturalNumber',    
			function (value, element) {         
				return this.optional(element) || 
					Number(value) == Number(value).toFixed(0) ;
				}, 'Enter a positive non-fractional number.');
		$.validator.addMethod('unique_email',    
			function (value, element) {         
					email_value = $("#idEmail").val();
					user_id_value = $("#internal_id").val();
					//alert(email_value);
					validation_result = false;
					$.ajax({
						async: false,
						cache: false,
						type: "POST",
						url: 'api.php?method=check_email',
						data: {email:email_value, user_id:user_id_value},
						success: function(response){ 
							
								if (response == "true")
								{
									//alert("Success " + response);
									validation_result = true;
								}
								else
								{
									validation_result = false;
								}
								
							},
						dataType: 'json'
					});
					//alert(validation_result);
					return validation_result;
				}, 'Entered email already assigned to another user.');
		$.validator.addMethod('unique_login',    
			function (value, element) {         
					login_value = $("#idUserid").val();
					user_id_value = $("#internal_id").val();
					//alert(login_value);
					validation_result = false;
					$.ajax({
						async: false,
						cache: false,
						type: "POST",
						url: 'api.php?method=check_login',
						data: {login:login_value, user_id:user_id_value},
						success: function(response){ 
							//alert("R " + login_value + user_id_value+response);
								if (response == "true")
								{
									validation_result = true;
								}
								else
								{
									validation_result = false;
								}
								
							},
						dataType: 'json'
					});
					//alert("VR " +validation_result);
					return validation_result;
				}, 'Entered login already assigned to another user.');
        $.validator.addMethod('unique_org_name', function (value, element){
            return checkOrgName(value);
        }, 'Organization with entered name already exists.');
		$.validator.addMethod('instances_limit',    
				function (value, element) {         
						number_value = value;
						active_instances_value = $("#idActiveInstances").val();
						num_instances_value = $("#idMaxInstancesAllowed").val();
						validation_result = false;
						validation_result = 
							(Number(active_instances_value) + Number(number_value))
							<= Number(num_instances_value);

						if ((Number(active_instances_value) + Number(number_value)) <= Number(num_instances_value))
						{
							return true;
						}
						return false;
					}, 'You exceeded limit on running instances.');
			$.validator.addMethod('instances_name_unique',    
				function (value, element) {         
						return check_instance_name(value) == true;
					}, 'This instance name already taken.');
			$.validator.addMethod('system_instance_unique_name',
				function (value, element) {
					return check_system_instance_name(value) == true;
				}, "This system instance name already taken.");
			$.validator.addMethod('system_instance_unique_host',
				function (value, element) {
					return check_system_instance_unique_host(value) == true;
				}, "This system instance host already taken.");
			$.validator.addMethod('ec2_instance_dns',
				function(value, element){
					return check_instance_host(value) == true;
				}, "Please specify correct instance DNS");
			$.validator.addMethod('valid_dataset_password',    
					function (value, element) {         
							password_value = $(element).val();
							name_value = $(element).attr('name');
							
							password_hash = hex_md5(password_value);
							password_value = null;
							
							dataset_id = /\d+/.exec(name_value);
							
							checkbox_id = '#dataset_type_'+dataset_id;
							if (checkbox_id)
							{
								checked = $(checkbox_id).attr('checked');
								if (!checked)
									return true;
							}
							return true; // TODO: Somehow we have invalid password 
							validation_result = false;
							$.ajax({
								async: false,
								cache: false,
								type: "POST",
								url: 'api.php?method=check_dataset_password',
								data: {dataset:dataset_id, password:password_hash},
								success: function(response){ 
									
										if (response == "true")
										{
											validation_result = true;
										}
										else
										{
											validation_result = false;
										}										
									},
								dataType: 'json'
							});
							//alert(validation_result);
							return validation_result;
						}, 'Invalid password.');
			$.validator.addMethod('dataset_password',    
					function (value, element) {         
							if ($("#idEncryptedFlag").val() != 'Y')
							{
								return true;
							}
							
							validation_result = (value != "");
							return validation_result;
						}, 'Password required for encrypted dataset.');
            $.validator.addMethod('svn_folder', function(element, value) {
                return checkSvnFolder(value);
            }, "Please specify correct SVN folder");
}

function setup_create_organization()
{
	init_validator();
	
	$(".form2").validate({
		rules: {
			organization_name: 
			{
				required: true,
                unique_org_name: true
			},
			organization_admin:
			{
				required: true,
				minlength: 2,
				maxlength: 128,
				unique_login: true
			},
			organization_admin_email:
			{
				required: true,
				email: true,
				unique_email: true,
				maxlength: 128
			},
			organization_admin_firstname:
			{
				required: true
			},
			organization_admin_lastname:
			{
				required: true
			},
			organization_admin_title:
			{
				required: true,
				maxlength: 128
			},
			organization_admin_phone:
			{
				required: true
			},
			organization_city:
			{
				required: true
			},
			organization_state:
			{
				required: true
			},
			organization_zip:
			{
				required: true
			},
			organization_budget:
			{
				required: true,
				number: true,
				positiveNumber: true,
				naturalNumber: true
			},
			organization_instances_limit:
			{
				required: true,
				number: true,
				positiveNumber: true,
				naturalNumber: true
			},
			organization_users_limit:
			{
				required: true,
				number: true,
				positiveNumber: true,
				naturalNumber: true
			},
			organization_admin_factor:
			{
				required: true,
				number: true,
				nonNegativeNumber: true,
				naturalNumber: true
			},
			"database_type[]":
			{
				required: true,
				minlength: 1
			}
		},
		submitHandler: function(form) 
		{
			$("#button_submit").attr('disabled', 'disabled');
			form.submit();
		},
		messages: {
			organization_name: {
			required: "Organization name is required"
		  },
		  organization_admin: {
			required: "Admin login is required"
		  },
		  organization_admin_email: {
			required: "Admin email is required"
		  },
		  organization_city: {
			required: "City is required"
		  },
		  organization_state: {
			required: "State is required"
		  },
		  organization_zip: {
			required: "ZIP code is required"
		  },
		  organization_budget: {
			required: "Budget is required"
		  },
		  organization_instances_limit: {
			required: "Instances limit is required"
		  },
		  organization_users_limit: {
			required: "User limit is required"
		  },
		  organization_admin_factor: {
			required: "Admin factor is required"
		  },
		  "database_type[]": {
			required: "At least one dataset must be selected",
			minlength: "At least one dataset must be selected"
		  },				 
		  "software_type[]": {
			required: "At least one software must be selected",
			minlength: "At least one software must be selected"
		  }
		},
		errorElement: 'span',
		errorPlacement: function(error, element) {
			var er = element.attr("name");
			if (er != "database_type[]" && er != "software_type[]")
			{
				error.insertAfter( element );
				$("<br/>").insertAfter( element );
			}
			else
			{
				error.insertAfter( element.parent().parent() );
				//$("<br/>").insertAfter( element ),			
			}
		}
	});
}



function setup_edit_organization()
{
	init_validator();
	
	$(".form2").validate({
		rules: {
			organization_name: 
			{
				required: true,
                unique_org_name: true
			},
			organization_admin_id:
			{
				required: true
			},
			organization_city:
			{
				required: true
			},
			organization_state:
			{
				required: true
			},
			organization_zip:
			{
				required: true
			},
			organization_budget:
			{
				required: true,
				number: true,
				positiveNumber: true,
				naturalNumber: true
			},
			organization_admin_factor:
			{
				required: true,
				number: true,
				nonNegativeNumber: true,
				naturalNumber: true
			},
			organization_instances_limit:
			{
				required: true,
				number: true,
				positiveNumber: true,
				naturalNumber: true
			},
			organization_users_limit:
			{
				required: true,
				number: true,
				positiveNumber: true,
				naturalNumber: true
			},
            organization_svn_folder:
            {
                svn_folder: true
            },
			"database_type[]":
			{
				required: true,
				minlength: 1
			}
		},
		submitHandler: function(form) 
		{
			$("#button_submit").attr('disabled', 'disabled');
			form.submit();
		},
		messages: {
			organization_name: {
			required: "Organization name is required"
		  },
		  organization_admin_id: {
			required: "Admin is required"
		  },
		  organization_city: {
			required: "City is required"
		  },
		  organization_state: {
			required: "State is required"
		  },
		  organization_zip: {
			required: "ZIP code is required"
		  },
		  organization_budget: {
			required: "Budget is required"
		  },
		  organization_instances_limit: {
			required: "Instances limit is required"
		  },
		  organization_users_limit: {
			required: "User limit is required"
		  },
		  organization_admin_factor: {
			required: "Admin factor is required"
		  },
		  "database_type[]": {
			required: "At least one dataset must be selected",
			minlength: "At least one dataset must be selected"
		  },				 
		  "software_type[]": {
			required: "At least one software must be selected",
			minlength: "At least one software must be selected"
		  }
		},
		errorElement: 'span',
		errorPlacement: function(error, element) {
			var er = element.attr("name");
			if (er != "database_type[]" && er != "software_type[]")
			{
				error.insertAfter( element );
				$("<br/>").insertAfter( element );
			}
			else
			{
				error.insertAfter( element.parent().parent() );
				//$("<br/>").insertAfter( element ),			
			}
		}
	});
}
/*
Function that initialize all user interaction within /user_history page.
*/
function setup_user_history()
{
	//init_validator();
	var update_users_list = function(){
		val = $('select#idOrganization').val();
		$.ajax({
			async: false,
			cache: false,
			type: "POST",
			url: 'api.php?method=get_organization_users',
			data: {organization_id : val},
			success: function(response){ 
					if (response != "flase")
					{
						$('#idUser').html('');
				    	var data = response;

				        $('#idUser').append('<option value="0">---All---</option>');
				        if (data !== undefined)
				        {
					        var data_length = data.length;
					        for(i=0;i<data_length;i++)
					        {
					        	u = data[i];
					        	$('#idUser').append('<option value="'+u.user_id+'">'+u.last_name+', '+u.first_name+'('+u.login_id+')</option>');
					        	u = null;
					        }
					        data = null;
				        }
					}					
				},
			dataType: 'json'
		});
	};
	$('select#idOrganization').change(update_users_list);
	$('select#idOrganization').keydown(update_users_list);
}

function setup_budget()
{
    $("#date-start").datepicker({
        buttonImage: '/images/datepicker.png',
        buttonImageOnly: true,
        showOn: "button",
        dateFormat: 'mm/dd/yy'
    });
    $("#date-end").datepicker({
        buttonImage: '/images/datepicker.png',
        buttonImageOnly: true,
        showOn: "button",
        dateFormat: 'mm/dd/yy'
    });

    $("#budget-table td.org-name").click(
        function()
        {
            if ($(this).attr("expanded") != "expanded") {
                $('img.expand', this).attr('src', "/images/table-minus.png");
                $(this).attr("expanded", "expanded");
                $(this).parent().parent().next().css("display", "table-row-group");
            }
            else
            {
                $('img.expand', this).attr('src', "/images/table-plus.png");
                $(this).attr("expanded", "collapsed");
                $(this).parent().parent().next().css("display", "none");
            }

        }
    );

    if (page_mode == 'graph') {
        var css_id = "#budget-graph-image";

        $(css_id).css("height", ($("#footer").offset().top - $(css_id).offset().top - 100) + 'px');

        $.plot($(css_id), graph_data, graph_options);
        var legend = $(css_id + " .legend table");
        $(css_id).parent().append(legend);
        $(legend).css("position", "static");
        $(css_id + " .legend").remove();
    }
}

function setup_result_storage()
{
    $("#result-loading").dialog({
        autoOpen: false,
        resizable:false,
        width: 500,
        /*height: 300,*/
        open : function () {
            $("#result-loading").html('Loading your data.<br />Please wait, this may take some time.<br /><br/>');
            var table = $("<table/>");
            table.attr({'border':0,'cellspacing':0});
            var filesource = $("#results_file").val();
            filesource = filesource.split("\\");
            var filename = filesource[filesource.length - 1];
            var uploadType = $.trim($('label[for="'+$('input[name="method"]:checked').attr('id')+'"]').text());
            var selectId = $('input[name="method"]:checked').attr('id');
            table.append('<tr><td><b>Type: </b></td><td style="padding-left: 20px;">'+uploadType+'</td></tr>');
            if (selectId == 'method-method') {
                table.append('<tr><td><b>Experiment: </b></td><td style="padding-left: 20px;">'+$("#idExperiment option:selected").text()+'</td></tr>');
            }
            else if (selectId == 'method-oscar') {
                table.append('<tr><td><b>Dataset: </b></td><td style="padding-left: 20px;">'+$("#idDataset option:selected").text()+'</td></tr>');
            }
            table.append('<tr><td><b>File: </b></td><td style="padding-left: 20px;">'+((filename)?filename:'<span style="color:red">no file selected</span>')+'</td></tr>');
            table.append('<tr><td><b>Load into S3: </b></td><td style="padding-left: 20px;">'+(($("#load-s3").attr('checked'))?'<span style="color:green">enabled</span>':'<span style="color:red">disabled</span>')+'</td></tr>');
            table.append('<tr><td><b>Load into Oracle database: </b></td><td style="padding-left: 20px;">'+(($("#load-oracle").attr('checked'))?'<span style="color:green">enabled</span>':'<span style="color:red">disabled</span>')+'</td></tr>');
            table.append('<tr><td><b>Overwrite existing results: </b></td><td style="padding-left: 20px;">'+(($("#override-results").attr('checked'))?'<span style="color:green">enabled</span>':'<span style="color:red">disabled</span>')+'</td></tr>');
            $("#result-loading").append(table);
        }
    });
    $("#result-storage-form").submit(
        function()
        {
            var is_storage_selected = false;
            var storages = new Array("load-oracle","load-s3");
            for (var i in storages) {
                if($('#'+storages[i]+':checked').attr('id')) {
                    is_storage_selected = true;
                    break;
                }
            }
            if(!is_storage_selected) {
                alert('You should select at least one storage type.');
            }
            else {
                $("#result-loading").dialog('open');
            }
            return is_storage_selected;
        }
    );
}

function setup_edit_system_instance()
{
	init_validator();

	$(".form2").validate({
		onsubmit:true,
		onfocusout:false,
		rules: {
			name: {
				required: true,
				maxlength: 50,
				system_instance_unique_name: true
			},
			host:
			{
				required: true,
				maxlength: 50,
				ec2_instance_dns: true,
				system_instance_unique_host: true
			},
            end_date: {
                required: false,
                date: true
            }
		},
		submitHandler: function(form) {
			form.submit();
		},
		messages: {
		  name: {
			required: "Name is required"
		  },
		  host: {
			required: "Host is required"
		  }
		},
		errorElement: 'span',
		errorPlacement: function(error, element) {
			var er = element.attr("name");
			if (er != "database_type[]" && er != "software_type[]")
			{
				error.insertAfter( element );
				$("<br/>").insertAfter( element );
			}
			else
			{
				error.insertAfter( element.parent().parent() );
				//$("<br/>").insertAfter( element ),
			}
		}
	});
    $("#idEDate").datepicker({
        buttonImage: '/images/datepicker.png',
        buttonImageOnly: true,
        showOn: "button",
        dateFormat: 'yy-mm-dd'
    });
}

function setup_create_system_instance()
{
	init_validator();

	$(".form2").validate({
		onsubmit:true,
		onfocusout:false,
		rules: {
			name: {
				required: true,
				maxlength: 50,
				system_instance_unique_name: true
			},
			host:
			{
				required: true,
				maxlength: 50,
				ec2_instance_dns: true,
				system_instance_unique_host: true
			}
		},
		submitHandler: function(form) {
			form.submit();
		},
		messages: {
		  name: {
			required: "Name is required"
		  },
		  host: {
			required: "Host is required"
		  }
		},
		errorElement: 'span',
		errorPlacement: function(error, element) {
			var er = element.attr("name");
			if (er != "database_type[]" && er != "software_type[]")
			{
				error.insertAfter( element );
				$("<br/>").insertAfter( element );
			}
			else
			{
				error.insertAfter( element.parent().parent() );
				//$("<br/>").insertAfter( element ),
			}
		}
	});
}

function setup_result_s3() {
    $("#runs-list").dialog({
        autoOpen: false
    });
    $("#run-details").dialog({
        autoOpen: false,
        width: 560,
        modal: false
    });

    $(".run-data").bind('click', show_s3_run_list);
}

function show_s3_run_list() {
    // Show nothing for empty cells
    if ($(this)[0].localName != 'input' && jQuery.trim($(this).text()) == '') {
        return false;
    }

    var a = $(this).attr('title').split('/');
    datasetName = a[0];
    methodName  = a[1];

    $("#run-details").dialog('close');
    $("#runs-list").html($("#ajax-loader-container").html());
    $("#runs-list").dialog('option', 'title', datasetName + ' ' + methodName + ' Runs List');
    $("#runs-list").dialog('option', 'width', 320);
    $("#runs-list").dialog('open');

    var html = '';

    $.ajax({
    	async: false,
        cache: false,
        type: "POST",
        url: '/public/s3results/runs',
        data: {
            method: methodName,
            dataset: datasetName,
            experiment: $('#idExperiment').val()
        },
        success: function(response){
            if (response.error == "0")
            {
                var link_class;
                for(i=0;i<response.data.length;i++) {
                    run = response.data[i];
                    link_class = run.notUploaded ? ' class="not-uploaded"' : '';
                    link_js = "return show_s3_run_details('"+datasetName+"', '"+methodName+"', '"+run.name+"')";
                    html += ('<tr><td><a'+link_class+' onclick="'+link_js+'" href="javascript:void(0);" >'+run.name+'</a></td><td>');
                    if (! run.notUploaded)
                        html += '<a href="/public/s3results/downloadrun/method/'+methodName+'/dataset/'+datasetName+'/run/'+run.name+'/experiment/'+$('#idExperiment').val()+'"><nobr>Download run results</nobr></a>'
                    html += '</td></tr>'
                }
                if (!response.data.length) {
                    html = '<p>There were no runs found for your request.</p>';
                } else {
                    html = '<table class="s3-runs">' + html + '</table>';
                }
            }
            else
            {
                html = 'Error occured while getting Runs List.';
            }
        },
        dataType: 'json'
    });

    if ('' != html) {
        $("#runs-list").dialog('close');
        $("#runs-list").html(html);
        $("#runs-list").dialog('open');
    }

    return false;
}

function show_s3_run_details(datasetName, methodName, runName) {
    var html = '';
    var button_title = datasetName+'/'+methodName;
    var back_button = '<button onmouseover="this.style.backgroundPosition=\'bottom\';" '
                     + 'onmouseout="this.style.backgroundPosition=\'top\';" '
                     + 'class="button_90" type="button" id="back-button" title="'+button_title+'">Back</button>';
    $("#runs-list").dialog('close');
    // this will not work in Chrome (there is no official bug in jquery bug tracker)
    $("#run-details").html($("#ajax-loader-container").html() + back_button);
    
    $("#run-details").dialog('option', 'title', runName + ' Run Details');
    $("#run-details").dialog('open');
    $("#back-button").bind('click', show_s3_run_list);

    $.ajax({
        async: false,
        cache: false,
        type: "POST",
        url: '/public/s3results/rundetails',
        data: {
            dataset: datasetName,
            method: methodName,
            run: runName,
            experiment: $('#idExperiment').val()
        },
        error: function() {
            html = 'Error occured while getting run details.';
        },
        success: function(response){
            if (response.error == "0")
            {
                html = '';
                html += '<tr><td>Method:</td><td>'+methodName+'</td></tr>';
                html += '<tr><td>Dataset:</td><td>'+datasetName+'</td></tr>';
                html += '<tr><td>Run:</td><td>'+runName+'</td></tr>';
                html = '<table style="width: 320px;"><tbody>' + html + '</tbody></table>';

                html += '<table>';
                html += '<thead><tr><th>Files</th><th>Date</th><th>Size</th></tr></thead>';
                var obj_class;
                var emptyDate = '&lt;No Date&gt;';
                var responseLength = response.data.length;
                for (i=0; i<responseLength; i++) {
                    file = response.data[i];
                    isUploaded = file.date && emptyDate != file.date && file.url != '';
                    if (isUploaded) {
                        obj_class = '';
                        fileNameEntry = '<a href="'+file.url+'">'+file.name+'</a>';
                    } else {
                        obj_class = ' class="not-uploaded"';
                        fileNameEntry = file.name;
                    }
                    html += '<tr'+obj_class+'>';
                    html += '<td>'+fileNameEntry+'</td>';
                    html += '<td><nobr>'+(file.date?file.date:emptyDate)+'</nobr></td>';
                    html += '<td class="number">'+(file.size?file.size:'-')+'</td>';
                    html += '</tr>';
                }
                
                html += '<tr><td rowspan="3" style="margin-left: 100px;">'
                     + back_button + '</td></tr></table>';
            }
            else
            {
                html = 'Error occured while getting run details.';
            }
        },
        dataType: 'json'
    });

    $("#run-details").dialog('close');
    $("#run-details").html(html);
    $("#run-details").dialog('open');
    $("#back-button").bind('click', show_s3_run_list);

    return false;
}

function setup_result_oracle() {
    $("#runs-list").dialog({
        autoOpen: false,
        width: 570
    });
    $("#run-details").dialog({
        autoOpen: false,
        width: 1000
    });
    $("#S3-results-table td.run-data").each(function() {
    	var text = $(this).text();
		var title = $(this).attr('title');
		var className = $(this).hasClass("red") ? "red" : "";
		var link = $("<a />")
			.attr('href','#')
			.attr('class',className)
			.attr('title',title)
			.text(text);
        $(this).html('').append(link);
    });
    $("#S3-results-table .run-data").bind(
        'click', show_oracle_run_details
    );
}

function show_oracle_run_list() {
    // Show nothing for empty cells
    if ($(this)[0].localName != 'input' && jQuery.trim($(this).text()) == '') {
        return false;
    }
    
    var a = $(this).attr('title').split('/');
    $.ajax({
    	async: false,
        cache: false,
        type: "POST",
        url: '/api.php?method=get_results_run_list',
        data: {
            method:a[1],
            dataset:a[0]
        },
        success: function(response){
            if (response.error == "0")
            {
                html = '';
                i = 0;
                for(i=0;i<response.data.length;i++) {
                	var run = response.data[i];
                    var total_amount = run['TOTAL_AMOUNT'];
                    var loaded_amount = run['LOADED_AMOUNT'];
                    var runName = run['RUN_NAME'];
                    var startTime = run['START_TIME'];
                    var duration = run['DURATION'];
                    var analysis_id = run['ANALYSIS_ID'];
                    total_amount = parseInt(total_amount);
                    loaded_amount = parseInt(loaded_amount);
                    var incomplete = total_amount > loaded_amount;
                    html += ('<tr><td>'+analysis_id+'</td><td'+(incomplete?' class="red"':'')+'><a href="javascript: return false;" class="run-details-link '+(incomplete?'red':'')+'" param="'+a[0]+'/'+a[1]+'/'+runName+'">' + runName + '</a></td><td>'+startTime+'</td><td>'+duration+'</td></tr>');
                }
                if (response.data.length) {
                    html = '<div class="tableContainer"><table><thead><tr><th>Analysis ID</th><th>Run Name</th><th>Start Date</th><th>Duration</th></tr></thead><tbody>' + html + '</tbody></table></div>';
                }
                else {
                    html = '<p>There were no runs found for your request.</p>';
                }

                $("#run-details").dialog('close');
                $("#runs-list").html(html);
                $(".run-details-link").bind('click', show_oracle_run_details);
                $("#runs-list").dialog('option', 'title', a[0] + ' ' + a[1] + ' Runs List');
                $("#runs-list").dialog('open');
            }
            else
            {
                alert('Error occured while getting Runs List.');
            }
        },
        dataType: 'json'
    });
    
    return false;
}

function show_oracle_run_details() {
    // dataset, method, run_id, run_name
    params = $(this).attr('title').split('/');

    var html = '';
    datasetName = params[0];
    methodName  = params[1];
    experimentId = $('#idExperiment').val();
    
    $.ajax({
        async: false,
        cache: false,
        type: "POST",
        url: '/public/run.results/get-oracle-run-details',
        data: {
            source: datasetName,
            method: methodName,
            experiment: experimentId
        },
        error: function() {
        	html = 'Error occured while getting run details.';
        },
        success: function(response){
        	
            header = '';
            header += '<tr><td>Experiment:</td><td>'+response.experiment_name+'</td></tr>';
            header += '<tr><td>Method:</td><td>'+methodName+'</td></tr>';
            header += '<tr><td>Dataset:</td><td>'+datasetName+'</td></tr>';

            wrapperHeaderTable = '<table style="width: 720px;"><tbody>' + header + '</tbody></table>';
            contentMissing = '';
            

        	if (response.error == "0")
            {
            	data = response.data;
                content = '<thead><tr><th>Analysis ID</th><th>Files</th>';
                has_suppl1 = false;
                if (typeof data[0]['SUPPL1_NUM'] != "undefined") {
                    has_suppl1 = true;
                    content += '<th>Supplemenatal 1</th>'
                }
                has_suppl2 = false;
                if (typeof data[0]['SUPPL2_NUM'] != "undefined") {
                    has_suppl2 = true;
                    content += '<th>Supplemenatal 2</th>'
                }
                content += '<th>Upload date</th><th>Records</th></tr></thead>';
                content += '<tbody>';
                for(i=0;i<data.length;i++) {
                	var item = data[i];
                	var analysisId = item['ANALYSIS_ID'];
                	var amount = item['AMOUNT'];
                	var total_amount = item['TOTAL_AMOUNT'];
                	var outputFileName = item['OUTPUT_FILE_NAME'];
                	var addDate = item['ADD_DATE'];
                	var amountFormatted = item['AMOUNT_FORMATTED'];
                	var incomplete = parseInt(total_amount) > parseInt(amount);
                    var suppl1_count = item['SUPPL1_NUM'];
                    var suppl2_count = item['SUPPL2_NUM'];
                    var suppl1_filename = item['SUPPLEMENT1_FILENAME'];
                	
                	if (amount > 0) {
            			fileUrl = '/public/run.results/downloadresult?analysisId=' + analysisId + 
            				'&file=' + outputFileName + '&experiment='+experimentId + '&source='+datasetName;
            			if (incomplete) {
            				fileUrl="javascript: return false;"; 
            			}
                        if (has_suppl1) {
                            if (typeof suppl1_count != 'undefined' && parseInt(suppl1_count)) {
                                suppl1_url = '/public/run.results/downloadresult?analysisId=' + analysisId +
                                '&file=' + suppl1_filename + '&experiment='+experimentId + '&source='+datasetName+'&type=suppl1'
                                suppl1 = '<td><a href="'+suppl1_url+'">Supplemental 1</a></td>'
                            }
                            else
                                suppl1 = '<td></td>'
                        } else {
                            suppl1 = ''
                        }
                        if (has_suppl2) {
                            if (typeof suppl2_count != 'undefined' && parseInt(suppl2_count)) {
                                suppl2_url = '/public/run.results/downloadresult?analysisId=' + analysisId +
                                '&file=ALL_' + outputFileName + '&experiment='+experimentId + '&source='+datasetName+'&type=suppl2'
                                suppl2 = '<td><a href="'+suppl2_url+'">Supplemental 2</a></td>'
                            }
                            else
                                suppl2 = '<td></td>'
                        } else {
                            suppl2 = '';
                        }
                        
                        
                        content += '<tr><td>'+analysisId+'</td><td'+(incomplete?' class="red"':'')+'><a href="' + fileUrl + '" '+(incomplete?' class="red"':'')+'>' + outputFileName + '</a></td>'
                            +suppl1
                            +suppl2
                        	+'<td><nobr>' + addDate + '</nobr></td>'
                        	+'<td class="number">' + amountFormatted + '</td>'
                        	+"</tr>\n";
                    }
                    else {
                    	contentMissing += '<tr class="red"><td>'+analysisId+'</td><td>' + outputFileName + '</td><td>-</td><td>-</td></tr>' + "\n";
                    }
                }
                content += '</tbody>';
            }
            else
            {
            	content = 'Error occured while getting run details.';
            }
            html = wrapperHeaderTable + '<table style="width: 720px;">' + $("#data-buffer").attr('data') + content + contentMissing + '</table>'
            + '<tr><td rowspan="2" style="margin-left: 100px;">'
        	+ '<input onmouseover="this.style.backgroundPosition=\'bottom\';" onmouseout="this.style.backgroundPosition=\'top\';" class="button_90" type="button" id="back_button" value="Close" title="'+params[0]+'/'+params[1]+'" /></td></tr>';
        },
        dataType: 'json'
    });

    $("#runs-list").dialog('close');
    $("#run-details").dialog('option', 'title', 'Run Details');
    $("#run-details").html(html);
    $("#back_button").bind('click', function(){$("#run-details").dialog('close');});
    $("#run-details").dialog('open');
    $("#data-buffer").attr('data', '');
    
    return false;
}

function setup_generateOsim2Summary()
{
	$(".form2").validate({
		rules: {
			newId: {
				required: true,
				number: true,
				positiveNumber: true,
				maxLength: 15
			},
			description: {
				required: true,
				maxlength: 2000
			},
			name: {
				required: true,
				maxlength: 64
			}
		},
		submitHandler: function(form) {
			form.submit();
		},
		messages: {
		  description: {
			required: "Description is required",
			minlength: "Description should be at least 2 characters long."
		  },
		  name: {
				required: "Name is required",
				minlength: "Description should be at least 2 characters long."
			  }
		},
		errorElement: 'span',
		errorPlacement: function(error, element) {
			var er = element.attr("name");
			if (er != "database_type[]" && er != "software_type[]")
			{
				error.insertAfter( element );
				$("<br/>").insertAfter( element );
			}
			else
			{
				error.insertAfter( element.parent().parent() );
				//$("<br/>").insertAfter( element ),			
			}
		}
	});
	jQuery("#sourceId").change(function(){
		currentSource = jQuery("#sourceId").val();
		filteredSources = allSources.filter(function(obj){return obj.id == currentSource});
		if (filteredSources.length == 0) {
			return;
		}
		filteredSource = filteredSources[0];
		jQuery("#newId").val(filteredSource.id);
		jQuery("#name").val(filteredSource.name);
		jQuery("#description").val(filteredSource.description);
	});
	jQuery("#sourceId").change();
}

function setup_loadOsim2Summary()
{
	setupOverrideDbServer();
	$(".form2").validate({
		rules: {
			newId: {
				required: true,
				number: true,
				positiveNumber: true,
				maxLength: 15
			},
			description: {
				required: true,
				maxlength: 2000
			},
			name: {
				required: true,
				maxlength: 64
			},
			definition: {
				required: true
			}
		},
		submitHandler: function(form) {
			form.submit();
		},
		messages: {
		  description: {
			required: "Description is required",
			minlength: "Description should be at least 2 characters long."
		  },
		  name: {
				required: "Name is required",
				minlength: "Description should be at least 2 characters long."
		  },
		  definition: {
			  required: "Definition of summary is required"
		  }
		},
		errorElement: 'span',
		errorPlacement: function(error, element) {
			var er = element.attr("name");
			if (er != "database_type[]" && er != "software_type[]")
			{
				error.insertAfter( element );
				$("<br/>").insertAfter( element );
			}
			else
			{
				error.insertAfter( element.parent().parent() );
				//$("<br/>").insertAfter( element ),			
			}
		}
	});
}

function setup_osim2SummaryDetails()
{
	
}

function setup_updateOsim2Summary()
{
	$(".form2").validate({
		rules: {
			newId: {
				required: true,
				number: true,
				positiveNumber: true,
				maxLength: 15
			},
			description: {
				required: true,
				maxlength: 2000
			},
			name: {
				required: true,
				maxlength: 64
			}
		},
		submitHandler: function(form) {
			form.submit();
		},
		messages: {
		  description: {
			required: "Description is required",
			minlength: "Description should be at least 2 characters long."
		  },
		  name: {
				required: "Name is required",
				minlength: "Description should be at least 2 characters long."
		  }
		},
		errorElement: 'span',
		errorPlacement: function(error, element) {
			var er = element.attr("name");
			if (er != "database_type[]" && er != "software_type[]")
			{
				error.insertAfter( element );
				$("<br/>").insertAfter( element );
			}
			else
			{
				error.insertAfter( element.parent().parent() );
				//$("<br/>").insertAfter( element ),			
			}
		}
	});
}

function setup_generateOsim2Dataset()
{
	jQuery("#summaryId").change(function(){
		currentSource = jQuery("#summaryId").val();
		filteredSources = allSets.filter(function(obj){return obj.id == currentSource});
		if (filteredSources.length == 0) {
			return;
		}
		filteredSource = filteredSources[0];
		jQuery("#description").val(filteredSource.description);
		jQuery("#summaryId").trigger('datasetNameChanged');
	});
	jQuery("#patients").change(function(){
		jQuery("#summaryId").trigger('datasetNameChanged');
	});
	jQuery("#addSignal").change(function(){
		jQuery("#summaryId").trigger('datasetNameChanged');
		jQuery("#signal").parent().toggle();
	});
	jQuery("#summaryId").bind('datasetNameChanged', function(){
		currentSource = jQuery("#summaryId").val();
		filteredSources = allSets.filter(function(obj){return obj.id == currentSource});
		if (filteredSources.length == 0) {
			return;
		}
		filteredSource = filteredSources[0];
		var description = jQuery("#description").val();
		var patients = jQuery("#patients").val();
		var addSignal = jQuery("#addSignal").attr('checked');
		var patientsM = Math.floor(patients / 1000);
		var patientsK = Math.floor(patients % 1000);
		var patientsString = "";
		if (patientsM != 0) {
			patientsString = patientsString + patientsM + "M";
		}
		if (patientsK != 0) {
			patientsString = patientsString + patientsK + "K";
		}
		var signalSuffix = addSignal ? "1" : "0"
		var name = filteredSource.name + "_" + patientsString + "_" + signalSuffix;
		jQuery("#name").val(name);
	});
	jQuery("#summaryId").change();
	var addSignal = jQuery("#addSignal").attr('checked');
	jQuery("#signal").parent().toggle(addSignal);

	setupOverrideDbServer();
	
	$(".form2").validate({
		rules: {
			patients: {
				required: true,
				number: true,
				positiveNumber: true,
				maxLength: 15
			},
			name: {
				required: true,
				maxlength: 300
			}
		},
		submitHandler: function(form) {
			form.submit();
		},
		messages: {
			patients: {
			required: "Patients qty is required",
			number: "Patients qty should be a positive number.",
			positiveNumber: "Patients qty should be a positive number."
		  },
		  name: {
				required: "Name is required",
				minlength: "Description should be at least 2 characters long."
		  }
		},
		errorElement: 'span',
		errorPlacement: function(error, element) {
			var er = element.attr("name");
			if (er != "database_type[]" && er != "software_type[]")
			{
				error.insertAfter( element );
				$("<br/>").insertAfter( element );
			}
			else
			{
				error.insertAfter( element.parent().parent() );
				//$("<br/>").insertAfter( element ),			
			}
		}
	});
}

function setupOverrideDbServer()
{
	jQuery("#overrideServer").change(function(){
		var overrideServer = jQuery("#overrideServer").attr('checked');
		jQuery("#serverHost").readonly(!overrideServer);
		jQuery("#dbName").readonly(!overrideServer);
		jQuery("#dbUsername").readonly(!overrideServer);
		jQuery("#dbPassword").readonly(!overrideServer);
	});
	jQuery("#overrideServer").change();
}
