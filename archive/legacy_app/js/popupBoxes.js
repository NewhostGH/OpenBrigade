function HideContent(d) {
if(d.length < 1) { return; }
document.getElementById(d).style.display = "none";
}

function ReverseContentDisplay(d) {
if(d.length < 1) { return; }
var dd = document.getElementById(d);
AssignPosition(dd, 0);
if(dd.style.display == "none") { dd.style.display = "block"; }
else { dd.style.display = "none"; }
}

function ReverseContentDisplay2(d) {
var additionalY = 120;
if(d.length < 1) { return; }
var dd = document.getElementById(d);
AssignPosition(dd, additionalY);
if(dd.style.display == "none") { dd.style.display = "block"; }
else { dd.style.display = "none"; }
}

var cX = 0; var cY = 0; var rX = 0; var rY = 0;
function UpdateCursorPosition(e){ cX = e.pageX; cY = e.pageY;}
function UpdateCursorPositionDocAll(e){ cX = event.clientX; cY = event.clientY;}
if(document.all) { document.onmousemove = UpdateCursorPositionDocAll; }
else { document.onmousemove = UpdateCursorPosition; }

function AssignPosition(d,additionalY) {
if(self.pageYOffset) {
    rX = self.pageXOffset;
    rY = self.pageYOffset;
    }
else if(document.documentElement && document.documentElement.scrollTop) {
    rX = document.documentElement.scrollLeft;
    rY = document.documentElement.scrollTop;
    }
else if(document.body) {
    rX = document.body.scrollLeft;
    rY = document.body.scrollTop;
    }
if(document.all) {
    cX += rX; 
    cY += rY;
    }
d.style.left = (cX+10) + "px";
d.style.top = (cY-180+additionalY) + "px";
}

function changeSectionOrder(p,t){
    url=p+"?sectionorder="+t;
    self.location.href=url;
    return true
}

function CompterChar(Target, max, nomchamp) {
    StrLen = Target.value.length
    if (StrLen > max ) {
        Target.value = Target.value.substring(0,max);
        CharsLeft = max;
    }
    else
    {
        CharsLeft = StrLen;
    }    
    nomchamp.value = CharsLeft;
}