var httpObject = null;
var httpObject2 = null;
var link = "";
var timerID = 0;

// Get the HTTP Object
function getHTTPObject(){
    if (window.ActiveXObject) return new ActiveXObject("Microsoft.XMLHTTP");
    else if (window.XMLHttpRequest) return new XMLHttpRequest();
    else {
        swalAlert("Votre browser ne supporte pas AJAX.");
        return null;
    }
}   

// Change the value of the outputText field
function setOutput(){
    if(httpObject.readyState == 4){
        var response = httpObject.responseText;
        var objDiv = document.getElementById("result");
        objDiv.innerHTML += response;
        objDiv.scrollTop = objDiv.scrollHeight;
        var inpObj = document.getElementById("msg");
        inpObj.value = "";
        inpObj.focus();
    }
}

// Change the value of the outputText field
function setAll(){
    if(httpObject.readyState == 4){
        var response = httpObject.responseText;
        var objDiv = document.getElementById("result");
        objDiv.innerHTML = response;
        objDiv.scrollTop = objDiv.scrollHeight;
    }
}

function setUsers(){
     if(httpObject2.readyState == 4){
        var response = httpObject2.responseText;
        var objDiv = document.getElementById("users");
        objDiv.innerHTML = response;
        objDiv.scrollTop = objDiv.scrollHeight;
      
     }
}

// Implement business logic
function doWork(){
    httpObject = getHTTPObject();
    if (httpObject != null) {
         if ( document.getElementById('msg').value.length > 0  ) {
            link = "chat_message.php?msg="+document.getElementById('msg').value;
            httpObject.open("GET", link , true);
            httpObject.onreadystatechange = setOutput;
            httpObject.send(null);
             document.getElementById('msg').value='';
        }
        else document.getElementById('msg').focus;
    }
}

// Implement business logic
function doReload(){
    httpObject = getHTTPObject();
    if (httpObject != null) {
        link = "chat_message.php?all=1";
        httpObject.open("GET", link , true);
        httpObject.onreadystatechange = setAll;
        httpObject.send(null);
    }
}

function getUsersList(){
    httpObject2 = getHTTPObject();
    if (httpObject2 != null) {
        link = "chat_message.php?users=1";
        httpObject2.open("GET", link , true);
        httpObject2.onreadystatechange = setUsers;
        httpObject2.send(null);
    }
}

function UpdateTimer() {
    doReload();
    getUsersList();
    timerID = setTimeout("UpdateTimer()", 5000);
}
    
function keypressed(e){
    if(e.keyCode=='13'){
        doWork();
    }
}