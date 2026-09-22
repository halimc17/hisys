// JavaScript Document
function filterKelJurnal()
{
	kodeunit=document.getElementById('kodeunitfilter').value;
	periode=document.getElementById('periodefilter').value;

	param='kodeunit='+kodeunit+'&periode='+periode;
	tujuan='keu_slave_5kelompokjurnal_filter.php';
	post_response_text(tujuan, param, respog);
	function respog()
	{
		if(con.readyState==4)
		{
			if (con.status == 200) {
				busy_off();
				document.getElementById('kelJurnalTableWrap').innerHTML=con.responseText;
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
