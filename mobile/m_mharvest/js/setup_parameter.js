var winUpdate;

function callAfterSubmit(e) {
  if (e.response.status == 'success') {
    $.Alert(e.response.message, () => {
      winUpdate.close();

      $.refresh();
    });
  } else {
    $.Alert(e.response.message);
  }
}

function newAction() {
  tujuan = $.options.slave +'?switcher=new';
  let options = {
    url: tujuan,
    title: 'Create New Parameter',
    success: () => {}
  };

  winUpdate = $.openWindow(options);
}

function deleteAction(getpage) {
  tujuan = $.options.slave + getpage;
	let ele = $.dataAction.target;
	$.Confirm('Anda yakin delete tabel ini? ', () => {
    $.get(ele, tujuan, function callback(Result) {
      if (!Result.response.error) {
        $.refresh();
      } else {
        $.Alert(Result.response.message);
      }
    });
  });
}

function editAction(getpage) {
  tujuan = $.options.slave + getpage;
  let options = {
    url: tujuan,
    title: 'Edit Parameter',
    success: () => {}
  };

  winUpdate = $.openWindow(options);
}

function tanpa_kutip(e) {
  if (e.keyCode == 39 || e.which == 39) {
    e.preventDefault();
    return false;
  }

  return true;
}
