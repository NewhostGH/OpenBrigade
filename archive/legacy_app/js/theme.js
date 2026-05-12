$(function(){
    $('.theme').change(function() {
        var bgcolor=$('.theme option:selected').css('background-color');
        var txtcolor=$('.theme option:selected').css('color');
        $('.theme').css('background-color', bgcolor);
        $('.theme').css('color', txtcolor);
    });       
});