/*==============================================================================
    OMOP - Cloud Research Lab

    Observational Medical Outcomes Partnership
    09-03-2011

    JS scripts for user history page

    (c)2009-2011 Foundation for the National Institutes of Health (FNIH)

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

==============================================================================*/
$(document).ready(function() {
    $("#idOrganization").change(function() {
        $.ajax({
            async: false,
            cache: false,
            type: "POST",
            url: '/public/user/get-users',
            data:
                {
                    'organization':$('#idOrganization').val()
                },
            success: function(response) {
                $("#idUser option").remove();
                    $("#idUser").append($("<option></option>").text("--All--").attr("value", 0))
                $(response.users).each(function(index, value) {
                    id = value.id;
                    name = value.name;
                    $("#idUser").append($("<option></option>").attr("value", id).text(name))
                })
                
            },
            dataType: 'json'
        });
       
    })
});
