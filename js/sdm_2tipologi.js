function getgraph(id,jenis){
    param = 'method=getgraph';
	if(id==''){
		a = document.getElementById('jumlah2_A').value;
		b = document.getElementById('jumlah2_B').value;
		c = document.getElementById('jumlah2_C').value;
		d = document.getElementById('jumlah2_D').value;
		param += '&a=' + a;
		param += '&b=' + b;
		param += '&c=' + c;
		param += '&d=' + d;
	}
	
	param += '&id=' + id;
	param += '&jenis=' + jenis;
    tujuan = 'sdm_slave_2tipologi.php';
	post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					if(jenis=='pdf'){
						
					}else{						
						document.getElementById('graph').innerHTML="";
						document.getElementById('graph').innerHTML=con.responseText;
					}
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function cekisi(val, row, kol){
	const col = ["A", "B", "C", "D"];
	let fLen = col.length;
	
	jlh=total=0;
	for (let i = 0; i < fLen; i++) {
		total = trim(document.getElementById('isi_'+row+"_"+col[i]).value);
		if(isNaN(parseFloat(total))){
			total=0;
		}
		jlh = parseFloat(jlh)+parseFloat(total);
	}
	
	document.getElementById('total_'+row).value=jlh;
	if(jlh>10){
		alertify.alert("Nilai salah, silahkan dicek kembali.");
	}
	
	//jumlah bawah
	jlh=total=0;
	for (let e = 1; e <= 5; e++) {
		total = trim(document.getElementById('isi_'+e+"_"+kol).value);
		if(isNaN(parseFloat(total))){
			total=0;
		}
		jlh = parseFloat(jlh)+parseFloat(total);				
	}
	document.getElementById('jumlah_'+kol).value=jlh;
	document.getElementById('jumlah2_'+kol).value=jlh*2;
	
	getgraph("","");
}

function posting(id){
    param = 'method=posting';
    param += '&id=' + id;
    tujuan = 'sdm_slave_2tipologi.php';
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
                    loaddata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpandt(){
	idht = document.getElementById('id').value;
	
	const col = ["A", "B", "C", "D"];
	let fLen = col.length;
	param  = '';
	
	jlh=isi=0;
	for(r = 1; r <= 5; r++){
		for (let i = 0; i < fLen; i++) {
			isi = trim(document.getElementById('isi_'+r+"_"+col[i]).value);
			param += '&nilai['+r+']['+col[i]+']=' + isi;
		}
		
		
		ttl = trim(document.getElementById('total_'+r).value);
		if(ttl!=10){
			alert("Jumlah salah."); return;
		}
	}
	
	param += '&method=simpandt';
	param += '&idht=' + idht;
	
	
	tujuan = 'sdm_slave_2tipologi.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alertify.alert(con.responseText);
				} else {
					// document.getElementById('rowdt'+no).style.setProperty('color', 'black', 'important');
					// document.getElementById('tombolsave'+no).style.backgroundColor='';
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
    tujuan = 'sdm_slave_2tipologi.php';

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
	let method       = document.getElementById('method').value;
	let idht       = document.getElementById('id').value;

    validate([
        ["nama","Nama Karyawan tidak boleh kosong."],
        ["jabatan","Jabatan tidak boleh kosong"],
        ["lokasitugas","Lokasi Tugas tidak boleh kosong"],
        ["tglnilai","Tanggal Penilaian tidak boleh kosong"]
    ]);

    param  = 'method='+method;
    param += '&karyawanid='+karyawanid+'&dept='+dept;
    param += '&jabatan='+jabatan+'&lokasitugas='+lokasitugas;
    param += '&tglnilai='+tglnilai;
    param += '&idht='+idht;
    
    tujuan = 'sdm_slave_2tipologi.php';
    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					// alert(con.responseText);
                   // alertify.alert('Done');
					//document.getElementById('method').value='update';
					document.getElementById('id').value=con.responseText;
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
	let idht     = document.getElementById('id').value;

    param  = 'method=loaddatadetail';
    param += '&karyawanid='+karyawanid+'&dept='+dept;
    param += '&jabatan='+jabatan+'&lokasitugas='+lokasitugas;
    param += '&tglnilai='+tglnilai;
    param += '&idht='+idht;
    
    tujuan = 'sdm_slave_2tipologi.php';

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
					getgraph("","");
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
    //setValue2('thnnilai',null);
    //setValue2('scjenis',null);
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

    param = 'method=loaddata';
    param += '&page=' + page;
    param += '&nama='+nama;
    param += '&dept='+dept;
    param += '&thnnilai='+thnnilai;
    tujuan = 'sdm_slave_2tipologi.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('listdata').innerHTML = con.responseText;
                    leftFixedTable();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function fillField(id,karyawanid,jabatan,dept,tanggal,lokasitugas) {
    newdata();
	setValue2('id',id);
	setValue2('nama',karyawanid);
	setValue2('jabatan',jabatan);
	setValue2('lokasitugas',lokasitugas);
	setValue2('dept',dept);
	setValue2('tglnilai',tanggal);
	setValue2('method','update');
	
	 validate([
        ["nama","Nama Karyawan tidak boleh kosong."],
        ["jabatan","Jabatan tidak boleh kosong"],
        ["lokasitugas","Lokasi Tugas tidak boleh kosong"],
        ["tglnilai","Tanggal Penilaian tidak boleh kosong"]
    ]);
	
	loaddatadetail();
}

function deletedata(id) {
    param = 'method=hapus';
    param += '&id=' + id;
    tujuan = 'sdm_slave_2tipologi.php';
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

function detail(id){
    param = 'method=detail';
    param += '&id=' + id;
    param += '&tipeprint=html';
    tujuan = 'sdm_slave_2tipologi.php';

    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {                    
                    alertify.popup().set({'resizable':true,'maximizable':true,'message':con.responseText}).resizeTo('70%','70%').show();
					getgraph(id,"");
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }       
}

function pdf(id){
	getgraph(id,'pdf');
	
    param = 'method=detail';
    param += '&id=' + id;
    param += '&tipeprint=pdf';
    tujuan = 'sdm_slave_2tipologi.php';
	tujuan = tujuan + "?" + param;
	alertify.popup("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" + tujuan + "'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
}