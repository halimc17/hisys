<?php
    $this->load->model('Mainmenu');
    $this->print_r($this->Mainmenu->selectByPrivilege());
?>

<!-- <ul>
    <li class="mmgr"><a onclick="show_sub_orgChart('grBSG',this);"><i class="fa fa-folder" aria-hidden="true"></i> <b class="elink" id="elBSG" style="height:22px;font:20">BSG: BAKRIE GROUP</b></a>
    <ul id="grBSG"><div id="mainBSG" class="mainultree">
        <li class="mmgr son"><a onclick="show_sub_orgChart('grSAG',this);"><i class="fa fa-folder-open" aria-hidden="true"></i><b class="elink" id="elSAG">1SAG : PT. Agrowiyana</b></a>
            <ul id="grSAG" style=""><div id="mainSAG" class="mainultree">
                <li class="mmgr son"><a onclick="show_sub_orgChart('grABLE',this);"><i class="fa fa-folder-open" aria-hidden="true"></i><b class="elink" id="elABLE">AG61 : PT. AGW Busdev</b></a>
                    <ul id="grABLE" style=""><div id="mainABLE" class="mainultree"></div></ul>
                </li>
                <li class="mmgr son"><a onclick="show_sub_orgChart('grACHO',this);"><i class="fa fa-folder-open" aria-hidden="true"></i><b class="elink" id="elACHO">AG00 : PT. AGW Corporate</b></a>
                    <ul id="grACHO" style=""><div id="mainACHO" class="mainultree"></div></ul>
                </li>
                <li class="mmgr son"><a onclick="show_sub_orgChart('grAEPE',this);"><i class="fa fa-folder-open" aria-hidden="true"></i><b class="elink" id="elAEPE">AG11 : PT. AGW Estate PBSN</b></a>
                    <ul id="grAEPE" style=""><div id="mainAEPE" class="mainultree"></div></ul>
                </li>
                <li class="mmgr son"><a onclick="show_sub_orgChart('grAHRO',this);"><i class="fa fa-folder" aria-hidden="true"></i><b class="elink" id="elAHRO">AG01 : PT. AGW HO</b></a>
                    <ul id="grAHRO" ><div id="mainAHRO" class="mainultree"></div></ul>
                </li>
                <li class="mmgr son"><a onclick="show_sub_orgChart('grAK1E',this);"><i class="fa fa-folder" aria-hidden="true"></i><b class="elink" id="elAK1E">AG92 : PT. AGW KUD Swakarsa</b></a>
                    <ul id="grAK1E" ><div id="mainAK1E" class="mainultree"></div></ul>
                </li>
                <li class="mmgr son"><a onclick="show_sub_orgChart('grAKPE',this);"><i class="fa fa-folder" aria-hidden="true"></i><b class="elink" id="elAKPE">AG93 : PT. AGW KUD Pirtrans</b></a>
                    <ul id="grAKPE" >
                        <div id="mainAKPE" class="mainultree"></div>
                    </ul>
                </li>
                <li class="mmgr son"><a onclick="show_sub_orgChart('grAKSE',this);"><i class="fa fa-folder" aria-hidden="true"></i><b class="elink" id="elAKSE">AG91 : PT. AGW KUD Suka Makmur</b></a>
                    <ul id="grAKSE" ><div id="mainAKSE" class="mainultree"></div></ul>
                </li>
            </ul>
        </li>
    </ul>
</ul> -->

<script>
    function show_sub_orgChart(id,obj)//used in menu settings
{
       if (document.getElementById(id).style.display == 'none') {
               document.getElementById(id).style.display = null;
			   if(obj.getElementsByTagName("i").length > 0){
				   obj.getElementsByTagName("i")[0].classList.remove('fa-folder');
				   obj.getElementsByTagName("i")[0].classList.add('fa-folder-open');
			   }
       }
       else {
               document.getElementById(id).style.display = 'none';
			   if(obj.getElementsByTagName("i").length > 0){
				   obj.getElementsByTagName("i")[0].classList.remove('fa-folder-open');
				   obj.getElementsByTagName("i")[0].classList.add('fa-folder');
			   }
       }

}
</script>