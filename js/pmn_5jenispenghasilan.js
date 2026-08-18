function loaddata(num){
	param='proses=loaddata';
	param+='&page='+num;
	tujuan = 'pmn_slave_5jenispenghasilan.php';
	post_response_text(tujuan, param, respog);
    //alert(param);			
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					document.getElementById('container').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
}



function fillField(kodepenghasilan,kodepajak,namapenghasilan,nourutparent) { //alert(kodepajak);
    document.getElementById('kodepenghasilan').value=kodepenghasilan;
    document.getElementById('kodepajak').value=kodepajak;
    document.getElementById('namapenghasilan').value=namapenghasilan;
    document.getElementById('nourut').value=nourutparent;
    document.getElementById('proses').value='update';   
     

}

function fillFielddt(kodepenghasilan,nourutchild,namapenghasilan) { //alert(kodepajak);
    document.getElementById('kodepenghasilandt').value=kodepenghasilan;
    document.getElementById('nourutdt').value=nourutchild;
    document.getElementById('namapenghasilandt').value=namapenghasilan;
    document.getElementById('prosesdt').value='updatedt';   
}


function tambahdata(idparent,kodepenghasilan){ 
	alert('Masukan Data ');
    //document.getElementById('kodepenghasilan').value=kodepenghasilan;
    //document.getElementById('nourut').value=nourut;
    document.getElementById('kodepenghasilan').value=kodepenghasilan;
    document.getElementById('idparent').value=kodepenghasilan;
    //document.getElementById('namapenghasilan').value=namapenghasilan;
    //document.getElementById('kodepajak').value=kodepajak;
    document.getElementById('proses').value='insertdt';
    document.getElementById('nourut').value='';
    document.getElementById('namapenghasilan').value='';
    document.getElementById('kodepajak').value='';   
     

}


function simpan()
{ 
    idpenghasilan=document.getElementById('kodepenghasilan').value;
    nourut=document.getElementById('nourut').value;
    namapenghasilan=document.getElementById('namapenghasilan').value;
    kodepajak=document.getElementById('kodepajak').value;
    idparent=document.getElementById('idparent').value;
    met=document.getElementById('proses').value;
   
	param='kodepenghasilan='+idpenghasilan+'&nourut='+nourut+'&namapenghasilan='+namapenghasilan+'&kodepajak='+kodepajak+'&idparent='+idparent+'&proses='+met;
	tujuan='pmn_slave_5jenispenghasilan.php';
	post_response_text(tujuan, param, respog);
        //alert(param);
    function respog()
    {
              if(con.readyState==4)
              {
                    if (con.status == 200) {
                        busy_off();
                        if (!isSaveResponse(con.responseText)) {
                            alert(con.responseText);
                        }
                        else {
							cancel();
                            loaddata();
                            // loaddata(con.responseText);
                        }
                    }
                    else {
                        busy_off();
                        error_catch(con.status);
                    }
              } 
     }
    //cancelGolongan(); 
}

function simpandt() { 
    kodepenghasilandt=document.getElementById('kodepenghasilandt').value;
    nourut=document.getElementById('nourutdt').value;
    namapenghasilan=document.getElementById('namapenghasilandt').value;
    kodepajak=document.getElementById('kodepajakdt').value;
    idparent=document.getElementById('idparentdt').value;
    met=document.getElementById('prosesdt').value;
	param='namapenghasilan='+namapenghasilan+'&kodepenghasilandt='+kodepenghasilandt+'&nourut='+nourut+'&kodepajak='+kodepajak+'&idparentdt='+idparent+'&proses='+met;
	tujuan='pmn_slave_5jenispenghasilan.php';
	post_response_text(tujuan, param, respog);
    function respog()
    {
		  if(con.readyState==4)
		  {
				if (con.status == 200) {
					busy_off();
					if (!isSaveResponse(con.responseText)) {
						alert(con.responseText);
					}
					else {
						loaddetail(idparent,kodepajak);
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
		  } 
     }
}

function cancel(){ 
    document.getElementById('kodepenghasilan').value='';
    document.getElementById('nourut').value='';
    document.getElementById('idparent').value='';
    document.getElementById('namapenghasilan').value='';
    document.getElementById('kodepajak').value='';
	    document.getElementById('proses').value='insert'; 
}

function canceldt(){ 
    document.getElementById('kodepenghasilandt').value='';
    document.getElementById('nourutdt').value='';
    document.getElementById('namapenghasilandt').value='';
	    document.getElementById('prosesdt').value='insertdt'; 
}


function detailpenghasilan(idpenghasilan,kodepajak,namapenghasilan){
//param(idpenghasilan);
    
    title="Detail Penghasilan : "+namapenghasilan;
    width='';
    height='';
    formListPP(title,width,height);
    param='idpenghasilan='+idpenghasilan;
    tujuan='pmn_slave_5jenispenghasilan.php';
    post_response_text(tujuan, param, respog);
    //alert(param);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }else {
                    loaddetail(idpenghasilan,kodepajak);
                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }  
}


function formListPP(title,wdth,heig){
    width='';
    height='';
    if(wdth!=''){
        width=wdth;
    }
    if(heig!=''){
        height=heig;
    }
    
    content="<div id=containerdetail></div>";
    ev='event';
    showDialog4(title,content,width,height,ev);
}

function loaddetail (idpenghasilan,kodepajak) 
{
    param='proses=loaddetail';
    param+='&idpenghasilan='+idpenghasilan;
	    param+='&kodepajak='+kodepajak;
		// alert(param);
    tujuan='pmn_slave_5jenispenghasilan.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                    document.getElementById('containerdetail').innerHTML=con.responseText;

                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }  
}

function tambah(idpenghasilan,idparent){

    
    title="Tambah Detail Penghasilan";
    width='';
    height='';
    formListPP1(title,width,height);
    param='idpenghasilan='+idpenghasilan;
    tujuan='pmn_slave_5jenispenghasilan.php';
    post_response_text(tujuan, param, respog);
    
    //alert(+idpenghasilan);
    function respog(){
        if(con.readyState==4){
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }else {
                    //document.getElementById('containerAkun').innerHTML=con.responseText;
                    document.getElementById('idparent').value=+idpenghasilan;
                    loadadd();

                }
            }else {
                busy_off();
                error_catch(con.status);
            }
        } 
    }  
}

function formListPP1(title,wdth,heig){
    width='';
    height='';
    if(wdth!=''){
        width=wdth;
    }
    if(heig!=''){
        height=heig;
    }
    
    content="<div id=container1></div>";
    ev='event';
    showDialog4(title,content,width,height,ev);
}


