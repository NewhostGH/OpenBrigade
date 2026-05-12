$(document).ready(function($){
    $("input").click(function(){        
            var trouve;
            var choixSection;
            choixSection = $("select#choixSection option:selected").val();
            trouve='';
            for (i=0; i<$("input").length; i++) {
                if($("input")[i].checked) {
                    trouve = $("input")[i].value;
                }
            }            
            $("input + label").css("color", "red");
            $("input:not(:checked) + label").css("color", "#2B2350");
                                     
            if(trouve!=''){
            $.post("search_personnel_result.php",{trouve:trouve,section:choixSection,typetri:'habilitation'},
            function (data){        
                $("#export").empty();
                $("#export").html(" ").append(data);
            });
            }else{
                $("#export").empty();
            }
            
    });

    $("select#choixSection").change(function(){
        $("select#choixSection option:selected").each(function () {
           choixSection = $(this).val();
        } );

        var trouve;
        var choixSection;
        trouve='';
            
        for (i=0; i<$("input").length; i++) {
                if($("input")[i].checked) {
                    trouve = $("input")[i].value;
            }
        }            
        $("input + label").css("color", "red");
        $("input:not(:checked) + label").css("color", "#2B2350");
                                
        if(trouve!=''){
        $.post("search_personnel_result.php",{trouve:trouve,section:choixSection,typetri:'habilitation'},
        function (data){        
            $("#export").empty();
            $("#export").html(" ").append(data);
        });
        }else{
            $("#export").empty();
        }
    });
});

function changeFilterHab(val, section){
    if(val != 'none'){
        $.post("search_personnel_result.php",{trouve:val,section:section,typetri:'habilitation'},
            function (data){        
                $("#export").empty();
                $("#export").html(" ").append(data);
            });
    }
    else
        $("#export").empty();
}