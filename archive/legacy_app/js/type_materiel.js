
function change_type() {
 	var type_materiel = document.getElementById('TM_USAGE').value;
	var tt = document.getElementById('row_tt')
 	
 	if ( type_materiel == 'Habillement') {
		tt.style.display = '';
	}
	else {
		tt.style.display = 'none';	 	 
	}
}