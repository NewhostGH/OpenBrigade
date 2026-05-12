function bouton_redirect(cible) {
     self.location.href = cible;
}

function calculateTotal() {
    var total = 0;
    $('.subtotal').each(function() {
        total += Number($(this).val());
    });
    total = Math.round( total * 100)/100;
    $('#sum').val(total);
    if (total > 0 ) {
        $("#save").removeAttr('disabled');
    }
}

function calculateLigne(targetid) {
    var parts = targetid.match(/(\D+)(\d+)$/);
    var id = parts[2];
    var quantite = document.getElementById('quantite'+id).value;
    var pu = document.getElementById('pu'+id).value;
    var remise = document.getElementById('remise'+id).value;
    var subtotal = document.getElementById('subtotal'+id);
    subtotal.value = Math.round(quantite * pu * 100 * ( 1 - remise / 100)) / 100;
    calculateTotal();
}


$(document).ready(function($) {
  $('.quantite').change(function (event) {
    calculateLigne(event.target.id);
    calculateTotal();
  });
  $('.pu').change(function (event) {
    calculateLigne(event.target.id);
    calculateTotal();
  });
  $('.remise').change(function (event) {
    calculateLigne(event.target.id);
    calculateTotal();
  });
  $('.labelx').click(function (event) {
    var parts = this.id.match(/(\D+)(\d+)$/);
    var i = parts[2];
    ReverseContentDisplay('t' + i);
    return false;
  });
  $('.closepopup').click(function (event) {
    var parts = this.id.match(/(\D+)(\d+)$/);
    var i = parts[2];
    HideContent('t' + i);
    return false;
  });
  
  // if an element is selected in the dropdown
  $('.type').change(function (event) {
    var parts = event.target.id.match(/(\D+)(\d+)$/);
    var id = parts[2];
    var newcomment = document.getElementById('element'+id).value;
    var reg=new RegExp("[;]+", "g");
    var array=newcomment.split(reg);
    $('#label'+id).attr('value',array[1]);
    $('#commentaire'+id).val(array[2]);
    $('#pu'+id).val(array[3]);
    $('#quantite'+id).val(1);
    $('#remise'+id).val(0);
    ReverseContentDisplay('t'+id);
    calculateLigne(event.target.id);
    calculateTotal();
    $("#facture_detail_form").submit();
  });
 
  // remove a row when clicking
  $('#FactureTable td i.delete').click(function(){
        var rowCount = $('#FactureTable >tbody >tr').length;
        if ( rowCount > 1 ) {
            $(this).parent().parent().remove();
            calculateTotal();
        }
        else {
          swalAlert("Vous devez conserver au moins une ligne");
        }
  });

  // trigger event when button is clicked
  $('#FactureTable td button.ajouter').click(function() {
    // add new row to table using addTableRow function
    addTableRow($('#FactureTable'));

    // prevent button redirecting to new page
    return false;
  });
   
  // function to add a new row to a table by cloning the last row and
  // incrementing the name and id values by 1 to make them unique
  function addTableRow(table) {
    var rowCount = $('#FactureTable >tbody >tr').length;
    var maxi = 100;
    if ( rowCount == maxi ) {
        swalAlert("Vous ne pouvez pas avoir plus de "+maxi+" ligne");
        return false;
    }
    else {
        // clone the last row in the table
        var $tr = $(table).find("tbody tr:last").clone();

        // get the name attribute for the input and select fields
        $tr.find("input,select,div,button").attr("name", function()
        {
            // break the field name and it's number into two parts
            var parts = this.id.match(/(\D+)(\d+)$/);
            return parts[1] + ++parts[2];
            // repeat for id attributes
        }).attr("id", function()
        {
            var parts = this.id.match(/(\D+)(\d+)$/);
            return parts[1] + ++parts[2];
        });
         
        // append the new row to the table
        $(table).find("tbody tr:last").after($tr);
    
        // erase fields
        $(table).find("tbody tr:last input.type").val('');
        $(table).find("tbody tr:last input.commentaire").val('');
        $(table).find("tbody tr:last input.pu").val('0');
        $(table).find("tbody tr:last input.quantite").val('');
        $(table).find("tbody tr:last input.remise").val('0');
        $(table).find("tbody tr:last input.subtotal").val('0');
    
        calculateTotal();

        // remove a row when clicking  
        $('#FactureTable td i.delete').click(function(){
            var rowCount = $('#FactureTable >tbody >tr').length;
            if ( rowCount > 1 ) {
                $(this).parent().parent().remove();
                calculateTotal();
            }
        });
        
        $('.quantite').change(function (event) {
            calculateLigne(event.target.id);
            calculateTotal();
        });
        $('.pu').change(function (event) {
            calculateLigne(event.target.id);
            calculateTotal();
        });
        $('.remise').change(function (event) {
            calculateLigne(event.target.id);
            calculateTotal();
        });
        $('.labelx').click(function (event) {
            var parts = event.target.id.match(/(\D+)(\d+)$/);
            var i = parts[2];
            ReverseContentDisplay('t' + i);
            return false;
        });
        $('.closepopup').click(function (event) {
            var parts = event.target.id.match(/(\D+)(\d+)$/);
            var i = parts[2];
            HideContent('t' + i);
            return false;
        });
        // if an element is selected in the dropdown
        $('.type').change(function (event) {
            var parts = event.target.id.match(/(\D+)(\d+)$/);
            var id = parts[2];
            var newcomment = document.getElementById('element'+id).value;
            var reg=new RegExp("[;]+", "g");
            var array=newcomment.split(reg);
            $('#label'+id).attr('value',array[1]);
            $('#commentaire'+id).val(array[2]);
            $('#pu'+id).val(array[3]);
            $('#quantite'+id).val(1);
            $('#remise'+id).val(0);
            ReverseContentDisplay('t'+id);
            calculateLigne(event.target.id);
            calculateTotal();
            $("#facture_detail_form").submit();
        });
    }
  };
});
