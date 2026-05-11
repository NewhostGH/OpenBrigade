function redirect(url) {
    self.location.href=url;
}

function goback(operation,code) {
    if (operation == 'insert' ) {
        url="parametrage.php?tab=5&child=14&operation=insert&upd=1";

    }
    else {
        url="parametrage.php?tab=5&child=14&operation=update&upd=1&old=" + code;
    }
    self.location.href=url;
}

function errorCat(operation){
    swal("Veuillez remplir tous les champs obligatoires",
        {addButton: 0},
        {class: 'icon swal2-info', style: 'flex', disableButton: 0});
    $('.swal2-confirm').on('click', function() {
        closeSwal();
        url="parametrage.php?tab=5&child=14&ope=edit_cat&"+operation+"=1";
        self.location.href=url;
        return 0;
    });
}

function errorGrade(operation, oldGrade){
    swal("Veuillez remplir tous les champs obligatoires",
        {addButton: 0},
        {class: 'icon swal2-info', style: 'flex', disableButton: 0});
    $('.swal2-confirm').on('click', function() {
        closeSwal();
        url="parametrage.php?tab=5&child=14&operation="+operation+"&upd=1&old="+oldGrade;
        self.location.href=url;
        return 0;
    });
}

function errorExtIcone(erreur,operation, oldGrade){
    swal( erreur,
        {addButton: 0},
        {class: 'icon swal2-info', style: 'flex', disableButton: 0});
    $('.swal2-confirm').on('click', function() {
        closeSwal();
        url="parametrage.php?tab=5&child=14&operation="+operation+"&upd=1&old="+oldGrade;
        self.location.href=url;
        return 0;
    });
}

function errorUploadIcone(erreur,operation, oldGrade){
    swal( erreur,
        {addButton: 0},
        {class: 'icon swal2-info', style: 'flex', disableButton: 0});
    $('.swal2-confirm').on('click', function() {
        closeSwal();
        url="parametrage.php?tab=5&child=14&operation="+operation+"&upd=1&old="+oldGrade;
        self.location.href=url;
        return 0;
    });
}

function errorCatExist(operation){
    swal("Le code de catégorie choisi existe déjà dans la base de données. Il doit être unique.",
        {addButton: 0},
        {class: 'icon swal2-info', style: 'flex', disableButton: 0});
    $('.swal2-confirm').on('click', function() {
        closeSwal();
        url="parametrage.php?tab=5&child=14&ope=edit_cat&"+operation+"=1"
        self.location.href=url;
        return 0;
    });
}

function errorGradeExist(operation, oldGrade){
    swal("Le code de grade choisi existe déjà dans la base de données. Il doit être unique.",
        {addButton: 0},
        {class: 'icon swal2-info', style: 'flex', disableButton: 0});
    $('.swal2-confirm').on('click', function() {
        closeSwal();
        url="parametrage.php?tab=5&child=14&operation="+operation+"&upd=1&old="+oldGrade;
        self.location.href=url;
        return 0;
    });
}

function errorIconeGradeExist(operation, old){
    swal("Le nom du fichier choisi existe déjà dans la base de données. Il doit être unique.",
        {addButton: 0},
        {class: 'icon swal2-info', style: 'flex', disableButton: 0});
    $('.swal2-confirm').on('click', function() {
        closeSwal();
        url="parametrage.php?tab=5&child=14&operation="+operation+"&upd=1&old="+old;
        self.location.href=url;
        return 0;
    });
}

function suppress(code) {
    swal("Voulez vous vraiment supprimer ce grade?",
        {addButton: 1, classButton: "btn-primary confirm", textButton: "Oui"},
        {class: 'icon swal2-info', style: 'flex', disableButton: 0});
    $('.confirm').on('click', function() {
        closeSwal();
        url="del_grade.php?G_GRADE="+code;
        self.location.href=url;
        return 0;
    });
}

function suppressCat(code) {
    swal("Voulez vous vraiment supprimer cette catégorie?",
        {addButton: 1, classButton: "btn-primary confirm", textButton: "Oui"},
        {class: 'icon swal2-info', style: 'flex', disableButton: 0});
    $('.confirm').on('click', function() {
        closeSwal();
        url="del_categorie_grade.php?CG_CATEGORY="+code;
        self.location.href=url;
        return 0;
    });
}

function suppressWithUser(code) {
    if ( confirm("Voulez vous vraiment supprimer ce grade malgré qu'il soit associé à au moins une personne?") ) {
        url="del_grade.php?G_GRADE="+code+"&user=on";
        self.location.href=url;
    }
    else{
        url="parametrage.php?tab=5&child=14";
        self.location.href=url;
    }
}

function orderfilter(p1){
    self.location.href="parametrage.php?tab=5&child=14&catGrade="+p1;
    return true
}

function orderfilterActiv(p1){
    self.location.href="parametrage.php?tab=5&child=14&activ="+p1;
    return true
}

function bouton_redirect(cible) {
    self.location.href = cible;
}

function activGrade(code) {
    $.ajax({
        url : 'save_grades.php',
        type : 'GET',
        data : {codeGrade : code} ,
        success : function(code_html, statut){
            console.log(statut);
        },

        error : function(resultat, statut, erreur){
            console.log(erreur);
            swal("La modification n'a pas été prise en compte. Veuillez réessayer plus tard.");
        }

    });

}

function change_grade(code, image, name ) {
    g = document.getElementById('grade');
    g.value=code;
    c = document.getElementById('current');
    c.innerHTML= "<img src='" + image + "' style='max-width:25px;'> " +  name;
}

function change_cat(code, name ) {
    g = document.getElementById('categorie');
    g.value=code;
    c = document.getElementById('current');
    c.innerHTML= name;
}





