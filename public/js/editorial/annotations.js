
var annotationItems = []; //array of objects with item readiness to annotate
var activeUUID; //active item uuid (used when checking entity URIs)
var activeSubjectType; // active item subject type
var entityType; //type of URI being checked ('predicate' or 'object')

var prefixDomID_button = "act-bt-"; //prefix for buttons to submit a new annotation
var prefixDomID_predURI = "pred-uri-"; //prefix for dom elements with predicate uris
var prefixDomID_objURI = "obj-uri-"; //prefix for dom elements with object uris
var prefixDomID_predLabel = "pred-label-"; //prefix for dom elements with predicate labels
var prefixDomID_objLabel = "obj-label-"; //prefix for dom elements with object labels
var prefixDomID_predEntity = "pred-entity-"; //prefix for dom elements for descriptions of predicate entities
var prefixDomID_objEntity = "obj-entity-"; //prefix for dom elements for descriptions of object entities

var selectedVocabs = false; //limits search for entities by a vocabulary
var propertiesFound = false;
var propertyUUIDs; //array of property uuids that are current

var activeEntityURI; //last entity that was checked
var entityStatuses = []; //URI in the good entity list

//documents annotation status for items
function addUpdateItemStatus(uuid, subjectType, annoURI, annoType){
    var uuidFound = false;
    for(var i = 0; i < annotationItems.length; i++){
        if(annotationItems[i].uuid == uuid){
            if(annoType == "predicate"){
                annotationItems[i].predicateURI = annoURI;
            }
            else{
                annotationItems[i].objectURI = annoURI;
            }
            uuidFound = true;
        }
    }
    if(!uuidFound){
        if(annoType == "predicate"){
            var newAnnotationItem = new itemAnnotationStatus(uuid, subjectType, annoURI, false);
        }
        else{
            var newAnnotationItem = new itemAnnotationStatus(uuid, subjectType, false, annoURI);
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


//object for noting the status of an item
function entityStatus(uri, status, label){
    this.uri = uri; //true false
    this.status = status; //true false
    this.label = label;
}

//update the array of entity URI statuses
function addUpdateEntityStatus(uri, status, label){
    var uriFound = false;
    
    for(var i = 0; i < entityStatuses.length; i++){
        if(entityStatuses[i].uri == uri){
            entityStatuses[i].status = status;
            entityStatuses[i].label = label;
            uriFound = true;
        }
    }
    if(!uriFound){
        var newEntity = new entityStatus(uri, status, label);
        entityStatuses.push(newEntity);
    }
}

//get the label for an entity, if it has a good status
function getEntityLabel(uri){
    var label = false;
    for(var i = 0; i < entityStatuses.length; i++){
        if(entityStatuses[i].uri == uri){
            if(entityStatuses[i].status != false){
                label = entityStatuses[i].label;     
            }
        }
    }
    
    return label;
}



//checks to see if item annotations are ready to submit
function checkReadySubmissions(){
    for(var i = 0; i < annotationItems.length; i++){
        var uuid = annotationItems[i].uuid;
        var subjectType = annotationItems[i].subjectType;
        var domID = prefixDomID_button + uuid;
        if(annotationItems[i].predicateURI != false &&  annotationItems[i].objectURI != false){
            if(subjectType != "property"){
                document.getElementById(domID).removeAttribute("disabled");
            }
        }
        else{
            if(subjectType != "property"){
                document.getElementById(domID).setAttribute("disabled", "disabled");
            }
        }
    }    
}

//checks an entity URI if it is recognized by open context
function getEntityByType(type, uuid, subjectType){
    entityType = type;
    activeUUID = uuid;
    activeSubjectType = subjectType;
    if(entityType == "object"){
        var domID = prefixDomID_objURI + uuid;
    }
    else if(entityType == "predicate"){
        var domID = prefixDomID_predURI + uuid;
    }
    else{
       
    }
    var uriDom = document.getElementById(domID);
    var uri = uriDom.value;
    getEntity(uri);
}

//gets entities on a URI
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
    var output = false;
    var respData = JSON.parse(response.responseText);
    if(!respData.errors){
        var result = respData.result;
        if(entityType == "object"){
            var actDomID = prefixDomID_objEntity + activeUUID;
            var actSmallLabelDom = prefixDomIDobjLabel + activeUUID;
        }
        else if(entityType == "predicate"){
            var actDomID = prefixDomID_predEntity + activeUUID;
            var actSmallLabelDom = prefixDomID_predLabel + activeUUID;
        }
        else{
            vocabEntity(result); //deal with the vocabulary entity
        }
        
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
            
            addUpdateItemStatus(activeUUID, activeSubjectType, result.uri, entityType); //note the status update
            output = true;
            addUpdateEntityStatus(result.uri, true, result.label);
        }
        else{
            addUpdateItemStatus(activeUUID, activeSubjectType, false, entityType); //note the status update
            addUpdateEntityStatus(activeEntityURI, false, false);
            smallLabelDom.innerHTML = "";
            var outputMessage = "<h5>Add new entity (form on right)</h5>";
            newEntityForm(activeEntityURI);
        }
        
        if(activeSubjectType != "property"){
            var actDom = document.getElementById(actDomID);
            actDom.innerHTML = outputMessage;
        }
    }
    
    return output; //returns true only if the entity was found
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



function selectEntity(actURI, type){
    entityType = type;
    var uuids = []; //array of uuids to check
    var doMainSubjectUUID = true;
    
    if(propertiesFound){
        var applyEntities = getCheckedRadio("applyEntities");
        if(applyEntities != "subjectUUID"){
            doMainSubjectUUID = false;   
        }
    }
    
    if(doMainSubjectUUID){
        var subjectUUIDdom = document.getElementById('subjectUUID');
        var subjectUUID = subjectUUIDdom.innerHTML;
        uuids.push(subjectUUID);
    }
    
    for(var i = 0; i < uuids.length; i++){
        var uuid = uuids[i];
        activeUUID = uuid;
        if(entityType == "predicate"){
            var domID = prefixDomID_predURI + uuid;
            var domLabelID = prefixDomID_predLabel + uuid;
        }
        else{
            var domID = prefixDomID_objURI + uuid;
            var domLabelID = prefixDomID_objLabel + uuid;
        }
        var uriDom = document.getElementById(domID);
        var labelDom = document.getElementById(domLabelID);
        uriDom.value = actURI;
        if(i < 1){
            var outcome = getEntity(actURI);
            alert("done uris " + entityStatuses.length);
            var label = getEntityLabel(actURI);
        }
        if(label != false){
            labelDom.innerHTML = label;
        }
    }
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
        propertyUUIDs = []; //make this global an array
        propertiesFound = true; //so that we can call upon 
        var result = respData.result;
        var outMessage = "<h4>Properties used with this Predicate</h4>";
        outMessage += "<table class=\"table table-condensed table-striped table-hover\" style=\"width:95%; font-size:75%;\">";
        outMessage += "<thead><th>UUID</th><th>Label</th><th>Annotations</th></thead>";
        outMessage += "<tbody>";
        for(var i = 0; i < result.length; i++){
            propertyUUIDs.push(result[i].uuid);
            var actLabel = result[i].label;
            var actURI = result[i].uri;
            outMessage += "<tr>";
            outMessage += "<td><a target=\"_bank\" href=\"" + actURI + "\">" + result[i].uuid + "</a></td>";
            outMessage += "<td>" + actLabel + "</td>";
            var annotations = result[i].annotations;
            var outAnnotations = "";
            if(annotations != false){
                
            }
            outMessage += "<td>" + outAnnotations + "</td>"; //predicate labels
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







