<?
require_once('master_validation.php');
include('lib/nangkoelib.php');
echo open_body();
?>
<script language=javascript src=js/zTools.js></script>
<script language=javascript1.2 src='js/budget_5harikerja.js?v=<?php echo time(); ?>'></script>
<?
$arr="##tahunbudget##hrsetahun##hrminggu##hrlibur##hrliburminggu##hkeffektif##method##oldtahunbudget##jlhcuti##s1s2##h1h2##p1p3##mangkir##unit";

include('master_mainMenu.php');
OPEN_BOX('','<span class=judul>'.getMenu('budget_5harikerja').'</span>');
echo"<fieldset>
     <legend>".$_SESSION['lang']['jumlah']." ".$_SESSION['lang']['hk']."</legend>
	 <table border=0>
		 <tr>
			<td colspan=3>".$_SESSION['lang']['unit']."</td>
		   <td><input type=text class=myinputtext id=unit name=unit disabled style=\"width:100px;\" maxlength=4 value=".$_SESSION['empl']['lokasitugas']." /></td>
		 </tr>
		 <tr>
			<td colspan=3>".$_SESSION['lang']['budgetyear']."</td>
		   <td><input type=text class=myinputtextnumber id=tahunbudget name=tahunbudget onkeypress=\"return angka_doang(event);\" style=\"width:100px;\" maxlength=4 /></td>
		 </tr>
		 <tr>
			<td colspan=3>1. Jumlah hari kerja  Tahun (HK)</td>
		   <td><input type=text class=myinputtextnumber id=jlhhkethn name=jlhhkethn onkeypress=\"return angka_doang(event);\" style=\"width:100px;\" disabled maxlength=4 /></td>
		 </tr>
		 <tr style=color:blue>
			<td width=20px></td>
		   <td width=250px colspan=2>1.1. Jumlah hari setahun (HKS)</td>
		   <td><input type=text class=myinputtextnumber id=hrsetahun name=hrsetahun onkeypress=\"return angka_doang(event);\" style=\"width:100px;\" maxlength=4 value=365 onchange=tambah() /></td>
		 </tr>
		 <tr>
			<td width=20px></td>
		   <td width=250px colspan=2>1.2. Hari libur (HL)</td>
		   <td><input type=text class=myinputtextnumber id=ttlhrlbr name=ttlhrlbr onkeypress=\"return angka_doang(event);\" style=\"width:100px;\" disabled maxlength=4 /></td>
		 </tr>
		 
		 <tr style=color:blue>
			<td></td>
			<td width=20px></td>
		   <td>- Hari Minggu</td>
		   <td><input type=text class=myinputtextnumber id=hrminggu name=hrminggu onkeypress=\"return angka_doang(event);\" style=\"width:100px;\" maxlength=100 onchange=tambah() /></td>
		 </tr>
		 <tr style=color:blue>
			<td></td>
			<td width=20px></td>
		   <td>- Hari libur nasional/resmi Pemerintah</td>
		   <td><input type=text class=myinputtextnumber id=hrlibur name=hrlibur onkeypress=\"return angka_doang(event);\" style=\"width:100px;\" maxlength=100 onchange=tambah() /></td>
		 </tr>
		 <tr style=color:blue>
			<td></td>
			<td width=20px></td>
		   <td>- Hari libur bertepatan Minggu</td>
		   <td><input type=text class=myinputtextnumber id=hrliburminggu name=hrliburminggu onkeypress=\"return angka_doang(event);\" style=\"width:100px;\" maxlength=100 onchange=tambah() /></td>
		 </tr>
		 
		 <tr>
			<td colspan=3>2. Hari Absensi (HA)  . . . . . . .</td>
		   <td><input type=text class=myinputtextnumber id=jlhhrabsen name=jlhhrabsen onkeypress=\"return angka_doang(event);\" style=\"width:100px;\" disabled maxlength=4 /></td>
		 </tr>
		 <tr style=color:blue>
			<td width=20px></td>
		   <td width=250px colspan=2>2.1. Cuti tahunan (ct)</td>
		   <td><input type=text class=myinputtextnumber id=jlhcuti name=jlhcuti onkeypress=\"return angka_doang(event);\" style=\"width:100px;\" maxlength=4  onchange=tambah() /></td>
		 </tr>
		 <tr>
			<td width=20px></td>
		   <td width=250px colspan=2>2.2. Cuti sakit/ijin (sim)</td>
		   <td><input type=text class=myinputtextnumber id=jlhsakit name=jlhsakit onkeypress=\"return angka_doang(event);\" style=\"width:100px;\" maxlength=4 disabled onchange=tambah() /></td>
		 </tr>
		 <tr style=color:blue>
			<td></td>
			<td width=20px></td>
		   <td>- S1/S2</td>
		   <td><input type=text class=myinputtextnumber id=s1s2 name=s1s2 onkeypress=\"return angka_doang(event);\" style=\"width:100px;\" maxlength=100 onchange=tambah() /></td>
		 </tr>
		 <tr style=color:blue>
			<td></td>
			<td width=20px></td>
		   <td>- H1/H2</td>
		   <td><input type=text class=myinputtextnumber id=h1h2 name=h1h2 onkeypress=\"return angka_doang(event);\" style=\"width:100px;\" maxlength=100 onchange=tambah() /></td>
		 </tr>
		 <tr style=color:blue>
			<td></td>
			<td width=20px></td>
		   <td>- P1/P3</td>
		   <td><input type=text class=myinputtextnumber id=p1p3 name=p1p3 onkeypress=\"return angka_doang(event);\" style=\"width:100px;\" maxlength=100 onchange=tambah() /></td>
		 </tr>
		 <tr style=color:blue>
			<td></td>
			<td width=20px></td>
		   <td>- Mangkir</td>
		   <td><input type=text class=myinputtextnumber id=mangkir name=mangkir onkeypress=\"return angka_doang(event);\" style=\"width:100px;\" maxlength=100 onchange=tambah() /></td>
		 </tr>
		 
		 <tr>
			<td colspan=3>3. Jumlah hari kerja efektif (HKE)</td>
		   <td><input type=text class=myinputtextnumber id=hkeffektif name=hkeffektif onkeypress=\"return tanpa_kutip(event);\" style=\"width:100px;\" maxlength=100 disabled /></td>
		 </tr>
		 
		 <tr>
			<td colspan=3>4. Prosentase hari kerja efektif (% HKE)</td>
		   <td><input type=text class=myinputtextnumber id=persenhke name=persenhke onkeypress=\"return tanpa_kutip(event);\" style=\"width:100px;\" maxlength=100 disabled /></td>
		 </tr>
		 
		 <tr>
			<td colspan=4 align=right>
                <input type=hidden value=insert id=method>
                <button class=mybutton onclick=savehk('log_slave_budget_5harikerja','".$arr."')>".$_SESSION['lang']['save']."</button>
                <button class=mybutton onclick=cancelIsi()>".$_SESSION['lang']['cancel']."</button>
			</td>
		 </tr>

	 </table>
     </fieldset><input type='hidden' id=oldtahunbudget name=oldtahunbudget />";
CLOSE_BOX();

OPEN_BOX();
echo"<fieldset><legend>".$_SESSION['lang']['list']."</legend><table class=sortable cellspacing=1 border=0>
     <thead>
	  <tr class=rowheader>
	   <td align=center rowspan=2>No</td>
	   <td align=center width=50px rowspan=2>".$_SESSION['lang']['unit']."</td>
	   <td align=center width=50px rowspan=2>".$_SESSION['lang']['budgetyear']."</td>
	   <td align=center width=50px rowspan=2>Jumlah hari kerja Tahun (HK)</td>
	   <td align=center width=50px rowspan=2>Jumlah hari setahun (HKS)</td>
	   <td align=center width=50px colspan=4>Hari libur (HL)</td>
	   <td align=center width=50px rowspan=2>Hari Absensi (HA)</td>
	   <td align=center width=50px rowspan=2>Cuti tahunan (ct)</td>
	   <td align=center width=50px colspan=5>Cuti sakit/ijin (sim)</td>
	   <td align=center width=50px rowspan=2>Jumlah hari kerja efektif (HKE)</td>
	   <td align=center width=50px rowspan=2>Prosentase hari kerja efektif (% HKE)</td>
	   
	   <td align=center rowspan=2>".$_SESSION['lang']['action']."</td>
	  </tr>
	  <tr class=rowheader style=align:center>
	   <td align=center width=50px>Hari Minggu</td>
	   <td align=center width=50px>Hari Libur</td>
	   <td align=center width=50px>Hari Libur Minggu</td>
	   <td align=center width=50px>Total</td>
	   <td align=center width=50px>S1/S2</td>
	   <td align=center width=50px>H1/H2</td>
	   <td align=center width=50px>P1/P3</td>
	   <td align=center width=50px>Mangkir</td>
	   <td align=center width=50px>Total</td>
	  </tr>
	 </thead>
	 <tbody id=container>";
	 echo"<script>loadData()</script>";
echo"</tbody>
     <tfoot>
        </tfoot>
        </table></fieldset>";
CLOSE_BOX();
echo close_body();
?>