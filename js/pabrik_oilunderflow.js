function inputData(fileform){
	if(fileform.tanggal.value == "" ||	 fileform.pabrik.value == ""){
		alert("Data tidak Lengkap.!");
		return false;
	}
	
	busy_on();
	var xhr = new XMLHttpRequest();
	xhr.onload = respon;
	xhr.open("post", 'pabrik_slave_oilunderflow.php?proses=insert');
	xhr.send(new FormData(fileform));
	function respon() {
        if (this.readyState == 4) {
            if (this.status == 200) {
                busy_off();
                if (!isSaveResponse(this.responseText)) {
                    alert(this.responseText);
                }else{
					try{
						var dataArr = JSON.parse(this.responseText);
						if(dataArr.err !== 'false'){
							alert(dataArr.err);
						}else{
							getSlave();
						}
						fileform.oil.value="";
						fileform.moisture.value="";
						fileform.sludge.value="";
						fileform.keterangan.value="";
					}catch (e) {
						console.log(this.responseText);
					}
				}
			}else {
                busy_off();
                error_catch(this.status);
            }
		}
	}
}
function getSlave(switchcase,ele,valuefor){
	var param = "";
	var prosees = "";
	var workwarp = document.getElementById('workwarp');
	var tanggal = document.getElementById('tanggal');
	var pabrik = document.getElementById('pabrik');
	var vr = "";
	if(typeof valuefor !== 'undefined'){
		vr = valuefor;
	}
	if(typeof switchcase !== 'undefined'){
		prosees = "?proses="+switchcase;
	}else{
		param = "tanggal="+tanggal.value;
		param += "&pabrik="+pabrik.value;
	}
	if(typeof ele !== 'undefined'){
		data = ele.getAttribute('data').split(',');
		par = ele.getAttribute('param').split(',');
		param = par[0]+"="+data[0];
		for(i=1; i<data.length; i++){
			param += "&"+par[i]+"="+data[i];
		}
	}
	post_response_text('pabrik_slave_oilunderflow.php'+prosees, param, respon);
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					//=== Success Response
					if(typeof switchcase !== 'undefined'){
						if(switchcase == 'select'){
							catchDataToEdit(con.responseText);
						}else{
							try{
								var dataArr = JSON.parse(con.responseText);
								if(dataArr.err == 'false'){
									getSlave();
								}else{
									alert(dataArr.err);
								}
							}catch(e){
								var data	= con.responseText;
								alert(e+":"+data);
							}
						}
					}else{
						workwarp.innerHTML = con.responseText;
					}
					
				}
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function deleteList(e){
	getSlave('delete',e);
}
function editList(e){
	getSlave('select',e);
}
function catchDataToEdit(data){
	try{
		var dataArr = JSON.parse(data);
		if(dataArr.err == 'false'){
			var form = document.getElementById('insert_underflow');
			data = dataArr.data[0];
			newtanggal = data.tanggal.split("-");
			form.tanggal.value = newtanggal[2]+"-"+newtanggal[1]+"-"+newtanggal[0];
			form.oil.value = data.oil;
			form.moisture.value = data.moisture;
			form.sludge.value = data.sludge;
			form.keterangan.value = data.keterangan;
		}else{
			alert(dataArr.err);
		}
	}catch(e){
		alert(e+":"+data);
	}
}
function getdata(){
	var tanggal = document.getElementById('tanggal');
	var pabrik = document.getElementById('pabrik');
	if(tanggal.value !=="" && pabrik.value !=="" ){
		getSlave();
	}
}