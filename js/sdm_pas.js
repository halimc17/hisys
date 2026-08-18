function approve(id){
    param = 'method=approve';
    param += '&id=' + id;
    tujuan = 'sdm_slave_pas.php';
    alertify.confirm("Approve","Anda yakin ???",
        function(){
            post_response_text(tujuan, param, respog);
        },
        function(){
            return;
        }
    );
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.popup().destroy();
                    loaddata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function detailcvmm(id){
    param = 'method=detail';
    param += '&id=' + id;
    param += '&tipeprint=html';
    tujuan = 'sdm_slave_coreman.php';

    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {                    
                    title = 'Data Detail';
                    tujuan = tujuan + "?" + param;
                    alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('70%','70%').show();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }       
}
function detailkpi(id){
    param = 'method=detail';
    param += '&id=' + id;
    param += '&tipeprint=html';
    tujuan = 'sdm_slave_2kpi.php';

    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {                    
                    alertify.popup().set({'resizable':true,'maximizable':true,'message':con.responseText}).resizeTo('70%','70%').show();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }       
}

function getfinalscore(nilai){
	if(nilai<61){
		hasil="KURANG";
	}else if(nilai>=61 && nilai<81){
		hasil="CUKUP";
	}else if(nilai>=81 && nilai<91){
		hasil="BAIK";
	}else if(nilai>=91 && nilai<110){
		hasil="SANGAT BAIK";
	}else if(nilai>110){
		hasil="LUAR BIASA";
	}
	
	document.getElementById('nilaifinalscore').innerHTML=hasil;
	showtombol();
}
function posting(id){
    param = 'method=posting';
    param += '&id=' + id;
    tujuan = 'sdm_slave_pas.php';
    alertify.confirm("Posting","Anda yakin ???",
        function(){
            post_response_text(tujuan, param, respog);
        },
        function(){
            return;
        }
    );
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
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
function unposting(id){
    param = 'method=unposting';
    param += '&id=' + id;
    tujuan = 'sdm_slave_pas.php';
    alertify.confirm("Unposting","Anda yakin ???",
        function(){
            post_response_text(tujuan, param, respog);
        },
        function(){
            return;
        }
    );
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
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

function newdata(){
    document.getElementById('entry').style.display='block';
    document.getElementById('listkriteria').style.display='block';
    document.getElementById('loadpreview').style.display='none';
    reset();
}

function displaylist() {
    document.getElementById('listkriteria').style.display='none';
    document.getElementById('loadpreview').style.display='block';
    loaddata(0);
	if(document.getElementById('cekperubahan')!=undefined){		
		document.getElementById('cekperubahan').value='';
	}
}

function simpan(err){
	let kelebihan      = document.getElementById('kelebihan').value;
	let usulankelebihan= document.getElementById('usulankelebihan').value;
	let pica           = document.getElementById('pica').value;
	let kelemahan      = document.getElementById('kelemahan').value;
	let usulankelemahan= document.getElementById('usulankelemahan').value;
	let catatankary    = document.getElementById('catatankary').value;
	let kehadiran      = document.getElementById('kehadiran').value;
	let atasanpenilai  = document.getElementById('atasanpenilai').value;
	let penilai        = document.getElementById('penilai').value;
	let idht           = document.getElementById('idht').value;
	let nilaifinal     = document.getElementById('nilaifinal').value;
	let nilaifinalscore= document.getElementById('nilaifinalscore').innerHTML;

    validate([
        ["penilai","Penilai tidak boleh kosong"],
        ["atasanpenilai","Atasan penilai tidak boleh kosong."]
    ]);
	
	if(err==true){
		alertify.alert('Masih ada kesalahan, lihat Daftar Kesalahan dan perbaiki.'); return;
	}
    param  = 'kelebihan='+kelebihan+'&method=simpan';
    param += '&usulankelebihan='+usulankelebihan+'&pica='+pica;
    param += '&kelemahan='+kelemahan+'&usulankelemahan='+usulankelemahan;
    param += '&catatankary='+catatankary+'&kehadiran='+kehadiran;
    param += '&atasanpenilai='+atasanpenilai+'&penilai='+penilai;
    param += '&id='+idht;
    param += '&nilaifinal='+nilaifinal;
    param += '&nilaifinalscore='+nilaifinalscore;
    
    tujuan = 'sdm_slave_pas.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    alertify.alert('Done');
					document.getElementById('tombolsimpan').style.display="none";
					document.getElementById('cekperubahan').value='';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function loaddatadetail(karyawanid,thnnilai,penilaian) {
    param  = 'method=loaddatadetail';
    param += '&karyawanid='+karyawanid+'&tahun='+thnnilai;
    param += '&penilaian='+penilaian;
    tujuan = 'sdm_slave_pas.php';
	if(document.getElementById('cekperubahan')!=undefined){
		cekperubahan = document.getElementById('cekperubahan').value;
		if(cekperubahan=='1'){
			alertify.alert('Ada perubahan yang belum disimpan, silahkan click tombol Simpan dibawah.');
			return;
		}
	}
	
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					document.getElementById('listkriteria').style.display="block";
					document.getElementById('loadpreview').style.display='none';
                    document.getElementById('container').innerHTML = con.responseText;
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:false
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
function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);	
}

function loaddata(page) {
    let nama = document.getElementById('scnama').value;
    let dept = document.getElementById('scdept').value;
    let thnnilai = document.getElementById('scthn').value;
	
	let penilaian = document.getElementById('scpenilaian').value;
    let unit = document.getElementById('scunit').value;
    let gol = document.getElementById('scgol').value;
    let post = document.getElementById('scpost').value;
    let atasan = document.getElementById('scatasan').value;

    param = 'method=loaddata';
	param += '&atasan=' + atasan;
    param += '&post=' + post;
    param += '&gol=' + gol;
    param += '&unit=' + unit;
    param += '&penilaian=' + penilaian;
    param += '&page=' + page;
    param += '&nama='+nama;
    param += '&dept='+dept;
    param += '&thnnilai='+thnnilai;
    tujuan = 'sdm_slave_pas.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('listdata').innerHTML = con.responseText;
                    // document.getElementById('loadpreview').style.display = 'block';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function detail(karyawanid,thnnilai,penilaian) {
    param  = 'method=detail';
    param += '&tipeprint=html';
    param += '&karyawanid='+karyawanid+'&tahun='+thnnilai;
    param += '&penilaian='+penilaian;
    tujuan = 'sdm_slave_pas.php';
	
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.popup().set({'resizable':true,'maximizable':true,'startMaximized':true,'message':con.responseText}).resizeTo('80%','70%').show();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function pdf(karyawanid,thnnilai,penilaian){
    param  = 'method=detail';
    param += '&tipeprint=pdf';
    param += '&karyawanid='+karyawanid+'&tahun='+thnnilai;
    param += '&penilaian='+penilaian;
    tujuan = 'sdm_slave_pas.php';
	tujuan = tujuan + "?" + param;
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" + tujuan + "'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}

function showtombol(){
	document.getElementById('tombolsimpan').style.display="";
	document.getElementById('cekperubahan').value='1';
}