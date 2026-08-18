function getbulan(periode){
	if(periode=='Q1'){
		setValue2('bulandr','01');
		setValue2('bulansd','03');
	}
	if(periode=='Q2'){
		setValue2('bulandr','04');
		setValue2('bulansd','06');
	}
	if(periode=='Q3'){
		setValue2('bulandr','07');
		setValue2('bulansd','09');
	}
	if(periode=='Q4'){
		setValue2('bulandr','10');
		setValue2('bulansd','12');
	}
	if(periode==''){
		setValue2('bulandr','01');
		setValue2('bulansd','01');
	}
}
function formajukan(idkpi){
    param = 'method=formajukan';
    param += '&idkpi=' + idkpi;
    tujuan = 'sdm_slave_2kpi.php';
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
    notransaksi     =document.getElementById('notransaksi_ajukan').value;
    jlh         =document.getElementById('jlh').value;
    var param   = 'method=ajukan';
    param       += '&notransaksi=' + notransaksi;
    param       += '&jlh=' + jlh;
    for (i = 1; i <= jlh; i++) {
        param += "&" + 'kepada'+ i + "=" + document.getElementById('kepada'+i).value;
    }
    if(jlh==0){
        alertify.alert("Warning: Approval kosong");
        return;
    }
    tujuan = 'sdm_slave_2kpi.php';
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
                    alert('Sucses');
                    loaddata(0);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function posting(idkpi){
	namaatasan = document.getElementById('namaatasan').value;
    param = 'method=posting';
    param += '&idkpi=' + idkpi;
    param += '&namaatasan=' + namaatasan;
    tujuan = 'sdm_slave_2kpi.php';
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

function formreject(idkpi){
	content="<table>";
	content+="<tr><td>Komentar :</td></tr>";
	content+="<tr><td><textarea class=myinputtext style='width:300px;height:100px;' id=komentar></textarea></td></tr>";
	content+="<tr><td align=center><button style=color:red;border-color:red; class=mybutton title='Reject' onclick=\"rejectkpi("+idkpi+");\">Reject</button></td></tr>";
	content+="</table>";
	
	//alertify.popup("Detail",content).set({'resizable':true,'maximizable':true}).resizeTo('400px','300px');
	
    alertify.popup().set({'resizable':true,'maximizable':false,'message':content,'title':'Reject ?'}).resizeTo('400px','300px').show();
}
function reject(idkpi){
	komentar = document.getElementById('komentar').value;
	
    param = 'method=reject';
    param += '&idkpi=' + idkpi;
	param += '&komentar=' + komentar;
    tujuan = 'sdm_slave_2kpi.php';
    alertify.confirm("Reject","Anda yakin ???",
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
                    getdetail('KPI');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function approve(idkpi){
    param = 'method=approve';
    param += '&idkpi=' + idkpi;
    tujuan = 'sdm_slave_2kpi.php';
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
function unposting(idkpi){
    param = 'method=unposting';
    param += '&idkpi=' + idkpi;
    tujuan = 'sdm_slave_2kpi.php';
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
function deldt(idkpi,idht){
    param = 'method=deldt';
    param += '&idkpi=' + idkpi;
    param += '&idht=' + idht;
    tujuan = 'sdm_slave_2kpi.php';
    alertify.confirm("Hapus","Anda yakin ???",
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
                    loaddatadetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cekedit(no){
	document.getElementById('tombolsave'+no).style.backgroundColor='red';
	
	bobot       = document.getElementById('bobot'+no).value;
	porsiatasan = document.getElementById('porsiatasan'+no).value;
	porsisendiri= document.getElementById('porsisendiri'+no).value;
	
	nilaiatasan = document.getElementById('nilaiatasan'+no).value;
	nilaisendiri= document.getElementById('nilaisendiri'+no).value;
	
	proporsiatasan = parseFloat(nilaiatasan)*(parseFloat(bobot)/100)*(parseFloat(porsiatasan)/100);
	if(isNaN(proporsiatasan)){proporsiatasan=0;}
	document.getElementById('proporsiatasan'+no).value=numberFormat(proporsiatasan,2);
	
	proporsisendiri = parseFloat(nilaisendiri)*(parseFloat(bobot)/100)*(parseFloat(porsisendiri)/100);
	if(isNaN(proporsisendiri)){proporsisendiri=0;}
	document.getElementById('proporsisendiri'+no).value=numberFormat(proporsisendiri,2);
	
	
	ttlproporsi = parseFloat(proporsisendiri)+parseFloat(proporsiatasan);
	if(isNaN(ttlproporsi)){ttlproporsi=0;}
	document.getElementById('ttlproporsi'+no).value=numberFormat(ttlproporsi,2);
	
	if(nilaiatasan>120){
		alertify.alert("Nilai maksimal 120 point");
		document.getElementById('nilaiatasan'+no).value=0;
		document.getElementById('proporsiatasan'+no).value=0;
		return;
	}
	if(nilaisendiri>120){
		alertify.alert("Nilai maksimal 120 point");
		document.getElementById('nilaisendiri'+no).value=0;
		document.getElementById('proporsisendiri'+no).value=0;
		return;
	}
	
	if(porsisendiri==0){
		document.getElementById('nilaisendiri'+no).value=0;
		document.getElementById('proporsisendiri'+no).value=0;
	}
	if(porsiatasan==0){
		document.getElementById('nilaiatasan'+no).value=0;
		document.getElementById('proporsiatasan'+no).value=0;
	}
	
	//totalbobot()
}
function totalbobot(){
	bbt = document.getElementsByName('bobot[]');
	
	total = 0;
	for(i=0;i<bbt.length;i++){
		total = total + parseFloat(bbt[i].value);
	}
	if(isNaN(total)){total=0;}
	document.getElementById('totaldt').value=total;
	if(total!=100){
		document.getElementById('totaldt').style.backgroundColor='red';
	}else{
		document.getElementById('totaldt').style.backgroundColor='';
	}

}
function simpandtall(method,jenis,arno){
dataar=arno.split('###');
let jlh=dataar.length-1;
simpandt(method,jenis,dataar[0],arno,jlh,0);

}

function simpandt(method,jenis,no,arno,jlh,urutan){
	idht           = document.getElementById('idht').value;
	idkpi          = document.getElementById('idkpi'+no).value;
	kpi            = document.getElementById('kpi'+no).value;
	bobot          = document.getElementById('bobot'+no).value;
	idtextkpi      = no;
	if(typeof document.getElementById('target'+no) !== 'undefined' && document.getElementById('target'+no) !== null){
		target= document.getElementById('target'+no).value;
		realisasi = document.getElementById('realisasi'+no).value;
		skor   = document.getElementById('skor'+no).value;
		nilaiakhir= document.getElementById('nilaiakhir'+no).value;
	}else{
		target='';
		realisasi='';
		skor='';
		nilaiakhir='';
	}

	
	totaldt        = document.getElementById('totaldt').value;
	
	//alert(plusminus);

	param  = '';
	param += '&penilaian=' + '';
	param += '&target=' + target;
	param += '&realisasi=' + realisasi;
	param += '&skor=' + skor;
	param += '&nilaiakhir=' + nilaiakhir;
	param += '&bobot=' + bobot;
	param += '&kpi=' + kpi;
	param += '&idht=' + idht;
	param += '&idkpi=' + idkpi;
	param += '&idtextkpi=' + idtextkpi;
	param += '&method=' + method;
	param += '&jenis=' + jenis;
	
	validate([
        ["bobot"+no,"Bobot tidak boleh kosong"]
    ]);
	
	if(totaldt>100){
		alertify.alert("Total bobot tidak boleh lebih besar dari 100%"); return;
	}
		// if(nilaiakhir<0){
		// 	alertify.alert("Nilai Akhir tidak boleh lebih kecil dari 0"); return;
		// }
	
	tujuan = 'sdm_slave_2kpi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {

					if(urutan!=jlh){
                		urutanbaru=urutan+1;
						dataar=arno.split('###');
                		databaru=dataar[urutanbaru];
						console.log('masuk ke :'+databaru+ ' dengan nilai :'+arno);
                		simpandt(method,jenis,databaru,arno,jlh,urutanbaru);
                	}else{
						document.getElementById('tombolsave').style.backgroundColor='';
                	}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function saveaddnew(jenis){
	method      = document.getElementById('methodaddnew').value;
	kpi         = document.getElementById('kpinew').value;
	bobot       = document.getElementById('bobotnew').value;
	idht        = document.getElementById('idht').value;
	idkpi       = document.getElementById('idkpi').value;
	totaldt     = document.getElementById('totaldt').value;

	param  = '';
	param += '&bobot=' + bobot;
	param += '&kpi=' + kpi;
	param += '&jenis=' + jenis;
	param += '&idht=' + idht;
	param += '&idkpi=' + idkpi;
	param += '&method=' + method;
	
	validate([
        ["kpinew","KPI tidak boleh kosong."],
        ["bobotnew","Bobot tidak boleh kosong."]
    ]);
	
	if(totaldt>100){
		alertify.alert("Total bobot tidak boleh lebih besar dari 100%"); return;
	}
	
	tujuan = 'sdm_slave_2kpi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					document.getElementById('kpinew').value="";
					document.getElementById('bobotnew').value="";
					loaddatadetail();
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function tambahkpi(jenis,idkpi,idht){
	param  = '';
	param += '&idkpi=' + idkpi;
	param += '&idht=' + idht;
	param += '&jenis=' + jenis;
	param += '&method=addnew';
	
	tujuan = 'sdm_slave_2kpi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					alertify.popup().destroy();
					alertify.popup(jenis,"<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('55%','50%');
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:false
						});
						// $('.select2-selection--single').height(30).css({
							// cursor: "auto"
						// });
						// $('.select2-selection__arrow b').css({
							// top: "70%"
						// });
						// $('.select2-selection__rendered').css({
							// 'line-height': '31px'
						// });
					});
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
    document.getElementById('entry').style.display = 'none';
    document.getElementById('listkriteria').style.display='none';
    document.getElementById('loadpreview').style.display='block';
    reset();
    loaddata(0);
}

function getDept(nama, iddept){
    param = 'method=getDept&nama='+nama;
    tujuan = 'sdm_slave_2kpi.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					data = con.responseText.split("####");
					
                    setValue2(iddept, data[0]);
                    setValue2('jabatan', data[1]);
                    setValue2('lokasitugas', data[2]);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpan(){
	let karyawanid   = document.getElementById('nama').value;
	let jabatan      = document.getElementById('jabatan').value;
	let lokasitugas  = document.getElementById('lokasitugas').value;
	let dept         = document.getElementById('dept').value;
	let tglnilai     = document.getElementById('tglnilai').value;
	let thnnilai     = document.getElementById('thnnilai').value;
	let penilaian    = document.getElementById('penilaian').value;
	let bulandr      = document.getElementById('bulandr').value;
	let bulansd      = document.getElementById('bulansd').value;
	let manmanagement= document.getElementById('manmanagement').value;
	let method       = document.getElementById('method').value;

    validate([
        ["nama","Nama Karyawan tidak boleh kosong."],
        ["jabatan","Jabatan tidak boleh kosong"],
        ["lokasitugas","Lokasi Tugas tidak boleh kosong"],
        ["thnnilai","Tahun Penilaian tidak boleh kosong"]
    ]);

    param  = 'method='+method;
    param += '&karyawanid='+karyawanid+'&dept='+dept;
    param += '&jabatan='+jabatan+'&lokasitugas='+lokasitugas;
    param += '&tglnilai='+tglnilai+'&thnnilai='+thnnilai;
    param += '&penilaian='+penilaian+'&bulandr='+bulandr;
    param += '&bulansd='+bulansd+'&manmanagement='+manmanagement;
    
    tujuan = 'sdm_slave_2kpi.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    alertify.alert('Done');
					document.getElementById('method').value='update';
                    loaddatadetail();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function loaddatadetail() {
	let karyawanid   = document.getElementById('nama').value;
	let jabatan      = document.getElementById('jabatan').value;
	let lokasitugas  = document.getElementById('lokasitugas').value;
	let dept         = document.getElementById('dept').value;
	let tglnilai     = document.getElementById('tglnilai').value;
	let thnnilai     = document.getElementById('thnnilai').value;
	let penilaian    = document.getElementById('penilaian').value;
	let bulandr      = document.getElementById('bulandr').value;
	let bulansd      = document.getElementById('bulansd').value;
	let manmanagement= document.getElementById('manmanagement').value;

    param  = 'method=loaddatadetail';
    param += '&karyawanid='+karyawanid+'&dept='+dept;
    param += '&jabatan='+jabatan+'&lokasitugas='+lokasitugas;
    param += '&tglnilai='+tglnilai+'&thnnilai='+thnnilai;
    param += '&penilaian='+penilaian+'&bulandr='+bulandr;
    param += '&bulansd='+bulansd+'&manmanagement='+manmanagement;
    tujuan = 'sdm_slave_2kpi.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					document.getElementById('listkriteria').style.display="block";
                    document.getElementById('container').innerHTML = con.responseText;
					//totalbobot();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function reset(){
    setValue2('nama',null);
    setValue2('dept',null);
    setValue2('jabatan',null);
    setValue2('lokasitugas',null);
    setValue2('penilaian',null);
    setValue2('thnnilai',null);

    document.getElementById('nama').disabled=false;
    document.getElementById('penilaian').disabled=false;
    document.getElementById('thnnilai').disabled=false;


    setValue2('scjenis',null);
    setValue2('scnama',null);
    setValue2('scdept',null);
    setValue2('scthn',null);
    //document.getElementById('tglnilai').value='';
    document.getElementById('container').innerHTML='';
    document.getElementById('method').value='insert';
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
    tujuan = 'sdm_slave_2kpi.php';

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

function fillField(id,karyawanid,jabatan,dept,manmanagement,penilaian,tahun,periodedr,periodesd,tanggal,lokasitugas) {
    newdata();
	setValue2('id',id);
	setValue2('nama',karyawanid);
	setValue2('jabatan',jabatan);
	setValue2('lokasitugas',lokasitugas);
	setValue2('dept',dept);
	setValue2('penilaian',penilaian);
	setValue2('thnnilai',tahun);
	setValue2('bulandr',periodedr);
	setValue2('bulansd',periodesd);
	setValue2('manmanagement',manmanagement);
	setValue2('tglnilai',tanggal);
	setValue2('method','update');
	document.getElementById('nama').disabled = true;
	document.getElementById('penilaian').disabled = true;
	document.getElementById('thnnilai').disabled = true;
	
	validate([
        ["nama","Nama Karyawan tidak boleh kosong."],
        ["jabatan","Jabatan tidak boleh kosong"],
        ["lokasitugas","Lokasi Tugas tidak boleh kosong"],
        ["thnnilai","Tahun Penilaian tidak boleh kosong"],
        ["tglnilai","Tanggal Penilaian tidak boleh kosong"]
    ]);
	
	loaddatadetail();
}

function formposting(idkpi){
    param = 'method=formposting';
    param += '&idkpi=' + idkpi;
    tujuan = 'sdm_slave_2kpi.php';
	post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					alertify.popup().destroy();
                    alertify.popup("Ajukan ?","<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':false}).resizeTo('500px','200px');
					$(document).ready(function() {
						$('.select2').select2({
							dropdownAutoWidth:false
						});
						// $('.select2-selection--single').height(30).css({
							// cursor: "auto"
						// });
						// $('.select2-selection__arrow b').css({
							// top: "70%"
						// });
						// $('.select2-selection__rendered').css({
							// 'line-height': '31px'
						// });
					});
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletedata(id) {
    param = 'method=hapus';
    param += '&id=' + id;
    tujuan = 'sdm_slave_2kpi.php';
    alertify.confirm("Informasi","Anda yakin ???",
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

function getskore(id,nox) {
    tipepenilaian=document.getElementById('tipepenilaian'+id).value
    realisasi=document.getElementById('realisasi'+id).value;
    target=document.getElementById('target'+id).value;

    param = 'method=getskore';
    param += '&id=' + id;
    param += '&tipepenilaian=' + tipepenilaian;
    param += '&realisasi=' + realisasi;
    param += '&target=' + target;
    tujuan = 'sdm_slave_2kpi.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                   	data = con.responseText.split("####");
				    document.getElementById('skor'+id).value=data[0];
				    document.getElementById('nilaiakhir'+id).value=data[1];
				    getnilaiakhir(nox);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function getnilaiakhir(nox) {
	arrayno=nox.split('###');
    totalxx=0;
    totalbbt=0;
    for (var i =0; i <arrayno.length; i++) {
    	//console.log('yang ke :'+arrayno[i]);
    	if(typeof document.getElementById('nilaiakhir'+arrayno[i]) !== 'undefined' && document.getElementById('nilaiakhir'+arrayno[i]) !== null){

    		//console.log('masuk ke :'+arrayno[i]+ ' dengan nilai :'+parseFloat(document.getElementById('nilaiakhir'+arrayno[i]).value));
    	 	totalxx=totalxx+parseFloat(document.getElementById('nilaiakhir'+arrayno[i]).value);
    	}

    	// if(typeof document.getElementById('bobot'+arrayno[i]) !== 'undefined' && document.getElementById('bobot'+arrayno[i]) !== null && document.getElementById('target'+arrayno[i]) == null){

    	// 	//console.log('masuk ke :'+arrayno[i]+ ' dengan nilai :'+parseFloat(document.getElementById('bobot'+arrayno[i]).value));
    	//  	totalbbt=totalbbt+parseFloat(document.getElementById('bobot'+arrayno[i]).value);
    	// }
    }
    document.getElementById('ttlnilaiakhir').value=totalxx;
    //document.getElementById('totaldt').value=totalbbt;
	// if(totalbbt!=100){
	// 	document.getElementById('totaldt').style.backgroundColor='red';
	// }else{
	// 	document.getElementById('totaldt').style.backgroundColor='';
	// }
}

function detail(id){
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

function pdf(id){
    param = 'method=detail';
    param += '&id=' + id;
    param += '&tipeprint=pdf';
    tujuan = 'sdm_slave_2kpi.php';
	tujuan = tujuan + "?" + param;
	alertify.popup("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" + tujuan + "'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}