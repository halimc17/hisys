function getdivisi2x(idhasil,isisumber,hasil) {
	param  = 'kdUnit=' + isisumber + '&proses=getdivisi2';
	param += '&hasil=' + hasil;
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					document.getElementById('divisi').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getdataunit(idhasil,isisumber) {
	param = 'proses=getdataunit&sumber=' + isisumber;
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById(idhasil).innerHTML=con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getdivisipusing(){
	kdUnit= document.getElementById('kdorg').options[document.getElementById('kdorg').selectedIndex].value;
	param='kdUnit='+kdUnit+'&proses=getdivisi';
	tujuan='slave_option.php';  
	post_response_text(tujuan, param, respog);
	function respog(){
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				}
				else {
					document.getElementById('divisi').innerHTML=con.responseText;
				}
			}
			else {
				busy_off();
				error_catch(con.status);
			}
		}
	}	
		
}

function getBlok(afd, idObjhsl, namalang) {
	afd = afd.options[afd.selectedIndex].value;
	param = 'afd=' + afd + '&proses=getBlok';
	tujuan = 'slave_option.php';
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById(idObjhsl).innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respon);
}

function getdata(jenis,idsumber,idhasil,isisumber) {
	param = 'proses='+jenis+'&sumber=' + isisumber;
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById(idhasil).innerHTML=con.responseText;
					/* if(jenis=='getklbyy' && isisumber=='TM'){
						document.getElementById('tglawal').disabled=true;
						document.getElementById('tglakhir').disabled=true;
						document.getElementById('prd').disabled=false;
						document.getElementById('prd2').disabled=false;
						document.getElementById('tglawal').value="";
						document.getElementById('tglakhir').value="";
					}else{
						document.getElementById('tglawal').disabled=false;
						document.getElementById('tglakhir').disabled=false;
						document.getElementById('prd').disabled=true;
						document.getElementById('prd2').disabled=true;
						document.getElementById('prd').value='';
						document.getElementById('prd2').value='';
						
					} */
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getkegiatan(idsumber,idhasil,hasiljikakosong) {
	param = 'sumber=' + idsumber + '&proses=getkegiatan';
	param += '&hasil=' + hasiljikakosong;
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById(idhasil).innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getakun(idsumber,idhasil,hasiljikakosong) {
	param = 'sumber=' + idsumber + '&proses=getakun';
	param += '&hasil=' + hasiljikakosong;
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById(idhasil).innerHTML = con.responseText;
					document.getElementById('kegiatan').options[document.getElementById('kegiatan').selectedIndex].value=0;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getEstateX(idsumber,idhasil,hasiljikakosong) {
	param = 'pt=' + idsumber + '&proses=getEstate';
	param += '&hasil=' + hasiljikakosong;
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById(idhasil).innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getDivisiX(idsumber,idhasil,hasiljikakosong) {
	param = 'kdUnit=' + idsumber + '&proses=getdivisi2';
	param += '&hasil=' + hasiljikakosong;
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById(idhasil).innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function getDivisiX2(idsumber,idhasil,hasiljikakosong) {
	param = 'kdUnit=' + idsumber + '&proses=getdivisi3';
	param += '&hasil=' + hasiljikakosong;
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById(idhasil).innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function getEstate() {
	pt = document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
	param = 'pt=' + pt + '&proses=getEstate';
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					document.getElementById('kdorg').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getEstate_x() {
	pt = document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
	param = 'pt=' + pt + '&proses=getEstateRKB';
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					document.getElementById('kdorg').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getdivisi_x(jenis) {

	kdUnit = document.getElementById('kdorg').options[document.getElementById('kdorg').selectedIndex].value;
	param = 'kdUnit=' + kdUnit + '&proses=getdivisi';
	param += '&jenis=' + jenis;
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					document.getElementById('divisi').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function getdivisi() {

	kdUnit = document.getElementById('kdUnit').options[document.getElementById('kdUnit').selectedIndex].value;
	param = 'kdUnit=' + kdUnit + '&proses=getdivisi';
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					document.getElementById('divisi').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function getdivisi2() {

	kdUnit = document.getElementById('kdUnit').options[document.getElementById('kdUnit').selectedIndex].value;
	param = 'kdUnit=' + kdUnit + '&proses=getdivisi2';
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					document.getElementById('divisi').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function getDiv() {

	unit = document.getElementById('unit').options[document.getElementById('unit').selectedIndex].value;
	param = 'unit=' + unit + '&proses=getDiv';
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					document.getElementById('bag').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function getUnit(idObjhsl, clearData, namalang, tipe) {
	pt = document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
	param = 'pt=' + pt + '&proses=getUnit';
	if (typeof tipe != 'undefined') {
		param += '&tipe=' + tipe;
	}
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					if ((typeof idObjhsl == 'undefined') || (idObjhsl == '')) {
						document.getElementById('kdorg').innerHTML = con.responseText;
					} else {
						document.getElementById(idObjhsl).innerHTML = con.responseText;
					}

					//jika field pt kosong maka kosongkan isi kebun,divisi dan tahun tanam
					if (typeof clearData != 'undefined') {
						isiField = clearData.split(",");
						for (i = 0; i < isiField.length; i++) {
							document.getElementById(isiField[i]).innerHTML = "<option value=''>" + namalang + "</option>";
						}
					}

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function getUnitRegional(unit, idObjhsl, clearData, namalang,inti='') {
	pt = unit.options[unit.selectedIndex].value;
	param = 'pt=' + pt + '&proses=getUnitRegional';
	param += '&inti=' + inti;
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					hsl = idObjhsl.split(",");
					balikandata = con.responseText.split("####");
					document.getElementById(hsl[0]).innerHTML = balikandata[0];
					// document.getElementById(hsl[1]).innerHTML = balikandata[1];
					//jika field pt kosong maka kosongkan isi kebun,divisi dan tahun tanam
					// isiField = clearData.split(",");
					// for (i = 0; i < isiField.length; i++) {
						// document.getElementById(isiField[i]).innerHTML = "<option value=''>" + namalang + "</option>";
					// }
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}
function getUnitThnTnm(unit, idObjhsl, clearData, namalang,inti='') {
	pt = unit.options[unit.selectedIndex].value;
	param = 'pt=' + pt + '&proses=getUnitThnTnm';
	param += '&inti=' + inti;
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					hsl = idObjhsl.split(",");
					balikandata = con.responseText.split("####");
					document.getElementById(hsl[0]).innerHTML = balikandata[0];
					document.getElementById(hsl[1]).innerHTML = balikandata[1];
					//jika field pt kosong maka kosongkan isi kebun,divisi dan tahun tanam
					isiField = clearData.split(",");
					for (i = 0; i < isiField.length; i++) {
						document.getElementById(isiField[i]).innerHTML = "<option value=''>" + namalang + "</option>";
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getAfdThnTnm(unit, idObjhsl, clearData, namalang) {
	kdorg = unit.options[unit.selectedIndex].value;
	param = 'kdorg=' + kdorg + '&proses=getAfdThnTnm';
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					hsl = idObjhsl.split(",");
					balikandata = con.responseText.split("####");
					document.getElementById(hsl[0]).innerHTML = balikandata[0];
					document.getElementById(hsl[1]).innerHTML = balikandata[1];
					//jika field pt kosong maka kosongkan isi kebun,divisi dan tahun tanam
					isiField = clearData.split(",");
					for (i = 0; i < isiField.length; i++) {
						document.getElementById(isiField[i]).innerHTML = "<option value=''>" + namalang + "</option>";
					}
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}

function getAfdeling(unit, idObjhsl, namalang, tipe) {
	unit = unit.options[unit.selectedIndex].value;
	param = 'unit=' + unit + '&proses=getAfdeling' + '&tipe=' + tipe;
	tujuan = 'slave_option.php';
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//=== Success Response
					document.getElementById(idObjhsl).innerHTML = con.responseText;

				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respon);
}
function getUnit2() {
	pt2 = document.getElementById('pt2').options[document.getElementById('pt2').selectedIndex].value;
	param = 'pt2=' + pt2 + '&proses=getUnit2';
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					document.getElementById('kdorg2').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function getUnitLapPremi() {
	pt = document.getElementById('pt').options[document.getElementById('pt').selectedIndex].value;
	param = 'pt=' + pt + '&proses=getUnitLapPremi';
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					document.getElementById('kdorg').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function getThnTnm(afd, idObjhsl, namalang) {
	afd = afd.options[afd.selectedIndex].value;
	param = 'afd=' + afd + '&proses=getThnTnm';
	tujuan = 'slave_option.php';
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//=== Success Response
					document.getElementById(idObjhsl).innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respon);
}
function getThnTnm2(afd, idObjhsl, namalang) {
	kdorg = document.getElementById('kdorg').options[document.getElementById('kdorg').selectedIndex].value;
	afd = afd.options[afd.selectedIndex].value;
	param = 'kdorg=' + kdorg + '&afd=' + afd + '&proses=getThnTnm2';
	tujuan = 'slave_option.php';
	function respon() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					//=== Success Response
					document.getElementById(idObjhsl).innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
	post_response_text(tujuan, param, respon);
}

function getunit() {

	kdUnit = document.getElementById('kodept').options[document.getElementById('kodept').selectedIndex].value;
	param = 'kdUnit=' + kdUnit + '&proses=getunit';
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					document.getElementById('unit').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function getkodevhsunit() {

	kdUnit = document.getElementById('kdorg').options[document.getElementById('kdorg').selectedIndex].value;
	param = 'kdUnit=' + kdUnit + '&proses=getkodevhsunit';
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {

					document.getElementById('kdvhc').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}

}

function getUnitGaji() {
	pt = document.getElementById('pt').value;
	param = 'proses=getUnitGaji' + '&pt=' + pt;
	tujuan = 'slave_option.php';
	post_response_text(tujuan, param, respog);
	function respog() {
		if (con.readyState == 4) {
			if (con.status == 200) {
				busy_off();
				if (!isSaveResponse(con.responseText)) {
					alert(con.responseText);
				} else {
					document.getElementById('unit').innerHTML = con.responseText;
				}
			} else {
				busy_off();
				error_catch(con.status);
			}
		}
	}
}