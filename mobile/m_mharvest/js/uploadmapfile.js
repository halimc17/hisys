

var map_clean;
var coorSetCenter_clean = { lat: -2.44565, lng: 117.8888};
var coordMapType_clean;
var kmlLayer;
var myParser;
var filePolygon={};
var AdvancedMarkerElement;
const iconsDef = {
    pokok: {
      icon: "../template/primary/assets/map/marker/marker_pokok.png",
    },
    tph: {
      icon: "../template/primary/assets/map/marker/marker_circle.png",
    },
    kantor: {
        icon: "../template/primary/assets/map/marker/marker_circle.png",
    },
    circle: {
      icon: "../template/primary/assets/map/marker/marker_circle.png",
    },
  };
const polygonDef = {
    primary: {
        strokeColor: "#567f9a",
        strokeOpacity: 1,
        strokeWeight: 2,
        fillColor: "#ffffff",
        fillOpacity: 1
    }
};

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
}
var addScript = async src => new Promise((resolve, reject) => {
    const el = document.createElement('script');
    el.src = src;
    el.addEventListener('load', resolve);
    el.addEventListener('error', reject);
    document.head.append(el);
}); 

function loadMap(arg){
    mapview = document.querySelectorAll('table[show]');
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
            mapId :'7abc3de8b1ee5325',
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
                        // filePolygon[key].id = key;
                        // filePolygon[key].file = ev.response;
                        addGeoJsonStyle(ev.response,key);
                    }catch(e){
                        console.log(e);
                    }
                });
            });
        }
    });
}
function testFunction(){
    console.log(arguments);
}
function show_geojson(){
    getGeoJson(map_clean,function(contents){
        console.log(contents);
        var contents = fs.readFileSync('./dmv_file_reader.txt').toString()
        var blob = new Blob([contents], { type: 'text/plain' });
        var file = new File([blob], "_temp.geojson", {type: "text/plain"});
        $.post(mapview[0],$.options.slave+"?switcher=openxml&path="+source[key],function(ev){
            try{
                // filePolygon[key].id = key;
                // filePolygon[key].file = ev.response;
                addGeoJsonStyle(ev.response,key);
            }catch(e){
                console.log(e);
            }
        });

    });
}
function setIcon(val,id){
    var collection = document.getElementById('collection_'+id);
    var stroke_color = document.getElementById('stroke_color'+id);
    var stroke_color_val = document.getElementById('stroke_color_val'+id);
    
    var fill_color = document.getElementById('fill_color'+id);
    var fill_color_val = document.getElementById('fill_color_val'+id);

    var marker_name = document.getElementById('marker_name'+id);
    var marker_name_val = document.getElementById('marker_name_val'+id);
    
    if(val=='pokok' || val=='tph' || val=='kantor' || val=='point'){
        stroke_color.style.display = "none";
        stroke_color_val.innerHTML = "";
        fill_color.style.display = "none";
        fill_color_val.innerHTML = "";
        marker_name.style.display = null;
        marker_name_val.innerHTML = "";
        tujuan= $.options.slave+"?switcher=marker-name&key="+val;
            $.get(collection,tujuan,function(ev){
                try{
                    let dataArr = ev.response;
                    Object.keys(dataArr).forEach((key, num) => {
                        marker_name_val[num] = new Option(dataArr[key],key);
                    });
                    marker_name_val.onchange = function(e){
                        var icon = this.value;
                        filePolygon['collection_'+id].forEach(function(feature){
                            feature.setProperty('marker-name',icon);
                        });
                        // map_clean.data.forEach(function(feature){
                        //     // console.log(feature);
                        //     // feature.setProperty('marker-name',icon);
                        // });
                    }
                }catch(e){
                    console.log(e);
                }
            });
    }else if(val=='jalan' || val=='sungai' || val=='line'){
        marker_name.style.display   = "none";
        marker_name_val.innerHTML   = "";
        fill_color.style.display    = "none";
        fill_color_val.innerHTML    = "";

        stroke_color.style.display  = null;
        stroke_color_val.innerHTML  = "";
        tujuan= $.options.slave+"?switcher=stroke-color&key="+val;
        $.get(collection,tujuan,function(ev){
            try{
                let dataArr = ev.response;
                Object.keys(dataArr).forEach((key, num) => {
                    stroke_color_val[num] = new Option(dataArr[key],key);
                });
                stroke_color_val.onchange = function(e){
                    var icon = this.value;
                    // map_clean.data.forEach(function(feature){
                    //     feature.setProperty('stroke-color',icon);
                    // });
                    filePolygon['collection_'+id].forEach(function(feature){
                        feature.setProperty('stroke-color',icon);
                    });
                }
            }catch(e){
                console.log(e);
            }
        });
    }else{
        marker_name.style.display   = 'none';
        marker_name_val.innerHTML   = "";

        stroke_color.style.display  = null;
        stroke_color_val.innerHTML  = "";
        fill_color.style.display    = null;
        fill_color_val.innerHTML    = "";
        
        $.get(collection,$.options.slave+"?switcher=stroke-color&key="+val,function(ev){
            try{
                let dataArr = ev.response;
                Object.keys(dataArr).forEach((key, num) => {
                    stroke_color_val[num] = new Option(dataArr[key],key);
                });
                stroke_color_val.onchange = function(e){
                    var icon = this.value;
                    console.log(icon);
                    filePolygon['collection_'+id].forEach(function(feature){
                        feature.setProperty('stroke-color',icon);
                    });
                    // map_clean.data.forEach(function(feature){
                    //     feature.setProperty('stroke-color',icon);
                    // });
                    
                }
            }catch(e){
                console.log(e);
            }
        });

        $.get(collection,$.options.slave+"?switcher=fill-color&key="+val,function(ev){
            try{
                let dataArr = ev.response;
                Object.keys(dataArr).forEach((key, num) => {
                    fill_color_val[num] = new Option(dataArr[key],key);
                });
                fill_color_val.onchange = function(e){
                    var icon = this.value;
                    filePolygon['collection_'+id].forEach(function(feature){
                        feature.setProperty('fill-color',icon);
                    });
                    // map_clean.data.forEach(function(feature){
                    //     feature.setProperty('fill-color',icon);
                    // });
                }
            }catch(e){
                console.log(e);
            }
        });
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
    // console.log(ev);
    if(ev.response.error === false){
        $.Alert("DONE");
        winUpload.close();
    }else{
        $.Alert(ev.response.message);
    }
    
}

function showFileGeojson(arg,event){
    addGeoJsonStyle(arg.response);
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
    addGeoJsonStyle(arg.response);
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
function addGeoJsonStyle(file=null,key='collection_temp'){
    // map_clean.data.setStyle(styleFeature);
    styleFeature();
    if(file !== null){
        filePolygon[key] = map_clean.data.addGeoJson(file);
        zoom(map_clean);
        // getGeoJson(map_clean,function(e){
        //     console.log(e);
        // })
    }
   
}
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