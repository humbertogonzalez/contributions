define([
    'jquery',
    'uiComponent',
    'ko',
    'Magento_Ui/js/form/element/abstract'
], function ($, Component, ko, Abstract) {
    'use strict';

    var codeStates = {"01": 764, "02": 765, "03": 766, "04": 767, "07": 768, "08": 769, "05": 770, "06": 771, "09": 772,
        "10": 773, "11": 774, "12": 775, "13": 776, "14": 777, "15": 778, "16": 779, "17": 780, "18": 781, "19": 782,
        "20": 783, "21": 784, "22": 785, "23": 786, "24": 787, "25": 788, "26": 789, "27": 790, "28": 791, "29": 792,
        "30": 793, "31": 794, "32": 795
    }

    var getCookie = function(name) {
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i < ca.length;i++) {
            var c = ca[i];
            while (c.charAt(0)==' ') c = c.substring(1,c.length);
            if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length,c.length);
        }
        return null;
    };

    var existInOption = function(cookie) {
        var exists = false;
        $('select[name="street[5]"] option').each(function(){
            if (this.value == cookie) {
                exists = true;
            }
        });
        return exists;
    };

    return Abstract.extend({
        postcode : function () {
            var self = this;
            
            $("#postcode").on("keyup", function(){

                var postcodeValue = $("#postcode").val();
                // Remove errors and clear colonia list
                $("#postcode-error").remove();
                $("#postcode").closest(".control").find(".mage-error").remove();
                $("select[name='street[5]']").html('');

                if(postcodeValue.length === 5){
                    var url = document.location.origin + '/sepomex/ajax/get';
                    var data = {cp: postcodeValue};
                    $.ajax({
                        method: "GET",
                        url: url,
                        data: data,
                        showLoader: true
                    }).done(function(data){

                        $("#postcode-error").remove();
                        var zipCodeInput = $("#postcode");
                        var form = zipCodeInput.closest("form");

                        if(data.status === "found"){

                            $("#postcode .control .warning").remove();
                            var coloniaSelect = form.find("select[name='street[5]']");
                            if(data["neighborhood"][0].d_ciudad){
                                form.find("input[name='city']").val(data["neighborhood"][0].d_ciudad).keyup();
                            }else {
                                form.find("input[name='city']").val(data["neighborhood"][0].d_mnpio).keyup();
                            }
                            form.find("select[name='region_id']").val(codeStates[data["neighborhood"][0].c_estado]).change();
                            form.find("input[name='region']").val(data["neighborhood"][0].d_estado).keyup();

                            coloniaSelect.html("");
                            data["neighborhood"].forEach(function(element){
                                form.find("select[name='street[5]']").append("<option value='"+ element.d_asenta +"'>"+ element.d_asenta +"</option>");
                            });

                            if(getCookie('cookie-red-street[5]') && existInOption(getCookie('cookie-red-cstreet[5]]'))){
                                coloniaSelect.val(getCookie('cookie-red-street[5]')).change();
                            }else{
                                coloniaSelect.val(form.find("select[name='street[5]'] option:first").val()).change();
                            }
                        } else if(data.status === "not found") {
                            if($("#postcode-error").length == 0) {
                                zipCodeInput.closest(".control").append('<div for="' + zipCodeInput.attr('id') + '" generated="true" class="mage-error" id="' + zipCodeInput.attr('id') + '-error">Ingresa un código postal válido.</div>')
                            }
                        } else {
                            console.log(data);
                        }
                    });
                }
            });

            $(".postcode").on("keyup", function(event){
                var postcodes = $(".postcode");
                for (var i=0; i<postcodes.length; i++){
                    if(postcodes[i].value.length === 5){
                        var url = document.location.origin + '/sepomex/ajax/get';
                        var data = {cp: postcodes[i].value};
                        $.ajax({
                            method: "GET",
                            url: url,
                            data: data,
                            showLoader: true
                        }).done(function(data){
                            try {
                                var zipCodeInput = postcodes[i-1];
                                var form = zipCodeInput.closest("form");

                                if(data.status === "found"){

                                    $("#postcode .control .warning").remove();
                                    var coloniaSelect = $("select[name='street[5]']")[i-1];
                                    if(data["neighborhood"][0].d_ciudad){
                                        var city = $("input[name='city']")[i-1];
                                        city.value = data["neighborhood"][0].d_ciudad;

                                    }else {
                                        var city = $("input[name='city']")[i-1];
                                        city.value = data["neighborhood"][0].d_mnpio;
                                    }
                                    var regionId = $("select[name='region_id']")[i-1];
                                    var region = $("input[name='region']")[i-1];

                                    regionId.value = codeStates[data["neighborhood"][0].c_estado];
                                    region.value = data["neighborhood"][0].d_estado;

                                    var option = "";
                                    data["neighborhood"].forEach(function(element){
                                        option += "<option value='"+ element.d_asenta +"'>"+ element.d_asenta +"</option>";
                                    });
                                    coloniaSelect.innerHTML = option;
                                } else if(data.status === "not found") {
                                    if($("#postcode-error").length == 0) {
                                        zipCodeInput.closest(".control").append('<div for="' + zipCodeInput.attr('id') + '" generated="true" class="mage-error" id="' + zipCodeInput.attr('id') + '-error">Ingresa un código postal válido.</div>')
                                    }
                                } else {
                                    console.log(data);
                                }
                            } catch (e) {
                                console.error(e);
                                // expected output: ReferenceError: nonExistentFunction is not defined
                                // Note - error messages will vary depending on browser
                            }
                        });

                    }
                }
            });
        }
    });
});
