function inputData(fileform){
	if(typeof fileform.namaprogram !== 'undefined' && fileform.namaprogram.value == ""){
		alert("Program Training Belum diisi!");
		return false;
	}
	var proses ="";
	if(fileform.getAttribute('action')){
		proses = "proses="+fileform.getAttribute('action');
	}
	busy_on();
	var xhr = new XMLHttpRequest();
	xhr.onload = respon;
	xhr.open("post", 'sdm_slave_programtraining.php?'+proses);
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
							alert(dataArr.messege);
							if(dataArr.redirect !== 'false'){
								eval(dataArr.redirect);
							}
						}else{
							alert(dataArr.messege);
							getSlave();
						}
						 fileform.reset();
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
	var proses = "";
	var workwarp = document.getElementById('workwarp');
	var vr = "";
	
	if(typeof switchcase !== 'undefined'){
		proses = "?proses="+switchcase;
	}
	if(typeof ele !== 'undefined' && ele !== ''){
		par = ele.getAttribute('param').split(',');
		data = ele.getAttribute('data').split(',');
		param = par[0]+"="+data[0];
		for(i=1; i<data.length; i++){
			param += "&"+par[i]+"="+data[i];
		}
	}
	
	if(typeof valuefor !== 'undefined'){
		vr = valuefor;
		if(param !== ""){
			param += "&";
		}
		param += vr;
	}
	
	post_response_text('sdm_slave_programtraining.php'+proses, param, respon);
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
						}else if(switchcase == 'getdetail'){
							viewdetail(con.responseText);
						}else if(switchcase == 'selectapproval'){
							selectapproval(con.responseText);
						}else{
							try{
								var dataArr = JSON.parse(con.responseText);
								if(dataArr.err == 'false'){
									getSlave();
								}else if(dataArr.err == 'redirect'){
									eval(dataArr.redirect);
								}else{
									alert(dataArr.err);
								}
							}catch(e){
								var data	= con.responseText;
								workwarp.innerHTML = data;
								//alert(proses);
								//getSlave();
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
function getValOption(e,v){
	var list = e.getAttribute('list');
	var datalist = document.getElementById(list);
	var content = document.getElementsByClassName(v+'_content');
	var num = e.getAttribute('num');
	var data = [];
	var val = [];
	var dataContent = [];
		for(i=0; i<datalist.options.length; i++){
			data.push(datalist.options[i].value);
			val.push(datalist.options[i].text);
		}
		for(i=0; i<content.length; i++){
			if(content[i].value !== ""){
				dataContent.push(content[i].value);
			}
		}
	if(e.value !== ""){
		if(data.indexOf(e.value) == -1){
			e.value = "";
			alert('Nama Tidak terdaftar');
		}else{
			if(dataContent.indexOf(val[data.indexOf(e.value)]) !== -1){
				e.value = "";
				if(document.getElementById(v+'_'+num+'_content')){
					document.getElementById(v+'_'+num+'_content').value = "";
				}
				alert('Nama sudah ada dalam list');	
			}else{
				if(document.getElementById(v+'_'+num+'_content') == null){
					input = document.createElement('input');
					input.id = v+'_'+num+'_content';
					input.setAttribute('name',v+'[]');
					input.setAttribute('class',v+'_content');
					input.setAttribute('type','hidden');
					input.value = val[data.indexOf(e.value)];
					e.parentNode.appendChild(input);
				}else{
					document.getElementById(v+'_'+num+'_content').value = val[data.indexOf(e.value)];
				}
				createNewList(e,v);
			}
		}
	}
	
}
function createNewList(e,v){
	var num = e.getAttribute('num');
	nexnum = parseInt(num)+1;
	
	if(v == 'karyawan'){
		if(document.getElementById(v+'_'+nexnum) == null){
			createKaryawan(nexnum,v);
		}
	}else if(v == 'biaya'){
		if(typeof document.getElementById(v+'_'+nexnum) == 'undefined' || document.getElementById(v+'_'+nexnum) == null){
			createBiaya(nexnum,v);
		}
	}
	
}
function deleteNull(e){
	var num = e.getAttribute('num');
	nexnum = parseInt(num)+1;
	v = e.id.split("_")[0];
	console.log(v+'_'+nexnum);
	if(e.value == ""){
		if(document.getElementById(v+'_'+nexnum)){
			if(document.getElementById(v+'_'+nexnum).value == ""){
				document.getElementById(v+'_'+nexnum).parentNode.remove();
			}
		}
	}
}
function createKaryawan(nexnum,v){
	var listpeserta = document.getElementById('listpeserta');
	li = document.createElement('li');
	input = document.createElement('input');
	input.id = v+'_'+nexnum;
	input.setAttribute('num',nexnum);
	input.setAttribute('list','karyawan');
	input.setAttribute('name','peserta[]');
	input.setAttribute('class','peserta myinputtext input-100');
	//input.setAttribute('onfocus','createNewList(this,\''+v+'\');');
	input.setAttribute('onchange','getValOption(this,\''+v+'\');');
	input.setAttribute('onblur','deleteNull(this);');
	li.appendChild(input);
	listpeserta.appendChild(li);
}
function createBiaya(nexnum,v){
	//<input name="biaya[]" num="1" value="0.00" class="biaya myinputtextnumber rightlist" type="text" onchange="hitungjumlah();">
	//<input id="biaya_1" name="namabiaya[]" num="1" value="" class="myinputtext leftlist" onfocus="createNewList(this,'biaya')">
							
	var listpeserta = document.getElementById('listbiaya');
	li = document.createElement('li');
	input = document.createElement('input');
	input2 = document.createElement('input');
	input.id = v+'_'+nexnum;
	input.setAttribute('num',nexnum);
	input.setAttribute('name','namabiaya[]');
	input.setAttribute('class','leftlist myinputtext');
	input.setAttribute('onchange','createNewList(this,\''+v+'\');');
	//input.setAttribute('onchange','getValOption(this,\''+v+'\');');
	input.setAttribute('onblur','deleteNull(this);');
	
	input2.setAttribute('name','biaya[]');
	input2.setAttribute('num',nexnum);
	input2.setAttribute('class','biaya myinputtextnumber rightlist');
	input2.setAttribute('onkeyup','hitungjumlah();');
	input2.setAttribute('onkeypress','return angka_doang(event);');
	input2.value = "0";
	
	li.appendChild(input2);
	li.appendChild(input);
	listpeserta.appendChild(li);
}
function hitungjumlah(){
	var nf = new Intl.NumberFormat();
	var biaya = document.getElementsByClassName('biaya');
	var total = document.getElementById('total');
	var jml = 0;
	for(i=0; i<biaya.length; i++){
		jml = jml + parseInt(biaya[i].value);
	}
	total.innerHTML = nf.format(jml);
}
function viewdetail(data){
	title= 'Detail';
	width='700';
	height='400';
	showDialog1(title,data,width,height,event);
	//getSlave(switchcase,ele,dataid)
}
function selectapproval(data){
	//var dataArr = JSON.parse(data);
	title= 'Select Approvement';
	width='400';
	height='300';
	showDialog2(title,data,width,height,event);
}

