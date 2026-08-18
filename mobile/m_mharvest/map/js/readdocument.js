var mimeType = "application/pdf";
var options = {
	title: "Neraca",
	documentView : {
		closeLabel : "Neraca View"
	},
	navigationView : {
		closeLabel : "Neraca Nav"
	},
	email : {
		enabled : false
	},
	print : {
		enabled : true
	},
	openWith : {
		enabled : true
	},
	bookmarks : {
		enabled : false
	},
	search : {
		enabled : false
	},
	autoClose: {
		onPause : true
	}
}
//cordova.plugins.SitewaertsDocumentViewer.canViewDocument(url, mimeType, options, onPossible, onMissingApp, onImpossible, onError);

function onMissingApp(appId, installer)
	if(confirm("Do you want to install the free PDF Viewer App "+appId+" for Android?")){
		installer();
	}
} 
function onError(error){
	console.log(error);
	notifAlert("Sorry! Cannot view document.");
}
function onClose(){
	console.log('document closed');
  //e.g. remove temp files
}
function onShow(){
	console.log('document shown');
  //e.g. track document usage
}
var successTrans = function (r) {
	hideProgress();
	notifAlert("Success Download File "+r.name,"Success");
}
var failureTrans = function (error) {
	hideProgress();
	notifAlert("An error has occurred: Code = " + error.code,"Error");
}