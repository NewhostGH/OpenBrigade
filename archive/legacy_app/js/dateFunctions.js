
function WrongSchedule(champ){
    swalAlert ("Attention l'heure de fin est avant le debut,\n veuillez corriger la date de fin ou les heures\nde début et de fin.");
    champ.value = '0';
}

function verifyDateRange(dtdebut,dtfin) {
    checkDate2(dtdebut);
    checkDate2(dtfin);
    var parts1 = dtdebut.value.split("-");
    var date1 = new Date(parts1[1] , parts1[0] , parts1[2]);
    var parts2 = dtfin.value.split("-");
    var date2 = new Date(parts2[1] , parts2[0] , parts2[2]);
    if (date1 > date2) {
        swalAlert ("ERREUR: La date de fin " + dtfin.value + " doit être égale ou postérieure à la date de début " + dtdebut.value + ".");
        dtfin.value = dtdebut.value;
    }
    return;
}


function EvtCalcDuree(date1,date2,heure1,heure2,champ){
// recherche les valeurs actuelles
var dtdb = date1.value;
var hrdb = heure1.value;
var dtfn = date2.value;
var hrfn = heure2.value;
if (dtdb == '') dtdb = "01-31-2008";
if (dtfn == '') dtfn=dtdb;

// transforme un objet date
dtdbTab = dtdb.split('-');
dtfnTab = dtfn.split('-');
hrdbTab = hrdb.split(':');
hrfnTab = hrfn.split(':');

var datedeb = new Date(dtdbTab[2],dtdbTab[1] -1,dtdbTab[0],hrdbTab[0],hrdbTab[1]);
var datefin = new Date(dtfnTab[2],dtfnTab[1] -1,dtfnTab[0],hrfnTab[0],hrfnTab[1]);
var curDuree = champ.value;

if (dtfn == dtdb) {
   if (parseInt(hrfnTab[0]) < parseInt(hrdbTab[0])) {
      WrongSchedule(champ);
      heure2.value = heure1.value;
      return;
   }
   if (parseInt(hrfnTab[0]) == parseInt(hrdbTab[0]))  {
         if (parseInt(hrfnTab[1]) < parseInt(hrdbTab[1])) {
          WrongSchedule(champ);
          heure2.value = heure1.value;
          return;
      }
   }
}
since = DateDiff(datedeb, datefin, 'hour');
if (since =='') {
    WrongSchedule(champ);
    return;
}
if ( champ.value != '999999'){
    var isiPad = navigator.userAgent.match(/iPad/i) != null;
    if ( isiPad ) {
        return;
    }
    champ.value = since;
 }
 return;
}

function DateDiff(from, until, format){
  var past = from == '' ? new Date() : new Date(from);
  var future = until == '' ? new Date() : new Date(until);

  var between = [
   future.getFullYear() - past.getFullYear(),
   future.getMonth() - past.getMonth(),
   future.getDate() - past.getDate(),
   future.getTime() - past.getTime()
  ];

 if(between[3] < 0){
   between[2]--;
   between[3] += 3600 * 1000;
  }
  
  if(between[2] < 0){
   between[1]--;
   var ynum = future.getFullYear();
   var mlengths = [31, (ynum % 4 == 0 && ynum % 100 != 0 || ynum % 400 == 0) ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
   var mnum = future.getMonth() - 1;
   if (mnum < 0){ mnum += 12; }
   between[2] += mlengths[mnum];
  }

  if(between[1] < 0){
   between[0]--;
   between[1] += 12;
  }
  return formatDateDiffNb(between, format);
 }

 function formatDateDiffNb(difference, format){
  var str = '';

  if(format == 'year'){
   if(difference[0] > 0){
    str += difference[0] + '';
   }
  }else if(format == 'month'){
   if(difference[1] > 0){
    str += difference[1] + '';  
   }
  }else if(format == 'day'){
   if (difference[2] > 0){
    str += difference[2] + ' day';
    str += difference[2] == 1 ? '' : 's';
   }
  }else if(format == 'hour'){
   if (difference[3] > 0){
    str += (difference[3] / 3600 / 1000) + '';
   }   
  }else{
   if(difference[0] > 0){
    str += difference[0] + ' year';
    str += difference[0] == 1 ? '' : 's';
    if (difference[1] > 0){
     str += difference[2] > 0 ? ', ' : ' and ';
    }else{
     str += difference[2] > 0 ? ' and ' : '';
    }
   }

   if(difference[1] > 0){
    str += difference[1] + ' month';
    str += difference[1] == 1 ? '' : 's';
    str += difference[2] > 0 ? ' and ' : '';
   }

   if (difference[2] > 0){
    str += difference[2] + ' day';
    str += difference[2] == 1 ? '' : 's';
   }
   
   if (difference[3] > 0){
    str += (difference[3] / 3600 / 1000) + ' hour';
    str += (difference[3] / 3600 / 1000) == 1 ? '' : 's';
   }      
  }
  return str;
 }