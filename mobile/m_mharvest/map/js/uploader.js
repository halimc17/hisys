function tryUploadPhoto2(test){
	 var col=['file'];
        strSelecty='SELECT * FROM kebun_spbdt order by blok';
        db.transaction(function (tx) {
                tx.executeSql(strSelecty, [], function(tx, rs){  
                  var data=new Array();
                  for(var i=0; i<rs.rows.length; i++) {
                        data[i][0] =rs.rows.item(i).sFilename;
                  }; 
                  sendImage2(col,data,test);  
                }, function(tx,error){
                  errorHandler(tx,error);
                });
          },null,null);
    //get File on spb

}
function toDataURL(url, callback) {
  var xhr = new XMLHttpRequest();
  xhr.onload = function() {
    var reader = new FileReader();
    reader.onloadend = function() {
      callback(reader.result);
    }
    reader.readAsDataURL(xhr.response);
  };
  xhr.open('GET', url);
  xhr.responseType = 'blob';
  xhr.send();
}
function sendImage2(col,data,id){
	 for(var i=0; i<data.length; i++) {
		 toDataURL(data[i][0]);
	 }
	printTable(col,data,id,'');

}
function callback(data){
	alert(data);
}