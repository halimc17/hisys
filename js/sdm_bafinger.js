function form(){
	width = '';
	height = '';
	content = "<fieldset><div id=container align=center style=\"width:1024px;max-height:400px;overflow:auto;\"></div></fieldset>";
	ev = 'event';
	title = "Detail HTML";
	showDialog5(title, content, width, height, ev);
}

function previewtr(ev,notransaksi,tipe){
	param='notransaksi='+notransaksi+'&method=previewtr';
	param += '&tipe=' + tipe;
	tujuan='sdm_slave_bafinger.php';
	if(tipe=='html'){
		post_response_text(tujuan, param, respog);
		function respog(){
			if(con.readyState == 4){
				if(con.status == 200){
					busy_off();
					if(!isSaveResponse(con.responseText)){
						alert(con.responseText);
					}else{
						form();
						document.getElementById('container').innerHTML=con.responseText;
					}
				}else{
					busy_off();
					error_catch(con.status);
				}
			}
		} 
	}else if(tipe=='pdf'){
		title='PDF';
		tujuan=tujuan+"?"+param;  
		width = 1024;
		height = 400;
		content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
		showDialog2(title, content, width, height, ev);
	}
}

function form_batal(noba) {
	width = "350";
	height = "";
	content =
	  '<fieldset><legend>Batal</legend><div id=containerbatal align=center style="width:320px;max-height:150px;overflow:auto;"></div></fieldset>';
	ev = "event";
	title = "";
	showDialog5(title, content, width, height, ev);
  
	param = "method=form_batal";
	param += "&noba=" + noba;
	tujuan = "sdm_slave_bafinger.php";
	post_response_text(tujuan, param, respog);
	function respog() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
		  } else {
			document.getElementById("containerbatal").innerHTML =
			  con.responseText;
		  }
		} else {
		  busy_off();
		  error_catch(con.status);
		}
	  }
	}
  }

  function batalkan() {
	noba = document.getElementById("notran_batal").innerHTML;
	keterangan = document.getElementById("ketbatal").value;
	param =
	  "method=batalkan" +
	  "&noba=" +
	  noba +
	  "&keterangan=" +
	  keterangan;
	if (keterangan == "") {
	  alertify.alert("Isikan keterangan.");
	  return;
	}
	tujuan = "sdm_slave_bafinger.php";
	post_response_text(tujuan, param, respog);
	function respog() {
	  if (con.readyState == 4) {
		if (con.status == 200) {
		  busy_off();
		  if (!isSaveResponse(con.responseText)) {
			alertify.alert(con.responseText);
		  } else {
			alert('Succses');
			closeDialog();
			pg = document.getElementById('pages');
			pg = pg.options[pg.selectedIndex].value;
			paged = parseFloat(pg) - 1;
			loaddata(paged);
		  }
		} else {
		  busy_off();
		  error_catch(con.status);
		}
	  }
	}
  }

function getsatuan(){
	komoditi = document.getElementById('komoditi').value;
	
	param = 'method=getsatuan'+'&komoditi='+komoditi;
	tujuan = 'sdm_slave_bafinger.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('lblsatuan').innerHTML = con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpan(){
	method = document.getElementById('method').value;
	noba = document.getElementById('noba').value;
	tanggal = document.getElementById('tanggal').value;
	unit = document.getElementById('unit').value;
	karyawan = document.getElementById('karyawan').value;
	keterangan = document.getElementById('keterangan').value;
	absen = document.getElementById('absen').value;
	jam = document.getElementById('jam').value;
	mnt = document.getElementById('mnt').value;
	jam2 = document.getElementById('jam2').value;
	mnt2 = document.getElementById('mnt2').value;
	jam3 = document.getElementById('jam3').value;
	mnt3 = document.getElementById('mnt3').value;
	jam4 = document.getElementById('jam4').value;
	mnt4 = document.getElementById('mnt4').value;
	tanggaljamkeluar = document.getElementById('tanggaljamkeluar').value;
	tanggaljammasuk = document.getElementById('tanggaljammasuk').value;
	tipeba = document.getElementById('tipeba').value;
	
	param = 'method='+method+'&noba='+noba+'&tanggal='+tanggal+'&unit='+unit+'&karyawan='+karyawan+'&absen='+absen+'&jam='+jam+'&mnt='+mnt+'&jam2='+jam2+'&mnt2='+mnt2+'&jam3='+jam3+'&mnt3='+mnt3+'&jam4='+jam4+'&mnt4='+mnt4+'&keterangan='+keterangan+'&tanggaljamkeluar='+tanggaljamkeluar+'&tanggaljammasuk='+tanggaljammasuk+'&tipeba='+tipeba;
	tujuan = 'sdm_slave_bafinger.php';
	post_response_text(tujuan, param, respog);
	
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					alert("Data berhasil di simpan.");
					document.getElementById('noba').value=con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getkaryawanid(){
	unit = document.getElementById('unitfile').value;

	param = 'method=getkaryawanid'+'&unit='+unit;
	
	ev='event';
	judul='excel';
	tujuan = 'sdm_slave_bafinger.php';

	printFile(param,tujuan,judul,ev);
}

function printFile(param,tujuan,title,ev){
	tujuan=tujuan+"?"+param;  
	width='';
	height='';
	content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
	showDialog1(title,content,width,height,ev); 	
 }

function simpanfile(){
	var file = document.getElementById('filex').files[0];
	method = document.getElementById('methods').value;
	noba = document.getElementById('noba').value;
	tanggal = document.getElementById('tanggalfile').value;
	unit = document.getElementById('unitfile').value;
	
	var formdata = new FormData();
    formdata.append("file", file);
    formdata.append("fileupload", getValue('filex'));
    formdata.append("tanggal", tanggal);
    formdata.append("noba", noba);
    formdata.append("method", method);
    formdata.append("unit", unit);

    if(tanggalfile == ''){
		alert("Warning : Harap tanggal diisikan.");
		return false;
    }else if(unitfile == ''){
		alert("Warning : Harap unit diisikan.");
		return false;
    }else if (getValue('filex') == "") {
		alert("Warning : Tidak ada data yang di upload !");
		return false;
	}

	busy_on();
    var con = createXMLHttpRequest();
    con.open("POST", "sdm_slave_bafinger.php?method="+method, true);
    con.onreadystatechange = eval(respon);
    con.send(formdata);

    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    alert("Data berhasil di simpan.");
					showalllist(0);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function showalllist(pg) {
	document.getElementById('scnoba').value = '';
	document.getElementById('sctanggal').value = '';
	document.getElementById('scnama').value = '';
	document.getElementById('scstatusper').value = '';
	document.getElementById('form_ba').style.display = 'none';
	document.getElementById('list_ba').style.display = 'block';
	loaddata(pg);
}

function displayFormInput() {
	clear_all_data();
	document.getElementById('list_ba').style.display = 'none';
	document.getElementById('form_ba').style.display = 'block';
}

function batal(){
	getpage();
	document.getElementById('list_ba').style.display = 'block';
	document.getElementById('form_ba').style.display = 'none';
}

function clear_all_data(){
	document.getElementById('noba').value='';
	document.getElementById('unit').selectedIndex=0;
	document.getElementById('absen').value='H';
	document.getElementById('keterangan').value='';


	// document.getElementById('jam').selectedIndex=0;
	// document.getElementById('mnt').selectedIndex=0;
	// document.getElementById('jam2').selectedIndex=0;
	// document.getElementById('mnt2').selectedIndex=0;
	// document.getElementById('jam3').selectedIndex=0;
	// document.getElementById('mnt3').selectedIndex=0;
	// document.getElementById('jam4').selectedIndex=0;
	// document.getElementById('mnt4').selectedIndex=0;

	setValue2('jam','00');
	setValue2('mnt','00');
	setValue2('jam2','00');
	setValue2('mnt2','00');
	setValue2('jam3','00');
	setValue2('mnt3','00');
	setValue2('jam4','00');
	setValue2('mnt4','00');

	document.getElementById('method').value="insert";
	document.getElementById('lblmethod').innerHTML="(New)";
	
	document.getElementById('noba').disabled=true;
	document.getElementById('tanggal').disabled=false;
	document.getElementById('unit').disabled=false;
	document.getElementById('karyawan').disabled=false;
	document.getElementById('absen').disabled=false;
	document.getElementById('tanggaljammasuk').disabled=false;
	document.getElementById('tanggaljamkeluar').disabled=false;
	
	getkaryawan();
}

function getpage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function loaddata(pg) {
	scnoba = document.getElementById('scnoba').value;
	sctanggal = document.getElementById('sctanggal').value;
	scnama = document.getElementById('scnama').value;
	scstatusper = document.getElementById('scstatusper').value;
	
	param = 'method=loaddata'+'&page='+pg+'&scnoba='+scnoba+'&sctanggal='+sctanggal+'&scnama='+scnama+'&scstatusper='+scstatusper;
	tujuan = 'sdm_slave_bafinger.php';
	post_response_text(tujuan, param, respog);
	
	function respog() {
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('contain').innerHTML = con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getkaryawan(){
	unit = document.getElementById('unit').value;
	
	param = 'method=getkaryawan'+'&unit='+unit;
	tujuan = 'sdm_slave_bafinger.php';
	post_response_text(tujuan, param, respog);
	
	function respog() {
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('karyawan').innerHTML = con.responseText;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getShift(){
	unit = document.getElementById('unit').value;
	karyawan = document.getElementById('karyawan').value;
	tanggal = document.getElementById('tanggal').value;

	
	param = 'method=getShift'+'&unit='+unit+'&karyawan='+karyawan+'&tanggal='+tanggal;
	tujuan = 'sdm_slave_bafinger.php';
	post_response_text(tujuan, param, respog);
	
	function respog() {
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					hasil = con.responseText.split("###");
					// for (i=0;i<hasil.length;i++){
					if (hasil[0]!='') {
						data = hasil[0].split(":");
						setValue2('jam',data[0]);
						setValue2('mnt',data[1]);
					}
					if (hasil[1]!='') {
						data = hasil[1].split(":");
						setValue2('jam4',data[0]);
						setValue2('mnt4',data[1]);
					}
					// }
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function editba(noba){
	param = "method=editba&noba="+noba;
	tujuan = 'sdm_slave_bafinger.php';
	post_response_text(tujuan, param, respon);
	
	function respon(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					document.getElementById('list_ba').style.display = 'none';
					document.getElementById('form_ba').style.display = 'block';
					data = con.responseText.split("####");
					document.getElementById('noba').value=data[0];
					document.getElementById('tanggal').value=data[1];
					document.getElementById('unit').value=data[2];
					document.getElementById('karyawan').value=data[3];
					document.getElementById('absen').value=data[4];
					document.getElementById('keterangan').value=data[9];
					document.getElementById('tanggaljamkeluar').value=data[10];
					document.getElementById('tanggaljammasuk').value=data[15];
					
					setValue2('karyawan',data[3]);
					setValue2('tipeba',data[16]);
					
					var jam = data[5].split(":");
					var jam2 = data[6].split(":");
					var jam3 = data[7].split(":");
					var jam4 = data[8].split(":");

					var minute = data[11].split(":");
					var minute2 = data[12].split(":");
					var minute3 = data[13].split(":");
					var minute4 = data[14].split(":");

					
					setValue2('jam',jam[0]);
					setValue2('mnt',minute[0]);
					setValue2('jam2',jam2[0]);
					setValue2('mnt2',minute2[0]);
					setValue2('jam3',jam3[0]);
					setValue2('mnt3',minute3[0]);
					setValue2('jam4',jam4[0]);
					setValue2('mnt4',minute4[0]);

					// document.getElementById('jam').value=jam[0];
					// document.getElementById('mnt').value=jam[1];
					// document.getElementById('jam2').value=jam2[0];
					// document.getElementById('mnt2').value=jam2[1];
					// document.getElementById('jam3').value=jam3[0];
					// document.getElementById('mnt3').value=jam3[1];
					// document.getElementById('jam4').value=jam4[0];
					// document.getElementById('mnt4').value=jam4[1];
					
					document.getElementById('method').value="update";
					document.getElementById('lblmethod').innerHTML="(Edit)";
					
					document.getElementById('noba').disabled=true;
					document.getElementById('tanggal').disabled=true;
					document.getElementById('unit').disabled=true;
					document.getElementById('karyawan').disabled=true;
					document.getElementById('absen').disabled=true;
					document.getElementById('tanggaljamkeluar').disabled=true;
					document.getElementById('tanggaljammasuk').disabled=true;
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function deleteba(noba){
	param = "method=deleteba&noba="+noba;
	tujuan = 'sdm_slave_bafinger.php';
	
	if (confirm('Anda yakin hapus No. BA '+noba+'?')) {
		post_response_text(tujuan, param, respon);
	}
	
	function respon(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					getpage();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function postingba(noba){
	param = "method=postingba&noba="+noba;
	tujuan = 'sdm_slave_bafinger.php';
	
	if (confirm('Anda yakin posting No. BA '+noba+'?')) {
		post_response_text(tujuan, param, respon);
	}
	
	function respon(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					getpage();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function formajukan(noba){
    param = 'method=formajukan';
    param += '&noba=' + noba;
    tujuan = 'sdm_slave_bafinger.php';
	post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.popup().destroy();
                    alertify.popup("","<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('300px','230px');
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
function ajukan(){
    noba 		=document.getElementById('notransaksi_ajukan').value;
    jlh         =document.getElementById('jlh').value;
    param       = 'method=ajukan';
    param       +='&noba=' + noba;
    param       +='&jlh=' + jlh;

    for (i = 1; i <= jlh; i++) {
        param += "&" + 'kepada'+ i + "=" + document.getElementById('kepada'+i).value;
    }

    if(jlh==0){
        alertify.alert("Warning: Approval kosong");
        return;
    }
    tujuan = 'sdm_slave_bafinger.php';
    closeDialog();
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                	alertify.popup().destroy();
                    alert('Succses');
					pg = document.getElementById('pages');
					pg = pg.options[pg.selectedIndex].value;
					paged = parseFloat(pg) - 1;
					loaddata(paged);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpanapproval(){
	notransaksi=document.getElementById('appnotransaksi').value;
	level=document.getElementById('level').value;
	approval=document.getElementById('approval').value;
	
	if(approval==''){
		alert('Gagal, Penyetuju masih belum di setting, silahkan hubungi Administrator');
		return false;
	}
	
	param = "method=simpanapproval&notransaksi="+notransaksi+'&approval='+approval+'&level='+level;
	tujuan = 'sdm_slave_bafinger.php';
	
	if (confirm('Anda yakin ajukan notransaksi '+notransaksi+'?')) {
		post_response_text(tujuan, param, respon);
	}
	
	function respon(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
				if(!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					closeDialog();
					getpage();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}