define([
    "jquery"
], function($){
    'use strict';

    var codeStates = {
        "01": 764,
        "02": 765,
        "03": 766,
        "04": 767,
        "07": 768,
        "08": 769,
        "05": 770,
        "06": 771,
        "09": 772,
        "10": 773,
        "11": 774,
        "12": 775,
        "13": 776,
        "14": 777,
        "15": 778,
        "16": 779,
        "17": 780,
        "18": 781,
        "19": 782,
        "20": 783,
        "21": 784,
        "22": 785,
        "23": 786,
        "24": 787,
        "25": 788,
        "26": 789,
        "27": 790,
        "28": 791,
        "29": 792,
        "30": 793,
        "31": 794,
        "32": 795
    }

    return function(config, element){
        var zipCodeInput = $(element);
        var updateData = function() {
            if(zipCodeInput.val().length === 5){
                var self = this;
                var url = document.location.origin + '/sepomex/ajax/get';
                var data = {cp: zipCodeInput.val()};
                $.ajax({
                    method: "GET",
                    url: url,
                    data: data,
                    showLoader: true
                }).done(function(data){
                    $("#postcode-error").remove();
                    var form = zipCodeInput.closest("form");
                    if(data.status === "found"){
                        if(data["neighborhood"][0].d_ciudad){
                            form.find("input[name='city']").val(data["neighborhood"][0].d_ciudad).keyup();
                        } else {
                            form.find("input[name='city']").val(data["neighborhood"][0].d_mnpio).keyup();
                        }
                        form.find("select[name='region_id']").val(codeStates[data["neighborhood"][0].c_estado]);
                        form.find("input[name='region']").val(data["neighborhood"][0].d_estado);
                        form.find("select[name='street[6]']").html('');
                        form.find("#street_6").html('');
                        data["neighborhood"].forEach(function(element){
                            form.find("#street_6").append("<option value='"+ element.d_asenta +"'>"+ element.d_asenta +"</option>");
                        });
                    } else if(data.status === "not found") {
                        if($("#postcode-error").length == 0) {
                            zipCodeInput.closest(".control").append('<div for="'+ zipCodeInput.attr('id') +'" generated="true" class="mage-error" id="'+ zipCodeInput.attr('id') +'-error">Ingresa un código postal válido.</div>');
                        }
                    } else {
                        console.log(data);
                    }
                });
            }
        }

        updateData();

        zipCodeInput.on("blur", updateData);
    }
});
