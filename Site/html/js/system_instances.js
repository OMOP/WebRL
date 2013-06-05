$(document).ready(function () {
    $('#key_dialog').dialog({
        title: "Choose certificate file",
        resizable: false,
        buttons : {
                "Cancel": function() {
                    $('#key_dialog').dialog('close');
                },
                "Ok": function() {
                    $.ajax({
                        async: false,
                        cache: false,
                        type: "POST",
                        url: '/api.php?method=save_key_path',
                        data:
                            {
                            path:$('#key_file').val()
                            },
                        success: function(response){
                            createCookie('certificate_path', $('#key_file').val(), 30);
                            }
//                        dataType: 'json'
                    });
                    window.location=$('#key_dialog').attr('url');
                    $('#key_dialog').dialog('close');
                }
        },
        autoOpen: false,
        modal: true,
        open: function() {
            if (readCookie('certificate_path')) {
                $('#key_file').val(readCookie('certificate_path'));
            }
        }
    });

})

function show_key_dialog(url) {
    $('#key_dialog').attr('url', url);
    $('#key_dialog').dialog('open');
}

function createCookie(name,value,days) {
	if (days) {
		var date = new Date();
		date.setTime(date.getTime()+(days*24*60*60*1000));
		var expires = "; expires="+date.toGMTString();
	}
	else var expires = "";
	document.cookie = name+"="+value+expires+"; path=/";
}

function readCookie(name) {
	var nameEQ = name + "=";
	var ca = document.cookie.split(';');
	for(var i=0;i < ca.length;i++) {
		var c = ca[i];
		while (c.charAt(0)==' ') c = c.substring(1,c.length);
		if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
	}
	return null;
}
