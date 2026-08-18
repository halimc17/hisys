/**
 * @author repindra.ginting
 */

function fillField(kode,kelompok,kelompok1,kelompokbiaya,noakun,noakungit)
{
  kelnumber	=document.getElementById('kelnumber');
  kelnumber.value	=kode;
  kelname	=document.getElementById('kelname');
  kelname.value	=kelompok;
  kelname1	=document.getElementById('kelname1');
  kelname1.value	=kelompok1;        
  document.getElementById('status').value='1';     
  kelbiaya	=document.getElementById('kelompokbiaya');
  kelnumber.disabled=true;
  cat=0;
  for(x=0;x<kelbiaya.length;x++)
  {
          //alert(kelbiaya.options[x].value+"-"+kelompokbiaya);
          if(kelbiaya.options[x].value==kelompokbiaya)
          {
                  cat=x;
          }
  }
  kelbiaya.options[cat].selected=true;
  akun			=document.getElementById('noakun');
  akun.value		=noakun;
  document.getElementById('noakungit').value= noakungit;
  document.getElementById('method').value='update';
}

function cancelKelompokBarang(){
  document.getElementById('method').value='insert';
  document.getElementById('kelnumber').disabled=false;	
  document.getElementById('kelnumber').value='';	
  document.getElementById('kelname').value='';
  document.getElementById('kelname1').value='';        
  document.getElementById('noakun').value='';
  document.getElementById('noakungit').value='';
}

function saveKelompokBarang()
{
  tujuan='log_slave_get_kelompok_barang.php';
  kode	= trim(document.getElementById('kelnumber').value);	
  nama	= trim(document.getElementById('kelname').value);
  nama1= trim(document.getElementById('kelname1').value);       
  noakun  = trim(document.getElementById('noakun').value);
  noakungit  = trim(document.getElementById('noakungit').value);
  status=document.getElementById('status').options[document.getElementById('status').selectedIndex].value;
  method= document.getElementById('method').value;

  kelbiaya=document.getElementById('kelompokbiaya');
  kelbiaya=kelbiaya.options[kelbiaya.selectedIndex].value;

  param='kode='+kode+'&nama='+nama+'&noakun='+noakun+'&nama1='+nama1+'&status='+status;
  param+='&method='+method+'&kelbiaya='+kelbiaya+'&noakungit='+noakungit;
  
	trapproval=document.getElementById('trapproval').innerHTML;
	
	if(trapproval=='')
	{
		alert("Please contact administrator to setup Approval.");
		return;
	}
	
	var tbl = document.getElementById("trapproval");
	var row = parseFloat(tbl.rows.length)+1;
	strUrl = '';
	for(i=1;i<row;i++)
	{
		if(document.getElementById('persetujuan'+i).innerHTML=='')
		{
			alert("Please contact administrator to setup Approval.");
			return false;
		}
		persetujuan = document.getElementById('persetujuan'+i).options[document.getElementById('persetujuan'+i).selectedIndex].value;
		if(persetujuan=='')
		{
			alert("Please compelete Approval");
			return;
		}
		strUrl += '&persetujuan['+i+']='+persetujuan;
	}
	param += strUrl;
  // alert(param);
  if(confirm('Saving '+nama+' .., Are you sure..?'))
  {
    if(kode=='' || nama=='')
          alert('Material group/code is obligatory');
    else
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
        else {
                //alert(con.responseText);
                document.getElementById('container').innerHTML=con.responseText;
            alert('Done');
                cancelKelompokBarang();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }	
  }  	

}

function delKelompok(kode,nama)
{
  tujuan='log_slave_get_kelompok_barang.php';
  param='kode='+kode+'&nama='+nama+'&method=delete';
  if(confirm('Deleting '+nama+' .., Are you sure..?'))
  {
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
          else {
                  //alert(con.responseText);
                  document.getElementById('container').innerHTML=con.responseText;
          }
        } else {
                busy_off();
                error_catch(con.status);
        }
      }	
    }  	
}


