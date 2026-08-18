(() => {
  class chartGenerator {
    constructor() {
      this.operations = ['Sum', 'Average', 'Minimum', 'Maximum', 'Count(Distinct)', 'Count', '% Grand Total'];
      this.reset();
    }

    init(rnumber, query, data) {
      this.rnumber = rnumber;
      this.columns = query || [];
      this.datasets = data;
      this.ctx = document.getElementById('myChart');
      this.menu = document.getElementById('menu');
    }

    reset() {
      this.clearActiveButtons();
      this.clearMenu();
      this.destroyChart();

      this.chart = null;
      this.chartData = null;
      this.chartOptions = null;
      this.operation = [];
      this.columnLabel = null;
      this.labels = null;
      this.columnData = [];
      this.dataChart = null;
      this.type = null;
      this.columnDataLength = 0;
      this.title = null;
      this.con = null;
    }

    clearActiveButtons() {
      document.querySelectorAll('#menu .btn').forEach(btn => {
        btn.classList.remove('active');
      });
    }

    clearMenu() {
      while (this.menu?.childElementCount > 3) {
        this.menu.lastElementChild.remove();
      }
    }

    destroyChart() {
      if (this.chart) {
        this.chart.destroy();
      }
    }

    generateParameter(data) {
      $.get(false, $.options.slave+"?switcher=parameter&rnumber="+data.rnumber, (e) => {
        document.getElementById('errorParam').style.display = 'none';
        
        const form = document.getElementById('listParam');
        form.style.display = '';
        form.action += '&rnumber=' + data.rnumber;

        while (form.childElementCount > 1) {
          if (form.firstElementChild.id !== 'btnSubmit') {
            form.firstElementChild.remove();
          } else {
            break;
          }
        }
        
        const parameters = JSON.parse(e.response);
        parameters.reverse().forEach((parameter, i) => {
          const value = parameter.value;
          const nullx = parameter.operator.indexOf('NULL');
          const betweenx = parameter.operator.indexOf('BETWEEN');

          const container = document.createElement('div');
          container.className = 'input-group input-group-sm mb-3';

          const colOperator = document.createElement('span');
          colOperator.className = 'input-group-text';
          colOperator.textContent = parameter.kolom.split('.')[1] + ' ' + parameter.operator;

          let input = document.createElement('input');
          input.type = 'text';
          input.className = 'form-control';
          input.id = 'parameter' + i;
          input.name = 'parameters[]';
          input.required = true;
          if (value == 'Date' && nullx < 0 && betweenx < 0) {
            input.type = 'date';
						input.className = 'form-control form-control-sm pb-0 me-2';
          } else if (value == 'Number' && nullx < 0 && betweenx < 0) {
            input.type = 'number';
            input.setAttribute('onkeypress', 'return angka_doang(event);');
          } else if (value == 'Setup') {
            input = this.createSetupSelect(i);
          } else if (nullx > -1) {
            input.disabled = true;
            input.value = parameter.operator;
          } else if (betweenx > -1) {
            let input1 = input
            input1.id = 'parametera' + i;
            if (value == 'Number') {
              input1.type = input.type = 'number';
              input.setAttribute('onkeypress', 'return angka_doang(event);');
              input1.setAttribute('onkeypress', 'return angka_doang(event);');
            } else if (value == 'Date') {
              input1.type = input.type = 'date';
              input1.className = input.className = 'form-control form-control-sm pb-0 me-2';
            } else if (value == 'Setup') {
              input1 = input = this.createSetupSelect(i);
              input1.id = 'parametera' + i;
            }

            const textAnd = document.createElement('span');
            textAnd.className = 'input-group-text';
            textAnd.textContent = 'AND';

            container.insertAdjacentElement('beforeend', textAnd);
            container.insertAdjacentElement('beforeend', input1);
          }

          container.insertAdjacentElement('afterbegin', input);
          container.insertAdjacentElement('afterbegin', colOperator);
          form.insertAdjacentElement('afterbegin', container);
        });
      });
    }

		/**
		 * Function to create a select dropdown for Setup parameters
		 * @param {number} index - The parameter index
		 * @param {string} fieldName - The field name
		 * @param {string} idPrefix - Prefix for the select element ID
		 * @returns {HTMLElement} - The created select element
		 */
		createSetupSelect(index, idPrefix = 'parameter') {
			const select = document.createElement('select');
			select.className = 'form-select px-1 py-0';
			select.style.fontSize = '12px';
			select.id = idPrefix + index;
			select.name = 'parameters[]';
			select.required = true;
			
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
					optionElement.value = option.value;
					optionElement.textContent = option.name;
					select.appendChild(optionElement);
				});
			});
			
			return select;
		}

    generateMenu(type) {
      this.type = type;

      this.clearActiveButtons();
      document.getElementById(`btn${this.type}`).classList.add('active');

      this.clearMenu(); // Clear existing menu items
      this.createAxisSelectors();
      this.createActionButtons();
      this.generateChart(); // Create the chart
    }

    createAxisSelectors() {
      const axisType = this.type === 'bar' || this.type === 'stacked-bar' ? ['Y-Axis', 'X-Axis'] : ['X-Axis', 'Y-Axis'];

      switch (this.type) {
        case 'scatter':
          this.menu.appendChild(this.createSelectElement('columnLabel', '', 'Value', 'Select Value', this.columns, (e) => {
            this.columnLabel = {
              id: e.target.id,
              value: e.target.value
            };
            this.generateChart();
          }));

          axisType.forEach((axis, i) => {
            const container = this.createAxisContainer(i, axis);
            this.menu.appendChild(container);
          });

          this.columnDataLength = 1;

          break;
        case 'pie':
        case 'doughnut':
        case 'polar':
        case 'radar':
          this.menu.appendChild(this.createSelectElement('columnLabel', '', 'Legend', 'Select Legend', this.columns, (e) => {
            this.columnLabel = {
              id: e.target.id,
              value: e.target.value
            };
            this.generateChart();
          }));
      
          for (let i = 0; i <= this.columnDataLength; i++) {
            const container = this.createAxisContainer(i, 'Value');
            this.menu.appendChild(container);
          }
          
          this.createAddMoreLink('Value');

          break;
        default:
          this.menu.appendChild(this.createSelectElement('columnLabel', '', axisType[0], `Select ${axisType[0]}`, this.columns, (e) => {
            this.columnLabel = {
              id: e.target.id,
              value: e.target.value
            };
            this.generateChart();
          }));
      
          for (let i = 0; i <= this.columnDataLength; i++) {
            const container = this.createAxisContainer(i, axisType[1]);
            this.menu.appendChild(container);
          }
          
          this.createAddMoreLink(axisType[1]);

          break;
      }

      // if (this.type === 'scatter') {
      //   this.menu.appendChild(this.createSelectElement('columnLabel', '', 'Value', 'Select Value', this.columns, (e) => {
      //     this.columnLabel = {
      //       id: e.target.id,
      //       value: e.target.value
      //     };
      //     this.generateChart();
      //   }));

      //   axisType.forEach((axis, i) => {
      //     const container = this.createAxisContainer(i, axis);
      //     this.menu.appendChild(container);
      //   });

      //   this.columnDataLength = 1;
      // } else if (this.type === 'pie' || this.type === 'doughnut' || this.type === 'polar' || this.type === 'radar') {
      //   this.menu.appendChild(this.createSelectElement('columnLabel', '', 'Legend', 'Select Legend', this.columns, (e) => {
      //     this.columnLabel = {
      //       id: e.target.id,
      //       value: e.target.value
      //     };
      //     this.generateChart();
      //   }));
    
      //   for (let i = 0; i <= this.columnDataLength; i++) {
      //     const container = this.createAxisContainer(i, 'Value');
      //     this.menu.appendChild(container);
      //   }
        
      //   this.createAddMoreLink('Value');
      // } else {
      //   this.menu.appendChild(this.createSelectElement('columnLabel', '', axisType[0], `Select ${axisType[0]}`, this.columns, (e) => {
      //     this.columnLabel = {
      //       id: e.target.id,
      //       value: e.target.value
      //     };
      //     this.generateChart();
      //   }));
    
      //   for (let i = 0; i <= this.columnDataLength; i++) {
      //     const container = this.createAxisContainer(i, axisType[1]);
      //     this.menu.appendChild(container);
      //   }
        
      //   this.createAddMoreLink(axisType[1]);
      // }
    }

    createSelectElement(id, index, label, text, options, onChangeHandler) {
      // Create the container div
      const container = document.createElement('div');
      container.className = 'form-floating m-2';

      // Create the select element
      const selectElement = document.createElement('select');
      selectElement.id = id;
      selectElement.className = 'form-select';
      selectElement.setAttribute('index', index);
      selectElement.required = true;
    
      // Add the default option
      if (text != '') {
        const defaultOption = document.createElement('option');
        // defaultOption.value = '';
        defaultOption.selected = this[id] === null;
        defaultOption.textContent = text;
        selectElement.appendChild(defaultOption);
      }

      const columnDataLength = this.columnData.length
    
      // Add the options to the select element
      options.forEach(option => {
        const optionElement = document.createElement('option');
        optionElement.value = option;
        if (id === 'columnLabel' && this[id]?.value === option) {
          optionElement.selected = true;
        } else {
          for (let i = 0; i < columnDataLength; i++) {
            if (this[id][i]?.index == index && this[id][i]?.value == option) {
              optionElement.selected = true;
            }
          }
        }
        optionElement.textContent = option;
        selectElement.appendChild(optionElement);
      });
    
      // Add the event handler
      selectElement.addEventListener('change', onChangeHandler);

      // Create the label element
      const labelElement = document.createElement('label');
      labelElement.htmlFor = id;
      labelElement.textContent = label;

      // Append the label and select element to the container
      container.appendChild(selectElement);
      container.appendChild(labelElement);
    
      return container;
    }

    createAxisContainer(index, axisLabel) {
      const container = document.createElement('div');
      container.className = 'd-flex';
      container.id = `columnDataContainer${index}`;

      const axisSelector = this.createSelectElement('columnData', index, axisLabel, `Select ${axisLabel}`, this.columns, (e) => {
        this.columnData[index] = {
          index: index,
          id: e.target.id,
          value: e.target.value
        };
        this.generateChart();
      });
      axisSelector.className += ' w-75';

      const operationSelector = this.createSelectElement('operation', index, 'Operation', 'Select Operation', this.operations, (e) => {
        this.operation[index] = {
          index: index,
          id: e.target.id,
          value: e.target.value
        };
        this.generateChart();
      });

      container.append(axisSelector, operationSelector);

      return container;
    }

    createAddMoreLink(axis) {
      const addMoreLink = document.createElement('a');
      addMoreLink.textContent = 'Add More';
      addMoreLink.title = 'Add more parameter';
      addMoreLink.style.cursor = 'pointer';
      addMoreLink.className = 'm-2 link-secondary link-offset-2 link-underline link-underline-opacity-0';
      addMoreLink.onclick = () => {
        this.columnDataLength++;
        const newAxisContainer = this.createAxisContainer(this.columnDataLength, axis);
        this.menu.insertBefore(newAxisContainer, addMoreLink);
      };

      this.menu.appendChild(addMoreLink);
    }

    createActionButtons() {
      const buttons = document.createElement('div');
      buttons.className = 'd-flex justify-content-evenly mt-2';

      const saveBtn = this.createButton('Save', 'btn-success');
      const resetBtn = this.createButton('Reset', 'btn-danger');

      buttons.append(saveBtn, resetBtn);
      this.menu.appendChild(buttons);
    }

    createButton(text, className) {
      const button = document.createElement('button');
      button.className = `btn ${className}`;
      button.textContent = text;
      if (text === 'Save') {
        button.setAttribute('data-bs-toggle', 'modal');
        button.setAttribute('data-bs-target', '#saveModal');
      } else {
        button.addEventListener('click', this.reset.bind(this));
      }

      return button;
    }

    generateChart() {
      if (this.chart) {
        this.destroyChart();
      }
      
      this.generateSetup();
    }

    generateSetup() {
      this.chartData = this.processChartData();
      this.chartOptions = this.getChartOptions();

      const chartType = this.determineChartType();
      this.chart = new Chart(this.ctx, {
        type: chartType,
        data: this.chartData,
        options: this.chartOptions
      });
    }

    processChartData() {
      const labels = this.getLabels();
      let data = this.getData(labels);

      if (this.type === 'scatter' && data.length > 1) {
        let dataTemp = [];
        for (let i = 0; i < labels.length; i++) {
          dataTemp.push({ x: data[0][i], y: data[1][i] });
        }

        data = [dataTemp];
      }

      return {
        labels,
        datasets: data.map((d, i) => ({
          label: this.type === 'scatter' ? this.columnLabel?.value : this.columnData[i].value,
          data: d,
          ...((this.type === 'bar' || this.type === 'stacked-bar') && { axis: 'y' }),
          ...((this.type === 'area' || this.type === 'stacked-area') && { fill: { target: 'origin' } }),
          ...((this.type === 'line-column' || this.type === 'line-stacked-column') && { type: i === 0 ? 'line' : 'bar' }),
        })),
      };
    }

    getLabels() {
      return [...new Set(this.datasets?.map(dataset => dataset[this.columnLabel?.value]))];
    }

    getData(labels) {
      return this.operation.map((op, i) => labels.map(label => this.calculateOperation(op?.value, label, i)));
    }

    calculateOperation(op, label, i) {
      const data = this.datasets.filter(dataset => dataset[this.columnLabel?.value] === label);
      const values = data.map(d => parseFloat(d[this.columnData[i].value]));

      switch (op) {
        case 'Sum': return values.reduce((a, b) => a + b, 0);
        case 'Average': return values.reduce((a, b) => a + b, 0) / values.length;
        case 'Minimum': return Math.min(...values);
        case 'Maximum': return Math.max(...values);
        case 'Count(Distinct)': return new Set(values).size;
        case 'Count': return values.length;
        case '% Grand Total': return (values.reduce((a, b) => a + b, 0) / this.datasets.reduce((a, b) => a + parseFloat(b[this.columnData[i].value]), 0)) * 100;
        default: return 0;
      }
    }

    determineChartType() {
      const typeMap = { 
        area: 'line', 
        column: 'bar', 
        'line-column': 'bar',
        'line-stacked-column': 'bar',
        polar: 'polarArea',
        'stacked-area': 'line',
        'stacked-bar': 'bar',
        'stacked-column': 'bar'
      };

      return typeMap[this.type] || this.type;
    }

    getChartOptions() {
      return {
        ...((this.type === 'bar' || this.type === 'stacked-bar') && { indexAxis: 'y' }),
        scales: {
          x: { stacked: ['stacked-bar', 'stacked-column', 'stacked-area', 'line-stacked-column'].includes(this.type) },
          y: { stacked: ['stacked-bar', 'stacked-column', 'stacked-area', 'line-stacked-column'].includes(this.type) }
        },
        plugins: {
          title: {
            display: true,
            text: this.columnLabel?.value
          }
        },
        responsive: true,
      };
    }

    save() {
      const title = this.title;
      const kolomLabel = this.columnLabel?.value;
      const kolomData = this.columnData.map(cd => cd.value);
      const operation = this.operation.map(op => op.value);
      const type = this.type;
      const rnumber = this.rnumber;

      const tujuan = $.options.slave+`?switcher=save&rnumber=${rnumber}&title=${title}&kolomLabel=${kolomLabel}&kolomData=${kolomData}&operation=${operation}&type=${type}`;

      $.get(false, tujuan, (e) => {
        const response = JSON.parse(e.response);
        if (response.status === 'success') {
          $.refresh();
          $.redirect('master?page=chart_generator');
        } else {
          $.Alert('Error', response.message);
        }
      });
    }

    createXMLHttpRequest() {
      try {
        return new XMLHttpRequest();
      } catch (e) {
        try {
          return new ActiveXObject('Msxml2.XMLHTTP');
        } catch (e) {
          try {
            return new ActiveXObject('Microsoft.XMLHTTP');
          } catch (e) {
            alert('XMLHTTPRequest not supported by your browser!');
            return false;
          }
        }
      }
    }

    post(tujuan, param, functiontoexecute) {
      if (!this.isSaveResponse(param)) {
        alertify.alert("errorcode: Hindari penggunaan kata ERROR, WARNING dan GAGAL");
        throw Error('Stop!');
      }

      let par = parent.location.href.replace("http://", "");
      par = par.replace("https://", "");
      par = par.replace("#", "");
      param += '&par='+par;

      this.con.open('POST', tujuan, true);
      this.con.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
      this.con.onreadystatechange = eval(functiontoexecute);
      this.con.send(param);
    }

    respog() {
      if (this.con.readyState === 4) {
        if (this.con.status == 200) {
          if (!this.isSaveResponse(this.con.responseText)) {
            alert('ERROR\n' + this.con.responseText);
          } else {
            alert(this.con.responseText);
          }
        } else {
          this.error_catch(this.con.status);
        }
      }
    }

    isSaveResponse(response) {
      response = response.toUpperCase();
      if (response.includes('ERROR') || response.includes('GAGAL') || response.includes('WARNING')) {
        return false;
      } else {
        return true;
      }
    }

    error_catch(status) {
      switch (status) {
        case 203:
          alert('Dibutuhkan Authority');
          break;
        case 400:
          alert('Error Server');
          break;
        case 403:
          alert('Anda dilarang masuk');
          break;
        case 404:
          alert('File tidak ditemukan');
          break;
        case 405:
          alert('Method tidak diijinkan');
          break;
        case 407:
          alert('Proxy Error');
          break;
        case 408:
          alert('Permintaan terlalu lama');
          break;
        case 409:
          alert('Query Conflict');
          break;
        case 414:
          alert('ULI terlalu panjang');
          break;
        case 412:
          alert('Variable terlalu banyak');
          break;
        case 415:
          alert('Unsupported Media Type');
          break;
        case 500:
          alert('Server busy, try submit later');
          break;
        case 502:
          alert('Bad gateway');
          break;
        case 505:
          alert('Browser anda terlalu tua');	    
          break;
      }
    }

    generateParamView(data) {
      const parameters = JSON.parse(data);

      const form = document.createElement('div');
      form.className = 'col-xl-10 col-md-12 col-xs-12';

			const table = document.createElement('table');
			table.id = 'flyTable';
			table.className = 'table';

      const tbody = document.createElement('tbody');
      parameters.forEach((parameter, i) => {
        const value = parameter.value;
        const nullx = parameter.operator.indexOf('NULL');
        const betweenx = parameter.operator.indexOf('BETWEEN');

				const tr = document.createElement('tr');
				
				const tdColumn = document.createElement('td');
				tdColumn.style.fontWeight = 'bold';
				tdColumn.style.backgroundColor = '#21252900';
				tdColumn.style.borderWidth = '0px';
				tdColumn.style.verticalAlign = 'middle';
				tdColumn.setAttribute('value', parameter.kolom);
				tdColumn.textContent = parameter.kolom.split('.')[1];

				const tdOperator = document.createElement('td');
				tdOperator.style.padding = '0px 10px';
				tdOperator.style.backgroundColor = '#21252900';
				tdOperator.style.verticalAlign = 'middle';
				tdOperator.style.borderWidth = '0px';
				tdOperator.textContent = parameter.operator;

				const tdValue = document.createElement('td');
				tdValue.style.backgroundColor = '#21252900';
				tdValue.style.borderWidth = '0px';

				// Handle different parameter types
				if (value === 'Setup' && nullx < 0 && betweenx < 0) {
					// Create a select dropdown for Setup parameters
					const select = this.createSetupSelect(i);
					tdValue.appendChild(select);
				} else if (value === 'Text' && nullx < 0 && betweenx < 0) {
					const input = document.createElement('input');
					input.type = 'text';
					input.className = 'form-control inputParameter';
					input.style.fontSize = '12px';
					input.id = 'parameter' + i;
					input.name = 'parameters[]';
					input.required = true;
					tdValue.appendChild(input);
				} else if (value === 'Date' && nullx < 0 && betweenx < 0) {
					const input = document.createElement('input');
					input.type = 'date';
					input.className = 'form-control form-control-sm pb-0 inputParameter';
					input.style.fontSize = '12px';
					input.id = 'parameter' + i;
					input.name = 'parameters[]';
					input.required = true;
					
					tdValue.appendChild(input);
				} else if (value === 'Number' && nullx < 0 && betweenx < 0) {
					const input = document.createElement('input');
					input.type = 'number';
					input.className = 'form-control inputParameter';
					input.style.fontSize = '12px';
					input.id = 'parameter' + i;
					input.name = 'parameters[]';
					input.required = true;
					input.setAttribute('onkeypress', 'return angka_doang(event);');
					tdValue.appendChild(input);
				} else if (nullx > -1) {
					const input = document.createElement('input');
					input.type = 'text';
					input.className = 'form-control inputParameter';
					input.id = 'parameter' + i;
					input.name = 'parameters[]';
					input.disabled = true;
					input.value = param.operator;
					tdValue.appendChild(input);
				} else if (betweenx > -1) {
					const betweenContainer = document.createElement('div');
					betweenContainer.className = 'd-flex align-items-center';
					
					let input1 = document.createElement('input');
					input1.id = 'parameter' + i;
					input1.name = 'parameters[]';
					input1.required = true;
					input1.className = 'form-control inputParameter me-2';
					input1.style.fontSize = '12px';

					const andLabel = document.createElement('span');
					andLabel.className = 'mx-2';
					andLabel.textContent = 'AND';
					
					let input2 = document.createElement('input');
					input2.id = 'parametera' + i;
					input2.name = 'parameters[]';
					input2.required = true;
					input2.className = 'form-control inputParameter ms-2';
					input2.style.fontSize = '12px';
					
					if (value === 'Text') {
						input1.type = 'text';
						input2.type = 'text';
					} else if (value === 'Number') {
						input1.type = 'number';
						input2.type = 'number';
						input1.setAttribute('onkeypress', 'return angka_doang(event);');
						input2.setAttribute('onkeypress', 'return angka_doang(event);');
					} else if (value === 'Date') {
						input1.type = 'date';
						input2.type = 'date';
						input1.className = 'form-control form-control-sm pb-0 inputParameter me-2';
						input2.className = 'form-control form-control-sm pb-0 inputParameter ms-2';
					} else if (value === 'Setup') {
						// For BETWEEN with Setup, we'll use two select dropdowns
						input1 = this.createSetupSelect(i, 'parameter');
						input2 = this.createSetupSelect(i, 'parametera');
					}
					
					betweenContainer.appendChild(input1);
					betweenContainer.appendChild(andLabel);
					betweenContainer.appendChild(input2);
					tdValue.appendChild(betweenContainer);
        }
				
				tr.appendChild(tdColumn);
				tr.appendChild(tdOperator);
				tr.appendChild(tdValue);
				tbody.appendChild(tr);
      });

			table.appendChild(tbody);
			form.appendChild(table);

      return form;
    }
  }

  $.CGen = $.CGen || new chartGenerator();
})();
