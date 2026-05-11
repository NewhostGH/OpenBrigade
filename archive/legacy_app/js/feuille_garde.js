function changeFilter(p1,p2){
    url = "feuille_garde.php?filter="+p1+"&subsections="+p2;
    self.location.href = url ;
}
function changeFilter2(p1,p2){
    if (p2.checked) s=1
    else s=0
    url = "feuille_garde.php?filter="+p1+"&subsections="+s;
    self.location.href = url ;
}
