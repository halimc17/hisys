function loadData()
{
  tujuan='sdm_slave_5Regionalbbm.php';
  param='proses=loadData';
  
    post_response_text(tujuan, param, respon); 	
    
    function respon()
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

function delRegionalbbm(regional,periode)
{
  tujuan='sdm_slave_5Regionalbbm.php';
  param='regional='+regional+'&periode='+periode+'&proses=delete';
  if(confirm('Deleting regional '+regional+' periode '+periode+' .., Are you sure..?'))
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
                  loadData();
          }
        } else {
                busy_off();
                error_catch(con.status);
        }
      }	
    }  	
}

function fillField(regional,periode,harga)
{
  document.getElementById('regional').value=regional;
  document.getElementById('regional').disabled=true;
  //getpriode(regional);
  document.getElementById('periode').value=periode;
  document.getElementById('periode').disabled=true;
  document.getElementById('harga').value=harga;     
  document.getElementById('proses').value='update';
}

function getharga(periode)
{
  regional = trim(document.getElementById('regional').value);
	var param = "periode="+periode+"&regional="+regional+"&proses=getharga";
    //alert(param);
    function respon() 
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
				else
				{

					document.getElementById('harga').value=con.responseText;	
                }
            }
			else
			{
				busy_off();
                error_catch(con.status);
            }
        }
    }
    
    post_response_text('sdm_slave_5Regionalbbm.php', param, respon);
}

function cancelRegionalbbm(){
  document.getElementById('proses').value='insert';
  document.getElementById('regional').value='';	
  document.getElementById('regional').disabled=false;
  document.getElementById('periode').value='';
  document.getElementById('periode').disabled=false;
  document.getElementById('harga').value=0;   
}

function saveRegionalbbm()
{
  tujuan='sdm_slave_5Regionalbbm.php';
  regional	= trim(document.getElementById('regional').value);	
  periode	= trim(document.getElementById('periode').value);
  harga= trim(document.getElementById('harga').value);      
  proses= document.getElementById('proses').value;

 //alert(svperiode);

  param='regional='+regional+'&periode='+periode+'&harga='+harga;
  param+='&proses='+proses;

  if(confirm('Saving regional '+regional+' periode '+periode+' .., Are you sure..?'))
  {
    if(regional=='' || periode=='' )
          alert('Material regional/periode is obligatory');
    else
     post_response_text(tujuan, param, respon);
  }
  function respon()
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
                cancelRegionalbbm();
                loadData();
        }
      } else {
        busy_off();
        error_catch(con.status);
      }
    }	
  }  	

}