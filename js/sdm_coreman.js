function approve(id){
    param = 'method=approve';
    param += '&id=' + id;
    tujuan = 'sdm_slave_coreman.php';
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
                    loaddata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function posting(id){
    param = 'method=posting';
    param += '&id=' + id;
    tujuan = 'sdm_slave_coreman.php';
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
    tujuan = 'sdm_slave_coreman.php';
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
function newdata(fromkpi){
    document.getElementById('entry').style.display='block';
    document.getElementById('listkriteria').style.display='block';
    document.getElementById('loadpreview').style.display='none';
    reset(fromkpi);
    loadbytipe();
}

function displaylist() {
    document.getElementById('entry').style.display = 'none';
    document.getElementById('listkriteria').style.display='none';
    document.getElementById('loadpreview').style.display='block';
    reset();
    loaddata(0);
}

function getDept(nama, iddept){
	let thnnilai = document.getElementById('thnnilai').value;
    param  = 'method=getDept&nama='+nama;
	param += '&thnnilai=' + thnnilai;
    tujuan = 'sdm_slave_coreman.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
					data = con.responseText.split("##");
                    setValue2(iddept, data[0]);
                    setValue2('atasan', data[1]);
					document.getElementById('tipe').innerHTML=data[2];
					getSelect2();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadbytipe(id,sumber){
    tipe = document.getElementById('tipe').value;
	let nama     = document.getElementById('nama').value;
	let thnnilai = document.getElementById('thnnilai').value;
	let penilaian= document.getElementById('penilaian').value;
	
	
    param = 'method=loadbytipe&tipe='+tipe;
	param += '&id=' + id;
	param += '&nama=' + nama;
	param += '&thnnilai=' + thnnilai;
	param += '&penilaian=' + penilaian;
	param += '&sumber=' + sumber;
    tujuan = 'sdm_slave_coreman.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
					setValue2('tipe',corevalue);
                } else {
                    ar = con.responseText.split("###");
                    document.getElementById('kriteria').innerHTML = ar[0];
                    document.getElementById('container').innerHTML = ar[1];
                    // leftFixedTable();
                    // loaddata(0);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function fillnilai(kode,nilai,loop,jenis){
    var arrnilai = {1:"F", 2:"A", 3:"S", 4:"T", 5:"E", 6:"R"};
    if (jenis == 'manmanagement') {
        var kd = arrnilai[kode];
    } else {
        var kd = kode;
    }
    var elements = document.getElementsByClassName(kd); 
    for(var a = 0; a < elements.length; a++){
        elements[a].style.color = "black";
    }
    document.getElementById(kd+nilai).style.setProperty('color', 'red', 'important');

    document.getElementById('nilai'+kode).value = nilai;

    var x = document.getElementsByClassName("nilai");
    var ratarata = 0;
    for(var i=0; i<loop; i++){
        ratarata = ratarata + parseInt(x[i].value);
    }
    
    document.getElementById('ratarata').value = numberFormat(ratarata/loop,2);
	
    document.getElementById('tombolsimpan').style.backgroundColor='red';
}

function simpan(){
	let id       = document.getElementById('id').value;
	let tipe     = document.getElementById('tipe').value;
	let nama     = document.getElementById('nama').value;
	let dept     = document.getElementById('dept').value;
	let tglnilai = document.getElementById('tglnilai').value;
	let thnnilai = document.getElementById('thnnilai').value;
	let kekuatan = document.getElementById('kekuatan').value;
	let kelemahan= document.getElementById('kelemahan').value;
	let method   = document.getElementById('method').value;
	let penilaian= document.getElementById('penilaian').value;
	let atasan   = document.getElementById('atasan').value;

    var arrnilai = {1:"F", 2:"A", 3:"S", 4:"T", 5:"E", 6:"R"};
    let strnilai = strnilaip2 = ''; 
    for (var key in arrnilai) {
        var val = arrnilai[key];
        if (tipe == 'corevalue') {
            strnilai += '&nilai['+val+']='+trim(document.getElementById('nilai'+val).value);
            if(trim(document.getElementById('nilai'+val).value)=='0'){
                alert('Silakan isi nilai');
                return;
            }
        } else if (tipe == 'manmanagement') {
            if(key==6){
                continue;
            }
            strnilai += '&nilai['+key+']='+trim(document.getElementById('nilai'+key).value);
            if(trim(document.getElementById('nilai'+key).value)=='0'){
                alert('Silakan isi nilai');
                return;
            }
        }
    }

    validate([
        ["nama","Nama Karyawan tidak boleh kosong."],
        ["thnnilai","Tahun Penilaian tidak boleh kosong"],
        ["tglnilai","Tanggal Penilaian tidak boleh kosong"],
        ["kekuatan","Kekuatan tidak boleh kosong"],
        ["kelemahan","Kelemahan tidak boleh kosong"],
        ["atasan","Atasan Penilai tidak boleh kosong"]
    ]);

    param = 'method='+method+'&tipe='+tipe+'&id='+id;
    param += '&nama='+nama+'&dept='+dept;
    param += '&tglnilai='+tglnilai+'&thnnilai='+thnnilai;
    param += '&kekuatan='+kekuatan+'&kelemahan='+kelemahan;
    param += '&penilaian='+penilaian;
    param += '&atasan='+atasan;
    param += strnilai;
    param += strnilaip2;
    tujuan = 'sdm_slave_coreman.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    alertify.alert('Done');
                    displaylist();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function reset(fromkpi){
    document.getElementById('id').value='';
	if(fromkpi!='fromkpi'){		
		setValue2('nama',null);
		setValue2('dept',null);
		//setValue2('thnnilai',null);
		setValue2('scjenis',null);
		setValue2('scnama',null);
		setValue2('scdept',null);
		setValue2('scthn',null);
		setValue2('atasan',null);
	   // document.getElementById('tglnilai').value='';
	}
    
    document.getElementById('kekuatan').value='';
    document.getElementById('kelemahan').value='';
    document.getElementById('method').value='insert';
    document.getElementsByClassName('nilai').value=0;
	
	document.getElementById('tipe').disabled = false;
	document.getElementById('nama').disabled = false;
	document.getElementById('thnnilai').disabled = false;
	document.getElementById('penilaian').disabled = false;
	document.getElementById('atasan').disabled = false;
	//document.getElementById('tglnilai').disabled = false;
	document.getElementById('kekuatan').disabled = false;
	document.getElementById('kelemahan').disabled = false;
	
	document.getElementById('tombolsimpan').style.backgroundColor='';
	
	var x = document.getElementsByClassName("nilai");
    for(var i=0; i<x.length; i++){
        x[i].value='0';
    }
	var x = document.getElementsByClassName("nilain");
    for(var i=0; i<x.length; i++){
        x[i].value='0';
    }
	
	var n = document.getElementsByName("detailkriteria[]");
    for(var i=0; i<n.length; i++){
        n[i].style.setProperty('color', 'black', 'important');
    }
    
}
function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);	
}
function loaddata(page) {
    let tipe = document.getElementById('scjenis').value;
    let nama = document.getElementById('scnama').value;
    let dept = document.getElementById('scdept').value;
    let thnnilai = document.getElementById('scthn').value;
    let penilaian = document.getElementById('scpenilaian').value;
    let unit = document.getElementById('scunit').value;
    let posting = document.getElementById('scpost').value;
    let golongan = document.getElementById('scgol').value;

    param = 'method=loaddata';
    param += '&penilaian=' + penilaian;
    param += '&unit=' + unit;
    param += '&posting=' + posting;
    param += '&golongan=' + golongan;
    param += '&page=' + page;
    param += '&tipe='+tipe;
    param += '&nama='+nama;
    param += '&dept='+dept;
    param += '&thnnilai='+thnnilai;
    tujuan = 'sdm_slave_coreman.php';

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

function fillField(id) {
    newdata();
    param = 'method=fillField';
    param += '&id=' + id;
    tujuan = 'sdm_slave_coreman.php';

    post_response_text(tujuan, param, respon);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    arr = con.responseText.split("###");
                    document.getElementById('id').value = id;
                    setValue2('tipe',arr[0]);
                    setValue2('nama',arr[1]);
                    setValue2('dept',arr[2]);
                    setValue2('thnnilai',arr[4]);
                    setValue2('penilaian',arr[7]);
                    setValue2('atasan',arr[8]);
                    document.getElementById('tglnilai').value = arr[3];
                    document.getElementById('kekuatan').value = arr[5];
                    document.getElementById('kelemahan').value = arr[6];
                    document.getElementById('method').value = 'update';
					
                    document.getElementById('tipe').disabled = true;
                    document.getElementById('nama').disabled = true;
                    document.getElementById('dept').disabled = true;
                    document.getElementById('thnnilai').disabled = true;
                    document.getElementById('penilaian').disabled = true;
                    document.getElementById('atasan').disabled = true;
                    document.getElementById('tglnilai').disabled = true;
                    document.getElementById('kekuatan').disabled = true;
                    document.getElementById('kelemahan').disabled = true;
					
					
                    param = 'method=loadbytipe&tipe='+arr[0];
					param += '&id=' + id;
                    tujuan = 'sdm_slave_coreman.php';

                    post_response_text(tujuan, param, respon);
                    function respon() {
                        if (con.readyState == 4) {
                            if (con.status == 200) {
                                busy_off();
                                if (!isSaveResponse(con.responseText)) {
                                    alertify.alert(con.responseText);
                                } else {
                                    ar = con.responseText.split("###");
                                    document.getElementById('kriteria').innerHTML = ar[0];
                                    document.getElementById('container').innerHTML = ar[1];
									
                                    let ratarata = rataratan = 0;
                                    var arrnilai = {1:"F", 2:"A", 3:"S", 4:"T", 5:"E", 6:"R"};
                                    if(arr[0] == 'corevalue') {
                                        for(i=9;i<15;i++){
                                            let idnilai = arr[i].substr(0,1);
                                            let nilai = arr[i].substr(1,3);
                                            document.getElementById('nilain'+idnilai).value = nilai;
                                            rataratan += parseInt(nilai);
                                            document.getElementById('rataratan').value = numberFormat(rataratan/6,2);
                                            //document.getElementById(idnilai+nilai).style.setProperty('color', 'red', 'important');
                                        }
										for(i=15;i<21;i++){
                                            let idnilai = arr[i].substr(0,1);
                                            let nilai = arr[i].substr(1,3);
                                            document.getElementById('nilai'+idnilai).value = nilai;
                                            ratarata += parseInt(nilai);
                                            document.getElementById('ratarata').value = numberFormat(ratarata/6,2);
                                            document.getElementById(idnilai+nilai).style.setProperty('color', 'red', 'important');
                                        }
                                    } else {
                                        for(i=9;i<14;i++){
                                            let idnilai = arr[i].substr(0,1);
                                            let nilai = arr[i].substr(1,3);
                                            document.getElementById('nilain'+idnilai).value = nilai;
                                            rataratan += parseInt(nilai);
                                            document.getElementById('rataratan').value = numberFormat(rataratan/5,2);
                                            //document.getElementById(arrnilai[idnilai]+nilai).style.setProperty('color', 'red', 'important');
                                        }
										for(i=14;i<19;i++){
                                            let idnilai = arr[i].substr(0,1);
                                            let nilai = arr[i].substr(1,3);
                                            document.getElementById('nilai'+idnilai).value = nilai;
                                            ratarata += parseInt(nilai);
                                            document.getElementById('ratarata').value = numberFormat(ratarata/5,2);
                                            document.getElementById(arrnilai[idnilai]+nilai).style.setProperty('color', 'red', 'important');
                                        }
                                    }
                                }
                            } else {
                                busy_off();
                                error_catch(con.status);
                            }
                        }
                    }
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
    tujuan = 'sdm_slave_coreman.php';
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

function pdf(id){
    param = 'method=detail';
    param += '&id=' + id;
    param += '&tipeprint=pdf';
    tujuan = 'sdm_slave_coreman.php';
	
	tujuan = tujuan + "?" + param;
	alertify.popuppdf("PDF","<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='"+tujuan+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
	/* 
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
                    alertify.popup(title,"<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" + tujuan + "'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('90%','85%');
					
					
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }       */ 
}