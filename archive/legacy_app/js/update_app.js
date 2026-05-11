function logEvent(message, withTime=1) {
    var logElem = document.querySelector(".upgrade_report");

    if ( withTime == 1 ) {
        var time = new Date();
        var timeStr = time.toLocaleTimeString();
        logElem.innerHTML += "<strong>"+ timeStr + "</strong> " + message + "<br/>";
    }
    else {
        logElem.innerHTML +=  message + "<br/>";
    }
}

function confirm_maj() {
    swal("Voulez vous faire la<br>mise à jour vers "+ newversion + " maintenant?<br>Cette opération prendra quelques secondes, voire minutes. Les utilisateurs ne pourront pas se connecter pendant cette courte période.",
    {addButton: 1, classButton: "btn-primary confirm", textButton: "Confirmer"},
    {class: 'icon swal2-info', style: 'flex', disableButton: 0});

    $('.confirm').on('click', function() {
        closeSwal();
        document.getElementById("update_button").style.display = "none";
        logEvent("<b>Début mise à jour</b>");
        maintenance_on ();
        return 0;
    });
}

function maintenance_on () {
    // activate maintenance mode
    logEvent("Passage de la base en mode maintenance <div id='spin_0' style='display: inline'><i class='fas fa-cog fa-spin fa-lg' title='maintenance mode'></i></div>");
    $.ajax({type: "POST", url: 'update_app.php', dataType: 'json', data: {reason: 'maintenance_on'},
        success: function() {
            document.getElementById("spin_0").innerHTML  = "<i class='fa fa-check' style='color:green' title='passsage en mode maintenance réussi'></i>";
            logEvent("Maintenance mode activé <i class='fa fa-check' style='color:green' title='Maintenance mode activé'></i>");
            download ();
        },
        error: function() {
            document.getElementById("spin_0").innerHTML  = "<i class='fas fa-exclamation-circle' style='color:red' title='erreur lors du passsage en mode maintenance'></i>";
            logEvent("Une erreur est survenu lors du passage en mode maintenance. <i class='fas fa-exclamation-circle'  style='color:red' title='erreur mode maintenance'></i>");
            return 1;
        }
    });
    return 0;
}

function download () {
    // download new package
    logEvent("Début téléchargement du nouveau package <div id='spin_1' style='display: inline'><i class='fas fa-cog fa-spin fa-lg' title='téléchargement en cours'></i></div>");
    $.ajax({type: "POST", url: 'download_package.php', dataType: 'json', data: {package: newpackage, md5sum: md5, reason: 'Mise a jour'},
        success: function() {
            document.getElementById("spin_1").innerHTML  = "<i class='fa fa-check' style='color:green' title='téléchargement réussi'></i>";
            logEvent("Téléchargement réussi de <b>" + newpackage +"</b> <i class='fa fa-check' style='color:green' title='téléchargement réussi'></i>");
            if ( auto_backup == 1 ) backup ();
            else unzip ();
        },
        error: function() {
            document.getElementById("spin_1").innerHTML  = "<i class='fas fa-exclamation-circle' style='color:red' title='erreur lors du téléchargement'></i>";
            logEvent("Une erreur est survenu lors du téléchargement de la mise à jour. Veuillez réessayer plus tard. <i class='fas fa-exclamation-circle'  style='color:red' title='erreur lors du téléchargement'></i>");
            return 1;
        }
    });
    return 0;
}

function backup () {
    // backup if needed
    logEvent("Début Backup de la base de données <div id='spin_2' style='display: inline'><i class='fas fa-cog fa-spin fa-lg' title='backup'></i></div>");
    $.ajax({type: "POST", url: 'update_app.php', dataType: 'json', data: {reason: 'backup'},
        success: function() {
            document.getElementById("spin_2").innerHTML  = "<i class='fa fa-check' style='color:green' title='database backup réussi'></i>";
            logEvent("Database backup réussi <i class='fa fa-check' style='color:green' title='backup réussi'></i>");
            unzip ();
        },
        error: function() {
            document.getElementById("spin_2").innerHTML  = "<i class='fas fa-exclamation-circle' style='color:red' title='erreur lors du backup'></i>";
            logEvent("Une erreur est survenu lors du backup de la database. <i class='fas fa-exclamation-circle'  style='color:red' title='erreur lors du backup'></i>");
            return 1;
        }
    });
    return 0;
}

function unzip () {
    // unzip
    logEvent("Début extraction de "+ newpackage +" <div id='spin_3' style='display: inline'><i class='fas fa-cog fa-spin fa-lg' title='unzip'></i></div>");
    $.ajax({type: "POST", url: 'update_app.php', dataType: 'json', data: {package: newpackage, reason: 'unzip'},
        success: function() {
            document.getElementById("spin_3").innerHTML  = "<i class='fa fa-check' style='color:green' title='unzip réussi'></i>";
            logEvent("Extraction réussie <i class='fa fa-check' style='color:green' title='unzip réussi'></i>");
            db_upgrade ();
        },
        error: function() {
            document.getElementById("spin_3").innerHTML  = "<i class='fas fa-exclamation-circle' style='color:red' title='erreur lors unzip'></i>";
            logEvent("Une erreur est survenu lors de l'extraction des nouveaux fichiers. <i class='fas fa-exclamation-circle'  style='color:red' title='erreur lors unzip'></i>");
            return 1;
        }
    });
    return 0;
}

function db_upgrade () {
    // db_upgrade
    logEvent("Début upgrade database vers <b>"+ newversion +"</b> <div id='spin_4' style='display: inline'><i class='fas fa-cog fa-spin fa-lg' title='db_upgrade'></i></div>");
    $.ajax({type: "POST", url: 'update_app.php', dataType: 'json', data: {package: newpackage, reason: 'db_upgrade'},
        success: function (data) {
            if (data == 2) {
                closeSwal();
                document.getElementById("spin_4").innerHTML  = "<i class='fas fa-exclamation-circle' style='color:red' title='version de la base incompatible avec les fichiers.'></i>";
                swal("La base de données est incompatible avec le code de l'application web<br>\
                version de l'application web:<b> "+newversion+"</b><br>\
                version requise pour la base de données:<b> "+newdbversion+"</b><br>\
                Vous devez manuellement exécuter les fichiers d'upgrade sur la base(voir répertoire sql)<br>\
                Vous devez désactiver manuellement <br>le mode maintenance");
                logEvent("Erreur: Pas de fichier sql d'upgrade trouvé <i class='fas fa-exclamation-circle' style='color:red' title='erreur lors du database upgrade'></i>");
                return 1;
            }
            else if (data == 1) {
                closeSwal();
                document.getElementById("spin_4").innerHTML  = "<i class='fa fa-check' style='color:green' title='database upgrade réalisé mais quelques erreurs'></i>";
                swal("La mise à jour vers la version "+newdbversion+" a généré des erreurs non bloquantes<br> <a href = "+logfile+" target=_blank>Consulter le rapport d'erreur.</a><br>Nous vous invitons à corriger les erreurs manuellement.");
                logEvent("Database upgrade réussi mais avec des erreurs <a href='" + logfile + "' target=_blank>Voir le log</a> <i class='fa fa-exclamation-triangle' style='color:orange' title='database upgrade réussi mais avec des erreurs'></i>");
            }
            else if (data == 0) {
                if ( currentdbversion == newdbversion ) {
                    document.getElementById("spin_4").innerHTML  = "<i class='fa fa-check' style='color:green' title='Pas de changements requis dans la base'></i>";
                }
                else {
                    document.getElementById("spin_4").innerHTML  = "<i class='fa fa-check' style='color:green' title='database upgrade réussi'></i>";
                    logEvent("Database upgrade réussi <a href='" + logfile + "' target=_blank>Voir le log</a> <i class='fa fa-check' style='color:green' title='database upgrade réussi'></i>");
                }
            }
            maintenance_off ();
        },
        error: function() {
            document.getElementById("spin_4").innerHTML  = "<i class='fas fa-exclamation-circle' style='color:red' title='erreur lors du database upgrade'></i>";
            logEvent("Une erreur est survenu pendant la mise à jour de la base de données. <a href='" + logfile + "' target=_blank>Voir le log</a> <i class='fas fa-exclamation-circle' style='color:red' title='erreur lors du database upgrade'></i>");
            return 1;
        }
    });
    return 0;
}

function maintenance_off () {
    // desactivate maintenance mode
    logEvent("Désactivation du mode maintenance <div id='spin_5' style='display: inline'><i class='fas fa-cog fa-spin fa-lg' title='maintenance mode'></i></div>");
    $.ajax({type: "POST", url: 'update_app.php', dataType: 'json', data: {reason: 'maintenance_off', patch_version: newversion},
        success: function() {
            document.getElementById("spin_5").innerHTML  = "<i class='fa fa-check' style='color:green' title='désactivation mode maintenance réussi'></i>";
            logEvent("Maintenance mode désactivé <i class='fa fa-check' style='color:green' title='Maintenance mode désactivé'></i>");
            logEvent("<b>Fin de la mise à jour. Vous utilisez maintenant " + newversion + "</b> ");
            logEvent("<br>Pensez à purger le cache du navigateur (CTRL + F5)",withTime=0);
        },
        error: function() {
            document.getElementById("spin_5").innerHTML  = "<i class='fas fa-exclamation-circle' style='color:red' title='erreur lors de la désactivation mode maintenance'></i>";
            logEvent("Une erreur est survenu lors de la désactivation du mode maintenance. <i class='fas fa-exclamation-circle' style='color:red' title='erreur mode maintenance'></i>");
            return 1;
        }
    });
    return 0;
}