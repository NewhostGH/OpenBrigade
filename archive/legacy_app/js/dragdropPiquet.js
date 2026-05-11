
$(function () {
    var occ=0;
//*********************************************************************************************************************************
function dragListed() {
        $('div[class^="listed"]')
            .draggable({
                revert: "invalid",
                revertDuration: 0,
                helper: "clone",
                appendTo: "body",
                start: function(e, ui) {//l'élément draggable a toujours le z-index le plus elevé
                    $(".ui-draggable").not(ui.helper.css("z-index", "1"))
                        .css("z-index", "0");
                },
            })
            .on("mouseover", function () {
                $(this).addClass("move-cursor")
            })
}

dragListed();//rendre les pompiers draggables en copies
//***********************************************************************************************************************************
function dropPoste() {
    $('div[class^="poste"]').droppable({
        activeClass: "ui-hover",
        hoverClass: "ui-active",
        drop: function (event, ui) {
            var i = event.target.id.split('_')[1];
            var j = ui.draggable.attr('id').split('_')[1];
            //un personnel peut être nouvellement affecté à un poste ou déplacé
            var classPompier = ui.draggable.attr('class').split(' ')[0];
            if ((i == j) || (j == 12)) {
                if (classPompier == 'listed') { //si l'étiquette est déplacée de la liste des personnels
                    $clone = ui.draggable.clone(false);
                    $clone.prop('id', ui.draggable.attr('id') + '_' + occ);//changement d'id nécessaire pour ne pas avoir de conflit de ids
                    $chaine = ui.draggable.attr('class');
                    $nouvellechaine = $chaine.replace("listed", "affected");//class modified
                    $clone.prop('class', $nouvellechaine);
                    occ = occ + 1
                    $clone.draggable({//rendre la clone draggable à son tour
                        revert: "invalid",
                        revertDuration: 0,
                        appendTo: "body",
                        drag: function (event, ui) {
                            document.getElementById('divPersonnel').style.display = 'none';
                            document.getElementById('titreDiv').style.display='none';
                            document.getElementById('glisser').style.display='block';
                            document.getElementById('minus').style.display = 'block';
                        },
                        stop: function (event, ui) {
                            document.getElementById('divPersonnel').style.display = 'block';
                            document.getElementById('titreDiv').style.display='block';
                            document.getElementById('minus').style.display = 'none';
                        },
                    });
                    placePompier($clone, $(this));
                } else{
                    copiePompier=ui.draggable
                    deletePlace(copiePompier);
                    placePompier(ui.draggable, $(this));
                }
            } else {
                if (classPompier == 'affected') { //si le personnel était déplacé donc il retourne à sa place si ça correspond pas à la periode
                    ui.draggable.draggable("option", "revert", true);
                    ui.draggable.draggable("option", "revertDuration", 0);
                }
                if (i == 1) {//affichage d'un message lorsque les periodes ne se correspondent pas
                    $msg = "Non disponible le jour";
                } else {
                    $msg = "Non disponible la nuit";
                }
                if ($(this).find('div').length == 0) {//si l'emplacement du drop qui ne correspond pas est vide
                    $text=$(this).html();//le role affiché par défaut
                    $(this).html($msg).animate({'background-color': 'red'}, 700, function () {
                        $(this).css({background: "#11ffee00"})
                        $(this).html($text).css({color:'darkgrey'});
                   });
                } else {
                    $div = $(this).find('div');
                    $text = $div.html();
                    $div.html($msg).animate({'background-color': 'red'}, 700, function () {
                        $div.css({background: "#d9d9d9"})
                        $div.html($text);

                    });
                }
            }
        },
    });
}

dropPoste();//rendre les emplacements dans les différents tableaux droppables

//*********************************************************************************************************************************
function dragAffectedPompier() {
    $('div[class^="affected"]')
        .draggable({
            revert: "invalid",
            revertDuration: 0,
            appendTo: "body",
            start: function(e, ui) {
                $(".ui-draggable").not(ui.helper.css("z-index", "1"))
                    .css("z-index", "0");
            },

            drag: function (event, ui) {
                document.getElementById('divPersonnel').style.display = 'none';
                document.getElementById('titreDiv').style.display='none';
                document.getElementById('minus').style.display = 'block';
                document.getElementById('glisser').style.display='block';
            },
            stop: function (event, ui) {
                document.getElementById('divPersonnel').style.display = 'block';
                document.getElementById('titreDiv').style.display='block';
                document.getElementById('minus').style.display = 'none';
                document.getElementById('glisser').style.display='none';
            },
        })
        .on("mouseover", function () {
            $(this).addClass("move-cursor")
        })
}

dragAffectedPompier();//cette fonction est necéssaire aprés la mise à jour du tableau (Ajax) sinon les pompiers affectés ne se déplacent pas

//*********************************************************************************************************************************
function placePompier($pompier, $poste){
    $pompier.css('width','100%');
    $pompier.css('height','100%');
    $pompier.css('left','0px');
    $pompier.css('top','0px');
    if( $poste.find('div').length>0){
        $poste.find('div').replaceWith( $pompier.css('position', 'absolute'));
    }
    else{
        $pompier.css('position', 'absolute').appendTo($poste);
    }
    saveDB($pompier, $poste);
}
//*********************************************************************************************************************************
function saveDB($pompier, $poste){
    var idPoste=$poste.attr('id').split('_');
    var idPompier=$pompier.attr('id').split('_');
    var periode=idPoste[1];
    var vehicule=idPoste[2];
    var piquet=idPoste[3];
    var evenement=idPoste[5];
    var pompier=idPompier[2];
    $.ajax({
        url: "save_piquet.php",
        data: {evenement: evenement, periode: periode, vehicule: vehicule, piquet: piquet, pid: pompier },
        success : function(code_html, statut){
            update_page(evenement,vehicule);
        },
        error : function(resultat, statut, erreur){
            console.log(erreur);
        },
    });
}
//*********************************************************************************************************************************
function dropMinus() {
    $('div[id="minus"]')
       .droppable({
        activeClass: "ui-hover",
        hoverClass: "ui-state-active",
        drop: function (event, ui) {
            idPoste = ui.draggable.parent().attr('id').split('_');
            idPompier = ui.draggable.attr('id').split('_');
            var periode = idPoste[1];
            var vehicule = idPoste[2];
            var piquet = idPoste[3];
            var evenement = idPoste[5];
            var pompier = idPompier[2];
            $.ajax({
                url: "deletePompier.php",
                data: {evenement: evenement, periode: periode, vehicule: vehicule, piquet: piquet, pid: pompier},
                success: function (code_html, statut) {
                    update_page(evenement, vehicule);
                },
                error: function (resultat, statut, erreur) {
                    console.log(erreur);
                },
            });
        }
    });
}

dropMinus();//suppression d'un pompier en le déplaçant sur le bonhomme minus
//*******************************************************************************************************************************************
function update_page(evenement,vehicule) {
    $.ajax({
        url: "update_page.php",
        data: {evenement:evenement, vehicule:vehicule},
        method: "GET",
        dataType: "html",
        success: function (data, statut) {
           document.getElementById(vehicule).innerHTML=data;
           dropPoste();
           dragAffectedPompier();
           doubleClickPompier24();
        },
        error : function(resultat, statut, erreur){
            console.log(erreur);
        }
    });
}

//*******************************************************************************************************************************************

function deletePlace($pompier) {
    idAncienPoste = $pompier.parent().attr('id').split('_');
    idPompier = $pompier.attr('id').split('_');
    var periode = idAncienPoste[1];
    var vehicule = idAncienPoste[2];
    var piquet = idAncienPoste[3];
    var evenement = idAncienPoste[5];
    var pompier = idPompier[2];
    $.ajax({
        url: "deletePompier.php",
        data: {evenement: evenement, periode: periode, vehicule: vehicule, piquet: piquet, pid: pompier},
        success: function (code_html, statut) {
            console.log(statut);
        },
        error: function (resultat, statut, erreur) {
            console.log(erreur);
        },
    });
}
//*******************************************************************************************************************************
function emptyPiquet() {
    $('button[id^="vide"]').click(function () {
        console.log($(this).attr('id'));
        idVide = $(this).attr('id');
        evenement = idVide.split('_')[1];
        console.log(evenement);
        $.ajax({
            url: "deletePompier.php",
            data: {evenement: evenement},
            success: function (code_html, statut) {
                console.log(statut);
                document.location.reload();
            },
            error: function (resultat, statut, erreur) {
                console.log(erreur);
            },
        });
    });
}
emptyPiquet()//vider les piquets
//*****************************************************************************************************************************
function affectAutoPiquet(){
    $('button[id^="affect"]').click(function () {
      idVehicules=$(this).attr('id').split('_');
      console.log("id vehicule",idVehicules);
      var evenement=idVehicules[1];
      $.ajax({
              url: "automaticPiquet.php",
              data: {evenement:evenement},
              method: "GET",
              dataType: "html",
              success: function (data, statut) {
                  console.log("chargementpage");
                  console.log(data);
                  document.location.reload();
              },
              error : function(resultat, statut, erreur){
                  console.log(erreur);
              }
          });
      });
}
affectAutoPiquet();
//*****************************************************************************************************************************
function doubleClickPompier24()
{
    $( 'div[id*="12"]' ).dblclick(function() {
        copie=$(this).clone();
        console.log("idparent ",$(this).parent().attr('id'));
        idPeriodeParent=$(this).parent().attr('id').split('_')[1];
        if(idPeriodeParent==1){
            idParentCible=$(this).parent().attr('id').replace("1","2");
        }else{
            idParentCible=$(this).parent().attr('id').replace("2","1");
        }
        console.log("idparentCible ",idParentCible);
        placePompier(copie,$('#'+idParentCible));
    });
}
doubleClickPompier24(); //la dupplication d'un pompier 24h pour la 2ième periode par un double click

});



