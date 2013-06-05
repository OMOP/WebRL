/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    5 Jan 2010

    JS file for method/analisys page

    (c)2009-2010 Foundation for the National Institutes of Health (FNIH)

    Licensed under the Apache License, Version 2.0 (the "License"); you may not
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
$(document).ready(function() {
$('#uploadDialog').dialog({
    title: "Choose file to upload",
    resizable: false,
    buttons : {
            "Cancel": function() {
                $('#uploadDialog').dialog('close');
            },
            "Ok": function() {
                $('#analysisFile').hide();
                $('#uploadHint').show();
                $('#uploadDialog span').remove();

                $.ajaxFileUpload({
                    url: '/public/method/upload',
                    dataType: 'json',
                    fileElementId: 'analysisFile',
                    success: function(data, status) {
                        if (typeof data.error == 'undefined')
                            if (typeof data.newUrl != 'undefined')
                                location = data.newUrl;
                            else
                                location.reload(true);
                        else {
                            $('#uploadHint').hide();
                            $('#analysisFile').show();
                            if (typeof data.error == 'object')
                                for (var k in data.error) {
                                    $('#uploadDialog').append('<span class="error">'+data.error[k]+"</span>");
                                }
                            else
                                $('#uploadDialog').append('<span class="error">'+data.error+"</span>");
                        }
                    },
                    error: function (data, status, e)
                    {
                        $('#uploadHint').hide();
                        $('#analysisFile').show();
                        $('#uploadDialog').append('<span class="error">Invalid server response. Please contact Administrator.</span>');
                    }

                });
            }
    },
    open: function() {
        $('#uploadDialog span').remove();
        $('#analysisFile').show();
        $('#uploadHint').hide();
    },
    autoOpen: false,
    modal: true
})

$('#copyDialog').dialog({
    title: "Copy analysis data",
    resizable: false,
    buttons : {
            "Cancel": function() {
                $('#copyDialog').dialog('close');
            },
            "Ok": function() {
                if ($('#copyDialog').attr('copied') == 'copied') {
                    window.location.reload();
                    return;
                }
                
                $('#copyDialog span').remove();
                
                $.ajax({
                    url: '/public/method/copy',
                    async: true,
                    cache: false,
                    type: "POST",
                    data: {
                        to: $('#hiddenId').val(),
                        from: $('#copyFrom').val(),
                        overwrite: $('#overwriteFlag').attr('checked')
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (typeof data,status != 'undefined')
                            if (data.status == 'success') {
                                if (typeof data.message == 'undefined')
                                    data.message = "Copy complete";
                                $('#copyDialog').append('<span class="message">'+data.message+"</span>");
                                $('#copyDialog').attr('copied', 'copied');
                            }
                            else if (data.status == 'error'){
                                $('#copyDialog').append('<span class="error">'+data.error+"</span>");
                            }
                        else
                            $('#copyDialog').append('<span class="error">Invalid server response. Please contact Administrator.</span>');
                    },
                    error: function (data)
                    {
                        $('#copyDialog').append('<span class="error">Invalid server response. Please contact Administrator.</span>');
                    }

                });
            }
    },
    open: function() {
        $('#copyDialog span').remove();
    },
    autoOpen: false,
    modal: true
})
$('#copyFrom').change(function() {
    $('#copyDialog').attr('copied', '');
    $('#copyDialog span').remove();
})
$('#overwriteFlag').change(function() {
    $('#copyDialog').attr('copied', '');
    $('#copyDialog span').remove();
})
})

