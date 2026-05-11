function orderfilter(p1,p2,p3,p4,p5,p6,p7,p8){
    self.location.href="export.php?filter="+p1+"&subsections="+p2+"&exp="+p3+"&dtdb="+p4+"&dtfn="+p5+"&yearreport="+p6+"&type_event="+p7+"&competence="+p8;
    return true
}
function orderfilter2(p1,p2,p3,p4,p5,p6,p7,p8){
    if (p2.checked) s = 1;
    else s = 0;
    self.location.href="export.php?filter="+p1+"&subsections="+s+"&exp="+p3+"&dtdb="+p4+"&dtfn="+p5+"&yearreport="+p6+"&type_event="+p7+"&competence="+p8;
    return true
}

function impression()
{
    this.print();
}
function showdates(reportid,divid) {
    var obj = document.getElementById(divid);
    var status = reportid.substring(0,1);
    if (status == '1' ) obj.style.display = ''
    else obj.style.display = 'none';
    if ( reportid == '1point' ) {
        document.frmExport.dtdb.value='".$dateJ."';
        document.frmExport.dtfn.value='".$dateJ."';
    }
}


$(document).ready(function() 
    {
        $("#exportTable").tablesorter({
            dateFormat: 'dd-mm-yyyy'
        });
    }
);
