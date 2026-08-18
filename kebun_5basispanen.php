<?//@Copy nangkoelframework
require_once('master_validation.php');
include('lib/nangkoelib.php');
include_once('lib/zLib.php');
echo open_body();
include('master_mainMenu.php');
OPEN_BOX();
?>
<script language=javascript src=js/zMaster.js></script>
<link rel=stylesheet type=text/css href=style/zTable.css>
  
<p align="left"><u><b><font face="Arial" size="5" color="#000080">Basis Panen</font></b></u></p>
<?php
#======Select Prep======
$optTopografi = makeOption($dbname,'setup_topografi','topografi,keterangan');
#======End Select Prep======
#=======Form============
echo "<div style='margin-bottom:30px'>";
$els = array();
# Fields
$els[] = array(
  makeElement('topografi','label',$_SESSION['lang']['topografi']),
  makeElement('topografi','select','',array('style'=>'width:100px'),$optTopografi)
);
$els[] = array(
  makeElement('keterangan','label',$_SESSION['lang']['keterangan']),
  makeElement('keterangan','text','',array('style'=>'width:250px','maxlength'=>'50',
    'onkeypress'=>'return tanpa_kutip(event)'))
);
$els[] = array(
  makeElement('batasbawah','label',$_SESSION['lang']['batasbawah']),
  makeElement('batasbawah','text','',array('style'=>'width:100px','maxlength'=>'10',
    'onkeypress'=>'return angka_doang(event)'))
);
$els[] = array(
  makeElement('batasatas','label',$_SESSION['lang']['batasatas']),
  makeElement('batasatas','text','',array('style'=>'width:100px','maxlength'=>'10',
    'onkeypress'=>'return angka_doang(event)'))
);
$els[] = array(
  makeElement('basisboronglaki','label',$_SESSION['lang']['basisboronglaki']),
  makeElement('basisboronglaki','text','',array('style'=>'width:100px','maxlength'=>'10',
    'onkeypress'=>'return angka_doang(event)'))
);
$els[] = array(
  makeElement('basisborongperempuan','label',$_SESSION['lang']['basisborongperempuan']),
  makeElement('basisborongperempuan','text','',array('style'=>'width:100px','maxlength'=>'10',
    'onkeypress'=>'return angka_doang(event)'))
);
$els[] = array(
  makeElement('basistugaslaki','label',$_SESSION['lang']['basistugaslaki']),
  makeElement('basistugaslaki','text','',array('style'=>'width:100px','maxlength'=>'10',
    'onkeypress'=>'return angka_doang(event)'))
);
$els[] = array(
  makeElement('basistugasperempuan','label',$_SESSION['lang']['basistugasperempuan']),
  makeElement('basistugasperempuan','text','',array('style'=>'width:100px','maxlength'=>'10',
    'onkeypress'=>'return angka_doang(event)'))
);
$els[] = array(
  makeElement('satuan','label',$_SESSION['lang']['satuan']),
  makeElement('satuan','text','',array('style'=>'width:50px','maxlength'=>'3',
    'onkeypress'=>'return tanpa_kutip(event)'))
);

# Fields
$fieldStr = '##topografi##keterangan##batasbawah##batasatas##basisboronglaki##basisborongperempuan';
$fieldStr .= '##basistugaslaki##basistugasperempuan##satuan';
$fieldArr = explode("##",substr($fieldStr,2,strlen($fieldStr)-2));

# Button
$els['btn'] = array(
  genFormBtn($fieldStr,
    'kebun_5basispanen',"##topografi")
);

# Generate Field
echo genElement($els);
echo "</div>";
#=======End Form============

#=======Table===============
# Display Table
echo "<div style='height:200px;overflow:auto'>";
echo masterTable($dbname,'kebun_5basispanen',"*",array(),array(),null,array(),null,'topografi');
echo "</div>";
#=======End Table============
?>
<!--FORM NAME = " ">
<p align="center"><u><b><font face="Verdana" size="4" color="#000080">Basis Panen</font></b></u></p>
<table border="0" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#111111" width="87%" id="AutoNumber1" height="115">
  <tr>
    <td width="24%" height="1">
    <p style="margin-top: 0; margin-bottom: 0">
    <font face="Arial">Topografi</font></td>
    <td width="46%" height="1">
    <p style="margin-top: 0; margin-bottom: 0"><font face="Fixedsys"> 
    <input type=text size="6" name="koderekening">&nbsp; </font>
    </td>
    <td width="16%" height="1">
    <p style="margin-top: 0; margin-bottom: 0">
    </td>
  </tr>
  <tr>
    <td width="24%" height="22">
    <p style="margin-top: 0; margin-bottom: 0">
    <font face="Arial">Keterangan</font></td>
    <td width="46%" height="22">
    <p style="margin-top: 0; margin-bottom: 0"><font face="Fixedsys"> 
    <input type=text size="53" name="tanggal"></font></td>
    <td width="16%" height="22">
    <p style="margin-top: 0; margin-bottom: 0">&nbsp;</td>
  </tr>
  <tr>
    <td width="24%" height="1">
    <p style="margin-top: 0; margin-bottom: 0">
    <font face="Arial">Batas Bawah</font></td>
    <td width="46%" height="1">
    <p style="margin-top: 0; margin-bottom: 0"><font face="Fixedsys"> 
    <input type=text size="6" name="koderekening">&nbsp; </font>
    </td>
    <td width="16%" height="22">
    <p style="margin-top: 0; margin-bottom: 0">&nbsp;</td>
  </tr>
  <tr>
    <td width="24%" height="1">
    <p style="margin-top: 0; margin-bottom: 0">
    <font face="Arial">Batas Atas</font></td>
    <td width="46%" height="1">
    <p style="margin-top: 0; margin-bottom: 0"><font face="Fixedsys"> 
    <input type=text size="6" name="koderekening">&nbsp; </font>
    </td>
    <td width="16%" height="22">&nbsp;</td>
  </tr><tr>
    <td width="24%" height="1">
    <p style="margin-top: 0; margin-bottom: 0">
    <font face="Arial">Basis Borong Laki-laki</font></td>
    <td width="46%" height="1">
    <p style="margin-top: 0; margin-bottom: 0"><font face="Fixedsys"> 
    <input type=text size="6" name="koderekening">&nbsp; </font>
    </td>
    <td width="16%" height="22">&nbsp;</td>
  </tr><tr>
    <td width="24%" height="1">
    <p style="margin-top: 0; margin-bottom: 0">
    <font face="Arial">Basis Borong Perempuan</font></td>
    <td width="46%" height="1">
    <p style="margin-top: 0; margin-bottom: 0"><font face="Fixedsys"> 
    <input type=text size="6" name="koderekening">&nbsp; </font>
    </td>
    <td width="16%" height="22">&nbsp;</td>
  </tr><tr>
    <td width="24%" height="1">
    <p style="margin-top: 0; margin-bottom: 0">
    <font face="Arial">Basis Tugas Laki-laki</font></td>
    <td width="46%" height="1">
    <p style="margin-top: 0; margin-bottom: 0"><font face="Fixedsys"> 
    <input type=text size="6" name="koderekening">&nbsp; </font>
    </td>
    <td width="16%" height="22">&nbsp;</td>
  </tr>
  <tr>
    <td width="24%" height="1">
    <p style="margin-top: 0; margin-bottom: 0">
    <font face="Arial">Basis Tugas Perempuan</font></td>
    <td width="46%" height="1">
    <p style="margin-top: 0; margin-bottom: 0"><font face="Fixedsys"> 
    <input type=text size="6" name="koderekening">&nbsp; </font>
    </td>
    <td width="16%" height="22">&nbsp;</td>
  </tr>
  <tr>
    <td width="24%" height="1">
    <p style="margin-top: 0; margin-bottom: 0">
    <font face="Arial">Satuan</font></td>
    <td width="46%" height="1">
    <p style="margin-top: 0; margin-bottom: 0"><font face="Fixedsys"> 
    <select size="1" name="D1">
    <option>JJG</option>
    <option>KG</option>
    </select>&nbsp; </font>
    </td>
    <td width="16%" height="22">&nbsp;</td>
  </tr>
  </table>
<p style="margin-top: 0; margin-bottom: 0">&nbsp;</p>
<p style="margin-top: 0; margin-bottom: 0"><font face="Fixedsys">
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="submit" value="Simpan" name="Simpan">
<input type="reset" value="Batal" name="Batal"></font></p>
<p style="margin-top: 0; margin-bottom: 0">&nbsp;</p>
<table border="1" cellpadding="0" cellspacing="0" style="border-collapse: collapse" bordercolor="#111111" width="100%" id="AutoNumber2"><tr><td width="7%" align="center">Topografi</td><td width="19%" align="center">Keterangan</td><td width="11%" align="center">Batas Bawah</td><td width="13%" align="center">Batas Atas</td><td width="14%" align="center">Basis Borong Laki-laki</td><td width="16%" align="center">Basis Borong Perempuan</td><td width="20%" align="center">Basis Tugas Laki-laki</td><td width="28%" align="center">Basis Tuga Perempuan</td><td width="16%" align="center">
Satuan</td></tr><tr><td width="7%">&nbsp;</td><td width="19%">&nbsp;</td>
<td width="11%">&nbsp;</td>
<td width="13%">&nbsp;</td>
<td width="14%">&nbsp;</td>
<td width="16%">&nbsp;</td>
<td width="20%">&nbsp;</td>
<td width="28%">&nbsp;</td>
<td width="16%">&nbsp;</td>
</tr></table>
<p><font face="Fixedsys">&nbsp;&nbsp;&nbsp; &nbsp;</font></p-->

<?
CLOSE_BOX();
echo close_body();
?>