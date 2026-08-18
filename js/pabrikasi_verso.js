//JS 

function tambahBarang(title,ev){
    content= "<div id=formBarang style=\"max-height:250px;width:100%;overflow:auto;\"></div>";
    title='Find Material';
    height='';
    width='';
    showDialog1(title,content,width,height,ev);	
    getListBarang();
}

function getListBarang(){
    param='method=getListBarang';
    tujuan = 'pabrikasi_slave_sales.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                                alert(con.responseText);
                }
                else {
                    //alert(con.responseText);
                    document.getElementById('formBarang').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 	
}

function cariListBarang(){
    namaBarangCari=document.getElementById('namaBarangCari').value;
    param='method=getListBarang'+'&namaBarangCari='+namaBarangCari;
  
    tujuan = 'pabrikasi_slave_sales.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                    alert(con.responseText);
                }
                else {
                    //alert(con.responseText);
                    document.getElementById('formBarang').innerHTML=con.responseText;
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
		
}

function moveDataBarang(kodebarang,namabarang,satuanbarang,hargabarang){
    document.getElementById('kdbrgsch').value=kodebarang;
    document.getElementById('nmbrgsch').value=namabarang;
    document.getElementById('listCariBarang').style.display='none';
    closeDialog();
	
}


function getform(no,kdso,kdbrg){
	
	ev='event';
    content= "<div id=fromalasan style=\"height:250px;width:350;overflow:scroll;\">";
	content+="Untuk status cancel harus memberikan alasan";
	content+="<input type=text id=kdsoal value="+ kdso +"  class=myinputtext hidden>";
	content+="<input type=text id=kdbrgal value="+ kdbrg +"  class=myinputtext hidden>";
	content+="<br><br><textarea onkeypress=return tanpa_kutip(event) id=alasan style=width:250px; rows=8></textarea>";
	content+="<br><br><button class=mybutton onclick=savealasan()>Save</button>    <button class=mybutton onclick=cancelalasan()>Cancel</button>";
	content+="</div>";
    title='Isi Alasan';
    height='250';
    width='350';
    showDialog1(title,content,width,height,ev);	
   // getListBarang();
}

function cancelalasan(){
	closeDialog();
	loaddata();
}


function savealasan(){
	kdsoal=trim(document.getElementById('kdsoal').value);
	kdbrgal=trim(document.getElementById('kdbrgal').value);
	alasan=trim(document.getElementById('alasan').value);
	param = 'method=savealasan' + '&kdsoal=' + kdsoal + '&kdbrgal=' + kdbrgal + '&alasan=' + alasan;
	tujuan='pabrikasi_slave_verso.php';
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
						closeDialog();
						loaddata();
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
		  }	
	 }
}



function update(no){
	stat=trim(document.getElementById('stat'+no).value);
	kdso=trim(document.getElementById('kdso'+no).innerHTML);
	kdbrg=trim(document.getElementById('kdbrg'+no).innerHTML);
	param = 'method=update' + '&stat=' + stat + '&kdso=' + kdso + '&kdbrg=' + kdbrg;
	tujuan='pabrikasi_slave_verso.php';
	if(confirm('Are you sure ??')) {
		if(stat==2){
			getform(no,kdso,kdbrg);
		}else{
			 post_response_text(tujuan, param, respog);
		}
       
    }else{
		loaddata();
	}
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
						loaddata();
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
		  }	
	 }
}

function hapus(){
	document.getElementById('kdptsch').value='';
	document.getElementById('kdcussch').value='';
	document.getElementById('kdsosch').value='';
	document.getElementById('tglsch').value='';
	document.getElementById('kdbrgsch').value='';
	document.getElementById('nmbrgsch').value='';
	document.getElementById('noposch').value='';
	document.getElementById('statussch').value='';
	
	document.getElementById('salesidsch').value='';
	loaddata();
}



/**/

function loaddata(num){
	kdptsch=document.getElementById('kdptsch').value;
	kdcussch=document.getElementById('kdcussch').value;
	kdsosch=document.getElementById('kdsosch').value;
	tglsch=document.getElementById('tglsch').value;
	kdbrgsch=document.getElementById('kdbrgsch').value;
	noposch=document.getElementById('noposch').value;
	salesidsch=document.getElementById('salesidsch').value;
	statussch=document.getElementById('statussch').value;
    param = 'method=loaddata&page=' + num;
	param += '&kdptsch=' + kdptsch+'&kdcussch=' + kdcussch+'&kdsosch=' + kdsosch+'&tglsch=' + tglsch;
	param += '&kdbrgsch=' + kdbrgsch+'&noposch=' + noposch+'&salesidsch=' + salesidsch+'&statussch=' + statussch;
    tujuan = 'pabrikasi_slave_verso.php';
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
                    alert(con.responseText);
                }
                else {
                    isdt = con.responseText.split("####");
                    document.getElementById('contain').innerHTML = isdt[0];
                    document.getElementById('footData').innerHTML = isdt[1];
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

