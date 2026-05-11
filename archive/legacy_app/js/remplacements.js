function changeParam(section) {
    status=document.getElementById('status').value;
    replaced=document.getElementById('replaced').value;
    substitute=document.getElementById('substitute').value; 
    debut=document.getElementById('dtdb').value;
    fin=document.getElementById('dtfn').value;
	url = "remplacements.php?status="+status+"&filter="+section+"&dtdb="+debut+"&dtfn="+fin+"&replaced="+replaced+"&substitute="+substitute;
	self.location.href = url;
}