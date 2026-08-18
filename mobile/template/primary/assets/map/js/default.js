var dataPolyline = {};
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
		strokeOpacity: 0.4,
		strokeWeight: 2,
		fillColor: "#ffffff",
		fillOpacity: 0.4
	}
};
function navigationMap(){
//==========================================================
	let menuMapUtama = document.createElement('ul');
    menuMapUtama.classList.add("menu-map");
    options = {
        text :'Search',
        title:'Search',
        class:[],
        icon:['fa','fa-search'],
        callback:function(){
            newRightWindow('module/search','Search','Search');
        }
    }
    let menuSearch = createButtonControl(map,options);
    menuMapUtama.appendChild(menuSearch);
    options = {
        text :'Menu',
        title:'Menu Map',
        class:[],
        icon:['fa','fa-bars']
    }
    let menuBars = createButtonControl(map,options);
    menuMapUtama.appendChild(menuBars);
	menuBars.onclick=function(){
		parentNd = this.parentNode;
		if(parentNd.classList.contains('active')){
			child = document.getElementsByClassName('child');
			for(let i=0; i<child.length;i++){
				child[i].classList.remove('active');
			}
			parentNd.classList.remove('active');
		}else{
			child = document.getElementsByClassName('child');
			for(let i=0; i<child.length;i++){
				child[i].classList.add('active');
			}
			parentNd.classList.add('active');
		}
	}
//==========================================================
    let menuMap = document.createElement('ul');
    menuMap.classList.add("menu-map");
    menuMap.classList.add("child");

	options = {
        text :'Activity',
        title:'Activity',
        class:[],
        icon:['fa','fa-briefcase'],
        callback:function(){
            // newRightWindow('module/search','Activity','Activity');
        }
    }
    let menuActivity = createButtonControl(map,options);
    menuMap.appendChild(menuActivity);
	let menuMap_ = document.createElement('ul');
	menuActivity.appendChild(menuMap_);
    options = {
        text :'User',
        title:'User Coordinate',
        class:[],
        icon:['fa','fa-user'],
        callback:function(){
            getRoute_all();
        }
    }
    let menuUser = createButtonControl(map,options);
    menuMap_.appendChild(menuUser);
   
    options = {
        text :'Truck',
        title:'Truck',
        class:[],
        icon:['fa','fa-truck'],
        callback:function(){
            newRightWindow('module/search','Truck','Truck');
        }
    }
    let menuTruck = createButtonControl(map,options);
    menuMap_.appendChild(menuTruck);
	
    options = {
        text :'Road',
        title:'Road',
        class:[],
        icon:['fa','fa-road'],
        callback:function(){
            newRightWindow('module/Road','Road','Road');
        }
    }
    let menuRoad= createButtonControl(map,options);
    menuMap_.appendChild(menuRoad);
//==========================================================
    const menuMapType = document.createElement('ul');
    menuMapType.classList.add("menu-map");
	menuMapType.classList.add("child");
    options = {
        text :'Map Roadmap',
        title:'Map Roadmap',
        class:[],
        icon:['fa','fa-map-o'],
        callback:function(){
            map.setMapTypeId('roadmap');
        }
    }
    let roadmapType = createButtonControl(map,options);
    menuMapType.appendChild(roadmapType);
	menuMapType_ = document.createElement('ul');
    roadmapType.appendChild(menuMapType_);

    options = {
        text :'Map Terrain',
        title:'Map Terrain',
        class:[],
        icon:['fa','fa-map'],
        callback:function(){
            map.setMapTypeId('terrain');
        }
    }
    let terrainType = createButtonControl(map,options);
    menuMapType_.appendChild(terrainType);
    options = {
        text :'Map Type',
        title:'Map Type',
        class:[],
        icon:['fa','fa-rocket'],
        callback:function(){
            map.setMapTypeId('satellite');
        }
    }
    let satelliteType = createButtonControl(map,options);
    menuMapType_.appendChild(satelliteType);
//==========================================================
    let menuZoom = document.createElement('ul');
    menuZoom.classList.add("menu-map");
	menuZoom.classList.add("child");
	elementToSendFullscreen = map.getDiv().firstChild;
	options = {
        text :'Full Screen',
        title:'Full Screen',
        class:[],
        icon:['fa','fa-television'],
        callback:function(){
            if (isFullscreen(elementToSendFullscreen)) {
				exitFullscreen(elementToSendFullscreen);
			} else {
				requestFullscreen(elementToSendFullscreen);
			}
        }
    }
    let menuFullScreen= createButtonControl(map,options);
	document.onwebkitfullscreenchange =
    document.onmsfullscreenchange =
    document.onmozfullscreenchange =
    document.onfullscreenchange =
      function () {
        if (isFullscreen(elementToSendFullscreen)) {
			menuFullScreen.classList.add("is-fullscreen");
        } else {
			menuFullScreen.classList.remove("is-fullscreen");
        }
      };
    menuZoom.appendChild(menuFullScreen);
    options = {
        text :'Zoom In',
        title:'Zoom In',
        class:[],
        icon:['fa','fa-plus'],
        callback:function(){
            map.setZoom(map.getZoom()+1);
        }
    }
    let menuZoomIn= createButtonControl(map,options);
    menuZoom.appendChild(menuZoomIn);
    options = {
        text :'Zoom Out',
        title:'Zoom Out',
        class:[],
        icon:['fa','fa-minus'],
        callback:function(){
            map.setZoom(map.getZoom()-1);
        }
    }
    let menuZoomOut= createButtonControl(map,options);
    menuZoom.appendChild(menuZoomOut);
	
//==========================================================
	return [menuMapUtama,menuMap,menuMapType,menuZoom];
}
function isFullscreen(element) {
	return (
	  (document.fullscreenElement ||
		document.webkitFullscreenElement ||
		document.mozFullScreenElement ||
		document.msFullscreenElement) == element
	);
}

function requestFullscreen(element) {
	if (element.requestFullscreen) {
		element.requestFullscreen();
	} else if (element.webkitRequestFullScreen) {
		element.webkitRequestFullScreen();
	} else if (element.mozRequestFullScreen) {
		element.mozRequestFullScreen();
	} else if (element.msRequestFullScreen) {
		element.msRequestFullScreen();
	}
}

function exitFullscreen() {
	if (document.exitFullscreen) {
		document.exitFullscreen();
	} else if (document.webkitExitFullscreen) {
		document.webkitExitFullscreen();
	} else if (document.mozCancelFullScreen) {
		document.mozCancelFullScreen();
	} else if (document.msExitFullscreen) {
		document.msExitFullscreen();
	}
}
function createButtonControl(map,options) {
	const controlLi = document.createElement("li");
	const controlButton = document.createElement("button");
	controlButton.classList.add("button-map");
	if(options.class.length>0){
	  for(let i=0; i<options.class.length; i++){
		  controlButton.classList.add(options.class[i]);
	  }
	}
	if(options.icon.length>0){
	  let controlIcon = document.createElement("i");
	  for(let i=0; i<options.icon.length; i++){
		  controlIcon.classList.add(options.icon[i]);
	  }
	  controlButton.appendChild(controlIcon);
	}else{
	  controlButton.textContent = options.text;
	}
	controlButton.title = options.title;
	controlButton.type = "button";
  
	// Setup the click event listeners: simply set the map to Chicago.
	controlButton.addEventListener("click", (e) => {
		if(typeof options.callback == 'function'){
			options.callback(controlButton,e);
		}
	});
	controlLi.appendChild(controlButton);
	return controlLi;
  }
function getGeoJson(map,callback){
	var geo={"type": "FeatureCollection","features": []},
	fx=function(g,t){
		var that  =[],arr,f= {
					MultiLineString :'LineString',
					LineString      :'Point',
					MultiPolygon    :'Polygon',
					Polygon         :'LinearRing',
					LinearRing      :'Point',
					MultiPoint      :'Point'
				};
		switch(t){
			case 'Point':
				g=(g.get)?g.get():g;
				return([g.lng(),g.lat()]);
				break;
			default:
				arr= g.getArray();
				for(var i=0;i<arr.length;++i){
				that.push(fx(arr[i],f[t]));
				}
				if( t=='LinearRing' 
					&&
					that[0]!==that[that.length-1]){
				that.push([that[0][0],that[0][1]]);
				}
				return that;
		}
	};

	map.data.forEach(function(feature){
		var _feature     = {type:'Feature',properties:{}}
			_id          = feature.getId(),
			_geometry    = feature.getGeometry(),
			_type        =_geometry.getType(),
			_coordinates = fx(_geometry,_type);

			_feature.geometry={type:_type,coordinates:_coordinates};
			if(typeof _id==='string'){
			_feature.id=_id;
			}

			geo.features.push(_feature);
			feature.forEachProperty(function(v,k){
			_feature.properties[k]=v;
			});
	}); 
	if(typeof callback==='function'){
		callback(geo);
	}     
	return geo;
	}
	function setNULL(USER){
		Object.keys(dataPolyline).forEach(key => {
			if(dataPolyline[key].hasOwnProperty('Polyline') && key != USER){
				dataPolyline[key].Polyline.setMap(null);
			}
			if(dataPolyline[key].hasOwnProperty('Marker') && key != USER){
				// dataPolyline[key].Marker.setMap(null);
			}
		});
	}
	function setSHOW(USER){
		Object.keys(dataPolyline).forEach(key => {
			if(dataPolyline[key].hasOwnProperty('Polyline') && key == USER){
				dataPolyline[key].Polyline.setMap(map);
			}
			if(dataPolyline[key].hasOwnProperty('Marker') && key == USER){
				// dataPolyline[key].Marker.setMap(map);
			}
		});
	}
	async function getRoute_all(tgl,zoom=14){
		var tanggal = tgl;
		$.getBackground("api/module/dashboard/traffic_user/load",function(Result){
			if(Result.response.result.length > 0){
				var data = Result.response.result;
				let user = new Promise((res)=>{
					let user_temp = [];
					data.forEach((value, key) => {
						if(typeof dataPolyline[value.namauser] === 'undefined'){
							dataPolyline[value.namauser] = {};
							dataPolyline[value.namauser].username = value.namauser;
							dataPolyline[value.namauser].ver = "00:00:00";
							dataPolyline[value.namauser].color = "";
							dataPolyline[value.namauser].coordinates = [];
						}
							user_temp.push(value.namauser);
						});
						res(user_temp);
					})
					.then(result => {
						let exec = new Promise((res)=>{
							result.forEach((value, key) => {
								if(tanggal != ''){
									value = value+'||'+tanggal;
								}
								getRoute_user(value,key,function(d,u){
									res(d);
								});
							});
					})
					.then(resExec =>{
						coorSetCenter = resExec;
						if(zoom !== null){
							map.setCenter(coorSetCenter);
							map.setZoom(zoom);
						}
					});
				});
			}
		});
	  } 
	  async function getRoute_user(UserTgl,multi,callback){
		let paramUser = UserTgl.split('||');
		let User = paramUser[0];
		let bounds = new google.maps.LatLngBounds();
		var Tgl = '';
		if(UserTgl.split('||').length > 1 ){
			Tgl = paramUser[1];
		}
		if(typeof multi == 'undefined'){
			setNULL(User);
		}
		let exec = new Promise((res)=>{
			var USER = User;
			let addver = "";
			if(dataPolyline.hasOwnProperty(USER) == true && dataPolyline[USER].hasOwnProperty('Polyline')){
				addver += "&ver="+dataPolyline[USER].ver;
				dataPolyline[USER].Polyline.setMap(map);
				if(dataPolyline[USER].hasOwnProperty('Marker')){
					// dataPolyline[USER].Marker.setMap(map);
				}
			}
			if(Tgl != ''){
				addver += "&tanggal="+Tgl;
			}
			$.getBackground("api/module/dashboard/traffic_locations/load?user="+USER+"&type=2"+addver,function(Result){
				if(Result.response.result != null){
					let update = false;
					let userData = Result.response.result;
					if(userData.hasOwnProperty(USER)){
						if(typeof dataPolyline[USER] === 'undefined'){
							dataPolyline[USER] = {};
						}
						if(dataPolyline[USER].hasOwnProperty('Polyline')){
							if(dataPolyline[USER].coordinates.length > 0 && dataPolyline[USER].ver != userData[USER].ver){
								update = true;
								dataPolyline[USER].ver = userData[USER].ver;
								dataPolyline[USER].color = userData[USER].color;
							}else{
								dataPolyline[USER].ver = userData[USER].ver;
								dataPolyline[USER].color = userData[USER].color;
								dataPolyline[USER].coordinates = userData[USER].coordinates
							}
							
						}else{
							dataPolyline[USER] = userData[USER];
						}
	
						if(dataPolyline[USER].hasOwnProperty('Polyline') && update == true){
							console.log('UPDATE');
							(async() => {
								let path = new Promise((res)=>{
											let PATH = dataPolyline[USER].Polyline.getPath();
											userData[USER].coordinates.forEach((value, key) => {
												PATH.push(value);
												bounds.extend(value);
											});
											res(PATH);
									}) ;
									path.then(res => {
										dataPolyline[USER].coordinates = res;
										dataPolyline[USER].Polyline.setPath(res);
									});
									coorSetCenter = userData[USER].coordinates[userData[USER].coordinates.length - 1];
									if(typeof multi == 'undefined'){
										map.setCenter(coorSetCenter);
										map.setZoom(16);
									}
									res(coorSetCenter);
							} )();
						}else if(!dataPolyline[USER].hasOwnProperty('Polyline') && dataPolyline[USER].coordinates.length > 0){
							dataPolyline[USER].Polyline = new google.maps.Polyline({
								path: dataPolyline[USER].coordinates,
								geodesic: true,
								strokeColor: dataPolyline[USER].color,
								strokeOpacity: 1.0,
								strokeWeight: 2,
							});
							dataPolyline[USER].Polyline.setMap(map);
							if(!dataPolyline[USER].hasOwnProperty('Marker')){
								// dataPolyline[USER].Marker = new google.maps.Marker({
								// 	position: userData[USER].coordinates[0],
								// 	map,
								// 	title: USER,
								// 	animation:google.maps.Animation.DROP
								// });
								// dataPolyline[USER].Marker.setMap(map);
								// dataPolyline[USER].Callback = function(){}
								// google.maps.event.addListener(dataPolyline[USER].Marker, 'click', function(newFunction) {
								// 	if(typeof openDataUserActivity == 'function'){
								// 		let param = new Array();
								// 		param.push(USER);
								// 		param.push(Tgl);
								// 		openDataUserActivity(param);
								// 	}
								// });
								try{
								nameTage = document.createElement("div");
								nameTage.className = "map-name-tag";
								nameTage.textContent = USER;
								nameTage.style.backgroundColor = dataPolyline[USER].color;
								nameTage.style.setProperty("bcolor", dataPolyline[USER].color);
								dataPolyline[USER].Marker = new google.maps.marker.AdvancedMarkerElement({
									map,
									position: userData[USER].coordinates[userData[USER].coordinates.length - 1],
									content: nameTage
								  });
								google.maps.event.addListener(dataPolyline[USER].Marker, 'click', function(newFunction) {
									if(typeof openDataUserActivity == 'function'){
										let param = new Array();
										param.push(USER);
										param.push(Tgl);
										openDataUserActivity(param);
									}
								});
								}catch(e){
									console.log(e);
								}
							}
							coorSetCenter = userData[USER].coordinates[userData[USER].coordinates.length - 1];
							if(typeof multi == 'undefined'){
								map.setCenter(coorSetCenter);
								map.setZoom(16);
							}
							
						}else{
							// console.log(USER+" : Data Empty");
						}
						res(coorSetCenter);
					}
				}else{
					res(coorSetCenter);
				}
			});
		});
		exec.then(result => {
			coorSetCenter = result;
			if(typeof callback != 'undefined'){
				callback(result,User);
			}
		});   
	}


function setStyleFeatureDefault(map){
	var zoom = map.getZoom();
	var relativePixelSize = Math.round(zoom/22*15); 
	var size2 = new google.maps.Size(relativePixelSize, relativePixelSize);
	map.data.setStyle((feature)=>{
		if((feature.getGeometry().getType() === 'Point' || feature.getGeometry().getType() === 'MultiPoint')){
			if(feature.getGeometry().getType() === 'MultiPoint'){
				return {icon:{
					url:((!feature.getProperty('marker-name'))?iconsDef['circle'].icon:iconsDef[feature.getProperty('marker-name')].icon),
					origin: new google.maps.Point(0,0),
					anchor: new google.maps.Point(7.5,7.5),
					size: size2,
					scaledSize: size2
				}};
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
function pushBounds(geometry, callback, thisArg) {
    if (geometry instanceof google.maps.LatLng) {
      callback.call(thisArg, geometry);
    } else if (geometry instanceof google.maps.Data.Point) {
      callback.call(thisArg, geometry.get());
    } else {
      geometry.getArray().forEach(function(g) {
        pushBounds(g, callback, thisArg);
      });
    }
  }
function zoomAt(map) {
    const bounds = new google.maps.LatLngBounds();
	map.data.forEach((feature) => {
		const geometry = feature.getGeometry();
		if (geometry) {
			pushBounds(geometry, bounds.extend, bounds);
		}
	});
    map.fitBounds(bounds);
  }
function openDataUserActivity(param){
	window.getRoute_user(param.join("||"));
}
function searchmap(type){
	switch(type){
		case 'open':
			if(document.getElementById('header')){
				var header = document.getElementById('header');
				var backsearchmap = document.getElementById('backsearchmap');
				var search_map = document.getElementById('search_map');
				var cachesearchboth = document.getElementById('cache-search-both');
				var tssLabel = document.getElementsByClassName('tss-label');
				header.style.height="100%";
				header.style.backgroundColor ="rgb(245,245,245)";
				cachesearchboth.style.opacity = 1;
				cachesearchboth.style.height =null;
				backsearchmap.style.display = "block";
				if(tssLabel.length>0){
					tssLabel[0].style.display = "none";
				}
				var button = cachesearchboth.getElementsByTagName('li');
				if(button.length > 0){
					for(i=0; i<button.length; i++){
						button[i].onclick = function(event){
							searchmap('close');
						}
					}
				}
				backsearchmap.onclick = function(event){
					searchmap('close');
				}
			}
		break;
		case 'close':
			if(document.getElementById('header')){
				var header = document.getElementById('header');
				var backsearchmap = document.getElementById('backsearchmap');
				var cachesearchboth = document.getElementById('cache-search-both');
				var tssLabel = document.getElementsByClassName('tss-label');
				header.style.height = null;
				header.style.backgroundColor = null;
				cachesearchboth.style.opacity = null;
				cachesearchboth.style.height = "0px";
				backsearchmap.style.display = null;
				if(tssLabel.length>0){
					tssLabel[0].style.display = null;
				}
			}
		break;
	}
	
}
function openNaveFoter(e){
	var parentEle = e.parentNode;
	var footnavmap = parentEle.getElementsByClassName('active');
	if(footnavmap.length>0){
		for(i=0; i<footnavmap.length; i++){
			if(footnavmap[i].getAttribute("content-id") !== e.getAttribute("content-id")){
				footnavmap[i].classList.remove('active');
			}else{
				footnavmap[i].classList.add('active');
			}
		}
	}	
	var _conentId = e.getAttribute('content-id');
	e.classList.add('active');
	openContent(_conentId);
	function openContent(id){
		if(document.getElementById(id)){
			contentid = document.getElementById(id);
			var parentContent = contentid.parentNode;
			var fdiv= parentContent.getElementsByClassName('footer-nav-content');
			if(fdiv.length>0){
				for(i=0; i<fdiv.length; i++){
					if(fdiv[i].id !== id){
						fdiv[i].classList.remove('active');
					}else{
						fdiv[i].classList.add('active');
					}
				}
			}	
			
		}
	}
}
function mapResetView(){
	if(document.getElementById('header')){
		header = document.getElementById('header');
		header.style.height = "0px";
	}
	if(document.getElementById('footermap')){
		footermap = document.getElementById('footermap');
		if(header.style.height !== null){
			footermap.setAttribute('prev-height',footermap.style.height);
			footermap.style.height = null;
		}
	}
}
function footerNaveMapSwipe(){
	
	var footermap = document.getElementById('footermap');
	var footer_nav = footermap.querySelector(".footer-nav");
	// var openclosenavboth = document.getElementById('openclosenavboth');
	// var openfloatnavboth = document.getElementById('openfloatnavboth');
	var clicked = 0;
	var prevDiff = 0;
	var upDown = '';
	var maxHeigh = (window.innerHeight-57);
	var cacheHeigh = (window.innerHeight-57);
	var standardH = footermap.clientHeight;
	var h = 0;
	function up(num){
		var result = false;
		if(Math.sign(num) == 1){
			result = true;
		}
		return result;
	}
	function down(num){
		var result = false;
		if(Math.sign(num) == -1){
			result = true;
		}
		return result;
	}
	var supportsTouch = false;
	if ('ontouchstart' in window) {
		//iOS & android
		supportsTouch = true;

	} else if(window.navigator.msPointerEnabled) {

		// Windows
		// To test for touch capable hardware 
		if(navigator.msMaxTouchPoints) {
			supportsTouch = true;
		}

	}
	if(supportsTouch){
		footer_nav.ontouchstart = function(evt) {
			if (evt.preventDefault) {
				  evt.preventDefault();
			}
			upDown = 'clicked';
			
			prevDiff = evt.touches[0].clientY;
			h = footermap.clientHeight;
			cacheHeigh = h;
			footer_nav.ontouchmove = function(evt) {
				y = evt.touches[0].clientY;
				diff = (h+(prevDiff-y));
				if((diff>60 && up((prevDiff-y)) == true) || down((prevDiff-y)) == true){
					if(diff <= (standardH+10)){
						footermap.style.height = (standardH)+"px";
						cacheHeigh = standardH;
					}else if(diff > (maxHeigh+40)){
						footermap.style.height = (maxHeigh)+"px";
						cacheHeigh = maxHeigh;
					}else{
						footermap.style.height = diff+"px";
						cacheHeigh = diff;
					}
				}
				if(up((prevDiff-y)) == true){
					upDown = 'up';
				}else if(down((prevDiff-y)) == true){
					upDown = 'down';
				}
			}
			
		}
		
		footer_nav.ontouchleave = function(evt) {
			if(upDown == 'up' || upDown == 'down'){
				if(footermap.clientHeight < (standardH+50)){
					footermap.style.height = standardH+"px";
				}else if((h < 300 && this.clientHeight > 300) || (h > 300 && this.clientHeight < 300)){
					footermap.style.height = 300+"px";
				}else if(h > 300 && this.clientHeight > maxHeigh){
					footermap.style.height = maxHeigh+"px";
				}
			}
			upDown = '';
			footer_nav.ontouchmove = null;
		}
		footer_nav.ontouchend = function(evt) {
			if(upDown == 'up' || upDown == 'down'){
				if(footermap.clientHeight < (standardH+50)){
					footermap.style.height = standardH+"px";
				}else if((h < 300 && footermap.clientHeight > 300) || (h > 300 && footermap.clientHeight < 300)){
					footermap.style.height = 300+"px";
				}else if(h > 300 && footermap.clientHeight > maxHeigh){
					footermap.style.height = maxHeigh+"px";
				}
			}else if(upDown == 'clicked'){
				if(evt.target.classList.contains("foot-nav-map")){
					openNaveFoter(evt.target);
				}else if(evt.target.parentNode.classList.contains("foot-nav-map")){
					openNaveFoter(evt.target.parentNode);
				}
				if(evt.target.id == 'openfloatnavboth'){
					if(footermap.style.maxWidth != 100+"%"){
						footermap.style.maxWidth = 100+"%";
						// openfloatnavboth.classList.remove("fa-arrow-right");
						// openfloatnavboth.classList.add("fa-arrow-left");
					}else{
						footermap.style.maxWidth = null;
						// openfloatnavboth.classList.remove("fa-arrow-left");
						// openfloatnavboth.classList.add("fa-arrow-right");
					}
				}else{
					
					if(footermap.clientHeight > standardH){
					// 	if(evt.target.id == openclosenavboth.id){
							footermap.style.height = standardH+"px";
					// 		openclosenavboth.classList.remove("fa-arrow-down");
					// 		openclosenavboth.classList.add("fa-arrow-up");
					// 	}
					}else if(footermap.clientHeight <= standardH){
					// 	openclosenavboth.classList.remove("fa-arrow-up");
					// 	openclosenavboth.classList.add("fa-arrow-down");
						footermap.style.height = 300+"px";
					}
				}
			}
			upDown = '';
			footer_nav.ontouchmove = null;

		}
	}else{
		footer_nav.onmousedown = function(evt) {
			if (evt.preventDefault) {
				  evt.preventDefault();
			}
			
			prevDiff = evt.clientY;
			// stopPropaganda(evt);
            evt.stopPropagation();

			h = footermap.clientHeight;
			upDown = 'clicked';
			cacheHeigh = h;
			footer_nav.onmousemove = function(evt) {
				y = evt.clientY;
				diff = (h+(prevDiff-y));
				if((diff>20 && up((prevDiff-y)) == true) || down((prevDiff-y)) == true){
					if(diff <= (standardH+10)){
						footermap.style.height = (standardH)+"px";
						cacheHeigh = standardH;
					}else if(diff > (maxHeigh+40)){
						footermap.style.height = (maxHeigh)+"px";
						cacheHeigh = maxHeigh;
					}else{
						footermap.style.height = diff+"px";
						cacheHeigh = diff;
					}
				}
				if(up((prevDiff-y)) == true){
					upDown = 'up';
					// openclosenavboth.classList.remove("fa-arrow-up");
					// openclosenavboth.classList.add("fa-arrow-down");
				}else if(down((prevDiff-y)) == true){
					upDown = 'down';
					// openclosenavboth.classList.remove("fa-arrow-down");
					// openclosenavboth.classList.add("fa-arrow-up");
				}
			}
		}
		
		footer_nav.onmouseleave = function(evt) {
			if(upDown == 'up' || upDown == 'down'){
				if(footermap.clientHeight < (standardH+50)){
					footermap.style.height = standardH+"px";
				}else if((h < 300 && footermap.clientHeight > 300) || (h > 300 && footermap.clientHeight < 300)){
					footermap.style.height = 300+"px";
				}else if(h > 300 && footermap.clientHeight > maxHeigh){
					footermap.style.height = maxHeigh+"px";
				}
			}
			upDown = '';
			footer_nav.onmousemove = null;
		}
		footer_nav.onmouseup = function(evt) {
			if(upDown == 'up' || upDown == 'down'){
				if(footermap.clientHeight < (standardH+50)){
					footermap.style.height = standardH+"px";
				}else if((h < 300 && footermap.clientHeight > 300) || (h > 300 && footermap.clientHeight < 300)){
					footermap.style.height = 300+"px";
				}else if(h > 300 && footermap.clientHeight > maxHeigh){
					footermap.style.height = maxHeigh+"px";
				}
			}else if(upDown == 'clicked'){
				if(evt.target.classList.contains("foot-nav-map")){
					openNaveFoter(evt.target);
				}else if(evt.target.parentNode.classList.contains("foot-nav-map")){
					openNaveFoter(evt.target.parentNode);
				}
				if(evt.target.id == 'openfloatnavboth'){
					if(footermap.style.maxWidth != 100+"%"){
						footermap.style.maxWidth = 100+"%";
						// openfloatnavboth.classList.remove("fa-arrow-right");
						// openfloatnavboth.classList.add("fa-arrow-left");
					}else{
						footermap.style.maxWidth = null;
						// openfloatnavboth.classList.remove("fa-arrow-left");
						// openfloatnavboth.classList.add("fa-arrow-right");
					}
				}else{
					
					if(footermap.clientHeight > standardH){
					// 	if(evt.target.id == openclosenavboth.id){
							footermap.style.height = standardH+"px";
					// 		openclosenavboth.classList.remove("fa-arrow-down");
					// 		openclosenavboth.classList.add("fa-arrow-up");
					// 	}
					}else if(footermap.clientHeight <= standardH){
					// 	openclosenavboth.classList.remove("fa-arrow-up");
					// 	openclosenavboth.classList.add("fa-arrow-down");
						footermap.style.height = 300+"px";
					}
				}
			}
			upDown = '';
			footer_nav.onmousemove = null;
			console.log("up null")
		}
	}
}
function showCoordBox(ele){
    if(ele.checked){
        map.overlayMapTypes.insertAt(0, coordMapType);
    }else{
        map.overlayMapTypes.removeAt(0);
    }
}


class CoordMapType {
	tileSize;
	alt = null;
	maxZoom = 17;
	minZoom = 0;
	name = null;
	projection = null;
	radius = 6378137;
	constructor(tileSize) {
	  this.tileSize = tileSize;
	}
	getTile(coord, zoom, ownerDocument) {
	  const div = ownerDocument.createElement("div");
	  let color = "#017cff80";
	  div.innerHTML = String(coord);
	  div.style.width = this.tileSize.width + "px";
	  div.style.height = this.tileSize.height + "px";
	  div.style.fontSize = "10";
	  div.style.color = color;
	  div.style.borderStyle = "solid";
	  div.style.borderWidth = ".5px";
	  div.style.borderColor = color;
	  return div;
	}
	releaseTile(tile) {}
}

function createCenterControl(map) {
	const controlButton = document.createElement("button");
	// Set CSS for the control.
	controlButton.style.backgroundColor = "#fff";
	controlButton.style.border = "2px solid #fff";
	controlButton.style.borderRadius = "3px";
	controlButton.style.boxShadow = "0 2px 6px rgba(0,0,0,.3)";
	controlButton.style.color = "rgb(25,25,25)";
	controlButton.style.cursor = "pointer";
	controlButton.style.fontFamily = "Roboto,Arial,sans-serif";
	controlButton.style.fontSize = "16px";
	controlButton.style.lineHeight = "38px";
	controlButton.style.margin = "8px 0 22px";
	controlButton.style.padding = "0 5px";
	controlButton.style.textAlign = "center";
	controlButton.textContent = "Center Map";
	controlButton.title = "Click to recenter the map";
	controlButton.type = "button";
	return controlButton;
}

// function SvgOverlay(options) {
// 	this.options_ = options || {};
// 	this.container_ = document.createElement('div');
// 	this.container_.style.position = 'absolute';
// 	this.center_ = new google.maps.LatLng(0, 0);
// 	if (!this.options_.layer) {
// 		this.options_.layer = 'overlayLayer';
// 	}

// 	if (this.options_.map) {
// 		this.setMap(this.options_.map);
// 	}

// 	if (this.options_.content) {
// 		this.setContent(this.options_.content);
// 	}
// }
// SvgOverlay.prototype = new google.maps.OverlayView();
// 	/**
// 	 * Internal method. Triggered when `setMap` was called with an argument.
// 	 */
// 	SvgOverlay.prototype.onAdd = function() {
// 	this.getPanes()[this.options_.layer].appendChild(this.container_);
// };

// /**
//  * Set the new SVG content to display on a map.
//  * @param {String} content The content to display (SVG)
//  */
// SvgOverlay.prototype.setContent = function(content) {
// 	this.container_.innerHTML = content;
// 	this.content_ = content;
// 	this.svg_ = this.container_.getElementsByTagName('svg')[0];

// 	this.draw();
// };

// /**
//  * Get the assigned SVG string.
//  * @return {String} The content passed in
//  */
// SvgOverlay.prototype.getContent = function() {
// 	return this.content_;
// };

// /**
//  * Get the surrounding DOM container.
//  * @return {Element} The container element
//  */
// SvgOverlay.prototype.getContainer = function() {
// 	return this.container_;
// };

// /**
//  * Get the SVG element.
//  * @return {Element} The SVG element
//  */
// SvgOverlay.prototype.getSvg = function() {
// 	return this.svg_;
// };

// /**
//  * Internal method. Called when the layer needs an update.
//  */
// SvgOverlay.prototype.draw = function() {
// 	var projection = this.getProjection(),
// 		style, center, width, offset, left, top;

// 	if (!projection || !this.svg_) {
// 		return;
// 	}

// 	style = this.container_.style;

// 	// compute layer offset
// 	center = projection.fromLatLngToDivPixel(this.center_);
// 	width = Math.round(projection.getWorldWidth());
// 	offset = width / 2;

// 	left = Math.round(center.x) - offset;
// 	top = Math.round(center.y) - offset;

// 	// scale svg to world bounds
// 	this.svg_.setAttribute('width', width);
// 	this.svg_.setAttribute('height', width);

// 	// apply offset
// 	style.left = left + 'px';
// 	style.top = top + 'px';
// };
  
// /**
//  * Internal method. Triggered when `setMap` was called with `null.
//  */
// SvgOverlay.prototype.onRemove = function() {
// 	this.container_.parentNode.removeChild(this.container_);
// };

// /**
//  * Make module compatible to module loaders
//  */
// if (typeof module == 'object') {
// 	module.exports = SvgOverlay;
// }
  
  