

var map_clean;
var coorSetCenter_clean = { lat: -2.44565, lng: 117.8888};
var coordMapType_clean;
var kmlLayer;
var myParser;
var filePolygon={};
var filePolygonHide={};
var AdvancedMarkerElement;
var winUpdate;
var loader;
(g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
    key: "AIzaSyAMZbc5nDMusMaE8HhXmCT-ICwwHZmX9us",
    v: "weekly"
});
async function initMapLib() {
    
}
initMapLib();
/**
 * @license
 * Copyright 2019 Google LLC. All Rights Reserved.
 * SPDX-License-Identifier: Apache-2.0
 */
function newUploadFile(){
    tujuan= $.options.slave+"?switcher=view";
    let options = {
        url: tujuan,
	    title:'Add File',
        success :function(arg){
            loadMap(null);
            dragOverFile();
        } 
    };
	winUpdate = $.openWindow(options);
    loader = $.loader(winUpdate.target.panel);
}
var addScript = async src => new Promise((resolve, reject) => {
    const el = document.createElement('script');
    el.src = src;
    el.addEventListener('load', resolve);
    el.addEventListener('error', reject);
    document.head.append(el);
}); 

function loadMap(arg){
    loader.on();
    mapview = document.querySelectorAll('[show]');
    src = {};
    if(mapview.length > 0){
        for(let i=0; i<mapview.length; i++){
            src[mapview[i].getAttribute('id')] = mapview[i].getAttribute('show');
        }
    }
    async function initMapClean(result) {
        const { Map } = await google.maps.importLibrary("maps");
        const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
        map_clean = new Map(document.getElementById("map_clean"), {
            center: coorSetCenter_clean,
            disableDefaultUI : true,
            zoom: 6,
            mapId :'8f0acee7725e9c9f',
            // mapId :'7abc3de8b1ee5325',
        });
        
        coordMapType_clean = new CoordMapType(new google.maps.Size(256, 256));
        map_clean.overlayMapTypes.insertAt(0, coordMapType_clean);
        result(src);
    }

    initMapClean(source=>{
        google.maps.event.addListener(map_clean, 'zoom_changed', function() {
            styleFeature();
        });
        google.maps.event.addListener(map_clean.data, 'rightclick', function(event) {
            if(actions = event.feature.getProperty("list-action")){
                if(Object.keys(actions).length>0){
                    $.menuActionList(actions,$,event.feature.getProperty("name"),event);
                };
            }
        });
        google.maps.event.addListener(map_clean.data, 'click', function(event) {
            $.Alert(event.feature.getProperty("description"));
        });
        if(arg!=null){
            Object.keys(source).forEach((key, num) => {
                $.get(mapview[0],$.options.slave+"?switcher=openxml&path="+source[key],function(ev){
                    try{
                        // console.log(ev.response);
                        addGeoJsonStyle(ev.response,key);
                    }catch(e){
                        console.log(e);
                    }
                });
            });
        }else{
            loader.off();
        }
    });
}
function testFunction(){
    console.log(arguments);
}
function tableToData(){
    
}
function publish_geojson(){
    getGeoJson(map_clean,function(contents){
        let collectionsId = document.getElementById('toolsmaps').getAttribute('data-id');
        let blob = new Blob([JSON.stringify(contents)], { type: 'application/json' });
        let file = new File([blob],collectionsId+"_temp.geojson", {type: "application/json"});
        eleform = new FormData();
        eleform.append('id',collectionsId);
        eleform.append('file_temp',file,file.name);
        $.post(eleform,$.options.slave+"?switcher=geojsontemp&id="+collectionsId,function(ev){
            try{
                winUpdate.close();
                $.refresh();
            }catch(e){
                console.log(e);
            }
        });

    });
}
function updateIcon(name,icon,collection_id,layer=0){
    if(layer != 0){
        filePolygon[collection_id].forEach(function(feature){
            if(feature.getProperty('style-name') == layer){
                feature.setProperty(name,icon);
            }
        });
    }else{
        filePolygon[collection_id].forEach(function(feature){
            feature.setProperty(name,icon);
        });
    }
}
function getBlok(id){
    //get-blok
    tujuan1= $.options.slave+"?switcher=get-blok";
    $.get(document.getElementById('collection_'+id),$.options.slave+"?switcher=get-blok",function(ev){
        try{
            let dataArr = ev.response;
            console.log(dataArr);
            Object.keys(dataArr).forEach((key, num) => {
                document.getElementById('dataname_'+id)[num] = new Option(dataArr[key],key);
            });
        }catch(e){
            console.log(e);
        }
    });
}
function setValTo(el,name){
    id = name.split('_')[1];
    nameAlias = "name_"+id;
    document.getElementById(nameAlias).value = el.getAttribute('value');
    let forData = new FormData();
    forData.append('id',id);
    forData.append('name',el.getAttribute('value'));
    $.post(forData,$.options.slave+"?switcher=set-name",function(ev){
        if(ev.response.error === false){
        }else{
            $.Alert(ev.response.message);
        }
    });
}
function setIcon(val,id,num=""){
    if(num == ""){
        num =id;
    }
    if(val=='pokok' || val=='tph' || val=='kantor' || val=='point'){
        document.getElementById('stroke_color'+id).style.display = "none";
        document.getElementById('stroke_color_val'+id).innerHTML = "";
        document.getElementById('fill_color'+id).style.display = "none";
        document.getElementById('fill_color_val'+id).innerHTML = "";
        document.getElementById('marker_name'+id).style.display = null;
        document.getElementById('marker_name_val'+id).innerHTML = "";
        tujuan= $.options.slave+"?switcher=marker-name&key="+val;
            $.get(document.getElementById('collection_'+num),tujuan,function(ev){
                try{
                    let dataArr = ev.response;
                    Object.keys(dataArr).forEach((key, num) => {
                        document.getElementById('marker_name_val'+id)[num] = new Option(dataArr[key],key);
                    });
                }catch(e){
                    console.log(e);
                }
            });
            document.getElementById('marker_name_val'+id).onchange = function(e){addEventChangeStyle('collection_'+id,val,e)};
            
    }else if(val=='jalan' || val=='sungai' || val=='line'){
        document.getElementById('marker_name'+id).style.display   = "none";
        document.getElementById('marker_name_val'+id).innerHTML   = "";
        document.getElementById('fill_color'+id).style.display    = "none";
        document.getElementById('fill_color_val'+id).innerHTML    = "";

        document.getElementById('stroke_color'+id).style.display  = null;
        document.getElementById('stroke_color_val'+id).innerHTML  = "";
        $.get(document.getElementById('collection_'+num),$.options.slave+"?switcher=stroke-color&key="+val,function(ev){
            try{
                let dataArr = ev.response;
                Object.keys(dataArr).forEach((key, num) => {
                    document.getElementById('stroke_color_val'+id)[num] = new Option(dataArr[key],key);
                });
            }catch(e){
                console.log(e);
            }
        });
        document.getElementById('stroke_color_val'+id).onchange = function(e){addEventChangeStyle('collection_'+id,val,e)};

    }else{
        if(val=='blok'){
            getBlok(id);
        }
        document.getElementById('marker_name'+id).style.display   = 'none';
        document.getElementById('marker_name_val'+id).innerHTML   = "";

        document.getElementById('stroke_color'+id).style.display  = null;
        document.getElementById('stroke_color_val'+id).innerHTML  = "";
        document.getElementById('fill_color'+id).style.display    = null;
        document.getElementById('fill_color_val'+id).innerHTML    = "";
        
        $.get(document.getElementById('collection_'+num),$.options.slave+"?switcher=stroke-color&key="+val,function(ev){
            try{
                let dataArr = ev.response;
                Object.keys(dataArr).forEach((key, num) => {
                    document.getElementById('stroke_color_val'+id)[num] = new Option(dataArr[key],key);
                });
            }catch(e){
                console.log(e);
            }
        });
        document.getElementById('stroke_color_val'+id).onchange = function(e){addEventChangeStyle('collection_'+id,val,e)};
        $.get(document.getElementById('collection_'+num),$.options.slave+"?switcher=fill-color&key="+val,function(ev){
            try{
                let dataArr = ev.response;
                Object.keys(dataArr).forEach((key, num) => {
                    document.getElementById('fill_color_val'+id)[num] = new Option(dataArr[key],key);
                });
            }catch(e){
                console.log(e);
            }
        });
        document.getElementById('fill_color_val'+id).onchange = function(e){addEventChangeStyle('collection_'+id,val,e)};

    }
}

function dragOverFile(){
	let dropContainer = document.getElementById("dropcontainer")
	let fileInput = document.getElementById("fileupload")
	dropContainer.addEventListener("dragover", (e) => {
		// prevent default to allow drop
		e.preventDefault()
	}, false)
	dropContainer.addEventListener("dragenter", () => {
		dropContainer.classList.add("drag-active")
	})

	dropContainer.addEventListener("dragleave", () => {
		dropContainer.classList.remove("drag-active")
	})
	dropContainer.addEventListener("drop", (e) => {
		e.preventDefault()
		dropContainer.classList.remove("drag-active")
		fileInput.files = e.dataTransfer.files
	})
}
function viewAction(getPage){
    tujuan= $.options.slave+getPage;
    let options = {
        url: tujuan,
	    title:'View File',
        success :function(arg){
            loadMap(arg);
            if(document.getElementById("dropcontainer")){
                dragOverFile();
            }
        } 
    };
	winUpdate = $.openWindow(options);
    loader = $.loader(winUpdate.target.panel);
}
function openUpload_form(id=0){
    tujuan1= $.options.slave+"?switcher=upload_form&layer="+id;
    winUpload = $.newDialog('uploadfile','Upload File',tujuan1,450,400);
}
function resetPreview(ele){
    var utils = $.Utils();
    form = utils.getParentByTagName(ele,'FORM');
    let uri = form.getAttribute('action');
    var Url = new URL(uri);
    if(Url.searchParams.has('for') == false){
        Url.searchParams.append('for','read');
    }else{
        Url.searchParams.set('for','read');
    }
    form.setAttribute('action',Url);
    form.setAttribute('callback','showFileGeojson');
    button = form.submit;
    button.value = "Preview";
    // console.log(map_clean.data);
    // if(Object.keys(filePolygon).length > 0){
        loadMap(null);
        filePolygon = {};
    // }
    $.scanFormTag(winUpdate.target.body);
    
}   
function afterUpload(ev){
    // console.log(ev);
    if(ev.response.error === false){
        $.Alert("DONE");
        winUpdate.close();
        $.refresh();
    }else{
        $.Alert(ev.response.message);
    }
}
function afterUploadLayer(ev){
    if(ev.response.error === false){
        $.Alert("DONE");
        winUpload.close();
        winUpdate.refresh();
    }else{
        $.Alert(ev.response.message);
    }
    
}

function showFileGeojson(arg,event){
    event.stopPropagation();
    file = arg.response;
    console.log(file);
    if(typeof file != 'object'){
        try{
            file = JSON.parse(file);
        }catch(e){
            console.log(e);
        }
    }
    addGeoJsonStyle(file);
    if(arg.element.tagName.toLowerCase() == 'form'){
        let uri = arg.element.getAttribute('action');
        var Url = new URL(uri);
        if(Url.searchParams.has('for') == false){
            Url.searchParams.append('for','upload');
        }else{
            Url.searchParams.set('for','upload');
        }
        arg.element.setAttribute('action',Url);
        arg.element.setAttribute('callback','afterUpload');
        button = arg.element.submit;
        button.value = "Upload";
        $.scanFormTag(winUpdate.target.body);

    }
}
function showLayerGeojson(arg,event){
    event.stopPropagation();
    file = arg.response;
    console.log(file);
    if(typeof file != 'object'){
        try{
            file = JSON.parse(file);
        }catch(e){
            console.log(e);
        }
    }
    addGeoJsonStyle(file);
    if(arg.element.tagName.toLowerCase() == 'form'){
        let uri = arg.element.getAttribute('action');
        var Url = new URL(uri);
        if(Url.searchParams.has('for') == false){
            Url.searchParams.append('for','upload');
        }else{
            Url.searchParams.set('for','upload');
        }
        arg.element.setAttribute('action',Url);
        arg.element.setAttribute('callback','afterUploadLayer');
        button = arg.element.submit;
        button.value = "Upload";
        $.scanFormTag(winUpdate.target.body);
    }
}

function styleFeature(){
    var zoom = map_clean.getZoom();
    var relativePixelSize = Math.round(zoom/22*15); 
    var size2 = new google.maps.Size(relativePixelSize, relativePixelSize);
    map_clean.data.setStyle((feature)=>{
        if((feature.getGeometry().getType() === 'Point' || feature.getGeometry().getType() === 'MultiPoint')){
            if(feature.getGeometry().getType() === 'MultiPoint'){
                return {icon:{
                    url:((!feature.getProperty('marker-name'))?iconsDef['circle'].icon:iconsDef[feature.getProperty('marker-name')].icon),
                    origin: new google.maps.Point(0,0),
                    anchor: new google.maps.Point(7.5,7.5),
                    size: size2,
                    scaledSize: size2
                }};
            }else{
                // new google.maps.Circle({
                //     map: map_clean,
                //     center: feature.getGeometry().get(),
                //     radius: 2,
                //     fillColor: '#f00',
                //     fillOpacity: 0.8,
                //     strokeWeight: 0,
                // });
            }
        }else{
            return  {
                fillColor: ((!feature.getProperty('fill-color'))?polygonDef.primary.fillColor:feature.getProperty('fill-color')),
                fillOpacity: ((!feature.getProperty('fill-opacity'))?polygonDef.primary.fillOpacity:feature.getProperty('fill-opacity')),
                strokeWeight: ((!feature.getProperty('stroke-width'))?polygonDef.primary.strokeWeight:feature.getProperty('stroke-width')),
                strokeColor: ((!feature.getProperty('stroke-color'))?polygonDef.primary.strokeColor:feature.getProperty('stroke-color')),
                strokeOpacity: ((!feature.getProperty('stroke-opacity'))?polygonDef.primary.strokeOpacity:feature.getProperty('stroke-opacity'))
            };
        }
    });
}
function addMenuChild(e){
    console.log(e);
}

function toggleComponen(el,stylekey,key){
    loader.on();
    el.style.display = "none";
    Both = document.getElementById(key+stylekey);
    bodyBoth = Both.querySelector('.body-frame');
    async function hideComp(res){
        map_clean.data.forEach((feature) =>{
            if(feature.getProperty('style-name') == stylekey){
                filePolygonHide[stylekey].push(feature);
                map_clean.data.remove(feature);
            }
        });
        bodyBoth.style.display = "none";
        res(el);
    }
    async function showComp(res){
        if(typeof filePolygonHide[stylekey] != 'undefined'){
            filePolygonHide[stylekey].forEach((feature) =>{
                map_clean.data.add(feature);
            });
            bodyBoth.style.display = null;
        }
        res(el);
    }
    if(el.checked == false){
        filePolygonHide[stylekey] = new Array();
        hideComp(el=>{
            el.style.display = null;
            loader.off();
        });
    }else{
        showComp(el=>{
            el.style.display = null;
            filePolygonHide[stylekey] = null;
            loader.off();
        });
    }
}
function getFeature(prop,valprop,type='property'){
    var data = new Array();
    new Promise((result)=>{
        map_clean.data.forEach((feature) =>{
            if(type == 'description'){
                Object.keys(feature.getProperty("description")).forEach((key,n)=>{
                    if(key == prop && feature.getProperty("description")[key] == valprop){
                        data.push(feature);
                    }
                });
            }else{
                if(feature.getProperty(prop) == valprop){
                    data.push(feature);
                }
            }
        });
        result(data);
    });
    return data;
}
function showPolyIndextop(el,prop,valprop){
    listFeatureGroup = getFeature(prop,valprop,'description');
    // console.log(getFeature(prop,valprop,'description'));

}
var detailDescription;
function openDetail(btn,childData,keyChild){
    // let btn = event.target;
    var ul = document.createElement('ul');
    if(typeof childData != 'undefined'){
        if(document.getElementById(btn.parentNode.parentNode.id+keyChild)){
            ul = document.getElementById(btn.parentNode.parentNode.id+keyChild);
            ul.innerHTML = "";
        }else{
            ul.id = btn.parentNode.parentNode.id+keyChild;
        }
        if(!btn.querySelector('i').classList.contains('fa-folder-open')){
            btn.querySelector('i').classList.remove('fa-folder');
            btn.querySelector('i').classList.add('fa-folder-open');
            ul.style.display=null;
        }else{
            btn.querySelector('i').classList.remove('fa-folder-open');
            btn.querySelector('i').classList.add('fa-folder');
            ul.style.display="none";

        }
        btn.appendChild(ul);
        Object.keys(childData).forEach((key,num)=>{
            li = createList("<i class='fa fa-file-o'></i> "+childData[key]+"",ul);
            li.addEventListener("click",function(e){
                showPolyIndextop(this,keyChild,childData[key]);
            });
        });
    }else{
        let stylekey = btn.getAttribute('stylekey');
        if(document.getElementById("detailstyle_"+stylekey)){
            document.getElementById("detailstyle_"+stylekey).remove();
        }else{
            ul.id = "detailstyle_"+stylekey;
            data = getFeature('style-name',stylekey);
            row = document.createElement('div');
            row.classList.add('row');
            btn.parentNode.appendChild(row);
            var featureGroup = {};  
            new Promise((result)=>{
                data.forEach((feature,num)=>{
                    Object.keys(feature.getProperty("description")).forEach((key,n)=>{
                        if(key != 'FID'){
                            if(typeof featureGroup[key] == 'undefined'){
                                featureGroup[key] = new Array();
                            }
                            if(typeof featureGroup[key] != 'undefined' && feature.getProperty("description")[key] != "" && !featureGroup[key].includes(feature.getProperty("description")[key])){
                                featureGroup[key].push(feature.getProperty("description")[key]);
                            }
                        }
                    });

                });
                result(featureGroup);
            }).then((result)=>{
                Object.keys(result).forEach((key,n)=>{
                    li = createList("<i class='fa fa-folder'></i> "+key,ul);
                    li.addEventListener("click",function(e){
                        openDetail(this,result[key],key);
                    });
                });
            }); 
           
        }
    }
    return ul;
}
function createList(data,ul){
    li = document.createElement('li');
    li.style.cursor="pointer";
    a = document.createElement('a');
    a.innerHTML = data;
    li.appendChild(a);
    ul.appendChild(li);
    return a;
}
function createLayerByStyle(key,stylekeyArr,idxLayer=0){
    if(typeof stylekeyArr[idxLayer] != 'undefined'){
        new Promise ((resolve, idxId) => {
        newEl = document.getElementById(key).cloneNode(true);
        newEl.querySelector('div.title').classList.remove('bg-red');
        newEl.querySelector('div.title').classList.add('bg-green');
        newEl.querySelector('div.title span').innerHTML = stylekeyArr[idxLayer];
        ckBox = document.createElement('input');
        ckBox.type = 'checkbox';
        ckBox.checked = 'true';
        ckBox.style.float = 'right';
        ckBox.addEventListener("click",function(e){
            toggleComponen(e.target,stylekeyArr[idxLayer],key);
        });
        newEl.querySelector('div.title').appendChild(ckBox);
        newEl.setAttribute("delete","removeFile('"+stylekeyArr[idxLayer]+"')");
        newEl.setAttribute('id',key+stylekeyArr[idxLayer]);
        newEl.setAttribute('parentid',key);
        num = key.split("_")[1];
        newEl.querySelector('table').style.marginBottom = "10px";

        let button = document.createElement('button');
        button.classList.add("mybutton");
        button.setAttribute('stylekey',stylekeyArr[idxLayer]);
        button.innerHTML = "Data List";
        button.onclick = function(e){
            if(detailDescription){
                try{
                    detailDescription.close();
                }catch(e){
                    detailDescription = undefined;
                }
            }
            detailDescription = $.createNewPage("Description","detailDescription",false,{window:'right',width:300});
            detailDescription.body.classList.add('mmgrchart');
            detailDescription.body.appendChild(openDetail(this));
            $.scanningElement(detailDescription.body);
        }
        // let button2 = document.createElement('button');
        // button2.classList.add("mybutton");
        // button2.innerHTML = "Save";
        // button2.onclick = function(e){
        //     $.Alert("Function Not Ready to Use");
        // }
        body = newEl.querySelector('div.body-frame');
        body.appendChild(button);
        // body.appendChild(button2);
        document.getElementById(key).parentNode.appendChild(newEl);
        resolve({'key':key,'stylekeyArr':stylekeyArr,'newEl': newEl,'stylekey':stylekeyArr[idxLayer],'num':num,'idx':idxLayer});
    }).then((data)=>{
        let typeL = undefined;
        let elChild = data.newEl.querySelectorAll('[id]');
        for(let i=0; i<elChild.length; i++){
            elId = elChild[i].getAttribute('id')+data.stylekey;
            elChild[i].setAttribute('parentid',data.key);
            elChild[i].setAttribute('layer',data.stylekey);
            elChild[i].setAttribute('idx',(i+1));
            elChild[i].setAttribute('id',elId);
            if(elChild[i].getAttribute('id') == "type_"+data.num+data.stylekey){
                elChild[i].onchange = function(e){
                    // console.log(this.value,data.num+data.stylekey,data.num);
                    setIcon(this.value,data.num+data.stylekey,data.num);
                };
                typeL = elChild[i];
            }else if(elChild[i].classList.contains('properties')){
                opt = elChild[i].options[elChild[i].selectedIndex];
                if(typeL != undefined){
                    elChild[i].onchange = function(e){
                        // updateIcon(elChild[i].name,opt.value,key,data.stylekey);
                        addEventChangeStyle(key+data.stylekey,elChild[i].getAttribute('id')+data.stylekey.options[elChild[i].getAttribute('id')+data.stylekey.selectedIndex].value,e);
                    };
                }
            }else{
                if(elChild[i].getAttribute('name') == 'name'){
                    elChild[i].value = data.stylekey;
                    elChild[i].setAttribute('value',data.stylekey);
                }
            }
        }
        createLayerByStyle(key,stylekeyArr,(data.idx+1));
    });
        
    }else{
        return false;
    }
}

function addGeoJsonStyle(file=null,key='collection_temp'){
    // map_clean.data.setStyle(styleFeature);
    loader.off();
    styleFeature();
    if(file !== null){
        filePolygon[key] = map_clean.data.addGeoJson(file);
        if(key!=='collection_temp'){
            if(typeof file.styleUrl != 'undefined'){
                if(Object.keys(file.styleUrl).length>0){
                    btnLayer = document.getElementById('style_'+key);
                    btnLayer.onclick = function(e){
                        if(btnLayer.getAttribute('open') == 'true'){
                            btnLayer.setAttribute('open','false');
                            listLayer = document.querySelectorAll('div[parentid="collection_46"]');
                            listLayer.forEach((key,idx)=>{
                                key.remove();
                                // console.log(key);
                            });
                        }else{
                            btnLayer.setAttribute('open','true');
                            let id = key.split('_')[1];
                            let tujuan= $.options.slave+"?switcher=getproperties&id="+id+"&styleid=true";
                            $.get(btnLayer,tujuan,function callback(Result){
                                console.log(Result);
                                createLayerByStyle(key,Object.keys(file.styleUrl));

                            });
                        }
                    }
                }
            }else{
                btnLayer = document.getElementById('style_'+key);
                btnLayer.onclick = function(e){
                    $.Alert('File Belum tersedia!');
                }
            }
            let properties = document.getElementById(key).getElementsByClassName('properties');
            let type = document.getElementById(key).querySelector('select[name="type"]');
            let typeVal = type.options[type.selectedIndex].value;
            for(let i=0; i<properties.length; i++){
                // console.log(typeof properties[i].getAttribute('layer'));
                let opt = properties[i].options[properties[i].selectedIndex];
                if(opt){
                    updateIcon(properties[i].name,opt.value,key);
                    properties[i].onchange = function(e){addEventChangeStyle(key,typeVal,e)};
                    
                }
            }
            
        }
        zoom(map_clean);
        // getGeoJson(map_clean,function(e){
        //     console.log(e);
        // })
    }
    
}

function removeFile(id){
    tujuan= $.options.slave+"?switcher=delete&id="+id;
    var ele = $.dataAction.target;
	$.Confirm('Anda yakin menghapus data ini? ',function(){
        $.get(ele,tujuan,function callback(Result){
            console.log(Result);
            if(!Result.response.error){
                filePolygon['collection_'+id].forEach(function(feature){
                    map_clean.data.remove(feature);
                });
                // document.getElementById('collection_'+id).remove();
                Result.element.remove();
                document.getElementById('publishbtn').style.display = null;
            }else{
                $.Alert(Result.response.message);
            }
        });
    });
}
function addEventChangeStyle(idFile,typeVal,event){
        var that = event.target;
        let forData = new FormData();
        let layer = 0;
        if(that.getAttribute('layer')){
            layer = that.getAttribute('layer');
            idFile = that.getAttribute('parentid');
        }
        let id = idFile.split('_')[1];
        let idName = id;
        if(that.getAttribute('layer')){
            layer = that.getAttribute('layer');
            idFile = that.getAttribute('parentid');
            idName = id+layer;
            console.log(idFile);
        }
        let nameID =  document.getElementById("name_"+idName);
        forData.append('id',id);
        forData.append('styleid',layer);
        stylename = nameID.value;
        forData.append('stylename',stylename);
        forData.append('type',typeVal);
        forData.append('name',that.name);
        forData.append('setvalue',that.value);
        $.post(forData,$.options.slave+"?switcher=set-style",function(ev){
            // console.log(ev.response);
            document.getElementById('publishbtn').style.display = null;
            if(ev.response.error === false){
                updateIcon(that.name,that.value,idFile,layer);
            }else{
                // $.Alert(ev.response.message);
            }
        });
}
// function mustSave(){
//     document.getElementById('publishbtn').style.display = null;
//     document.getElementById('publishbtn').value = "Save";
//     document.getElementById('publishbtn').onclick = function(){
//         let elChild = document.getElementById('toolsmaps').querySelectorAll('.form-frame');
//     };
// }
function processPoints(geometry, callback, thisArg) {
    if (geometry instanceof google.maps.LatLng) {
      callback.call(thisArg, geometry);
    } else if (geometry instanceof google.maps.Data.Point) {
      callback.call(thisArg, geometry.get());
    } else {
      geometry.getArray().forEach(function(g) {
        processPoints(g, callback, thisArg);
      });
    }
  }
  function zoom(map) {
    const bounds = new google.maps.LatLngBounds();
    map.data.forEach((feature) => {
      const geometry = feature.getGeometry();
      if (geometry) {
        processPoints(geometry, bounds.extend, bounds);
      }
    });
    map.fitBounds(bounds);
  }
  function setPointToCircle(map) {
    map.data.forEach((feature) => {
      if(feature.getGeometry().getType() === 'Point'){
        var radius = parseFloat(feature.getProperty('radius'));
        if (radius) {
            new google.maps.Circle({
              map: map,
              center: feature.getGeometry().get(),
              radius: radius,
              fillColor: '#f00',
              fillOpacity: 0.5,
              strokeWeight: 0,
            });
          }
      }else{
        
      }
    });
  }
  function changePoint(e){
    console.log(e);
  }
function getParentAttr(a,TAGNAME,select){
    var utils = $.Utils();//from owlproject.js
    var b;a= utils.getElement(a);
        while(a){
            if(utils.Def(a)){
                if(a.querySelector(TAGNAME)){
                    if(select == 'tag'){
                        b = a.querySelector(TAGNAME);
                    }else{
                        b = a;
                    }
                    break;
                }
            }
            a=utils.Def(a.parentNode)?a.parentNode:null
        }
        return b
}
function deleteAction(param){
    tujuan= $.options.slave+param;
	let ele = $.dataAction.target;
	$.Confirm('Anda yakin menghapus data ini? ',function(){
        $.get(ele,tujuan,function callback(Result){
            if(!Result.response.error){
                Result.element.remove();
            }else{
                $.Alert(Result.response.message);
            }
        });
    });
}
function postingAction(param){
    tujuan= $.options.slave+param;
	let ele = $.dataAction.target;
	$.Confirm('Anda yakin unpublish data ini? ',function(){
        $.get(ele,tujuan,function callback(Result){
            if(!Result.response.error){
                $.refresh();
            }else{
                $.Alert(Result.response.message);
            }
        });
    });
}