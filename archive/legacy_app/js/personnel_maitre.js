function choisirM(pid, newmaitre, civilite) {
    cible="personnel_maitre.php?pid="+pid+"&maitre="+newmaitre+"&action=save&civilite="+civilite;
    self.location.href=cible;
	return;
}
