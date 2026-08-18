function getblok(blok) {
	// alertify.alert(blok);
	afdeling = document.getElementById('afdeling').options[document.getElementById('afdeling').selectedIndex].value;
	param = 'afdeling=' + afdeling;
	param += "&method=getblok";
	param += '&blok=' + blok;
	tujuan = 'kebun_slave_5kavling.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// alertify.alert(con.responseText);
					document.getElementById('blok').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getafdeling(kodeunit,afdeling,kodeblok) {
	// alertify.alert(blok);
	unit = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	// if(kodeunit!='')unit=kodeunit;
	param = 'unit=' + unit;
	param += "&method=getafdeling";
	param += '&afdeling=' + afdeling;
	tujuan = 'kebun_slave_5kavling.php';
	// alert(param);
	// document.getElementById('afdeling').value=afdeling;
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// alertify.alert(con.responseText);
					document.getElementById('afdeling').innerHTML = con.responseText;
					// document.getElementById('blok').innerHTML = "<option value=''></option>";
					// alert(afdeling+' '+kodeblok);
					getblok(kodeblok);
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function simpan() {
	unit = document.getElementById('unit').value;
	afdeling = document.getElementById('afdeling').value;
	blok = document.getElementById('blok').value;
	hamparan = trim(document.getElementById('hamparan').value);
	kavling = trim(document.getElementById('kavling').value);
	tahuntanam = trim(document.getElementById('tahuntanam').value);
	nama = trim(document.getElementById('nama').value);
	status = document.getElementById('status').value;
	method = document.getElementById('method').value;
	id = document.getElementById('id').value;
	
	param = 'id=' + id;
	param += '&unit=' + unit;
	param += '&afdeling=' + afdeling;
	param += '&blok=' + blok;
	param += '&hamparan=' + hamparan;
	param += '&kavling=' + kavling;
	param += '&tahuntanam=' + tahuntanam;
	param += '&nama=' + nama;
	param += '&status=' + status;
	param += '&method=' + method;
	tujuan = 'kebun_slave_5kavling.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function edit(id,kodeunit,afdeling,kodeblok,no_hamp,no_kavl,t_tnm,nama,status){
	document.getElementById('unit').value=kodeunit;
	getafdeling(kodeunit,afdeling,kodeblok);
	document.getElementById('hamparan').value=no_hamp;
	document.getElementById('kavling').value=no_kavl;
	document.getElementById('tahuntanam').value=t_tnm;
	document.getElementById('nama').value=nama;
	document.getElementById('status').value=status;
	document.getElementById('status').disabled=false;	
//	getblok(kodeblok);
	document.getElementById('id').value=id;
	document.getElementById('method').value='update';

	document.getElementById('unit').disabled=true;	
	document.getElementById('afdeling').disabled=true;	
}

function batal(){
	document.getElementById('id').value='';
	document.getElementById('blok').value='';
	document.getElementById('hamparan').value='';
	document.getElementById('kavling').value='';
	document.getElementById('tahuntanam').value='';
	document.getElementById('nama').value='';
	document.getElementById('status').value='0';
	document.getElementById('status').disabled=true;	
	document.getElementById('method').value='insert';

	document.getElementById('unit').disabled=false;	
	document.getElementById('afdeling').disabled=false;	
}

function formajukan(unit,id){
  param = 'unit='+unit+'&id='+id+'&method=formajukan';
  tujuan = 'kebun_slave_5kavling.php';

  content = "<div id=formajukan style=\"height:100%;width:100%;\"></div>";
  title = 'Ajukan';
  height = '';
  width = 500;
  showDialog4(title, content, width, height, 'event');

  post_response_text(tujuan, param, respon);
  function respon() {
      if (con.readyState == 4) {
          if (con.status == 200) {
              busy_off();
              if (!isSaveResponse(con.responseText)) {
                  alertify.alert("Informasi",con.responseText);
              } else {
                  document.getElementById('formajukan').innerHTML = con.responseText;
              }
          } else {
              busy_off();
              error_catch(con.status);
          }
      }
  }
} 

function submitfile(notrans,maxaproval) {
	var tanggalpengajuan = document.getElementById("tanggalpengajuan").value;
	var kriteriaefil = document.getElementById("kriteriaefil").value;
	var file = document.getElementById("upload").files[0];
	var formdata = new FormData();
	formdata.append("file", file);
	formdata.append("fileupload", getValue('upload'));
	formdata.append("notrans", notrans);
	formdata.append("maxaproval", maxaproval);
	formdata.append("tanggalpengajuan", tanggalpengajuan);
	formdata.append("kriteriaefil", kriteriaefil);

	// for(i=1;i<=maxaproval;i++){
	// 	formdata.append('&persetujuan['+i+']=', trim(document.getElementById('persetujuan'+i).value)); 
 //  }
	
	if (maxaproval=='0') {
		alertify.alert("Informasi","warning : Belum ada setup persetujuan.");
		return false;
	}
	if (tanggalpengajuan=='') {
		alertify.alert("Informasi","warning : Silakan isi tanggal pengajuan.");
		return false;
	}
	for(i=1;i<=maxaproval;i++){
		formdata.append('persetujuan['+i+']=', trim(document.getElementById('persetujuan'+i).value));
		if(trim(document.getElementById('persetujuan'+i).value)==''){
		alertify.alert("Informasi","warning : Silakan isi persetujuan.");
		return false;
		}
	}
	if (getValue('upload') == "") {
		alertify.alert("Informasi","warning : Silakan lampirkan file.");
		return false;
	}
	// for(i=1;i<=maxaproval;i++){
	// 	formdata.append('&persetujuan['+i+']=', trim(document.getElementById('persetujuan'+i).value));
	// 	if(trim(document.getElementById('persetujuan'+i).value)==''){
	// 	alertify.alert("Informasi","warning : Silakan isi persetujuan.");
	// 	alert('Silakan isi persetujuan');
	// 	return;
	// 	}
 //  }

  // alertify.confirm("Informasi","Yakin ingin mengajukan ???",
  //   function(){
			document.getElementsByClassName("mybutton").disabled=true;
			busy_on();
			var con = createXMLHttpRequest();
			con.open("POST", "kebun_slave_5kavling.php?method=submitfile", true);
			con.onreadystatechange = eval(respon);
			con.send(formdata);
  //   },
  //   function(){
  //     return;
  //   }
  // );  

	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert("Informasi",con.responseText);
				} else {
					//=== Success Response
					// document.getElementsByClassName("mybutton").disabled=false;
					alertify.alert("Informasi",'Uploaded Success.');
					// document.getElementById("upload").value = "";
					// loadfiles(noinvoice);
          closeDialog4();
          loaddata();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function posting(notrans,maxaproval) {
  param = '';
  method = 'posting';
  tanggalpengajuan = document.getElementById('tanggalpengajuan').value; 

  if(maxaproval=='0'){
    alert('Belum ada setup persetujuan.');
    return;
  }
  if(tanggalpengajuan==''){
    alert('Silakan isi tanggal pengajuan.');
    return;
  }

  strper = '';
  for(i=1;i<=maxaproval;i++){
   strper += '&persetujuan['+i+']='+trim(document.getElementById('persetujuan'+i).value);
   if(trim(document.getElementById('persetujuan'+i).value)==''){
    alert('Silakan isi persetujuan');
    return;
   }
  }
  param += 'notrans=' + notrans + '&tanggalpengajuan=' + tanggalpengajuan;
  param += '&maxaproval=' + maxaproval;
  param += '&method=' + method;
  param += strper;  
  tujuan = 'kebun_slave_5kavling.php';

  alertify.confirm("Informasi","Yakin ingin memposting ???",
    function(){
      post_response_text(tujuan, param, respon);
    },
    function(){
      return;
    }
  );  
  
  function respon() {
    if (con.readyState == 4) {
      if (con.status == 200) {
        busy_off();
        if (!isSaveResponse(con.responseText)) {
          alertify.alert('Informasi',con.responseText);
        } else {
          closeDialog4();
          loaddata();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }
  }  
} 

function loaddata(num) {
	batal();
	find_nama = document.getElementById('find_nama').value;
	find_unit = document.getElementById('find_unit').value;
	
	param = 'method=loaddata';
	param += '&page=' + num + '&find_nama=' + find_nama;
	param += '&find_unit=' + find_unit;
	tujuan = 'kebun_slave_5kavling.php';
	post_response_text(tujuan, param, respog);

	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('container').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}









function batalcari() {
	document.getElementById('find_nama').value = '';
	document.getElementById('find_unit').value = '';
	loaddata();
}
 

function getPage() {
	pg = document.getElementById('pages');
	pg = pg.options[pg.selectedIndex].value;
	paged = parseFloat(pg) - 1;
	loaddata(paged);
}

function del(id) {
	param = 'method=delete' + '&id=' + id;
	tujuan = 'kebun_slave_5kavling.php';
	if (confirm(' Anda yakin hapus data ???')) {
		post_response_text(tujuan, param, respog);
	}
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					getPage();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
