/*================================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    Dec, 23 2010

    Support for S3 results page.

    (c)2009-2010 Foundation for the National Institutes of Health (FNIH)

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
$(document).ready(function(){
    $("#runs-list").dialog({autoOpen: false});
    $("#run-details").dialog({
        autoOpen: false,
        width: 560
    });
    $(".run-data").bind('click', show_s3_run_list);
});

function show_s3_run_list()
{
    // Show nothing for empty cells
    if (jQuery.trim($(this).text()) == '') {
        return;
    }
    var a = $(this).attr('title').split('/');
    var html = '<ul>';
    var link_class;
    var dataset = a[0];
    var method = a[1];
    for(var i in runs[method][dataset]) {
        run = runs[method][dataset][i];
        if (run.hasNotUploadedFiles)
            link_class = ' class="not-uploaded"';
        else
            link_class = '';
        html += ('<li><a'+link_class+' href="javascript:run_details(\''+dataset+'\', \''+method+'\', runs[\''+method+'\'][\''+dataset+'\']['+i+']);" >' + run.name + '</a></li>');
    }
    html += '</ul>';
    $("#runs-list").html(html);
    $("#runs-list").dialog('option', 'title', dataset + ' ' + method + ' Run List');
    $("#run-details").dialog('close');
    $("#runs-list").dialog('open');
}

function run_details(dataset, method, run) {
    $("#runs-list").dialog('close');
    var html;
    html = '';
    html += '<tr><td>Method:</td><td>'+method+'</td></tr>'
    html += '<tr><td>Dataset:</td><td>'+dataset+'</td></tr>'
    html += '<tr><td>Run:</td><td>'+run.name+'</td></tr>'
    html = '<table style="width: 320px;"><tbody>' + html + '</tbody></table>';
    
    html += '<table>';
    html += '<thead><tr><th>Files</th><th>Date</th><th>Size</th></tr></thead>';
    var row_class;
    var emptyDate = '&lt;No Date&gt;';
    for(i in run.files) {
        if (!run.files[i].date || emptyDate == run.files[i].date)
            row_class = ' class="not-uploaded"';
        else
            row_class = '';
        html += '<tr'+row_class+'><td>'+run.files[i].file+'</td><td>'+(run.files[i].date?run.files[i].date:'-')+'</td><td class="number">'+(run.files[i].size?run.files[i].size:'-')+'</td></tr>';
    }
    var button_title = dataset+'/'+method;
    html += '<tr><td rowspan="3" style="margin-left: 100px;">'
         + '<button onmouseover="this.style.backgroundPosition=\'bottom\';" onmouseout="this.style.backgroundPosition=\'top\';" class="button_90" type="button" id="back-button" title="'+button_title+'">Back</button></td></tr>'
         + '</table>';

    $("#run-details").html(html);
    $("#run-details").dialog('open');
    /**
     * @todo It seems not to work
     */
    // onclick="javascript: show_s3_run_list();"
    $("#back-button").bind('click', show_s3_run_list);
}