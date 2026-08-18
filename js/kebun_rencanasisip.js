/**
 * @author repindra.ginting
 */

function hapus(periode,blok)
{
    periode2=document.getElementById('periode2').options[document.getElementById('periode2').selectedIndex].value;
    if(confirm('Delete '+periode+' '+blok+'?')){
        met='delete';
        param='periode='+periode+'&blok='+blok+'&method='+met+'&periode2='+periode2;
        tujuan='kebun_slave_rencanasisip.php';
        post_response_text(tujuan, param, respog);		
    }else{
        
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

function posting(periode,blok,ev)
{
	var content = "";
	content += "<input type=hidden id=postPeriode value='"+periode+"'>";
	content += "<input type=hidden id=postBlok value='"+blok+"'>";
	content += "<fieldset style='height:60px'><div>Nomor Berita Acara Sisip :</div>";
	content += "<div><input id=baSisip style='width:187px' class=myinputtext onkeypress='return tanpa_kutip(event)'></div>";
	
	content += "<hr><div ><button class=mybutton onclick=\"doPosting('"+periode+"','"+blok+"')\">";
	content += "Posting</button></div></fieldset>";
	showDialog1('Posting',content,210,75,ev);
	frame = document.getElementById('dynamic1');
	frame.style.left = '700px';
}

function doPosting(periode,blok) {
    periode2=document.getElementById('periode2').options[document.getElementById('periode2').selectedIndex].value;
	ba = document.getElementById('baSisip').value;
	if(ba=='') {
		alert("Nomor BA harus diisi");
		return;
	}
    if(confirm('Posting '+periode+' '+blok+'?')){
        met='posting';
        param='periode='+periode+'&blok='+blok+'&method='+met+'&periode2='+periode2
			+'&ba='+ba;
        tujuan='kebun_slave_rencanasisip.php';
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
					closeDialog();
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

function pilihperiode()
{
    periode2=document.getElementById('periode2').options[document.getElementById('periode2').selectedIndex].value;
        param='periode2='+periode2;
        tujuan='kebun_slave_rencanasisip.php';
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
//                    cancel();
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }
    
}

function gantiblok(){        
    blok=document.getElementById('blok').options[document.getElementById('blok').selectedIndex].value;
    param='blok='+blok+'&method=gantiblok';
    tujuan='kebun_slave_rencanasisip.php';
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
                    hasil = con.responseText.split('##');
                    document.getElementById('pokok').value=hasil[0];
                    document.getElementById('sph').value=hasil[1];
                    document.getElementById('luas').value=hasil[2];
//                    alert(hasil[0]);
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }

}


function hitungsph(){        
    blok=document.getElementById('blok').options[document.getElementById('blok').selectedIndex].value;
    pokok=document.getElementById('pokok').value;
    rencanasisip=document.getElementById('rencanasisip').value;
	
    param='blok='+blok+'&rencanasisip='+rencanasisip+'&pokok='+pokok+'&method=hitungsph';
	// alert(param);
	tujuan='kebun_slave_rencanasisip.php';
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
                     hasil = con.responseText.split('##');
					
                    document.getElementById('sphssp').value=hasil[0];
                    document.getElementById('pokoksetelahsisip').value=hasil[1];
                    
                }
            }
            else {
                busy_off();
                error_catch(con.status);
            }
        }	
    }

}


function simpan()
{
    kebun=document.getElementById('kebun').options[document.getElementById('kebun').selectedIndex].value;
    blok=document.getElementById('blok').options[document.getElementById('blok').selectedIndex].value;
    periode=document.getElementById('periode').options[document.getElementById('periode').selectedIndex].value;
    pokok=document.getElementById('pokok').value;
    sph=document.getElementById('sph').value;
    pokokmati=document.getElementById('pokokmati').value;
    rencanasisip=document.getElementById('rencanasisip').value;
    keterangan=document.getElementById('keterangan').options[document.getElementById('keterangan').selectedIndex].value;
    met=document.getElementById('method').value;
    periode2=periode;
    if((trim(periode)=='')||(trim(kebun)=='')||(trim(blok)=='')||(trim(pokok)=='0')||(trim(sph)=='0')||(trim(pokokmati)=='')||(trim(rencanasisip)=='')||(trim(keterangan)==''))
    {
        alert('Please fill all fields.');
    }
    else
    {
        if(parseFloat(rencanasisip) > parseFloat(pokokmati)){
            alert('Rencana Sisip tidak bisa melebihi Pokok Mati');
        }else{
            param='kebun='+kebun+'&blok='+blok+'&method='+met+'&periode='+periode+'&pokok='+pokok+'&sph='+sph+'&pokokmati='+pokokmati+'&rencanasisip='+rencanasisip+'&keterangan='+keterangan+'&periode2='+periode2;
            tujuan='kebun_slave_rencanasisip.php';
            post_response_text(tujuan, param, respog);		
        }
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

function fillField(periode,blok,pokok,sph,pokokmati,rencanasisip,keterangan,luas)
{
    l=document.getElementById('periode');
    
    for(a=0;a<l.length;a++)
    {
        if(l.options[a].value==periode)
        {
            l.options[a].selected=true;
        }
    }

    k=document.getElementById('blok');
    
    for(a=0;a<k.length;a++)
    {
        if(k.options[a].value==blok)
        {
            k.options[a].selected=true;
        }
    }

    j=document.getElementById('keterangan');
    
    for(a=0;a<j.length;a++)
    {
        if(j.options[a].value==keterangan)
        {
            j.options[a].selected=true;
        }
    }

    document.getElementById('periode').disabled=true;
    document.getElementById('blok').disabled=true;
    document.getElementById('pokok').value=pokok;
    document.getElementById('sph').value=sph;
    document.getElementById('pokokmati').value=pokokmati;
    document.getElementById('rencanasisip').value=rencanasisip;
    document.getElementById('luas').value=luas;
    document.getElementById('method').value='update';
	hitungsph()
}

function cancel()
{
    document.getElementById('blok').disabled=false;
    document.getElementById('periode').disabled=false;
    document.getElementById('blok').value='';
    document.getElementById('pokok').value='';
    document.getElementById('sph').value='';
    document.getElementById('pokokmati').value='';
    document.getElementById('rencanasisip').value='';
    document.getElementById('method').value='insert';		
    document.getElementById('keterangan').value='';
    document.getElementById('pokoksetelahsisip').value='';
    document.getElementById('sphssp').value='';
    document.getElementById('luas').value='';
    
    periode2=document.getElementById('periode2').options[document.getElementById('periode2').selectedIndex].value;
    l=document.getElementById('periode');    
    for(a=0;a<l.length;a++)
    {
        if(l.options[a].value==periode2)
        {
            l.options[a].selected=true;
        }
    }
    
}