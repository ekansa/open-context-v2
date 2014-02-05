



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


var entityType;
function getEntityByType(type){
    entityType = type;
    clearNewEntityForm("pred-newEntityForm");
    clearNewEntityForm("obj-newEntityForm");
    if(entityType == "object"){
        var uriDom = document.getElementById('add-obj-uri');
    }
    else{
        var uriDom = document.getElementById('add-pred-uri');
    }
    var uri = uriDom.value;
    getEntity(uri);
}

//gets entities on a URI
var activeEntityURI;
function getEntity(uri){
    activeEntityURI = uri;
    var rURI = "../../edit/get-entity";
    var myAjax = new Ajax.Request(rURI,
        {   method: 'get',
            parameters:
                {uri: uri
                },
        onComplete: getEntityDone }
    );    
}

//displays results on checking on a linked entity
function getEntityDone(response){
    var respData = JSON.parse(response.responseText);
    if(!respData.errors){
        var result = respData.result;
        if(entityType == "object"){
            var actDomID = "add-obj-entity";
            var actSmallLabelDom = "add-obj-label";
            var formRootDomID = "obj-newEntityForm";
        }
        else{
            var actDomID = "add-pred-entity";
            var actSmallLabelDom = "add-pred-label";
            var formRootDomID = "pred-newEntityForm";
        }
        var actDom = document.getElementById(actDomID);
        var smallLabelDom = document.getElementById(actSmallLabelDom);
        
        if(result != false){
            smallLabelDom.innerHTML = result.label;
            var outputMessage = "<h5>" + result.label + "</h5>";
            if("vocabURI" in result){
                outputMessage += "<p>Vocabulary: <br/>" + result.vocabURI +" (" + result.vocabLabel + ")</p>";
            }
            else{
                outputMessage += "<p>Open Context Item: <br/>" + result.itemType;
                outputMessage += "<a href=\""+ result.uri + "\">[Link]</a></p>";
            }
        }
        else{
           
            var outputMessage = "<h5>Entity new to Open Context</h5>";
            newEntityForm(formRootDomID, activeEntityURI);
        }
        actDom.innerHTML = outputMessage;
    }
}

//remove the new entity form
function clearNewEntityForm(formRootDomID){
    var actDom = document.getElementById(formRootDomID);
    actDom.innerHTML = "";
}


var activeNewEntityURI;
function newEntityForm(formRootDomID, uri){
    activeNewEntityURI = uri;
    var actDom = document.getElementById(formRootDomID);
    var formHTML = "<h5>Label, Vocabulary for: </h5>";
    formHTML += "<p><a target=\"_blank\" href=\"" + uri + "\">" + uri + "</a></p>";
    formHTML += "<br/>";
    formHTML += "Entity Label:<br/>";
    formHTML += "<input type=\"text\" id=\"new-entity-label\" value=\"\" class=\"form-control\" />";
    
    formHTML += "<br/>";
    formHTML += "Entity Alt-Label:<br/>";
    formHTML += "<input type=\"text\" id=\"new-entity-alt-label\" value=\"\" class=\"form-control\" />";
    
    formHTML += "<br/>";
    formHTML += "Entity Alt-Label:<br/>";
    formHTML += "<input type=\"text\" id=\"new-entity-alt-label\" value=\"\" class=\"form-control\" />";
    
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







