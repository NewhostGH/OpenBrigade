function redirect(url) {
     self.location.href=url;
}

function suppress(code) {
	if ( confirm("Voulez vous vraiment supprimer ce type de vehicule?") ) {
		url="del_type_vehicule.php?TV_CODE="+code;
		self.location.href=url;
	}
	else{
		url="upd_type_vehicule.php?TV_CODE="+code;
		self.location.href=url;
	}
}

function goback(operation,code) {
	if (operation == 'insert' ) {
		url="upd_type_vehicule.php?operation=insert";
	}
	else {
		url="upd_type_vehicule.php?TV_CODE=" + code;
	}
	self.location.href=url;
}

function hideRow(k) {
	document.getElementById('row_'+k).style.display = 'none';
}

function showRow(k) {
	document.getElementById('row_'+k).style.display = '';
}

function changeNbEquipage(n, m) {
	if ( n == 0 ) {
		hideRow(0);
	}
	else {
		showRow(0);
	}
 	for ( var i = 1; i <= m ; i++) {
		if ( i <= n ) {
			showRow(i);
		}
		else {
			hideRow(i);
		}
	}
}