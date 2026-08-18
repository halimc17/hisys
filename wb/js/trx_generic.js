//BEGIN GET INDICATOR//
var mainReminder;
var lastMess;
var idle=1;
var counttemp=0;
var weightemp=0;
startReminder();
function startReminder()
{
	//default looping 
    //1000 adalah 1 detik(a secon)
    interval=500;
    mainReminder= window.setInterval("getReminderData()",interval);
}

function stopreminder(){
	clearInterval(mainReminder);
}

function getReminderData()
{
	reminderSlave='wb_getIndicatorValue.php';
	if (idle == 0) 
	{
		//if prev query has not response than wait
	}
	else 
	{
		//post request
        post_param(reminderSlave, 'x='+Math.random(), respot);
        idle = 0;//waiting for response;
	}
	
	function respot() 
	{
		if (hendrycon.readyState == 4) 
		{
			if (hendrycon.status == 200) 
			{
				idle=1;//set idle=true
                if (!isSaveResponse(hendrycon.responseText)) 
				{
					alert(hendrycon.responseText);
                } 
				else 
				{
					try 
					{
						if(hendrycon.responseText==0)
						{
							if(counttemp > 2)
							{
								document.getElementById('weight').value = hendrycon.responseText;
								counttemp = 0;
								weightemp = 0;
							}
							else
							{
								document.getElementById('weight').value = weightemp;
								counttemp += 1;
							}
						}
						else
						{
							document.getElementById('weight').value = hendrycon.responseText;
							weightemp = hendrycon.responseText;
							counttemp = 0;
						}
					}
					catch(ER)
					{
						clearTimeout(mainReminder);
					}
				}
			}
			else 
			{
				error_catch(hendrycon.status);
            }
        }
    }
}
var hendrycon = createXMLHttpRequest();
function post_param(tujuan,param,functiontoexecute)
{
	hendrycon.open("POST", tujuan, true);
    hendrycon.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	
	hendrycon.onreadystatechange = eval(functiontoexecute);
    hendrycon.send(param);
}
//END GET INDICATOR//


function ambil_tanggal(idel1,idel2,tipe)
{
	var myDate = new Date();
    var tanggal,bulan,tahun,jam,menitdetik;
    var output;
    
	tanggal= myDate.getDate().toString();
    bulan  = (myDate.getMonth()+1).toString();
    tahun  = myDate.getFullYear().toString();
    jam     = myDate.getHours().toString();
    menit  = myDate.getMinutes().toString();
    detik  = myDate.getSeconds().toString();
	
	if(tanggal.length<2)
		tanggal="0"+tanggal;
	if(bulan.length<2)
		bulan="0"+bulan;
	if(jam.length<2)
		jam="0"+jam;
	if(menit.length<2)
		menit="0"+menit;
	if(detik.length<2)
		detik="0"+detik;
	
	output=tanggal+"-"+bulan+"-"+tahun+" "+jam+":"+menit+":"+detik;
    document.getElementById(idel1).value=output;
    weigh=document.getElementById('weight').value;
    document.getElementById(idel2).value = weigh;
	

	wei1st = document.getElementById('wei1st').value;
	wei2nd = document.getElementById('wei2nd').value;
	kgpotongan = document.getElementById('kgpotongan').value;
	if (tipe == 'penerimaan') {
		bruto = wei1st-wei2nd;
	}else{
		bruto = wei2nd-wei1st;
	}
	netto = bruto-kgpotongan;
	if (wei1st !== '' && wei2nd !== '') {
		document.getElementById('bruto').value=bruto;
		document.getElementById('netto').value=netto;
	}

}

function periksa(obj)
{
	if(obj.value=='')
	{
		obj.value=0;
    }
}

function roundToTwo(num)
{
	return +(Math.round(num + "e+2")  + "e-2");
}
