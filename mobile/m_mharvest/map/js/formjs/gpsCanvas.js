
function layeringSVG(url,id){	
	var imageSVG = url;
	var ajax = new XMLHttpRequest();
	ajax.open("GET",imageSVG, true);
	ajax.send();
	ajax.onload = function(e) {
		hideProgress();
		//try{
			Node = new DOMParser().parseFromString(ajax.responseText, "image/svg+xml");
			g = Node.getElementsByTagName("g")[0]; 
			g.id = id;
			layeringPolygon_in(g,id);
		/*}catch(ex){
			message = "Gagal build SVG, apakah Anda ingin mendownload ulang ke online ?";
			title ="Gagal!";
			buttonLabels = ['Update','Cancel'];
			notifConfirm(message,title,buttonLabels,callback);
			function callback(res){
				if(res == 1){
					if(onOnline()){
						reGettingBlockSvg(id);
					}
				}
			}
			
		}*/
		
	}
}
function reGettingBlockSvg(id){
	if(typeof id === 'undefined'){
		var id = sessionStorage.kebun;
	}
	if(onOnline()){
		del_file(id+".svg");
		db.transaction(function (tx) {
			tx.executeSql('delete from data_svg where name ="'+id+'" ',[],function(tx, rs){ 
				reloadBlockSvg(id);
			},function(tx,error){errorHandler(tx,error);});
		},null,null); 
	}
}
var successTrans = function (r) {
	hideProgress();
	alert(JSON.stringify(r.name));
	locationFile = r.toURL();
	var id = r.name.split(".")[0];
	db.transaction(function (tx) {
		var InsertSTR = 'INSERT INTO data_svg (name,path,version) values ("'+id+'","'+locationFile+'","1");';
		alert("insert SVG ");
		tx.executeSql(InsertSTR,[],function(){
			alert(locationFile);
			if(!document.getElementById(id)){
				layeringSVG(locationFile,id);
			}
		},function(tx,error){errorHandler(tx,error);});
	},null,null); 
	
}
function zoomtestblok(id){
	var blok = panZoom.zoomByIdName(id);
	z = 843;
	var s = panZoom.getSizes();
	var p = panZoom.getPan();
	//var z = Math.min((s.width/blok.width),(s.height/blok.height));
	//console.log(z,blok.width);
	x = blok.longitude; //106.8537106
	y = (parseFloat(blok.latitude)*-1); //6.4379798
	panZoom.pan({
		x:-(x * s.realZoom) + (s.width/2) - (blok.width/2),
		y:-(y * s.realZoom) + (s.height/2) - (blok.height/2)
	});
	panZoom.zoom((z),false);
}
var failureTrans = function (error) {
	hideProgress();
	alert("An error has occurred: Code = " + error.code);
}
function del_file(filename) {
    window.resolveLocalFileSystemURL(cordova.file.externalDataDirectory, function (dir) {
        dir.getFile(filename, {create: false}, function (fileEntry) {
            fileEntry.remove(function (file) {
                alert("file removed!");
            }, function (error) {
                alert("error occurred: " + error.code);
            }, function () {
                alert("file does not exist");
            });
        });
    });
}
function reloadBlockSvg(id){
	showProgress();
	if(typeof id === "undefined"){
		id = sessionStorage.kebun;
	}
	db.transaction(function (tx) {
		tx.executeSql('CREATE TABLE IF NOT EXISTS data_svg(name TEXT,path TEXT,version TEXT,viewbox TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
		tx.executeSql('SELECT * FROM data_svg where name= "'+id+'" limit 1 ', [], function(tx, rs){ 
			if(rs.rows.length>0){
				var locationFile = rs.rows.item(0).path;
				//alert("select SVG");
				layeringSVG(locationFile,id);
			}else{
				var name = id+".svg";
				fileURL = cordova.file.dataDirectory+name;
				var options = new FileUploadOptions();
				options.chunkedMode = false;
				options.fileKey = "file";
				options.headers = {
				  Connection: "close"
			   };
				options.fileName = fileURL.substr(fileURL.lastIndexOf('/') + 1);
				options.mimeType = "image/svg+xml";

				var params = {};
				params.pt = sessionStorage.pt;
				params.lokasitugas = sessionStorage.kebun;
				params.subbagian = sessionStorage.subbagian;
				params.typedata = 'svgkebun';
				params.file = id;
				
				options.params = params;
				url = encodeURI(sessionStorage.server+"/bi_mobile/testmap.php");
				if(onOnline()){
					var ft = new FileTransfer();
					ft.download(url,fileURL, successTrans, failureTrans, options);
				}
			}
		}, function(tx,error){
			errorHandler(tx,error);
		});
	 },null,null); 
}

function reloadBlockSvg_test(){
	var url ="map/test.svg";
	var id = "testblock";
	if(!document.getElementById(id)){
		layeringSVG(url,id);
	}else{
		zoomtestblok(id);
	}	
}
function trackingGPSNow(){
	showProgress();
	//<div class="zoomnav"><button id="getZoomIn" class="zoom-button zoomin" onclick="javascript:getZoomIn();"><div class="icon"></div></button><button id="getZoomOut"class="zoom-button zoomout" onclick="javascript:getZoomOut();"><div class="icon"></div></button><div class="zoom-button-divider"></div></div>
	var newElement = '<a class="currentlocation" onclick="javascript:getLocation();"><div class="iconcurrentlocation"></div></a><div class="zoomnav"><button id="getZoomIn" class="zoom-button zoomin" onclick="javascript:getZoomIn();"><div class="icon"></div></button><button id="getZoomOut"class="zoom-button zoomout" onclick="javascript:getZoomOut();"><div class="icon"></div></button><div class="zoom-button-divider"></div></div>';
	var newElementFooter = '<span id="showscale" class="showscale">0</span><br><span id="showmeter" class="showscale">@0,0</span>';
	
	//var bodyCanvas = document.getElementById("body_"+sessionStorage.panel);
	var bodyCanvas = document.getElementById("home_map");
	bodyCanvas.style.paddingLeft = "0px";
	bodyCanvas.style.paddingRight = "0px";
	//var canvas = document.createElement("canvas");
	var div = document.createElement("div");
	div.className = "nav";
	div.innerHTML = newElement;
	var footer = document.createElement("div");
	footer.className = "footer-info";
	footer.innerHTML = newElementFooter;
	//canvas.id = "ruangmap";
	//bodyCanvas.appendChild(canvas);
	bodyCanvas.appendChild(div);
	bodyCanvas.appendChild(footer);
	CreateSVG('indonesia');
}

function pinPath(height, radius,x,y) {
  //const dyAC = height - radius;
  //const alpha = Math.acos(radius / dyAC);
  //console.log(alpha);
 // const deltaX = radius * Math.sin(alpha);
 // const deltaY = height * (height - radius * 2) / dyAC;
  
  path = "M182.9,551.7c0,0.1,0.2,0.3,0.2,0.3S358.3,283,358.3,194.6c0-130.1-88.8-186.7-175.4-186.9   C96.3,7.9,7.5,64.5,7.5,194.6c0,88.4,175.3,357.4,175.3,357.4S182.9,551.7,182.9,551.7z M122.2,187.2c0-33.6,27.2-60.8,60.8-60.8   c33.6,0,60.8,27.2,60.8,60.8S216.5,248,182.9,248C149.4,248,122.2,220.8,122.2,187.2z";
  
  return path;
 // return "M "+x+","+y+" L "+(x-deltaX)+","+(y+sdeltaY)+" A "+radius+" "+radius+" 1 1 1 "+(deltaX)+","+(deltaY*-1)+" L 0,0 z";
}

function addPin(background, border,x,y) {
	g = document.createElementNS('http://www.w3.org/2000/svg', "g");
    //const path = pinPath(height, radius,x,y);
	//<image x="10" y="20" width="80" height="80" xlink:href="recursion.svg" />

	svg = 'http://www.w3.org/2000/svg';
	p = document.createElementNS(svg, "image");
	p.id = "location_image";
	p.setAttributeNS(null,"x",x);
	p.setAttributeNS(null,"y",y);
	p.setAttributeNS(null,"width","100");
	p.setAttributeNS(null,"height","100");
	p.setAttributeNS("http://www.w3.org/1999/xlink","xlink:href","map/pin.svg");
	g.appendChild(p);
	//p.setAttribute('d', path);
	//p.style.stroke = border;
	//p.style.fill = background;
    layeringPolygon(g);
}

function reCreateSVG(newSvg,id,callback){
	window.panZoom.destroy();
	var svgEle = newSvg;
	window.panZoom = svgPanZoom(svgEle, {
		zoomEnabled: true,
		controlIconsEnabled: true,
		fit: 1,
		center: 1,
		onPan: function(){
			isPaused = true;
			stabiliZationPin(this.getPan(),this.getSizes(),this.getZoom());
		}
	});
	if(typeof callback == "function"){
		eval(callback(id));
	}
}
function CreateSVG(typeload,url,callback){
		if(typeload == "indonesia"){
			url = "map/indonesia.svg";
		}
		var ajax = new XMLHttpRequest();
		ajax.open("GET", url, true);
		ajax.send();
		ajax.onload = function(e) {
			hideProgress();
			if(typeload == "svg" || typeload == "indonesia"){
				Node = new DOMParser().parseFromString(ajax.responseText, "image/svg+xml");
				mapCache = Node.getElementsByTagName("svg")[0];
				
				if(typeload == "indonesia"){
					var bodyCanvas = document.getElementById("home_map");
					bodyCanvas.appendChild(mapCache);
					var svgEle = document.getElementById("owl-map");
					window.panZoom = svgPanZoom(svgEle, {
						zoomEnabled: true,
						controlIconsEnabled: true,
						fit: 1,
						center: 1,
						onPan: function(){
							isPaused = true;
							stabiliZationPin(this.getPan(),this.getSizes(),this.getZoom());
						}
					});
				}
				
			}else if(typeload == "base64"){
				var div = document.createElement("div");
				div.innerHTML = ajax.responseText;
				svgEle = div.childNodes[0];
				var xml = new XMLSerializer().serializeToString(svgEle);
				var svg64 = btoa(xml);
				var b64Start = 'data:image/svg+xml;base64,';
				var image64 = b64Start + svg64;
				mapCache = image64;
			}
			if(callback && typeof callback == "function"){
				eval(callback(mapCache));
			}			
		}
	
}	
function getZoomIn(){	
	window.panZoom.zoomIn();
}
function getZoomOut(){
	window.panZoom.zoomOut();
}
function getZoomReset(){
	window.panZoom.reset();
}
function layeringPolygon(g){
	viewport = document.getElementById("viewport");
	svg = viewport.parentNode;
	svg.appendChild(g);
}
function layeringPolygon_in(g,id){
	viewport = document.getElementById("viewport");
	viewport.appendChild(g);
	if(typeof id !== "undefined"){
		zoomtestblok(id);
	}
}
function reDrawSVG(g){
	showProgress();
	var imageSVG = "map/indonesia.svg";
	var ajax = new XMLHttpRequest();
	ajax.open("GET",imageSVG, true);
	ajax.send();
	ajax.onload = function(e) {
		hideProgress();
		Node = new DOMParser().parseFromString(ajax.responseText, "image/svg+xml");
		indonesia = Node.getElementsByTagName("svg")[0];
		// sisipkan 
		viewport = indonesia.getElementById("viewport");
		viewport.appendChild(g);	
		
		//reDraw
		//var serializer = new XMLSerializer();
		//var indonesia = serializer.serializeToString(indonesia);
		//var blob = new Blob([indonesia], {type: 'image/svg+xml'}); 
		//var objectURL = URL.createObjectURL(blob);
		//load back
		
		//loadSVG(objectURL);		
	}
}

function readMyTrack(){
	//createTrackLine();
db.transaction(function (tx) {
	tx.executeSql('CREATE TABLE IF NOT EXISTS gps_location ('+
	   'username TEXT,'+
	   'latitude TEXT,'+
	   'longitude TEXT,'+
	   'altitude TEXT,'+
	   'devicename TEXT,'+
	   'tanggal TEXT,'+
	   'waktu TEXT,'+
	   'synchronized TEXT)',[],null,function(tx,error){errorHandler(tx,error);});
	//console.log("2");
	qry1 ="select tanggal from gps_location where upper(username) = '"+sessionStorage.username.toUpperCase()+"' order by tanggal,waktu DESC limit 1";   
	tx.executeSql(qry1, [], function(tx, rs){
		//console.log("3");
		if(rs.rows.length > 0){   
			tanggal = rs.rows.item(0).tanggal;
			qry ="select latitude,longitude from gps_location where upper(username) = '"+sessionStorage.username.toUpperCase()+"' and tanggal = '"+tanggal+"' order by tanggal,waktu ASC";
			tx.executeSql(qry, [], function(tx, rs){
				if(rs.rows.length > 0){
					var data = new Array();
					for(i=0; i<rs.rows.length; i++){
						data.push({'latitude':rs.rows.item(i).latitude,'longitude':rs.rows.item(i).longitude});
					}
					//console.log(data);
					createTrackLine(data);
				}
			}, function(tx,error){errorHandler(tx,error);});
		}else{
			hideProgress();
			//console.log("no data");
		}
	}, function(tx,error){errorHandler(tx,error);});
},null,null);	
}
function createTrackLine(data) {
	/*var data = [{'latitude':-6.395962,'longitude':106.835529},
				{'latitude':-6.403012,'longitude':106.838575},
				{'latitude':-6.524478,'longitude':106.981304},
				{'latitude':-6.496357,'longitude':107.076034},
				{'latitude':-6.489313,'longitude':107.126944},
				{'latitude':-6.376956,'longitude':107.064674},
				{'latitude':-6.308121,'longitude':106.933150}];*/
	
	var g = document.createElementNS('http://www.w3.org/2000/svg', 'g');
	var svgNS = g.namespaceURI;	
	if(data.length>1){
		//x1 = data[0].longitude;
		//y1 = (data[0].latitude*-1);
		//<polyline points="30,40 40,25 60,40 80,120 120,140 200,180" style="fill:none;stroke:black;stroke-width:3" />
		var polyline = document.createElementNS(svgNS,'polyline');
		strokeWidth = 0.0009;
		polyline.id = "userline";
		polyline.style.fill = "none";
		polyline.style.stroke = "#e91e63";
		polyline.style.strokeWidth = strokeWidth;
		var xY = new Array();
		for(i=0; i<data.length; i++){
			x = data[i].longitude;
			y = (data[i].latitude*-1);			
			line = x+","+y;
			xY.push(line);
			/*
			newLine.setAttribute('class','linetrack');
			newLine.setAttribute('x1',x1);
			newLine.setAttribute('y1',y1);
			newLine.setAttribute('x2',x2);
			newLine.setAttribute('y2',y2);
			newLine.setAttribute("stroke", "black");
			newLine.setAttribute("stroke-width",zRLine); 
			x1 = x2;
			y1 = y2;*/
		}
		cir = document.createElementNS('http://www.w3.org/2000/svg', "circle");
		cir.setAttribute("cx",data[0].longitude); 
		cir.setAttribute("cy",(data[0].latitude*-1)); 
		cir.setAttribute("fill","#e91e63");
		cir.setAttribute("r",(strokeWidth*2));
		point = xY.join(" ");
		polyline.setAttribute('points',point);
		g.appendChild(cir);
		g.appendChild(polyline);
		layeringPolygon_in(g);
	}				

}
function makeSVGEl(tag, attrs) {
    var el = document.createElementNS('http://www.w3.org/2000/svg', tag);
	if(typeof attrs != "undefined"){
		for (var k in attrs) {
		  el.setAttribute(k, attrs[k]);
		}
	}
    return el;
}
var viewGPSku;
var ZoomGetLocation = false;
var isPaused = false;
function getLocation(){
	if(document.getElementById("userposition")){
		userSign = document.getElementById("userposition");
		userSign.style.fillOpacity = 0;
	}
	var ZoomGetLocation = false;
	var z = 2800;
	var optionsGPS = {
		enableHighAccuracy: true,
		maximumAge: 0,
		timeout: 5000
	};
	if(sessionStorage.latitude !=""){
		
	}
	if(typeof viewGPSku == "number"){
		if(sessionStorage.latitude !="" && sessionStorage.latitude !=0){
			zoomGetloc(sessionStorage.latitude,sessionStorage.longitude);
			createPosition(sessionStorage.latitude,sessionStorage.longitude);
		}
	}else{
		if(sessionStorage.latitude !="" && sessionStorage.latitude !=0){
			zoomGetloc(sessionStorage.latitude,sessionStorage.longitude);
		
			navigator.geolocation.getCurrentPosition(successGPS1, errorGPS1, optionsGPS);
			//zoomGetloc(sessionStorage.latitude,sessionStorage.longitude);
			readMyTrack();
			viewGPSku = setInterval(function(){
				if(!isPaused) {
					navigator.geolocation.getCurrentPosition(successGPS, errorGPS, optionsGPS);
				}
			},2000);
		}else{
			navigator.geolocation.getCurrentPosition(successGPS1, errorGPS1, optionsGPS);
			getLocation();
		}
	}
	
	function successGPS1(pos){
		var crd = pos.coords;
		var lat = crd.latitude;
		var lng = crd.longitude;
		var alt = crd.altitude;
		var acc = crd.accuracy;
		sessionStorage.latitude 	= lat;
		sessionStorage.longitude 	= lng;
		sessionStorage.altitude 	= alt;
		sessionStorage.accuracy 	= acc;
		createPosition(sessionStorage.latitude,sessionStorage.longitude);
	}
	function errorGPS1(err){
		sessionStorage.latitude 	= -6.4379798;
		sessionStorage.longitude 	= 106.8537106;
		createPosition(sessionStorage.latitude,sessionStorage.longitude);
	}
	function successGPS(pos){
		var crd = pos.coords;
		var lat = crd.latitude;
		var lng = crd.longitude;
		var alt = crd.altitude;
		var acc = crd.accuracy;
		sessionStorage.latitude 	= lat;
		sessionStorage.longitude 	= lng;
		sessionStorage.altitude 	= alt;
		sessionStorage.accuracy 	= acc;
		createPosition(sessionStorage.latitude,sessionStorage.longitude);
	}

	function errorGPS(err){
		sessionStorage.latitude 	= -6.4379798;
		sessionStorage.longitude 	= 106.8537106;
		createPosition(sessionStorage.latitude,sessionStorage.longitude);
	}
	
	function zoomGetloc(latitude,longitude){
		z = 7900;
		var s = panZoom.getSizes();
		var p = panZoom.getPan();
		x = longitude; //106.8537106
		y = (parseFloat(latitude)*-1); //6.4379798
		panZoom.pan({
			x:-(x * s.realZoom) + (s.width/2),
			y:-(y * s.realZoom) + (s.height/2)
		});
		panZoom.zoom((z),false);
		
		
	}
	function createPosition(latitude,longitude){
		//var jarak = measure(firstLat, firstLong, latitude, longitude);
		//document.getElementById("showmeter").innerHTML = jarak+" m";
		var p = panZoom.getPan();
		var s = panZoom.getSizes();
		position = {'coords':{'latitude':latitude,'longitude':longitude}};
		showPosition(position,p,s);
	}
	
}
function showPosition(position,p,s) {
		//console.log(panZoom.getSizes());
		x = position.coords.longitude; //106.8537106
		y = (parseFloat(position.coords.latitude)*-1); //6.4379798
		imgW = 25; 
		imgH = 40; 
		sImgW = ((20/s.width)*20)*imgW;
		sImgH = ((20/s.width)*20)*imgH;
		zR = (s.width/s.realZoom)/70;
		zRStroke = zR-(zR*0.5)
		//x = p.x+(x * s.realZoom);
		//y = p.y+(y * s.realZoom);
		yC = y;
		xC = x;
		yImg = y-sImgH;
		xImg = x-(sImgW/2);
		//var xPin = position.coords.longitude-(sPinR/4);
		//var yPin = (position.coords.latitude*-1)-(sPinR-(sPinR/20));
		if(document.getElementById("user") == null){
			g = document.createElementNS('http://www.w3.org/2000/svg', "g");
			g.id = "user";
			userSign = document.createElementNS('http://www.w3.org/2000/svg', "circle");
			userSign.id ="userposition";
			//userSign.className ="zoom-switch element";
			userSign.setAttribute("class","element"); 
			userSign.setAttribute("cx",xC); 
			userSign.setAttribute("cy",yC); 
			userSign.setAttribute("fill","#07c");
			userSign.setAttribute("stroke","#9ecbed66");
			userSign.setAttribute("stroke-width",zRStroke); 
			userSign.setAttribute("r",zR);
			userSign.style.fillOpacity = 1;
			g.appendChild(userSign);
			
			image = document.createElementNS('http://www.w3.org/2000/svg', "image");
			image.setAttributeNS("http://www.w3.org/1999/xlink","xlink:href","map/mml_001.png");
			image.setAttributeNS(null,"id","pinmylocation");
			image.setAttributeNS(null,"class","element");
			image.setAttributeNS(null,"width",'0.4');
			image.setAttributeNS(null,"height",'0.4');
			
			image.setAttributeNS(null,"x","111.31406092818234");
			image.setAttributeNS(null,"y","2.1803347129720718");
			image.setAttributeNS(null,"coordonateX",position.coords.longitude);
			image.setAttributeNS(null,"coordonateY",(position.coords.latitude*-1));
			g.appendChild(image);
			layeringPolygon_in(g);
			
		}else{
			userSign = document.getElementById("userposition");
			//console.log(userSign);
			//userSign.setAttribute("cx",xC); 
			//userSign.setAttribute("cy",yC); 
			userSign.setAttribute("stroke-width",zRStroke); 
			userSign.setAttribute("r",zR);
			userSign.style.fillOpacity = 1;
			//panZoom.transformSecond(userSign,['stroke-width','r']);
			//image = document.getElementById("pinmylocation");
			//image.setAttributeNS(null,"x",xImg);
			//image.setAttributeNS(null,"y",yImg);
			isPaused = false;
		}
		
		//addPin("#ff9292","#9ecbed66",x,y);
}
function stabiliZationPin(p,s,z){
	position = {'coords':{'latitude':sessionStorage.latitude,'longitude':sessionStorage.longitude}};
	if(document.getElementById("user") !== null){
		showPosition(position,p,s);
	}
	/*
	linetrack = document.getElementsByClassName("linetrack");
	if(linetrack.length > 0){
		for(i=0;i<linetrack.length; i++){
			linetrack[i].setAttribute("stroke-width",zRLine); 
		}
		
	}*/
}
function savebase64AsImageFile(folderpath,filename,content,contentType){
    // Convert the base64 string in a Blob
    var DataBlob = b64toBlob(content,contentType);
    
    console.log(DataBlob);
    
    window.resolveLocalFileSystemURL(folderpath, function(dir) {
        console.log("Access to the directory granted succesfully");
		dir.getFile(filename, {create:true}, function(file) {
            console.log("File created succesfully.");
            file.createWriter(function(fileWriter) {
                console.log("Writing content to file");
                fileWriter.write(DataBlob);
            }, function(){
                alert('Unable to save file in path '+ folderpath);
            });
		});
    });
}
function zoomin(){
	var canvas = document.getElementById("ruangmap");
	var ctx = canvas.getContext('2d');
	console.log(ctx.getSVG());
}
function loadSVG(mytrack_svg){
	var bodyCanvas = document.getElementById("body_gpsCanvas");
	bodyCanvas.style.overflow = "hidden";
	var canvas = document.getElementById("ruangmap");
	canvas.width = bodyCanvas.clientWidth*2;
	canvas.height = bodyCanvas.clientHeight*2;
	var img = new Image;
	img.style.objectFit = "contain";
	img.onload = function(){	
		var ctx = canvas.getContext('2d');
		hideProgress();
		trackTransforms(ctx);
		function redraw(){
			// Clear the entire canvas
			var p1 = ctx.transformedPoint(0,0);
			var p2 = ctx.transformedPoint(canvas.width,canvas.height);
			ctx.clearRect(p1.x,p1.y,p2.x-p1.x,p2.y-p1.y);

			ctx.save();
			ctx.setTransform(1,0,0,1,0,0);
			ctx.clearRect(0,0,canvas.width,canvas.height);
			ctx.restore();
			ctx.drawImage(img,0,0);

		}
		redraw();
		
		var lastX=canvas.width/2, lastY=canvas.height/2;
		var dragStart,dragged;
		// start ===
		function start(evt){
			bodyCanvas.style.mozUserSelect = bodyCanvas.webkitUserSelect = bodyCanvas.style.userSelect = 'none';
			if(typeof evt.targetTouches[0] !== "undefined"){
				x = evt.targetTouches[0].pageX;
				y = evt.targetTouches[0].pageY;
				lastX = (x - canvas.offsetLeft);
				lastY = (y - canvas.offsetTop);
			}
			//console.log(lastX,lastY);
			dragStart = ctx.transformedPoint(lastX,lastY);
			console.log("start");
			dragged = false;
			
		}
		function movit(evt){
			if(typeof evt.targetTouches[0] !== "undefined"){
				x = evt.targetTouches[0].pageX;
				y = evt.targetTouches[0].pageY;
				lastX = (x - canvas.offsetLeft);
				lastY = (y - canvas.offsetTop);
			}
			var pt = ctx.transformedPoint(lastX,lastY);
			ctx.translate(pt.x-dragStart.x,pt.y-dragStart.y);
			redraw();
			dragged = true;
			console.log("move");
		}
		function stopMove(evt){
			console.log("stop");
			if (dragStart){
				var pt = ctx.transformedPoint(lastX,lastY);
				ctx.translate(pt.x-dragStart.x,pt.y-dragStart.y);
				redraw();
			}
			dragStart = null;
		   // if (!dragged) zoom(evt.shiftKey ? -1 : 1 ); console.log(evt.shiftKey);
		}
		//for Mobile ==============
		  canvas.addEventListener('touchstart',function(evt){
			  start(evt);
			  tapHandler(evt);
		  },false);
			canvas.addEventListener('touchmove',function(evt){
			  movit(evt);
		  },false);
			canvas.addEventListener('touchend',function(evt){
			  stopMove(evt);
		  },false);
		   var scaleFactor = 1.1;
		  var zoom = function(clicks){
			  var pt = ctx.transformedPoint(lastX,lastY);
			  ctx.translate(pt.x,pt.y);
			  var factor = Math.pow(scaleFactor,clicks);
			  ctx.scale(factor,factor);
			  ctx.translate(-pt.x,-pt.y);
			  redraw();
		  }
		  var handleScroll = function(evt){
          var delta = evt.wheelDelta ? evt.wheelDelta/40 : evt.detail ? -evt.detail : 0;
          if (delta) zoom(delta);
          return evt.preventDefault() && false;
		  };
		
		  canvas.addEventListener('DOMMouseScroll',handleScroll,false);
		  canvas.addEventListener('mousewheel',handleScroll,false);

		  document.getElementById('getZoomIn').addEventListener('click',function(){
			  console.log("zoom in");
			  zoom(1);
		  },false);
		   document.getElementById('getZoomOut').addEventListener('click',function(){
			  console.log("zoom out");
			  zoom(-1);
		  },false);
		  
		  // double tap
		  var tapedTwice = false;

		function tapHandler(event) {
			if(!tapedTwice) {
				tapedTwice = true;
				setTimeout( function() { tapedTwice = false; }, 300 );
				return false;
			}
			event.preventDefault();
			//action on double tap goes below
			zoom(2);
		 }
	};
	img.src = mytrack_svg;

}
function trackTransforms(ctx){
	var svg = document.createElementNS("http://www.w3.org/2000/svg",'svg');
	var xform = svg.createSVGMatrix();
	ctx.getTransform = function(){ return xform; };

	var savedTransforms = [];
	var save = ctx.save;
	ctx.save = function(){
	  savedTransforms.push(xform.translate(0,0));
	  return save.call(ctx);
	};

	var restore = ctx.restore;
	ctx.restore = function(){
	xform = savedTransforms.pop();
	return restore.call(ctx);
		  };

	var scale = ctx.scale;
	ctx.scale = function(sx,sy){
	xform = xform.scaleNonUniform(sx,sy);
	return scale.call(ctx,sx,sy);
	};

	var rotate = ctx.rotate;
	ctx.rotate = function(radians){
	  xform = xform.rotate(radians*180/Math.PI);
	  return rotate.call(ctx,radians);
	};

	var translate = ctx.translate;
	ctx.translate = function(dx,dy){
	  xform = xform.translate(dx,dy);
	  return translate.call(ctx,dx,dy);
	};

	var transform = ctx.transform;
	ctx.transform = function(a,b,c,d,e,f){
	  var m2 = svg.createSVGMatrix();
	  m2.a=a; m2.b=b; m2.c=c; m2.d=d; m2.e=e; m2.f=f;
	  xform = xform.multiply(m2);
	  return transform.call(ctx,a,b,c,d,e,f);
	};

	var setTransform = ctx.setTransform;
	ctx.setTransform = function(a,b,c,d,e,f){
	  xform.a = a;
	  xform.b = b;
	  xform.c = c;
	  xform.d = d;
	  xform.e = e;
	  xform.f = f;
	  return setTransform.call(ctx,a,b,c,d,e,f);
	};

	var pt  = svg.createSVGPoint();
	ctx.transformedPoint = function(x,y){
	  pt.x=x; pt.y=y;
	  return pt.matrixTransform(xform.inverse());
	}
}
function __loadSVG(mytrack_svg){
	console.log(mytrack_svg);
	var bodyCanvas = document.getElementById("body_gpsCanvas");
	var canvas = document.getElementById("ruangmap");
	canvas.width = bodyCanvas.clientWidth*2;
	canvas.height = (bodyCanvas.clientHeight*2)-47;
	
	var img = new Image;
	img.onload = function(){	
		hideProgress();
	var ctx = canvas.getContext('2d');
	trackTransforms(ctx);
	function redraw(){
		// Clear the entire canvas
		var p1 = ctx.transformedPoint(0,0);
		var p2 = ctx.transformedPoint(canvas.width,canvas.height);
		ctx.clearRect(p1.x,p1.y,p2.x-p1.x,p2.y-p1.y);

		ctx.save();
		ctx.setTransform(1,0,0,1,0,0);
		ctx.clearRect(0,0,canvas.width,canvas.height);
		ctx.restore();
		ctx.drawImage(img,0,0);

	}
    redraw();
    var lastX=canvas.width/2, lastY=canvas.height/2;
	var dragStart,dragged;
	// start ===
	function start(evt){
		bodyCanvas.style.mozUserSelect = bodyCanvas.webkitUserSelect = bodyCanvas.style.userSelect = 'none';
		x = (evt.targetTouches) ? evt.targetTouches[0].pageX : evt.pageX
		y = (evt.targetTouches) ? evt.targetTouches[0].pageY : evt.pageY
		lastX = evt.offsetX;
		lastY = evt.offsetY;
		console.log(lastX,lastY);
		dragStart = ctx.transformedPoint(lastX,lastY);
		console.log("start");
		dragged = false;
		
	}
	function movit(evt){
		x = (evt.targetTouches) ? evt.targetTouches[0].pageX : evt.pageX
		y = (evt.targetTouches) ? evt.targetTouches[0].pageY : evt.pageY
		lastX = evt.offsetX;
		lastY = evt.offsetY;
		console.log(lastX,lastY);
		dragged = true;
		console.log("move");
	}
	function stopMove(evt){
		console.log("stop");
		if (dragStart){
			var pt = ctx.transformedPoint(lastX,lastY);
			ctx.translate(pt.x-dragStart.x,pt.y-dragStart.y);
			redraw();
		}
		dragStart = null;
       // if (!dragged) zoom(evt.shiftKey ? -1 : 1 ); console.log(evt.shiftKey);
	}
      
      canvas.addEventListener('mousedown',function(evt){
           start(evt);
      },false);

      canvas.addEventListener('mousemove',function(evt){
          movit(evt);
      },false);

      canvas.addEventListener('mouseup',function(evt){
          stopMove(evt);
      },false);
	  
	  
	  //for Mobile ==============
	  canvas.addEventListener('touchstart',function(evt){
		  start(evt);
	  },false);
		canvas.addEventListener('touchmove',function(evt){
		  movit(evt);
	  },false);
		canvas.addEventListener('touchend',function(evt){
		  stopMove(evt);
	  },false);

      var scaleFactor = 1.1;
		
      var zoom = function(clicks){
          var pt = ctx.transformedPoint(lastX,lastY);
          ctx.translate(pt.x,pt.y);
          var factor = Math.pow(scaleFactor,clicks);
          ctx.scale(factor,factor);
          ctx.translate(-pt.x,-pt.y);
          redraw();
      }

      var handleScroll = function(evt){
          var delta = evt.wheelDelta ? evt.wheelDelta/40 : evt.detail ? -evt.detail : 0;
          if (delta) zoom(delta);
          return evt.preventDefault() && false;
      };
    
      canvas.addEventListener('DOMMouseScroll',handleScroll,false);
      canvas.addEventListener('mousewheel',handleScroll,false);

	  document.getElementById('getZoomIn').addEventListener('click',function(){
		  console.log("zoom in");
		  zoom(1);
	  },false);
	   document.getElementById('getZoomOut').addEventListener('click',function(){
		  console.log("zoom out");
		  zoom(-1);
	  },false);
      //canvas.addEventListener('gesturechange',handleScroll,false);
	};

	img.src = mytrack_svg;

	// Adds ctx.getTransform() - returns an SVGMatrix
	// Adds ctx.transformedPoint(x,y) - returns an SVGPoint
 
	function trackTransforms(ctx){
		var svg = document.createElementNS("http://www.w3.org/2000/svg",'svg');
		var xform = svg.createSVGMatrix();
		ctx.getTransform = function(){ return xform; };

		var savedTransforms = [];
		var save = ctx.save;
		ctx.save = function(){
		  savedTransforms.push(xform.translate(0,0));
		  return save.call(ctx);
		};

		var restore = ctx.restore;
		ctx.restore = function(){
		xform = savedTransforms.pop();
		return restore.call(ctx);
			  };

		var scale = ctx.scale;
		ctx.scale = function(sx,sy){
		xform = xform.scaleNonUniform(sx,sy);
		return scale.call(ctx,sx,sy);
		};

		var rotate = ctx.rotate;
		ctx.rotate = function(radians){
		  xform = xform.rotate(radians*180/Math.PI);
		  return rotate.call(ctx,radians);
		};

		var translate = ctx.translate;
		ctx.translate = function(dx,dy){
		  xform = xform.translate(dx,dy);
		  return translate.call(ctx,dx,dy);
		};

		var transform = ctx.transform;
		ctx.transform = function(a,b,c,d,e,f){
		  var m2 = svg.createSVGMatrix();
		  m2.a=a; m2.b=b; m2.c=c; m2.d=d; m2.e=e; m2.f=f;
		  xform = xform.multiply(m2);
		  return transform.call(ctx,a,b,c,d,e,f);
		};

		var setTransform = ctx.setTransform;
		ctx.setTransform = function(a,b,c,d,e,f){
		  xform.a = a;
		  xform.b = b;
		  xform.c = c;
		  xform.d = d;
		  xform.e = e;
		  xform.f = f;
		  return setTransform.call(ctx,a,b,c,d,e,f);
		};

		var pt  = svg.createSVGPoint();
		ctx.transformedPoint = function(x,y){
		  pt.x=x; pt.y=y;
		  return pt.matrixTransform(xform.inverse());
		}
	}
}	
	
	