function getdetail() {
    kodeorg = document.getElementById('kodeorg').value;

	param = 'kodeorg=' + kodeorg + '&method=getdetail';
	
	tujuan = 'sdm_slave_5aktivasifp.php';
	post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					document.getElementById('jenisval').value = '';
					
					if(con.responseText=='KEBUN'){
						document.getElementById('jenisval').disabled = false;
					}else{
						document.getElementById('jenisval').disabled = true;
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}
function getjenisval() {
    kodeorg = document.getElementById('kodeorg').value;
    jenisval = document.getElementById('jenisval').value;
    detailval = document.getElementById('detailval').value;

	param = 'kodeorg=' + kodeorg + '&method=getjenisval';
	param += '&jenisval=' + jenisval;
	param += '&detailval=' + detailval;
	
	tujuan = 'sdm_slave_5aktivasifp.php';
	post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					if(jenisval!=''){
						alertify.popup("Detail",con.responseText).set({
							'resizable':true,
							'maximizable':false,
								onclose:function(){
									adddata()
								}
						}).resizeTo('500px','400px');
						//alertify.popup("Detail",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('500px','400px');
					}else{
						document.getElementById('detailval').value="";
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}
function adddata(){
	i = document.getElementsByName("nama[]");
	e = document.getElementsByName("check[]");
	data=dtnm=""; jlh=0;
	for(n=0;n<e.length;n++){
		if(e[n].checked==true){
			data+=i[n].innerHTML+",";
			jlh=jlh+1;
		}
	}
	document.getElementById('detailval').value = data.substr(0,data.length-1);
	alertify.popup().destroy();
}

function clickall(){
	e = document.getElementsByName("check[]");
	h = document.getElementById('checkall');
	for(i=0;i<e.length;i++){
		if(e[i].disabled==false){			
			if(h.checked==true){
				e[i].checked=true;
			}else{
				e[i].checked=false;
			}
		}
	}
}

function simpanJ() {
	kodeorg  = document.getElementById('kodeorg').value;
	tanggal  = document.getElementById('tanggal').value;
	jenisval = document.getElementById('jenisval').value;
	detailval= document.getElementById('detailval').value;
    

    tutup = document.getElementById('tutup');
    if (tutup.checked == true)
        tutup = 1;
    else
        tutup = 0;
    met = document.getElementById('method').value;

    if (trim(kodeorg) == '') {
        alert('Each Field are obligatory');
        document.getElementById('kodeorg').focus();
    } else {
        param = 'kodeorg=' + kodeorg + '&method=' + met;
        param += '&tutup=' + tutup;
        param += '&tanggal=' + tanggal;
        param += '&detailval=' + detailval;
        param += '&jenisval=' + jenisval;
        tujuan = 'sdm_slave_5aktivasifp.php';
        post_response_text(tujuan, param, respog);
    }

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //alert(con.responseText);
                    document.getElementById('container').innerHTML = con.responseText;
                    cancelJ();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function fillField(kodeorg, tutup,tanggal,jenis,detail, tipe) {
    jk = document.getElementById('kodeorg');
    for (x = 0; x < jk.length; x++) {
        if (jk.options[x].value == kodeorg) {
            jk.options[x].selected = true;
        }
    }

    setValue('kodeorg', kodeorg);
    
    document.getElementById('tanggal').value = tanggal;
	
	if(tipe=='KEBUN'){
		document.getElementById('jenisval').disabled = false;
		document.getElementById('detailval').disabled = false;
	}else{
		document.getElementById('jenisval').disabled = true;
		document.getElementById('detailval').disabled = true;
	}
    document.getElementById('jenisval').value = jenis;
    document.getElementById('detailval').value = detail;
    document.getElementById('kodeorg').disabled = true;

    if (tutup == '1')
        document.getElementById('tutup').checked = true;
    else
        document.getElementById('tutup').checked = false;

    document.getElementById('method').value = 'update';
}

function cancelJ() {
    document.getElementById('kodeorg').disabled = false;
    document.getElementById('kodeorg').value = '';
    document.getElementById('tanggal').value = '';
    setValue2('kodeorg', null);
    document.getElementById('tutup').checked = false;
    document.getElementById('method').value = 'insert';
}