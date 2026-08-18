<?
include('../config/connection.php');
include('../lib/nangkoelib.php');


OPEN_BODY_NEWBI();
?>
<link rel=stylesheet type=text/css href=../bi/style/graph.css>

<script>

function detailrow(nourut,total)
{
	for(i=1;i<=total;i++)
	{
		var row = document.getElementById('dt'+nourut+i);
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




function menu()
{
	document.getElementById('isidata').innerHTML = '';
	var row = document.getElementById('nav');
	if(row !== null){
		if (row.style.display == '') {
			row.style.display = 'none';
		}
		else {
			row.style.display = '';
		}
	}
}


function clearmenu()
{
	document.getElementById('nav').style.display = 'none';
}


function getdata(file)
{
    param = 'method=getdata';
    tujuan =file;
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
                    alert('ERROR TRANSACTION,\n' + con.responseText);
                }
                else {
                    document.getElementById('isidata').style.display = 'block';
                    document.getElementById('isidata').innerHTML = con.responseText;
					
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
</script>



  
<?



echo"<div id=head  style='display:block'>";
OPEN_BOX('','');





echo "<div style='height:600px' style='display:block'>";
$col=array("1"=>"orange","2"=>"teal","3"=>"green","4"=>"pink","5"=>"wisteria");


$str="select * from ".$dbname.".bi_5menugraph where tipe=0 order by caption asc ";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);

while($bar=$res->fetch())
{
	$menuht[$bar['id']]=$bar['id'];
	$capht[$bar['id']]=$bar['caption'];
	$icon[$bar['id']]=$bar['icon'];

	$no+=1;

	if($bar['icon']!='')
	{
		$logo="<img src=".$bar['icon']." class=iconmenu>";
	}
	else
	{
		$logo="<img src=images/owl.png class=iconmenu>";
	}
	
	$div='thumbnail tile tile-wide tile-'.$col[$no];
	echo"
	<div class='col-sm-1 col-md-3'>
		<div class='".$div."' onclick=getmenu(".$bar['id'].")>
			".$logo."
			<br><br>
				<h2 align=center><b>".$bar['caption']."</b></h2>
		</div>
	</div>
	";
}
echo"</div>";
CLOSE_BOX();
echo"</div>";


echo"<div id=foot  style='display:none'>";
OPEN_BOX('','  <img style=cursor:pointer class=ressicon onclick=showoption() src=images/menuBtn.png class=iconmenu>');


//Get All PT
$optPT = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str = "select * from ".$dbname.".organisasi where tipe = 'PT'";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);

while($bar = $res->fetch()){
	$optPT .= "<option value='".$bar['kodeorganisasi']."'>".$bar['namaorganisasi']."</option>";
}
$frm[0] .= "<table>
	<tr>
		<td>
			<select id='pt' onchange=getmenu()>".$optPT."</select>
		</td>
	</tr>
</table>";
$hfrm[0] = $_SESSION['lang']['pt'];


$optthn = "<option value=''>".$_SESSION['lang']['pilihdata']."</option>";
$str = "select distinct(substr(periode,1,4)) as tahun from ".$dbname.".setup_periodeakuntansi";
$res=$owlPDO->query($str) or die(print " Gagal: ".PDOException::getMessage());
$res->setFetchMode(PDO::FETCH_ASSOC);

while($bar = $res->fetch()){
	$optthn .= "<option value='".$bar['tahun']."'>".$bar['tahun']."</option>";
}
$frm[1] .= "<table>
	<tr>
		<td>
			<select id='thn' onchange=getmenu()>".$optthn."</select>
		</td>
	</tr>
</table>";
$hfrm[1] = $_SESSION['lang']['tahun'];

echo"<div id='menumap' style='display:none'>
<div id=header style='padding-top:15px;padding-bottom:15px;padding-left:10px'>
	<b>OWL Plantaion Graph</b>
	<span style='float:right;margin-right:5px;cursor:pointer' title='Hidden Menu' onclick='hideoption()'><img src='images/36.png'></span>
</div>
<hr>";
drawaccordion($hfrm,$frm);
echo "</div>";


echo "<div style='height:600px' id=menudt>";
echo"</div>";
CLOSE_BOX();
echo"</div>";



CLOSE_BODY_NEWBI();
?>