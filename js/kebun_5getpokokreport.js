function cancel(){
	// document.getElementById('kegiatan').disabled=false;
	// setValue2('kegiatan',null)
	// document.getElementById('ispokok').checked = true;
	document.getElementById('namalaporan').disabled=false;
	document.getElementById('namalaporan').value='';
	document.getElementById('idlaporan').value='';
	setValue2('status',null)
	document.getElementById('proses').value='insert';
}

function simpan(){
	proses = document.getElementById('proses').value;
    namalaporan = document.getElementById('namalaporan').value;
	stts = document.getElementById('status');
	// stts = document.getElementById('status').options[document.getElementById('status').selectedIndex].value;
	// kegiatan = document.getElementById('kegiatan').options[document.getElementById('kegiatan').selectedIndex].value;
	// ispokok = document.getElementById('ispokok');

    if(stts.checked==true)
    {
        stts=1;
    }
    else
    {
        stts=0;
    }
	
	// param='proses='+proses+'&namalaporan='+namalaporan+'&kegiatan='+kegiatan+'&ispokok='+ispokok+'&status='+stts;
	param='proses='+proses+'&namalaporan='+namalaporan+'&status='+stts;
    if (proses=='edit') {
        idlaporan = document.getElementById('idlaporan').value;
        param += '&idlaporan='+idlaporan;
    }
	tujuan='kebun_slave_5getpokokreport';
	post_response_text(tujuan+'.php', param, respon);
	
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.alert("Success");
					cancel();
                    loaddata(0);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loaddata(num){
	param='proses=loaddata&page='+num;
	tujuan='kebun_slave_5getpokokreport';
	post_response_text(tujuan+'.php', param, respon);
	
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
				  document.getElementById('container').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletefield(idlaporan,stts){
	param='proses=delete&idlaporan='+idlaporan+'&status='+stts;
	tujuan='kebun_slave_5getpokokreport';
	if(confirm('Anda yakin hapus item ini?'))post_response_text(tujuan+'.php', param, respon);
	
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
				  document.getElementById('container').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function fillfield(namalaporan,idlaporan,stts){
    // setValue2('kegiatan',kegiatan);
	// document.getElementById('kegiatan').disabled=true;

	document.getElementById('idlaporan').value=idlaporan;
	document.getElementById('namalaporan').value=namalaporan;
	document.getElementById('namalaporan').disabled=true;

	if(stts=='1')
    {
        document.getElementById('status').checked=true;
    }
    else
    {
        document.getElementById('status').checked=false;
    }

	document.getElementById('proses').value = 'edit';
}

function detailView(idlaporan, stts) {
    param = 'proses=detailView';
    param += '&idlaporan='+idlaporan;
    param += '&status'+stts;

    tujuan = 'kebun_slave_5getpokokreport.php';

    post_response_text(tujuan,param,respog)

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    alertify.popup().destroy();
                    alertify.popup(con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
                    $(document).ready(function () {
                        $(".select2").select2({
                            dropdownAutoWidth: false,
                        });
                        $(".select2-selection--single").height(25).css({
                            cursor: "auto",
                        });
                        $(".select2-selection__arrow b").css({
                            top: "40%",
                        });
                        $(".select2-selection__rendered").css({
                            "line-height": "25px",
                        });
                    });
                    loadDetail(idlaporan);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadDetail(idlaporan) {
    param = 'proses=loadDetail';
    param += '&idlaporan='+idlaporan;

    tujuan = 'kebun_slave_5getpokokreport.php';

    post_response_text(tujuan,param,respog)

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('contDetailList').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cancel_dt(){
	document.getElementById('kegiatan').value = "";
    setValue2('kegiatan',null);
	document.getElementById('kegiatan').disabled = false;
	document.getElementById('ispokok').checked = true;
}

function simpan_dt(){
	proses = document.getElementById('proses_dt').value;
    kegiatan = document.getElementById('kegiatan').options[document.getElementById('kegiatan').selectedIndex].value;
	ispokok = document.getElementById('ispokok');
    idlaporan = document.getElementById('idlap_dt').value;

    if(ispokok.checked==true)
    {
        ispokok=1;
    }
    else
    {
        ispokok=0;
    }
	
	param='proses='+proses+'&kegiatan='+kegiatan+'&ispokok='+ispokok;
    param += '&idlaporan='+idlaporan;
    
	tujuan='kebun_slave_5getpokokreport';
	post_response_text(tujuan+'.php', param, respon);
	
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.alert("Data Berhasil Disimpan.");
                    cancel_dt();
                    loadDetail(idlaporan);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function delete_dt(idlaporan,kegiatan,ispokok){
	param='proses=delete_dt&idlaporan='+idlaporan+'&kegiatan='+kegiatan+'&ispokok='+ispokok;
	tujuan='kebun_slave_5getpokokreport';
	if(confirm('Anda yakin hapus item ini?'))post_response_text(tujuan+'.php', param, respon);
	
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadDetail(idlaporan);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function fillfield_dt(idlaporan,kegiatan,ispokok){
	document.getElementById('kegiatan').value=kegiatan;
    setValue2('kegiatan',kegiatan);
	document.getElementById('kegiatan').disabled=true;

	if(ispokok=='1')
    {
        document.getElementById('ispokok').checked=true;
    }
    else
    {
        document.getElementById('ispokok').checked=false;
    }

	document.getElementById('idlap_dt').value = idlaporan;
	document.getElementById('proses_dt').value = 'edit_dt';
}