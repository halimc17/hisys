/*
function detailpengirmanblok(kdorg,tgl2,tipe,ev)
{
	param='method=detailpengirmanblok';
	param += '&kdorg=' + kdorg+'&tgl2=' + tgl2+'&tipe=' + tipe;
	title="Data Detail";
	 showDialog1(title,"<iframe frameborder=0 style='width:845px;height:395px'"+
	" src='kebun_slave_2rpp.php?"+param+"'></iframe>",'850','400',ev);	
	var dialog = document.getElementById('dynamic1');
	dialog.style.top = '50px';
	dialog.style.left = '15%';
}


function detailspb(blok, tgl, tipe, ev) {
	param = 'tipe=' + tipe + '&blok=' + blok + '&tgl=' + tgl;
	tujuan = 'kebun_slave_2rpp.php' + "?" + param;
	width = '500';
	height = '200';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	showDialog1('Detail Transaksi' + blok, content, width, height, ev);
}

*/

function showheader(){
	if(document.getElementById('tableheader').style.display=="none"){		
		document.getElementById('tableheader').style.display="block";
		document.getElementById('showhead').innerHTML="Hide Filter";
		document.getElementById('tombolexport').style.display="none";
	}else{
		document.getElementById('tableheader').style.display="none";
		document.getElementById('tombolexport').style.display="block";
		document.getElementById('showhead').innerHTML="Show Filter";
		document.getElementById('printContainerv2').style.display = 'block';
		document.getElementById('prev').style.display = 'none';
	}	
}




function detailspb(blok,tgl2,tipe,ev)
{
    width = '600';
	height = '200';
    content = "<div id=detailspb  style='overflow:auto;height:190px;width:590px';></div>";
    ev = 'event';
    title = "Detail";
    showDialog4(title, content, width, height, ev); 
    param = 'method=detailspb' + '&blok=' + blok + '&tgl2=' + tgl2 + '&tipe=' + tipe;
    tujuan = 'kebun_slave_2rpp.php';
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
                else
                {
                    document.getElementById('detailspb').innerHTML = con.responseText;
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


function detailpengirmanblokexcel(kdorg,tgl2,tipe,print,ev)
{
	//param = 'tipe=' + tipe + '&blok=' + blok + '&tgl=' + tgl;
	param = 'method=detailpengirmanblok' + '&kdorg=' + kdorg + '&tgl2=' + tgl2 + '&tipe=' + tipe + '&print=' + print;
	tujuan = 'kebun_slave_2rpp.php' + "?" + param;
	width = '600';
	height = '200';
	content = "<iframe frameborder=0 width=100% height=100% src='" + tujuan + "'></iframe>"
	showDialog5('Detail Transaksi' + kdorg, content, width, height, ev);
}


function detailpengirmanblok(kdorg,tgl2,tipe,print,ev)
{
	width = '900';
    height = '400';
    content = "<div id=detailpengirmanblok  style='overflow:auto;height:100%;width:100%';></div>";
    ev = 'event';
    title = "Detail";
    showDialog1(title, content, width, height, ev); 
    param = 'method=detailpengirmanblok' + '&kdorg=' + kdorg + '&tgl2=' + tgl2 + '&tipe=' + tipe + '&print=' + print;
    tujuan = 'kebun_slave_2rpp.php';
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
                else
                {
                    document.getElementById('detailpengirmanblok').innerHTML = con.responseText;
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




function prev3(kdorg,tgl2)
{
    kdorg=document.getElementById('kdorg').value;
    tgl2=document.getElementById('tgl2').value;
    param = 'method=prev3';
    param += '&kdorg=' + kdorg+'&tgl2=' + tgl2;
    tujuan = 'kebun_slave_2rpp.php';
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
                    document.getElementById('prev3').innerHTML = con.responseText;
				
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

function prev2(kdorg,tgl2)
{
    param='method=prev2'+'&kdorg='+kdorg+'&tgl2='+tgl2;
    tujuan = 'kebun_slave_2rpp.php';
    post_response_text(tujuan, param, respog);		
    function respog(){
        if (con.readyState == 4) {
            if (con.status == 200) {
                busy_off();
                if (!isSaveResponse(con.responseText)) {
                                alert(con.responseText);
                }
                else {
                    document.getElementById('prev2').innerHTML=con.responseText;
					prev3(kdorg,tgl2);
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }
    } 
		
}


function prev1()
{
    kdorg=document.getElementById('kdorg').value;
    tgl2=document.getElementById('tgl2').value;
    param = 'method=prev1';
    param += '&kdorg=' + kdorg+'&tgl2=' + tgl2;
    tujuan = 'kebun_slave_2rpp.php';
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
                    alert(con.responseText	);
                }
                else {
					document.getElementById('printContainerv2').style.display = 'none';
					document.getElementById('prev').style.display = 'block';
                    document.getElementById('prev1').innerHTML = con.responseText;
					document.getElementById('isitglr').innerHTML = tgl2;
					document.getElementById('isitglp').innerHTML = tgl2;
					document.getElementById('isitglg').innerHTML = tgl2;
					prev2(kdorg,tgl2);
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