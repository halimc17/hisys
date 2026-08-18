
<div class="owl-container bg-white">
    <div id="header" class="header-content"></div>
    <div id="map" class="full-height"></div>
</div>


<script>
  (g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
    key: "AIzaSyAMZbc5nDMusMaE8HhXmCT-ICwwHZmX9us",
    v: "weekly",
  });
</script>
<script type="text/javascript">
/**
 * @license
 * Copyright 2019 Google LLC. All Rights Reserved.
 * SPDX-License-Identifier: Apache-2.0
 */
var map;
var coorSetCenter = { lat: -2.548926, lng: 118.0148634};
var coordMapType;
var geoJsonFile;
var kmlLayer;
var menuRight;
var navBox;
// var svgLayer;
var AdvMarker;
// var AdvancedMarkerElement;

async function initMap(result) {
	const { Map } = await google.maps.importLibrary("maps");
    const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
	map = new Map(document.getElementById("map"), {
		center: coorSetCenter,
        disableDefaultUI : true,
		zoom: 5.6,
        mapId :'8f0acee7725e9c9f',
	});
    
    coordMapType = new CoordMapType(new google.maps.Size(256, 256));

    navBox = navigationMap();
    map.controls[google.maps.ControlPosition.LEFT] = navBox;
    result("DONE");
}
initMap(res=>{
    google.maps.event.addListener(map, 'zoom_changed', function() {
        // setStyleFeatureDefault(map);
    });
    google.maps.event.addListener(map.data, 'rightclick', function(event) {
        if(actions = event.feature.getProperty("list-action")){
            if(Object.keys(actions).length>0){
                $.menuActionList(actions,$,event.feature.getProperty("name"),event);
            };
        }
    });
    google.maps.event.addListener(map.data, 'click', function(event) {
        $.Alert(event.feature.getProperty("description"));
    });
    loadAllData(res);
});
    
function loadAllData(res){
    $.get(document.getElementById("map"),'<?php echo $this->site_url('api/module/datamap/geojson/load'); ?>',function callback(Result){
        // console.log(Result);
        if(!Result.response.error){
            let data = Result.response.result;
            if(data.length > 0){
                data.forEach((value, key) => {
                    map.data.loadGeoJson(value.src+'?version='+value.version,null,function (features) {
                        // map.fitBounds(bounds); // or do other stuff what requires all features loaded
                        setStyleFeatureDefault(map);
                        zoomAt(map);
                    });
                });
            }   
        }else{
            $.Alert(Result.response.message);
        }
    });
    // zoomAt(map);
    
    // Load User Route Mobile
    // getRoute_all("2024-07-09",map.getZoom());

    // Load Transaction

    //load company Poligone
}
function newRightWindow(tujuan,title,id){
    if(menuRight){
        menuRight.close();
    }
    var option = {
        window:'right',
        width:300
    };
    menuRight = $.newWindow(tujuan,title,id,false,false,option);
}
</script>
