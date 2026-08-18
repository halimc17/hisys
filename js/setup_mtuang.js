//JS 



function cancel()
{	
	document.getElementById('kodetambah').value='';
	document.getElementById('matauangtambah').value='';
	document.getElementById('simboltambah').value='';	
	document.getElementById('kodeisotambah').value='';
	document.getElementById('method').value='insert';
	document.location.reload();	
}







function loadData (kode) 
{    
    document.getElementById('kodedetail').value=kode;
    per=document.getElementById('per');
    param='method=loadData'+'&kode='+kode;
    if(per) 
    {
        param+='&per='+per.value;
    }
 // alert(param);
    
    tujuan='setup_slave_mtuang.php';
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
                               
                            }
                    }
                    else {
                            busy_off();
                            error_catch(con.status);
                    }
          }	
     }  
}




function gethitung(){
	kursjual=document.getElementById('kursjual').value;
	kursbeli=document.getElementById('kursbeli').value;
	rp=(parseFloat(kursjual)+parseFloat(kursbeli))/2;
	document.getElementById('kursdet').value=rp;
}

function getpersenpph(){
	kursjual=document.getElementById('kursjual').value;
	kursjual=kursjual.replace(/,/g, "");
	kursdet=document.getElementById('kursdet').value;
	kursdet=kursdet.replace(/,/g, "");
	persen=(parseFloat(kursdet)/parseFloat(kursjual))*100;

	document.getElementById('kursbeli').value=numberWithCommas(persen.toFixed(2));
}

function getrpppn(){
	kursjual=document.getElementById('nilaiinvoice').value;
	kursjual=kursjual.replace(/,/g, "");
	rpppn=parseFloat(kursjual)*0.1;
	document.getElementById('nilaippn').value=numberWithCommas(rpppn.toFixed(0));
}

function numberWithCommas(x) {
	return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}





function delhead(kode,matauang,simbol,kodeiso)
{
	if(confirm('Anda yakin untuk menghapus '+ kode +' ?'))
	param='method=delhead'+'&kode='+kode+'&matauang='+matauang+'&simbol='+simbol+'&kodeiso='+kodeiso;
	//alert(param);
	tujuan='setup_slave_mtuang.php';
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
					else 
					{
						cancel();
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
		  }	
	}

}

function deldetail(kode,daritanggal,jam)
{
	param='method=deldetail'+'&kode='+kode+'&daritanggal='+daritanggal+'&jam='+jam;
	//alert(param);
	tujuan='setup_slave_mtuang.php';
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
					else 
					{
                                            
                                           
						loadData(kode);
						//cariBast();
					}
				}
				else {
					busy_off();
					error_catch(con.status);
				}
		  }	
	}

}


function simpanbaru()
{
	kodetambah=document.getElementById('kodetambah').value;
	matauangtambah=document.getElementById('matauangtambah').value;
	simboltambah=document.getElementById('simboltambah').value;
	kodeisotambah=document.getElementById('kodeisotambah').value;
	method=document.getElementById('method').value;
	
	param='kodetambah='+kodetambah+'&matauangtambah='+matauangtambah+'&simboltambah='+simboltambah+'&kodeisotambah='+kodeisotambah+'&method='+method;
	tujuan='setup_slave_mtuang.php';
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
							cancel();							
                           // loadData();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }
}



function simpandetail(kode)
{
	kodedet=document.getElementById('kodedet').value;
	tgl=document.getElementById('tgl').value;
	jm=document.getElementById('jm').value;
	mn=document.getElementById('mn').value;
	kursjual=document.getElementById('kursjual').value;
	kursbeli=document.getElementById('kursbeli').value;
	kursdet=document.getElementById('kursdet').value;
	method=document.getElementById('method').value;
	param='method=simpandetail'+'&tgl='+tgl+'&jm='+jm+'&mn='+mn+'&kursjual='+kursjual+'&kursbeli='+kursbeli+'&kursdet='+kursdet+'&kodedet='+kodedet;
	//alert(param);
	tujuan='setup_slave_mtuang.php';
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
							//cariBast();
                                                        loadData(kode);

						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }
}



function edithead(kode)
{
	kodehead=kode;
	kodeheadedit=document.getElementById('kode'+kode).value;
	matauangheadedit=document.getElementById('matauang'+kode).value;
	simbolheadedit=document.getElementById('simbol'+kode).value;
	kodeisoheadedit=document.getElementById('kodeiso'+kode).value;
	methodheadedit=document.getElementById('method').value;
	
	if(confirm('Anda yakin untuk merubah data '+ kode +' ?'))
	
	
	param='method=edithead'+'&kodeheadedit='+kodeheadedit+'&matauangheadedit='+matauangheadedit+'&simbolheadedit='+simbolheadedit+'&kodeisoheadedit='+kodeisoheadedit+'&kodehead='+kodehead;
	//alert(param);
	tujuan='setup_slave_mtuang.php';
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
							cancel();							
                           // loadData();
						}
					}
					else {
						busy_off();
						error_catch(con.status);
					}
		      }	
	 }
}

function getFormUplaod(type){
    if(type==''){
        document.getElementById('uForm').style.display='none';
        document.getElementById('sample').innerHTML='';
        document.getElementById('jenisdata').value='';
        
    }
    else{
        document.getElementById('uForm').style.display='';
        document.getElementById('jenisdata').value=type;
    }
    
    if(type=='KURS')
        {  
           document.getElementById('sample').innerHTML='Format: kode,daritanggal,kursjual,kursbeli,kurs<br>Eg. EUR,20170401,16000,16500,16250<br>Note : kurs = (kursjual+kursbeli) / 2 <br><b>This form must be preceded by a header on the first line</b> <a href=tool_slave_getExample.php?form=KURSMATAUANG target=frame>Click here for example</a';
        }
    // if(type=='JOURNAL')
    //     {
    //        document.getElementById('sample').innerHTML='<b>Journal history form. This form must be preceded by a header on the first line</b> <a href=tool_slave_getExample.php?form=JOURNAL target=frame>Click here for example</a>'; 
    //     }
    // if(type=='INV')
    //     {
    //        document.getElementById('sample').innerHTML='<b>Inventory previous balance. This form must be preceded by a header on the first line</b> <a href=tool_slave_getExample.php?form=INV target=frame>Click here for example</a>'; 
    //     }         
    // if(type=='PO')
    //     {
    //        document.getElementById('sample').innerHTML='<b>PO outstanding. This form must be preceded by a header on the first line</b> <a href=tool_slave_getExample.php?form=PO target=frame>Click here for example</a>'; 
    //     }  
}

function submitFile(){
    if(confirm('Are you sure..?')){
    document.getElementById('frm').submit();
    }
}


