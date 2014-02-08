
var multUUIDtoDoList = []; //todo list for checking on entities
var annotationItems = []; //array of objects with item readiness to annotate, but not yet qued for submission

var prefixDomID_button = "act-bt-"; //prefix for buttons to submit a new annotation
var prefixDomID_predURI = "pred-uri-"; //prefix for dom elements with predicate uris
var prefixDomID_objURI = "obj-uri-"; //prefix for dom elements with object uris
var prefixDomID_predLabel = "pred-label-"; //prefix for dom elements with predicate labels
var prefixDomID_objLabel = "obj-label-"; //prefix for dom elements with object labels
var prefixDomID_predEntity = "pred-entity-"; //prefix for dom elements for descriptions of predicate entities
var prefixDomID_objEntity = "obj-entity-"; //prefix for dom elements for descriptions of object entities
var predixDomID_propAnno = "prop-anno-"; //prefix for dom elements for property annotations
var predixDomID_itemLabel = "item-label-"; //prefix for a subject item's label

var selectedVocabs = false; //limits search for entities by a vocabulary
var propertiesFound = false;
var properties = []; //array of property objects that are current
var submitAnnotationToDoList = []; //to do list for submitting item annotations

//to do object for checking on entities
function uuidToDo(uuid, subjectType, uri, uriEntityType, doneResult, entityChecked){
    var uuids = [];
    uuids.push(uuid);
    this.uuids = uuids;
    this.subjectType = subjectType;
    this.uri = uri;
    this.uriEntityType = uriEntityType;
    this.doneResult = doneResult;
    this.entityChecked = entityChecked;
}


//documents annotation status for items
function addUpdateItemStatus(uuid, subjectType, uri, uriEntityType){
    var uuidFound = false;
    for(var i = 0; i < annotationItems.length; i++){
        if(annotationItems[i].uuid == uuid){
            if(uriEntityType == "predicate"){
                annotationItems[i].predicateURI = uri;
            }
            else{
                annotationItems[i].objectURI = uri;
            }
            uuidFound = true;
            break;
        }
    }
    if(!uuidFound){
        if(uriEntityType == "predicate"){
            var newAnnotationItem = new itemAnnotationStatus(uuid, subjectType, uri, false);
        }
        else{
            var newAnnotationItem = new itemAnnotationStatus(uuid, subjectType, false, uri);
        }
        annotationItems.push(newAnnotationItem);
    }
    checkReadySubmissions();
}

//object to document if an item is ready to annotate, 
function itemAnnotationStatus(uuid, subjectType, predicateURI, objectURI){
    this.uuid = uuid;
    this.subjectType = subjectType;
    this.predicateURI = predicateURI;
    this.objectURI = objectURI;
}






//checks to see if item annotations are ready to submit
function checkReadySubmissions(){
    for(var i = 0; i < annotationItems.length; i++){
        var uuid = annotationItems[i].uuid;
        var subjectType = annotationItems[i].subjectType;
        var domID = prefixDomID_button + uuid;
        if(annotationItems[i].predicateURI != false &&  annotationItems[i].objectURI != false){
            document.getElementById(domID).removeAttribute("disabled");
            if(subjectType == "property"){
                var propSubmit = "javascript:itemAnnotate('" + uuid + "');";
                document.getElementById(domID).setAttribute("onclick", propSubmit);
            }
        }
        else{
            document.getElementById(domID).setAttribute("disabled", "disabled");
            if(subjectType == "property"){
                document.getElementById(domID).removeAttribute("onclick");
            }
        }
    }    
}

//verifies that a specific item is ready to annotate
function checkItemReady(uuid){
    var output = false;
    for(var i = 0; i < annotationItems.length; i++){
        if(uuid == annotationItems[i].uuid){
            if(annotationItems[i].predicateURI != false &&  annotationItems[i].objectURI != false){
                output = true;
            }
            break;
        }
    }
    return output;
}


//get the user-input entity URI from the input element associated with the uuid and uriEnityType
function getInputEntityURI(uuid, uriEntityType){
    var uri = false;
    if(uriEntityType == "object"){
        var domID = prefixDomID_objURI + uuid;
    }
    else if(uriEntityType == "predicate"){
        var domID = prefixDomID_predURI + uuid;
    }
    else{
       uri = false;
    }
    var uriDom = document.getElementById(domID);
    uri = uriDom.value;
    
    return uri;
}



//checks an entity URI if it is recognized by open context
function getEntityByType(uriEntityType, uuid, subjectType){
    
    var uri = getInputEntityURI(uuid, uriEntityType);
    
    multUUIDtoDoList = [];
    var toDoItem = new uuidToDo(uuid, subjectType, uri, uriEntityType, false, false);
    multUUIDtoDoList.push(toDoItem);
    processEntityToDoList();
}



function processEntityToDoList(){
    if(multUUIDtoDoList.length > 0){
        for(var i = 0; i < multUUIDtoDoList.length; i++){
            var toDoItem = multUUIDtoDoList[i];
            if(!toDoItem.entityChecked){
                //alert("not checked");
                getEntity(toDoItem.uri);
            }
            else{
                for(var j = 0; j < toDoItem.uuids.length; j++){
                    var uuid = toDoItem.uuids[j];
                    var result = toDoItem.doneResult;
                    if(toDoItem.uriEntityType == "object"){
                        var actDomID = prefixDomID_objEntity + uuid;
                        var actSmallLabelDom = prefixDomID_objLabel + uuid;
                        var URIdomID = prefixDomID_objURI + uuid;
                    }
                    else{ //predicate
                        var actDomID = prefixDomID_predEntity + uuid;
                        var actSmallLabelDom = prefixDomID_predLabel + uuid;
                        var URIdomID = prefixDomID_predURI + uuid;
                    }
                    
                    var uriDom = document.getElementById(URIdomID);
                    uriDom.value = toDoItem.uri;
                    
                    var smallLabelDom = document.getElementById(actSmallLabelDom);
                    
                    if(result != false){
                        //the entity is known to open context
                        smallLabelDom.innerHTML = result.label;
                        var outputMessage = "<h5>" + result.label + "</h5>";
                        if("vocabURI" in result){
                            outputMessage += "<p>Vocabulary: <br/>" + result.vocabURI +" (" + result.vocabLabel + ")</p>";
                        }
                        else{
                            outputMessage += "<p>Open Context Item: <br/>" + result.itemType;
                            outputMessage += "<a href=\""+ toDoItem.uri + "\">[Link]</a></p>";
                        }
                        
                        
                        addUpdateItemStatus(uuid, toDoItem.subjectType, toDoItem.uri, toDoItem.uriEntityType); //note the status update
                    }
                    else{ //the entity was not recognized by open context
                        
                        addUpdateItemStatus(uuid, toDoItem.subjectType, false, toDoItem.uriEntityType); //note the status update
                        smallLabelDom.innerHTML = "";
                        var outputMessage = "<h5>Add new entity (form on right)</h5>";
                        newEntityForm(toDoItem.uri);
                    }
                    
                    if(toDoItem.subjectType != "property"){
                        var actDom = document.getElementById(actDomID);
                        actDom.innerHTML = outputMessage;
                    }
                }
            }
        }
        
        var allDone = true;
        for(var i = 0; i < multUUIDtoDoList.length; i++){
            var toDoItem = multUUIDtoDoList[i];
            if(!toDoItem.entityChecked){
                allDone = false;
            }
        }
        
        if(allDone){
            multUUIDtoDoList = []; //reset the todo list when all done
        }
    }
}




//gets entities on a URI
function getEntity(uri){
    var rURI = "../../edit/get-entity";
    var myAjax = new Ajax.Request(rURI,
        {   method: 'get',
            parameters:
                {uri: uri
                },
        onComplete: getEntityDone }
    );    
}


function getEntityDone(response){
    
    var respData = JSON.parse(response.responseText);
    if(!respData.errors){
        var uri = respData.requestParams.uri;
        if(multUUIDtoDoList.length >0){
            for(var i = 0; i < multUUIDtoDoList.length; i++){
                if(multUUIDtoDoList[i].uri == uri){
                    multUUIDtoDoList[i].entityChecked = true;
                    multUUIDtoDoList[i].doneResult = respData.result;
                }
            }
        }
    }
    
    processEntityToDoList();
}






function newEntityForm(uri){
    var uriDom = document.getElementById('new-entity-uri');
    uriDom.value = uri;
   
    var formDom = document.getElementById('new-entity-form');
    formDom.setAttribute("class", "panel-collapse collapse in");
}


 
function searchEntities(){
    
    var searchLabelDom = document.getElementById("entity-lookup-label");
    var searchLabel = searchLabelDom.value;
    if(!selectedVocabs){
        var vocab = getCheckedRadio("vocabularies");
    }
    else{
        var vocab = selectedVocabs;
        selectedVocabs = false;
    }
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
        var outMessage = "<h4>Entities</h4>";
        outMessage += "<table class=\"table table-condensed table-striped table-hover\" style=\"width:95%; font-size:75%;\">";
        outMessage += "<thead><th>Pred.</th><th>Obj.</th><th>URI</th><th>Label</th></thead>";
        outMessage += "<tbody>";
        for(var i = 0; i < respData.result.length; i++){
            var actLabel = respData.result[i].label;
            var actURI = respData.result[i].uri;
            outMessage += "<tr>";
            outMessage += "<td><button onclick=\"javascript:selectEntity('" + actURI + "', 'predicate');\" title=\"Use as new predicate URI\" type=\"button\" class=\"btn btn-default btn-xs\">+</button></td>";
            outMessage += "<td><button onclick=\"javascript:selectEntity('" + actURI + "', 'object');\" title=\"Use as new object URI\" type=\"button\" class=\"btn btn-primary btn-xs\">+</button></td>";
            outMessage += "<td><a target=\"_bank\" href=\"" + actURI + "\">" + actURI + "</a></td><td>" + actLabel + "</td>";
            outMessage += "</tr>";
        }
        outMessage += "</tbody>";
        outMessage += "</table>";
        actDom.innerHTML = outMessage;
    }
}



function selectEntity(uri, uriEntityType){
    
    var doMainSubjectUUID = true;
    multUUIDtoDoList = [];
    
    if(propertiesFound){
        var applyEntities = getCheckedRadio("applyEntities");
        if(applyEntities != "subjectUUID"){
            
            doMainSubjectUUID = false;
            if(applyEntities == "selected-props"){
                var selectedPropUUIDs = getCheckedBoxes("propUUID"); 
            }
            else{
                var selectedPropUUIDs = [];
                for(var i = 0; i < propertyUUIDs.length; i++){
                    selectedPropUUIDs.push(propertyUUIDs[i].uuid);
                }
            }
            
            if(selectedPropUUIDs.length > 0){
                var subjectType = "property";
                for(var j = 0; j < selectedPropUUIDs.length; j++){
                    var uuid = selectedPropUUIDs[j];
                    var toDoItem = new uuidToDo(uuid, subjectType, uri, uriEntityType, false, false);
                    multUUIDtoDoList.push(toDoItem);
                }
            }
        }
    }
    
    
    if(doMainSubjectUUID){
        var subjectUUIDdom = document.getElementById('subjectUUID');
        var uuid = subjectUUIDdom.innerHTML;
        var subjectType = "subject";
        var toDoItem = new uuidToDo(uuid, subjectType, uri, uriEntityType, false, false);
        multUUIDtoDoList.push(toDoItem);
    }
    
    processEntityToDoList();
}

//posts label and vocabulary information on a new entity
function addEntity(){
    var uriDom = document.getElementById('new-entity-uri');
    var uri = uriDom.value;
    
    var labelDom = document.getElementById('new-entity-label');
    var label = labelDom.value;
    
    var altLabelDom = document.getElementById('new-entity-altLabel');
    var altLabel = altLabelDom.value;
    
    var vocabURIDom = document.getElementById('new-entity-vocabURI');
    var vocabURI = vocabURIDom.value;
    
    var entityType = getCheckedRadio("newEntityType");
    
    var rURI = "../../edit/add-entity";
   
    var myAjax = new Ajax.Request(rURI,
        {   method: 'post',
            parameters:
                {uri: uri,
                label: label,
                altLabel: altLabel,
                vocabURI: vocabURI,
                type: entityType
                },
        onComplete: addEntityDone }
    );  
    
}

function addEntityDone(response){
    var respData = JSON.parse(response.responseText);
    if(!respData.errors){
        
        var uriDom = document.getElementById('new-entity-uri');
        //uriDom.value = "";
        
        var labelDom = document.getElementById('new-entity-label');
        var label = labelDom.value;
        //labelDom.value = "";
        
        var altLabelDom = document.getElementById('new-entity-altLabel');
        //altLabelDom.value = "";
    
        var vocabURIDom = document.getElementById('new-entity-vocabURI');
        var vocabURI = vocabURIDom.value;
        //vocabURIDom.value = "";
        
        var searchLabelDom = document.getElementById("entity-lookup-label");
        searchLabelDom.value = label;
        selectedVocabs = vocabURI;
        
        searchEntities();
        var formDom = document.getElementById('new-entity-form');
        formDom.setAttribute("class", "panel-collapse collapse");
    }
}




function propertyItem(uuid, subjectType, projectUUID, sourceID, label){
    this.uuid = uuid;
    this.subjectType = subjectType;
    this.projectUUID = projectUUID;
    this.sourceID = sourceID;
    this.label = label;
}


//get list of properties (and their annotations) associated with an opencontext predicate item
function predicateProperties(){
    
    var searchTermDom = document.getElementById("search-prop-term");
    var searchTerm = searchTermDom.value;
    var predicateUUIDdom = document.getElementById("search-prop-predicateUUID");
    var predicateUUID = predicateUUIDdom.value
    var rURI = "../../edit/predicate-properties";
    var myAjax = new Ajax.Request(rURI,
        {   method: 'get',
            parameters:
                {predicateUUID: predicateUUID,
                q: searchTerm,
                getAnnotations: true
                },
        onComplete: predicatePropertiesDone }
    );    
}

function predicatePropertiesDone(response){
    var actDom = document.getElementById("properties");
    actDom.innerHTML = "";
    var respData = JSON.parse(response.responseText);
    if(respData.result != false){
        properties = []; //make this global an array
        propertiesFound = true; //so that we can call upon 
        var result = respData.result;
        var outMessage = "<h4>Properties used with this Predicate</h4>";
        outMessage += "<table class=\"table table-condensed table-striped table-hover\" style=\"width:100%; font-size:75%;\">";
        outMessage += "<thead><th style=\"width:15%;\">UUID</th><th style=\"width:20%;\">Label</th><th style=\"width:65%;\">Annotations</th></thead>";
        outMessage += "<tbody>";
        for(var i = 0; i < result.length; i++){
            
            var propItem = new propertyItem(result[i].uuid, result[i].itemType, result[i].projectUUID, result[i].sourceID, result[i].label);
            properties.push(propItem);
            
            var actLabel = result[i].label;
            var actURI = result[i].uri;
            outMessage += "<tr>";
            outMessage += "<td><a target=\"_bank\" href=\"" + actURI + "\">" + result[i].uuid + "</a></td>";
            outMessage += "<td id=\"" + predixDomID_itemLabel + result[i].uuid + "\">" + actLabel + "<br/>";
            outMessage += "<input type=\"checkbox\" name=\"propUUID\" value=\"" + result[i].uuid + "\" >" + "</td>";
            var annotations = result[i].annotations;
            var outAnnotations = outputSubAnnotations(result[i].uuid, annotations);
            outMessage += "<td id=\"" + predixDomID_propAnno + result[i].uuid + "\">" + outAnnotations + "</td>"; //predicate labels
            outMessage += "</tr>";
        }
        outMessage += "</tbody>";
        outMessage += "</table>";
        actDom.innerHTML = outMessage;
    }
    else{
        propertyUUIDs = []; //make this global an array that is empty
        propertiesFound = false; //so that we can call upon 
    }
}


function outputSubAnnotations(uuid, annotations){
    var outAnnotations = "<table class=\"table table-condensed\">";
    outAnnotations += "<thead>";
    outAnnotations += "<th>+/-</th>";
    outAnnotations += "<th>Predicate Label</th>";
    outAnnotations += "<th>Predicate URI</th>";
    outAnnotations += "<th>Object Label</th>";
    outAnnotations += "<th>Object URI</th>";
    outAnnotations += "</thead>";
    outAnnotations += "<tbody>";
    if(annotations != false){
        
    }
    
    outAnnotations += "<tr>";
    outAnnotations += "<td><button id=\""+ prefixDomID_button + uuid + "\" class=\"btn btn-primary btn-xs\" disabled=\"disabled\">+</button></td>";
    outAnnotations += "<td id=\""+ prefixDomID_predLabel + uuid + "\"></td>";
    outAnnotations += "<td><input style=\"font-size:65%;\" onchange=\"javascript:getEntityByType('predicate','" + uuid + "','property');\" class=\"form-control\" type=\"text\" name=\"predicateURI\" id=\"" + prefixDomID_predURI + uuid + "\"/></td>";
    
    outAnnotations += "<td id=\""+ prefixDomID_objLabel + uuid + "\"></td>";
    outAnnotations += "<td><input style=\"font-size:65%;\" onchange=\"javascript:getEntityByType('object','" + uuid + "','property');\" class=\"form-control\" type=\"text\" name=\"objectURI\" id=\"" + prefixDomID_objURI + uuid + "\"/></td>";
    
    outAnnotations += "</tr>";
    outAnnotations += "</tbody>";
    outAnnotations += "</table>";
    
    return outAnnotations;
}



function submitAnnotationItem(uuid, subjectType, projectUUID, sourceID, predicateURI, objectURI, completed){
    this.uuid = uuid;
    this.subjectType = subjectType;
    this.projectUUID = projectUUID;
    this.sourceID = sourceID;
    this.predicateURI = predicateURI;
    this.objectURI = objectURI;
    this.completed = completed;
}


function itemAnnotate(uuid){
    var annotationReady = checkItemReady(uuid);
    if(annotationReady){
        submitAnnotationToDoList = []; //annotation items actually qued for submission
        if(properties.length > 0){
            for(var i = 0; i < properties.length; i++){
                if(properties[i].uuid == uuid){
                    var predicateURI = getInputEntityURI(uuid, "predicate");
                    var objectURI = getInputEntityURI(uuid, "object");
                    var annoItem = new submitAnnotationItem(uuid, properties[i].subjectType, properties[i].projectUUID, properties[i].sourceID, predicateURI, objectURI, false);                    
                    submitAnnotationToDoList.push(annoItem); // add to the to do list for annotating items
                    break;
                }
            }
        }
        
        processSubmitAnnotationToDoList();
    }
}

function processSubmitAnnotationToDoList(){
    if(submitAnnotationToDoList.length > 0){
        
        alert("now here");
        var someToDo = false;
        for(var i = 0; i < submitAnnotationToDoList.length; i++){
            var submitItem = submitAnnotationToDoList[i];
            if(!submitItem.completed){
                someToDo = true;
                postItemAnnotation(submitItem.uuid, submitItem.subjectType, submitItem.projectUUID, submitItem.sourceID, submitItem.predicateURI, submitItem.objectURI); 
            }
        }
        
        if(!someToDo){
            itemAnnotationToDoList = []; //reset the to-do list
        }
    }
}

function postItemAnnotation(uuid, subjectType, projectUUID, sourceID, predicateURI, objectURI){
    var rURI = "../../edit/add-annotation";
    var myAjax = new Ajax.Request(rURI,
        {   method: 'post',
            parameters:
                {uuid: uuid,
                subjectType: subjectType,
                projectUUID: projectUUID,
                sourceID: sourceID,
                predicateURI: predicateURI,
                objectURI: objectURI,
                json: true,
                returnAnnotations: true
                },
        onComplete: postItemAnnotationDone }
    );
}


function postItemAnnotationDone(response){
    var respData = JSON.parse(response.responseText);
    var uuid = respData.requestParams.uuid;
    var subjectType = respData.requestParams.subjectType;
    addUpdateItemStatus(uuid, subjectType, false, "predicate"); //make the item predicate status false
    addUpdateItemStatus(uuid, subjectType, false, "object"); //make the item object status false
    
    for(var i = 0; i < submitAnnotationToDoList.length; i++){
        submitAnnotationToDoList[i].completed = true;
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

function getCheckedBoxes(boxName) {
    var checkBoxes = document.getElementsByName(boxName);
    var valueArray = [];
    for(var i = 0; i < checkBoxes.length; i++){
        if(checkBoxes[i].checked){
            valueArray.push(checkBoxes[i].value);
        }
    }
    
    return valueArray;
}





