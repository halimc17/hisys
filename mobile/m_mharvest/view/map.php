
<div class="owl-container bg-white">
    <div id="header" class="header-content"></div>
    <div id="map" class="full-height"></div>
</div>

<script>
  (g=>{var h,a,k,p="The Google Maps JavaScript API",c="google",l="importLibrary",q="__ib__",m=document,b=window;b=b[c]||(b[c]={});var d=b.maps||(b.maps={}),r=new Set,e=new URLSearchParams,u=()=>h||(h=new Promise(async(f,n)=>{await (a=m.createElement("script"));e.set("libraries",[...r]+"");for(k in g)e.set(k.replace(/[A-Z]/g,t=>"_"+t[0].toLowerCase()),g[k]);e.set("callback",c+".maps."+q);a.src=`https://maps.${c}apis.com/maps/api/js?`+e;d[q]=f;a.onerror=()=>h=n(Error(p+" could not load."));a.nonce=m.querySelector("script[nonce]")?.nonce||"";m.head.append(a)}));d[l]?console.warn(p+" only loads once. Ignoring:",g):d[l]=(f,...n)=>r.add(f)&&u().then(()=>d[l](f,...n))})({
    key: "AIzaSyAMZbc5nDMusMaE8HhXmCT-ICwwHZmX9us",
    v: "weekly",
    // Use the 'v' parameter to indicate the version to use (weekly, beta, alpha, etc.).
    // Add other bootstrap parameters as needed, using camel case.
	//https://maps.googleapis.com/maps/api/js?libraries=maps&key=AIzaSyAMZbc5nDMusMaE8HhXmCT-ICwwHZmX9us&v=weekly&callback=google.maps.__ib__
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
var kmlLayer;
var AdvMarker;
// var AdvancedMarkerElement;
async function initMap(result) {
	const { Map } = await google.maps.importLibrary("maps");
    const { AdvancedMarkerElement } = await google.maps.importLibrary("marker");
	map = new Map(document.getElementById("map"), {
		center: coorSetCenter,
		zoom: 5.6,
        mapId :'8f0acee7725e9c9f',
        zoomControl: true,
        zoomControlOptions: {
            style: {
                bottom : 100,
            }
        }
	});
    // kmlLayer = new google.maps.KmlLayer(optionKML.a.url, {
    //       suppressInfoWindows: false,
    //       preserveViewport: false,
    //       map: map
    // });
    // kmlLayer.addListener('click', function(event) {
    //     // event.featureData
    // });

    coordMapType = new CoordMapType(new google.maps.Size(256, 256));
    // const centerControlDiv = document.createElement("div");
    // centerControlDiv.appendChild(centerControl);
    // map.controls[google.maps.ControlPosition.TOP_CENTER].push(centerControlDiv);

    result("DONE");
}
initMap(res=>{
    loadAllDaata(res);
});
    
function loadAllDaata(res){
    // Load User Route Mobile
    // getRoute_all("2024-07-09",map.getZoom());

    // Load Transaction

    //load company Poligone
}
</script>
