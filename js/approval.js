function batal() {
    //document.getElementById('kodeorg').selectedIndex=0;
    //document.getElementById('jenispersetujuan').selectedIndex=0;
    document.getElementById('level').selectedIndex = 0;
    document.getElementById('nilaidari').value = '';
    document.getElementById('nilaisampai').value = '';
    document.getElementById('karyawanid').selectedIndex = 0;
    document.getElementById('departemen').selectedIndex = 0;
    document.getElementById('tipekaryawan').selectedIndex = 0;
    document.getElementById('golongan').selectedIndex = 0;
    document.getElementById('jabatan').selectedIndex = 0;
    document.getElementById('method').value = "simpan";
    document.getElementById('tipe').checked = false;
    document.getElementById('kodeorgold').value = '';
    document.getElementById('jenispersetujuanold').value = '';
    document.getElementById('levelold').value = '';
    document.getElementById('karyawanidold').value = '';
    document.getElementById('departemenold').value = '';
    document.getElementById('jabatanold').value = '';
    document.getElementById('tipekaryawanold').value = '';
    document.getElementById('golonganold').value = '';
    document.getElementById('tipeold').value = '';
    document.getElementById('karyawaniduser').innerHTML = '';

}

function batalcari() {
    document.getElementById('crkodeorg').selectedIndex = 0;
    document.getElementById('crjenispersetujuan').selectedIndex = 0;
    document.getElementById('crlevel').selectedIndex = 0;
    document.getElementById('crkaryawanid').selectedIndex = 0;
    document.getElementById('crdepartemen').selectedIndex = 0;
    document.getElementById('crtipekaryawan').selectedIndex = 0;
    document.getElementById('crgolongan').selectedIndex = 0;
    document.getElementById('crjabatan').selectedIndex = 0;
}

function batalcari2() {
    document.getElementById('cr2kodeorg').selectedIndex = 0;
    document.getElementById('cr2jenispersetujuan').selectedIndex = 0;
    document.getElementById('cr2level').selectedIndex = 0;
    document.getElementById('cr2karyawanid').selectedIndex = 0;
    document.getElementById('cr2departemen').selectedIndex = 0;
    document.getElementById('cr2tipekaryawan').selectedIndex = 0;
    document.getElementById('cr2golongan').selectedIndex = 0;
    document.getElementById('cr2jabatan').selectedIndex = 0;
    loaddata();
}

function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);	
}


function loaddata(page=0,tipeprint='') {
	kodeunit        = document.getElementById('crkodeorg').options[document.getElementById('crkodeorg').selectedIndex].value;
	jenispersetujuan= document.getElementById('crjenispersetujuan').options[document.getElementById('crjenispersetujuan').selectedIndex].value;
	level           = document.getElementById('crlevel').options[document.getElementById('crlevel').selectedIndex].value;
	karyawanid      = document.getElementById('crkaryawanid').options[document.getElementById('crkaryawanid').selectedIndex].value;
	departemen      = document.getElementById('crdepartemen').options[document.getElementById('crdepartemen').selectedIndex].value;
	tipekaryawan    = document.getElementById('crtipekaryawan').options[document.getElementById('crtipekaryawan').selectedIndex].value;
	golongan        = document.getElementById('crgolongan').options[document.getElementById('crgolongan').selectedIndex].value;
	jabatan         = document.getElementById('crjabatan').options[document.getElementById('crjabatan').selectedIndex].value;
	karyawaniduser  = document.getElementById('crkaryawaniduser').options[document.getElementById('crkaryawaniduser').selectedIndex].value;

    param = 'method=loaddata&kodeunit=' + kodeunit + '&jenispersetujuan=' + jenispersetujuan + '&level=' + level + '&karyawanid=' + karyawanid + '&departemen=' + departemen + '&tipekaryawan=' + tipekaryawan + '&golongan=' + golongan  + '&jabatan=' + jabatan+ '&page=' + page+ '&karyawaniduser=' + karyawaniduser+'&tipeprint='+tipeprint;
    tujuan = 'setup_slave_approval.php';
	
	if(tipeprint!=''){
		printnopopup(tujuan+'?'+param);
		return false;
	}
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					document.getElementById('container').innerHTML = con.responseText;
					leftFixedTable();
                    batal();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletefield(kodeunit, jenispersetujuan, level, karyawanid, departemen, tipekaryawan, golongan,tipe,jabatan) {
    param = 'method=delete' + '&kodeunit=' + kodeunit + '&jenispersetujuan=' + jenispersetujuan + '&level=' + level + '&karyawanid=' + karyawanid + '&departemen=' + departemen + '&tipekaryawan=' + tipekaryawan + '&golongan=' + golongan+ '&tipe=' + tipe+ '&jabatan=' + jabatan;
    tujuan = 'setup_slave_approval.php';

    if (confirm('Are You Sure Delete This Data?')) {
        post_response_text(tujuan, param, respog);
    }

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
function notiffield(kodeunit, jenispersetujuan, level, karyawanid, departemen, tipekaryawan, golongan,tipe,jabatan,karyawaniduser) {
    param = 'method=notiffield' + '&kodeunit=' + kodeunit + '&jenispersetujuan=' + jenispersetujuan + '&level=' + level + '&karyawanid=' + karyawanid + '&departemen=' + departemen + '&tipekaryawan=' + tipekaryawan + '&golongan=' + golongan+ '&tipe=' + tipe+ '&jabatan=' + jabatan;
    param += "&karyawaniduser=" + karyawaniduser;
	tujuan = 'setup_slave_approval.php';

	post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    alertify.popup("Detail notifikasi","<center>"+con.responseText+"</center>").set({'resizable':true,'maximizable':true}).resizeTo('350px','70%');
					loaddatanotif();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function loaddatanotif() {
	kodeunit        = document.getElementById('kodeunitnotif').value;
	jenispersetujuan= document.getElementById('jenispersetujuannotif').value;
	level           = document.getElementById('levelnotif').value;
	karyawanid      = document.getElementById('karynotif').value;
	departemen      = document.getElementById('departemennotif').value;
	tipekaryawan    = document.getElementById('tipekaryawannotif').value;
	golongan        = document.getElementById('golongannotif').value;
	karyawaniduser  = document.getElementById('karyawanidusernotif').value;
	
    param = 'method=loaddatanotif&kodeunit=' + kodeunit + '&jenispersetujuan=' + jenispersetujuan + '&level=' + level + '&karyawanid=' + karyawanid + '&departemen=' + departemen + '&tipekaryawan=' + tipekaryawan + '&golongan=' + golongan;
    param += "&karyawaniduser=" + karyawaniduser;

    tujuan = 'setup_slave_approval.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					document.getElementById('bodynotif').innerHTML = con.responseText
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpannotif() {
	kodeunit        = document.getElementById('kodeunitnotif').value;
	jenispersetujuan= document.getElementById('jenispersetujuannotif').value;
	level           = document.getElementById('levelnotif').value;
	karyawanid      = document.getElementById('karynotif').value;
	departemen      = document.getElementById('departemennotif').value;
	tipekaryawan    = document.getElementById('tipekaryawannotif').value;
	golongan        = document.getElementById('golongannotif').value;
	karyawaniduser  = document.getElementById('karyawanidusernotif').value;
	
    param = 'method=simpannotif&kodeunit=' + kodeunit + '&jenispersetujuan=' + jenispersetujuan + '&level=' + level + '&karyawanid=' + karyawanid + '&departemen=' + departemen + '&tipekaryawan=' + tipekaryawan + '&golongan=' + golongan;
    param += "&karyawaniduser=" + karyawaniduser;
    
    tujuan = 'setup_slave_approval.php';
	post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					loaddatanotif();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}
function deletenotif(id) {
	
    param = 'method=deletenotif&id=' + id;
    
    tujuan = 'setup_slave_approval.php';
	post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					loaddatanotif();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function fillField(kodeunit, jenispersetujuan, level, karyawanid, departemen, jabatan, tipekaryawan, golongan, tipe,karyawaniduser,namakary,nilaidari,nilaisampai) {
    document.getElementById('kodeorg').value = kodeunit;
    document.getElementById('kodeorgold').value = kodeunit;
    document.getElementById('jenispersetujuan').value = jenispersetujuan;
    document.getElementById('jenispersetujuanold').value = jenispersetujuan;
    document.getElementById('level').value = level;
    document.getElementById('levelold').value = level;
    document.getElementById('nilaidari').value = nilaidari;
    document.getElementById('nilaisampai').value = nilaisampai;
    document.getElementById('karyawanid').value = karyawanid;
	
    document.getElementById('karyawanidold').value = karyawanid;
    document.getElementById('departemen').value = departemen;
    document.getElementById('departemenold').value = departemen;
    document.getElementById('jabatan').value = jabatan;
    document.getElementById('jabatanold').value = jabatan;
    document.getElementById('tipekaryawan').value = tipekaryawan;
    document.getElementById('tipekaryawanold').value = tipekaryawan;
    document.getElementById('golongan').value = golongan;
    document.getElementById('golonganold').value = golongan;
    document.getElementById('tipeold').value = tipe;
    document.getElementById('method').value = 'update';
    if (tipe == 1) {
        document.getElementById('tipe').checked = true;
    } else {
        document.getElementById('tipe').checked = false;
    }

    if (jenispersetujuan == 'PR') {
        document.getElementById('tipekaryawan').disabled = true;
        document.getElementById('jabatan').disabled = true;
        document.getElementById('tipe').disabled = true;
    } else {
        document.getElementById('tipekaryawan').disabled = false;
        document.getElementById('jabatan').disabled = false;
        document.getElementById('tipe').disabled = false;
    }
    //getkarygol();
	document.getElementById('karyawaniduser').innerHTML="<option value='"+ karyawaniduser +"'>"+ namakary +"</option>";
    document.getElementById('karyawaniduserold').value = karyawaniduser;
	window.scroll({
		top: 0,
		left: 0,
		behavior: 'smooth'
	});
}

function simpan() {
	kodeorgold         = document.getElementById('kodeorgold').value;
	jenispersetujuanold= document.getElementById('jenispersetujuanold').value;
	levelold           = document.getElementById('levelold').value;
	karyawanidold      = document.getElementById('karyawanidold').value;
	departemenold      = document.getElementById('departemenold').value;
	jabatanold         = document.getElementById('jabatanold').value;
	tipekaryawanold    = document.getElementById('tipekaryawanold').value;
	golonganold        = document.getElementById('golonganold').value;
	tipeold            = document.getElementById('tipeold').value;

	kodeunit           = document.getElementById('kodeorg').value;
	jenispersetujuan   = document.getElementById('jenispersetujuan').value;
	level              = document.getElementById('level').value;
	nilaidari 		   = document.getElementById('nilaidari').value;
    nilaisampai 	   = document.getElementById('nilaisampai').value;
	karyawanid         = document.getElementById('karyawanid').value;
	departemen         = document.getElementById('departemen').value;
	tipekaryawan       = document.getElementById('tipekaryawan').value;
	golongan           = document.getElementById('golongan').value;
	jabatan            = document.getElementById('jabatan').value;
	tipe               = document.getElementById('tipe');
	method             = document.getElementById('method').value;
	karyawaniduser     = document.getElementById('karyawaniduser').value;
	karyawaniduserold  = document.getElementById('karyawaniduserold').value;

    if (tipe.checked == true) {
        tipe = 1;
    } else {
        tipe = 0;
    } 

    param = 'method=' + method + '&kodeunit=' + kodeunit + '&jenispersetujuan=' + jenispersetujuan + '&level=' + level + '&nilaidari=' + nilaidari + '&nilaisampai=' + nilaisampai  + '&karyawanid=' + karyawanid + '&departemen=' + departemen + '&tipekaryawan=' + tipekaryawan + '&golongan=' + golongan + '&tipe=' + tipe + '&jabatan=' + jabatan;

    param += "&kodeorgold=" + kodeorgold;
    param += "&jenispersetujuanold=" + jenispersetujuanold;
    param += "&levelold=" + levelold;
    param += "&karyawanidold=" + karyawanidold;
    param += "&departemenold=" + departemenold;
    param += "&jabatanold=" + jabatanold;
    param += "&tipekaryawanold=" + tipekaryawanold;
    param += "&golonganold=" + golonganold;
    param += "&tipeold=" + tipeold;
    
    param += "&karyawaniduser=" + karyawaniduser;
    param += "&karyawaniduserold=" + karyawaniduserold;

    tujuan = 'setup_slave_approval.php';

    if (kodeunit == '' || jenispersetujuan == '' || level == '') {
        alert('Warning : Kode organisasi, Jenis persetujua dan Level harus diisi.');
        return;
    }

    if (tipe == 0) {
        if (karyawanid == '') {
            alert('Warning : Karyawan harus diisi.');
            return;
        }
    }

    if (confirm('Are You Sure Save This Data?')) {
        post_response_text(tujuan, param, respog);
    }

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    alert("Success");
					document.getElementById('crkodeorg').value=kodeunit;
					document.getElementById('crjenispersetujuan').value=jenispersetujuan;
                    loaddata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpancopy() {
	kodeorgcopy1         = document.getElementById('kodeorgcopy1').value;
	jenispersetujuancopy = document.getElementById('jenispersetujuancopy').value;
	jenispersetujuancopy2= document.getElementById('jenispersetujuancopy2').value;
	kodeorgcopy2         = document.getElementById('kodeorgcopy2').value;
	departemencopy       = document.getElementById('departemencopy').value;
	departemencopy2      = document.getElementById('departemencopy2').value;
	golongancopy         = document.getElementById('golongancopy').value;
	golongancopy2        = document.getElementById('golongancopy2').value;

    param = 'method=simpancopy&kodeorgcopy1=' + kodeorgcopy1 + '&jenispersetujuancopy=' + jenispersetujuancopy + '&kodeorgcopy2=' + kodeorgcopy2 + '&jenispersetujuancopy2=' + jenispersetujuancopy2;
    param += '&departemencopy=' + departemencopy;
    param += '&departemencopy2=' + departemencopy2;
    param += '&golongancopy=' + golongancopy;
    param += '&golongancopy2=' + golongancopy2;
    tujuan = 'setup_slave_approval.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    alert("Berhasil copy approval.");
                    loaddata();
                    // document.getElementById('kodeorgcopy1').value='';
                    // document.getElementById('jenispersetujuancopy').value='';
                    // document.getElementById('jenispersetujuancopy2').value='';
                    // document.getElementById('kodeorgcopy2').value='';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function simpanreplace() {
    karyawanidrep1 = document.getElementById('karyawanidrep1').value;
    karyawanidrep2 = document.getElementById('karyawanidrep2').value;

    param = 'method=simpanreplace&karyawanidrep1=' + karyawanidrep1 + '&karyawanidrep2=' + karyawanidrep2;
    tujuan = 'setup_slave_approval.php';
    post_response_text(tujuan, param, respog);

    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loaddata();
                    document.getElementById('karyawanidrep1').value = '';
                    document.getElementById('karyawanidrep2').value = '';
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

// function simpanDep()
// {
// kodeorg=document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
// app=document.getElementById('app').value;
// met=document.getElementById('method').value;
// karyawanid=document.getElementById('karyawanid').options[document.getElementById('karyawanid').selectedIndex].value;

// param='kodeorg='+kodeorg+'&app='+app+'&method='+met+'&karyawanid='+karyawanid;
// tujuan='setup_slave_save_approval.php';
// post_response_text(tujuan, param, respog);

// function respog()
// {
// if(con.readyState==4)
// {
// if (con.status == 200) {
// busy_off();
// if (!isSaveResponse(con.responseText)) {
// alert(con.responseText);
// }
// else {
// //alert(con.responseText);
// document.getElementById('container').innerHTML=con.responseText;
// }
// }
// else {
// busy_off();
// error_catch(con.status);
// }
// }
// }

// }

// function dellField(kodeorg,app,karyawanid)
// {
// met='delete';
// param='kodeorg='+kodeorg+'&app='+app+'&method='+met+'&karyawanid='+karyawanid;
// // alert(param);
// tujuan='setup_slave_save_approval.php';
// post_response_text(tujuan, param, respog);
// function respog()
// {
// if(con.readyState==4)
// {
// if (con.status == 200) {
// busy_off();
// if (!isSaveResponse(con.responseText)) {
// alert(con.responseText);
// }
// else {
// //alert(con.responseText);
// document.getElementById('container').innerHTML=con.responseText;
// }
// }
// else {
// busy_off();
// error_catch(con.status);
// }
// }
// }
// }

function getkary() {
    kodeunit = document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
    jenispersetujuan = document.getElementById('jenispersetujuan').options[document.getElementById('jenispersetujuan').selectedIndex].value;
    departemen = document.getElementById('departemen').options[document.getElementById('departemen').selectedIndex].value;
    tipekaryawan = document.getElementById('tipekaryawan').options[document.getElementById('tipekaryawan').selectedIndex].value;
    golongan = document.getElementById('golongan').options[document.getElementById('golongan').selectedIndex].value;
    jabatan = document.getElementById('jabatan').options[document.getElementById('jabatan').selectedIndex].value;
    if (jenispersetujuan == 'PR') {
        document.getElementById('tipekaryawan').disabled = true;
        document.getElementById('jabatan').disabled = true;
        document.getElementById('tipe').disabled = true;
    } else {
        document.getElementById('tipekaryawan').disabled = false;
        document.getElementById('jabatan').disabled = false;
        document.getElementById('tipe').disabled = false;
    }

    param = 'jenispersetujuan=' + jenispersetujuan + '&kodeunit=' + kodeunit + '&method=getkary' + '&departemen=' + departemen + '&tipekaryawan=' + tipekaryawan + '&golongan=' + golongan + '&jabatan=' + jabatan;
    tujuan = 'setup_slave_approval.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //document.getElementById('karyawanid').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function getkarygol() {
    kodeunit = document.getElementById('kodeorg').options[document.getElementById('kodeorg').selectedIndex].value;
    golongan = document.getElementById('golongan').options[document.getElementById('golongan').selectedIndex].value;
    level = document.getElementById('level').options[document.getElementById('level').selectedIndex].value;
    karyawanid = document.getElementById('karyawanid').options[document.getElementById('karyawanid').selectedIndex].value;
    jenispersetujuan = document.getElementById('jenispersetujuan').options[document.getElementById('jenispersetujuan').selectedIndex].value;
    param = 'kodeunit=' + kodeunit + '&method=getkarygol' + '&golongan=' + golongan + '&jenispersetujuan=' + jenispersetujuan + '&level=' + level + '&karyawanid=' + karyawanid;
    tujuan = 'setup_slave_approval.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('karyawaniduser').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}

function tampilkanformdelete(ev) {
	width = 1024;
    height = 400;
    
    content = "<fieldset style=width:98%><div id=containerd style=\"height:385px;width:100%;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "Preview";
    showDialog4(title, content, width, height, ev);
	
	param = 'method=tampilkanformdelete';
    tujuan = 'setup_slave_approval.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					document.getElementById('containerd').innerHTML = con.responseText;
                    //alertify.popup("Delete",con.responseText).set({'resizable':true,'maximizable':true}).resizeTo('80%','70%');
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function formdelete(ev) {
    // width = 1024;
    // height = 400;
    
    // content = "<fieldset style=width:98%><div id=containerd style=\"height:385px;width:100%;overflow:auto;\"></div></fieldset>";
    // ev = 'event';
    // title = "Preview";
    // showDialog4(title, content, width, height, ev);
    

	kodeunit        = document.getElementById('cr2kodeorg').value;
	jenispersetujuan= document.getElementById('cr2jenispersetujuan').value;
	level           = document.getElementById('cr2level').value;
	karyawanid      = document.getElementById('cr2karyawanid').value;
	departemen      = document.getElementById('cr2departemen').value;
	tipekaryawan    = document.getElementById('cr2tipekaryawan').value;
	golongan        = document.getElementById('cr2golongan').value;
	jabatan         = document.getElementById('cr2jabatan').value;

    param = 'method=formdelete&kodeunit=' + kodeunit + '&jenispersetujuan=' + jenispersetujuan + '&level=' + level + '&karyawanid=' + karyawanid + '&departemen=' + departemen + '&tipekaryawan=' + tipekaryawan + '&golongan=' + golongan  + '&jabatan=' + jabatan;
    tujuan = 'setup_slave_approval.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById('contdelall').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deleteall(wherex) {
    //alert(wherex);
    param = 'wherex=' + wherex + '&method=deleteall';
    tujuan = 'setup_slave_approval.php';
    if (confirm('Are You Sure Delete ALL This Data?')) {
        post_response_text(tujuan, param, respog);
    }
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    closeDialog4();
                    batalcari2();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }

}