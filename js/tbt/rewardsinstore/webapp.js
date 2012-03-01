/**
 * WDCA - Sweet Tooth Instore
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the SWEET TOOTH (TM) INSTORE
 * License, which extends the Open Software License (OSL 3.0).
 * The Sweet Tooth Instore License is available at this URL: 
 * http://www.sweettoothrewards.com/instore/sweet_tooth_instore_license.txt
 * The Open Software License is available at this URL: 
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * DISCLAIMER
 * 
 * By adding to, editing, or in any way modifying this code, WDCA is 
 * not held liable for any inconsistencies or abnormalities in the 
 * behaviour of this code. 
 * By adding to, editing, or in any way modifying this code, the Licensee
 * terminates any agreement of support offered by WDCA, outlined in the 
 * provided Sweet Tooth Instore License. 
 * Upon discovery of modified code in the process of support, the Licensee 
 * is still held accountable for any and all billable time WDCA spent 
 * during the support process.
 * WDCA does not guarantee compatibility with any other framework extension. 
 * WDCA is not responsbile for any inconsistencies or abnormalities in the
 * behaviour of this code if caused by other framework extension.
 * If you did not receive a copy of the license, please send an email to 
 * support@wdca.ca or call 1-888-699-WDCA(9322), so we can send you a copy 
 * immediately.
 * 
 * @category   [TBT]
 * @package    [TBT_Rewardsinstore]
 * @copyright  Copyright (c) 2011 Sweet Tooth (http://www.sweettoothrewards.com)
 * @license    http://www.sweettoothrewards.com/instore/sweet_tooth_instore_license.txt
 */

var pageState = 'login';
var isAutocompleteOpen = false;
var storefrontId = 0;
var lastAjax = [];

$(function() {
    if (isLoggedIn) {
        loadMainPage(launchUri, onFinishedLoadingMain);
    } else {
        loadLoginPage(launchUri, function() {
            lastAjax['loadLogin'] = true;
            
            $('#loader').css('opacity', 0);
            
            $('#loginDiv').css('position', 'relative')
                .css('float', 'left');
            $('#mainDiv').css('position', 'relative')
                .css('float', 'left');
            
            $('#username').labelIntoInput().focus().blur().attr('disabled', "disabled");
            $('#passwordLabel').labelIntoInput().focus().blur().attr('disabled', "disabled");
            $('#password').attr('disabled', "disabled");
            $('.continue').attr('disabled', "disabled");
            $('#locationBox').focus();
            
            var matches = filterArray(storefronts, function(storefront) {
                return storefront.code == storefrontCode;
            });
            if (matches.length > 0) {
                $('#locationBox').val(matches[0].name);
                storefrontId = matches[0].id;
                enableLoginForm();
            }
            
            $('#locationBox').autocomplete({
                html: true,
                autoFocus: true,
                source: function(request, responseCallback) {
                    var matches = filterArray(storefronts, function(storefront) {
                        return (storefront.code.search(new RegExp(request.term, "i")) != -1) || (storefront.name.search(new RegExp(request.term, "i")) != -1);
                    });
                    
                    var results = $.map(matches, function(item) {
                        return {
                            label: item.name +" <span style='font-size:small; margin-left:10px;'>"+ item.address +"</span>",
                            value: item.name,
                            id: item.id,
                            code: item.code
                        };
                    });
                    
                    $('.ui-autocomplete').css('marginLeft', 0);
                    
                    responseCallback(results);
                },
                open: function(event, ui) {
                    isAutocompleteOpen = true;
                    $('.ui-autocomplete').css('marginLeft', 20)
                        .css('width', "-=20px");
                },
                close: function(event, ui) {
                    isAutocompleteOpen = false;
                    $('.ui-autocomplete').css('marginLeft', 0)
                        .css('width', "+=20px");
                },
                select: function(event, ui) {
                    storefrontId = ui.item.id;
                    enableLoginForm();
                }
            });
            
            $('#autocompleteArrow').click(function() {
                if (isAutocompleteOpen) {
                    $('#locationBox').focus().autocomplete('close');
                } else {
                    $('#locationBox').focus()
                        .autocomplete('option', 'minLength', 0)
                        .autocomplete('search', "")
                        .autocomplete('option', 'minLength', 1);
                }
            });
            
            $('.loginInput').keydown(function(event) {
                if ((window.event ? event.keyCode : event.which) == 13) {
                    login();
                }
            });
            $('.continue').click(function() {
                login();
            });
            
            $('#passwordLabel').focus(function() {
                $('#passwordLabel').blur().hide();
                $('#password').show().focus();
            });
            $('#password').blur(function() {
                if ($('#password').val() == "") {
                    $('#password').hide();
                    $('#passwordLabel').show();
                }
            });
        });
    }
});

function enableLoginForm()
{
    $('#username').removeAttr('disabled');
    $('#password').removeAttr('disabled');
    $('#passwordLabel').removeAttr('disabled');
    $('.continue').removeAttr('disabled');
    
    $('#loginForm').animate({opacity: 1}, 300);
    
    $('#username').focus();
}

function login()
{
    hideError();
    
    if (storefrontId == 0) {
        showError("You must select a <b>location</b>.");
        $('#locationBox').focus();
        return;
    }
    
    if ($('#username').val() == "" || $('#username').val() == "Username") {
        showError("You must supply a <b>username</b>.");
        $('#username').focus();
        return;
    }
    
    if ($('#password').val() == "") {
        showError("You must supply a <b>password</b>.");
        $('#passwordLabel').focus();
        return;
    }
    
    loadMainPage(onFinishedLoadingMain);
}

function loadLoginPage(uri, callback)
{
    if (lastAjax['loadLogin'] == undefined) {
        lastAjax['loadLogin'] = false;
        
        $('#loader').css('top', ($('#container').innerHeight() - $('#loader').outerHeight()) / 2 + $('#container').offset().top)
            .css('left', ($('#container').innerWidth() - $('#loader').outerWidth()) / 2 + $('#container').offset().left)
            .css('opacity', 0.6);
        
        $('#loginDiv').load(uri, callback);
    }
}

function loadMainPage(uri, callback)
{
    if (!callback) {
        callback = uri;
        uri = undefined;
    }
    
    if (lastAjax['login'] == undefined) {
        $('input').attr('disabled', "disabled");
        $('#autocompleteArrow').attr('disabled', "disabled");
        $('#container').animate({
            opacity: 0.3
        });
        
        if (uri == undefined) {
            
            $('#loader').css('top', ($('#main').innerHeight() - $('#loader').outerHeight()) / 2 + $('#main').offset().top - $('.break').innerHeight())
                .css('left', ($('#container').innerWidth() - $('#loader').outerWidth()) / 2 + $('#container').offset().left)
                .css('opacity', 0.6);
                // TODO: .animate({opacity: 0.6});
            
            lastAjax['login'] = $('#mainDiv').load(generateLoginUri(storefrontId), {
                'login[username]':  $('#username').val(),
                'login[password]':  $('#password').val(),
                'form_key':         formKey
            }, callback);
        } else {
            $('#loader').css('top', ($('#container').innerHeight() - $('#loader').outerHeight()) / 2 + $('#container').offset().top)
                .css('left', ($('#container').innerWidth() - $('#loader').outerWidth()) / 2 + $('#container').offset().left)
                .css('opacity', 0.6);
                // TODO: .animate({opacity: 0.6});
            
            lastAjax['login'] = $('#mainDiv').load(uri, callback);
        }
    }
}

function onFinishedLoadingMain()
{
    var pageIdentifier = $('#mainDiv .pageIdentifier');
    if (pageIdentifier.val() == "main") {
        $('#loginDiv').css('position', 'relative')
            .css('float', 'left');
        $('#mainDiv').css('position', 'relative')
            .css('float', 'left');
        
        $('#loader').css('opacity', 0);
        $('#container').animate({
            opacity: 1.0
        });
        $('#mainDiv').css('left', -$('#loginDiv').outerWidth()).fadeIn(function() {
            $('#loginDiv').hide().html("");
            $('#mainDiv').css('left', 0);
        });
        main_onLoad();
    } else if (pageIdentifier.val() == "login") {
        showError("Invalid Username or Password.");
        
        lastAjax['login'] = undefined;
        $('#loader').css('opacity', 0);
        $('input').removeAttr('disabled');
        $('#autocompleteArrow').removeAttr('disabled');
        $('#container').animate({
            opacity: 1.0
        });
        $('#username').focus();
    }
}

function generateLoginUri(storefrontId)
{
    var query = "storefront_id=" + storefrontId;
    
    var separator = "?";
    if (loginUri.indexOf("?") != -1) {
        separator = "&";
    }
    
    return loginUri + separator + query;
}

function showError(msg)
{
    msg = (msg == undefined) ? "There has been an unknown error." : msg;
    var prefix = (pageState == 'login') ? '#loginDiv' : '#mainDiv';
    
    $(prefix + ' div.error #msg').html(msg);
    
    $(prefix + ' div.error').animate({
        top: 0
    }, 500);
    $(prefix + ' #main').animate({
        top: 0
    }, 500);
    $('.mainFoot').animate({
        top: 0
    }, 500);
}

function hideError()
{
    var prefix = (pageState == 'login') ? '#loginDiv' : '#mainDiv';
    
    $(prefix + ' div.error').animate({
        top: -$(prefix + ' div.error').innerHeight()
    }, 300);
    $(prefix + ' #main').animate({
        top: -$(prefix + ' div.error').innerHeight()
    }, 300);
    $('.mainFoot').animate({
        top: -$(prefix + ' div.error').innerHeight()
    }, 300);
}

function filterArray(haystack, callback)
{
    var result = [];
    
    if (!callback || typeof(callback) != 'function') {
        return [];
    }
    if (!haystack || !haystack.length || haystack.length < 1) {
        return [];
    }
    
    for (var i = 0; i < haystack.length; i++) {
        if (callback(haystack[i])) {
            result.push(haystack[i]);
        }
    }
    
    return result;
}

function ucfirst(string)
{
    return string.charAt(0).toUpperCase() + string.substring(1);
}
