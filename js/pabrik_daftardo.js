function simpan(){
    method=document.getElementById('method').value;
	
    //pabrik=document.getElementById('pabrik').value;
	tgl=document.getElementById('tgl').value;
	nodaftar=trim(document.getElementById('nodaftar').value);
	nokontrak=trim(document.getElementById('nokontrak').value);
	nodo=trim(document.getElementById('nodo').value);
	komoditi=document.getElementById('komoditi').value;
	volkontrak=document.getElementById('volkontrak').value;
	toleransi=document.getElementById('toleransi').value;
	ket=trim(document.getElementById('ket').value);
	cust=trim(document.getElementById('cust').value);
	
    if(tgl=='' || nokontrak=='' || komoditi=='' || cust==''){
        alert('Please complete the form');return;
    }
    param='cust='+cust+'&tgl='+tgl+'&nodaftar='+nodaftar+'&nokontrak='+nokontrak+'&nodo='+nodo;
    param+='&method='+method+'&komoditi='+komoditi+'&volkontrak='+volkontrak+'&ket='+ket+'&toleransi='+toleransi;
    tujuan='pabrik_slave_daftardo.php';
    post_response_text(tujuan, param, respog);		

    function respog() {
        if(con.readyState==4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                } else {
                    hapus();							
                    loadData();
                }
            } else {
                busy_off();
                error_catch(con.status);
            }
        }	
     }
}

function hapus(){
    document.getElementById('method').value='insert';
    document.getElementById('pabrik').value='';
	document.getElementById('tgl').value='';
    document.getElementById('nodo').value='';
    document.getElementById('nodaftar').value='';
    document.getElementById('nokontrak').value='';
    document.getElementById('volkontrak').value='0';
    document.getElementById('toleransi').value='0';
    document.getElementById('ket').value='';
	document.getElementById('cust').value='';
	document.getElementById('komoditi').value='';
}

function loadData(num) {
	tglsch=document.getElementById('tglsch').value;
	nodaftarsch=trim(document.getElementById('nodaftarsch').value);
	nokontraksch=trim(document.getElementById('nokontraksch').value);
	nodosch=trim(document.getElementById('nodosch').value);
	komoditisch=document.getElementById('komoditisch').value;
	
	//param='method=loadData';
	param='method=loadData'+'&nodaftarsch='+nodaftarsch+'&nokontraksch='+nokontraksch+'&nodosch='+nodosch+'&komoditisch='+komoditisch+'&tglsch='+tglsch;
	param+='&page='+num;
	tujuan='pabrik_slave_daftardo.php';
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
                                   // alert(con.responseText);
                                    document.getElementById('container').innerHTML=con.responseText;	
									//getperiodesort();
                                }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
              }	
	 }  
}

function fillField(nodaftar,tgl,nokontrak,nodo,komoditi,volkontrak,toleransi,ket,cust){
	//document.getElementById('pabrik').value=pabrik;
    document.getElementById('nodaftar').value=nodaftar;
	document.getElementById('tgl').value=tgl;
	document.getElementById('nokontrak').value=nokontrak;
	document.getElementById('nodo').value=nodo;
	document.getElementById('komoditi').value=komoditi;
    document.getElementById('volkontrak').value=volkontrak;
    document.getElementById('toleransi').value=toleransi;
    document.getElementById('ket').value=ket;  
	document.getElementById('cust').value=cust;  
    document.getElementById('method').value='update';	
}


function batalcari(){
	document.getElementById('nodaftarsch').value='';
	document.getElementById('nokontraksch').value='';
	document.getElementById('nodosch').value='';
	document.getElementById('komoditisch').value='';
	document.getElementById('tglsch').value='';
	loadData(0);
}


function del(nodaftar)
{
    param='method=delete'+'&nodaftar='+nodaftar;
    tujuan='pabrik_slave_daftardo.php';
    if(confirm("Delete data?")){
            post_response_text(tujuan, param, respog);	
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
                else 
                {
                    document.getElementById('container').innerHTML=con.responseText;
                    loadData();	
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }
	//alert("Data telah terhapus !!!");	
}

function getperiodesort()
{
	param='method=getperiodesort';	
	//alert(param);
	tujuan='pabrik_slave_daftardo.php';
        post_response_text(tujuan, param, respog);
	
	function respog()
	{
		  if(con.readyState==4)
		  {
				if (con.status == 200)
				{
					busy_off();
					if (!isSaveResponse(con.responseText)) 
					{
						alert(con.responseText);
					}
					else 
					{
						//alert(con.responseText);
						document.getElementById('periodesort').innerHTML=con.responseText;
					  	getsuppsort();
					}//
				}
				else 
				{
					busy_off();
					error_catch(con.status);
				}
		  }	
	} 	
}


function getsuppsort()
{
	param='method=getsuppsort';	
	//alert(param);
	tujuan='pabrik_slave_daftardo.php';
    post_response_text(tujuan, param, respog);
	
	function respog()
	{
		  if(con.readyState==4)
		  {
				if (con.status == 200)
				{
					busy_off();
					if (!isSaveResponse(con.responseText)) 
					{
						alert(con.responseText);
					}
					else 
					{
						//alert(con.responseText);
						document.getElementById('suppsort').innerHTML=con.responseText;
						getorgsort();
					}//
				}
				else 
				{
					busy_off();
					error_catch(con.status);
				}
		  }	
	} 	
}


function getorgsort()
{
	param='method=getorgsort';	
	//alert(param);
	tujuan='pabrik_slave_daftardo.php';
    post_response_text(tujuan, param, respog);
	
	function respog()
	{
		  if(con.readyState==4)
		  {
				if (con.status == 200)
				{
					busy_off();
					if (!isSaveResponse(con.responseText)) 
					{
						alert(con.responseText);
					}
					else 
					{
						//alert(con.responseText);
						document.getElementById('kdorgsort').innerHTML=con.responseText;
					}//
				}
				else 
				{
					busy_off();
					error_catch(con.status);
				}
		  }	
	} 	
}


function cari()
{
    brgSch=document.getElementById('brgSch').options[document.getElementById('brgSch').selectedIndex].value;
    tglSch=document.getElementById('tglSch').value;
    param='method=loadData'+'&tglSch='+tglSch+'&brgSch='+brgSch;
    //alert (param);
    tujuan='pabrik_slave_daftardo.php';
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
                        //alert(con.responseText);
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

function getSounding(){
    pbrik=document.getElementById('pabrik');
    pbrik=pbrik.options[pbrik.selectedIndex].value;
    tngki=document.getElementById('tangki');
    tngki=tngki.options[tngki.selectedIndex].value;
    tanggal=document.getElementById('tgl').value;
    param='method=getSounding'+'&tgl='+tanggal+'&pabrik='+pbrik;
    param+='&tangki='+tngki;
    //alert (param);
    tujuan='pabrik_slave_daftardo.php';
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
                        //alert(con.responseText);
                        document.getElementById('sawal').value=con.responseText;
                }
            }
            else {
                    busy_off();
                    error_catch(con.status);
            }
        }   
     } 
}
function itungSisa(){
    var hsl=0;
    sw=document.getElementById('sawal').value;
    if(sw!=0){
        rey=document.getElementById('jmlRey').value;
        if(parseFloat(sw)<parseFloat(rey)){
            alert("Jumlah Daur Ulang Lebih Besar Dari Saldo Awal");
            return;
        }
        hsl=parseFloat(sw)-parseFloat(rey);    
    }
    document.getElementById('jmlWaste').value=hsl;
}