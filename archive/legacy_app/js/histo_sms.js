function fermerfenetre(){
	var obj_window = window.open('', '_self');
	obj_window.opener = window;
	obj_window.focus();
	opener=self;
	self.close();
}

function redirect(type, sms_account, debut, fin, order) {
	 url = "histo_sms.php?type="+type+"&sms_account="+sms_account+"&dtdb="+debut+"&dtfn="+fin+"&order="+order;
	 self.location.href = url;
}