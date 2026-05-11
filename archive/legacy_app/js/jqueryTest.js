$(function()
{

    function savepiquet(evenement, folder, selector, htmldiv, popup, event) {
        console.log("selector", selector.childList);

        var selectedtext = selector.options[selector.selectedIndex].text;
        if (selectedtext == 'Personne') {
            Pname = '<small>Choisir</small>';
            var currentPid = 0;
        } else {
            var chunks = selectedtext.split('(');
            var Pname = chunks[0];
            if (chunks[1] !== undefined) {
                var grade = chunks[1].replace(')', '');
                if (grade != '') {
                    Pname = '<img src=' + folder + '/' + grade + '.png class=img-max-18> ' + Pname;
                }
            }
            var currentValue = selector.value.split("_");
            var currentQualified = currentValue[0];
            var currentPid = currentValue[1];
            if (currentQualified == 0) {
                Pname = Pname + "<i class='fa fa-warning' style='color:orange; title = 'Attention : personne non qualifi?e pour ce r?le'></i>"
            }
        }
        var res = selector.name.split("_");
        var periode = res[1];
        var vehicule = res[2];
        var piquet = res[3];
        htmldiv.innerHTML = Pname;
        blink(htmldiv, currentPid);
        popup.style.display = 'none';
        $.get('save_piquet.php', {
            evenement: evenement,
            periode: periode,
            vehicule: vehicule,
            piquet: piquet,
            pid: currentPid
        });
        if (currentPid > 0) {
            // supprimer la personne des autres piquets du vehicule
            var i;
            for (i = 1; i < 10; i++) {
                var div = document.getElementById('htmldiv_' + periode + '_' + vehicule + '_' + i);
                var sel = document.getElementById('select_' + periode + '_' + vehicule + '_' + i);
                if (div) {
                    if (div !== undefined && sel !== undefined && parseInt(piquet) !== 'NaN') {
                        var v = sel.value.split("_")[1];
                        if (v == currentPid && i != piquet) {
                            div.innerHTML = '<small>Choisir</small>';
                            blink(div, 0);
                        }
                    }
                }
            }
        }
        return true;
    }
}