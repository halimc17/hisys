function preview(tipeprint, ev) {
    tujuan = 'keu_slave_2alokasigaji.php';
    pt = getValue('pt');
    unit = getValue('unit');
    periode = getValue('periode');
    jenis = getValue('jenis');

    if(pt == '' || periode == '') {
		if(jenis=='detail'){			
			alertify.alert("Informasi",'Mohon Pilih Perusahaan & Periode !');
			return;
		}
    }

    param = 'method=preview&tipeprint='+tipeprint+'&unit='+unit+'&pt='+pt+'&periode='+periode+'&jenis='+jenis;

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi",con.responseText);
                } else {
                    if (tipeprint == 'html') {
                        //document.getElementById('formlist').style.display = "block";
                        document.getElementById('listdata').innerHTML = con.responseText;
                        leftFixedTable();
                    } else if (tipeprint == 'excel') {
                        tujuan=tujuan+"?"+param;  
                        printnopopup(tujuan);
                    } else if (tipeprint == 'pdf') {
                        title = 'Report PDF';
                        tujuan = tujuan + "?" + param;
                        // width = 1024;
                        // height = 400;
                        // content = "<iframe frameborder=0 width=100% height=100% src="+tujuan+"></iframe>";
                        // showDialog4(title, content, width, height, ev);
                        alertify.popuppdf(title,"<iframe frameborder=0 style='width:100%;height:90%;overflow:none' src='" + tujuan + "'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('80%','70%');
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}

function printnopopup(url) {
    // alert(url);
    var ifrm = document.createElement("iframe");
    ifrm.setAttribute("src", url);
    ifrm.style.display = 'none';
    document.body.appendChild(ifrm);
}

function loadunit() {
    let pt = document.getElementById('pt').value;
    tujuan = 'keu_slave_2alokasigaji.php';
    param = 'pt='+pt+'&method=loadunit';

    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert("Informasi",con.responseText);
                } else {
                    document.getElementById('unit').innerHTML = con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    post_response_text(tujuan, param, respon);
}

function jmlhgaji(karyawanid, periode, namakaryawan) {
    param = 'karyawanid='+karyawanid+'&periode='+periode+'&method=jmlhgaji';
    tujuan = 'keu_slave_2alokasigaji.php';
    title = 'JUMLAH GAJI ' + namakaryawan;
    tujuan = tujuan + "?" + param;
    // width = 500;
    // height = 300;
    // content = "<iframe frameborder=0 width=100% height=100% src="+tujuan+" style=background:#E8F4F4></iframe>";
    // showDialog2(title, content, width, height, 'event');
    alertify.popup(title,"<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='"+tujuan+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('50%','70%');
}

function alokasi(karyawanid, periode, namakaryawan) {
    param = 'karyawanid='+karyawanid+'&periode='+periode+'&method=alokasi';
    tujuan = 'keu_slave_2alokasigaji.php';
    title = 'ALOKASI GAJI ' + namakaryawan;
    tujuan = tujuan + "?" + param;
    // width = 500;
    // height = 300;
    // content = "<iframe frameborder=0 width=100% height=100% src="+tujuan+" style=background:#E8F4F4></iframe>";
    // showDialog5(title, content, width, height, 'event');
    alertify.popup(title,"<iframe frameborder=0 style='width:100%;height:100%;overflow:none' src='"+tujuan+"'></iframe>").set({'resizable':true, 'overflow':false}).resizeTo('50%','70%');
}
