

var map_clean;
var coorSetCenter_clean = { lat: -2.44565, lng: 117.8888};
var coordMapType_clean;
var kmlLayer;
var myParser;
/**
 * @license
 * Copyright 2019 Google LLC. All Rights Reserved.
 * SPDX-License-Identifier: Apache-2.0
 */
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
        getFileUrl(source);
        // let loadFile = new Promise((res)=>{
        //         kmlLayer = new google.maps.KmlLayer(source,{
        //             map: map_clean,
        //         });
        //         kmlLayer.addListener('click', function(event) {
        //             console.log(event.featureData);
        //         });
        //         res(kmlLayer);
        // });
        // loadFile.then(res => {
        //     console.log(map_clean);



        // });
      


        // if(kmlLayer.status == 'INVALID_REQUEST'){
        //     $.Alert("Goole Map cannot load Data KML.");
        //     console.log(kmlLayer);
        // }
        // getFileUrl(src);
        // let reader = new FileReader();
        // reader.onload = function (event) {
        //     let arrayBuffer = event.target.result;
        //     let dom = new DOMParser().parseFromString(arrayBuffer, "application/xml");
        //     let error = dom.querySelector("parsererror");
        //     if (error) throw new Error(error.innerText);
        //     console.log(dom);
        // };
        
    });
    // document.getElementById('author').innerHTML = kmlLayer.getMetadata().author.name;
    //     document.getElementById('description').innerHTML = kmlLayer.getMetadata().description;
    //     document.getElementById('nametag').innerHTML = kmlLayer.getMetadata().name;
    // $.Alert(kmlLayer.getMetadata().author.name);
}
function viewAction(getPage){
    tujuan= $.options.slave+getPage;
    let options = {
        url: tujuan,
	    title:'View File',
        success :function(arg){
            loadMap(arg);
        } 
    };
	winUpdate = $.openWindow(options);
}

function openkmz(src){ 
    console.log(src);
    var myParser = new geoXML3.parser({map: map_clean});
    myParser.parse(src);
}
function showFileKMZ(arg){
    console.log(arg);
}
function getFileUrl(src){	
    console.log(src);
    fetch(
        src,
        {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/octet-stream',
                'Content-Disposition': 'attachment; filename="dummy.js"',
            },
            body: {
                image: dummyBase64Image,
            }
        }
    )
    // var ajax = new XMLHttpRequest();
    // ajax.open("PUT",src, true);
    // ajax.
    // ajax.send();
    // ajax.onload = function(e){
    //     xmlDoc = new DOMParser().parseFromString(e.target.responseText, "application/octet-stream");
    //     let googlePolygons = [];
    //     let googleMarkers = [];
    //     if (xmlDoc.documentElement.nodeName == "kml") {
    //         console.log(xmlDoc.documentElement.nodeName);
    //     }else{
    //         console.log(xmlDoc.documentElement);
    //     }
    //     // let error = Node.querySelector("parsererror");
    //     // if (error) throw new Error(error.innerText);
    //     // console.log(Node);

    // }
}

// file: any
//   fileChanged(e) {
//     this.file = e.target.files[0]
//     this.parseDocument(this.file)
//   }parseDocument(file) {
//     let fileReader = new FileReader()
//     fileReader.onload = async (e: any) => {
//       let result = await this.extractGoogleCoords(e.target.result)

//       //Do something with result object here
//       console.log(result)

//     }
//     fileReader.readAsText(file)
//   }

// async extractGoogleCoords(plainText) {
//     let parser = new DOMParser()
//     let xmlDoc = parser.parseFromString(plainText, "text/xml")
//     let googlePolygons = []
//     let googleMarkers = []

//     if (xmlDoc.documentElement.nodeName == "kml") {

//       for (const item of xmlDoc.getElementsByTagName('Placemark') as any) {
//         let placeMarkName = item.getElementsByTagName('name')[0].childNodes[0].nodeValue.trim()
//         let polygons = item.getElementsByTagName('Polygon')
//         let markers = item.getElementsByTagName('Point')

//         /** POLYGONS PARSE **/        
//         for (const polygon of polygons) {
//           let coords = polygon.getElementsByTagName('coordinates')[0].childNodes[0].nodeValue.trim()
//           let points = coords.split(" ")

//           let googlePolygonsPaths = []
//           for (const point of points) {
//             let coord = point.split(",")
//             googlePolygonsPaths.push({ lat: +coord[1], lng: +coord[0] })
//           }
//           googlePolygons.push(googlePolygonsPaths)
//         }

//         /** MARKER PARSE **/    
//         for (const marker of markers) {
//           var coords = marker.getElementsByTagName('coordinates')[0].childNodes[0].nodeValue.trim()
//           let coord = coords.split(",")
//           googleMarkers.push({ lat: +coord[1], lng: +coord[0] })
//         }
//       }
//     } else {
//       throw "error while parsing"
//     }

//     return { markers: googleMarkers, polygons: googlePolygons }

//   }