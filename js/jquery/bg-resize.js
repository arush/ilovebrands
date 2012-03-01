// JavaScript Document
$j = jQuery.noConflict();

$j(document).ready(function() {
    $j("#body-background").ezBgResize();
});

$j(window).bind("resize", function(){
    $j("#body-background").ezBgResize();
});