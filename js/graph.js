function showoption(){
	document.getElementById('menumap').style.display = 'block';
}

function hideoption(){
	document.getElementById('menumap').style.display = 'none';
}

function homegraph(){
	document.getElementById('head').style.display = 'block';
	document.getElementById('foot').style.display = 'none';
	document.getElementById('addons').style.display = 'none';
}





function getmenu(idmenu){
	if(idmenu==undefined){
		idmenu=document.getElementById('idmenu').value;
	}
	pt = document.getElementById('pt');
    pt = pt.options[pt.selectedIndex].value;
	
	thn = document.getElementById('thn');
    thn = thn.options[thn.selectedIndex].value;
	
    param = 'method=getmenu';
    param += '&idmenu=' + idmenu;
	if (pt != '') {
        param += '&pt=' + pt;
    }
	if (thn != '') {
        param += '&thn=' + thn;
    }

    tujuan = 'bi_slave_graph.php';
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
					document.getElementById('head').style.display = 'none';
                    document.getElementById('foot').style.display = 'block';
					document.getElementById('menudt').innerHTML = con.responseText;
					document.getElementById('idmenu').value=idmenu;
					document.getElementById('addons').style.display = 'block';
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









// function getmenu(idmenu,rowdt){
	// if(idmenu==undefined){
		// idmenu=document.getElementById('idmenu').value;
	// }
	// pt = document.getElementById('pt');
    // pt = pt.options[pt.selectedIndex].value;
	
	// thn = document.getElementById('thn');
    // thn = thn.options[thn.selectedIndex].value;
	
    // param = 'method=getmenu';
    // param += '&idmenu=' + idmenu;
	// if (pt != '') {
        // param += '&pt=' + pt;
    // }
	// if (thn != '') {
        // param += '&thn=' + thn;
    // }

    // tujuan = 'bi_slave_graph.php';
    // post_response_text(tujuan, param, respog);
    // function respog()
    // {
        // if (con.readyState == 4)
        // {
            // if (con.status == 200)
            // {
                // busy_off();
                // if (!isSaveResponse(con.responseText))
                // {
                    // alert(con.responseText);
                // }
                // else {
					// document.getElementById('head').style.display = 'none';
                    // document.getElementById('foot').style.display = 'block';
					// document.getElementById('menudt').innerHTML = con.responseText;
					// document.getElementById('idmenu').value=idmenu;
					// document.getElementById('addons').style.display = 'block';
                    // if(rowdt!=0){
                        // pancing(idmenu,rowdt);    
                    // }
                    
                // }
            // }
            // else
            // {
                // busy_off();
                // error_catch(con.status);
            // }
        // }
    // }
// }


function pancing(menuid,maxrow){
    tampilatudua(menuid,maxrow,1);
}


function tampilatudua(menuid,maxrow){
    param='method=namafile';
    param += '&idmenu=' + menuid;
    tujuan='bi_slave_graph.php';
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
                        tampilatuatu(menuid,maxrow,con.responseText,1);  
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

function tampilatuatu(menuid,maxrow,tujuuan,currentrow){
    pt = document.getElementById('pt');
    pt = pt.options[pt.selectedIndex].value;
    
    thn = document.getElementById('thn');
    thn = thn.options[thn.selectedIndex].value;
    param = 'method=global';
    param += '&idmenu=' + idmenu;
    if (pt != '') {
        param += '&pt=' + pt;
    }
    if (thn != '') {
        param += '&thn=' + thn;
    }
    var datArr=JSON.parse(tujuuan);
    var txt = "";
    var isiw= "";
    for(x in datArr){
        txt = datArr[x];
        tujuan=txt;
        isiw=x;
        

		
        post_response_text(tujuan, param, respog);
        function respog(){
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
						
                        //document.getElementById('tampilin_'+isiw).innerHTML = con.responseText;         
						document.getElementById('tampilin_'+isiw).innerHTML = con.responseText;						
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
}




function detailpt(nopt,totalpt)
{
	for(i=1;i<=totalpt;i++)
	{
		var row = document.getElementById('unitlist'+nopt+i);
		if(row !== null){
			if (row.style.display == '') {
				row.style.display = 'none';
			}
			else {
				row.style.display = '';
			}
		}
	}
}


function detailunit(nounit,totalunit)
{
	for(i=1;i<=totalunit;i++)
	{
		var row = document.getElementById('divisilist'+nounit+i);
		if(row !== null){
			if (row.style.display == '') {
				row.style.display = 'none';
			}
			else {
				row.style.display = '';
			}
		}
	}
}

function detailgraph(id,file, ev)
{
    content = "<div>";
    //content += "<fieldset><legend>asd</legend>";
    // content += " </fieldset>";
    content += "<div id=listdetail style=\"height:200px;width:600px;overflow:scroll;\"></div><fieldset></div>";
    title =' Detail :';
    width = '610';
    height = '210';
    showDialog1bi(title, content, width, height, ev);
	isidetailgraph(id,file)
}


function isidetailgraph(id,file)
{
    thn=document.getElementById('thn').value;
	pt=document.getElementById('pt').value;
    param = 'method=detailgraph' + '&thn=' + thn + '&pt=' + pt;
    tujuan = file;
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
                    document.getElementById('listdetail').innerHTML = con.responseText;
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


// function detailgraph(id,file,ev)
// {
	// thn=document.getElementById('thn').value;
	// pt=document.getElementById('pt').value;
    // param = 'method=detailgraph' + '&thn=' + thn + '&pt=' + pt;
    // title="Data Detail";
     // showDialog1(title,"<iframe frameborder=0 style='width:795px;height:395px'"+
    // " src='"+ file +"?"+param+"'></iframe>",'800','400',ev);	
    // var dialog = document.getElementById('dynamic1');
    // dialog.style.top = '50px';
    // dialog.style.left = '15%';
// }




