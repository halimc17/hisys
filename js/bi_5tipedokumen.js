function batal(){
	document.getElementById("tipe").value = '';
	document.getElementById("deskripsi").value = '';
	document.getElementById("tabel").selectedIndex = 0;
	getfield();
	document.getElementById("periode").value = '';
	document.getElementById("method").value = 'insert';
}

function simpan(){
	tipe = document.getElementById("tipe").value;
	deskripsi = document.getElementById("deskripsi").value;
	tabel=document.getElementById('tabel');
    tabel=tabel.options[tabel.selectedIndex].value;
	nodok=document.getElementById('nodok');
    nodok=nodok.options[nodok.selectedIndex].value;
	jnskgtn=document.getElementById('jnskgtn');
    jnskgtn=jnskgtn.options[jnskgtn.selectedIndex].value;
	kodeorg=document.getElementById('kodeorg');
    kodeorg=kodeorg.options[kodeorg.selectedIndex].value;
	periode=document.getElementById('periode');
    periode=periode.options[periode.selectedIndex].value;
	method = document.getElementById("method").value;
	
	param = "tipe="+tipe+"&deskripsi="+deskripsi+'&tabel='+tabel+'&nodok='+nodok+'&jnskgtn='+jnskgtn+'&kodeorg='+kodeorg+'&periode='+periode;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					alert("Success");
                    loaddata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('bi_slave_5tipedokumen.php?proses='+method, param, respon);
}

function loaddata(){
	param = "";
	
	function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    document.getElementById("container").innerHTML = con.responseText;
					batal();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('bi_slave_5tipedokumen.php?proses=loaddata', param, respon);
}

function fillfield(tipe,deskripsi,tabel,nodok,jnskgtn,kodeorg,periode){
	document.getElementById("tipe").value = tipe;
	document.getElementById("deskripsi").value = deskripsi;
	ltabel=document.getElementById('tabel');
    for(ard=0;ard<ltabel.length;ard++)
    {
        if(ltabel.options[ard].value==tabel)
            {
                ltabel.options[ard].selected=true;
            }
    }
	getfield(nodok,jnskgtn,kodeorg,periode);
	document.getElementById("method").value = 'update';
}

function deletefield(tipe){
	param = "tipe="+tipe+'&method=delete';
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					alert("Success");
                    loaddata();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
    
	if(confirm('Are you sure delete this item : '+tipe+'?'))
		post_response_text('bi_slave_5tipedokumen.php?proses=delete', param, respon);
}

function getfield(nodok,jnskgtn,kodeorg,periode){
	tabel=document.getElementById('tabel');
    tabel=tabel.options[tabel.selectedIndex].value;
	
	param = 'tabel='+tabel;
    
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
					document.getElementById('nodok').innerHTML = con.responseText;
					document.getElementById('jnskgtn').innerHTML = con.responseText;
					document.getElementById('kodeorg').innerHTML = con.responseText;
					document.getElementById('periode').innerHTML = con.responseText;
					getvaluefield(nodok,jnskgtn,kodeorg,periode);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
	
	post_response_text('bi_slave_5tipedokumen.php?proses=getfield', param, respon);
}

function getvaluefield(nodok,jnskgtn,kodeorg,periode){
	lnodok=document.getElementById('nodok');
    for(ard=0;ard<lnodok.length;ard++)
    {
        if(lnodok.options[ard].value==nodok)
            {
                lnodok.options[ard].selected=true;
            }
    }
	ljnskgtn=document.getElementById('jnskgtn');
    for(ard=0;ard<ljnskgtn.length;ard++)
    {
        if(ljnskgtn.options[ard].value==jnskgtn)
            {
                ljnskgtn.options[ard].selected=true;
            }
    }
	lkodeorg=document.getElementById('kodeorg');
    for(ard=0;ard<lkodeorg.length;ard++)
    {
        if(lkodeorg.options[ard].value==kodeorg)
            {
                lkodeorg.options[ard].selected=true;
            }
    }
	lperiode=document.getElementById('periode');
    for(ard=0;ard<lperiode.length;ard++)
    {
        if(lperiode.options[ard].value==periode)
            {
                lperiode.options[ard].selected=true;
            }
    }
}