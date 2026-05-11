function redirect(url) {
     self.location.href=url;
}

function suppress(id) {
	if ( confirm("Voulez vous vraiment supprimer ce type de consommable?\n tous les articles de ce type seront supprimés")) {
		url="del_type_consommable.php?TC_ID="+id;
		self.location.href=url;
	}
	else{
       	url="parametrage.php?tab=3&child=4&ope=edit&id="+id;
		self.location.href=url;
	}
}

function orderfilter(p1,p2){
	 self.location.href="parametrage.php?tab=3&child=4&order="+p1+"&catconso="+p2;
	 return true
}
function displaymanager(p1){
	 self.location.href="parametrage.php?tab=3&child=4&ope=edit&id="+p1;
	 return true
}

function bouton_redirect(cible) {
	 self.location.href = cible;
}