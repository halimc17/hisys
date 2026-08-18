var reportModal;

function callAfterSubmit(e) {
  const table = $.QGen.createTableElement(e.response);

  const htmlOutput = document.getElementById('reportOutput');
  htmlOutput.innerHTML = '';
  htmlOutput.appendChild(table);
}

function callbackPreview(e) {
  console.log(e);
  const table = $.QGen.createTableElement(e.response);
  
  const html = document.getElementById('reportOutput');
  html.innerHTML = '';
  html.appendChild(table);
}

function reset() {
  $.Confirm('Are you sure you want to reset the form? This will clear all selections.', () => {
    winUpdate.refresh();
    $.getElementById('dbList').value = '';
  });
}

function newAction() {
  $.get(false, $.options.slave+"?switcher=getDatabases", (e) => {
    file = $.QGen.createSelectElement(
			'dbList',
			'Select a database',
			Object.values(JSON.parse(e.response)),
      (ev) => {
        $.QGen.db = ev.target.value;
        $.get(false, $.options.slave+"?switcher=getTables&db="+ev.target.value, (eve) => {
          $.QGen.tableList = Object.values(JSON.parse(eve.response));
          const target = $.getElementById('tableListContainer');
          const oldSelect = $.getElementById('tableList1');
          const newSelect = $.QGen.createSelectElement(
            'tableList1',
            'Select a table',
            $.QGen.tableList,
            (event) => {$.QGen.getThisField(event.target.value, 'table1')}
          );

          if (oldSelect) {
            target.replaceChild(newSelect, oldSelect);
          }
        });
        $.getElementById('btNew').disabled = false;
      }
		);

    tujuan = $.options.slave +'?switcher=new';
    let options = {
      url: tujuan,
      title: 'Create New Table',
      success: () => {
        winUpdate.target.body.querySelector('#dbListContainer').appendChild(file);
      }
    };

    winUpdate = $.openWindow(options);
  });
}

function viewAction(getpage, title) {
  $.get(false, $.options.slave+"?switcher=sendData"+getpage, (e) => {
    file = $.QGen.displayBrowseReport(e.response);
    tujuan = $.options.slave +'?switcher=view'+ getpage;
    let options = {
      url: tujuan,
      title: title,
      success: () => {
        winUpdate.target.body.querySelector('#listParam').insertAdjacentElement('afterbegin', file);
      }
    };
    winUpdate = $.openWindow(options);
    
  });
}

function publishAction(getpage) {
  tujuan = $.options.slave + getpage;
	let ele = $.dataAction.target;
	$.Confirm('Anda yakin publish tabel ini? ', () => {
    $.get(ele, tujuan, function callback(Result) {
      if (!Result.response.error) {
        $.refresh();
      } else {
        $.Alert(Result.response.message);
      }
    });
  });
}

function unpublishAction(getpage) {
  tujuan = $.options.slave + getpage;
	let ele = $.dataAction.target;
	$.Confirm('Anda yakin unpublish tabel ini? ', () => {
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
  $.get(false, $.options.slave+getpage, (res) => {
    const response = JSON.parse(res.response);
    $.get(false, $.options.slave+"?switcher=getDatabases", (e) => {
      file = $.QGen.createSelectElement(
        'dbList',
        'Select a database',
        Object.values(JSON.parse(e.response)),
        (ev) => {
          $.QGen.db = ev.target.value;
          $.get(false, $.options.slave+"?switcher=getTables&db="+ev.target.value, (eve) => {
            $.QGen.tableList = Object.values(JSON.parse(eve.response));
            const target = $.getElementById('tableListContainer');
            const oldSelect = $.getElementById('tableList1');
            const newSelect = $.QGen.createSelectElement(
              'tableList1',
              'Select a table',
              $.QGen.tableList,
              (event) => {$.QGen.getThisField(event.target.value, 'table1')}
            );

            if (oldSelect) {
              target.replaceChild(newSelect, oldSelect);
            }
          });
          $.getElementById('btNew').disabled = false;
        }
      );

      tujuan = $.options.slave +'?switcher=new';
      let options = {
        url: tujuan,
        title: 'Edit Table',
        success: () => {
          winUpdate.target.body.querySelector('#dbListContainer').appendChild(file);
          $.QGen.editViewConstructor(response);
        }
      };

      winUpdate = $.openWindow(options);
    });
  })
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
