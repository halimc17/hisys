

var map_clean;
var coorSetCenter_clean = { lat: -2.44565, lng: 117.8888};
var coordMapType_clean;
var kmlLayer;
var myParser;
var filePolygon={};
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
    mapview = document.getElementById('mapview');
    let src = mapview.getAttribute('show');
    if(!document.getElementById('googlemapsource')){
        (g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.id=`googlemapsource`;a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
            key: "AIzaSyAMZbc5nDMusMaE8HhXmCT-ICwwHZmX9us",
            v: "weekly",
            });
    }
    async function initMapClean(result) {
        const { Map } = await google.maps.importLibrary("maps");
        const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
        map_clean = new Map(document.getElementById("map_clean"), {
            center: coorSetCenter_clean,
            zoom: 6,
            mapId :'7abc3de8b1ee5325',
        });
        coordMapType_clean = new CoordMapType(new google.maps.Size(256, 256));
        map_clean.overlayMapTypes.insertAt(0, coordMapType_clean);
        result(src);
    }

    initMapClean(source=>{
        if(arg!=null){
            tujuan= $.options.slave+"?switcher=openxml&path="+source;
            $.get(mapview,tujuan,function(ev){
                showFileKMZ(ev);
            });
        }
    });
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
            dragOverFile();
        } 
    };
	winUpdate = $.openWindow(options);
}
function getoutput(event){
    if (!event || !event.target || !event.target.files || event.target.files.length === 0) {
        return;
      }
    let reader = new FileReader(); // built in API
    reader.onload = handleFileLoad;
    reader.readAsText(event.target.files[0]);
    function handleFileLoad(ev){
        let loadFile = new Promise((res)=>{
            kmlLayer = new google.maps.KmlLayer(ev.result,{
                map: map_clean,
            });
            kmlLayer.addListener('click', function(event) {
                console.log(event.featureData);
            });
            res(kmlLayer);
            console.log(kmlLayer);

         });
    }
}
function showFileKMZ(arg,event){
    // console.log(arg.element.submit);
    console.log(arg.response);
    // DATA ARRAY
    // ===================
    // fileupload: "Blok_KTJE_(4).kmz"
    // name :"doc.kml"
    // info:
    //     basename: "doc.kml"
    //     dirname: "."
    //     extension: "kml"
    //     filename:"doc"
    var dataArr = arg.response;
    let polygon = new Promise((res)=>{
        let allData = new Array();
        dataArr.forEach((value, key) => {
            let polygon_temp = {
                file_name : "",
                file_ext : "",
                type : "Google Maps",
                dataExtrac : new Array()
            };
            (async() => {
                polygon_temp.file_name = value.fileupload;
                polygon_temp.file_ext = value.info.extension;
                let data = await extractGoogleCoords(value.content);
                polygon_temp.dataExtrac = data;
                allData.push(polygon_temp);
            } )();
            res(allData);
        })
    }).then(result=>{
        if(result.length > 0){
            let polygonLoop = new Promise((res)=>{
                let data = new Array();
                (async() => {
                    result.forEach((file, keyFile) => {
                        console.log(file.dataExtrac);
                        if(file.dataExtrac.hasOwnProperty('bounds')){
                            for(let x in file.dataExtrac){
                                if(x !== 'bounds'){
                                    filePolygon[x] = {};
                                    filePolygon[x].data = {};
                                    filePolygon[x].data.id = file.dataExtrac[x].id;
                                    filePolygon[x].data.name = file.dataExtrac[x].name;
                                    filePolygon[x].data.description = file.dataExtrac[x].description;
                                    console.log(file.dataExtrac[x].polygons);
                                    if(file.dataExtrac[x].polygons.length > 0){
                                        filePolygon[x].Polygon = new google.maps.Polygon({
                                            paths: file.dataExtrac[x].polygons,
                                            strokeColor: "#637987",
                                            strokeOpacity: 0.8,
                                            strokeWeight: 2,
                                            fillColor: "#ffffff",
                                            fillOpacity: 0.35,
                                        });
                                        filePolygon[x].Polygon.addListener('click', function(event) {
                                            document.getElementById('idtag').innerHTML = filePolygon[x].data.id;
                                            document.getElementById('nametag').innerHTML = filePolygon[x].data.name;
                                            document.getElementById('description').innerHTML = filePolygon[x].data.description;
                                            var that = this;
                                            document.getElementById('colortag').onchange = function(){
                                                that.setOptions({fillColor:this.value});
                                            }
                                        });
                                    }
                                    filePolygon[x].Polygon.setMap(map_clean);
                                    if(file.dataExtrac[x].polylines.length > 0){
                                        filePolygon[x].Polyline = new google.maps.Polyline({
                                            path: file.dataExtrac[x].polylines,
                                            geodesic: true,
                                            strokeColor: '#FF0000',
                                            strokeOpacity: 1.0,
                                            strokeWeight: 2,
                                        });
                                        filePolygon[x].Polyline.setMap(map_clean);
                                    }
                                    if(file.dataExtrac[x].markers.length > 0){
                                        filePolygon[x].markers = new Array();
                                        for(let k in file.dataExtrac[x].markers){
                                            filePolygon[x].markers[k] = new google.maps.Marker({
                                                position: file.dataExtrac[x].markers[k],
                                                map_clean,
                                                animation:google.maps.Animation.DROP
                                            });
                                        }
                                    }
                                    data.push(file.dataExtrac[x]);
                                    
                                    // nameTage = document.createElement("div");
                                    // nameTage.textContent = file.dataExtrac[x].name;
                                    // filePolygon[x].Polygon.Marker = new google.maps.marker.AdvancedMarkerElement({
                                    //     map_clean,
                                    //     position: file.dataExtrac.bounds.getCenter(),
                                    //     content: nameTage
                                    // });
                                    // console.log(filePolygon[x]);
                                    // filePolygon[x].Label.setMap(map_clean);
                                }else{
                                    console.log(file.dataExtrac.bounds);
                                    map_clean.setCenter(file.dataExtrac.bounds.getCenter());
                                    map_clean.fitBounds(file.dataExtrac.bounds);
                                }
                                // map_clean
                            }
                        }else{
                            // console.log(file.dataExtrac);
                            // Map polygon coordinates
                            // svg = file.dataExtrac
                            // var points = path.match(/(\d+)/g);
                            // var polyCoordinates = [];
                            // for (var i = 0; i < points.length; i += 2) {
                            //     var longitude = toLongitude(parseInt(points[i])),
                            //         latitude = toLatitude(parseInt(points[i + 1]));
                        
                            //     polyCoordinates.push(new google.maps.LatLng(latitude, longitude));
                            // }
                            console.log(file.dataExtrac);
                            for(let x in file.dataExtrac){
                                if(x !== 'viewBox'){
                                    let icon = {
                                        path: file.dataExtrac[x].paths,
                                        fillColor: '#FF0000',
                                        fillOpacity: .6,
                                        anchor: new google.maps.Point(file.dataExtrac[x].x,file.dataExtrac[x].y),
                                        strokeWeight: 0,
                                        scale: 1
                                    }
                                    filePolygon[x] = {};
                                    filePolygon[x].markers = new google.maps.Marker({
                                        position: file.dataExtrac[x].LatLng,
                                        map: map_clean,
                                        draggable: false,
                                        icon: icon
                                    });
                                    // filePolygon[x].Polygon = new google.maps.Polygon({
                                    //     paths: file.dataExtrac[x].paths,
                                    //     strokeColor: "#637987",
                                    //     strokeOpacity: 0.8,
                                    //     strokeWeight: 2,
                                    //     fillColor: "#ffffff",
                                    //     fillOpacity: 0.35,
                                    // });
                                    // filePolygon[x].Polygon.setMap(map_clean);
                                    // console.log(file.dataExtrac[x].width);
                                }else{
                                    console.log(file.dataExtrac.viewBox);
                                    map_clean.setCenter(file.dataExtrac.viewBox.getCenter());
                                    map_clean.fitBounds(file.dataExtrac.viewBox);
                                }
                                data.push(file.dataExtrac[x]);
                            }
                        }
                    });
                    res(data);
                } )();
                
            });
            polygonLoop.then(resPoly=>{
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
                }else{
                    
                }
            });
        }
    });
   
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
    form.setAttribute('callback','showFileKMZ');
    button = form.submit;
    button.value = "Preview";
    $.scanFormTag(winUpdate.target.body);
}
function afterUpload(ev){
    console.log(ev);
    $.Alert('DONE');
    winUpdate.close();
    $.refresh();
}

async function extractGoogleCoords(plainText){
    let parser = new DOMParser()
    let xmlDoc = parser.parseFromString(plainText, "text/xml");
    var bounds = new google.maps.LatLngBounds();
    let result = {};
    if (xmlDoc.documentElement.nodeName == "kml"){
        for (const item of xmlDoc.getElementsByTagName('Placemark')){
            let Placemark = {
                id :'',
                name:'',
                description:'',
                polygons:new Array(),
                markers:new Array(),
                polylines:new Array(),
            }
            
            let polygons = item.getElementsByTagName('Polygon')
            let markers = item.getElementsByTagName('Point')
            let polylines = item.getElementsByTagName('polyline')
            Placemark.id = item.id;
            Placemark.name = item.querySelector('name').firstChild.nodeValue.trim();
            Placemark.description = item.querySelector('description').firstChild.nodeValue.trim();
            /** POLYGONS PARSE **/        
            for (const polygon of polygons) {
                let coords = polygon.getElementsByTagName('coordinates')[0].childNodes[0].nodeValue.trim()
                let points = coords.split(" ")

                let googlePolygonsPaths = []
                for (const point of points) {
                    let coord = point.split(",")
                    googlePolygonsPaths.push({ lat: +coord[1], lng: +coord[0] })
                    bounds.extend({ lat: +coord[1], lng: +coord[0] });
                }
                Placemark.polygons.push(googlePolygonsPaths)
            }
            /** MARKER PARSE **/    
            for (const marker of markers) {
                var coords = marker.getElementsByTagName('coordinates')[0].childNodes[0].nodeValue.trim()
                let coord = coords.split(",")
                Placemark.markers.push({ lat: +coord[1], lng: +coord[0] })
            }
            for (const polyline of polylines) {
                var coords = polyline.getElementsByTagName('coordinates')[0].childNodes[0].nodeValue.trim()
                let points = coords.split(" ")
                let googlePolylinesPaths = []
                for (const point of points) {
                    let coord = point.split(",")
                    googlePolylinesPaths.push({ lat: +coord[1], lng: +coord[0] })
                    bounds.extend({ lat: +coord[1], lng: +coord[0] });
                }
                Placemark.polylines.push(googlePolylinesPaths)
            }
            result[item.id] = Placemark;
        }
        result.bounds = bounds;

    // }else if(xmlDoc.documentElement.nodeName == "svg"){
    //     let viewBoxNS;
    //     // let viewBox = xmlDoc.documentElement.getAttribute('viewBox').split(' ');
    //     var svg = xmlDoc.documentElement;
    //     var p = svg.createSVGPoint();
    //     // console.log(p);
    //     var width = 400, // width of SVG
    //         height = 200; // height of SVG
    //     let polyCoordinates = new Array();
    //     // for (let i = 0; i < viewBox.length; i += 2) {
    //     //     let longitude = toLongitude(parseInt(viewBox[i]),width),
    //     //     latitude = toLatitude(parseInt(viewBox[i + 1]),width);
    //     //     // polyCoordinates.push(new google.maps.LatLng(latitude, longitude));
            
    //     // }
    //     function screenToSVG(screenX, screenY) {
    //          p.x = screenX
    //          p.y = screenY
    //          return p.matrixTransform(svg.getScreenCTM().inverse());
    //      }
         
    //      function SVGToScreen(svgX, svgY) {
    //          p.x = svgX
    //          p.y = svgY
    //          return p.matrixTransform(svg.getScreenCTM());
    //      }
    //     function toLongitude(x,w) {
    //         return x * 180 / w;
    //     }
        
    //     function toLatitude(y,w) {
    //         return -y * 180 / w + 90;
    //     }
    //     for (const item of svg.querySelectorAll("path")){
    //         let Placemark = {
    //             paths: new Array()
    //         }
    //         parentNode = getParentAttr(item,'rect','element');
    //         reactNode = getParentAttr(item,'rect','tag');
    //         let x = reactNode.getAttribute('x')
    //         let y = reactNode.getAttribute('y')
    //         let w = reactNode.getAttribute('width')
    //         let h = reactNode.getAttribute('height')
    //         let g = item.parentNode;
    //         let path = item.getAttribute("d")
    //         let points = path.match(/(\d+)/g)
    //         Placemark.id = g.id
    //         Placemark.name = g.id
    //         Placemark.x = p.x
    //         Placemark.y = p.y
    //         Placemark.w = w
    //         Placemark.h = h
    //         Placemark.lat = SVGToScreen(x,y).y
    //         Placemark.lng = SVGToScreen(x,y).x
    //         Placemark.LatLng = new google.maps.LatLng(Placemark.lat,Placemark.lng)
    //         Placemark.paths = path;
    //         Placemark.polygons = new Array();
    //         bounds.extend(new google.maps.LatLng(Placemark.lat,Placemark.lng));
    //         // Map polygon coordinates
    //         let googlePolygonsPaths = []
    //         for (let i = 0; i < points.length; i += 2) {
    //             let longitude = toLongitude(parseInt(points[i]),w),
    //                 latitude = toLatitude(parseInt(points[i + 1]),w);
    //             // let latLong = SVGToScreen(latitude,longitude);
    //                 // latlong = screenToSVG(points[i],points[i+1]);
    //                 googlePolygonsPaths.push(latitude,longitude)
    //                 bounds.extend(new google.maps.LatLng(latitude, longitude));
    //         }
    //         Placemark.polygons.push(googlePolygonsPaths);
            
    //         // bounds.extend(polyCoordinates);
            
    //         result[g.id] = Placemark;
    //     }
        
    //     result.viewBox = bounds;
    //     // console.log(result);

    //     // for (const item of xmlDoc.querySelectorAll("g")){
    //     //     let rect = item.getAttribute("rect");
    //     // }
    //     // for (const item of xmlDoc.querySelectorAll("text")){
    //     //     let polygons = item.getAttribute("d");
    //     // }
         
    //     // let s = new XMLSerializer();
    //     // let svgString = s.serializeToString(xmlDoc);
    //     // result;//'data:image/svg+xml;charset=UTF-8;base64,' + btoa(svgString);
    } else {
        $.Alert("Error while parsing, This file ("+xmlDoc.documentElement.nodeName+") cannot be open.");
        throw "error while parsing"
    }
    return result
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