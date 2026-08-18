function inputData(fileform,funct,validation){
	eval(validation(fileform));
	var prosees = ""	
	if(typeof switchcase !== 'undefined'){
		prosees = "?"+switchcase;
	}
	busy_on();
	var xhr = new XMLHttpRequest();
	xhr.onload = respon;
	xhr.open("post", fileform.action);
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
						if(funct){
							eval(funct(dataArr,fileform));
						}
					}catch (e) {
						if(funct){
							eval(funct(this.responseText,fileform));
						}
					}
				}
			}else {
                busy_off();
                error_catch(this.status);
            }
		}
	}
}
//About Validation
function validationInsert(form){
	//validation
}
function afterInsert(data,ele){
	//result After insert
	loadlist();
}

function getSlave(switchcase,param,ele,funct) {
	var prosees = ""
	if(typeof switchcase !== 'undefined'){
		prosees = "?"+switchcase;
	}
	if(typeof param !== 'undefined'){
		param = param;
	}else{
		param = "";
	}
	post_response_text('sdm_slave_exit_interview.php'+prosees, param, respon);
	function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }else{
                    //=== Success Response
					if(funct){
						eval(funct(con.responseText,ele));
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function returnForm(data){
	alert(data);
}
function loadform(data,ele){
	var workwarp = getById(ele);
	workwarp.innerHTML = data;
}
function loaddata(e){
	var val_param = "karyawanid="+e.value;
	getSlave('proses=loadform',val_param,'workwarp',loadform);
}
function create_new_field(e){
	var parent = e.parentNode;
	var ol = parent.getElementsByTagName('ol')[0];
	var optexample = ol.getElementsByClassName('optexample');
	if(optexample.length == 10){
		alert("Maksimal List 10");
		return false;
	}
	var cloningField = optexample[0].cloneNode(true);
	inputEl  = cloningField.getElementsByTagName('input')[0];
	inputEl.value = "";
	var newField = cloningField;
	ol.appendChild(newField);
}
function replaceSpliter(e){
	var val = e.value;
	newVal = val.replace(/[^a-zA-Z0-9 ]/g,'');
	e.value = newVal.toUpperCase();
}
function loadlist(num){
	var txtsearch;
	if(typeof num !== 'undefined'){
		num = 1;
	}
	if(document.getElementById('txtsearch')){
		txtsearch = document.getElementById('txtsearch').value; 
	}
	var val_param = "txtsearch="+txtsearch;
	getSlave('proses=loadlist&hal='+num,val_param,'workwarp',loadform);
}
function displayFormInput(){
	getSlave('proses=loadform','','workwarp',loadform);
}
function posting(notrans){
	var val_param = "notransaksi="+notrans;
	getSlave('proses=posting',val_param,'workwarp',afterPosting);
	function afterPosting(data,ele){
		try{
			var dataArr = JSON.parse(data);
			if(dataArr.err == "false"){
				alert(dataArr.mssg);
				loadlist(0);
			}else{
				alert(dataArr.mssg);
			}
		}catch(e){
			var workwarp = getById(ele);
			workwarp.innerHTML = data;
		}
	}
}
