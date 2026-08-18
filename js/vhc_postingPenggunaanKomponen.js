// JavaScript Document
function displayList()
{
        document.getElementById('txtsearch').value='';
        document.getElementById('tgl_cari').value='';
        document.getElementById('periodecari').value='';
        //document.getElementById('proses').value='insert';
        load_new_data();
}
function load_new_data(){
		txtSearch=document.getElementById('txtsearch').value;
		periodecari=document.getElementById('periodecari').value;
        txtTgl=document.getElementById('tgl_cari').value;
        param='proses=load_data&txtSearch='+txtSearch+'&txtTgl='+txtTgl+'&periodecari='+periodecari;
		tujuan='vhc_slave_postingPenggunaanKomponen.php';
        function respon(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alertify.alert(con.responseText);
                } else {
                    // Success Response
                                        //alertify.alert(con.responseText);
                                        document.getElementById('contain').innerHTML=con.responseText;
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }
    }
        post_response_text(tujuan, param, respon);
}
function cariBast(num)
{
                param='proses=load_data';
                param+='&page='+num;
                tujuan = 'vhc_slave_postingPenggunaanKomponen.php';
                post_response_text(tujuan, param, respog);			
                function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alertify.alert(con.responseText);
                                        }
                                        else {
                                                document.getElementById('contain').innerHTML=con.responseText;
                                        }
                                }
                                else {
                                        busy_off();
                                        error_catch(con.status);
                                }
                        }
                }	
}
function cariTransaksi()
{
        txtSearch=document.getElementById('txtsearch').value;
        txtTgl=document.getElementById('tgl_cari').value;

        param='txtSearch='+txtSearch+'&txtTgl='+txtTgl+'&proses=cari_transaksi';
        //alertify.alert(param);
        tujuan='vhc_slave_postingPenggunaanKomponen.php';
        post_response_text(tujuan, param, respog);			
        function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alertify.alert(con.responseText);
                                        }
                                        else {						
                                                //load_new_data();
                                                document.getElementById('contain').innerHTML=con.responseText;
                                        }
                                }
                                else {
                                        busy_off();
                                        error_catch(con.status);
                                }
                        }
                }	
}
function cariData(num)
{
                txtSearch=document.getElementById('txtsearch').value;
                txtTgl=document.getElementById('tgl_cari').value;		
                param='txtSearch='+txtSearch+'&txtTgl='+txtTgl+'&proses=cari_transaksi';
                param+='&page='+num;
                tujuan = 'vhc_slave_postingPenggunaanKomponen.php';
                post_response_text(tujuan, param, respog);			
                function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alertify.alert(con.responseText);
                                        }
                                        else {
                                                document.getElementById('contain').innerHTML=con.responseText;
                                        }
                                }
                                else {
                                        busy_off();
                                        error_catch(con.status);
                                }
                        }
                }	
}

function posting_data(notrans,kdvhc)
{
        no_trans=notrans;
        kdVhc=kdvhc;
        param='notrans='+no_trans+'&proses=postingData'+'&kdVhc='+kdVhc;
        tujuan='vhc_slave_postingPenggunaanKomponen.php';
        function respog(){
                        if (con.readyState == 4) {
                                if (con.status == 200) {
                                        busy_off();
                                        if (!isSaveResponse(con.responseText)) {
                                                alertify.alert(con.responseText);
                                        }else {						
            //                                     if(con.responseText=='external'){
												// 	form_ajukan(notrans,kdVhc);
												// }else{
													load_new_data();
												// }
												
                                        }
                                }else {
                                        busy_off();
                                        error_catch(con.status);
                                }
                        }
                }if(confirm("are you sure ?")){
                        post_response_text(tujuan, param, respog);			
                }else{ 
					return; 
				}
}

function form(){
    width = '720';
    height = '';
    content = "<fieldset><div id=containerd style=\"width:700px;max-height:700px;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "Detail HTML";
    showDialog5(title, content, width, height, ev); 
}


function html(notransaksi){
    form();
	param = 'proses=html'+'&trans_no='+notransaksi;
    tujuan = 'vhc_slave_service.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert(con.responseText);
                }else{
                    document.getElementById('containerd').innerHTML = con.responseText;
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}

function form_ajukan(notrans,kdVhc){
	width = '300';
    height = '';
    content = "<fieldset><legend>Submission Form</legend><div id=containeraju align=center style=\"width:100%;max-height:100px;overflow:auto;\"></div></fieldset>";
    ev = 'event';
    title = "";
    showDialog1(title, content, width, height, ev);
	
	param = 'proses=form_ajukan&notrans='+notrans+'&kdVhc='+kdVhc;
    tujuan = 'vhc_slave_postingPenggunaanKomponen.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if (con.readyState == 4)
        {
            if (con.status == 200)
            {
                busy_off();
                if (!isSaveResponse(con.responseText))
                {
                    alertify.alert(con.responseText);
                }
                else
                {
					document.getElementById("tutupdialogsatu").style.display="none";
                    document.getElementById('containeraju').innerHTML = con.responseText;
					
                }
            }
            else
            {
                busy_off();
                error_catch(con.status);
            }
        }
    }
}


function ajukan(){
	kepada=document.getElementById('kepada').value;
    notrans=document.getElementById('notran_aju').innerHTML;
	param = 'proses=ajukan' + '&notrans=' + notrans+ '&kepada=' + kepada;
    
	if(kepada==''){
		alertify.alert('Isikan nama penyetuju.');
		return;
	}
	tujuan = 'vhc_slave_postingPenggunaanKomponen.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if (con.readyState == 4){
            if (con.status == 200){
                busy_off();
                if (!isSaveResponse(con.responseText)){
                    alertify.alert(con.responseText);
                }else{
                 	alertify.alert('Sucses');
					closeDialog();
					load_new_data();
                }
            }else{
                busy_off();
                error_catch(con.status);
            }
        }
    }
}