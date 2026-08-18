
function cariBast(num){
    kdBrgSch=document.getElementById('kdBrgSch').options[document.getElementById('kdBrgSch').selectedIndex].value;
    tglSch=document.getElementById('tglSch').value;
    param='method=loadData'+'&tglSch='+tglSch+'&kdBrgSch='+kdBrgSch;
    param+='&page='+num;
    tujuan = 'vhc_slave_stokbarangbekas.php';
    post_response_text(tujuan, param, respog);			
    function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}else {
					document.getElementById('container').innerHTML=con.responseText;
				}
			}else {
				busy_off();
				error_catch(con.status);
			}
		}
    }	
}

function simpan(){
    kdOrg=trim(document.getElementById('kdOrg').value);
    kdBrg=trim(document.getElementById('kdBrg').value);
    tgl=trim(document.getElementById('tgl').value);
    keluar=trim(document.getElementById('keluar').value);
    ket=trim(document.getElementById('ket').value);
    tgljam=trim(document.getElementById('tgljam').value);
    method=document.getElementById('method').value;
        
    if(kdOrg=='' || tgl=='' || kdBrg=='' || keluar==0 || keluar =='' || ket == ''){
        alert('Please complete the form');return;
    }

    param='tgl='+tgl+'&kdOrg='+kdOrg+'&kdBrg='+kdBrg+'&keluar='+keluar;
    param+='&ket='+ket+'&tgljam='+tgljam;
    param+='&method='+method;
    tujuan='vhc_slave_stokbarangbekas.php';
    post_response_text(tujuan, param, respog);		

    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert('ERROR TRANSACTION,\n'+con.responseText);
                }else {
                    hapus();							
                    loadData();
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}

function hapus(){
    document.getElementById('method').value='insert';
    document.getElementById('tgl').value='';
    document.getElementById('tgl').disabled=false;
    document.getElementById('kdOrg').value='';
    document.getElementById('kdOrg').disabled=false;
    document.getElementById('kdBrg').value='';
    document.getElementById('kdBrg').disabled=false;
    document.getElementById('saldo').value='';
    document.getElementById('keluar').value='';
    document.getElementById('sisa').value='';
    document.getElementById('ket').value='';   
    document.getElementById('tgljam').value='';   
}

function loadData () {
	param='method=loadData';
	tujuan='vhc_slave_stokbarangbekas.php';
    post_response_text(tujuan, param, respog);
	function respog(){
		if(con.readyState==4){
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
				}else {
				   // alert(con.responseText);
					document.getElementById('container').innerHTML=con.responseText;	
				}
			}else {
					busy_off();
					error_catch(con.status);
			}
		}	
	}  
}

function fillField(kdOrg,tgl,kdBrg,keluar,ket,tgljam){   
    document.getElementById('kdOrg').value=kdOrg;
    document.getElementById('kdOrg').disabled=true;
    document.getElementById('tgl').value=tgl;
    document.getElementById('tgl').disabled=true;
    document.getElementById('kdBrg').value=kdBrg;
    document.getElementById('kdBrg').disabled=true;
    document.getElementById('keluar').value=keluar;
    document.getElementById('tgljam').value=tgljam;
    document.getElementById('ket').value=ket; 
    document.getElementById('method').value='update';
	getstok();
}

function batalcari(){
	document.getElementById('kdBrgSch').value='';
	document.getElementById('tglSch').value='';
	loadData();
}


function del(kdOrg,tgl,kdBrg,tgljam){
    param='method=delete'+'&kdOrg='+kdOrg+'&tgl='+tgl+'&kdBrg='+kdBrg+'&tgljam='+tgljam;
    tujuan='vhc_slave_stokbarangbekas.php';
    if(confirm("Delete data?")){
		post_response_text(tujuan, param, respog);	
    }
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }else {
                    document.getElementById('container').innerHTML=con.responseText;
                    loadData();	
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }
}

function cari(){
    kdBrgSch=document.getElementById('kdBrgSch').options[document.getElementById('kdBrgSch').selectedIndex].value;
    tglSch=document.getElementById('tglSch').value;
    param='method=loadData'+'&tglSch='+tglSch+'&kdBrgSch='+kdBrgSch;
    tujuan='vhc_slave_stokbarangbekas.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
                }else {
					//alert(con.responseText);
					document.getElementById('container').innerHTML=con.responseText;
                }
            }else {
                    busy_off();
                    error_catch(con.status);
            }
        }	
    } 
}

function getstok(){
	kdOrg=document.getElementById('kdOrg').options[document.getElementById('kdOrg').selectedIndex].value;
	kdBrg=document.getElementById('kdBrg').options[document.getElementById('kdBrg').selectedIndex].value;
    tgl=document.getElementById('tgl').value;
    param='method=getstok'+'&kdOrg='+kdOrg+'&kdBrg='+kdBrg+'&tgl='+tgl;
    tujuan='vhc_slave_stokbarangbekas.php';
    post_response_text(tujuan, param, respog);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }else {
                        //alert(con.responseText);
                        document.getElementById('saldo').value=con.responseText;
						getsisa();
                }
            }else {
                    busy_off();
                    error_catch(con.status);
            }
        }	
    } 
}

function getsisa(){
	saldo=document.getElementById('saldo').value;
	keluar=document.getElementById('keluar').value;
	saldo=saldo.replace(/,/g, "");
	keluar=keluar.replace(/,/g, "");
	sisa=parseFloat(saldo)-parseFloat(keluar);
	document.getElementById('sisa').value=numberWithCommas(sisa.toFixed(2));
	if(sisa<0){
		alert('Jumlah keluar tidak boleh lebih besar dari stok.');
	}
}

function numberWithCommas(x) {
	return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}