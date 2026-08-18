function cariBarang(){
    txtcari=document.getElementById('txtcari').value;
	kelbrg=document.getElementById('kelbrg').value;
	subklbarang=document.getElementById('subklbarang').value;
    gdg  =document.getElementById('gdg').value;

	param='kelbrg='+kelbrg+'&gdg='+gdg+'&txtcari='+txtcari+'&subklbarang='+subklbarang;
    param+='&proses=preview';
	tujuan='log_slaveLaporanDaftarBarang.php';
	post_response_text(tujuan, param, respog);
    
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
                document.getElementById('list_daftarbrg').innerHTML=con.responseText;
				leftFixedTable();
			}else{
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getsubkelompok(){
    kelbrg=document.getElementById('kelbrg').value;
    
	param='kelbrg='+kelbrg+'&proses=getsubkelompok';
	tujuan='log_slaveLaporanDaftarBarang.php';
    post_response_text(tujuan, param, respog);
    
	function respog(){
		if(con.readyState == 4){
			if(con.status == 200){
				busy_off();
                document.getElementById('subklbarang').innerHTML=con.responseText;
			}else{
				busy_off();
                error_catch(con.status);
			}
		}
	}
}

function viewDetailbarang(kodebarang,ev)
{
        tujuan='log_slave_material_picture_detail.php?kodebarang='+kodebarang;
        content="<iframe name=disPhotobarang src="+tujuan+" frameborder=0 width=680px height=380px></iframe>";   
        showDialog1("Detail:"+kodebarang,content,'700','400',ev);

}
nameV='winbarang';
x=0;
function editDetailbarang(kodebarang,ev)
{
        x+=1;
        nx=nameV+x;
        tujuan='log_slave_edit_material_detail.php?kodebarang='+kodebarang;
    content="<iframe name="+nx+" src="+tujuan+" frameborder=0 width=590px height=490px></iframe>";   
    showDialog1("Edit Detail Barang:"+kodebarang,content,'500','300',ev);
}
function printFile(param,tujuan,title,ev)
{
   tujuan=tujuan+"?"+param;  
   width='700';
   height='400';
   content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
   showDialog1(title,content,width,height,ev); 	
}
function fisikKeExcel(ev,tujuan){
	kelbrg=document.getElementById('kelbrg');
	subklbarang=document.getElementById('subklbarang').value;
        gdg  =document.getElementById('gdg');
        txtcari=document.getElementById('txtcari').value;
        kelbrg=kelbrg.options[kelbrg.selectedIndex].value;
        gdg=gdg.options[gdg.selectedIndex].value;
	judul='Report Ms.Excel';	
	param='kelbrg='+kelbrg+'&gdg='+gdg+'&txtcari='+txtcari+'&subklbarang='+subklbarang;
	printFile(param,tujuan,judul,ev)	
}