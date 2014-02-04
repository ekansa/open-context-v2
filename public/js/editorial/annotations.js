



//gets annotations on a URI
function getAnnotations(){
    
    var uuid = document.getElementById('in-item-uuid');
    var rURI = "../edit/get-annotations";
    
    var myAjax = new Ajax.Request(rURI,
        {   method: 'get',
            parameters:
                {uuid: uuid
                },
        onComplete: getAnnotationsDone }
    );
    
}

//displays results on checking on new media
function getAnnotationsDone(response){
    var respData = JSON.parse(response.responseText);
    var i = 0;
    for (i=0; i< respData.length; i++){
        var fileType = respData[i].filetype;
        var actDomID = fileType + "-newStatus";
        var actDom = document.getElementById(actDomID);
        var bytes = respData[i].bytes;
        var outputMessage = "<button class=\"btn btn-danger btn-mini\">Not Found!</button>";
        if(bytes > 0){
            var outputMessage = "<button class=\"btn btn-success btn-mini\">" + respData[i].human + "</button>";
        }
        actDom.innerHTML = outputMessage;
    }
}    














function getCheckedRadio(radioName) {
    var radios = document.getElementsByName(radioName);
    var radioValue = false;
    for(var i = 0; i < radios.length; i++){
        if(radios[i].checked){
            radioValue = radios[i].value;
        }
    }
    return radioValue;
}







