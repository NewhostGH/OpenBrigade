//-- Global Variables
var RowsInForm = 5


//=====================================================================
// Mise à jour des totaux
//=====================================================================

//-- Updates the totals in the lower part of table.
function updateTotal(mybox,totalbox) {
    var V = parseInt(totalbox.value);
    if ( mybox.checked ) {
          totalbox.value = V + 1;
    }
    else {
        totalbox.value = V - 1;
    }
}

//=====================================================================
// choix personne
//=====================================================================
function redirect(p1,p2,p3,p4,p5,p6) {
     if ( p4 == 'saisie' ) {
         url="dispo.php?person="+p1+"&month="+p2+"&year="+p3;
         self.location.href=url;
     }
     if ( p4 == 'ouvrir' ) {
        if ( confirm ("Attention : Vous allez permettre la saisie des disponibilités pour le mois "+p2+"/"+p3+" par le personnel de "+p6+".\nLes agents pourront de nouveau modifier leur disponibilités.\nConfirmer ?" )) {
          cible="tableau_garde_status.php?month="+p2+"&year="+p3+"&action=ouvrir&filter="+p5+"&person="+p1;
          self.location.href = cible;
        }
     }
      if ( p4 == 'fermer' ) {
        if ( confirm ("Attention : Vous allez bloquer la saisie des disponibilités pour le mois "+p2+"/"+p3+" par le personnel de "+p6+".\nLes agents ne pourront plus saisir ou modifier leur disponibilités pour le mois suivant.\nConfirmer ?" )) {
            cible="tableau_garde_status.php?month="+p2+"&year="+p3+"&action=fermer&filter="+p5+"&person="+p1;
            self.location.href = cible;
        }
     }
     
}

//=====================================================================
// check all
//=====================================================================
function CheckAll(field,checkValue){
    var dForm = document.dispo;
    var F = 'total'+field;
    var V = document.getElementById(F).value;
    
    // Vérif du compteur
    document.getElementById(F).value = ((checkValue!=true)? V:0 );

    // Parcours des jours et mise à jour des cases à cocher
    for (i=0;i<dForm.length;i++)
    {
        var element = dForm[i];
        if (element.type=='checkbox'){
            var G = 'total'+element.name.substring(0,1);
            var B = document.getElementById(G);
            if (element.name.substring(0,1)==field){
                if ( element.disabled == false ) {
                    element.checked = ((checkValue!=true)?false:true);
                    updateTotal(element,B);
                }
            }    
        }
    }
}

function myalert(year,month,day,section,poste) {
    self.location.href = "alerte_create.php?section="+section+"&poste="+poste+"&dispo="+year+"-"+month+"-"+day;
}

function redirect_homme(year,month,section) {
    url = "dispo.php?tab=3&month="+month+"&year="+year+"&filter="+section;
    self.location.href = url;
}

function fillmenu(frm, menu1,menu2,person) {
    year=frm.menu1.options[frm.menu1.selectedIndex].value;
    month=frm.menu2.options[frm.menu2.selectedIndex].value;
    url = "upd_personnel.php?from=default&tab=14&pompier="+person+"&person="+person+"&table=1&year="+year+"&month="+month;
    self.location.href = url;
}

function fillmenu_2(frm, menu1,menu2,menu3,menu4,menu5) {
    year=frm.menu1.options[frm.menu1.selectedIndex].value;
    month=frm.menu2.options[frm.menu2.selectedIndex].value;
    day=frm.menu3.options[frm.menu3.selectedIndex].value;
    section=frm.menu4.options[frm.menu4.selectedIndex].value;
    poste=frm.menu5.options[frm.menu5.selectedIndex].value;
    url = "dispo.php?tab=2&month="+month+"&year="+year+"&day="+day+"&poste="+poste+"&filter="+section+"&print=NO";
    self.location.href = url;
}

function fillmenu_4(frm, menu1,menu2,menu3) { 
    year=frm.menu1.options[frm.menu1.selectedIndex].value;
    month=frm.menu2.options[frm.menu2.selectedIndex].value;
    section=frm.menu3.options[frm.menu3.selectedIndex].value;
    url = 'dispo.php?tab=4&month='+month+'&year='+year+'&filter='+section;
    self.location.href = url;
}

$(document).ready(function() {
    var mql = window.matchMedia('(max-width:768px) and (orientation:portrait)');
    var isVMobile = mql.matches;
    mql.addEventListener('change', function() {
        isVMobile = mql.matches;
    });
    
    var startDay = document.querySelectorAll(".dispoEmptyTdBegin").length;
    var daysOfTheWeek = ["Lundi", "Mardi", "Mercredi", "Jeudi", "Vendredi", "Samedi", "Dimanche"];
    var dispoTds = document.querySelectorAll(".dispoEmptyTdBegin, .dispoDayTd, .dispoEmptyTdEnd");
    var dispoDayTds = document.querySelectorAll(".dispoDayTd");
    var dispoTHeader = $('#dispoTHeader').html();
    var dpAM = document.querySelector("[data-dp-code-am='AM']");
    var resized = false;
    $(window).resize(function() {
        if(window.innerWidth < 768 || isVMobile) {
            if (!resized) {
                $('#dispoTHeader').html("");
                $('.dispoWeeks').remove();
                for (var i = 0; i < dispoDayTds.length; i++) {
                    var tr = document.createElement('tr');
                    tr.setAttribute('class', 'dispoWeeksToggle');
                    tr.style.borderBottom = "2px solid white";
                    var td1 = document.createElement('td');
                    td1.style.backgroundColor = dispoDayTds[i].style.backgroundColor;
                    td1.appendChild(dispoDayTds[i].querySelector('.dispoDayNumber').cloneNode(true));
                    var span = document.createElement("span");
                    span.appendChild(document.createTextNode(daysOfTheWeek[(startDay + i) % daysOfTheWeek.length] + " "));
                    span.setAttribute('class', 'dispoDayOfTheWeekToggle');
                    td1.insertBefore(span, td1.querySelector('.dispoDayNumber'));
                    tr.appendChild(td1);
                    dispoDayTds[i].querySelector('table tr:first-child').style.display = "none";
                    tr.appendChild(dispoDayTds[i]);
                    document.getElementById('dispoTHeader').parentElement.appendChild(tr);
                }
                if (dpAM) [dpAM.textContent, dpAM.dataset.dpCodeAm] = [dpAM.dataset.dpCodeAm, dpAM.textContent];
            }
            resized = true;
        } else {
            if ($('.dispoWeeks').length === 0) {
                $('#dispoTHeader').html(dispoTHeader);
                $('.dispoWeeksToggle').remove();
                for (var i = 0; i < dispoDayTds.length; i++) {
                    dispoDayTds[i].querySelector('table tr:first-child').style.display = "table-row";
                }
                for (var i = 0; i < dispoTds.length / 7; i++) {
                    var tr = document.createElement('tr');
                    tr.setAttribute('class', 'dispoWeeks');
                    for (var j = 0; j < 7; j++) {
                        tr.appendChild(dispoTds[i*7+j]);
                    }
                    document.getElementById('dispoTHeader').parentElement.appendChild(tr);
                }
                if (dpAM) [dpAM.textContent, dpAM.dataset.dpCodeAm] = [dpAM.dataset.dpCodeAm, dpAM.textContent];
            }
            resized = false;
        }
    }).resize();
});