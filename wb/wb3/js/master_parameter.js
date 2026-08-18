function getmill(millcode=''){
	pt=getValue('kodeperusahaan');
	
	param='method=getmill&pt='+pt+'&millcode='+millcode;
    tujuan='master_parameter_slave.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Info",con.responseText);
                } else {
                    document.getElementById('millcode').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }   
    }
}

function loaddata() {
	param = 'method=loaddata';
    tujuan = 'master_parameter_slave.php';
    post_response_text(tujuan, param, respog);

    function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
                if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
                }else{
					document.getElementById('output').innerHTML = con.responseText;
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:false
						});
						$('.select2-selection--single').height(30).css({
							cursor: "auto"
						});
						$('.select2-selection__arrow b').css({
							top: "70%"
						});
						$('.select2-selection__rendered').css({
							'line-height': '31px'
						});
					});
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function rubah(){
	document.getElementById('rubah').disabled=true;
	document.getElementById('simpan').disabled=false;
	document.getElementById('kodeperusahaan').disabled=false;
	document.getElementById('millcode').disabled=false;
	document.getElementById('deskripsi').disabled=false;
	document.getElementById('alamat1').disabled=false;
	document.getElementById('alamat2').disabled=false;
	document.getElementById('manager').disabled=false;
	document.getElementById('ktu').disabled=false;
	document.getElementById('labname').disabled=false;
	document.getElementById('email').disabled=false;
	document.getElementById('idtimbangan').disabled=false;
	document.getElementById('porttimbangan').disabled=false;
	document.getElementById('baudratetimbangan').disabled=false;
	document.getElementById('databittimbangan').disabled=false;
	document.getElementById('paritytimbangan').disabled=false;
	document.getElementById('stopbittimbangan').disabled=false;

	millcode=document.getElementById('millcode').value;
	getmill(millcode);
}

function simpan(){
	kodeperusahaan=document.getElementById('kodeperusahaan').value;
	millcode=document.getElementById('millcode').value;
	deskripsi=document.getElementById('deskripsi').value;
	alamat1=document.getElementById('alamat1').value;
	alamat2=document.getElementById('alamat2').value;
	manager=document.getElementById('manager').value;
	ktu=document.getElementById('ktu').value;
	labname=document.getElementById('labname').value;
	idtimbangan=document.getElementById('idtimbangan').value;
	method=document.getElementById('method').value;
	port = document.getElementById('porttimbangan').value;
	baudrate = document.getElementById('baudratetimbangan').value;
	databit = document.getElementById('databittimbangan').value;
	parity = document.getElementById('paritytimbangan').value;
	stopbit = document.getElementById('stopbittimbangan').value;
	email = document.getElementById('email').value;
	
	validate([
        ["kodeperusahaan","Perusahaan harus dipilih."],
        ["millcode","Mill Code harus diisi"],
        ["idtimbangan","ID Timbangan harus diisi"],
		['porttimbangan','Port Timbangan harus diisi'],
		['baudratetimbangan','Baudrate Timbangan harus diisi'],
		['databittimbangan','Databit Timbangan harus diisi'],
		['paritytimbangan','Parity Timbangan harus diisi'],
		['stopbittimbangan','Stopbit Timbangan harus diisi']
	]);
	
	param  = '';
	param += '&kodeperusahaan=' + kodeperusahaan;
	param += '&millcode=' + millcode;
	param += '&deskripsi=' + deskripsi;
	param += '&alamat1=' + alamat1;
	param += '&alamat2=' + alamat2;
	param += '&manager=' + manager;
	param += '&ktu=' + ktu;
	param += '&labname=' + labname;
	param += '&idtimbangan=' + idtimbangan;
	param += '&method=' + method;
	param += `&port=${port}&baudrate=${baudrate}&databit=${databit}&parity=${parity}&stopbit=${stopbit}&email=${email}`;
	
	tujuan = 'master_parameter_slave.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('rubah').disabled=false;
					document.getElementById('simpan').disabled=true;
					document.getElementById('kodeperusahaan').disabled=true;
					document.getElementById('millcode').disabled=true;
					document.getElementById('deskripsi').disabled=true;
					document.getElementById('alamat1').disabled=true;
					document.getElementById('alamat2').disabled=true;
					document.getElementById('manager').disabled=true;
					document.getElementById('ktu').disabled=true;
					document.getElementById('labname').disabled=true;
					document.getElementById('idtimbangan').disabled=true;
					document.getElementById('baudratetimbangan').disabled=true;
					document.getElementById('databittimbangan').disabled=true;
					document.getElementById('paritytimbangan').disabled=true;
					document.getElementById('porttimbangan').disabled=true;
					document.getElementById('stopbittimbangan').disabled=true;
					document.getElementById('email').disabled=true;
					
					alertify.set('notifier','position', 'top-center');
                  	alertify.success('Sukses');
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}