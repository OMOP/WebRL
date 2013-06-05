function generatePop (instanceId,leftPos,topPos) {
        var divEditInstanceName = $('<div/>');
        divEditInstanceName.attr({id:'editInstanceName',title: 'Rename Instance'});
        divEditInstanceName.addClass("C text ui-widget-content ui-corner-all");
        var pValidateTips  = $('<p/>');
        pValidateTips.attr({'id':'validateTips'});
        pValidateTips.html('');
        divEditInstanceName.append(pValidateTips);
        var formEditInstanceName = $('<form/>');
        var hiddenId = $('<input/>');
        hiddenId.attr({id:'instanceId',type:'hidden','name':'instanceId','value':instanceId});
        formEditInstanceName.append(hiddenId);
        var labelForName = $('<label/>');
        labelForName.attr({'for':'instanceName','style':'padding-right:10px'});
        labelForName.html('New instance name:');
        formEditInstanceName.append(labelForName);
        var inputInstanceName = $('<input/>');
        inputInstanceName.attr({'type':'text','name':'instanceName','id':'instanceName', 'maxlength':256});
        /*inputInstanceName.focus(function(){
            if (this.value==$("#"+instanceId+" name").html())
                this.value='';
        });
        inputInstanceName.blur(function(){
            if (this.value=='')
                this.value=$("#"+instanceId+" name").html();
        });*/
        formEditInstanceName.append(inputInstanceName);
        var loaderImage = $('<img/>');
        loaderImage.attr({'src':'/images/ajax-loader.gif'/*,'width':'16','height':'16'*/});
        formEditInstanceName.append(loaderImage);
        formEditInstanceName.append("<br />");
        /*var spanOldName = $("<span/>");
        //spanOldName.addClass("hint");
        spanOldName.html('Old name: '+$("#"+instanceId+" name").html());
        formEditInstanceName.append(spanOldName);*/
        divEditInstanceName.append(formEditInstanceName);
        $('div .list').append(divEditInstanceName);
        loaderImage.hide();
        $("#editInstanceName").dialog({
                            autoOpen: true,
                            /*height: 300,*/
                            width: 400,
                            modal: true,
                            /*draggable: false,*/
                            resizable: false,
                            /*position: [leftPos,topPos],*/
                            buttons: {"Change instance name": function() {
                                            loaderImage.show();
                                            var query = new Object();
                                            query.id = $("#instanceId").val();
                                            query.name = $("#instanceName").val();
                                            query.oldname = $("#"+query.id+" > span > name").html();
                                            $.ajax({
                                                type: "POST",
                                                url:  '/public/instance/changename',
                                                data: query,
                                                dataType: "json",
                                                timeout: 30000,
                                                success: function(message){
                                                            try {
                                                                if ((message instanceof Object) && (message.update != undefined) ) {
                                                                    if (message.update) {
                                                                        $("#validateTips").html('Update successfully!');
                                                                        var displayName;
                                                                        $("#"+query.id).removeClass();
                                                                        if (query.name.length > 30) {
                                                                                displayName = query.name.substring(0,29)+"...";
                                                                                $("#"+query.id).addClass("tooltip_handle");
                                                                        }
                                                                        $("#"+query.id).addClass("instance-name");
                                                                        $("#"+query.id+" > name").html(displayName);
                                                                        $("#"+query.id+" > span > name").html(query.name);
                                                                        var instanceRow = $("#"+query.id).parent().parent().children()[1];
                                                                        var sshHref, sshTitle, sshLink;
                                                                        instanceRow = $(instanceRow).children();
                                                                        for (var i =0,to = instanceRow.length;i < to;i++) {
                                                                                sshLink = instanceRow[i];
                                                                                sshHref = $(sshLink).attr('href');
                                                                                sshHref = sshHref.replace(query.oldname,query.name);
                                                                                sshTitle = $(sshLink).attr('title');
                                                                                sshTitle = sshTitle.replace(query.oldname,query.name);
                                                                                $(sshLink).attr({href:sshHref,title:sshTitle});
                                                                        }
                                                                        loaderImage.hide();
                                                                        $('#editInstanceName').dialog('close');
                                                                    }
                                                                    else {
                                                                        throw new Error (message.error);
                                                                    }
                                                                }
                                                                else
                                                                    throw new Error ('Response format is unknown!');
                                                            }
                                                            catch (exc) {
                                                                $("#validateTips").html(exc.message);
                                                                loaderImage.hide();
                                                            }
                                                         },
                                                error: function(){$("#validateTips").html('No response from server! Try leter.');}
                                            });     
                                      }/*,
                                      'Close': function() {
                                            $(this).dialog("close");
                                      }*/

                                },
                            close: function() {
                                $('#editInstanceName').hide();
                                $('#editInstanceName').remove();
                            }                       
        })/*.parent().find("div").filter(".ui-dialog-titlebar.ui-widget-header.ui-corner-all.ui-helper-clearfix").hide();
        divEditInstanceName.show()*/;
        inputInstanceName.focus(false);
        inputInstanceName.attr({'value':$("#"+instanceId+" span name").html()});
}

$(document).ready(function(){
    $('.instance-name').click(function(){
        var element = $(this);
        var instanceId = element.attr('id');
        var leftPos = element.offset().left;
        var topPos = element.offset().top + element.height();
        generatePop(instanceId,leftPos,topPos);
        return false;
    });
});


