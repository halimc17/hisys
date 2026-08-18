//JS 

//untuk load page nya pake yg pg sesuaikan di slave laod datanya
function getPage(pg){
    pg=document.getElementById('pages');
    pg=pg.options[pg.selectedIndex].value;
    paged=parseFloat(pg)-1;
    loadData(paged);
    // cariBast(pg-1);    
}


function pilih(no){
 
    baik=document.getElementById('baik'+no).checked;
    cukup=document.getElementById('cukup'+no).checked;
    kurang=document.getElementById('kurang'+no).checked;
    if(baik==true){
        document.getElementById('cukup'+no).disabled=true;
        document.getElementById('kurang'+no).disabled=true;
    }else if(cukup==true){
      document.getElementById('baik'+no).disabled=true;
        document.getElementById('kurang'+no).disabled=true;
    }else if(kurang==true){
      document.getElementById('baik'+no).disabled=true;
        document.getElementById('cukup'+no).disabled=true;
    }else if(baik==false){
      document.getElementById('baik'+no).disabled=false;
        document.getElementById('cukup'+no).disabled=false;
        document.getElementById('kurang'+no).disabled=false;
    }else if(cukup==false){
        document.getElementById('baik'+no).disabled=false;
        document.getElementById('kurang'+no).disabled=false;
    }else if(kurang==false){
      document.getElementById('baik'+no).disabled=false;
        document.getElementById('cukup'+no).disabled=false;
      }
}

function choose(){
 
    pendapat1=document.getElementById('pendapat1').checked;
    pendapat2=document.getElementById('pendapat2').checked;
    pendapat3=document.getElementById('pendapat3').checked;
    pendapat4=document.getElementById('pendapat4').checked;
    pendapat5=document.getElementById('pendapat5').checked;
    pendapat6=document.getElementById('pendapat6').checked;
    pendapat7=document.getElementById('pendapat7').checked;
    pendapat8=document.getElementById('pendapat8').checked;

    if(pendapat1==true){
        document.getElementById('pendapat2').disabled=true;
        document.getElementById('pendapat3').disabled=true;
        document.getElementById('pendapat4').disabled=true;
        document.getElementById('pendapat5').disabled=true;
        document.getElementById('pendapat6').disabled=true;
        document.getElementById('pendapat7').disabled=true;
        document.getElementById('pendapat8').disabled=true;
        document.getElementById('lanjutkan').disabled=true;
    }else if(pendapat2==true){
      document.getElementById('pendapat1').disabled=true;
        document.getElementById('pendapat3').disabled=true;
        document.getElementById('pendapat4').disabled=true;
        document.getElementById('pendapat5').disabled=true;
        document.getElementById('pendapat6').disabled=true;
        document.getElementById('pendapat7').disabled=true;
        document.getElementById('pendapat8').disabled=true;
        document.getElementById('lanjutkan').disabled=true;
        }else if(pendapat3==true){
      document.getElementById('pendapat1').disabled=true;
        document.getElementById('pendapat2').disabled=true;
        document.getElementById('pendapat4').disabled=true;
        document.getElementById('pendapat5').disabled=true;
        document.getElementById('pendapat6').disabled=true;
        document.getElementById('pendapat7').disabled=true;
        document.getElementById('pendapat8').disabled=true;
        document.getElementById('lanjutkan').disabled=true;
        }else if(pendapat4==true){
      document.getElementById('pendapat1').disabled=true;
        document.getElementById('pendapat2').disabled=true;
        document.getElementById('pendapat3').disabled=true;
        document.getElementById('pendapat5').disabled=true;
        document.getElementById('pendapat6').disabled=true;
        document.getElementById('pendapat7').disabled=true;
        document.getElementById('pendapat8').disabled=true;
        document.getElementById('lanjutkan').disabled=true;
        }else if(pendapat5==true){
      document.getElementById('pendapat1').disabled=true;
        document.getElementById('pendapat2').disabled=true;
        document.getElementById('pendapat3').disabled=true;
        document.getElementById('pendapat4').disabled=true;
        document.getElementById('pendapat6').disabled=true;
        document.getElementById('pendapat7').disabled=true;
        document.getElementById('pendapat8').disabled=true;
        document.getElementById('lanjutkan').disabled=true;
        }else if(pendapat6==true){
      document.getElementById('pendapat1').disabled=true;
        document.getElementById('pendapat2').disabled=true;
        document.getElementById('pendapat3').disabled=true;
        document.getElementById('pendapat4').disabled=true;
        document.getElementById('pendapat5').disabled=true;
        document.getElementById('pendapat7').disabled=true;
        document.getElementById('pendapat8').disabled=true;
        document.getElementById('lanjutkan').disabled=true;
        }else if(pendapat7==true){
      document.getElementById('pendapat1').disabled=true;
        document.getElementById('pendapat2').disabled=true;
        document.getElementById('pendapat3').disabled=true;
        document.getElementById('pendapat4').disabled=true;
        document.getElementById('pendapat5').disabled=true;
        document.getElementById('pendapat6').disabled=true;
        document.getElementById('pendapat8').disabled=true;
        document.getElementById('lanjutkan').disabled=true;
        }else if(pendapat8==true){
      document.getElementById('pendapat1').disabled=true;
        document.getElementById('pendapat2').disabled=true;
        document.getElementById('pendapat3').disabled=true;
        document.getElementById('pendapat4').disabled=true;
        document.getElementById('pendapat5').disabled=true;
        document.getElementById('pendapat6').disabled=true;
        document.getElementById('pendapat7').disabled=true;
        // document.getElementById('lanjutkan').disabled=true;

    }else if(pendapat1==false){
      document.getElementById('pendapat1').disabled=false;
      document.getElementById('pendapat2').disabled=false;
        document.getElementById('pendapat3').disabled=false;
        document.getElementById('pendapat4').disabled=false;
        document.getElementById('pendapat5').disabled=false;
        document.getElementById('pendapat6').disabled=false;
        document.getElementById('pendapat7').disabled=false;
        document.getElementById('pendapat8').disabled=false;
        document.getElementById('lanjutkan').disabled=false;
        document.getElementById('lanjutkan').value='';
    }
}

function opsi(){
 
    promosi=document.getElementById('promosi').checked;
    jarak=document.getElementById('jarak').checked;
    jamkerja=document.getElementById('jamkerja').checked;
    benefit=document.getElementById('benefit').checked;
    gajibaik=document.getElementById('gajibaik').checked;
    perubahan=document.getElementById('perubahan').checked;

    if(promosi==true){
        document.getElementById('jarak').disabled=true;
        document.getElementById('jamkerja').disabled=true;
        document.getElementById('benefit').disabled=true;
        document.getElementById('gajibaik').disabled=true;
        document.getElementById('perubahan').disabled=true;
    }else if(jarak==true){
      document.getElementById('promosi').disabled=true;
        document.getElementById('jamkerja').disabled=true;
        document.getElementById('benefit').disabled=true;
        document.getElementById('gajibaik').disabled=true;
        document.getElementById('perubahan').disabled=true;
        }else if(jamkerja==true){
      document.getElementById('jarak').disabled=true;
        document.getElementById('promosi').disabled=true;
        document.getElementById('benefit').disabled=true;
        document.getElementById('gajibaik').disabled=true;
        document.getElementById('perubahan').disabled=true;
        }else if(benefit==true){
     document.getElementById('jarak').disabled=true;
        document.getElementById('jamkerja').disabled=true;
        document.getElementById('promosi').disabled=true;
        document.getElementById('gajibaik').disabled=true;
        document.getElementById('perubahan').disabled=true;
        }else if(perubahan==true){
      document.getElementById('jarak').disabled=true;
        document.getElementById('jamkerja').disabled=true;
        document.getElementById('benefit').disabled=true;
        document.getElementById('gajibaik').disabled=true;
        document.getElementById('promosi').disabled=true;
        }else if(gajibaik==true){
      document.getElementById('jarak').disabled=true;
        document.getElementById('jamkerja').disabled=true;
        document.getElementById('benefit').disabled=true;
        document.getElementById('promosi').disabled=true;
        document.getElementById('perubahan').disabled=true;
 
    }else if(promosi==false){
      document.getElementById('promosi').disabled=false;
      document.getElementById('jarak').disabled=false;
        document.getElementById('jamkerja').disabled=false;
        document.getElementById('benefit').disabled=false;
        document.getElementById('gajibaik').disabled=false;
        document.getElementById('perubahan').disabled=false;
        // document.getElementById('pendapat7').disabled=false;
        // document.getElementById('pendapat8').disabled=false;
        // document.getElementById('lanjutkan').disabled=false;
        // document.getElementById('lanjutkan').value='';
    }
}


function pilihan(){
 
    alasan1=document.getElementById('alasan1').checked;
    alasan2=document.getElementById('alasan2').checked;
    alasan3=document.getElementById('alasan3').checked;
    alasan4=document.getElementById('alasan4').checked;
    alasan5=document.getElementById('alasan5').checked;
    alasan6=document.getElementById('alasan6').checked;
    alasan7=document.getElementById('alasan7').checked;
    alasan8=document.getElementById('alasan8').checked;
    alasan9=document.getElementById('alasan9').checked;
    alasan10=document.getElementById('alasan10').checked;
    alasan11=document.getElementById('alasan11').checked;
    alasan12=document.getElementById('alasan12').checked;
    alasan13=document.getElementById('alasan13').checked;
    alasan14=document.getElementById('alasan14').checked;
    alasan15=document.getElementById('alasan15').checked;
    alasan16=document.getElementById('alasan16').checked;
    alasan17=document.getElementById('alasan17').checked;
    alasan18=document.getElementById('alasan18').checked;

    if(alasan1==true){
        document.getElementById('alasan2').disabled=true;
        document.getElementById('alasan3').disabled=true;
        document.getElementById('alasan4').disabled=true;
        document.getElementById('alasan5').disabled=true;
        document.getElementById('alasan6').disabled=true;
        document.getElementById('alasan7').disabled=true;
        document.getElementById('alasan8').disabled=true;
        document.getElementById('alasan9').disabled=true;
        document.getElementById('alasan10').disabled=true;
        document.getElementById('alasan11').disabled=true;
        document.getElementById('alasan12').disabled=true;
        document.getElementById('alasan13').disabled=true;
        document.getElementById('alasan14').disabled=true;
        document.getElementById('alasan15').disabled=true;
        document.getElementById('alasan16').disabled=true;
        document.getElementById('alasan17').disabled=true;
        document.getElementById('alasan18').disabled=true;
        document.getElementById('kesempatantext').disabled=true;
        document.getElementById('lainnya').disabled=true;
    }else if(alasan2==true){
      document.getElementById('alasan1').disabled=true;
        document.getElementById('alasan3').disabled=true;
        document.getElementById('alasan4').disabled=true;
        document.getElementById('alasan5').disabled=true;
        document.getElementById('alasan6').disabled=true;
        document.getElementById('alasan7').disabled=true;
        document.getElementById('alasan8').disabled=true;
        document.getElementById('alasan9').disabled=true;
        document.getElementById('alasan10').disabled=true;
        document.getElementById('alasan11').disabled=true;
        document.getElementById('alasan12').disabled=true;
        document.getElementById('alasan13').disabled=true;
        document.getElementById('alasan14').disabled=true;
        document.getElementById('alasan15').disabled=true;
        document.getElementById('alasan16').disabled=true;
        document.getElementById('alasan17').disabled=true;
        document.getElementById('alasan18').disabled=true;
        document.getElementById('kesempatantext').disabled=true;
        document.getElementById('lainnya').disabled=true;
        }else if(alasan3==true){
      document.getElementById('alasan1').disabled=true;
        document.getElementById('alasan2').disabled=true;
        document.getElementById('alasan4').disabled=true;
        document.getElementById('alasan5').disabled=true;
        document.getElementById('alasan6').disabled=true;
        document.getElementById('alasan7').disabled=true;
        document.getElementById('alasan8').disabled=true;
        document.getElementById('alasan9').disabled=true;
        document.getElementById('alasan10').disabled=true;
        document.getElementById('alasan11').disabled=true;
        document.getElementById('alasan12').disabled=true;
        document.getElementById('alasan13').disabled=true;
        document.getElementById('alasan14').disabled=true;
        document.getElementById('alasan15').disabled=true;
        document.getElementById('alasan16').disabled=true;
        document.getElementById('alasan17').disabled=true;
        document.getElementById('alasan18').disabled=true;
        document.getElementById('kesempatantext').disabled=true;
        document.getElementById('lainnya').disabled=true;
        }else if(alasan4==true){
      document.getElementById('alasan1').disabled=true;
        document.getElementById('alasan2').disabled=true;
        document.getElementById('alasan3').disabled=true;
        document.getElementById('alasan5').disabled=true;
        document.getElementById('alasan6').disabled=true;
        document.getElementById('alasan7').disabled=true;
        document.getElementById('alasan8').disabled=true;
        document.getElementById('alasan9').disabled=true;
        document.getElementById('alasan10').disabled=true;
        document.getElementById('alasan11').disabled=true;
        document.getElementById('alasan12').disabled=true;
        document.getElementById('alasan13').disabled=true;
        document.getElementById('alasan14').disabled=true;
        document.getElementById('alasan15').disabled=true;
        document.getElementById('alasan16').disabled=true;
        document.getElementById('alasan17').disabled=true;
        document.getElementById('alasan18').disabled=true;
        document.getElementById('kesempatantext').disabled=true;
        document.getElementById('lainnya').disabled=true;
        }else if(alasan5==true){
      document.getElementById('alasan1').disabled=true;
        document.getElementById('alasan2').disabled=true;
        document.getElementById('alasan3').disabled=true;
        document.getElementById('alasan4').disabled=true;
        document.getElementById('alasan6').disabled=true;
        document.getElementById('alasan7').disabled=true;
        document.getElementById('alasan8').disabled=true;
        document.getElementById('alasan9').disabled=true;
        document.getElementById('alasan10').disabled=true;
        document.getElementById('alasan11').disabled=true;
        document.getElementById('alasan12').disabled=true;
        document.getElementById('alasan13').disabled=true;
        document.getElementById('alasan14').disabled=true;
        document.getElementById('alasan15').disabled=true;
        document.getElementById('alasan16').disabled=true;
        document.getElementById('alasan17').disabled=true;
        document.getElementById('alasan18').disabled=true;
        document.getElementById('kesempatantext').disabled=true;
        document.getElementById('lainnya').disabled=true;
        }else if(alasan6==true){
      document.getElementById('alasan1').disabled=true;
        document.getElementById('alasan2').disabled=true;
        document.getElementById('alasan3').disabled=true;
        document.getElementById('alasan4').disabled=true;
        document.getElementById('alasan5').disabled=true;
        document.getElementById('alasan7').disabled=true;
        document.getElementById('alasan8').disabled=true;
        document.getElementById('alasan9').disabled=true;
        document.getElementById('alasan10').disabled=true;
        document.getElementById('alasan11').disabled=true;
        document.getElementById('alasan12').disabled=true;
        document.getElementById('alasan13').disabled=true;
        document.getElementById('alasan14').disabled=true;
        document.getElementById('alasan15').disabled=true;
        document.getElementById('alasan16').disabled=true;
        document.getElementById('alasan17').disabled=true;
        document.getElementById('alasan18').disabled=true;
        document.getElementById('kesempatantext').disabled=true;
        document.getElementById('lainnya').disabled=true;
        }else if(alasan7==true){
      document.getElementById('alasan1').disabled=true;
        document.getElementById('alasan2').disabled=true;
        document.getElementById('alasan3').disabled=true;
        document.getElementById('alasan4').disabled=true;
        document.getElementById('alasan5').disabled=true;
        document.getElementById('alasan6').disabled=true;
        document.getElementById('alasan8').disabled=true;
        document.getElementById('alasan9').disabled=true;
        document.getElementById('alasan10').disabled=true;
        document.getElementById('alasan11').disabled=true;
        document.getElementById('alasan12').disabled=true;
        document.getElementById('alasan13').disabled=true;
        document.getElementById('alasan14').disabled=true;
        document.getElementById('alasan15').disabled=true;
        document.getElementById('alasan16').disabled=true;
        document.getElementById('alasan17').disabled=true;
        document.getElementById('alasan18').disabled=true;
        document.getElementById('kesempatantext').disabled=true;
        document.getElementById('lainnya').disabled=true;
        }else if(alasan8==true){
      document.getElementById('alasan1').disabled=true;
        document.getElementById('alasan2').disabled=true;
        document.getElementById('alasan3').disabled=true;
        document.getElementById('alasan4').disabled=true;
        document.getElementById('alasan5').disabled=true;
        document.getElementById('alasan6').disabled=true;
        document.getElementById('alasan7').disabled=true;
        document.getElementById('alasan9').disabled=true;
        document.getElementById('alasan10').disabled=true;
        document.getElementById('alasan11').disabled=true;
        document.getElementById('alasan12').disabled=true;
        document.getElementById('alasan13').disabled=true;
        document.getElementById('alasan14').disabled=true;
        document.getElementById('alasan15').disabled=true;
        document.getElementById('alasan16').disabled=true;
        document.getElementById('alasan17').disabled=true;
        document.getElementById('alasan18').disabled=true;
        document.getElementById('kesempatantext').disabled=true;
        document.getElementById('lainnya').disabled=true;
        }else if(alasan9==true){
      document.getElementById('alasan1').disabled=true;
        document.getElementById('alasan2').disabled=true;
        document.getElementById('alasan3').disabled=true;
        document.getElementById('alasan4').disabled=true;
        document.getElementById('alasan5').disabled=true;
        document.getElementById('alasan6').disabled=true;
        document.getElementById('alasan7').disabled=true;
        document.getElementById('alasan8').disabled=true;
        document.getElementById('alasan10').disabled=true;
        document.getElementById('alasan11').disabled=true;
        document.getElementById('alasan12').disabled=true;
        document.getElementById('alasan13').disabled=true;
        document.getElementById('alasan14').disabled=true;
        document.getElementById('alasan15').disabled=true;
        document.getElementById('alasan16').disabled=true;
        document.getElementById('alasan17').disabled=true;
        document.getElementById('alasan18').disabled=true;
        document.getElementById('kesempatantext').disabled=true;
        document.getElementById('lainnya').disabled=true;
         }else if(alasan10==true){
      document.getElementById('alasan1').disabled=true;
        document.getElementById('alasan2').disabled=true;
        document.getElementById('alasan3').disabled=true;
        document.getElementById('alasan4').disabled=true;
        document.getElementById('alasan5').disabled=true;
        document.getElementById('alasan6').disabled=true;
        document.getElementById('alasan7').disabled=true;
        document.getElementById('alasan8').disabled=true;
        document.getElementById('alasan9').disabled=true;
        document.getElementById('alasan11').disabled=true;
        document.getElementById('alasan12').disabled=true;
        document.getElementById('alasan13').disabled=true;
        document.getElementById('alasan14').disabled=true;
        document.getElementById('alasan15').disabled=true;
        document.getElementById('alasan16').disabled=true;
        document.getElementById('alasan17').disabled=true;
        document.getElementById('alasan18').disabled=true;
        document.getElementById('kesempatantext').disabled=true;
        document.getElementById('lainnya').disabled=true;
         }else if(alasan11==true){
      document.getElementById('alasan1').disabled=true;
        document.getElementById('alasan2').disabled=true;
        document.getElementById('alasan3').disabled=true;
        document.getElementById('alasan4').disabled=true;
        document.getElementById('alasan5').disabled=true;
        document.getElementById('alasan6').disabled=true;
        document.getElementById('alasan7').disabled=true;
        document.getElementById('alasan8').disabled=true;
        document.getElementById('alasan9').disabled=true;
        document.getElementById('alasan10').disabled=true;
        document.getElementById('alasan12').disabled=true;
        document.getElementById('alasan13').disabled=true;
        document.getElementById('alasan14').disabled=true;
        document.getElementById('alasan15').disabled=true;
        document.getElementById('alasan16').disabled=true;
        document.getElementById('alasan17').disabled=true;
        document.getElementById('alasan18').disabled=true;
        document.getElementById('kesempatantext').disabled=true;
        document.getElementById('lainnya').disabled=true;
         }else if(alasan12==true){
      document.getElementById('alasan1').disabled=true;
        document.getElementById('alasan2').disabled=true;
        document.getElementById('alasan3').disabled=true;
        document.getElementById('alasan4').disabled=true;
        document.getElementById('alasan5').disabled=true;
        document.getElementById('alasan6').disabled=true;
        document.getElementById('alasan7').disabled=true;
        document.getElementById('alasan8').disabled=true;
        document.getElementById('alasan9').disabled=true;
        document.getElementById('alasan10').disabled=true;
        document.getElementById('alasan11').disabled=true;
        document.getElementById('alasan13').disabled=true;
        document.getElementById('alasan14').disabled=true;
        document.getElementById('alasan15').disabled=true;
        document.getElementById('alasan16').disabled=true;
        document.getElementById('alasan17').disabled=true;
        document.getElementById('alasan18').disabled=true;
        document.getElementById('kesempatantext').disabled=true;
        document.getElementById('lainnya').disabled=true;
        }else if(alasan13==true){
      document.getElementById('alasan1').disabled=true;
        document.getElementById('alasan2').disabled=true;
        document.getElementById('alasan3').disabled=true;
        document.getElementById('alasan4').disabled=true;
        document.getElementById('alasan5').disabled=true;
        document.getElementById('alasan6').disabled=true;
        document.getElementById('alasan7').disabled=true;
        document.getElementById('alasan8').disabled=true;
        document.getElementById('alasan9').disabled=true;
        document.getElementById('alasan10').disabled=true;
        document.getElementById('alasan11').disabled=true;
        document.getElementById('alasan12').disabled=true;
        document.getElementById('alasan14').disabled=true;
        document.getElementById('alasan15').disabled=true;
        document.getElementById('alasan16').disabled=true;
        document.getElementById('alasan17').disabled=true;
        document.getElementById('alasan18').disabled=true;
        document.getElementById('kesempatantext').disabled=true;
        document.getElementById('lainnya').disabled=true
         }else if(alasan14==true){
      document.getElementById('alasan1').disabled=true;
        document.getElementById('alasan2').disabled=true;
        document.getElementById('alasan3').disabled=true;
        document.getElementById('alasan4').disabled=true;
        document.getElementById('alasan5').disabled=true;
        document.getElementById('alasan6').disabled=true;
        document.getElementById('alasan7').disabled=true;
        document.getElementById('alasan8').disabled=true;
        document.getElementById('alasan9').disabled=true;
        document.getElementById('alasan10').disabled=true;
        document.getElementById('alasan11').disabled=true;
        document.getElementById('alasan12').disabled=true;
        document.getElementById('alasan13').disabled=true;
        document.getElementById('alasan15').disabled=true;
        document.getElementById('alasan16').disabled=true;
        document.getElementById('alasan17').disabled=true;
        document.getElementById('alasan18').disabled=true;
        document.getElementById('kesempatantext').disabled=true;
        document.getElementById('lainnya').disabled=true
        }else if(alasan15==true){
      document.getElementById('alasan1').disabled=true;
        document.getElementById('alasan2').disabled=true;
        document.getElementById('alasan3').disabled=true;
        document.getElementById('alasan4').disabled=true;
        document.getElementById('alasan5').disabled=true;
        document.getElementById('alasan6').disabled=true;
        document.getElementById('alasan7').disabled=true;
        document.getElementById('alasan8').disabled=true;
        document.getElementById('alasan9').disabled=true;
        document.getElementById('alasan10').disabled=true;
        document.getElementById('alasan11').disabled=true;
        document.getElementById('alasan12').disabled=true;
        document.getElementById('alasan13').disabled=true;
        document.getElementById('alasan14').disabled=true;
        document.getElementById('alasan16').disabled=true;
        document.getElementById('alasan17').disabled=true;
        document.getElementById('alasan18').disabled=true;
        document.getElementById('kesempatantext').disabled=true;
        document.getElementById('lainnya').disabled=true
         }else if(alasan16==true){
      document.getElementById('alasan1').disabled=true;
        document.getElementById('alasan2').disabled=true;
        document.getElementById('alasan3').disabled=true;
        document.getElementById('alasan4').disabled=true;
        document.getElementById('alasan5').disabled=true;
        document.getElementById('alasan6').disabled=true;
        document.getElementById('alasan7').disabled=true;
        document.getElementById('alasan8').disabled=true;
        document.getElementById('alasan9').disabled=true;
        document.getElementById('alasan10').disabled=true;
        document.getElementById('alasan11').disabled=true;
        document.getElementById('alasan12').disabled=true;
        document.getElementById('alasan13').disabled=true;
        document.getElementById('alasan14').disabled=true;
        document.getElementById('alasan15').disabled=true;
        document.getElementById('alasan17').disabled=true;
        document.getElementById('alasan18').disabled=true;
        document.getElementById('kesempatantext').disabled=true;
        document.getElementById('lainnya').disabled=true
        document.getElementById('promosi').disabled=false;
        document.getElementById('jarak').disabled=false
        document.getElementById('jamkerja').disabled=false;
        document.getElementById('benefit').disabled=false
        document.getElementById('gajibaik').disabled=false;
        document.getElementById('perubahan').disabled=false
         }else if(alasan17==true){
      document.getElementById('alasan1').disabled=true;
        document.getElementById('alasan2').disabled=true;
        document.getElementById('alasan3').disabled=true;
        document.getElementById('alasan4').disabled=true;
        document.getElementById('alasan5').disabled=true;
        document.getElementById('alasan6').disabled=true;
        document.getElementById('alasan7').disabled=true;
        document.getElementById('alasan8').disabled=true;
        document.getElementById('alasan9').disabled=true;
        document.getElementById('alasan10').disabled=true;
        document.getElementById('alasan11').disabled=true;
        document.getElementById('alasan12').disabled=true;
        document.getElementById('alasan13').disabled=true;
        document.getElementById('alasan14').disabled=true;
        document.getElementById('alasan15').disabled=true;
        document.getElementById('alasan16').disabled=true;
        document.getElementById('alasan18').disabled=true;
        document.getElementById('kesempatantext').disabled=false;
        document.getElementById('lainnya').disabled=true
         }else if(alasan18==true){
      document.getElementById('alasan1').disabled=true;
        document.getElementById('alasan2').disabled=true;
        document.getElementById('alasan3').disabled=true;
        document.getElementById('alasan4').disabled=true;
        document.getElementById('alasan5').disabled=true;
        document.getElementById('alasan6').disabled=true;
        document.getElementById('alasan7').disabled=true;
        document.getElementById('alasan8').disabled=true;
        document.getElementById('alasan9').disabled=true;
        document.getElementById('alasan10').disabled=true;
        document.getElementById('alasan11').disabled=true;
        document.getElementById('alasan12').disabled=true;
        document.getElementById('alasan13').disabled=true;
        document.getElementById('alasan14').disabled=true;
        document.getElementById('alasan15').disabled=true;
        document.getElementById('alasan16').disabled=true;
        document.getElementById('alasan17').disabled=true;
        document.getElementById('kesempatantext').disabled=true;
        document.getElementById('lainnya').disabled=false
         
        // document.getElementById('lanjutkan').disabled=true;

    }else if(alasan1==false){
      document.getElementById('alasan1').disabled=false;
      document.getElementById('alasan2').disabled=false;
        document.getElementById('alasan3').disabled=false;
        document.getElementById('alasan4').disabled=false;
        document.getElementById('alasan5').disabled=false;
        document.getElementById('alasan6').disabled=false;
        document.getElementById('alasan7').disabled=false;
        document.getElementById('alasan8').disabled=false;
        document.getElementById('alasan9').disabled=false;
        document.getElementById('alasan10').disabled=false;
        document.getElementById('alasan11').disabled=false;
        document.getElementById('alasan12').disabled=false;
        document.getElementById('alasan13').disabled=false;
        document.getElementById('alasan14').disabled=false;
        document.getElementById('alasan15').disabled=false;
        document.getElementById('alasan16').disabled=false;
        document.getElementById('alasan17').disabled=false;
        document.getElementById('alasan18').disabled=false;
        document.getElementById('kesempatantext').disabled=false;
        document.getElementById('lainnya').disabled=false;
        document.getElementById('kesempatantext').value='';
        document.getElementById('lainnya').value='';
        document.getElementById('promosi').disabled=true;
      document.getElementById('jarak').disabled=true;
        document.getElementById('jamkerja').disabled=true;
        document.getElementById('benefit').disabled=true;
        document.getElementById('gajibaik').disabled=true;
        document.getElementById('perubahan').disabled=true;
        document.getElementById('promosi').value='';
        document.getElementById('jarak').value='';
        document.getElementById('benefit').value='';
        document.getElementById('gajibaik').value='';
        document.getElementById('perubahan').value='';
        document.getElementById('jamkerja').value='';

    }
}


function getData()
{  
    nama=document.getElementById('nama').options[document.getElementById('nama').selectedIndex].value;
    // nama=document.getElementById('nama').value;
    // tanggal=document.getElementById('tanggal').value;
    param='nama='+nama+'&method=getData';
    tujuan='sdm_slave_exitinterview.php';
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
                                                    arr=con.responseText.split("###");
                                                    document.getElementById('departemen').value=arr[0];
                                                    document.getElementById('tglmasuk').value=arr[1];
                                                    document.getElementById('jabatan').value=arr[2];
                                                    document.getElementById('email').value=arr[3];
                                                    document.getElementById('nohp').value=arr[4];
                                                    document.getElementById('tglkeluar').value=arr[5];
                                                    document.getElementById('perusahaan').value=arr[6];

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
    nama=document.getElementById('nama').value;
    
     baik1=document.getElementById('baik1');
    cukup1=document.getElementById('cukup1');
     kurang1=document.getElementById('kurang1');

    if(baik1.checked==true){
      peranatasan1=3;
    } else if(cukup1.checked==true) {
      peranatasan1=2;
    } else if(kurang1.checked==true) {
      peranatasan1=1;
    } 
    // alert(peranatasan1);
    baik2=document.getElementById('baik2');
    cukup2=document.getElementById('cukup2');
     kurang2=document.getElementById('kurang2');

    if(baik2.checked==true){
      peranatasan2=3;
    } else if(cukup2.checked==true) {
      peranatasan2=2;
    } else if(kurang2.checked==true) {
      peranatasan2=1;
    }

    baik3=document.getElementById('baik3');
    cukup3=document.getElementById('cukup3');
     kurang3=document.getElementById('kurang3');

    if(baik3.checked==true){
      peranatasan3=3;
    } else if(cukup3.checked==true) {
      peranatasan3=2;
    } else if(kurang3.checked==true) {
      peranatasan3=1;
    }

    baik4=document.getElementById('baik4');
    cukup4=document.getElementById('cukup4');
     kurang4=document.getElementById('kurang4');

    if(baik4.checked==true){
      peranatasan4=3;
    } else if(cukup4.checked==true) {
      peranatasan4=2;
    } else if(kurang4.checked==true) {
      peranatasan4=1;
    }

    baik5=document.getElementById('baik5');
    cukup5=document.getElementById('cukup5');
     kurang5=document.getElementById('kurang5');

    if(baik5.checked==true){
      peranatasan5=3;
    } else if(cukup5.checked==true) {
      peranatasan5=2;
    } else if(kurang5.checked==true) {
      peranatasan5=1;
    }

    baik6=document.getElementById('baik6');
    cukup6=document.getElementById('cukup6');
     kurang6=document.getElementById('kurang6');

    if(baik6.checked==true){
      peranatasan6=3;
    } else if(cukup6.checked==true) {
      peranatasan6=2;
    } else if(kurang6.checked==true) {
      peranatasan6=1;
    }

    baik7=document.getElementById('baik7');
    cukup7=document.getElementById('cukup7');
     kurang7=document.getElementById('kurang7');

    if(baik7.checked==true){
      peranatasan7=3;
    } else if(cukup7.checked==true) {
      peranatasan7=2;
    } else if(kurang7.checked==true) {
      peranatasan7=1;
    }

    baik8=document.getElementById('baik8');
    cukup8=document.getElementById('cukup8');
     kurang8=document.getElementById('kurang8');

    if(baik8.checked==true){
      peranatasan8=3;
    } else if(cukup8.checked==true) {
      peranatasan8=2;
    } else if(kurang8.checked==true) {
      peranatasan8=1;
    }

    baik8=document.getElementById('baik8');
    cukup8=document.getElementById('cukup8');
     kurang8=document.getElementById('kurang8');

    if(baik8.checked==true){
      peranatasan8=3;
    } else if(cukup8.checked==true) {
      peranatasan8=2;
    } else if(kurang8.checked==true) {
      peranatasan8=1;
    }

    baik9=document.getElementById('baik9');
    cukup9=document.getElementById('cukup9');
     kurang9=document.getElementById('kurang9');

    if(baik9.checked==true){
      peranatasan9=3;
    } else if(cukup9.checked==true) {
      peranatasan9=2;
    } else if(kurang9.checked==true) {
      peranatasan9=1;
    }

    baik10=document.getElementById('baik10');
    cukup10=document.getElementById('cukup10');
     kurang10=document.getElementById('kurang10');

    if(baik10.checked==true){
      peranatasan10=3;
    } else if(cukup10.checked==true) {
      peranatasan10=2;
    } else if(kurang10.checked==true) {
      peranatasan10=1;
    }

    baik11=document.getElementById('baik11');
    cukup11=document.getElementById('cukup11');
     kurang11=document.getElementById('kurang11');

    if(baik11.checked==true){
      peranatasan11=3;
    } else if(cukup11.checked==true) {
      peranatasan11=2;
    } else if(kurang11.checked==true) {
      peranatasan11=1;
    }

    baik12=document.getElementById('baik12');
    cukup12=document.getElementById('cukup12');
     kurang12=document.getElementById('kurang12');

    if(baik12.checked==true){
      penilaian1=3;
    } else if(cukup12.checked==true) {
      penilaian1=2;
    } else if(kurang12.checked==true) {
      penilaian1=1;
    }

    baik13=document.getElementById('baik13');
    cukup13=document.getElementById('cukup13');
     kurang13=document.getElementById('kurang13');

    if(baik13.checked==true){
      penilaian2=3;
    } else if(cukup13.checked==true) {
      penilaian2=2;
    } else if(kurang13.checked==true) {
      penilaian2=1;
    }

    baik14=document.getElementById('baik14');
    cukup14=document.getElementById('cukup14');
     kurang14=document.getElementById('kurang14');

    if(baik14.checked==true){
      penilaian3=3;
    } else if(cukup14.checked==true) {
      penilaian3=2;
    } else if(kurang14.checked==true) {
      penilaian3=1;
    }

    baik15=document.getElementById('baik15');
    cukup15=document.getElementById('cukup15');
     kurang15=document.getElementById('kurang15');

    if(baik15.checked==true){
      penilaian4=3;
    } else if(cukup15.checked==true) {
      penilaian4=2;
    } else if(kurang15.checked==true) {
      penilaian4=1;
    }

    baik16=document.getElementById('baik16');
    cukup16=document.getElementById('cukup16');
     kurang16=document.getElementById('kurang16');

    if(baik16.checked==true){
      penilaian5=3;
    } else if(cukup16.checked==true) {
      penilaian5=2;
    } else if(kurang16.checked==true) {
      penilaian5=1;
    }

    baik17=document.getElementById('baik17');
    cukup17=document.getElementById('cukup17');
     kurang17=document.getElementById('kurang17');

    if(baik17.checked==true){
      penilaian6=3;
    } else if(cukup17.checked==true) {
      penilaian6=2;
    } else if(kurang17.checked==true) {
      penilaian6=1;
    }

    baik18=document.getElementById('baik18');
    cukup18=document.getElementById('cukup12');
     kurang18=document.getElementById('kurang18');

    if(baik18.checked==true){
      penilaian7=3;
    } else if(cukup18.checked==true) {
      penilaian7=2;
    } else if(kurang18.checked==true) {
      penilaian7=1;
    }

    baik19=document.getElementById('baik19');
    cukup19=document.getElementById('cukup19');
     kurang19=document.getElementById('kurang19');

    if(baik19.checked==true){
      penilaian8=3;
    } else if(cukup19.checked==true) {
      penilaian8=2;
    } else if(kurang19.checked==true) {
      penilaian8=1;
    }

    baik20=document.getElementById('baik20');
    cukup20=document.getElementById('cukup20');
     kurang20=document.getElementById('kurang20');

    if(baik20.checked==true)
      gaji1=3;
     else if(cukup20.checked==true) 
      gaji1=2;
     else if(kurang20.checked==true) 
      gaji1=1;
    

    baik21=document.getElementById('baik21');
    cukup21=document.getElementById('cukup21');
     kurang21=document.getElementById('kurang21');

    if(baik21.checked==true)
      gaji2=3;
     else if(cukup21.checked==true) 
      gaji2=2;
    else if(kurang21.checked==true) 
      gaji2=1;
    

    baik22=document.getElementById('baik22');
    cukup22=document.getElementById('cukup22');
     kurang22=document.getElementById('kurang22');

    if(baik22.checked==true)
      gaji3=3;
     else if(cukup22.checked==true) 
      gaji3=2;
    else if(kurang22.checked==true) 
      gaji3=1;
    

    baik23=document.getElementById('baik23');
    cukup23=document.getElementById('cukup23');
     kurang23=document.getElementById('kurang23');

    if(baik23.checked==true)
      gaji4=3;
     else if(cukup23.checked==true) 
      gaji4=2;
     else if(kurang23.checked==true) 
      gaji4=1;
    

    baik24=document.getElementById('baik24');
    cukup24=document.getElementById('cukup24');
     kurang24=document.getElementById('kurang24');

    // if(baik24.checked==true){
    //   gaji5=3;
    // } else if(cukup24.checked==true) {
    //   gaji5=2;
    // } else if(kurang24.checked==true) {
    //   gaji5=1;
    // }

    yaapa=document.getElementById('yaapa').value;
    komenlain=document.getElementById('komenlain').value;

    alasan1=document.getElementById('alasan1');
    alasan2=document.getElementById('alasan2');
    alasan3=document.getElementById('alasan3');
    alasan4=document.getElementById('alasan4');
    alasan5=document.getElementById('alasan5');
    alasan6=document.getElementById('alasan6');
    alasan7=document.getElementById('alasan7');
    alasan8=document.getElementById('alasan8');
    alasan9=document.getElementById('alasan9');
    alasan10=document.getElementById('alasan10');
    alasan11=document.getElementById('alasan11');
    alasan12=document.getElementById('alasan12');
    alasan13=document.getElementById('alasan13');
    alasan14=document.getElementById('alasan14');
    alasan15=document.getElementById('alasan15');
    alasan16=document.getElementById('alasan16');
    alasan17=document.getElementById('alasan17');
    alasan18=document.getElementById('alasan18');
    promosi=document.getElementById('promosi');
    jarak=document.getElementById('jarak');
    jamkerja=document.getElementById('jamkerja');
    benefit=document.getElementById('benefit');
    gajibaik=document.getElementById('gajibaik');
    perubahan=document.getElementById('perubahan');
    // alasan3=document.getElementById('kurang25');

    if(alasan1.checked==true){
      alasankeluar='Kondisi Kerja';
    } else if(alasan2.checked==true) {
      alasankeluar='Kompensasi';
    } else if(alasan3.checked==true) {
      alasankeluar='Gangguan Kerja';
    }else if(alasan4.checked==true) {
      alasankeluar='Diskriminasi';
    } else if(alasan5.checked==true) {
      alasankeluar='Alasan Kesehatan';
    }else if(alasan6.checked==true) {
      alasankeluar='Dekat Dengan Keluarga';
    } else if(alasan7.checked==true) {
      alasankeluar='Mengikuti Suami';
    }else if(alasan8.checked==true) {
      alasankeluar='Nilai Buku Habis';
    }else if(alasan9.checked==true) {
      alasankeluar='Pemutusan Hubungan Kerja';
    } else if(alasan10.checked==true) {
      alasankeluar='Melanjutkan Pendidikan';
    }else if(alasan11.checked==true) {
      alasankeluar='Alasan Pribadi';
    } else if(alasan12.checked==true) {
      alasankeluar='Ketidakpastian atas kelanjutan pekerjaan';
    }else if(alasan13.checked==true) {
      alasankeluar='Kurangya pengakuan/penghargaan';
    } else if(alasan14.checked==true) {
      alasankeluar='Kurangnya tantangan dan peluang untuk maju';
    }else if(alasan15.checked==true) {
      alasankeluar='Terlalu banyak tekanan untuk memenuhi target';
    }else if(alasan17.checked==true) {
      alasankeluar='Kesempatan Karir';
    }else if(alasan18.checked==true) {
      alasankeluar='Alasan Lainnya';
    }else if(promosi.checked==true) {
      alasankeluar='Promosi';
    }else if(jarak.checked==true) {
      alasankeluar='Jarak Dari Kantor';
    }else if(jamkerja.checked==true) {
      alasankeluar='Jam Kerja';
    }else if(benefit.checked==true) {
      alasankeluar='Benefit Lebih Baik';
    }else if(benefit.checked==true) {
      alasankeluar='Gaji Lebih Baik';
    }else if(perubahan.checked==true) {
      alasankeluar='Perubahan Karir';
    }

    pendapat1=document.getElementById('pendapat1');
    pendapat2=document.getElementById('pendapat2');
    pendapat3=document.getElementById('pendapat3');
    pendapat4=document.getElementById('pendapat4');
    pendapat5=document.getElementById('pendapat5');
    pendapat6=document.getElementById('pendapat6');
    pendapat7=document.getElementById('pendapat7');
    pendapat8=document.getElementById('pendapat8');
    // pendapat3=document.getElementById('kurang25');

    if(pendapat1.checked==true){
      pendapat='Beban pekerjaan terlalu berat/banyak';
    } else if(pendapat2.checked==true) {
      pendapat='Beban kerja Bervariasi, tapi masih teratasi';
    } else if(pendapat3.checked==true) {
      pendapat='Beban kerja cukup Baik';
    }else if(pendapat4.checked==true) {
      pendapat='Beban pekerjaan cukup ringan/terlalu banyak menganggur';
    } else if(pendapat5.checked==true) {
      pendapat='Pekerjaan terlalu rutin, sehingga membosankan';
    }else if(pendapat6.checked==true) {
      pendapat='Tugas-tugas yang diberikan tidak jelas';
    } else if(pendapat7.checked==true) {
      pendapat='Pekerjaan tidak sesuai dengan minat';
    }else if(pendapat8.checked==true) {
      pendapat='Lainnya';
    }

    umpanbalik=document.getElementById('umpanbalik').value;
    diskusi=document.getElementById('diskusi').value;
    minat=document.getElementById('minat').value;
    suka=document.getElementById('suka').value;
    kurangsuka=document.getElementById('kurangsuka').value;
    kemajuan=document.getElementById('kemajuan').value;
    komentar=document.getElementById('komentar').value;
    invent1=document.getElementById('invent1').value;
    invent2=document.getElementById('invent2').value;
    invent3=document.getElementById('invent3').value;
    invent4=document.getElementById('invent4').value;
    keterangan=document.getElementById('keterangan').value;
    // invent4=document.getElementById('invent4').value;

   // namapemilik=document.getElementById('pemilik').value;

    method=document.getElementById('method').value;
          
    // met1=document.getElementById('method').value;

    // if(nama=='' || umpanbalik=='' || diskusi=='' || minat=='' || suka=='' || kurangsuka=='' || kemajuan=='' || komentar=='' || invent1=='' || invent2=='' || invent3=='' || invent4=='' || keterangan=='')
    // {
    //         alert('Field Was Empty');
    //         return;
    // }

    param='nama='+nama+'&alasankeluar='+alasankeluar+'&peranatasan1='+peranatasan1+'&peranatasan2='+peranatasan2+'&peranatasan3='+peranatasan3+'&peranatasan4='+peranatasan4+'&peranatasan5='+peranatasan5+'&peranatasan6='+peranatasan6+'&peranatasan7='+peranatasan7+'&peranatasan8='+peranatasan8+'&peranatasan9='+peranatasan9+'&peranatasan10='+peranatasan10+'&peranatasan11='+peranatasan11+'&penilaian1='+penilaian1+'&penilaian2='+penilaian2+'&penilaian3='+penilaian3+'&penilaian4='+penilaian4+'&penilaian5='+penilaian5+'&penilaian6='+penilaian6+'&penilaian7='+penilaian7+'&penilaian8='+penilaian8+'&pendapat='+pendapat+'&gaji1='+gaji1+'&gaji2='+gaji2+'&gaji3='+gaji3+'&gaji4='+gaji4+'&yaapa='+yaapa+'&komenlain='+komenlain+'&umpanbalik='+umpanbalik+'&diskusi='+diskusi+'&minat='+minat+'&suka='+suka+'&kurangsuka='+kurangsuka+'&kemajuan='+kemajuan+'&komentar='+komentar+'&method='+method+'&invent1='+invent1+'&invent2='+invent2+'&invent3='+invent3+'&invent4='+invent4+'&keterangan='+keterangan;

     alert('masukkk');
     alert(param);

    // param+='&penilaian1='+penilaian1+'&penilaian2='+penilaian2+'&penilaian3='+penilaian3+'&penilaian4='+penilaian4+'&penilaian5='+penilaian5+'&penilaian6='+penilaian6+'&penilaian7='+penilaian7+'&penilaian8='+penilaian8+'&pendapat='+pendapat;
    // param+='&gaji1='+gaji1+'&gaji2='+gaji2+'&gaji3='+gaji3+'&gaji4='+gaji4+'&yaapa='+yaapa+'&komenlain='+komenlain+'&umpanbalik='+umpanbalik+'&diskusi='+diskusi+'&minat='+minat+'&suka='+suka+'&kurangsuka='+kurangsuka+'&kemajuan='+kemajuan+'&komentar='+komentar;
    // param+='&invent1='+invent1+'&invent2='+invent2+'&invent3='+invent3+'&invent4='+invent4+'&keterangan='+keterangan;
    // param+='&method='+method;
    // alert(param);

    tujuan='sdm_slave_exitinterview.php';
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
                            cancel();                            
                            loadData(0);
                        }
                    }
                    else {
                        busy_off();
                        error_catch(con.status);
                    }
              } 
     }
}

function cancel(no){
    document.getElementById('nama').value='';
    document.getElementById('tglmasuk').value='';
    document.getElementById('email').value='';
    document.getElementById('tglkeluar').value='';
    document.getElementById('departemen').value='';
    document.getElementById('jabatan').value='';
    document.getElementById('nohp').value='';
    document.getElementById('perusahaan').value='';

    document.getElementById('alasan1').checked=false;
    document.getElementById('alasan1').disabled=false;
    document.getElementById('alasan2').checked=false;
    document.getElementById('alasan2').disabled=false;
    document.getElementById('alasan3').checked=false;
    document.getElementById('alasan4').checked=false;
    document.getElementById('alasan5').checked=false;
    document.getElementById('alasan6').checked=false;
    document.getElementById('alasan7').checked=false;
    document.getElementById('alasan8').checked=false;
    document.getElementById('alasan9').checked=false;
    document.getElementById('alasan10').checked=false;
    document.getElementById('alasan11').checked=false;
    document.getElementById('alasan12').checked=false;
    document.getElementById('alasan13').checked=false;
    document.getElementById('alasan14').checked=false;
    document.getElementById('alasan15').checked=false;
    document.getElementById('alasan16').checked=false;
    document.getElementById('alasan17').checked=false;
    document.getElementById('alasan18').checked=false;

    document.getElementById('alasan3').disabled=false;
    document.getElementById('alasan4').disabled=false;
    document.getElementById('alasan5').disabled=false;
    document.getElementById('alasan6').disabled=false;
    document.getElementById('alasan7').disabled=false;
    document.getElementById('alasan8').disabled=false;
    document.getElementById('alasan9').disabled=false;
    document.getElementById('alasan10').disabled=false;
    document.getElementById('alasan11').disabled=false;
    document.getElementById('alasan12').disabled=false;
    document.getElementById('alasan13').disabled=false;
    document.getElementById('alasan14').disabled=false;
    document.getElementById('alasan15').disabled=false;
    document.getElementById('alasan16').disabled=false;
    document.getElementById('alasan17').disabled=false;
    document.getElementById('alasan18').disabled=false;
	
    document.getElementById('kesempatantext').value='';
    document.getElementById('lainnya').value='';
    document.getElementById('lanjutkan').value='';
    document.getElementById('yaapa').value='';
    document.getElementById('komenlain').value='';
    document.getElementById('umpanbalik').value='';
    document.getElementById('diskusi').value='';
    document.getElementById('minat').value='';
    document.getElementById('suka').value='';
    document.getElementById('kurangsuka').value='';
    document.getElementById('kemajuan').value='';
    document.getElementById('komentar').value='';
    document.getElementById('invent1').value='';
    document.getElementById('invent2').value='';
    document.getElementById('invent3').value='';
    document.getElementById('invent4').value='';
    document.getElementById('keterangan').value='';

    document.getElementById('promosi').checked=false;
    document.getElementById('jarak').checked=false;
    document.getElementById('jamkerja').checked=false;
    document.getElementById('benefit').checked=false;
    document.getElementById('gajibaik').checked=false;
    document.getElementById('perubahan').checked=false;
    document.getElementById('promosi').disabled=true;
    document.getElementById('jarak').disabled=true;
    document.getElementById('jamkerja').disabled=true;
    document.getElementById('benefit').disabled=true;
    document.getElementById('gajibaik').disabled=true;
    document.getElementById('perubahan').disabled=true;

    document.getElementById('baik'+no).disabled=false;
    document.getElementById('cukup'+no).disabled=false;
    document.getElementById('kurang'+no).disabled=false;
    
    loadData(0);
    // document.getElementById('lainnya').value='';


}

//==========CANCEL / RESET FORM cari awal ==================//
function cancelsearch()
{
   
    document.getElementById('txtNoakun').value='';
    document.getElementById('txtsearch').value='';
    // document.getElementById('statusup').checked=false;
    // document.getElementById('alamat').value='';
    // document.getElementById('aktif').checked=false;
    // document.getElementById('method').value='insert';
    // document.getElementById('supplierid').disabled=false;
    loadData(0);
}


function loadData(num) 
{
    // alert('masukk');
    param='method=loadData';
    param+='&page='+num;
    // param+='&supplierid2='+idsupplier_detail;
    tujuan='sdm_slave_exitinterview.php';
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
                                    
                                    document.getElementById('container').innerHTML=con.responseText;
                                    // getPage();
                                    // detaildt();

                                }
                        }
                        else {
                                busy_off();
                                error_catch(con.status);
                        }
              } 
     }  
}

function delData(nama)
{
    param='method=delData'+'&nama='+nama;
    alert(param);
    tujuan='sdm_slave_exitinterview.php';
    if(confirm('Anda yakin hapus item ini?'))post_response_text(tujuan, param, respog);
    // post_response_text(tujuan, param, respog);   
    function respog()
    {
          if(con.readyState==4)
          {
                if (con.status == 200) {
                    busy_off();
                    if (!isSaveResponse(con.responseText)) {
                        alert(con.responseText);
                    }
                    else 
                    {
                        loadData();
                    }
                }
                else {
                    busy_off();
                    error_catch(con.status);
                }
          } 
    }

}

//#searching data
function previewAkun(nosk,ev)
{
        param='table='+nosk;
        tujuan = 'keu_slave_5daftarperkiraan_pdf.php?'+param;   
     //display window
       title=nosk;
       width='700';
       height='400';
       content="<iframe frameborder=0 width=100% height=100% src='"+tujuan+"'></iframe>"
       showDialog1(title,content,width,height,ev);

}


function edit(noakun,namaakun,namaakun1,tipeakun,kasbank,level,matauang,kodeorg,detail,kasbank1,tagihan,jurnal,
			kodekegiatan,kodeasset,nik,kodecustomer,kodesupplier,kodevhc,kodeblok,pemilik){
				
	if(kodekegiatan=='1')
       document.getElementById('kodekegiatan').checked=true;
    else
       document.getElementById('kodekegiatan').checked=false;		
   
   if(kodeasset=='1')
       document.getElementById('kodeasset').checked=true;
    else
       document.getElementById('kodeasset').checked=false;	
   
   if(nik=='1')
       document.getElementById('nik').checked=true;
    else
       document.getElementById('nik').checked=false;

   
   if(kodecustomer=='1')
       document.getElementById('kodecustomer').checked=true;
    else
       document.getElementById('kodecustomer').checked=false;
   
   if(kodesupplier=='1')
       document.getElementById('kodesupplier').checked=true;
    else
       document.getElementById('kodesupplier').checked=false;	
   
   if(kodevhc=='1')
       document.getElementById('kodevhc').checked=true;
    else
       document.getElementById('kodevhc').checked=false;	
   
   if(kodeblok=='1')
       document.getElementById('kodeblok').checked=true;
    else
       document.getElementById('kodeblok').checked=false;		
				
				
				
				
    document.getElementById('noakun').value=noakun;
    document.getElementById('noakun').disabled=true;
    document.getElementById('namaakun').value=namaakun;
    document.getElementById('namaakun1').value=namaakun1;
    document.getElementById('tipeakun').value=tipeakun;
     document.getElementById('level').value=level;
     document.getElementById('matauang').value=matauang;
     document.getElementById('kodeorg').value=kodeorg;
     document.getElementById('kodeorg').disabled=true;

     if(kasbank=='1')
       document.getElementById('kasbank').checked=true;
    else
       document.getElementById('kasbank').checked=false;

    if(detail=='1')
       document.getElementById('detail').checked=true;
    else
       document.getElementById('detail').checked=false;

   if(kasbank1=='1')
       document.getElementById('kasbank1').checked=true;
    else
       document.getElementById('kasbank1').checked=false;

   if(tagihan=='1')
       document.getElementById('tagihan').checked=true;
    else
       document.getElementById('tagihan').checked=false;

   if(jurnal=='1')
       document.getElementById('jurnal').checked=true;
    else
       document.getElementById('jurnal').checked=false;

   document.getElementById('pemilik').value=pemilik;
    document.getElementById('method').value = 'update';
    // document.getElementById('method').value='update';
}







