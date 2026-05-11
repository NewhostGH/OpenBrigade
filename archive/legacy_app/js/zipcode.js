$(document).ready(function($){
    $("input#zipcode").keyup(function(){
        checkZipcode();
    });
});

function select_city(city_name, city_code) {
    var d1 = document.getElementById('city');
    d1.value=city_name;
    var d2 = document.getElementById('zipcode');
    d2.value=city_code;
    HideContent('divzipcode');
}

function checkZipcode(){
    var ZipCode;
    ZipCode = $("input#zipcode").val();
    var re = /^([0-9]*)$/;
    if (! re.test(ZipCode)) {
        swalAlert ("Saisissez un code postal à 5 chiffres: '"+ ZipCode + "' ne convient pas.");
        return false;
    }
    if ( ZipCode.length > 5 ) {
        swalAlert ("Maximum 5 chiffres: '"+ ZipCode + "' ne convient pas.");
        return false;
    }        
    $.post("zipcode.php",{ZipCode:ZipCode},    
    function (data) {
            var dd = document.getElementById('divzipcode');
            dd.style.display = "block";
            $("#divzipcode").empty();
            $("#divzipcode").append(data);
    });
}