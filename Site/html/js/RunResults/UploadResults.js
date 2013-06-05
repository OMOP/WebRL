/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


try {
    var RunResults;

    if (!RunResults) {
        RunResults = {};
    }
    else if (typeof RunResults != 'object') {
        throw new Error("Couldn't create namespace with name RunResult. Name already using.");
    }
    
    if (!RunResults.UploadResults) {
        RunResults.UploadResults = {};
    }
    else if (typeof RunResults.UploadResults != 'object') {
        throw new Error("Couldn't create module with name UploadResults. Name already using.");
    }
    
    RunResults.UploadResults.onchageRadio = function ($radio) {
        if ($($radio).attr("checked"))
            RunResults.UploadResults.chageSelectSource($($radio).val());
    }
    
    RunResults.UploadResults.chageSelectSource = function (methodType) {
        with (RunResults.UploadResults) {
            switch (methodType) {
                case 'method':
                    $("#dataset-experiment").html('');
                    var lableForSelect = $("<label/>");
                    lableForSelect.attr("for","idExperiment");
                    lableForSelect.html("Select Experiment:");
                    $("#dataset-experiment").append(lableForSelect);
                    if ((typeof experiments == "object") && (experiments)) {
                        var select = $("<select/>");
                        select.attr({"id":"idExperiment","name":"experiment"});
                        var option;
                        for (var id in experiments) {
                            option = $("<option/>");
                            option.attr("value",id);
                            if ((choisenExperiment) && (choisenExperiment == id))
                                option.attr("selected","selected");
                            option.html(experiments[id]);
                            select.append(option);
                        }
                        $("#dataset-experiment").append(select);
                    }
                    else {
                        $("#dataset-experiment").append("<p><em>There are no available experiments.</em></p>");
                    }
                    break;
                case 'oscar':
                    $("#dataset-experiment").html('');
                    var lableForSelect = $("<label/>");
                    lableForSelect.attr("for","idDataset");
                    lableForSelect.html("Select dataset:");
                    $("#dataset-experiment").append(lableForSelect);
                    if ((typeof datasets == "object") && (datasets)) {
                        var select = $("<select/>");
                        select.attr({"id":"idDataset","name":"dataset"});
                        var option;
                        for (var id in datasets) {
                            option = $("<option/>");
                            option.attr("value",id);
                            if ((choisenDataset) && (choisenDataset == id))
                                option.attr("selected","selected");
                            option.html(datasets[id]);
                            select.append(option);
                        }
                        $("#dataset-experiment").append(select);
                    }
                    else {
                        $("#dataset-experiment").append("<p><em>There are no available datasets.</em></p>");
                    }
            }
        }
    }
}
catch (exc) {
    console.log(exc.message);
}

$(document).ready(function() {
    $('.form2 input:radio[name=method]').change(function () {
        RunResults.UploadResults.onchageRadio(this);
    });
    $('.form2 input:radio[name=method]:checked').change();
});