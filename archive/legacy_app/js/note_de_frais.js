
function change_statut(note,action,csrf) {
    section = document.getElementById('section').value;
    url = "note_frais_save.php?nfid="+note+"&action="+action+"&csrf_token_note="+csrf;
    self.location.href = url;
}

function confirm_reject(note,csrf) {
    rc = document.getElementById('reject_comment').value;
    url = "note_frais_save.php?nfid="+note+"&action=reject&reject_comment="+rc+"&csrf_token_note="+csrf;
    self.location.href = url;
}

function delete_note(nfid,from1,csrf) {
    section = document.getElementById('section').value;
    if ( confirm ('Voulez vous vraiment supprimer la note de frais ' + nfid +  '?' )) {
        url = "note_frais_save.php?action=delete&from="+from1+"&nfid="+nfid+"&csrf_token_note="+csrf;
        self.location.href = url;
    }
}

function deletefile(nfid, fileid, file) {
    if ( confirm ('Voulez vous vraiment supprimer le justificatif ' + file +  '?' )) {
        self.location = 'delete_event_file.php?number=' + nfid + '&fileid=' + fileid + '&type=note&file=' + file;
    }
}

function calculateTotal() {
    var total = 0;
    $('.montant').each(function() {
        value = $(this).val();
        if ( value == '' ) value=0;
        total += Number(value);
    });
    total = Math.round( total * 100)/100;
    $('#sum').val(total.toFixed(2));
    updateButtons();
}

function updateButtons() {
    $('#envoyer').attr('disabled', true);
    $('#envoyer').attr('title', 'Vous devez enregistrer les changements avant d\'envoyer la note pour validation');
    $('#valider').attr('disabled', true);
    $('#valider').attr('title', 'Vous devez enregistrer les changements avant de valider');
    $('#valider1').attr('disabled', true);
    $('#valider1').attr('title', 'Vous devez enregistrer les changements avant de valider');
    $('#rejeter').attr('disabled', true);
    $('#rejeter').attr('title', 'Vous devez enregistrer les changements avant de rejeter');
    $('#rembourser').attr('disabled', true);
    $('#rembourser').attr('title', 'Vous devez enregistrer les changements avant de rembourser');
    $('#save').removeClass('btn-default');
    $('#save').addClass('btn-primary');
    $('#save').attr('title', 'N\'oubliez pas de sauver les changements'); 
}

function calculateLigne(targetid) {
    var parts = targetid.match(/(\D+)(\d+)$/);
    var subtotal = document.getElementById('montant'+parts[2]);
    var quantite = document.getElementById(targetid).value;
    var type = document.getElementById('type'+parts[2]).value;
    var pattern=new RegExp("_");

    if (pattern.test(type)) {
        var prixUnitaire=type.split("_")[1];
        if (subtotal.value == '') subtotal.value = 0;
        subtotal.value = (Math.round(quantite * prixUnitaire * 100)/100).toFixed(2) ;
        calculateTotal();
    }
}

function changeType(selectform) {
    updateButtons();
    var parts = selectform.id.match(/(\D+)(\d+)$/);
    var number=parts[2];
    var s = document.getElementById('type'+number);
    var w = document.getElementById('warning'+number);
    var o = s.options[s.selectedIndex];
    var value = o.value;
    var text = o.text;
    var title = o.title;
    if ( title != '' ) {
        w.innerHTML = "<i class='fa fa-warning' style='color:red' title=\""+ title + "\"></i>";
    } else {
        w.innerHTML = "";
    }
}

function openNewDocument(note,section,person){
    url='upd_document.php?person='+person+'&section='+section+'&note='+note;
    self.location.href=url;
}

$(document).ready(function($) {
    syndicate=document.getElementById('syndicate').value;

    // uncheck boxes if needed
    $('#national').click (function() {
        if ($("#national").is (':checked')) {
            $("#departemental").prop('checked',false);
        } else {
            if (syndicate == 1) $("#departemental").prop('checked', true);
        }
    });

    $('#departemental').click (function() {
        if ($("#departemental").is(':checked')) {
            $("#national").prop('checked', false);
        } else {
            if (syndicate == 1) {
                $("#national").prop('checked', true);
            }
        }
    });

    // calculer montant total
    $('.montant').change(function () {
        calculateTotal();
    });
    // calculer montant ligne
    $('.quantite').change(function (event) {
        calculateLigne(event.target.id);
    });
 
    // remove a row when clicking
    $('#NoteFraisTable td i.delete').click(function(){
        var rowCount = $('#NoteFraisTable >tbody >tr').length;
        if (rowCount > 2) {
            $(this).parent().parent().remove();
            calculateTotal();
        } else {
            swalAlert("Vous devez conserver au moins une ligne");
        }
    });
  
    $('#ajouterTabRow').click(function() {
        // add new row to table using addTableRow function
        addTableRow($('#NoteFraisTableDetail'));

        // prevent button redirecting to new page
        return false;
    });
   
    // function to add a new row to a table by cloning the last row and
    // incrementing the name and id values by 1 to make them unique
    function addTableRow(table) {
        var rowCount = $('#NoteFraisTableDetail >tbody >tr').length;
        var maxi = 15;
        if ( rowCount == maxi ) {
            swalAlert("Vous ne pouvez pas avoir plus de "+maxi+" ligne");
        } else {
            // clone the last row in the table
            var $tr = $(table).find("tbody tr:last").clone();

            // get the name attribute for the input and select fields
            $tr.find("input,select,div").attr("name", function() {
                // break the field name and it's number into two parts
                var parts = this.id.match(/(\D+)(\d+)$/);
                var nextnumber = ++parts[2];
                return parts[1] + nextnumber;
                // repeat for id attributes
            }).attr("id", function() {
                var parts = this.id.match(/(\D+)(\d+)$/);
                var nextnumber = ++parts[2];
                return parts[1] + nextnumber;
            });
         
            // append the new row to the table
            $(table).find("tbody tr:last").after($tr);
    
            // erase 4 fields
            $(table).find("tbody tr:last input.commentaire").val('');
            $(table).find("tbody tr:last input.montant").val('');
            $(table).find("tbody tr:last input.quantite").val('');
            $(table).find("tbody tr:last div.warning").html('');
            calculateTotal();

            // remove a row when clicking 
            $tr.find('i.delete').click(function(){
                var rowCount = $('#NoteFraisTableDetail >tbody >tr').length;
                if ( rowCount > 2 ) {
                    $(this).parent().parent().remove();
                    calculateTotal();
                }
                else {
                    swalAlert("Vous devez conserver au moins une ligne");
                }
            });

            // calculer montant ligne
            $('.quantite').change(function (event) {
                calculateLigne(event.target.id);
            });
            // calculer montant total
            $('.montant').change(function () {
                calculateTotal();
            });
        }
    };

    // remove a row when clicking  
    $('#NoteFraisTableDetail td i.delete').click(function(){
        var rowCount = $('#NoteFraisTableDetail >tbody >tr').length;
        if ( rowCount > 2 ) {
            $(this).parent().parent().remove();
            calculateTotal();
        }
        else {
            swalAlert("Vous devez conserver au moins une ligne");
        }
    });

    // calculer montant ligne
    $('.quantite').change(function (event) {
        calculateLigne(event.target.id);
    });
    // calculer montant total
    $('.montant').change(function () {
        calculateTotal();
    });
});

function checkNumberwithMax(textfield) {
    var parts = textfield.getAttribute("name").match(/(\D+)(\d+)$/);
    var k = parts[1];
    var id = parts[2];
    if ( k == 'montant' ) checkFloat(textfield,'0');
    if ( k == 'quantite' ) checkNumberNullAllowed(textfield,'1');
    var qval = document.getElementById('quantite'+id).value;
    var w = document.getElementById('warning'+id);
    var m = document.getElementById('montant'+id);
    var x = document.getElementById('type'+id).selectedIndex;
    var y = document.getElementById('type'+id).options;
    var str = y[x].text;
    var n = str.search("max");
    if ( n > 0 && qval > 0 ) {
        var am = str.split("(")[1].split(")")[0].replace(/[^\d.]/g, '');
        if ( m.value / qval > am ) {
            var newval = am * qval;
            swalAlert ('Attention le maximum permis est '+ am +' / personne. Le montant total est donc automatiquement reduit a '+ qval + ' x ' + am + ' soit ' + newval.toFixed(2));
            m.value = newval.toFixed(2);
        }
    }
    calculateTotal();
}