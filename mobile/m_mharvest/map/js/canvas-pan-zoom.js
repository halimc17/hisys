/** Creator : Atwal 
 canvas-pan-zoom v.1.0.0
 **/
 (function e(t,n,r){function s(o,u){if(!n[o]){if(!t[o]){var a=typeof require=="function"&&require;if(!u&&a)return a(o,!0);if(i)return i(o,!0);var f=new Error("Cannot find module '"+o+"'");throw f.code="MODULE_NOT_FOUND",f}var l=n[o]={exports:{}};t[o][0].call(l.exports,function(e){var n=t[o][1][e];return s(n?n:e)},l,l.exports,e,t,n,r)}return n[o].exports}var i=typeof require=="function"&&require;for(var o=0;o<r.length;o++)s(r[o]);return s})({1:[function(require,module,exports){
	var canvasPanZoom = require('./canvas-pan-zoom.js');
	// UMD module definition
	//console.log(canvasPanZoom);
	
	(function(window, document){
	  // AMD
	  if (typeof define === 'function' && define.amd) {
			define('canvas-pan-zoom', function () {
		  return canvasPanZoom;
		});
	  // CMD
	  } else if (typeof module !== 'undefined' && module.exports) {
		module.exports = canvasPanZoom;

		// Browser
		// Keep exporting globally as module.exports is available because of browserify
		window.canvasPanZoom = canvasPanZoom;
	  }
	})(window, document)

},{"./canvas-pan-zoom.js":3}],2:[function(require,module,exports){
	module.exports = {
		/**
		* Extends an object
		*
		* @param  {Object} target object to extend
		* @param  {Object} source object to take properties from
		* @return {Object}        extended object
		*/
		extend: function(target, source) {
			target = target || {};
			for (var prop in source) {
			  // Go recursively
			  if (this.isObject(source[prop])) {
				target[prop] = this.extend(target[prop], source[prop])
			  } else {
				target[prop] = source[prop]
			  }
			}
		return target;
		}

		/**
		* Checks if an object is a DOM element
		*
		* @param  {Object}  o HTML element or String
		* @return {Boolean}   returns true if object is a DOM element
		*/
		, isElement: function(o){
			return (
			  o instanceof HTMLElement || o instanceof SVGElement || o instanceof SVGSVGElement || //DOM2
			  (o && typeof o === 'object' && o !== null && o.nodeType === 1 && typeof o.nodeName === 'string')
			);
		}

		/**
		* Checks if an object is an Object
		*
		* @param  {Object}  o Object
		* @return {Boolean}   returns true if object is an Object
		*/
		, isObject: function(o){
			return Object.prototype.toString.call(o) === '[object Object]';
		}
		/**
	   * Check if an event is a double click/tap
	   * TODO: For touch gestures use a library (hammer.js) that takes in account other events
	   * (touchmove and touchend). It should take in account tap duration and traveled distance
	   *
	   * @param  {Event}  evt
	   * @param  {Event}  prevEvt Previous Event
	   * @return {Boolean}
	   */
		,isDblClick: function(evt, prevEvt) {
			// Double click detected by browser
			if (evt.detail === 2) {
			  return true;
			}
			// Try to compare events
			else if (prevEvt !== void 0 && prevEvt !== null) {
			  var timeStampDiff = evt.timeStamp - prevEvt.timeStamp // should be lower than 250 ms
				, touchesDistance = Math.sqrt(Math.pow(evt.clientX - prevEvt.clientX, 2) + Math.pow(evt.clientY - prevEvt.clientY, 2))
			console.log(evt,touchesDistance);
			  return timeStampDiff < 250 && touchesDistance < 10
			}

			// Nothing found
			return false;
		}
		/**
	   * If it is a touch event than add clientX and clientY to event object
	   *
	   * @param  {Event} evt
	   * @param  canvas
	   */
	, mouseAndTouchNormalize: function(evt, canvas) {
		// If no cilentX and but touch objects are available
		if (evt.clientX === void 0 || evt.clientX === null) {
		  // Fallback
		  evt.clientX = 0
		  evt.clientY = 0

		  // If it is a touch event
		  if (evt.changedTouches !== void 0 && evt.changedTouches.length) {
			// If touch event has changedTouches
			if (evt.changedTouches[0].clientX !== void 0) {
			  evt.clientX = evt.changedTouches[0].clientX
			  evt.clientY = evt.changedTouches[0].clientY
			}
			// If changedTouches has pageX attribute
			else if (evt.changedTouches[0].pageX !== void 0) {
			  var rect = canvas.getBoundingClientRect();

			  evt.clientX = evt.changedTouches[0].pageX - rect.left
			  evt.clientY = evt.changedTouches[0].pageY - rect.top
			}
		  // If it is a custom event
		  } else if (evt.originalEvent !== void 0) {
			if (evt.originalEvent.clientX !== void 0) {
			  evt.clientX = evt.originalEvent.clientX
			  evt.clientY = evt.originalEvent.clientY
			}
		  }
		}
	  }
		/**
		* Checks if variable is Number
		*
		* @param  {Integer|Float}  n
		* @return {Boolean}   returns true if variable is Number
		*/
		, isNumber: function(n) {
			return !isNaN(parseFloat(n)) && isFinite(n);
		}, 
		getSvg: function(elementOrSelector) {
			var element, svg;
			if (!this.isElement(elementOrSelector)) {
			  // If selector provided
			  if (typeof elementOrSelector === 'string' || elementOrSelector instanceof String) {
				// Try to find the element
				element = document.querySelector(elementOrSelector)

				if (!element) {
				  throw new Error('Provided selector did not find any elements. Selector: ' + elementOrSelector)
				  return null
				}
			  } else {
				throw new Error('Provided selector is not an HTML object nor String')
				return null
			  }
			} else {
			  element = elementOrSelector
			}

			if (element.tagName.toLowerCase() === 'svg') {
			  svg = element;
			} else {
			  if (element.tagName.toLowerCase() === 'object') {
				svg = element.contentDocument.documentElement;
			  } else {
				if (element.tagName.toLowerCase() === 'embed') {
				  svg = element.getSVGDocument().documentElement;
				} else {
				  if (element.tagName.toLowerCase() === 'img') {
					throw new Error('Cannot script an SVG in an "img" element. Please use an "object" element or an in-line SVG.');
				  } else {
					throw new Error('Cannot get SVG.');
				  }
				  return null
				}
			  }
			}

			return svg
		 },
		 setSVGToImage: function(svg) {
			var ele = this.getSvg(svg);
			var xml = (new XMLSerializer).serializeToString(ele);
			var svg64 = btoa(xml);
			var svgSrc = 'data:image/svg+xml;base64,'+svg64;
			//svgSrc = "data:image/svg+xml;charset=utf-8,"+xml;
			return svgSrc
		 },
		 getCanvas: function(elementOrSelector) {
			var element, canvas;
			if (!this.isElement(elementOrSelector)) {
			  // If selector provided
			  if (typeof elementOrSelector === 'string' || elementOrSelector instanceof String) {
				// Try to find the element
				element = document.querySelector(elementOrSelector)

				if (!element) {
				  throw new Error('Provided selector did not find any elements. Selector: ' + elementOrSelector)
				  return null
				}
			  } else {
				throw new Error('Provided selector is not an HTML object nor String')
				return null
			  }
			} else {
			  element = elementOrSelector
			}

			if (element.tagName.toLowerCase() === 'canvas') {
			  canvas = element;
			} else {
			  if (element.tagName.toLowerCase() === 'object') {
				canvas = element.contentDocument.documentElement;
			  } else {
				 throw new Error('Cannot get Canvas Element.');
			  }
			}

			return canvas
		 }
	}
},{}],3:[function(require,module,exports){
	var Utils = require('./utilities');
	var optionsDefaults = {
		  zoomScaleSensitivity: 0.2 // Zoom sensitivity
		, dblClickZoomEnabled: true
		, minZoom: 1
		, maxZoom: 10000 
		, fit: true
		, contain: false 
		, center: true 
		, width : 800
		, height : 600
		, evCache: 0
		, prevDiff: -1
	}
	var CanvasPanZoom = function(svg, options) {
	  this.init(svg, options)
	}
	/*** Register event handlers */
	CanvasPanZoom.prototype.setupHandlers = function() {
		var that = this, prevEvt = null; // use for touchstart event to detect double tap

		this.eventListeners = {
			touchstart: function(evt) {
				var result = that.handleTouchDown(evt, prevEvt);
				prevEvt = evt
				return result;
			}, touchend: function(evt) {
				return that.handleTouchUp(evt);
			}, touchmove: function(evt) {
				return that.handleTouchMove(evt);
			}, touchleave: function(evt) {
				return that.handleTouchUp(evt);
			}, touchcancel: function(evt) {
				return that.handleTouchUp(evt);
			}, mousewheel: function(evt) {
				return that.handleScroll(evt);
			}, DOMMouseScroll: function(evt) {
				return that.handleScroll(evt);
			}

		}
		// Bind eventListeners
		for (var event in this.eventListeners) {
			// Attach event to eventsListenerElement or SVG if not available
			(this.options.eventsListenerElement || this.canvas).addEventListener(event, this.eventListeners[event], false)
		}

		// Zoom using mouse wheel
		if (this.options.mouseWheelZoomEnabled) {
			this.options.mouseWheelZoomEnabled = false // set to false as enable will set it back to true
			this.enableMouseWheelZoom()
		}
	}
	
	CanvasPanZoom.prototype.init = function(svg, options) {
		var that = this

		this.svg = svg
		var bodyCanvas = document.getElementById("home_map");
		this.options = Utils.extend(Utils.extend({}, optionsDefaults), options)
		this.canvas = Utils.getCanvas(this.options.canvas)
		this.canvas.width = bodyCanvas.clientWidth*2;
		this.canvas.height = bodyCanvas.clientHeight*2;
		this.gkhead = new Image;
		this.ctx = this.canvas.getContext('2d');
		this.trackTransforms(this.ctx,this.svg);
		
		
		 // Set options
		// Set default state
		this.state = 'none'
		// Wrap callbacks into public API context
		var publicInstance = this.getPublicInstance()
		
		// Process Redraw
		this.lastX=this.canvas.width/2
		this.lastY=this.canvas.height/2
		this.dragStart
		this.dragged
		this.scaleFactor = 1.1
		this.loadSVG(this.svg)
		this.setupHandlers()
	}
	CanvasPanZoom.prototype.getPublicInstance = function() {
	   var that = this

		// Create cache
		if (!this.publicInstance) {
		   this.publicInstance = this.pi = {
			  zoomIn: function() {that.publicZoom(1); return that.pi}
			, zoomOut: function() {that.publicZoom(-1); return that.pi}
			, addNewLayer: function(g){that.addNewLayer(g); return that.pi}  
			, getSVG: function(){return that.svg;}  
			   
		   }
		}
	   return this.publicInstance
	}
	CanvasPanZoom.prototype.addNewLayer = function(g){
		var svg = this.svg;
		viewport = svg.getElementById("viewport");
		viewport.appendChild(g);
		this.loadSVG(svg);
	}
	CanvasPanZoom.prototype.loadSVG = function(svg){
		var img = this.gkhead;
		img.style.objectFit = "contain";
		var trackTransforms = this.trackTransforms;
		var reDraw 			= this.reDraw;
		var canvas 			= this.canvas;
		var ctx 			= canvas.getContext('2d');
		img.onload = function(){	
			trackTransforms(ctx,svg);
			reDraw(ctx,this,canvas);
		}
		img.src = Utils.setSVGToImage(svg);
	}
	CanvasPanZoom.prototype.reDraw = function(ctx,img,canvas){
		 //var ctx = this.ctx.getContext('2d');
		// Clear the entire canvas
		if(typeof ctx === "undefined"){
			ctx = this.ctx;
		}
		if(typeof img === "undefined"){
			img = this.gkhead;
		}
		if(typeof canvas === "undefined"){
			canvas = this.canvas;
		}
		var p1 = ctx.transformedPoint(0,0);
		var p2 = ctx.transformedPoint(canvas.width,canvas.height);

		ctx.clearRect(p1.x,p1.y,p2.x-p1.x,p2.y-p1.y);

		ctx.save();
		ctx.setTransform(1,0,0,1,0,0);
		ctx.clearRect(0,0,canvas.width,canvas.height);
		ctx.restore();

		ctx.drawImage(img,0,0);
	}
	CanvasPanZoom.prototype.trackTransforms = function(ctx,svg) {
		//var svg = document.createElementNS("http://www.w3.org/2000/svg",'svg');
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
	
	CanvasPanZoom.prototype.handleDblClick = function(evt){
		this.zoom(evt.shiftKey ? -1 : 1 );
	}
	CanvasPanZoom.prototype.handleTouchDown = function(evt,prevEvt){
		// Double click detection; more consistent than ondblclick
		Utils.mouseAndTouchNormalize(evt, this.canvas);
		
		if (this.options.dblClickZoomEnabled && Utils.isDblClick(evt, prevEvt)){
			this.handleDblClick(evt)
		} else {
			document.body.style.mozUserSelect = document.body.style.webkitUserSelect = document.body.style.userSelect = 'none';
			var jml = evt.touches.length;
			if(jml == 1){
				this.lastX = evt.touches[0].clientX;
				this.lastY = evt.touches[0].clientY;
				this.dragStart = this.ctx.transformedPoint(this.lastX,this.lastY);
				this.dragged = false;
			 }
		  }
	}
	CanvasPanZoom.prototype.handleTouchMove = function(evt){
		 var jml = evt.touches.length;
		  if(jml == 1){
			  this.lastX = evt.touches[0].clientX;
			  this.lastY = evt.touches[0].clientY;
			  this.dragged = true;
			  if (this.dragStart){
				var pt = this.ctx.transformedPoint(this.lastX,this.lastY);
				this.ctx.translate(pt.x-this.dragStart.x,pt.y-this.dragStart.y);
				this.reDraw();
			  }
		  }
		  
	}
	
	CanvasPanZoom.prototype.handleTouchUp = function(evt){
		this.dragStart = null;
        //if (!this.dragged) this.zoom(evt.shiftKey ? -1 : 1 );
	}
	CanvasPanZoom.prototype.handleScroll = function(evt){
          var delta = evt.wheelDelta ? evt.wheelDelta/40 : evt.detail ? -evt.detail : 0;
          if (delta) this.zoom(delta);
          return evt.preventDefault() && false;
      
	}
	CanvasPanZoom.prototype.zoom = function(clicks){
          var pt = this.ctx.transformedPoint(this.lastX,this.lastY);
          this.ctx.translate(pt.x,pt.y);
          var factor = Math.pow(this.scaleFactor,clicks);
          this.ctx.scale(factor,factor);
          this.ctx.translate(-pt.x,-pt.y);
          this.reDraw();
    }
	CanvasPanZoom.prototype.publicZoom = function(scale) {
		this.zoom(scale)
	}
	
	var instancesStore = []
	var canvasPanZoom = function(elementOrSelector, options) {
		var svg = Utils.getSvg(elementOrSelector);
		if (svg === null) {
			return null
		}else {
		    for(var i = instancesStore.length - 1; i >= 0; i--) {
			  if (instancesStore[i].svg === svg) {
				return instancesStore[i].instance.getPublicInstance()
			  }
			}
			 instancesStore.push({
			  svg: svg, instance: new CanvasPanZoom(svg, options)
			})
			// Return just pushed instance
			return instancesStore[instancesStore.length - 1].instance.getPublicInstance()
		}
		
		
	}

module.exports = canvasPanZoom;

},{"./utilities":2}]},{},[1]);
 
 
