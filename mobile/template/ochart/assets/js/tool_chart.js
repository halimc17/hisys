(() => {
  class chart {
    constructor() {
      this.subMenu = [];
      this.titleDashboard = null;
      this.oldData = {};
      this.dataChart = [];
      this.con = null;
      this.selectedChart = [];
      this.parameter = {};
      this.chartInstances = {};
    }

    init(tableList, chartList) {
      this.tableList = tableList;
      this.chartList = chartList;

      return this.createAccordionTableList();
    }

    maximizeSidebar() {
      const sidebar = document.getElementById('sidebar');
      sidebar.style.width = '';
      sidebar.className = 'col-3 py-3 d-flex flex-column gap-3 bg-primary-subtle overflow-auto';

      document.getElementById('headerSidebar').className = 'd-flex flex-row-reverse gap-3 w-100';
      document.getElementById('rightArrow').style.display = 'block';
      document.getElementById('leftArrow').style.display = 'none';
      document.getElementById('titleSidebar').style.writingMode = 'horizontal-tb';
      document.getElementById('accordionTableList').style.display = 'block';

      if (document.getElementById('btnSave')) {
        document.getElementById('btnSave').style.display = 'block';
      }

      const containerChart = document.getElementById('containerChart');
      if (containerChart) {
        const [w, h] = document.getElementById('selectRatio').value.split(':').map(Number);
        containerChart.style.height = `${(h / w) * containerChart.offsetWidth}px`;
      }
    }

    minimizeSidebar() {
      const sidebar = document.getElementById('sidebar');
      sidebar.style.width = '40px';
      sidebar.className = 'col-1 py-3 px-0 d-flex flex-column gap-3 bg-primary-subtle';

      document.getElementById('headerSidebar').className = 'd-flex flex-column gap-3 w-100';
      document.getElementById('rightArrow').style.display = 'none';
      document.getElementById('leftArrow').style.display = 'block';
      document.getElementById('titleSidebar').style.writingMode = 'vertical-lr';
      document.getElementById('accordionTableList').style.display = 'none';

      if (document.getElementById('btnSave')) {
        document.getElementById('btnSave').style.display = 'none';
      }

      const containerChart = document.getElementById('containerChart');
      if (containerChart) {
        const [w, h] = document.getElementById('selectRatio').value.split(':').map(Number);
        containerChart.style.height = `${(h / w) * containerChart.offsetWidth}px`;
      }
    }

    createAccordionTableList() {
      const accordion = document.createElement('div');
      accordion.id = 'accordionTableList';
      accordion.className = 'accordion';
      accordion.style.display = 'none';

      if (this.tableList.length === 0) {
        const noTable = document.createElement('p');
        noTable.className = 'fs-6';
        noTable.innerText = 'No table created';
        accordion.appendChild(noTable);
      } else {
        this.tableList.forEach((item) => {
          const accordionItem = this.createAccordionItem(item);
          accordion.appendChild(accordionItem);
        })
      }

      return accordion;
    }

    createAccordionItem(item) {
      const accordionItem = document.createElement('div');
      accordionItem.className = 'accordion-item';

      const accordionHeader = document.createElement('h6');
      accordionHeader.className = 'accordion-header';

      const accordionButton = document.createElement('button');
      accordionButton.className = 'accordion-button collapsed';
      accordionButton.type = 'button';
      accordionButton.dataset.bsToggle = 'collapse';
      accordionButton.dataset.bsTarget = `#collapse${item.rnumber}`;
      accordionButton.setAttribute('aria-expanded', 'false');
      accordionButton.setAttribute('aria-controls', `collapse${item.rnumber}`);
      accordionButton.innerText = item.namalaporan;
      
      accordionHeader.appendChild(accordionButton);
      accordionItem.appendChild(accordionHeader);

      const accordionCollapse = this.createAccordionCollapse(item);
      accordionItem.appendChild(accordionCollapse);

      return accordionItem;
    }

    createAccordionCollapse(item) {
      const accordionCollapse = document.createElement('div');
      accordionCollapse.id = `collapse${item.rnumber}`;
      accordionCollapse.className = 'accordion-collapse collapse';
      accordionCollapse.dataset.bsParent = '#accordionTableList';

      const accordionBody = document.createElement('div');
      accordionBody.className = 'accordion-body';

      const relatedChart = this.chartList.filter(chartItem => chartItem.rnumber === item.rnumber);

      if (relatedChart.length > 0) {
        relatedChart.forEach(chartItem => {
          const checkbox = this.createCheckbox(chartItem);
          accordionBody.appendChild(checkbox);
        });
      } else {
        const noChart = document.createElement('p');
        noChart.className = 'fs-6';
        noChart.innerText = 'No chart created';
        accordionBody.appendChild(noChart);
      }
      
      accordionCollapse.appendChild(accordionBody);

      return accordionCollapse;
    }

    createCheckbox(chartItem) {
      const checkbox = document.createElement('div');
      checkbox.className = 'form-check';

      const input = document.createElement('input');
      input.className = 'form-check-input vertical-align-middle';
      input.type = 'checkbox';
      input.value = chartItem.id;
      input.id = `chart${chartItem.id}`;
      input.addEventListener('change', (e) => this.handleCheckboxChange(e, chartItem));

      const label = document.createElement('label');
      label.className = 'form-check-label fs-6';
      label.setAttribute('for', `chart${chartItem.id}`);
      label.innerText = chartItem.nama;

      checkbox.appendChild(input);
      checkbox.appendChild(label);

      return checkbox;
    }

    handleCheckboxChange(e, chartItem) {
      if (e.target.checked) {
        this.addChart(chartItem);
      } else {
        this.removeChart(chartItem);
      }
    }

    removeChart(chartItem) {
      this.selectedChart = this.selectedChart.filter(item => item.id !== chartItem.id);

      document.getElementById(`Chart${chartItem.id}`)?.remove();
      this.updateIds();


      if (!this.selectedChart.map(item => item.rnumber).includes(chartItem.rnumber)) {
        document.getElementById(`parameter${chartItem.rnumber}`)?.remove();
      }

      if (this.selectedChart.length === 0) {
        document.getElementById('containerHeader')?.remove();
        document.getElementById('containerChart')?.remove();
        document.getElementById('btnSave')?.remove();
      }
    }

    addChart(chartItem) {
      if (!document.getElementById('titleDashboard')) {
        this.createDashboardContainer();
      }

      this.selectedChart.push(chartItem);
      this.createCanvas(chartItem.id);

      if (this.selectedChart.filter((item) => item.rnumber === chartItem.rnumber).length === 1) {
        this.getParameter(chartItem.rnumber);
      }

      if (this.oldData[chartItem.rnumber]) {
        this.update(chartItem.rnumber);
      }

      if (!document.getElementById('btnSave')) {
        this.createBtnSave();
      }
    }

    createBtnSave() {
      const sidebar = document.getElementById('sidebar');

      const btnSave = document.createElement('button');
      btnSave.id = 'btnSave';
      btnSave.className = 'btn btn-success';
      btnSave.innerText = 'Save';
      btnSave.addEventListener('click', () => this.saveDashboard());

      sidebar.appendChild(btnSave);
    }

    saveDashboard() {
      this.updateData();
      if (this.subMenu.length != 3) {
        $.Alert("Submenu Required", 'Please fill in the submenu before saving the dashboard');
      } else if (!this.titleDashboard) {
        $.Alert("Dashboard Title Required", 'Title dashboard cannot be empty');
      } else if (!this.parameter || Object.keys(this.parameter).length === 0) {
        $.Alert("Parameter Required", 'Please fill in the parameters before saving the dashboard');
      } else {
        $.get(false, $.options.slave+"?switcher=save&title="+this.titleDashboard+"&data="+encodeURIComponent(JSON.stringify(this.selectedChart)), (e) => {
          const response = JSON.parse(e.response);
          if (response.status === 'success') {
            $.get(false, $.options.pathinfo.site_url+"/api/module/ochart/uploadJson/load?id="+response.id+"&parameter="+encodeURIComponent(JSON.stringify([this.parameter]))+"&submenu="+encodeURIComponent(JSON.stringify(this.subMenu)), (e) => {
              if (!e.response.status.error) {
                $.refresh();
                $.redirect('master?page=page_chart_generator');
              } else {
                $.Alert('Error', response.message);
              }
            });
          } else {
            $.Alert('Error', response.message);
          }
        });
      }
    }

    updateData() {
      const chartContainers = document.getElementById('containerChart');

      chartContainers.childNodes.forEach((row) => {
        if (row.childElementCount > 0) {
          row.childNodes.forEach((col, index) => {
            const chartId = col.firstChild.id.split('Chart')[1];
            const chartIndex = this.selectedChart.findIndex((item) => item.id === chartId);
            const yPosition = row.id.split('row')[1];
            const xPosition = index+1;
            const width = col.className.split(' ')[0].split('-')[1];
            const height = row.clientHeight;

            this.selectedChart[chartIndex].y = yPosition;
            this.selectedChart[chartIndex].x = xPosition;
            this.selectedChart[chartIndex].w = width;
            this.selectedChart[chartIndex].h = height;
          });
        }
      });
    }

    createDashboardContainer() {
      const dashboard = document.getElementById('dashboard');
      
      const containerHeader = document.createElement('div');
      containerHeader.className = 'card-header d-flex flex-column gap-3';
      containerHeader.id = 'containerHeader';

      const containerTitle = document.createElement('div');
      containerTitle.className = 'd-flex flex-row gap-3';

      const titleDashboard = document.createElement('input');
      titleDashboard.id = 'titleDashboard';
      titleDashboard.className = 'form-control form-control-lg';
      titleDashboard.style.width = '80%';
      titleDashboard.type = 'text';
      titleDashboard.placeholder = 'Title Dashboard';
      titleDashboard.setAttribute('aria-label', 'titleDashboard');
      titleDashboard.addEventListener('change', (e) => this.titleDashboard = e.target.value);

      const containerSelect = document.createElement('div');
      containerSelect.style.width = '20%';

      const labelSelect = document.createElement('label');
      labelSelect.className = 'form-label';
      labelSelect.innerText = 'Select Ratio';
      labelSelect.setAttribute('for', 'selectRatio');

      const selectRatio = document.createElement('select');
      selectRatio.className = 'form-select py-0';
      selectRatio.setAttribute('aria-label', 'selectRatio');
      selectRatio.id = 'selectRatio';
      selectRatio.style.fontSize = '12px'
      selectRatio.addEventListener('change', (e) => {
        const containerChart = document.getElementById('containerChart');
        const containerWidth = containerChart.offsetWidth;

        const [w, h] = e.target.value.split(':').map(Number);
        
        containerChart.style.height = `${(h / w) * containerWidth}px`;
      });

      const options = [
        { value: '4:3', text: '4:3' },
        { value: '16:9', text: '16:9' },
        { value: '16:10', text: '16:10' },
        { value: '21:9', text: '21:9' },
        { value: '9:16', text: '9:16' }
      ];

      options.forEach(option => {
        const optionElement = document.createElement('option');
        optionElement.value = option.value;
        optionElement.textContent = option.text;

        selectRatio.appendChild(optionElement);
      });

      containerSelect.appendChild(labelSelect);
      containerSelect.appendChild(selectRatio);

      containerTitle.appendChild(titleDashboard);
      containerTitle.appendChild(containerSelect);

      const containerParameter = document.createElement('div');
      containerParameter.id = 'containerParameter';
      containerParameter.className = 'row'

      const containerChart = document.createElement('div');
      containerChart.id = 'containerChart';
      containerChart.className = 'row card-body w-100 bg-light';
      containerChart.style.marginLeft = '0';

      containerHeader.appendChild(containerTitle);
      containerHeader.appendChild(containerParameter);

      const containerSubmenu = document.createElement('div');
      containerSubmenu.id = 'containerSubmenu';
      containerSubmenu.className = 'd-flex flex-row gap-3 mb-3';

      const labelInputSubmenu = document.createElement('label');
      labelInputSubmenu.className = 'form-label';
      labelInputSubmenu.innerText = 'Input Submenu';
      labelInputSubmenu.setAttribute('for', 'inputSubmenu');

      const inputSubmenu = document.createElement('input');
      inputSubmenu.id = 'inputSubmenu';
      inputSubmenu.className = 'form-control form-control-sm py-0';
      inputSubmenu.style.fontSize = '12px';
      inputSubmenu.type = 'text';
      inputSubmenu.placeholder = 'Submenu Name';
      inputSubmenu.setAttribute('aria-label', 'inputSubmenu');
      inputSubmenu.addEventListener('change', (e) => {
        if (this.subMenu.length == 0) {
          this.subMenu.push({ name: e.target.value });
        } else {
          this.subMenu.forEach((item) => {
            item.name = e.target.value;
          });
        }
      });

      const containerInputSubmenu = document.createElement('div');
      containerInputSubmenu.className = 'd-flex flex-row gap-3';

      containerInputSubmenu.appendChild(labelInputSubmenu);
      containerInputSubmenu.appendChild(inputSubmenu);

      const labelSelectIcon = document.createElement('label');
      labelSelectIcon.className = 'form-label';
      labelSelectIcon.innerText = 'Submenu Icon';
      labelSelectIcon.setAttribute('for', 'selectIcon');

      const selectIcon = document.createElement('select');
      selectIcon.id = 'selectIcon';
      selectIcon.className = 'form-select py-0';
      selectIcon.style.fontSize = '12px';
      selectIcon.setAttribute('aria-label', 'selectIcon');
      selectIcon.addEventListener('change', (e) => {
        if (this.subMenu.length < 2) {
          this.subMenu.push({ icon: e.target.value });
          this.subMenu.push({ iconColor: e.target.options[e.target.selectedIndex].dataset.color });
        } else {
          this.subMenu.forEach((item) => {
            item.icon = e.target.value;
            item.iconColor = e.target.options[e.target.selectedIndex].dataset.color;
          });
        }
      });

      const iconOptions = [
        { value: '', text: 'Select icon', color: '' },
        { value: 'truck', text: 'Icon Truck', color: 'success' },
        { value: 'file-powerpoint-o', text: 'Icon File PowerPoint', color: 'warning' },
        { value: 'list-ol', text: 'Icon List', color: 'info' },
        { value: 'industry', text: 'Icon Industry', color: 'primary' },
        { value: 'cloud-download', text: 'Icon Cloud Download', color: 'danger' },
        { value: 'tint', text: 'Icon Tint', color: 'success' },
        { value: 'bitbucket', text: 'Icon Bitbucket', color: 'warning' },
        { value: 'tv', text: 'Icon TV', color: 'info' },
        { value: 'users', text: 'Icon Users', color: 'primary' },
        { value: 'clock-o', text: 'Icon Clock', color: 'danger' },
        { value: 'money', text: 'Icon Money', color: 'success' },
        { value: 'gear', text: 'Icon Gear', color: 'warning' }
      ];

      iconOptions.forEach(option => {
        const optionElement = document.createElement('option');
        optionElement.value = option.value;
        optionElement.dataset.color = option.color;
        optionElement.innerHTML = `<i class="fa fa-${option.value} text-${option.color}"></i> ${option.text}`;

        selectIcon.appendChild(optionElement);
      });

      const containerSelectIcon = document.createElement('div');
      containerSelectIcon.className = 'd-flex flex-row gap-3';

      containerSelectIcon.appendChild(labelSelectIcon);
      containerSelectIcon.appendChild(selectIcon);

      containerSubmenu.appendChild(containerInputSubmenu);
      containerSubmenu.appendChild(containerSelectIcon);

      dashboard.appendChild(containerSubmenu);
      dashboard.appendChild(containerHeader);
      dashboard.appendChild(containerChart);

      const containerWidth = containerChart.offsetWidth;
      containerChart.style.height = `${(3 / 4) * containerWidth}px`;
    }

    createCanvas(id) {
      const newRow = this.selectedChart.length % 2 === 1;
      const containerChart = document.getElementById('containerChart');

      const rowDiv = newRow ? document.createElement('div') : document.getElementById(`row${Math.ceil(this.selectedChart.length / 2)}`);
      rowDiv.className = 'row chart-row';
      rowDiv.id = `row${Math.ceil(this.selectedChart.length / 2)}`;
      rowDiv.style.height = 'auto';
      rowDiv.setAttribute('drop', true);
      rowDiv.addEventListener('dragover', (e) => this.allowDrop(e));
      rowDiv.addEventListener('drop', (e) => this.drop(e));

      const canvasContainer = document.createElement('div');
      canvasContainer.id = `Chart${id}`;
      canvasContainer.className = 'col-6 canvas-container';
      canvasContainer.style.height = 'auto';
      canvasContainer.addEventListener('dragstart', (e) => this.drag(e));
      canvasContainer.draggable = true;

      const canvas = document.createElement('canvas');
      canvas.id = `myChart${id}`;

      canvasContainer.appendChild(canvas);
      rowDiv.appendChild(canvasContainer);
      
      if (this.selectedChart.length === 1) {
        containerChart.appendChild(rowDiv);
      } else if (this.selectedChart.length === 2) {
        containerChart.appendChild(this.createDropArea());
        containerChart.appendChild(rowDiv);
        containerChart.appendChild(this.createDropArea());
      } else if (newRow) {
        containerChart.appendChild(rowDiv);
        containerChart.appendChild(this.createDropArea());
      }

      const observer = new ResizeObserver((mutations) => {
        const containerWidth = containerChart.clientWidth;
        let mutation = mutations.shift();
        let colClass = this.getColsNumber(containerWidth, mutation.target.clientWidth);
        canvasContainer.className = `col-${colClass} canvas-container`;
        canvasContainer.style.width = null;
      });
      observer.observe(canvasContainer);
    }

    createCanvasView(id, data) {
      const newRow = document.getElementById(`row${data.y}`) ? false : true;
      const containerChart = document.getElementById('containerChart');

      const rowDiv = newRow ? document.createElement('div') : document.getElementById(`row${data.y}`);
      rowDiv.className = 'row chart-row';
      rowDiv.id = `row${data.y}`;
      rowDiv.style.height = `${data.h}px`;

      const canvasContainer = document.createElement('div');
      canvasContainer.id = `Chart${id}`;
      canvasContainer.className = `col-${data.w}`;
      canvasContainer.style.height = `${data.h}px`;

      const canvas = document.createElement('canvas');
      canvas.id = `myChart${id}`;

      canvasContainer.appendChild(canvas);
      rowDiv.appendChild(canvasContainer);
      containerChart.appendChild(rowDiv);
    }

    getColsNumber(containerWidth, clientWidth) {
      return Math.min(12, Math.max(1, Math.round(clientWidth / (containerWidth / 12))));
    }

    createDropArea() {
      let dropArea = document.createElement('div');
      dropArea.id = 'dropArea';
      dropArea.classList.add('row');
      dropArea.classList.add('dropArea');
      dropArea.setAttribute('drop', true);
      dropArea.style.height = 'auto';
      dropArea.addEventListener('dragover', (e) => this.allowDrop(e));
      dropArea.addEventListener('drop', (e) => this.drop(e));
      return dropArea;
    }
    
    allowDrop(e) {
      e.preventDefault();
      e.stopPropagation();
      if (e.target.hasAttribute('drop') && e.target.getAttribute('drop') == 'true') {
        e.target.style.border = '1px solid black';
        e.target.style.height = 'auto';
      }
    }

    drag(e) {
      e.dataTransfer.setData('text', e.target.id);
      e.target.style.border = 'none';
      e.target.style.height = 'auto';
    }

    async drop(e) {
      e.preventDefault();
      e.stopPropagation();
      if (e.target.hasAttribute('drop') && e.target.getAttribute('drop') == 'true') {
        let wC = 0;
        let element = document.getElementById(e.dataTransfer.getData('text'));

        if(e.target.querySelectorAll('.canvas-container').length > 0){
          e.target.querySelectorAll('.canvas-container').forEach((child) => {
            wC += child.clientWidth;
          });

          if(wC <  e.target.clientWidth){
            let clienWidht = (e.target.clientWidth-wC);
            element.className = 'canvas-container col-'+parseInt(this.getColsNumber(e.target.clientWidth,clienWidht));
          }
        }

        await e.target.appendChild(element);
        e.target.className = 'row chart-row';
        e.target.style.border = 'none';
        e.target.style.height = 'auto';

        document.querySelectorAll('.dropArea').forEach((dropArea) => {
          dropArea.style.border = 'none';
          dropArea.style.height = 'auto';
        });
        document.querySelectorAll('.chart-row').forEach((canvasContainer) => {
          canvasContainer.style.border = 'none';
          canvasContainer.style.height = 'auto';
        });
        
        this.updateIds();
      }
    }
    
    updateIds() {
      let chartContainers = document.getElementById('containerChart');

      let index = 0;
      chartContainers.childNodes.forEach((row) => {
        if (row.childElementCount > 0) {
          index++;
          row.id = `row${index}`;
        }
      });

      if (chartContainers.firstChild.id !== 'dropArea') {
        chartContainers.insertAdjacentElement('afterbegin', this.createDropArea());
      }

      if (chartContainers.lastChild.id !== 'dropArea') {
        chartContainers.insertAdjacentElement('beforeend', this.createDropArea());
      }

      const chartRows = document.querySelectorAll('.chart-row');
      chartRows.forEach((row) => {
        if (row.childElementCount === 0) {
          row.remove();
        }

        if (row.nextElementSibling?.id !== 'dropArea') {
          row.insertAdjacentElement('afterend', this.createDropArea());
        }
      });

      chartContainers = document.getElementById('containerChart');
      chartContainers.childNodes.forEach((row) => {
        if (row.childElementCount > 0 && row.nextSibling?.id !== 'dropArea') {
          row.insertAdjacentElement('afterend', this.createDropArea());
        }
      });

      chartContainers = document.getElementById('containerChart');
      chartContainers.childNodes.forEach((row) => {
        if (row.id === 'dropArea' && row.nextSibling?.id === 'dropArea') {
          row.remove();
        }
      });
    }

    getParameter(rnumber) {
      $.get(false, $.options.slave+"?switcher=parameter&rnumber="+rnumber, (e) => {
        const parameters = JSON.parse(e.response);
        const label = this.tableList.find((item) => item.rnumber === parameters[0].rnumber).namalaporan;
        this.createInputParameter(parameters, label);
      });
    }
    
    createInputParameter(parameters, label) {
      const dashboard = document.getElementById('containerParameter');

      const container = document.createElement('div');
      container.id = `parameter${parameters[0].rnumber}`;
      container.className = 'my-3 col-6';

      const title = document.createElement('h6');
      title.className = 'form-label';
      title.innerText = label
      container.appendChild(title);
      
      parameters.forEach((item, i) => {
        container.appendChild(this.createInputGroup(item, i));
      });

      dashboard.appendChild(container);
    }

    createInputGroup(item, i) {
      const inputGroup = document.createElement('div');
      inputGroup.className = 'input-group input-group-sm my-1';

      const inputGroupText = document.createElement('span');
      inputGroupText.className = 'input-group-text';
      inputGroupText.innerText = item.kolom.split('.')[1] + ' ' + item.operator;

      let input;
      if (item.value == 'Setup') {
        input = this.createSetupSelect(item);
      } else {
        input = document.createElement('input');
        input.className = 'form-control';
        input.type = item.value;
        input.placeholder = item.kolom.split('.')[1];
        input.dataset.kolom = item.kolom;
        input.dataset.operator = item.operator;
        input.dataset.rnumber = item.rnumber;
      }

      input.addEventListener('change', (e) => {
        this.parameter[`${item.rnumber}_${i}`] = {
          rnumber: item.rnumber,
          kolom: item.kolom,
          operator: item.operator,
          isi: e.target.value,
          type: item.value,
          php: item.value == 'Setup' ? e.target.options[e.target.selectedIndex]?.dataset.php : ''
        };
        this.parameter[item.rnumber] = '';
        Object.keys(this.parameter).forEach((key) => {
          if (key.split('_')[0] === item.rnumber && key !== `${item.rnumber}`) {
            const param = this.parameter[key];
            if (param.operator === 'BETWEEN') {
              if (this.parameter[item.rnumber]) {
                this.parameter[item.rnumber] += ' AND ';
              }

              this.parameter[item.rnumber] += `(${param.kolom} ${param.operator} '${param.isi}' AND '${param?.isi2}')`;
            } else if (param.operator === 'NULL') {
              this.parameter[item.rnumber] = `${param.kolom} ${param.operator}`;
            } else if (param.operator === 'LIKE') {
              if (this.parameter[item.rnumber]) {
                this.parameter[item.rnumber] += ' AND ';
              }

              this.parameter[item.rnumber] += `${param.kolom} ${param.operator} '%${param.isi}%'`;
            } else {
              if (this.parameter[item.rnumber]) {
                this.parameter[item.rnumber] += ' AND ';
              } 

              this.parameter[item.rnumber] += `${param.kolom} ${param.operator} '${param.isi}'`;
            }
          }
        });

        this.fetchData(item.rnumber);
      });

      inputGroup.appendChild(inputGroupText);
      inputGroup.appendChild(input);

      if (item.operator === 'BETWEEN') {
        const inputGroupText2 = document.createElement('span');
        inputGroupText2.className = 'input-group-text';
        inputGroupText2.innerText = 'AND';

        const input2 = document.createElement('input');
        input2.className = 'form-control';
        input2.type = item.value;
        input2.placeholder = item.kolom.split('.')[1];
        input2.dataset.kolom = item.kolom;
        input2.dataset.operator = item.operator;
        input2.dataset.rnumber = item.rnumber;

        input2.addEventListener('change', (e) => {
          this.parameter[`${item.rnumber}_${i}`].isi2 = e.target.value;
          this.parameter[item.rnumber] = '';
          Object.keys(this.parameter).forEach((key) => {
            if (key.split('_')[0] === item.rnumber && key !== `${item.rnumber}`) {
              const param = this.parameter[key];
              if (param.operator === 'BETWEEN') {
                if (this.parameter[item.rnumber]) {
                  this.parameter[item.rnumber] += ' AND ';
                }

                this.parameter[item.rnumber] += `(${param.kolom} ${param.operator} '${param.isi}' AND '${param.isi2}')`;
              } else if (param.operator === 'NULL') {
                this.parameter[item.rnumber] = `${param.kolom} ${param.operator}`;
              } else if (param.operator === 'LIKE') {
                if (this.parameter[item.rnumber]) {
                  this.parameter[item.rnumber] += ' AND ';
                }

                this.parameter[item.rnumber] += `${param.kolom} ${param.operator} '%${param.isi}%'`;
              } else {
                if (this.parameter[item.rnumber]) {
                  this.parameter[item.rnumber] += ' AND ';
                } 

                this.parameter[item.rnumber] += `${param.kolom} ${param.operator} '${param.isi}'`;
              }
            }
          });
          
          this.fetchData(item.rnumber);
        });

        inputGroup.appendChild(inputGroupText2);
        inputGroup.appendChild(input2);
      }

      return inputGroup;
    }

		/**
		 * Function to create a select dropdown for Setup parameters
		 * @param {number} index - The parameter index
		 * @param {string} fieldName - The field name
		 * @param {string} idPrefix - Prefix for the select element ID
		 * @returns {HTMLElement} - The created select element
		 */
		createSetupSelect(item) {
			const select = document.createElement('select');
			select.className = 'form-select';
      select.style.height = '30.33px';
      select.dataset.kolom = item.kolom;
      select.dataset.operator = item.operator;
      select.dataset.rnumber = item.rnumber;
			
			// Add a placeholder option
			const placeholderOption = document.createElement('option');
			placeholderOption.value = '';
			placeholderOption.textContent = 'Select a value';
			placeholderOption.disabled = true;
			placeholderOption.selected = true;
			select.appendChild(placeholderOption);
			
			// Get options based on the field name
			$.get(false, $.options.slave+"?switcher=getSetupParams", (e) => {
				const options = e.response
				// Add options to the select element
				options.forEach(option => {
					const optionElement = document.createElement('option');
					optionElement.value = option.nilai;
          optionElement.dataset.php = option.value.replaceAll('"', '\\"');
					optionElement.textContent = option.name;
					select.appendChild(optionElement);
				});
			});
			
			return select;
		}
    
    createInputParamView(parameters, label) {
      const dashboard = document.getElementById('containerParameter');

      const container = document.createElement('div');
      container.id = `parameter${parameters[0].rnumber}`;
      container.className = 'my-3 col-6';

      const title = document.createElement('h6');
      title.className = 'form-label';
      title.innerText = label
      container.appendChild(title);
      
      parameters.forEach((item, i) => {
        container.appendChild(this.createInputGroupView(item, i));
      });

      dashboard.appendChild(container);
    }

    createInputGroupView(item, i) {
      const inputGroup = document.createElement('div');
      inputGroup.className = 'input-group input-group-sm my-1';

      const inputGroupText = document.createElement('span');
      inputGroupText.className = 'input-group-text';
      inputGroupText.innerText = item.kolom.split('.')[1] + ' ' + item.operator;

      const input = document.createElement('input');
      input.className = 'form-control';
      input.type = item.value;
      input.placeholder = item.kolom.split('.')[1];
      input.name = `parameter${item.rnumber}[]`;
      input.required = true;
      input.dataset.kolom = item.kolom;
      input.dataset.operator = item.operator;
      input.dataset.rnumber = item.rnumber;

      input.addEventListener('change', (e) => {
        this.parameter[`${item.rnumber}_${i}`] = {
          rnumber: item.rnumber,
          kolom: item.kolom,
          operator: item.operator,
          isi: e.target.value,
        };
        this.parameter[item.rnumber] = '';
        Object.keys(this.parameter).forEach((key) => {
          if (key.split('_')[0] === item.rnumber && key !== `${item.rnumber}`) {
            const param = this.parameter[key];
            if (param.operator === 'BETWEEN') {
              if (this.parameter[item.rnumber]) {
                this.parameter[item.rnumber] += ' AND ';
              }

              this.parameter[item.rnumber] += `(${param.kolom} ${param.operator} '${param.isi}' AND '${param?.isi2}')`;
            } else if (param.operator === 'NULL') {
              this.parameter[item.rnumber] = `${param.kolom} ${param.operator}`;
            } else if (param.operator === 'LIKE') {
              if (this.parameter[item.rnumber]) {
                this.parameter[item.rnumber] += ' AND ';
              }

              this.parameter[item.rnumber] += `${param.kolom} ${param.operator} '%${param.isi}%'`;
            } else {
              if (this.parameter[item.rnumber]) {
                this.parameter[item.rnumber] += ' AND ';
              } 

              this.parameter[item.rnumber] += `${param.kolom} ${param.operator} '${param.isi}'`;
            }
          }
        });
        this.fetchData(item.rnumber);
      });

      inputGroup.appendChild(inputGroupText);
      inputGroup.appendChild(input);

      if (item.operator === 'BETWEEN') {
        const inputGroupText2 = document.createElement('span');
        inputGroupText2.className = 'input-group-text';
        inputGroupText2.innerText = 'AND';

        const input2 = document.createElement('input');
        input2.className = 'form-control';
        input2.type = item.value;
        input2.placeholder = item.kolom.split('.')[1];
        input2.name = `parameter${item.rnumber}[]`;
        input2.required = true;
        input2.dataset.kolom = item.kolom;
        input2.dataset.operator = item.operator;
        input2.dataset.rnumber = item.rnumber;

        input2.addEventListener('change', (e) => {
          this.parameter[`${item.rnumber}_${i}`].isi2 = e.target.value;
          this.parameter[item.rnumber] = '';
          Object.keys(this.parameter).forEach((key) => {
            if (key.split('_')[0] === item.rnumber && key !== `${item.rnumber}`) {
              const param = this.parameter[key];
              if (param.operator === 'BETWEEN') {
                if (this.parameter[item.rnumber]) {
                  this.parameter[item.rnumber] += ' AND ';
                }

                this.parameter[item.rnumber] += `(${param.kolom} ${param.operator} '${param.isi}' AND '${param.isi2}')`;
              } else if (param.operator === 'NULL') {
                this.parameter[item.rnumber] = `${param.kolom} ${param.operator}`;
              } else if (param.operator === 'LIKE') {
                if (this.parameter[item.rnumber]) {
                  this.parameter[item.rnumber] += ' AND ';
                }

                this.parameter[item.rnumber] += `${param.kolom} ${param.operator} '%${param.isi}%'`;
              } else {
                if (this.parameter[item.rnumber]) {
                  this.parameter[item.rnumber] += ' AND ';
                } 

                this.parameter[item.rnumber] += `${param.kolom} ${param.operator} '${param.isi}'`;
              }
            }
          });
          this.fetchData(item.rnumber);
        });

        inputGroup.appendChild(inputGroupText2);
        inputGroup.appendChild(input2);
      }

      return inputGroup;
    }

    createBtnSubmit() {
      const form = document.getElementById('containerParameter');

      const container = document.createElement('div');
      container.className = 'col-12 u-margin-b-10';

      const btnSubmit = document.createElement('input');
      btnSubmit.className = 'mybutton';
      btnSubmit.type = 'submit';
      btnSubmit.value = 'Submit';

      container.appendChild(btnSubmit);
      form.appendChild(container);
    }
    
    fetchData(rnumber) {
      $.get(false, $.options.slave+"?switcher=data&rnumber="+rnumber+"&parameter="+encodeURIComponent(this.parameter[rnumber]), (e) => {
        const newData = e.response;
        this.oldData[rnumber] = newData;
        this.update(rnumber);
      });
    }

    update(rnumber) {
      const newData = this.oldData[rnumber];
      console.log("New Data:", newData);
      for (let i = 0; i < this.selectedChart.length; i++) {
        if (this.selectedChart[i].rnumber === rnumber) {
          if (this.chartInstances[this.selectedChart[i].id]) {
            this.chartInstances[this.selectedChart[i].id].destroy();
          }

          $.CGen.init(rnumber, newData.columns, newData.rows);
          $.CGen.ctx = document.getElementById('myChart'+this.selectedChart[i].id).getContext('2d');
          $.CGen.type = this.selectedChart[i].type;
          $.CGen.columnLabel = {
            id: 'columnLabel',
            value: this.selectedChart[i].kolomlabel
          }

          this.selectedChart[i].operation.split(',').forEach((item, index) => {
            $.CGen.operation[index] = {
              index: index,
              id: 'operation',
              value: item
            };
          });
          this.selectedChart[i].kolomdata.split(',').forEach((item, index) => {
            $.CGen.columnData[index] = {
              index: index,
              id: 'columnData',
              value: item
            };
          });

          $.CGen.generateSetup();
          this.chartInstances[this.selectedChart[i].id] = $.CGen.chart;
        }
      }
    }
  }

  $.PChart = $.PChart || new chart();
})();
