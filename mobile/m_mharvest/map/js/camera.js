
    var pictureSource;   // picture source
    var destinationType; // sets the format of returned value

    document.addEventListener("deviceready",onDeviceReady,false);

    function onDeviceReady() {
        pictureSource=navigator.camera.PictureSourceType;
        destinationType=navigator.camera.DestinationType;
    }

    function onPhotoDataSuccess(imageData) {
		base64 = "data:image/jpeg;base64,"+imageData;
		// Create original image
		var img = new Image();
		img.src = base64;
		img.onload = function() {
			var foto = resizeImg(img,300,300);
			var smallImage = document.getElementById('smallImage');
			smallImage.style.display = 'block';
			smallImage.src = foto.toDataURL('image/jpeg');
			setValue('spbLatitude',sessionStorage.latitude);
			setValue('spbLongitude',sessionStorage.longitude);
			setValue('spbAltitude',sessionStorage.altitude);
			setValue('spbAccuracy',sessionStorage.accuracy); 
		}
    }

    function capturePhoto() {
        // Take picture using device camera and retrieve image as base64-encoded string
        navigator.camera.getPicture(onPhotoDataSuccess, onFail, { quality: 20,
            correctOrientation:true,destinationType: destinationType.DATA_URL
             });
    }

    function capturePhotoEdit() {
        // Take picture using device camera, allow edit, and retrieve image as base64-encoded string
        navigator.camera.getPicture(onPhotoDataSuccess, onFail, { quality: 20, allowEdit: true,
            correctOrientation:true,destinationType: destinationType.DATA_URL });
    }

    function getPhoto(source) {
        // Retrieve image file location from specified source
        try{
			navigator.camera.getPicture(onPhotoURISuccess, onFail, { quality: 10,
            destinationType: destinationType.FILE_URI,
            sourceType: source,
            correctOrientation:true });
        }catch(e){
            alert('Aplikasi Anda tidak support dengan kamera');
        }
    }
	function onPhotoURISuccess(imageURI) {
		var largeImage = document.getElementById('smallImage');
		largeImage.style.display = 'block';
		largeImage.src = imageURI;
    }
	
    function onFail(message) {
        alert('Failed because: ' + message);
    }
	

