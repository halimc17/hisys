//JS 

function tambahBarang(title,ev){
    content= "<div id=formBarang style=\"height:250px;width:350;overflow:scroll;\"></div>";
    title='Add Material';
    height='250';
    width='350';
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

function update(no){
	stat=trim(document.getElementById('stat'+no).value);
	kdso=trim(document.getElementById('kdso'+no).innerHTML);
	kdpab=trim(document.getElementById('kdpab'+no).innerHTML);
	param = 'method=update' + '&stat=' + stat + '&kdso=' + kdso + '&kdpab=' + kdpab;
	tujuan='pabrikasi_slave_verrab.php';
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
	document.getElementById('salesidsch').value='';
	loaddata();
}



/**/

function loaddata(num){
	kdpabsch=document.getElementById('kdpabsch').value;
	statussch=document.getElementById('statussch').value;
	kdsosch=document.getElementById('kdsosch').value;
	
    param = 'method=loaddata&page=' + num;
	param += '&kdpabsch=' + kdpabsch+'&statussch=' + statussch+'&kdsosch=' + kdsosch;
    tujuan = 'pabrikasi_slave_verrab.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if (con.readyState == 4)
        {
            if (con.status == 200)
            {
                busy_off();
                if (!isSaveResponse(con.responseText)){
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

