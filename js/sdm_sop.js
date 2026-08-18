function loadfiles(notransaksi) {
    param = 'method=loadfiles&notransaksi=' + document.getElementById('notransaksi').value;
    //alert(param)
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    if (document.getElementById('listfiles') !== null) {
                        document.getElementById('listfiles').innerHTML = con.responseText;
                    }
                    if (document.getElementById('loadfilesdetail') !== null) {
                        document.getElementById('loadfilesdetail').innerHTML = con.responseText;
                    }
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletefile(notransaksi, namafile) {
    param = "method=deletefile";
    param += "&notransaksi=" + notransaksi;
    param += "&namafile=" + namafile;
    tujuan = 'sdm_slave_sop.php';
    alert(param);
    post_response_text(tujuan, param, respog);
    function respog() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    loadfiles(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function submitfile() {
    var file = document.getElementById("upload").files[0];
    var notransaksi = document.getElementById("notransaksi").value;
    var formdata = new FormData();
    formdata.append("notransaksi", notransaksi);
    formdata.append("file", file);
    formdata.append("fileupload", getValue('upload'));
    if (getValue('upload') == "") {
        alert("warning : Upload file has been empty.");
        return false;
    }
    var con = createXMLHttpRequest();
    con.open("POST", "sdm_slave_sop.php?method=submitfile", true);
    busy_on();
    con.onreadystatechange = eval(respon);
    con.send(formdata);
    function respon() {
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                } else {
                    //=== Success Response
                    //alert(notransaksi);
                    //alert(con.responseText);
                    alert('Uploaded Success.');
                    document.getElementById("upload").value = "";
                    loadfiles(notransaksi);
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function displaylist(){
    document.getElementById('listdata').style.display = 'block';
    document.getElementById('header').style.display = 'none';
	document.getElementById('detail').style.display='none';
    loaddata(0);
}

function getPage(){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loaddata(paged);	
}


function loaddata(num){
	// thnsch = document.getElementById('thnsch');
    // thnsch = thnsch.options[thnsch.selectedIndex].value;
	
    param = 'method=loaddata&page=' + num;
	// if (thnsch != '') 
	// {
        // param += '&thnsch=' + thnsch;
    // }
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    isdt = con.responseText.split("####");
                    document.getElementById('contain').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
                    
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}



function edithead(notransaksi,norevisi,tanggalefektif,disiapkan,diperiksa,disahkan,departemen,jabatan,tanggaldisiapkan,tanggaldiperiksa,tanggaldisahkan){
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('header').style.display = 'block';
	document.getElementById('detail').style.display='block';
	
	document.getElementById('notransaksi').value=notransaksi;
    document.getElementById('norevisi').value=norevisi;
    document.getElementById('tanggalefektif').value=tanggalefektif;	
	
	document.getElementById('disiapkan').value=disiapkan;
    document.getElementById('diperiksa').value=diperiksa;
    document.getElementById('disahkan').value=disahkan;	
	
	document.getElementById('tanggaldisiapkan').value=tanggaldisiapkan;
    document.getElementById('tanggaldiperiksa').value=tanggaldiperiksa;
    document.getElementById('tanggaldisahkan').value=tanggaldisahkan;
    document.getElementById('departemen').value=departemen;
    document.getElementById('jabatan').value=jabatan;
    document.getElementById('method').value='updatehead';
	
	document.getElementById('notransaksi').disabled=true;
	document.getElementById('norevisi').disabled=true;
	
	loadtujuan();
	// savehead();
}


function cancelhead(){
	document.getElementById('listdata').style.display = 'none';
	document.getElementById('detail').style.display='none';
	
	document.getElementById('notransaksi').value='';
    document.getElementById('norevisi').value='';
    document.getElementById('tanggalefektif').value='';	
	
	document.getElementById('disiapkan').value='';
    document.getElementById('diperiksa').value='';
    document.getElementById('disahkan').value='';	
	
	document.getElementById('tanggaldisiapkan').value='';
    document.getElementById('tanggaldiperiksa').value='';
    document.getElementById('tanggaldisahkan').value='';
    document.getElementById('departemen').value='';
    document.getElementById('jabatan').value='';

	document.getElementById('notransaksi').disabled=false;
	document.getElementById('norevisi').disabled=false;
	
	document.getElementById('method').value='savehead';
	
}





function savehead(notransaksi){
    notransaksi=document.getElementById('notransaksi').value;
    norevisi=document.getElementById('norevisi').value;
    tanggalefektif=document.getElementById('tanggalefektif').value;
	
	disiapkan=document.getElementById('disiapkan').value;
	tanggaldisiapkan=document.getElementById('tanggaldisiapkan').value;
	
	diperiksa=document.getElementById('diperiksa').value;
	tanggaldiperiksa=document.getElementById('tanggaldiperiksa').value;
	
	disahkan=document.getElementById('disahkan').value;
    tanggaldisahkan=document.getElementById('tanggaldisahkan').value;
    departemen=document.getElementById('departemen').value;
	jabatan=document.getElementById('jabatan').value;
	
	method=document.getElementById('method').value;
	
    param ='notransaksi=' + notransaksi + '&norevisi=' + norevisi + '&tanggalefektif=' + tanggalefektif;
    param+= '&disiapkan=' + disiapkan + '&tanggaldisiapkan=' + tanggaldisiapkan;
    param+= '&diperiksa=' + diperiksa + '&tanggaldiperiksa=' + tanggaldiperiksa;
    param+= '&disahkan=' + disahkan + '&tanggaldisahkan=' + tanggaldisahkan;
    param+= '&departemen=' + departemen + '&jabatan=' + jabatan;
	param+='&method='+method;
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    document.getElementById('detail').style.display='block';
					loadtujuan();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletehead(notransaksi,norevisi){
    param = 'method=deletehead' + '&notransaksi=' + notransaksi + '&norevisi=' + norevisi;
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
                    loaddata(0);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function posting(notransaksi){
    param = 'method=posting' + '&notransaksi=' + notransaksi;
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					loaddata(0);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}



function newdata(){
    document.getElementById('header').style.display = 'block';
    document.getElementById('listdata').style.display = 'none';
    document.getElementById('detail').style.display='none';
	cancelhead();
}

/***********************************************************************************************************************************************/
/************* TUJUAN **************************************************************************************************************************/
/***********************************************************************************************************************************************/



function savetujuan(){
    notransaksi=document.getElementById('notransaksi').value;
    norevisi=document.getElementById('norevisi').value;
	keterangantujuan=document.getElementById('keterangantujuan').value;
    param = 'method=savetujuan' + '&notransaksi=' + notransaksi + '&norevisi=' + norevisi + '&keterangantujuan=' + keterangantujuan;
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					document.getElementById('keterangantujuan').value='';
					loadtujuan();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadtujuan(){
    notransaksi=document.getElementById('notransaksi').value;
    norevisi=document.getElementById('norevisi').value;
    param = 'method=loadtujuan' + '&notransaksi=' + notransaksi + '&norevisi=' + norevisi;
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					document.getElementById('listtujuan').innerHTML=con.responseText;
					loadruanglingkup();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deletetujuan(notransaksi,norevisi){
    notransaksi=document.getElementById('notransaksi').value;
    norevisi=document.getElementById('norevisi').value;
    param = 'method=deletetujuan' + '&notransaksi=' + notransaksi + '&norevisi=' + norevisi;
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					loadtujuan();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function edittujuan(notransaksi,norevisi,keterangantujuan){
	document.getElementById('keterangantujuan').value=keterangantujuan;
}



/***********************************************************************************************************************************************/
/********* RUANG LINGKUP ***********************************************************************************************************************/
/***********************************************************************************************************************************************/


function saveruanglingkup(){
    notransaksi=document.getElementById('notransaksi').value;
    norevisi=document.getElementById('norevisi').value;
	keteranganruanglingkup=document.getElementById('keteranganruanglingkup').value;
    param = 'method=saveruanglingkup' + '&notransaksi=' + notransaksi + '&norevisi=' + norevisi + '&keteranganruanglingkup=' + keteranganruanglingkup;
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					document.getElementById('keteranganruanglingkup').value='';
					loadruanglingkup();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadruanglingkup(){
    notransaksi=document.getElementById('notransaksi').value;
    norevisi=document.getElementById('norevisi').value;
    param = 'method=loadruanglingkup' + '&notransaksi=' + notransaksi + '&norevisi=' + norevisi;
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					document.getElementById('listruanglingkup').innerHTML=con.responseText;
					loadtanggungjawab();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function deleteruanglingkup(notransaksi,norevisi){
    param = 'method=deleteruanglingkup' + '&notransaksi=' + notransaksi + '&norevisi=' + norevisi;
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					loadruanglingkup();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function editruanglingkup(notransaksi,norevisi,keteranganruanglingkup){
	document.getElementById('keteranganruanglingkup').value=keteranganruanglingkup;
}



/***********************************************************************************************************************************************/
/****^**** tanggung jawab **********************************************************************************************************************/
/***********************************************************************************************************************************************/


function savetanggungjawab(){
    notransaksi=document.getElementById('notransaksi').value;
    norevisi=document.getElementById('norevisi').value;
    nouruttanggungjawab=document.getElementById('nouruttanggungjawab').value;
    keterangantanggungjawab=document.getElementById('keterangantanggungjawab').value;
    param = 'method=savetanggungjawab' + '&notransaksi=' + notransaksi + '&norevisi=' + norevisi;
	param+='&nouruttanggungjawab=' + nouruttanggungjawab + '&keterangantanggungjawab=' + keterangantanggungjawab;
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					document.getElementById('keterangantanggungjawab').value='';
					document.getElementById('nouruttanggungjawab').value='';
					document.getElementById('nouruttanggungjawab').disabled=false;
					loadtanggungjawab();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadtanggungjawab(){
    notransaksi=document.getElementById('notransaksi').value;
    norevisi=document.getElementById('norevisi').value;
    param = 'method=loadtanggungjawab' + '&notransaksi=' + notransaksi + '&norevisi=' + norevisi;
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
				
					document.getElementById('listtanggungjawab').innerHTML=con.responseText;
					loadreferensi();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function deletetanggungjawab(notransaksi,norevisi,nouruttanggungjawab){
    param = 'method=deletetanggungjawab' + '&notransaksi=' + notransaksi + '&norevisi=' + norevisi+ '&nouruttanggungjawab=' + nouruttanggungjawab;
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					loadtanggungjawab();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function edittanggungjawab(notransaksi,norevisi,nouruttanggungjawab,keterangantujuan){
	document.getElementById('nouruttanggungjawab').value=nouruttanggungjawab;
	document.getElementById('nouruttanggungjawab').disabled=true;
	document.getElementById('keterangantanggungjawab').value=keterangantujuan;
}




/*******************************************************************************************************************************************/
/*********** referensi *********************************************************************************************************************/
/*******************************************************************************************************************************************/



function savereferensi(){
    notransaksi=document.getElementById('notransaksi').value;
    norevisi=document.getElementById('norevisi').value;
    nourutreferensi=document.getElementById('nourutreferensi').value;
    keteranganreferensi=document.getElementById('keteranganreferensi').value;
    param = 'method=savereferensi' + '&notransaksi=' + notransaksi + '&norevisi=' + norevisi;
	param+='&nourutreferensi=' + nourutreferensi + '&keteranganreferensi=' + keteranganreferensi;
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					document.getElementById('keteranganreferensi').value='';
					document.getElementById('nourutreferensi').value='';
					document.getElementById('nourutreferensi').disabled=false;
					loadreferensi();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadreferensi(){
	
    notransaksi=document.getElementById('notransaksi').value;
    norevisi=document.getElementById('norevisi').value;
    param = 'method=loadreferensi' + '&notransaksi=' + notransaksi + '&norevisi=' + norevisi;
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
				
					document.getElementById('listreferensi').innerHTML=con.responseText;
					loaddefinisi();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function deletereferensi(notransaksi,norevisi,nourutreferensi){
    param = 'method=deletereferensi' + '&notransaksi=' + notransaksi + '&norevisi=' + norevisi+ '&nourutreferensi=' + nourutreferensi;
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					loadreferensi();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function editreferensi(notransaksi,norevisi,nourutreferensi,keterangantujuan){
	document.getElementById('nourutreferensi').value=nourutreferensi;
	document.getElementById('nourutreferensi').disabled=true;
	document.getElementById('keteranganreferensi').value=keterangantujuan;
}




/*******************************************************************************************************************************************/
/************ definisi *********************************************************************************************************************/
/*******************************************************************************************************************************************/


function savedefinisi(){
    notransaksi=document.getElementById('notransaksi').value;
    norevisi=document.getElementById('norevisi').value;
    nourutdefinisi=document.getElementById('nourutdefinisi').value;
    keterangandefinisi=document.getElementById('keterangandefinisi').value;
    param = 'method=savedefinisi' + '&notransaksi=' + notransaksi + '&norevisi=' + norevisi;
	param+='&nourutdefinisi=' + nourutdefinisi + '&keterangandefinisi=' + keterangandefinisi;
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					document.getElementById('keterangandefinisi').value='';
					document.getElementById('nourutdefinisi').value='';
					document.getElementById('nourutdefinisi').disabled=false;
					loaddefinisi();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loaddefinisi(){
	
    notransaksi=document.getElementById('notransaksi').value;
    norevisi=document.getElementById('norevisi').value;
    param = 'method=loaddefinisi' + '&notransaksi=' + notransaksi + '&norevisi=' + norevisi;
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
				
					document.getElementById('listdefinisi').innerHTML=con.responseText;
					loadketentuanumum();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function deletedefinisi(notransaksi,norevisi,nourutdefinisi){
    param = 'method=deletedefinisi' + '&notransaksi=' + notransaksi + '&norevisi=' + norevisi+ '&nourutdefinisi=' + nourutdefinisi;
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					loaddefinisi();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function editdefinisi(notransaksi,norevisi,nourutdefinisi,keteranganruanglingkup){
	document.getElementById('nourutdefinisi').value=nourutdefinisi;
	document.getElementById('nourutdefinisi').disabled=true;
	document.getElementById('keterangandefinisi').value=keteranganruanglingkup;
}




/*******************************************************************************************************************************************/
/************ ketentuanumum ****************************************************************************************************************/
/*******************************************************************************************************************************************/


function saveketentuanumum(){
    notransaksi=document.getElementById('notransaksi').value;
    norevisi=document.getElementById('norevisi').value;
    nourutketentuanumum=document.getElementById('nourutketentuanumum').value;
    keteranganketentuanumum=document.getElementById('keteranganketentuanumum').value;
    param = 'method=saveketentuanumum' + '&notransaksi=' + notransaksi + '&norevisi=' + norevisi;
	param+='&nourutketentuanumum=' + nourutketentuanumum + '&keteranganketentuanumum=' + keteranganketentuanumum;
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					document.getElementById('keteranganketentuanumum').value='';
					document.getElementById('nourutketentuanumum').value='';
					document.getElementById('nourutketentuanumum').disabled=false;
					loadketentuanumum();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadketentuanumum(){
    notransaksi=document.getElementById('notransaksi').value;
    norevisi=document.getElementById('norevisi').value;
    param = 'method=loadketentuanumum' + '&notransaksi=' + notransaksi + '&norevisi=' + norevisi;
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
				
					document.getElementById('listketentuanumum').innerHTML=con.responseText;
					loadprosedur();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function deleteketentuanumum(notransaksi,norevisi,nourutketentuanumum){
    param = 'method=deleteketentuanumum' + '&notransaksi=' + notransaksi + '&norevisi=' + norevisi+ '&nourutketentuanumum=' + nourutketentuanumum;
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					loadketentuanumum();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function editketentuanumum(notransaksi,norevisi,nourutketentuanumum,keterangantujuan){
	document.getElementById('nourutketentuanumum').value=nourutketentuanumum;
	document.getElementById('nourutketentuanumum').disabled=true;
	document.getElementById('keteranganketentuanumum').value=keterangantujuan;
}




/*******************************************************************************************************************************************/
/************ prosedur *********************************************************************************************************************/
/*******************************************************************************************************************************************/


function adduserprosedur(){
	useridprosedur = document.getElementById('useridprosedur').value;
	// usernamaprosedur = document.getElementById('usernamaprosedur').value;
	param='method=adduserprosedur&useridprosedur='+useridprosedur;
	// param='method=adduserprosedur&useridprosedur='+useridprosedur+'&usernamaprosedur='+usernamaprosedur;
	tujuan='sdm_slave_sop.php';
	newRow = document.createElement("tr");
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					vsplit = con.responseText.split("####");
					tabBody = document.getElementById('newmaster');
					tabBody.appendChild(newRow);
					newRow.setAttribute("id","tr_"+vsplit[0]+"_"+vsplit[1]);
					newRow.setAttribute("class","rowcontent");
					newRow.innerHTML += "<td hidden style='text-align:center;'>"+vsplit[0]+"</td>";
					newRow.innerHTML += "<td style='text-align:center;'>"+vsplit[1]+"</td>";
					newRow.innerHTML += "<td style='text-align:center'><img title='Hapus' class=resicon onclick=\"deleteuserprosedur(this,'"+con.responseText+"')\" src='images/delete_32.png'/></td>";
					document.getElementById('useridprosedur').value = '';
					
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	post_response_text(tujuan, param, respog);
}




function deleteuserprosedur(btn,arrVal){
	vsplit = arrVal.split("####");
	param='proses=deleteuserprosedur&useridprosedur='+vsplit[0];
	tujuan='sdm_slave_sop.php';
	
	function respog(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					var row = btn.parentNode.parentNode;
					row.parentNode.removeChild(row);
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	post_response_text(tujuan, param, respog);
}


function saveprosedur(){ 
	notransaksi=document.getElementById('notransaksi').value;
    norevisi=document.getElementById('norevisi').value;
	nourutprosedur = document.getElementById('nourutprosedur').value;
	keteranganprosedur =document.getElementById('keteranganprosedur').value;
	bataswaktuprosedur =document.getElementById('bataswaktuprosedur').value;
	param = 'method=saveprosedur' + '&notransaksi=' + notransaksi + '&norevisi=' + norevisi;
	param+='&nourutprosedur=' + nourutprosedur + '&keteranganprosedur=' + keteranganprosedur+ '&bataswaktuprosedur=' + bataswaktuprosedur;
    tujuan = 'sdm_slave_sop.php';
	function respon(){
		if(con.readyState==4){
			if (con.status == 200){
				busy_off();
				if (!isSaveResponse(con.responseText)){
					alert(con.responseText);
				}else{
					// alert("Input data berhasil.");
					loadprosedur();
				}
			}else{
				busy_off();
				error_catch(con.status);
			}
		}	
	}
	post_response_text(tujuan, param, respon);
}



function loadprosedur(){
    notransaksi=document.getElementById('notransaksi').value;
    norevisi=document.getElementById('norevisi').value;
    param = 'method=loadprosedur' + '&notransaksi=' + notransaksi + '&norevisi=' + norevisi;
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					document.getElementById('listprosedur').innerHTML=con.responseText;
					cancelprosedur();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}



function deleteprosedur(notransaksi,norevisi,nourutprosedur){
    param = 'method=deleteprosedur' + '&notransaksi=' + notransaksi + '&norevisi=' + norevisi+ '&nourutprosedur=' + nourutprosedur;
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					loadprosedur();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function cancelprosedur(){
	document.getElementById('nourutprosedur').value='';
	document.getElementById('keteranganprosedur').value='';
	document.getElementById('bataswaktuprosedur').value='';
    param = 'method=cancelprosedur';
    tujuan = 'sdm_slave_sop.php';
    post_response_text(tujuan, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					document.getElementById('newmaster').innerHTML = '';
					loadlampiran();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}




/*******************************************************************************************************************************************/
/************ lampiran *********************************************************************************************************************/
/*******************************************************************************************************************************************/


function savelampiran(){
    notransaksi=document.getElementById('notransaksi').value;
    norevisi=document.getElementById('norevisi').value;
    nourutlampiran=document.getElementById('nourutlampiran').value;
    keteranganlampiran=document.getElementById('keteranganlampiran').value;
    param = 'method=savelampiran' + '&notransaksi=' + notransaksi + '&norevisi=' + norevisi;
	param+='&nourutlampiran=' + nourutlampiran + '&keteranganlampiran=' + keteranganlampiran;
    ruanglingkup = 'sdm_slave_sop.php';
    post_response_text(ruanglingkup, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					document.getElementById('keteranganlampiran').value='';
					document.getElementById('nourutlampiran').value='';
					document.getElementById('nourutlampiran').disabled=false;
					loadlampiran();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function loadlampiran(){
    notransaksi=document.getElementById('notransaksi').value;
    norevisi=document.getElementById('norevisi').value;
    param = 'method=loadlampiran' + '&notransaksi=' + notransaksi + '&norevisi=' + norevisi;
    ruanglingkup = 'sdm_slave_sop.php';
    post_response_text(ruanglingkup, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
				
					document.getElementById('listlampiran').innerHTML=con.responseText;
					loadperubahan();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function deletelampiran(notransaksi,norevisi,nourutlampiran){
    notransaksi=document.getElementById('notransaksi').value;
    norevisi=document.getElementById('norevisi').value;
    param = 'method=deletelampiran' + '&notransaksi=' + notransaksi + '&norevisi=' + norevisi+ '&nourutlampiran=' + nourutlampiran;
    ruanglingkup = 'sdm_slave_sop.php';
    post_response_text(ruanglingkup, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					loadlampiran();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function editlampiran(notransaksi,norevisi,nourutlampiran,keteranganruanglingkup){
	document.getElementById('nourutlampiran').value=nourutlampiran;
	document.getElementById('nourutlampiran').disabled=true;
	document.getElementById('keteranganlampiran').value=keteranganruanglingkup;
}



/*******************************************************************************************************************************************/
/************ lampiran *********************************************************************************************************************/
/*******************************************************************************************************************************************/


function loadperubahan(){
    notransaksi=document.getElementById('notransaksi').value;
    param = 'method=loadperubahan' + '&notransaksi=' + notransaksi;
    ruanglingkup = 'sdm_slave_sop.php';
    post_response_text(ruanglingkup, param, respon);
    function respon(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alert(con.responseText);
                }else{
					document.getElementById('listperubahan').innerHTML=con.responseText;
                    loadfiles(0);
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}












