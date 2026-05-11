function orderfilter1(page,section,sub){
    self.location.href=page+"?filter="+section+"&subsections="+sub;
    return true
}
function orderfilter2(page,section,sub){
    if (sub.checked) s = 1;
    else s = 0;
    self.location.href=page+"?filter="+section+"&subsections="+s;
    return true
}