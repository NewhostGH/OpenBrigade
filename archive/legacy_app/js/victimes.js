
// manage changes on
$(document).ready(function($){
    intoxicationHandler();
    DAEHandler();
    medicamentHandler();
    garrotHandler();
    traumaHandler();

    // calcul glasgow
    $("#bilan230, #bilan240, #bilan250").change(function() {
        calculGlasgow();
    });

    // intox
    $("#bilan20").change(function() {
        intoxicationHandler();
    });
    
    // DAE
    $("#bilan750").change(function() {
        DAEHandler();
    });
    
    // medicaments
    $("#bilan780").change(function() {
        medicamentHandler();
    });
    
    // medicaments
    $("#bilan700").change(function() {
        garrotHandler();
    });
    
    // gestes
    $(".gestes").change(function() {
        if ( $(this).attr('checked') ) {
            $("#soins").attr('checked',true);
        }
    });
    
    // traumatisme principal
    $("#bilan500, #bilan531, #bilan535").change(function() {
          traumaHandler();
    });
    
});


// afficher ou pas traumatsime 2 et 3
function traumaHandler() {
    if ( document.getElementById('bilan500')) {
        var t1 = document.getElementById('bilan500').value;
        var t2 = document.getElementById('bilan531').value;
        var t3 = document.getElementById('bilan535').value;
        if ( t1 == '' ) {
            $("#t2a").hide("fast");
            $("#t2b").hide("fast");
            $("#t3a").hide("fast");
            $("#t3b").hide("fast");
            $("#bilan510").val( '' );
            $("#bilan520").val( '' );
            $("#bilan531").val( '' );
            $("#bilan532").val( '' );
            $("#bilan533").val( '' );
            $("#bilan534").val( '' );
            $("#bilan535").val( '' );
            $("#bilan536").val( '' );
            $("#bilan537").val( '' );
            $("#bilan538").val( '' );
            
        }
        else {
            $("#t2a").show("slow");
            $("#t2b").show("slow");
            if ( t2 == '' ) {
                $("#t3a").hide("fast");
                $("#t3b").hide("fast");
                $("#bilan532").val( '' );
                $("#bilan533").val( '' );
                $("#bilan534").val( '' );
                $("#bilan535").val( '' );
                $("#bilan536").val( '' );
                $("#bilan537").val( '' );
                $("#bilan538").val( '' );
            }
            else {
                $("#t3a").show("slow");
                $("#t3b").show("slow");
                if ( t3 == '' ) {
                    $("#bilan536").val( '' );
                    $("#bilan537").val( '' );
                    $("#bilan538").val( '' );
                }
            }
        }
    }
}

// calcul glasgow
function calculGlasgow() {
    var g1 = parseInt(document.getElementById('bilan230').value);
    var g2 = parseInt(document.getElementById('bilan240').value);
    var g3 = parseInt(document.getElementById('bilan250').value);
    if(isNaN(g1)) g1=parseInt(0);
    if(isNaN(g2)) g2=parseInt(0);
    if(isNaN(g3)) g3=parseInt(0);
    var score = g1 + g2 + g3;
    if(isNaN(score)) score='';
    $("#bilan260").val( score );
}

function intoxicationHandler() {
    if (document.getElementById('bilan20')) {
        if ( document.getElementById('bilan20').value == '4' ) {
            $("#bilan40").removeAttr('disabled');
            $("#bilan50").removeAttr('disabled');
        }
        else {
            $("#bilan40").attr('disabled','disabled');
            $("#bilan50").attr('disabled','disabled');
            $("#bilan40").val( '' );
            $("#bilan50").val( '' );
        }
    }
}

function DAEHandler() {
    if ( document.getElementById('bilan750')) {
        var dae = document.getElementById('bilan750');
        if ( dae.checked ) {
            $("#bilan760").removeAttr('disabled');
            $("#bilan770").removeAttr('disabled');
        }
        else {
            $("#bilan760").attr('disabled','disabled');
            $("#bilan770").attr('disabled','disabled');
            $("#bilan760").val( '' );
            $("#bilan770").val( '' );
        }
    }
}

function medicamentHandler() {
    if ( document.getElementById('bilan780')) {
        var medicament = document.getElementById('bilan780');
        if ( medicament.checked ) {
            $("#bilan790").removeAttr('disabled');
        }
        else {
            $("#bilan790").attr('disabled','disabled');
            $("#bilan790").val( '' );
        }
    }
}

function garrotHandler() {
    if ( document.getElementById('bilan700')) {
        var garrot = document.getElementById('bilan700');
        if ( garrot.checked ) {
            $("#bilan705").removeAttr('disabled');
        }
        else {
            $("#bilan705").attr('disabled','disabled');
            $("#bilan705").val( '' );
        }
    }
}

function redirect(evenement){
    url="evenement_display.php?evenement="+evenement+"&from=interventions";
    self.location.href=url;
}

function ready(numinter){
    url="intervention_edit.php?numinter="+numinter;
    self.location.href=url;
}

function readyliste(evenement, numcav){
    url="evenement_display.php?tab=23&evenement="+evenement+"&type_victime="+numcav;
    self.location.href=url;
}

function readyliste2(evenement){
    url="liste_victimes.php?evenement_victime="+evenement;
    self.location.href=url;
}

function fermerfenetre(){
    var obj_window = window.open('', '_self');
    obj_window.opener = window;
    obj_window.focus();
    opener=self;
    self.close();
}

function changedType() {
    var transport = document.getElementById('transport');
    var rowDestination = document.getElementById('rowDestination');
    var rowHeureHopital = document.getElementById('rowHeureHopital');
    if (transport.checked) {
        rowDestination.style.display = '';
        rowHeureHopital.style.display = '';
        $("#reparti").attr('checked',false);
    } else {
        rowDestination.style.display = 'none';
        rowHeureHopital.style.display = 'none';
    }
}

function ChangedReparti() {
    var transport = document.getElementById('transport');
    var rowDestination = document.getElementById('rowDestination');
    var rowHeureHopital = document.getElementById('rowHeureHopital');
    if ( $("#reparti").attr('checked') ) {
        $("#transport").attr('checked',false);
        rowDestination.style.display = 'none';
        rowHeureHopital.style.display = 'none';
    }
}

function putInCav() {
    var incav = document.getElementById('type_victime');
    var rowtime1 = document.getElementById('rowtime1');
    var rowtime2 = document.getElementById('rowtime2');
    if (incav.value > 0 ) {
        rowtime1.style.display = '';
        rowtime2.style.display = '';
    } else {
        rowtime1.style.display = 'none';
        rowtime2.style.display = 'none';
    }
}

function deleteIt(victime,numinter){
    if ( confirm ("Vous allez supprimer cette fiche victime.\nVoulez vous continuer ?" ))
        confirmed=1;
    else return;
    url="victimes.php?numinter="+numinter+"&victime="+victime+"&action=delete";
    self.location.href=url;
}

function deleteIt2(victime,numcav){
    if ( confirm ("Vous allez supprimer cette fiche victime.\nVoulez vous continuer ?" ))
        confirmed=1;
    else return;
    url="victimes.php?numcav="+numcav+"&victime="+victime+"&action=delete";
    self.location.href=url;
}

function changeAge(defaultValue) {
     var age=document.getElementById('age');
     var a=age.value;
     var birthdate=document.getElementById('date_naissance');
     var re = /^([0-9]+)$/;
     if (! re.test(a) && a.length > 0 ) {
        swalAlert ("Saisissez un nombre entier: '"+ a + "' ne convient pas.");
        age.value = defaultValue;
        return false;
     }
     birthdate.value='';
}

function deletefile(victime, fileid, file) {
   if ( confirm ('Voulez vous vraiment supprimer le fichier ' + file +  '?' )) {
        self.location.href = 'delete_event_file.php?number=' + victime + '&fileid=' + fileid + '&file=' + file + '&type=victime';
   }
}

function updatedoc(victime,filename,securityid,docid) {
    $('#modal_doc_' + docid).modal('hide');
    url='victimes.php?victime='+ victime + '&filename=' + filename +'&securityid=' + securityid + '&action=document';
    self.location.href=url;
}