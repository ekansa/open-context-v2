

var entityType;
function getEntityByType(type){
    entityType = type;
    
    if(entityType == "object"){
        clearNewEntityForm("pred-newEntityForm");
        clearNewEntityForm("obj-newEntityForm");
        var uriDom = document.getElementById('add-obj-uri');
    }
    else if(entityType == "predicate"){
        clearNewEntityForm("pred-newEntityForm");
        clearNewEntityForm("obj-newEntityForm");
        var uriDom = document.getElementById('add-pred-uri');
    }
    else{
        var uriDom = document.getElementById('new-entity-vocab-uri');
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
        else if(entityType == "predicate"){
            var actDomID = "add-pred-entity";
            var actSmallLabelDom = "add-pred-label";
            var formRootDomID = "pred-newEntityForm";
        }
        else{
            vocabEntity(result); //deal with the vocabulary entity
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
            smallLabelDom.innerHTML = "";
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
    formHTML += "Entity Vocabulary URI:<br/>";
    formHTML += "<input onchange=\"javascript:getEntityByType('vocabulary');\" type=\"text\" id=\"new-entity-vocab-uri\" value=\"\" class=\"form-control\" />";
    
    formHTML += "<br/>";
    formHTML += "Entity Vocabulary Label <span id=\"vocab-new-note\"></span>:<br/>";
    formHTML += "<input type=\"text\" id=\"new-entity-vocab-label\" value=\"\" class=\"form-control\" />";
    
    actDom.innerHTML = formHTML;
}


function vocabEntity(vocabResult){
    var newNoteDom = document.getElementById("vocab-new-note");
    var vocabLabelDom = document.getElementById("new-entity-vocab-label");
    if(vocabResult != false){
        newNoteDom.innerHTML = "[Used]";
        var vocabLabelDom = document.getElementById("new-entity-vocab-label");
        vocabLabelDom.value = vocabResult.label;
    }
    else{
        newNoteDom.innerHTML = "<strong>[NEW]<strong>";
        vocabLabelDom.value = "";
        vocabLabelDom.placeholder = "Add vocabulary label";
    }
}



function searchEntities(){
    
    var searchLabelDom = document.getElementById("entity-lookup-label");
    var searchLabel = searchLabelDom.value;
    var vocab = getCheckedRadio("vocabularies");
    
    var rURI = "../../edit/search-entities";
    var myAjax = new Ajax.Request(rURI,
        {   method: 'get',
            parameters:
                {q: searchLabel,
                vocabularies: vocab
                },
        onComplete: searchEntitiesDone }
    );    
}

function searchEntitiesDone(response){
    var actDom = document.getElementById("lookup-entities");
    actDom.innerHTML = "";
    var respData = JSON.parse(response.responseText);
    if(respData.result != false){
        var outMessage = "<ul>";
        for(var i = 0; i < respData.result.length; i++){
            var actLabel = respData.result[i].label;
            var actURI = respData.result[i].uri;
            outMessage += "<li>" + actURI + " <em>" + actLabel + "</em></li>";
        }
        outMessage += "</ul>";
        actDom.innerHTML = outMessage;
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







