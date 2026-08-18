/*function graph(ev)
{
	kebun=document.getElementById('kebun').value;
	blok=document.getElementById('blok').value;
	
	if(kebun=='' || blok=='')
	{
		alert('Lengkapi Pengisian');return
	}
	else
	{
	}
	
   //param='kebun='+kebun+'&blok='+blok;
   param='method=getgraph'+'&kebun='+kebun+'&blok='+blok;
   //alert(param);
   //document.getElementById('container').innerHTML="<img src='pabrik_slave_grafikProduksi.php?"+param+"'>";		
   tujuan='kebun_slave_2hp.php?'+param;
   title=blok;
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev);
}*/


function graph(ev)
{
    
    kebun=document.getElementById('kebun').value;
    blok=document.getElementById('blok').value;
    //content= "<div id=listpupuk style=\"height:200px;width:250;overflow:scroll;\"></div>";
    content= "<div id=listpupuk  style=\"height:250px;width:650;overflow:scroll;\"></div>";
    content+= "<div id=listproduksi style=\"height:250px;width:650;overflow:scroll;\"></div>";
    title='Graph';
     width='1000';
    height='500';
    showDialog1(title,content,width,height,ev);	
    getgraphpupuk(kebun,blok);
}


function graphv()
{
    //document.getElementById('listpupuk').innerHTML='';
   // document.getElementById('listproduksi').innerHTML='';
    kebun=document.getElementById('kebun').value;
    blok=document.getElementById('blok').value;
    
    if(kebun=='' || blok=='')
    {
        alert('Field masih kosong');return;
    }
    
    getgraphpupuk(kebun,blok);
}


function getgraphpupuk(kebun,blok)
{
    param='method=getgraphpupuk'+'&kebun='+kebun+'&blok='+blok;
    tujuan = 'kebun_slave_2hp.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                                alert(con.responseText);
                }
                else {
                   
                    document.getElementById('listpupuk').innerHTML=con.responseText;
                    getgraphproduksi(kebun,blok);
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
		
}



function getgraphproduksi(kebun,blok)
{
    param='method=getgraphproduksi'+'&kebun='+kebun+'&blok='+blok;
    tujuan = 'kebun_slave_2hp.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                                alert(con.responseText);
                }
                else {
                   
                    document.getElementById('listproduksi').innerHTML=con.responseText;
                   // getgraphproduksi(kebun,blok);
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
		
}











function getblok()
{
    kebun=document.getElementById('kebun').value; 
    param='method=getblok'+'&kebun='+kebun;
    tujuan='kebun_slave_2hp.php';
    post_response_text(tujuan, param, respog);
    function respog()
    {
        if(con.readyState==4)
        {
            if (con.status == 200) 
            {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                }
                else {
                    //alert(con.responseText);
                    document.getElementById('blok').innerHTML=con.responseText;
                   
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
     }  	
}

