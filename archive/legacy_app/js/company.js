function orderfilter(p1,p2){
	 self.location.href="company.php?order="+p1+"&typecompany="+p2;
	 return true
}

function orderfilter(p1,p2,p3,p4){
	 self.location.href="company.php?order="+p1+"&filter="+p2+"&subsections="+p3+"&typecompany="+p4;
	 return true
}

function orderfilter2(p1,p2,p3,p4){
 	 if (p3.checked) s = 1;
 	 else s = 0;
	 self.location.href="company.php?order="+p1+"&filter="+p2+"&subsections="+s+"&typecompany="+p4;
	 return true
}


function displaymanager(p1){
	 self.location.href="upd_company.php?C_ID="+p1;
	 return true
}

function bouton_redirect(cible) {
	 self.location.href = cible;
}

function saveresponsable(p1,p2,p3){
	 self.location.href="upd_company_role.php?C_ID="+p1+"&TCR_CODE="+p2+"&P_ID="+p3;
	 return true
}