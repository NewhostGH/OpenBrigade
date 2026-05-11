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

// function confirm_maj() {
//     swal("Voulez vous faire la<br>mise à jour vers maintenant?<br>Cette opération prendra quelques secondes, voire minutes. Les utilisateurs ne pourront pas se connecter pendant cette courte période.",
//         {addButton: 1, classButton: "btn-primary confirm", textButton: "Confirmer"},
//         {class: 'icon swal2-info', style: 'flex', disableButton: 0});
//
//     $('.confirm').on('click', function() {
//         closeSwal();
//         // document.getElementById("update_button").style.display = "none";
//         logEvent("<b>Début mise à jour</b>");
//         maintenance_on ();
//         return 0;
//     });
// }

function install_init (module, version, licence, md5, libelle, description, end_datetime, section_id, seats) {
    download_addon(module, md5, version, libelle, description, licence, end_datetime, section_id, seats);
}

function download_addon (module, md5, version, libelle, description, licence, end_datetime, section_id, seats) {
    // activate maintenance mode
    logEvent("Téléchargement du module <div id='spin_0' style='display: inline'><i class='fas fa-cog fa-spin fa-lg' title='maintenance mode'></i></div>");
    $.ajax({type: "POST", url: 'download_addon.php', dataType: 'json', data: {module: module, version: version, md5sum: md5, reason: 'download'},
        success: function() {
            document.getElementById("spin_0").innerHTML  = "<i class='fa fa-check' style='color:green' title='passsage en mode maintenance réussi'></i>";
            // logEvent("Module téléchargé <i class='fa fa-check' style='color:green' title='Maintenance mode activé'></i>");
            install_addon (module, version, libelle, description, licence, end_datetime, section_id, seats);
        },
        error: function() {
            document.getElementById("spin_0").innerHTML  = "<i class='fas fa-exclamation-circle' style='color:red' title='erreur lors du passsage en mode maintenance'></i>";
            // logEvent("Une erreur est survenue lors du téléchargement du module <i class='fas fa-exclamation-circle'  style='color:red' title='erreur mode maintenance'></i>");
            return 1;
        }
    });
    return 0;
}

function install_addon (module, version, libelle, description, licence, end_datetime, section_id, seats) {
    // download new package
    logEvent("Installation du module <div id='spin_1' style='display: inline'><i class='fas fa-cog fa-spin fa-lg' title='téléchargement en cours'></i></div>");
    $.ajax({type: "POST", url: 'install_addon.php', dataType: 'json', data: {module: module, reason: 'install'},
        success: function() {
            document.getElementById("spin_1").innerHTML  = "<i class='fa fa-check' style='color:green' title='téléchargement réussi'></i>";
            // logEvent("Module installé </b> <i class='fa fa-check' style='color:green' title='téléchargement réussi'></i>");
            install_addon_db (module, version, libelle, description, licence, end_datetime, section_id, seats);
        },
        error: function(xhr, textStatus, error) {
            console.log(xhr.responseText);
            console.log(xhr.statusText);
            console.log(textStatus);
            console.log(error);
            document.getElementById("spin_1").innerHTML  = "<i class='fas fa-exclamation-circle' style='color:red' title='erreur lors du téléchargement'></i>";
            logEvent("Un erreur est survenue lors de l'installation du module "+libelle+" <i class='fas fa-exclamation-circle'  style='color:red' title='erreur lors du téléchargement'></i>");
            return 1;
        }
    });
    return 0;
}

function install_addon_db (module, version, libelle, description, licence, end_datetime, section_id, seats) {
    // download new package
    logEvent("Importation de la configuration <div id='spin_2' style='display: inline'><i class='fas fa-cog fa-spin fa-lg' title='téléchargement en cours'></i></div>");
    $.ajax({type: "POST", url: 'install_addon.php', dataType: 'json', data: {module: module, libelle: libelle, version: version, description: description, reason: 'install_db'},
        success: function() {
            document.getElementById("spin_2").innerHTML  = "<i class='fa fa-check' style='color:green' title='téléchargement réussi'></i>";
            // logEvent("Module installé </b> <i class='fa fa-check' style='color:green' title='téléchargement réussi'></i>");
            delete_addon (module, libelle, description, licence, end_datetime, section_id, seats);
        },
        error: function(xhr, textStatus, error) {
            console.log(xhr.responseText);
            console.log(xhr.statusText);
            console.log(textStatus);
            console.log(error);
            document.getElementById("spin_2").innerHTML  = "<i class='fas fa-exclamation-circle' style='color:red' title='erreur lors du téléchargement'></i>";
            // logEvent("Un erreur est survenue lors de l'installation du module "+libelle+" <i class='fas fa-exclamation-circle'  style='color:red' title='erreur lors du téléchargement'></i>");
            return 1;
        }
    });
    return 0;
}

function delete_addon (module, libelle, description, licence, end_datetime, section_id, seats) {
    // db_upgrade
    logEvent("Nettoyage des fichiers temporaires </b> <div id='spin_3' style='display: inline'><i class='fas fa-cog fa-spin fa-lg' title='db_upgrade'></i></div>");
    $.ajax({type: "POST", url: 'install_addon.php', dataType: 'json', data: {module: module, reason: 'delete'},
        success: function (data) {
            document.getElementById("spin_3").innerHTML  = "<i class='fa fa-check' style='color:green' title='database backup réussi'></i>";
            // logEvent("Nettoyage terminée <i class='fa fa-check' style='color:green' title='backup réussi'></i>");
            insert_licence(module, libelle, description, licence, end_datetime, section_id, seats);
        },
        error: function() {
            document.getElementById("spin_3").innerHTML  = "<i class='fas fa-exclamation-circle' style='color:red' title='erreur lors du database upgrade'></i>";
            // logEvent("Une erreur est survenue lors du nettoyage. Veuillez supprimer manuellement <a href='" + logfile + "' target=_blank>Voir le log</a> <i class='fas fa-exclamation-circle' style='color:red' title='erreur lors du database upgrade'></i>");
            return 1;
        }
    });
    return 0;
}

function insert_licence (module, libelle, description, licence, end_datetime, section_id, seats) {
    // backup if needed
    logEvent("Importation de la licence <div id='spin_4' style='display: inline'><i class='fas fa-cog fa-spin fa-lg' title='backup'></i></div>");
    $.ajax({type: "POST", url: 'install_addon.php', dataType: 'json', data: {module: module, licence: licence, end_datetime: end_datetime, section_id: section_id, seats: seats, reason: 'import_licence'},
        success: function() {
            document.getElementById("spin_4").innerHTML  = "<i class='fa fa-check' style='color:green' title='database backup réussi'></i>";
            // logEvent("Importation terminée <i class='fa fa-check' style='color:green' title='backup réussi'></i>");
            // delete_addon (module);
        },
        error: function() {
            document.getElementById("spin_4").innerHTML  = "<i class='fas fa-exclamation-circle' style='color:red' title='erreur lors du backup'></i>";
            // logEvent("Une erreur est survenue lors de l'importation de la licence <i class='fas fa-exclamation-circle'  style='color:red' title='erreur lors du backup'></i>");
            return 1;
        }
    });
    return 0;
}
