(() => {
	class owlQueriesGenerator {
		constructor() {
			this.selectedChart = [];
			this.parameter = {};
			this.con = null;
			this.db = null;
			this.tableList = [];
		}

		/**
		 * Function to get the number of rows in the table.
		 * @returns {number} - The number of rows in the table.
		 */
		getRowCount() {
			return document.getElementById("table-container").children.length;
		}

		/**
		 * Function to add a join form to the table.
		 * @param {string} targetId - The ID of the target element.
		 * @returns {void}
		 * @description This function determines which row the action is on, gets the table name, and displays the join fields.
		 */
		addJoinForm(targetId) {
			// determine which row the action is on
			const idTable = targetId.charAt(targetId.length - 1);
			const numberOfRow = this.getRowCount();
			const tableNameElement = document.getElementById('tableList' + idTable);
			const tableName = tableNameElement.options[tableNameElement.selectedIndex].value;
			if (idTable > '1' && tableName != '') {
				const arrField1 = this.getFields(idTable, 'previous');
				const arrField2 = this.getFields(idTable, 'current');

				// create and display join fields
				const targetElement = document.getElementById(targetId);
				const containerDiv = this.createJoinFieldsContainer(idTable, arrField1, arrField2);

				targetElement.appendChild(document.createElement('br'));
				targetElement.appendChild(document.createElement('br'));
				targetElement.appendChild(document.createTextNode('Join Fields: '));
				targetElement.appendChild(containerDiv);
			}
		}

		/**
		 * Function to get the fields of the table.
		 * @param {string} idTable - The ID of the table.
		 * @param {string} type - The type of the table.
		 * @returns {Array} - An array of fields.
		 * @description This function gets the fields of the table.
		 */
		getFields(idTable, type) {
			const fields = [];

			const readSpans = (container) => {
				if (!container) return;
				const first = container.childNodes && container.childNodes[0];
				const list = first && first.childNodes ? first.childNodes : [];
				for (let i = 0; i < list.length; i++) {
					const node = list[i];
					if (node && node.tagName === 'SPAN') {
						const v = (node.innerText || node.textContent || '').trim();
						if (v) fields.push(v);
					}
				}
			};

			if (type === 'previous') {
				for (let i = 1; i < idTable; i++) {
					readSpans(document.getElementById('table' + i));
				}
			} else if (type === 'current') {
				readSpans(document.getElementById('table' + idTable));
			}

			return fields;
		}

		/**
		 * Function to create a join fields container.
		 * @param {string} idTable - The ID of the table.
		 * @param {Array} arrField1 - An array of fields.
		 * @param {Array} arrField2 - An array of fields.
		 * @returns {HTMLElement} - The created join fields container.
		 * @description This function creates a join fields container.
		 */
		createJoinFieldsContainer(idTable, arrField1, arrField2) {
			const containerDiv = document.createElement('div');
			containerDiv.className = 'rounded5';
			containerDiv.appendChild(document.createTextNode('[ '));

			const joinFields = [
				{ id: `join${idTable-1}a1`, options: arrField1 },
				{ id: `join${idTable-1}b1`, options: arrField2 },
				{ id: `join${idTable-1}a2`, options: arrField1, optional: true },
				{ id: `join${idTable-1}b2`, options: arrField2, optional: true },
				{ id: `join${idTable-1}a3`, options: arrField1, optional: true },
				{ id: `join${idTable-1}b3`, options: arrField2, optional: true }
			];

			joinFields.forEach((field, index) => {
				if (index % 2 == 0 && index != 0) {
					containerDiv.appendChild(document.createElement('br'));
					containerDiv.appendChild(document.createElement('br'));
				}

				const selectElement = this.createSelectElement(
					field.id,
					index < 2 ? '' : 'Optional',
					field.options,
					() => {}
				);
				selectElement.classList.add('d-inline');
				selectElement.style.width = '45%';

				containerDiv.appendChild(selectElement);
				if (index % 2 == 0) {
					containerDiv.appendChild(document.createTextNode(' = '));
				} else if (index < joinFields.length - 1) {
					containerDiv.appendChild(document.createTextNode(' AND '));
				}
			});

			containerDiv.appendChild(document.createTextNode(' ]'));

			return containerDiv;
		}
		
		/**
		 * Function to get the fields of the table.
		 * @param {string} tablename - The name of the table.
		 * @param {string} targetId - The ID of the target element.
		 * @returns {void}
		 * @description This function processes the request to get the fields of the table and displays them.
		 */
		getThisField(tablename, targetId) {
			const idTable = targetId.charAt(targetId.length - 1);
			const numberOfRow = this.getRowCount();
			let status = true;
		
			// cache DOM elements
			const tableList = document.getElementById('tableList' + idTable);
			const table = document.getElementById('table' + idTable);
			const myTable = document.getElementById('myTable');
			
			// make sure not choose the same field
			for (let i = 1; i <= numberOfRow; i++) {
				if (i != idTable) {
					const otherTableList = document.getElementById('tableList' + i);
					const selectedOption = otherTableList.options[otherTableList.selectedIndex].value;
					if (tablename === selectedOption && tablename !== '') {
						$.Alert("Table in Use", 'Table '+tablename+' used in row '+i);
		
						tableList.options[0].selected = true;
						table.innerHTML = '';
						status = false;
		
						break;
					}
				}
			}
		
			if (status) {
				// process the request
				$.get(false, $.options.slave+"?switcher=getFields&tablename="+tablename+"&db="+this.db, (e) => {
					const response = typeof(e.response) === 'string' ? JSON.parse(e.response) : e.response;
					const fields = response.map(field => field.Field);
					const targetElement = document.getElementById(targetId);
					targetElement.innerHTML = '';
					if (targetElement) {
						const columnCardElement = this.createColumnCardElement(fields, tablename, idTable);
						targetElement.appendChild(columnCardElement);
					}

					if (tablename != '') {
						this.addJoinForm(targetId);
					}
				});
			}
		
			// clear rows below if current table name is empty
			if (tablename === '') {
				for (let i = numberOfRow - 1; i >= parseInt(idTable) - 1; i--) {
					if (i !== 0) {
						myTable.deleteRow(i);
					}
				}
			}
		}

		/**
		 * Function to allow the drop event.
		 * @param {Event} e - The event object.
		 * @returns {void}
		 */
		allowDrop(e) {
			e.preventDefault();
		}
		
		/**
		 * Function to handle the drag event.
		 * @param {Event} e - The event object.
		 * @returns {void}
		 */
		drag(e) {
			e.dataTransfer.setData("text", e.target.id);
		}

		/**
		 * Function to handle the drop event.
		 * @param {Event} e - The event object.
		 * @returns {void}
		 */
		drop(e) {
			e.preventDefault();
			const data = e.dataTransfer.getData("text");
			const draggedElement = document.getElementById(data);
			const targetId = e.target.id;

			// Check if the target is columnList
			if (targetId === "columnList" || e.target.closest("#columnList")) {
				if (draggedElement.parentElement.id === "columnList") {
					checkDropArea();
				} else {
					// Clone the element for columnList
					const clonedElement = draggedElement.cloneNode(true);
					clonedElement.id = `${draggedElement.id}-copy-${Date.now()}`; // Ensure unique ID for the clone
					clonedElement.setAttribute('ondragstart', '$.QGen.drag(event);'); // Retain drag functionality
					e.target.appendChild(clonedElement);
				}

				if (!document.getElementById('delete')) {
					// Create delete area if it doesn't exist
					this.createDeleteColumnElement();
				}
			} else if (targetId.startsWith("container-card-") || e.target.closest(".field-container")) {
        // Prevent dropping from columnList back to table
        if (draggedElement.parentElement.id === "columnList") {
					return false;
        }

				checkDropArea();
			} else if (targetId === "delete" || e.target.closest("#delete")) {
				if (draggedElement.parentElement.id.startsWith("container-card-")) {
					// Prevent dropping from columnList back to table
					return false;
				}
				// Remove the element if dropped on delete area
				draggedElement.remove();
			} else {
				return false;
			}

			function checkDropArea() {
				if (e.target.classList.contains('draggable-field')) {
					// If the target is a draggable field, drop the element after it
					e.target.parentElement.insertBefore(draggedElement, e.target.nextSibling);
				} else {
					// Move the original element if dropped on a table
					e.target.appendChild(draggedElement);
				}
			}
		}

		/**
		 * Function to create the delete column element.
		 * @returns {void}
		 */
		createDeleteColumnElement() {
			const columnCollectorLabel = document.getElementById('columnCollectorLabel');

			// Create delete area
			const deleteArea = document.createElement('div');
			deleteArea.id = 'delete';
			deleteArea.className = 'text-danger cursor-pointer';
			deleteArea.innerHTML = '<i class="fa fa-trash"></i> Drop Here to Delete';
			deleteArea.setAttribute('ondrop', '$.QGen.drop(event);');
			deleteArea.setAttribute('ondragover', '$.QGen.allowDrop(event);');

			// Insert the delete area above columnList
			columnCollectorLabel.appendChild(deleteArea);
		}

		/**
		 * Function to hide an element by its ID.
		 * @param {string} id - The ID of the element to hide.
		 * @returns {void}
		 */
		hideById(id) {
			$.getElementById(id).style.display = 'none';
		}

		/**
		 * Function to show an element by its ID.
		 * @param {string} id - The ID of the element to show.
		 * @returns {void}
		 */
		showById(id) {
			$.getElementById(id).style.display = '';
		}

		/**
		 * Function to create a select element with options and an event handler.
		 * @param {string} id - The ID of the select element.
		 * @param {Array} options - An array of options to be added to the select element.
		 * @param {Function} onChangeHandler - The event handler function for the change event.
		 * @returns {HTMLElement} - The created select element.
		 */
		createSelectElement(id, text, options, onChangeHandler) {
			const selectElement = document.createElement('select');
			selectElement.id = id;
			selectElement.className = 'form-select p-1 pt-0';
			selectElement.style.fontSize = '12px';

			if (text) {
				const defaultOption = document.createElement('option');
				defaultOption.value = '';
				defaultOption.textContent = text;
				selectElement.appendChild(defaultOption);
			}
		
			options.forEach(option => {
				const optionElement = document.createElement('option');
				optionElement.value = option;
				optionElement.textContent = option;
				selectElement.appendChild(optionElement);
			});
		
			selectElement.addEventListener('change', onChangeHandler);
			return selectElement;
		}

		/**
		 * Function to create a column card element.
		 * @param {Array} fields - An array of column names.
		 * @param {string} tableName - The name of the table.
		 * @param {string} rowNumber - The row number of the table.
		 * @returns {HTMLElement} - The created column card element.
		 */
		createColumnCardElement(fields, tableName, rowNumber) {
			const container = document.createElement('div');
			container.id = `container-card-${rowNumber}`;
			// container.className = 'container-column-card';
			container.className = 'd-flex flex-wrap gap-1';
			container.setAttribute('ondrop', '$.QGen.drop(event); $.QGen.generateParameter();');
			container.setAttribute('ondragover', '$.QGen.allowDrop(event);');
			
			const bgColors = ['bg-success', 'bg-primary', 'bg-info', 'bg-danger'];
			const colorClass = bgColors[(rowNumber - 1) % 4];
			// Create the column card element
			fields.forEach(field => {
				const columnCardElement = document.createElement('span');
				columnCardElement.id = `${tableName}${field}`;
				// columnCardElement.className = `myButton${rowNumber}`;
				columnCardElement.className = `btn btn-sm text-white draggable-field ${colorClass}`;
				columnCardElement.style.fontSize = '12px';
				columnCardElement.draggable = true;
				columnCardElement.setAttribute('ondragstart', '$.QGen.drag(event);');
				columnCardElement.setAttribute('contenteditable', 'false');
				columnCardElement.textContent = `${tableName}.${field}`
				container.appendChild(columnCardElement);
			})
		
			return container;
		}

		/**
		 * Function to generate the parameter.
		 * @returns {void}
		 * @description This function generates the parameter.
		 */
		generateParameter() {
			// clear the caption element
			const captionElement = document.getElementById('caption');
			captionElement.innerHTML = '';

			const selectedColumns = document.getElementById('columnList').childNodes;

			const container = document.createElement('div');
			container.className = 'row g-3 align-items-end';
			container.id = 'captionDisplay';

			selectedColumns.forEach((col, i) => {
				const colWrapper = document.createElement('div');
				colWrapper.className = 'col-lg-3 col-md-4 col-sm-6';

				const valueInput = col.textContent.includes('(') ? col.textContent : col.textContent.split('.')[1];

				const card = document.createElement('div');
				card.className = 'card h-100';

				const cardHeader = document.createElement('div');
				cardHeader.id = 'column' + i;
				cardHeader.className = 'card-header p-1 bg-primary text-white';
				cardHeader.textContent = col.textContent;

				const cardBody = document.createElement('div');
				cardBody.className = 'card-body p-2';
				
				const headerCaption = document.createElement('div');
				headerCaption.className = 'mb-2';

				const inputCaption = document.createElement('input');
				inputCaption.type = 'text';
				inputCaption.className = 'form-control form-control-sm';
				inputCaption.id = 'caption' + i;
				inputCaption.value = valueInput;
				
				headerCaption.appendChild(inputCaption);

				cardBody.appendChild(headerCaption);

				const groupCheckbox = document.createElement('div');
				groupCheckbox.className = 'form-check form-switch';

				const inputGroup = document.createElement('input');
				inputGroup.className = 'form-check-input';
				inputGroup.type = 'checkbox';
				inputGroup.id = 'group' + i;
				inputGroup.onchange = (e) => { this.protectGroup(e, i); };
				
				const labelGroup = document.createElement('label');
				labelGroup.className = 'form-check-label';
				labelGroup.setAttribute('for', 'group' + i);
				labelGroup.textContent = 'Group By';
				
				groupCheckbox.appendChild(inputGroup);
				groupCheckbox.appendChild(labelGroup);
				
				cardBody.appendChild(groupCheckbox);

				const subtotalCheckbox = document.createElement('div');
				subtotalCheckbox.className = 'form-check form-switch';
				
				const inputSubtotal = document.createElement('input');
				inputSubtotal.className = 'form-check-input';
				inputSubtotal.type = 'checkbox';
				inputSubtotal.id = 'subtotal' + i;
				inputSubtotal.onchange = (e) => { this.protectSubtotal(e, i); };
				
				const labelSubtotal = document.createElement('label');
				labelSubtotal.className = 'form-check-label';
				labelSubtotal.setAttribute('for', 'subtotal' + i);
				labelSubtotal.textContent = 'Subtotal';
				
				subtotalCheckbox.appendChild(inputSubtotal);
				subtotalCheckbox.appendChild(labelSubtotal);
				
				cardBody.appendChild(subtotalCheckbox);
				
				const orderCheckbox = document.createElement('div');
				orderCheckbox.className = 'form-check form-switch';
				
				const inputOrder = document.createElement('input');
				inputOrder.className = 'form-check-input';
				inputOrder.type = 'checkbox';
				inputOrder.id = 'order' + i;
				inputOrder.onchange = (e) => { this.protectOrder(e, i); };
				
				const labelOrder = document.createElement('label');
				labelOrder.className = 'form-check-label';
				labelOrder.setAttribute('for', 'order' + i);
				labelOrder.textContent = 'Order By';
				
				orderCheckbox.appendChild(inputOrder);
				orderCheckbox.appendChild(labelOrder);
				
				cardBody.appendChild(orderCheckbox);

				card.appendChild(cardHeader);
				card.appendChild(cardBody);
				
				colWrapper.appendChild(card);

				container.appendChild(colWrapper);
			});

			captionElement.appendChild(container);
		}

		/**
		 * Function to create an input element.
		 * @param {string} id - The ID of the input element.
		 * @param {string} value - The value of the input element.
		 * @returns {HTMLElement} - The created input element.
		 * @description This function creates an input element.
		 */
		createInputElement(id, value) {
			const inputElement = document.createElement('input');
			inputElement.type = 'text';
			inputElement.className = 'myinputtext';
			inputElement.id = id;
			inputElement.style.width = '95%';
			inputElement.setAttribute('value', value);

			return inputElement;
		}

		/**
		 * Function to create a checkbox element.
		 * @param {string} id - The ID of the checkbox element.
		 * @param {Function} onClickHandler - The event handler function for the click event.
		 * @returns {HTMLElement} - The created checkbox element.
		 * @description This function creates a checkbox element.
		 */
		createCheckboxElement(id, onClickHandler) {
			const checkbox = document.createElement('input');
			checkbox.type = 'checkbox';
			checkbox.id = id;
			checkbox.onclick = onClickHandler;

			return checkbox;
		}

		/**
		 * Function to protect the group.
		 * @param {HTMLElement} obj - The HTML element.
		 * @param {string} serial - The serial number.
		 * @returns {void}
		 * @description This function protects the group.
		 */
		protectGroup(obj, serial) {
			if (!obj.target.checked) {
				const subtotalElement = document.getElementById('subtotal' + serial);
				subtotalElement.checked = false;
			}
		}

		/**
		 * Function to protect the column.
		 * @param {HTMLElement} obj - The HTML element.
		 * @param {string} serial - The serial number.
		 * @returns {void}
		 * @description This function protects the column.
		 */
		protectSubtotal(obj, serial) {
			if (obj.target.checked) {
				const groupElement = document.getElementById('group' + serial);
				groupElement.checked = true
			}
		}

		/**
		 * Function to protect the order.
		 * @param {HTMLElement} obj - The HTML element.
		 * @param {string} serial - The serial number.
		 * @returns {void}
		 * @description This function protects the order.
		 */
		protectOrder(obj, serial) {
			// if (obj.target.checked) {
			// 	console.log('checked');
			// } else {
			// 	console.log('unchecked');
			// }
		}

		/**
		 * Function to generate new row.
		 * @returns {void}
		 */
		addNewRow() {
			if (document.getElementById('columnControl').style.display !== 'none') {
				$.Alert('Action Restricted', "Can't add new table while configuring columns. Please close the configuration panel first.");
				return;
			}

			const tableContainer = document.getElementById('table-container');
			const currentRowCount = tableContainer.querySelectorAll('.table-row').length;
			if (currentRowCount >= 4) {
				$.Alert("Maximum Tables Exceeded", 'You can join a maximum of 4 tables.');
				return;
			}
			
			const nextIndex = currentRowCount + 1;
			
			const newRow = document.createElement('div');
			newRow.className = 'row mb-3 table-row';
			newRow.id = `table-row-${nextIndex}`;

			// const prevSelect = document.getElementById('tableList' + currentRowCount);
			// console.log(prevSelect);
			// const tableNames = Array.from(prevSelect.options).map(opt => opt.value).filter(Boolean);

			const selectEl = this.createSelectElement(
				`tableList${nextIndex}`,
				'Select a table to join',
				this.tableList,
				(e) => { this.getThisField(e.target.value, `table${nextIndex}`); }
			);

			const cell1 = document.createElement('div');
			cell1.className = 'col-md-4';
			
			const label1 = document.createElement('label');
			label1.setAttribute('for', `tableList${nextIndex}`);
			label1.className = 'form-label';
			label1.textContent = 'Join Table';
			cell1.appendChild(label1);

			const tableListContainer = document.createElement('div');
			tableListContainer.id = `tableListContainer${nextIndex}`;
			cell1.appendChild(tableListContainer);

			const cell2 = document.createElement('div');
			cell2.className = 'col-md-8';

			const label2 = document.createElement('label');
			label2.className = 'form-label d-flex justify-content-between';

			const labelText = document.createElement('span');
			labelText.textContent = 'Fields ';

			// create the maximize and minimize link
			const maximizeLink = document.createElement('a');
			maximizeLink.onclick = () => {showById('table' + nextIndex)};
			maximizeLink.title = 'Maximize';
			maximizeLink.textContent = '+';

			const minimizeLink = document.createElement('a');
			minimizeLink.onclick = () => {hideById('table' + nextIndex)};
			minimizeLink.title = 'Minimize';
			minimizeLink.textContent = '-';

			labelText.appendChild(maximizeLink);
			labelText.appendChild(document.createTextNode(' / '));
			labelText.appendChild(minimizeLink);
			label2.appendChild(labelText);

			const removeLink = document.createElement('a');
			removeLink.href = '#';
			removeLink.onclick = () => {
				// const fieldContainer = newRow.querySelector('.field-container');
				// if (fieldContainer) {
				// 	fieldContainer.remove();
				// }

				const selectTable = document.getElementById('tableList' + nextIndex);
				if (selectTable.disabled) {
					$.Alert("Table is currently being configured", 'You cannot remove a table. Please reset the configuration panel first.');
					return false;
				}

				newRow.remove();
				return false;
			};
			removeLink.className = 'text-danger';
			removeLink.innerHTML = '<i class="fa fa-trash"></i> Remove Join';
			label2.appendChild(removeLink);

			cell2.appendChild(label2);

			const fieldContainer = document.createElement('div');
			fieldContainer.id = `table${nextIndex}`;
			fieldContainer.className = 'p-2 border rounded bg-light field-container';
			cell2.appendChild(fieldContainer);

			newRow.appendChild(cell1);
			newRow.appendChild(cell2);

			newRow.querySelector(`#tableListContainer${nextIndex}`).appendChild(selectEl);
			tableContainer.appendChild(newRow);
		}

		/**
		 * Function to close the form dialogue.
		 * @returns {void}
		 */
		closeFormDialogue() {
			try {
				const dynamic3 = document.getElementById('dynamic3');
				dynamic3?.remove();
			} catch (err) {
				console.error(err)
			}
		}
		
		/**
		 * Function to configure the column.
		 * @returns {void}
		 * @description This function configures the column.
		 */
		configureColumn() {
			this.closeFormDialogue();

			const columnControl = document.getElementById('columnControl');
			const tableList1 = document.getElementById('tableList1');
			if (columnControl.style.display == '') {
				$.Confirm('Configured column will be discharges, are you sure?', () => {
					columnControl.style.display = 'none';
					const selectedTable = tableList1.options[tableList1.selectedIndex].value;
					this.getThisField(selectedTable, 'table1');
					this.enableTable();
					this.clearColumnConfig();
				});
			} else {
				if (this.allClear()) {
					this.disableTable();
					this.loadCondition();
					this.loadFunction();
					columnControl.style.display = '';
				}
			}
		}

		/**
		 * Function to load the function operations.
		 * @returns {void}
		 */
		loadFunction() {
			const container = document.getElementById('funcOpr');
			container.innerHTML = '';
			
			const numberOfRow = this.getRowCount();
			const fields = [];
			for (let i = 1; i <= numberOfRow; i++) {
				const table = document.getElementById('table' + i).childNodes[0].childNodes;
				for (let j = 0; j < table.length; j++) {
					if (table[j].tagName == 'SPAN') {
						fields.push(table[j].textContent);
					}
				}
			}

			const funtionOptions = ['AVG', 'COUNT', 'COUNT (DISTINCT)', 'MAX', 'MIN', 'SUBSTR', 'SUM'];
			const functionSelect = this.createFunctionSelectElement(
				'function',
				'Select a function',
				funtionOptions,
				(e) => {
					this.handlerSelectFunction(e.target.value);
					this.generateParameter();
					e.target.options[0].selected = true;
				}
			);
			functionSelect.classList.add('col-6')
			
			const orderOptions = ['ASC', 'DESC'];
			const orderSelect = this.createFunctionSelectElement(
				'orderby',
				'Select an order',
				orderOptions,
				() => {}
			);
			orderSelect.classList.add('col-6');

			container.appendChild(functionSelect);
			container.appendChild(orderSelect);
		}

		/**
		 * Function to handle the selection of a function.
		 * @param {string} value - The selected function value.
		 * @returns {void}
		 */
		handlerSelectFunction(value) {
			const target = document.getElementById('columnList');
			
			if (value != '') {
				const cardElement = document.createElement('span');
				cardElement.id = `${value}`;
				cardElement.className = `myButtonF`;
				cardElement.draggable = true;
				cardElement.setAttribute('ondragstart', '$.QGen.drag(event);');
				
				const textNode = document.createTextNode(`${value}(`);
				if (value == 'COUNT (DISTINCT)') {
					textNode.textContent = 'COUNT(DISTINCT ';
				}

				const droppedElement = document.createElement('div');
				droppedElement.id = `${value}param`;
				droppedElement.style.minWidth = '50px';
				droppedElement.style.minHeight = '20px';
				droppedElement.style.margin = '0px 10px';
				droppedElement.setAttribute('ondrop', '$.QGen.drop(event);');
				droppedElement.setAttribute('ondragover', '$.QGen.allowDrop(event);');
				cardElement.setAttribute('contenteditable', 'true');
				cardElement.setAttribute('onblur', '$.QGen.generateParameter();');

				const textNode2 = document.createTextNode(')');

				cardElement.appendChild(textNode);
				cardElement.appendChild(droppedElement);
				cardElement.appendChild(textNode2);

				target.appendChild(cardElement);
			}
		}

		/**
		 * Function to create a select checkbox element.
		 * @param {string} id - The ID for the select element.
		 * @param {string} placeholder - The placeholder text.
		 * @param {Array<string>} options - The options for the select element.
		 * @returns {HTMLElement} - The created select checkbox element.
		 */
		createSelectCheckboxElement(id, placeholder, options) {
			// Create the wrapper div
			const wrapper = document.createElement('div');
			wrapper.className = 'select-radio-wrapper';
			
			// Create the "select" button
			const selectButton = document.createElement('button');
			selectButton.id = id;
			selectButton.type = 'button';
			selectButton.style.cursor = 'pointer';
  		selectButton.className = 'form-control form-control-sm text-start dropdown-toggle';
			selectButton.style.fontSize = '12px';
			selectButton.style.width = 'auto';
			selectButton.style.minHeight = '0px';
			selectButton.dataset.value = '';

			const textBtn = document.createTextNode(placeholder || 'Select an option');
			selectButton.appendChild(textBtn);
			
			wrapper.appendChild(selectButton);

			// Create the dropdown container
			const dropdown = document.createElement('div');
  		dropdown.classList.add('dropdown-menu');
			dropdown.style.display = 'none';
			dropdown.style.maxHeight = '200px';
			dropdown.style.overflowY = 'auto';
			dropdown.style.zIndex = '1000';
			dropdown.style.position = 'absolute';
			dropdown.style.backgroundColor = '#fff';
			dropdown.style.border = '1px solid #ccc';
			dropdown.style.borderRadius = '4px';
			dropdown.style.padding = '8px';
			wrapper.appendChild(dropdown);

			// Add radio buttons to the dropdown
			const radioName = `radio-${id}`;

			// Add checkboxes to the dropdown
			options.forEach((option, index) => {
				const checkboxWrapper = document.createElement('div');
				checkboxWrapper.className = 'form-check mb-1';

				const checkbox = document.createElement('input');
				checkbox.type = 'radio';
				checkbox.className = 'form-check-input';
				checkbox.name = radioName;
				checkbox.id = `${id}-option-${index}`;
				checkbox.value = option;

				checkbox.addEventListener('change', () => {
					if (checkbox.checked) {
						selectButton.value = option;
						selectButton.textContent = option;
						selectButton.dataset.value = option;
						// Close dropdown after selection
						dropdown.style.display = 'none';
					}
				});

				const label = document.createElement('label');
				label.className = 'form-check-label';
				label.setAttribute('for', `${id}-option-${index}`);
				label.textContent = option;

				checkboxWrapper.appendChild(checkbox);
				checkboxWrapper.appendChild(label);
				dropdown.appendChild(checkboxWrapper);
			});

			// Add event listener to toggle dropdown visibility
			selectButton.addEventListener('click', () => {
				dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
			});

			// Close the dropdown when clicking outside
			document.addEventListener('click', (event) => {
				if (!wrapper.contains(event.target)) {
					dropdown.style.display = 'none';
				}
			});

			return wrapper;
		}

		/**
		 * Function to create a function select element.
		 * @param {string} id - The ID for the select element.
		 * @param {string} text - The label text.
		 * @param {Array<string>} options - The options for the select element.
		 * @param {Function} onChangeHandler - The change event handler.
		 * @returns {HTMLElement} - The created function select element.
		 */
		createFunctionSelectElement(id, text, options, onChangeHandler) {
			const container = document.createElement('div');

			const labelText = document.createElement('p');
			labelText.textContent = `${id.toUpperCase().split('')[0]}${id.slice(1)}: `;
			labelText.style.marginTop = '0px';
			labelText.style.marginBottom = '5px';

			const selectElement = id == 'function' ? this.createSelectElement(id, text, options, onChangeHandler) : this.createSelectCheckboxElement(id, text, options);
			
			container.appendChild(labelText);
			container.appendChild(selectElement);
			
			return container;
		}

		/**
		 * Function to load the condition.
		 */
		loadCondition() {
			this.clearCondition();
			
			const numberOfRow = this.getRowCount();
			const conditionElement = document.getElementById('condition');
			const fields = [];
			for (let i = 1; i <= numberOfRow; i++) {
				const table = document.getElementById('table' + i).childNodes[0].childNodes;
				for (let j = 0; j < table.length; j++) {
					if (table[j].tagName == 'SPAN') {
						fields.push(table[j].textContent);
					}
				}
			}

			// create the first select element
			const selectElement1 = this.createSelectElement(
				'condition1',
				'Select a field',
				fields,
				() => {}
			);

			// create the second select element
			const selectElement2 = this.createSelectElement(
				'operator1',
				'Select an operator',
				['=', '!=', '>', '<', '>=', '<=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'IS NULL', 'IS NOT NULL'],
				() => {}
			);

			// create the third select element
			const selectElement3 = this.createSelectElement(
				'parameter1',
				'Select a parameter',
				['Text', 'Number', 'Date', 'Setup'],
				() => {}
			);

			// create the table element
			const tableElement = document.createElement('table');
			tableElement.id = 'parameter';
			tableElement.style.width = '100%';

			// create the first row
			const row1 = tableElement.insertRow(0);
			row1.insertCell(0).textContent = 'Condition 1: ';
			row1.insertCell(1).appendChild(selectElement1);
			row1.insertCell(2).appendChild(selectElement2);
			row1.insertCell(3).appendChild(selectElement3);

			// append the table to the condition element
			conditionElement.appendChild(tableElement);

			// create the "Add More" link
			const addMoreLink = document.createElement('a');
			const addMoreText = document.createTextNode('Add More');
			addMoreLink.appendChild(addMoreText);
			addMoreLink.title = 'Add more parameter';
			addMoreLink.style.cursor = 'pointer';
			addMoreLink.onclick = () => {this.addMoreParameter()};
			addMoreLink.style.display = 'block';
			addMoreLink.style.fontWeight = 'bold';
			addMoreLink.style.textAlign = 'center';
			addMoreLink.style.paddingTop = '20px';
			addMoreLink.style.paddingBottom = '5px';
			addMoreLink.style.width = 'fit-content';
			addMoreLink.style.margin = '0 auto';

			// append the link to the condition element
			conditionElement.appendChild(addMoreLink);
		}

		/**
		 * Function to add more parameter.
		 * @returns {void}
		 * @description This function adds more parameter.
		 */
		addMoreParameter() {
			const table = document.getElementById('parameter');
			const parameterRow = table.rows.length;
			const prevCon = document.getElementById('condition1');
			if (parameterRow >= prevCon.length) {
				$.Alert("Maximum Condition Exceeded", 'Reached maximum condition');
			} else {
				const row = table.insertRow(parameterRow);

				// create and append the first cell
				let cell1 = row.insertCell(0);
				cell1.textContent = 'Condition ' + (parameterRow + 1) + ': ';

				// create and append the second cell
				let cell2 = row.insertCell(1);
				let conditionClone = prevCon.cloneNode(true);
				conditionClone.id = 'condition' + (parameterRow + 1);
				cell2.appendChild(conditionClone);

				// create and append the third cell
				let cell3 = row.insertCell(2);
				let operatorClone = document.getElementById('operator1').cloneNode(true);
				operatorClone.id = 'operator' + (parameterRow + 1);
				cell3.appendChild(operatorClone);

				// create and append the fourth cell
				let cell4 = row.insertCell(3);
				let parameterClone = document.getElementById('parameter1').cloneNode(true);
				parameterClone.id = 'parameter' + (parameterRow + 1);
				cell4.appendChild(parameterClone);
			}
		}

		/**
		 * Function to disable the table.
		 * @returns {void}
		 * @description This function disables the table.
		 */
		disableTable() {
			const numberOfRow = this.getRowCount();
			for (let i = 1; i <= numberOfRow; i++) {
				document.getElementById('tableList' + i).disabled = true;
			}
		}

		/**
		 * Function to check if all rows have correct options.
		 * @returns {string} - The status of the check.
		 * @description This function checks if all rows have correct options.
		 */
		allClear() {
			// verify all rows have correct options
			const numberOfRow = this.getRowCount();
			const status = true;
			for (let i = 1; i <= numberOfRow; i++) {
				// table name checker
				const tableList = document.getElementById('tableList' + i);
				let selectedTable = tableList.options[tableList.selectedIndex].value;
				if (selectedTable == '') {
					$.Alert("Missing Table Reference", 'Table on row number ' + i + ' required');
					status = false;
					break;
				} else {
					// join table checker
					if (i < numberOfRow) {
						for (let j = 1; j <= 3; j++) {
							const joinA = document.getElementById('join' + i + 'a' + j);
							const joinB = document.getElementById('join' + i + 'b' + j);
							const joinAValue = joinA?.options[joinA.selectedIndex].value;
							const joinBValue = joinB?.options[joinB.selectedIndex].value;
							if ((joinAValue == '' && joinBValue != '') || (joinAValue != '' && joinBValue == '')) {
								$.Alert("Invalid Join Condition", 'Join table condition incorrect on row number ' + (i+1));
								status = false;
								break;
							}
						}
					}
				}
			}

			return status;
		}

		/**
		 * Function to clear the column configuration.
		 * @returns {void}
		 * @description This function clears the column configuration.
		 */
		clearColumnConfig() {
			document.getElementById('columnList').innerHTML = '';
			document.getElementById('judul').value = '';
			document.getElementById('caption').innerHTML = '';
			this.clearCondition();
		}

		/**
		 * Function to clear the column configuration.
		 * @returns {void}
		 */
		clearCondition() {
			document.getElementById('condition').innerHTML = '';
		}

		/**
		 * Function to enable the table.
		 * @returns {void}
		 */
		enableTable() {
			const numberOfRow = this.getRowCount();
			for (let i = 1; i <= numberOfRow; i++) {
				document.getElementById('tableList' + i).disabled = false;
			}
		}

		/**
		 * Function to create the preview query.
		 * @param {Event} e - The event object.
		 * @returns {void}
		 * @description This function creates the preview query.
		 */
		previewQuery() {
			// 1. get db
			const db = document.getElementById('dbList').value;
			if (!db) {
				$.Alert("Missing Database", "Please select a database first");
				return;
			}

			// 2. get table
			const tableAndJoin = this.getTableAndJoin();
			if (!tableAndJoin || !tableAndJoin[0] || !tableAndJoin[0].length) {
				$.Alert("Missing Table", "Please select at least one table");
				return;
			}
			const join	= tableAndJoin[1].join(',');
			const table = tableAndJoin[0].toString();

			// 3. get judul laporan
			const title = this.getTitle();
			if (!title) {
				$.Alert("Missing Title", "Please enter a title for the report");
				return;
			}

			// 4. get column display
			const columnDisplay = this.getColumnDisplay();
			const select = columnDisplay[0].join('|');
			const columnName = columnDisplay[1].join('|');
			const group = columnDisplay[2].join('|');
			const subtotal = columnDisplay[3].join('|');
			const order = columnDisplay[4].join('|');

			// 5. get condition
			const condition = this.getCondition();
			const format = [];
			const operator = [];
			const field = [];
			for (let i = 0; i < condition.length; i++) {
				format.push(condition[i].parameter);
				operator.push(condition[i].operator);
				field.push(condition[i].condition);
			}

			// 6. get sort
			const sort = document.getElementById('orderby').value;

			if (columnDisplay && condition && title && db) {
				let winUpdate;

				const file = this.displayPreview(condition);
				const tujuan = $.options.slave+`?switcher=preview&dbname=${db}&table=${table}&join=${join}&judul=${title}&kolomTampil=${columnName}&grouping=${group}&subtotal=${subtotal}&order=${order}&kolomSelect=${select}&format=${format}&operator=${operator}&field=${field}&sort=${sort}`;

				let options = {
					url: tujuan,
					title: title,
					success: () => {
						winUpdate.target.body.querySelector('#listParam').insertAdjacentElement('afterbegin', file);
					}
				};

				winUpdate = $.openWindow(options);
			}
		}
		
		/**
		 * Function to display the preview of the query.
		 * @param {Array} data - The data to display in the preview.
		 * @returns {HTMLElement} - The created preview element.
		 */
		displayPreview(data) {
			const container = document.createElement('div');
			container.className = 'col-xl-10 col-md-12 col-xs-12';

			const table = document.createElement('table');
			table.id = 'flyTable';
  		table.className = 'table';

			const tbody = document.createElement('tbody');
			data.forEach((param, i) => {
				const value = param.parameter;
				const nullx = param.operator.indexOf('NULL');
				const betweenx = param.operator.indexOf('BETWEEN');

				const tr = document.createElement('tr');
				
				const tdColumn = document.createElement('td');
				tdColumn.style.fontWeight = 'bold';
				tdColumn.style.backgroundColor = '#21252900';
				tdColumn.style.borderWidth = '0px';
				tdColumn.style.verticalAlign = 'middle';
				tdColumn.setAttribute('value', param.condition);
				tdColumn.textContent = param.condition.split('.')[1];

				const tdOperator = document.createElement('td');
				tdOperator.style.padding = '0px 10px';
				tdOperator.style.backgroundColor = '#21252900';
				tdOperator.style.verticalAlign = 'middle';
				tdOperator.style.borderWidth = '0px';
				tdOperator.textContent = param.operator;

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
					input.id = 'frmparam' + i;
					input.name = 'frmparam[]';
					input.required = true;
					tdValue.appendChild(input);
				} else if (value === 'Date' && nullx < 0 && betweenx < 0) {
					const input = document.createElement('input');
					input.type = 'date';
					input.className = 'form-control form-control-sm pb-0 inputParameter';
					input.style.fontSize = '12px';
					input.id = 'frmparam' + i;
					input.name = 'frmparam[]';
					input.required = true;
					
					tdValue.appendChild(input);
				} else if (value === 'Number' && nullx < 0 && betweenx < 0) {
					const input = document.createElement('input');
					input.type = 'number';
					input.className = 'form-control inputParameter';
					input.style.fontSize = '12px';
					input.id = 'frmparam' + i;
					input.name = 'frmparam[]';
					input.required = true;
					input.setAttribute('onkeypress', 'return angka_doang(event);');
					tdValue.appendChild(input);
				} else if (nullx > -1) {
					const input = document.createElement('input');
					input.type = 'text';
					input.className = 'form-control inputParameter';
					input.id = 'frmparam' + i;
					input.name = 'frmparam[]';
					input.disabled = true;
					input.value = param.operator;
					tdValue.appendChild(input);
				} else if (betweenx > -1) {
					const betweenContainer = document.createElement('div');
					betweenContainer.className = 'd-flex align-items-center';
					
					let input1 = document.createElement('input');
					input1.id = 'frmparam' + i;
					input1.name = 'frmparam[]';
					input1.required = true;
					input1.className = 'form-control inputParameter me-2';
					input1.style.fontSize = '12px';

					const andLabel = document.createElement('span');
					andLabel.className = 'mx-2';
					andLabel.textContent = 'AND';
					
					let input2 = document.createElement('input');
					input2.id = 'frmparama' + i;
					input2.name = 'frmparam[]';
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
						input1 = this.createSetupSelect(i, 'frmparam');
						input2 = this.createSetupSelect(i, 'frmparama');
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
			container.appendChild(table);

			return container;
		}

		/**
		 * Function to create a select dropdown for Setup parameters
		 * @param {number} index - The parameter index
		 * @param {string} fieldName - The field name
		 * @param {string} idPrefix - Prefix for the select element ID
		 * @returns {HTMLElement} - The created select element
		 */
		createSetupSelect(index, idPrefix = 'frmparam') {
			const select = document.createElement('select');
			select.className = 'form-select inputParameter px-1 py-0';
			select.style.fontSize = '12px';
			select.id = idPrefix + index;
			select.name = 'frmparam[]';
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

		/**
		 * Function to create a form dialogue.
		 * @param {string} title - The title of the form dialogue.
		 * @param {string} content - The content of the form dialogue.
		 * @param {string} width - The width of the form dialogue.
		 * @param {string} height - The height of the form dialogue.
		 * @param {Event} e - The event object.
		 * @returns {void}
		 * @description This function creates a form dialogue.
		 */
		formDialoque(id, title, content, width, height, e) {
			$.Alert(title, content);

			const slave = $.options.slave +'?switcher=new';
			$.newDialog(id, title, slave, width, height, e);
			
			this.closeFormDialogue();
			
			// create the main content
			const divElement = document.createElement('div');
			divElement.id = id;
			divElement.className = 'drag';
			divElement.style.position = 'absolute';
			divElement.style.display = 'none';
			divElement.style.width = width + 'px';
			divElement.style.paddingTop = '3px';
			$.Alert(divElement);

			// create the tittle bar
			const titleBar = document.createElement('div');
			const titleText = document.createElement('b');
			titleText.style.color = '#ffffff';
			titleText.textContent = title;
			titleBar.appendChild(titleText);

			// create the close button
			const closeBtn = document.createElement('img');
			closeBtn.src = 'images/closebig.gif';
			closeBtn.setAttribute('align', 'right');
			closeBtn.setAttribute('onclick', '$.QGen.closeFormDialogue();');
			closeBtn.title = 'Close detail';
			closeBtn.className = 'closebtn';
			closeBtn.setAttribute('onmouseover', 'this.src = "images/closebigon.gif";');
			closeBtn.setAttribute('onmouseout', 'this.src = "images/closebig.gif";');
			titleBar.appendChild(closeBtn);

			// create the content container
			const contentContainer = document.createElement('div');
			contentContainer.id = 'dynamicX';
			contentContainer.style.backgroundColor = '#ffffff';
			contentContainer.style.border = '2px solid #777777';
			contentContainer.style.height = height + 'px';
			contentContainer.innerHTML = content;

			// append the title bar and content container to the main content
			divElement.appendChild(titleBar);
			divElement.appendChild(document.createElement('br'));
			divElement.appendChild(document.createElement('br'));
			divElement.appendChild(contentContainer);

			// set the position and display the dialog
			const pos = getMouseP(e);
			divElement.style.top = pos[1] + 'px';
			divElement.style.left = (pos[0] - 100) + 'px';
			divElement.style.display = '';
		}

		/**
		 * Function to get the query data result.
		 * @param {Event} e - The event object.
		 * @param {string} type - The type of the action.
		 */
		goTest() {
			// 1. get db
			const db = document.getElementById('dbList').value;

			// 2. get table
			const tableAndJoin = this.getTableAndJoin();
			const join	= tableAndJoin[1].join(',');
			const table = tableAndJoin[0].toString();

			// 3. get judul laporan
			const title = this.getTitle();

			// 4. get column display
			const columnDisplay = this.getColumnDisplay();
			const select = columnDisplay[0].join('|')
			const columnName = columnDisplay[1].join('|');
			const group = columnDisplay[2].join('|');
			const subtotal = columnDisplay[3].join('|');
			const order = columnDisplay[4].join('|');

			// 5. get condition
			const condition = this.getCondition();
			const format = [];
			const operator = [];
			const field = [];
			for (let i = 0; i < condition.length; i++) {
				format.push(condition[i].parameter);
				operator.push(condition[i].operator);
				field.push(condition[i].condition);
			}

			// 6. get sort
			const sort = document.getElementById('orderby').value;

			if (columnDisplay && condition && title && db) {
				const tujuan = $.options.slave+`?switcher=save&dbname=${db}&table=${table}&join=${join}&judul=${title}&kolomTampil=${columnName}&grouping=${group}&subtotal=${subtotal}&order=${order}&kolomSelect=${select}&format=${format}&operator=${operator}&field=${field}&sort=${sort}`;

				$.get(false, tujuan, (e) => {
					const response = JSON.parse(e.response) || e.response;
					if (response.status == 'success') {
						$.refresh();
						$.redirect('master?page=query_generator');
					} else {
						$.Alert('Query Failed', e.response.message);
					}
				});
			}
		}

		/**
		 * Function to create a table element.
		 * @param {Object} data - The data object.
		 * @returns {HTMLElement} - The created table element.
		 * @description This function creates a table element.
		 */
		createTableElement(data) {
			data = JSON.parse(data);
			const table = document.createElement('table');
			table.className = 'table table-bordered table-striped table-hover';

			const thead = document.createElement('thead');
			thead.className = 'table-primary';
			const headerRow = thead.insertRow();
			headerRow.insertCell().textContent = 'No';
			
			data.columns.forEach(column => {
				const th = document.createElement('th');
				th.textContent = column;
				headerRow.appendChild(th);
			});
			table.appendChild(thead);

			const tbody = document.createElement('tbody');
			data.rows.forEach((row, index) => {
				const tr = tbody.insertRow();
				tr.insertCell().textContent = index + 1;
				row.forEach(cell => {
					tr.insertCell().textContent = cell;
				});
			});
			table.appendChild(tbody);

			return table;
		}

		/**
		 * Function to get the condition.
		 * @returns {Array} - The condition.
		 * @description This function gets the condition.
		 */
		getParameterInput() {
			const flyTable = document.getElementById('flyTable');
			const rows = flyTable.rows.length;
			let parameter = '';
			for (let i = 0; i < rows; i++) {
				if (parameter != '') {
					parameter += ' AND';
				}

				const cell = flyTable.rows[i].cells[1];
				const cellValue = cell.textContent || cell.innerText;

				const nullx = cellValue.indexOf('NULL');
				const betweenx = cellValue.indexOf('BETWEEN');
				const likex = cellValue.indexOf('LIKE');
				const inx = cellValue.indexOf('IN');

				let operator = cellValue;
				operator = operator.replace('&gt;', '>');
				operator = operator.replace('&lt;', '<');

				const rt = document.getElementById('frmparam' + i).getAttribute('onmousemove');
				const parameterValue = flyTable.rows[i].cells[0].getAttribute('value');
				if (betweenx > -1) {
					let param1 = document.getElementById('frmparam' + i).value;
					let param2 = document.getElementById('frmparama' + i).value;
					if (rt != null && rt.indexOf('setCalendar') > -1) {
						param1 = param1.split('-').reverse().join('-');
						param2 = param2.split('-').reverse().join('-');
					}
					
					parameter += ` (${parameterValue} ${operator} "${param1}" and "${param2}")`
				} else if (nullx > -1) {
					parameter += ` ${parameterValue} ${operator}`
				} else if (likex > -1) {
					let param1 = document.getElementById('frmparam' + i).value;
					if (rt != null && rt.indexOf('setCalendar') > -1) {
						param1 = param1.split('-').reverse().join('-');
					}
					
					parameter += ` ${parameterValue} ${operator} "%${param1}%"`
				} else if (inx > -1) {
					let raw = document.getElementById('frmparam' + i).value;
					raw = raw.split(',');
					let param = '';
					for (let j = 0; j < raw.length; j++) {
						if (j == 0) {
							param += `"${raw[j]}"`;
						} else {
							param += `, "${raw[j]}"`;
						}
					}
					
					parameter += ` ${parameterValue} ${operator} ${param}`
				} else {
					let param = document.getElementById('frmparam' + i).value;
					if (rt != null && rt.indexOf('setCalendar') > -1) {
						param = param.split('-').reverse().join('-');
					}
					
					parameter += ` ${parameterValue} ${operator} ${param}`
				}
			}

			return parameter;
		}

		/**
		 * Function to get the report title.
		 * @returns {string} - The report title.
		 * @description This function gets the report title.
		 */
		getTitle() {
			const title = document.getElementById('judul').value;
			if (title == '') {
				$.Alert("Missing Report Title", 'Report Title required');
				return false;
			} else {
				return title;
			}
		}

		/**
		 * Function to get the table and join.
		 * @returns {Array} - The table and join.
		 * @description This function gets the table and join.
		 */
		getTableAndJoin() {
			const numberOfRow = this.getRowCount();
			const table = [];
			const join = [];
			for (let i = 1; i <= numberOfRow; i++) {
				const tableList = document.getElementById('tableList' + i);
				const tableName = tableList.options[tableList.selectedIndex].value;
				table.push(tableName);
				if (i < numberOfRow) {
					for (let j = 1; j <= 3; j++) {
						const joinA = document.getElementById('join' + i + 'a' + j);
						const joinB = document.getElementById('join' + i + 'b' + j);
						const joinAValue = joinA.options[joinA.selectedIndex].value;
						const joinBValue = joinB.options[joinB.selectedIndex].value;
						if (joinAValue != '' && joinBValue != '') {
							join.push(joinAValue + '=' + joinBValue);
						}
					}
				}
			}

			const tableAndJoin = [];
			tableAndJoin.push(table);
			tableAndJoin.push(join);

			return tableAndJoin;
		}

		/**
		 * Function to get the column display.
		 * @returns {Array} - The column display.
		 * @description This function gets the column display.
		 */
		getColumnDisplay() {
			const column = [];
			const caption = [];
			const group = [];
			const subtotal = [];
			const order = [];
			let status = true;
			try {
				const captionDisplay = document.getElementById('captionDisplay');
				
				const cells = captionDisplay.childElementCount;
				if (cells > 0) {
					for (let i = 0; i < cells; i++) {
						// column
						column.push(document.getElementById('column' + i).textContent);

						// caption
						const captionElement = document.getElementById('caption' + i);
						if (captionElement.value == '') {
							status = false;
						} else {
							caption.push(captionElement.value);
						}

						// group
						const groupElement = document.getElementById('group' + i);
						group.push(groupElement.checked ? 1 : 0);

						// subtotal
						const subtotalElement = document.getElementById('subtotal' + i);
						subtotal.push(subtotalElement.checked ? 1 : 0);

						// order
						const orderElement = document.getElementById('order' + i);
						order.push(orderElement.checked ? 1 : 0);
					}
				} else {
					status = false;
				}

				const all = [column, caption, group, subtotal, order];
				if (status) {
					return all;
				} else {
					$.Alert("Missing Caption Display", 'Caption display required');
					return false;
				}
			} catch(e) {
				$.Alert("No Columns Available", 'No column to display');
				return false;
			}
		}

		/**
		 * Function to get the condition.
		 * @returns {Array} - The condition.
		 * @description This function gets the condition.
		 */
		getCondition() {
			const table = document.getElementById('parameter');
			const parameterRow = table.rows.length;
			const parameters = [];
			const checker = [];
			for (let i = 1; i <= parameterRow; i++) {
				const parameter = document.getElementById('parameter' + i);
				const parameterValue = parameter.options[parameter.selectedIndex].value;
				const operator = document.getElementById('operator' + i);
				const operatorValue = operator.options[operator.selectedIndex].value;
				const condition = document.getElementById('condition' + i);
				const conditionValue = condition.options[condition.selectedIndex].value;
				if (parameterValue != 'Choose' && operatorValue != 'Choose') {
					parameters.push({
						parameter: parameterValue,
						operator: operatorValue,
						condition: conditionValue
					});
					checker.push(conditionValue);
				}
			}

			checker.sort();

			let status = true;
			for (let i = 1; i < checker.length; i++) {
				if (checker[i] == checker[i - 1]) {
					status = false;

					$.Alert("Duplicate Parameter Detected", `Duplicate parameter condition on ${checker[i]}`);

					break;
				}
			}

			return status ? parameters : false;
		}

		/**
		 * Function to save the configuration.
		 * @param {Event} e - The event object.
		 * @param {string} type - The type of the action.
		 * @returns {void}
		 */
		saveConfig() {
			$.Confirm('Configuration will be saved and cannot be changed, are you sure?', () => {
				this.goTest();
			});
		}

		/**
		 * Function to create a table list.
		 * @param {Array} data - The data array.
		 * @param {string} user - The user.
		 * @returns {HTMLElement} - The created table list.
		 * @description This function creates a table list.
		 */
		createTableList(data, user) {
			const table = document.createElement('table');
			table.className = 'sortable';
			table.setAttribute('cellspacing', '1');
			table.setAttribute('cellpadding', '5');
			table.setAttribute('border', '0');

			const thead = document.createElement('thead');
			const tr = document.createElement('tr');
			tr.className = 'rowheader';
			const headers = ['No', 'Report Title', 'Create Date', 'Designer', 'HTML', 'Excel', 'PDF', 'Status', 'Assign User', 'Browse', 'Chart'];
			headers.forEach(header => {
				const th = document.createElement('td');
				th.textContent = header;
				th.setAttribute('align', 'center');
				tr.appendChild(th);
			});
			thead.appendChild(tr);
			table.appendChild(thead);
			

			const tbody = document.createElement('tbody');
			data.forEach((row) => {
				const tr = document.createElement('tr');
				tr.className = 'rowcontent';

				const tdNo = document.createElement('td');
				tdNo.setAttribute('align', 'center');
				tdNo.textContent = row.rnumber;

				const tdTitle = document.createElement('td');
				tdTitle.textContent = row.namalaporan;
				
				const tdDate = document.createElement('td');
				tdDate.textContent = new Date(row.createdate).toLocaleDateString('id-ID');

				const tdDesigner = document.createElement('td');
				tdDesigner.textContent = row.owner;
				
				const tdHtml = document.createElement('td');
				const tdExcel = document.createElement('td');
				const tdPdf = document.createElement('td');
				const tdStatus = document.createElement('td');
				const tdAsign = document.createElement('td');
				if (row.owner == user) {
					tdHtml.setAttribute('align', 'center');
					const inputHtml = document.createElement('input');
					inputHtml.type = 'checkbox';
					inputHtml.id = 'html' + row.rnumber;
					inputHtml.checked = row.html == 1;
					inputHtml.setAttribute('onclick', "$.QGen.change(this, 'html', '', " + row.rnumber + ")");
					tdHtml.appendChild(inputHtml);
					
					tdExcel.setAttribute('align', 'center');
					const inputExcel = document.createElement('input');
					inputExcel.type = 'checkbox';
					inputExcel.id = 'xls' + row.rnumber;
					inputExcel.checked = row.speadsheet == 1;
					inputExcel.setAttribute('onclick', "$.QGen.change(this, 'speadsheet', '', " + row.rnumber + ")");
					tdExcel.appendChild(inputExcel);

					tdPdf.setAttribute('align', 'center');
					const inputPdf = document.createElement('input');
					inputPdf.type = 'checkbox';
					inputPdf.id = 'pdf' + row.rnumber;
					inputPdf.checked = row.pdf == 1;
					inputPdf.setAttribute('onclick', "$.QGen.change(this, 'pdf', '', " + row.rnumber + ")");
					tdPdf.appendChild(inputPdf);

					const selectStatus = document.createElement('select');
					selectStatus.id = 'statusR' + row.rnumber;
					selectStatus.setAttribute('onchange', `$.QGen.change(this, 'status', this.options[this.selectedIndex].value, ${row.rnumber})`);
					const statusOptions = [
						{ value: row.status, text: row.status == '0' ? 'Not Published' : row.status == '1' ? 'Active' : 'Deleted' },
						{ value: '0', text: 'Not Published' },
						{ value: '1', text: 'Active' },
						{ value: '2', text: 'Delete' }
					];
					statusOptions.forEach(option => {
						const opt = document.createElement('option');
						opt.value = option.value;
						opt.textContent = option.text;
						if (option.value == row.status) {
							opt.selected = true;
						}
						selectStatus.appendChild(opt);
					});
					tdStatus.appendChild(selectStatus);

					tdAsign.setAttribute('align', 'center');
					const imgUser = document.createElement('img');
					imgUser.src = 'images/orgicon.png';
					imgUser.style.cursor = 'pointer';
					imgUser.className = 'zImgBtn';
					imgUser.title = 'Assign User Access';
					imgUser.setAttribute('onclick', `$.QGen.userOf(event, ${row.rnumber})`);
					tdAsign.appendChild(imgUser);
				} else {
					tdHtml.textContent = row.html == 1 ? 'Yes' : 'No';
					tdExcel.textContent = row.speadsheet == 1 ? 'Yes' : 'No';
					tdPdf.textContent = row.pdf == 1 ? 'Yes' : 'No';
					
					tdStatus.textContent = row.status == '0' ? 'Not Published' : row.status == '1' ? 'Active' : 'Deleted';
				}

				const tdBrowser = document.createElement('td');
				tdBrowser.setAttribute('align', 'center');
				const imgBrowser = document.createElement('img');
				imgBrowser.src = 'images/skyblue/zoom.png';
				imgBrowser.style.cursor = 'pointer';
				imgBrowser.className = 'zImgBtn';
				imgBrowser.title = 'Try Report';
				imgBrowser.setAttribute('onclick', `$.QGen.browseR(event, ${row.rnumber})`);
				tdBrowser.appendChild(imgBrowser);

				const tdChart = document.createElement('td');
				tdChart.setAttribute('align', 'center');

				const imgCreateChart = document.createElement('img');
				imgCreateChart.src = 'images/skyblue/plus.png';
				imgCreateChart.style.cursor = 'pointer';
				imgCreateChart.className = 'zImgBtn';
				imgCreateChart.title = 'Create Chart';
				imgCreateChart.setAttribute('data-row', JSON.stringify(row));
				imgCreateChart.setAttribute('onclick', `$.QGen.chart(event, ${row.rnumber})`);
				
				tdChart.appendChild(imgCreateChart);

				tr.appendChild(tdNo);
				tr.appendChild(tdTitle);
				tr.appendChild(tdDate);
				tr.appendChild(tdDesigner);
				tr.appendChild(tdHtml);
				tr.appendChild(tdExcel);
				tr.appendChild(tdPdf);
				tr.appendChild(tdStatus);
				tr.appendChild(tdAsign);
				tr.appendChild(tdBrowser);
				tr.appendChild(tdChart);
				tbody.appendChild(tr);
			});

			table.appendChild(tbody);

			return table;
		}

		/**
		 * Function to get data for chart.
		 * @param {Event} e - The event object.
		 * @param {number} rnumber - The report number.
		 * @returns {void}
		 * @description This function gets data for chart.
		 */
		chart(e, rnumber) {
			const param = `action=browseReport&rnumber=${rnumber}`;
			const tujuan = 'tool_slaveQueryGenerator.php';
			post_response_text(tujuan, param, respog);

			function respog() {
				if (con.readyState == 4) {
					if (con.status == 200) {
						busy_off()
						if (!isSaveResponse(con.responseText)) {
							$.Alert('Error', con.responseText);
						} else {
							const data = JSON.parse(con.responseText);
							const browseReport = $.QGen.displayChart(data);
							const id = 'dynamic3';
							const title = 'Customized Form';
							const height = '180px';
							const width = '200px';
							$.QGen.formDialoque(id, title, browseReport.outerHTML, width, height, e, 'bodyutama');
						}
					} else {
						busy_off();
						error_catch(con.status);
					}
				}
			}
		}

		/**
		 * Function to create input parameter for chart form dialogue.
		 * @param {Object} data - The data object.
		 * @returns {HTMLElement} - The created input parameter.
		 * @description This function creates input parameter for chart form dialogue.
		 */
		displayChart(data) {
			const container = document.createElement('fieldset');
			const title = document.createElement('legend');
			title.textContent = `Title: ${data.judul}`;
			container.appendChild(title);

			const table = document.createElement('table');
			table.id = 'flyTable';
			table.className = 'rounded5';

			const tbody = document.createElement('tbody');
			data.parameter.reverse().forEach((param, i) => {
				const value = param.value;
				const nullx = param.operator.indexOf('NULL');
				const betweenx = param.operator.indexOf('BETWEEN');

				const tr = document.createElement('tr');
				
				const tdColumn = document.createElement('td');
				tdColumn.setAttribute('value', param.kolom);
				tdColumn.textContent = param.kolom.split('.')[1];

				const tdOperator = document.createElement('td');
				tdOperator.textContent = param.operator;

				const tdValue = document.createElement('td');

				const input = document.createElement('input');
				input.type = 'text';
				input.className = 'myinputtext';
				input.id = 'frmparam' + i;
				input.style.width = '150px';
				if (value == 'Text' && nullx < 0 && betweenx < 0) {
					// input.setAttribute('onkeypress', 'return tanpa_kutip(event);');
				} else if (value == 'Date' && nullx < 0 && betweenx < 0) {
					input.setAttribute('onkeypress', 'return false;');
					input.setAttribute('onmousemove', 'setCalendar(this.id)');
				} else if (value == 'Number' && nullx < 0 && betweenx < 0) {
					input.className = 'myinputtextnumber';
					input.setAttribute('onkeypress', 'return angka_doang(event);');
				} else if (nullx > -1) {
					input.disabled = true;
					input.value = param.operator;
				} else if (betweenx > -1) {
					if (value == 'Text') {
						const input1 = document.createElement('input');
						input1.type = 'text';
						input1.className = 'myinputtext';
						input1.id = 'frmparam' + i;
						input1.style.width = '60px';

						input.id = 'frmparama' + i
						input1.style.width = '60px';

						tdValue.appendChild(input1);
						tdValue.appendChild(document.createTextNode(' AND '));
					} else if (value == 'Number') {
						const input1 = document.createElement('input');
						input1.type = 'text';
						input1.className = 'myinputtextnumber';
						input1.id = 'frmparam' + i;
						input1.style.width = '60px';
						input1.setAttribute('onkeypress', 'return angka_doang(event);');

						input.id = 'frmparama' + i
						input.className = 'myinputtextnumber';
						input.setAttribute('onkeypress', 'return angka_doang(event);');

						tdValue.appendChild(input1);
						tdValue.appendChild(document.createTextNode(' AND '));
					} else if (value == 'Date') {
						const input1 = document.createElement('input');
						input1.type = 'text';
						input1.className = 'myinputtext';
						input1.id = 'frmparam' + i;
						input1.style.width = '60px';
						input1.setAttribute('onkeypress', 'return false;');
						input1.setAttribute('onmousemove', 'setCalendar(this.id)');

						input.id = 'frmparama' + i
						input.setAttribute('onkeypress', 'return false;');
						input.setAttribute('onmousemove', 'setCalendar(this.id)');

						tdValue.appendChild(input1);
						tdValue.appendChild(document.createTextNode(' AND '));
					}
				}

				tdValue.appendChild(input);
				tr.appendChild(tdColumn);
				tr.appendChild(tdOperator);
				tr.appendChild(tdValue);
				tbody.appendChild(tr);
			});

			table.appendChild(tbody);
			container.appendChild(table);

			// Add buttons for show or create chart
			const buttonContainer = document.createElement('div');
			buttonContainer.style.textAlign = 'center';

			const showChartBtn = document.createElement('button');
			showChartBtn.className = 'mybutton';
			showChartBtn.title = 'Show Chart';
			showChartBtn.style.cursor = 'pointer';
			showChartBtn.textContent = 'Show Chart';
			showChartBtn.setAttribute('onclick', `$.QGen.showChart(event, ${data.rnumber})`);

			const createChartBtn = document.createElement('button');
			createChartBtn.className = 'mybutton';
			createChartBtn.title = 'Create Chart';
			createChartBtn.style.cursor = 'pointer';
			createChartBtn.setAttribute('onclick', `$.QGen.createChart(${data.rnumber})`);
			createChartBtn.textContent = 'Create Chart';

			buttonContainer.appendChild(showChartBtn);
			buttonContainer.appendChild(createChartBtn);
			container.appendChild(buttonContainer);

			return container;
		}

		/**
		 * Function to open a new tab to create a chart.
		 * @param {number} rnumber - The report number.
		 * @returns {void}
		 * @description This function opens a new tab to create a chart.
		 */
		createChart(rnumber) {
			const parameterInput = encodeURIComponent(this.getParameterInput());
			window.open(`tool_chartGenerator.php?rnumber=${rnumber}&parameter=${parameterInput}`, '_blank');
		}

		/**
		 * Function to open a new tab to show a chart.
		 * @param {number} rnumber - The report number.
		 * @returns {void}
		 * @description This function opens a new tab to show a chart.
		 */
		showChart(e, rnumber) {
			this.selectedChart = [];

			const param = `action=chart&rnumber=${rnumber}`;
			const tujuan = 'tool_slaveQueryGenerator.php';
			post_response_text(tujuan, param, respog);

			function respog() {
				if (con.readyState == 4) {
					if (con.status == 200) {
						busy_off()
						if (!isSaveResponse(con.responseText)) {
							$.Alert('Error', con.responseText);
						} else {
							const data = JSON.parse(con.responseText);
							const chart = $.QGen.displayChartList(rnumber, data);
							const title = 'Chart List';
							const height = '180px';
							const width = '200px';
							showDialog1(title, chart.outerHTML, width, height, e, 'bodyutama');
						}
					} else {
						busy_off();
						error_catch(con.status);
					}
				}
			}
		}

		/**
		 * Function to display the chart list.
		 * @param {number} rnumber - The report number.
		 * @param {Array} data - The chart data.
		 * @returns {HTMLElement} - The container element.
		 */
		displayChartList(rnumber, data) {
			const container = document.createElement('fieldset');
			const title = document.createElement('legend');
			title.textContent = 'Select Chart';
			container.appendChild(title);

			const table = document.createElement('table');
			table.id = 'flyTable';
			table.className = 'rounded5';

			const tbody = document.createElement('tbody');
			data.forEach((chart) => {
				const tr = document.createElement('tr');

				const tdCheckbox = document.createElement('td');
				const inputCheckbox = document.createElement('input');
				inputCheckbox.type = 'checkbox';
				inputCheckbox.id = chart.id;
				inputCheckbox.value = chart.id;
				inputCheckbox.setAttribute('onclick', `$.QGen.selectChart(event, ${chart.id})`);
				tdCheckbox.appendChild(inputCheckbox);

				const tdChartName = document.createElement('td');
				tdChartName.textContent = chart.nama;
				
				tr.appendChild(tdCheckbox);
				tr.appendChild(tdChartName);
				tbody.appendChild(tr);
			});
			
			table.appendChild(tbody);
			container.appendChild(table);

			const btnContainer = document.createElement('div');
			btnContainer.style.textAlign = 'center';
			
			const btnShow = document.createElement('button');
			btnShow.className = 'mybutton';
			btnShow.title = 'Show Chart';
			btnShow.textContent = 'Show Chart';
			btnShow.style.cursor = 'pointer';
			btnShow.setAttribute('onclick', `$.QGen.showSelectedChart(${rnumber})`);
			btnContainer.appendChild(btnShow);

			container.appendChild(btnContainer);

			return container;
		}

		/**
		 * Function to select a chart.
		 * @param {Event} e - The event object.
		 * @param {number} id - The chart ID.
		 * @returns {void}
		 * @description This function selects a chart.
		 */
		selectChart(e, id) {
			if (e.target.checked) {
				$.QGen.selectedChart.push(parseInt(e.target.value));
			} else {
				$.QGen.selectedChart = $.QGen.selectedChart.filter(id => id != e.target.value);
			}
		}

		/**
		 * Function to show the selected chart.
		 * @param {number} rnumber - The report number.
		 */
		showSelectedChart(rnumber) {
			const parameterInput = encodeURIComponent(this.getParameterInput());
			const selectedChart = this.selectedChart.join(',');

			window.open(`tool_chart.php?rnumber=${rnumber}&parameter=${parameterInput}&chart=${selectedChart}`, '_blank');
		}

		/**
		 * Function to show all charts.
		 * @returns {void}
		 * @description This function shows all charts.
		 */
		showAllChart() {
			const btnShow = document.createElement('button');
			btnShow.className = 'mybutton';
			btnShow.title = 'Show Chart';
			btnShow.textContent = 'Show Chart';
			btnShow.style.cursor = 'pointer';
			btnShow.setAttribute('onclick', "window.open('tool_chart.php', '_blank')");

			const reportListContainer = document.getElementById('tableReportList');
			reportListContainer.appendChild(btnShow);
		}

		/**
		 * Function to get data parameter.
		 * @param {Event} e - The event object.
		 * @param {number} rnumber - The report number.
		 * @returns {void}
		 * @description This function gets data parameter.
		 */
		browseR(e, rnumber) {
			const param = `action=browseReport&rnumber=${rnumber}`;
			const tujuan = 'tool_slaveQueryGenerator.php';
			post_response_text(tujuan, param, respog);

			function respog() {
				if (con.readyState == 4) {
					if (con.status == 200) {
						busy_off()
						if (!isSaveResponse(con.responseText)) {
							$.Alert('Error', con.responseText);
						} else {
							const data = JSON.parse(con.responseText);
							const browseReport = $.QGen.displayBrowseReport(data);
							const id = 'dynamic3';
							const title = 'Customized Form';
							const height = '180px';
							const width = '200px';
							$.QGen.formDialoque(id, title, browseReport.outerHTML, width, height, e, 'bodyutama');
						}
					} else {
						busy_off();
						error_catch(con.status);
					}
				}
			}
		}

		/**
		 * Function to display browse report.
		 * @param {Object} data - The data object.
		 * @returns {HTMLElement} - The browse report.
		 * @description This function displays browse report.
		 */
		displayBrowseReport(data) {
			const dataParsed = JSON.parse(data);

			const container = document.createElement('div');
			container.className = 'col-xl-10 col-md-12 col-xs-12';

			const table = document.createElement('table');
			table.id = 'flyTable';
			table.className = 'table';

			const tbody = document.createElement('tbody');
			dataParsed.forEach((param, i) => {
				const value = param.value;
				const nullx = param.operator.indexOf('NULL');
				const betweenx = param.operator.indexOf('BETWEEN');

				const tr = document.createElement('tr');
				
				const tdColumn = document.createElement('td');
				tdColumn.style.fontWeight = 'bold';
				tdColumn.style.backgroundColor = '#21252900';
				tdColumn.style.borderWidth = '0px';
				tdColumn.style.verticalAlign = 'middle';
				tdColumn.setAttribute('value', param.kolom);
				tdColumn.textContent = param.kolom.split('.')[1];

				const tdOperator = document.createElement('td');
				tdOperator.style.padding = '0px 10px';
				tdOperator.style.backgroundColor = '#21252900';
				tdOperator.style.verticalAlign = 'middle';
				tdOperator.style.borderWidth = '0px';
				tdOperator.textContent = param.operator;

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
					input.id = 'frmparam' + i;
					input.name = 'frmparam[]';
					input.required = true;
					tdValue.appendChild(input);
				} else if (value === 'Date' && nullx < 0 && betweenx < 0) {
					const input = document.createElement('input');
					input.type = 'date';
					input.className = 'form-control form-control-sm pb-0 inputParameter';
					input.style.fontSize = '12px';
					input.id = 'frmparam' + i;
					input.name = 'frmparam[]';
					input.required = true;
					
					tdValue.appendChild(input);
				} else if (value === 'Number' && nullx < 0 && betweenx < 0) {
					const input = document.createElement('input');
					input.type = 'number';
					input.className = 'form-control inputParameter';
					input.style.fontSize = '12px';
					input.id = 'frmparam' + i;
					input.name = 'frmparam[]';
					input.required = true;
					input.setAttribute('onkeypress', 'return angka_doang(event);');
					tdValue.appendChild(input);
				} else if (nullx > -1) {
					const input = document.createElement('input');
					input.type = 'text';
					input.className = 'form-control inputParameter';
					input.id = 'frmparam' + i;
					input.name = 'frmparam[]';
					input.disabled = true;
					input.value = param.operator;
					tdValue.appendChild(input);
				} else if (betweenx > -1) {
					const betweenContainer = document.createElement('div');
					betweenContainer.className = 'd-flex align-items-center';
					
					let input1 = document.createElement('input');
					input1.id = 'frmparam' + i;
					input1.name = 'frmparam[]';
					input1.required = true;
					input1.className = 'form-control inputParameter me-2';
					input1.style.fontSize = '12px';

					const andLabel = document.createElement('span');
					andLabel.className = 'mx-2';
					andLabel.textContent = 'AND';
					
					let input2 = document.createElement('input');
					input2.id = 'frmparama' + i;
					input2.name = 'frmparam[]';
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
						input1 = this.createSetupSelect(i, 'frmparam');
						input2 = this.createSetupSelect(i, 'frmparama');
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
			container.appendChild(table);

			return container;
		}

		/**
		 * Toggle the visibility of the date options dropdown.
		 * @param {Event} e - The click event.
		 * @param {number} i - The index of the dropdown.
		 */
		toggleDropdown(e, i) {
			const dropdown = document.getElementById(`dateOptions${i}`);
			dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
		}

		/**
		 * Get the function options based on the type.
		 * @param {string} type - The type of the parameter.
		 * @returns {Array} - The array of function options.
		 */
		getFunctionOptions(type) {
			const options = [];
			options.push({ value: '', text: 'Choose' });
			if (type == 'Date') {
				options.push({ value: 'Hari ini', text: 'Hari ini' });
				options.push({ value: 'Kemarin', text: 'Kemarin' });
				options.push({ value: 'Minggu ini', text: 'Minggu ini' });
				options.push({ value: 'Minggu lalu', text: 'Minggu lalu' });
				options.push({ value: 'Bulan ini', text: 'Bulan ini' });
				options.push({ value: 'Bulan lalu', text: 'Bulan lalu' });
				options.push({ value: 'Tahun ini', text: 'Tahun ini' });
				options.push({ value: 'Tahun lalu', text: 'Tahun lalu' });
			}

			return options;
		}

		/**
		 * Fetch data from the server.
		 * @param {number} rnumber - The row number.
		 */
		fetchData(rnumber) {
			this.con = this.createXMLHttpRequest();
			
			const param = `action=data&rnumber=${rnumber}&parameter=${encodeURIComponent(this.parameter[rnumber])}`;
			const tujuan = 'api/tool_slaveChartGenerator.php';

			this.post(tujuan, param, this.respog.bind(this));
		}

		/**
		 * Create a new XMLHttpRequest object.
		 * @returns {XMLHttpRequest|boolean} - The XMLHttpRequest object or false if not supported.
		 */
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
						$.Alert("Browser Incompatibility", 'XMLHTTPRequest not supported by your browser!');
						return false;
					}
				}
			}
		}

		/**
		 * Send a POST request.
		 * @param {string} tujuan - The URL to send the request to.
		 * @param {string} param - The parameters to include in the request.
		 * @param {function} functiontoexecute - The function to execute on response.
		 */
		post(tujuan, param, functiontoexecute) {
			if (!this.isSaveResponse(param)) {
				$.Alert("Error Code", "Hindari penggunaan kata ERROR, WARNING dan GAGAL");
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

		/**
		 * Handle the response from the server.
		 */
		respog() {
			if (this.con.readyState === 4) {
				if (this.con.status == 200) {
					if (!this.isSaveResponse(this.con.responseText)) {
						$.Alert('ERROR', this.con.responseText);
					} else {
						console.log(this.con.responseText);
					}
				} else {
					this.error_catch(this.con.status);
				}
			}
		}

		/**
		 * Check if the response indicates a successful save.
		 * @param {string} response - The response text.
		 * @returns {boolean} - True if the response indicates a successful save, false otherwise.
		 */
		isSaveResponse(response) {
			response = response.toUpperCase();
			if (response.includes('ERROR') || response.includes('GAGAL') || response.includes('WARNING')) {
				return false;
			} else {
				return true;
			}
		}

		/**
		 * Handle errors based on the status code.
		 * @param {number} status - The HTTP status code.
		 */
		error_catch(status) {
			switch (status) {
				case 203:
					$.Alert('203', 'Non-Authoritative Information');
					break;
				case 400:
					$.Alert('400', 'Bad Request');
					break;
				case 403:
					$.Alert('403', 'Forbidden');
					break;
				case 404:
					$.Alert('404', 'Not Found');
					break;
				case 405:
					$.Alert('405', 'Method Not Allowed');
					break;
				case 407:
					$.Alert('407', 'Proxy Authentication Required');
					break;
				case 408:
					$.Alert('408', 'Request Timeout');
					break;
				case 409:
					$.Alert('409', 'Conflict');
					break;
				case 414:
					$.Alert('414', 'Request-URI Too Long');
					break;
				case 412:
					$.Alert('412', 'Precondition Failed');
					break;
				case 415:
					$.Alert('415', 'Unsupported Media Type');
					break;
				case 500:
					$.Alert('500', 'Internal Server Error');
					break;
				case 502:
					$.Alert('502', 'Bad Gateway');
					break;
				case 505:
					$.Alert('505', 'HTTP Version Not Supported');
					break;
			}
		}

		/**
		 * Function to display the report.
		 * @param {string} title - The title of the report.
		 * @param {string} type - The type of the report.
		 * @param {number} rnumber - The report number.
		 * @param {Event} e - The event object.
		 * @returns {void}
		 * @description This function displays the report.
		 */
		displayReportUser(title, type, rnumber, e) {
			const parameterInput = encodeURIComponent(this.getParameterInput());
			const param = `action=load&tipe=${type}&rnumber=${rnumber}&parameter=${parameterInput}`;
			const tujuan = 'tool_slaveQueryGenerator.php';
			post_response_text(tujuan, param, respog);

			function respog() {
				if (con.readyState == 4) {
					if (con.status == 200) {
						busy_off()
						if (!isSaveResponse(con.responseText)) {
							$.Alert('Error', con.responseText);
						} else {
							const data = JSON.parse(con.responseText);
							const tableElement = $.QGen.createTableElement(data);
							if (type === 'pdf') {
								$.QGen.downloadAsPDF(tableElement, title);
							} else if (type === 'excel') {
								$.QGen.downloadAsExcel(tableElement, title);
							} else {
								const height = '400px';
								const width = '900px';
								showDialog1(title, tableElement.outerHTML, width, height, e, 'bodyutama');
							}
						}
					} else {
						busy_off();
						error_catch(con.status);
					}
				}
			}
		}

		/**
		 * Function to download table as PDF.
		 * @param {HTMLElement} tableElement - The table element.
		 * @param {string} title - The title of the PDF.
		 */
		downloadAsPDF(tableElement, title) {
			const { jsPDF } = window.jspdf;
			const doc = new jsPDF();

			doc.text(title, 10, 10);
			doc.autoTable({ html: tableElement });

			doc.save(`${title}.pdf`);
		}

		/**
		 * Function to download table as Excel.
		 * @param {HTMLElement} tableElement - The table element.
		 * @param {string} title - The title of the Excel file.
		 */
		downloadAsExcel(tableElement, title) {
			const wb = XLSX.utils.table_to_book(tableElement, { sheet: "Sheet1" });
			XLSX.writeFile(wb, `${title}.xlsx`);
		}

		/**
		 * Create a link element.
		 * @param {string} text - The text content of the link.
		 * @param {function} onChangeHandler - The function to call when the link is clicked.
		 * @returns {HTMLElement} - The created link element.
		 */
		createLink(text, onChangeHandler) {
			const link = document.createElement('a');
			link.textContent = text;
			link.href = '#';
			link.addEventListener('click', onChangeHandler);

			return link;
		}

		/**
		 * Function to show the user dialog.
		 * @param {Event} e - The event object.
		 * @param {string} rnumber - The report number.
		 * @returns {void}
		 * @description This function shows the user dialog.
		 */
		userOf(e, rnumber) {
			const param = `action=getUser&rnumber=${rnumber}`;
			const tujuan = 'tool_slaveQueryGenerator.php';
			post_response_text(tujuan, param, respog);

			function respog() {
				if (con.readyState == 4) {
					if (con.status == 200) {
						busy_off()
						if (!isSaveResponse(con.responseText)) {
							$.Alert('Error', con.responseText);
						} else {
							const data = JSON.parse(con.responseText);
							const dialog = $.QGen.dialogAssignUser(data, rnumber);
							const id = 'dynamic3';
							const title = '';
							const height = '350px';
							const width = '500px';
							$.QGen.formDialoque(id, title, dialog.outerHTML, width, height, e);
						}
					} else {
						busy_off();
						error_catch(con.status);
					}
				}
			}
		}

		/**
		 * Function to create the assign user dialog.
		 * @param {Object} data - The data object.
		 * @param {string} rnumber - The report number.
		 * @returns {HTMLElement} - The created fieldset element.
		 * @description This function creates the assign user dialog.
		 */
		dialogAssignUser(data, rnumber) {
			const fieldset = document.createElement('fieldset');
			const title = document.createElement('div');
			title.style.height = '250px';
			title.style.width = '500px';
			title.style.overflow = 'auto';
			title.innerHTML = `User for ${data.judul}<br>Report Number: ${rnumber}`;

			const table = document.createElement('table');
			table.className = 'sortable';
			table.setAttribute('cellspacing', '1');
			table.setAttribute('cellpadding', '5');
			table.setAttribute('border', '0');

			const thead = document.createElement('thead');
			const tr = document.createElement('tr');
			tr.className = 'rowheader';
			const headers = ['No', 'Username', 'Location', 'Action'];
			headers.forEach(header => {
				const th = document.createElement('td');
				th.textContent = header;
				th.setAttribute('align', 'center');
				tr.appendChild(th);
			});
			thead.appendChild(tr);
			table.appendChild(thead);

			const tbody = document.createElement('tbody');
			data.users.forEach((user, index) => {
				const tr = document.createElement('tr');
				tr.className = 'rowcontent';

				const tdNo = document.createElement('td');
				tdNo.setAttribute('align', 'center');
				tdNo.textContent = index + 1;

				const tdName = document.createElement('td');
				tdName.textContent = user.namauser;

				const tdLocation = document.createElement('td');
				tdLocation.textContent = user.lokasitugas;

				const tdAccess = document.createElement('td');
				tdAccess.setAttribute('align', 'center');
				const inputAccess = document.createElement('input');
				inputAccess.type = 'checkbox';
				inputAccess.value = user.namauser;
				if (user.status == '1') {
					inputAccess.setAttribute('checked', 'true');
				}
				inputAccess.setAttribute('onclick', `$.QGen.updateToolUser(this, ${rnumber}, '${user.namauser}')`);
				tdAccess.appendChild(inputAccess);

				tr.appendChild(tdNo);
				tr.appendChild(tdName);
				tr.appendChild(tdLocation);
				tr.appendChild(tdAccess);
				tbody.appendChild(tr);
			});
			table.appendChild(tbody);
			title.appendChild(table);
			fieldset.appendChild(title);

			return fieldset;
		}

		/**
		 * Function to update the tool user.
		 * @param {HTMLElement} obj - The element object.
		 * @param {string} rnumber - The report number.
		 * @param {string} user - The user.
		 * @returns {void}
		 * @description This function updates the tool user.
		 */
		updateToolUser(obj, rnumber, user) {
			const value = obj.checked ? 1 : 0;
			const param = `action=updateUser&val=${value}&rnumber=${rnumber}&user=${user}`;
			const tujuan = 'tool_slaveQueryGenerator.php';
			post_response_text(tujuan, param, respog);

			function respog() {
				if (con.readyState == 4) {
					if (con.status == 200) {
						busy_off()
						if (!isSaveResponse(con.responseText)) {
							obj.checked = !obj.checked;
							$.Alert('Error', con.responseText);
						}
					} else {
						busy_off();
						error_catch(con.status);
					}
				}
			}
		}

		/**
		 * Function to update the table.
		 * @param {HTMLElement} obj - The element object.
		 * @param {string} type - The type of the action.
		 * @param {string} value - The value of the action.
		 * @param {string} row - The row number.
		 * @returns {void}
		 */
		change(obj, type, value, row) {
			obj.style.backgroundColor = 'orange';
			let status = value == '' ? (obj.checked ? 1 : 0) : value;
			let param = `action=updateTable&column=${type}&status=${status}&rnumber=${row}`;
			const tujuan = 'tool_slaveQueryGenerator.php';
			post_response_text(tujuan, param, respog);

			function respog() {
				if (con.readyState == 4) {
					if (con.status == 200) {
						busy_off()
						if (!isSaveResponse(con.responseText)) {
							$.Alert('Error', con.responseText);
						} else {
							obj.style.backgroundColor = 'white';
						}
					} else {
						busy_off();
						error_catch(con.status);
					}
				}
			}
		}

		// editViewConstructor1(data) {
		// 	console.log(data);
		// 	// fill constructor
		// 	this.db = data.report[0].dbname;

		// 	// fill dbList
		// 	const dbList = $.getElementById('dbList');
		// 	dbList.value = this.db;

		// 	// fill tableList
		// 	const patternTable = new RegExp(`\\b(${this.db})\\.([\\w_]+)\\b`, 'g');
		// 	const tables = []
		// 	let match;
		// 	while ((match = patternTable.exec(data.report[0].query)) !== null) {
		// 		tables.push(match[2]);
		// 	}

		// 	$.get(false, $.options.slave+"?switcher=getTables&db="+this.db, (eve) => {
		// 		this.tableList = Object.values(JSON.parse(eve.response));

		// 		tables.forEach((table, i) => {
		// 			if (i === 0) {
		// 				const target = $.getElementById('tableListContainer');
		// 				const oldSelect = $.getElementById('tableList1');
		// 				const newSelect = this.createSelectElement(
		// 					'tableList1',
		// 					'Select a table',
		// 					this.tableList,
		// 					(event) => {$.QGen.getThisField(event.target.value, 'table1')}
		// 				);

		// 				if (oldSelect) {
		// 					target.replaceChild(newSelect, oldSelect);
		// 				}

		// 				$.getElementById('tableList1').value = table;
		// 				this.getThisField(table, 'table1');
		// 				$.getElementById('btNew').disabled = false;
		// 			} else {
		// 				this.addNewRow();
		// 				$.getElementById('tableList'+(i+1)).value = table;
		// 				this.getThisField(table, 'table'+(i+1));
		// 			}
		// 		});
		// 	});

		// 	const patternJoin = new RegExp(`LEFT JOIN\\s+(${this.db}\\.[\\w_]+)\\s+ON\\s+((?:[\\w.]+\\s*=\\s*[\\w.]+(?:\\s+AND\\s+[\\w.]+\\s*=\\s*[\\w.]+)*)+)`, 'g');
		// 	const joins = [];
		// 	// Mencari semua matches
		// 	while ((match = patternJoin.exec(data.report[0].query)) !== null) {
		// 		joins.push({
		// 			table: match[1],
		// 			condition: match[2].split(/\s+AND\s+/).map(cond => cond.trim().split(/\s*=\s*/))
		// 		});
		// 	}
			
		// 	console.log(joins);

		// 	joins.forEach((join, i) => {
		// 		join.condition.forEach((cond, j) => {
		// 			console.log(`join${i+1}a${j+1}`)
		// 			console.log(`join${i+1}b${j+1}`)
		// 			console.log(document.getElementById(`join${i+1}a${j+1}`))
		// 			console.log(document.getElementById(`join${i+1}b${j+1}`))
		// 			console.log(cond)
		// 			document.getElementById(`join${i}a${j}`).value = cond[0];
		// 			document.getElementById(`join${i}b${j}`).value = cond[1];
		// 		});
		// 	});
		// }

		// /**
		//  * Inisialisasi tampilan Edit secara aman.
		//  * data: {
		//  *   dbname: "nama_db",
		//  *   tables: ["tbl_utama","tbl_join1","tbl_join2", ...],
		//  *   joins:  ["a.id=b.a_id","b.kd=c.kd", ...],        // opsional
		//  *   columns: [{select:"t.col", label:"Alias", group:0, subtotal:0, order:0}, ...], // opsional
		//  *   sort: "ASC"|"DESC"                               // opsional
		//  * }
		//  */
		async editViewConstructor(data = {}) {
			console.log(data);
			// 1) Pastikan kontainer dasar sudah ada (dibangun oleh template "new")
			await this.waitForEl('#dbListContainer');
			await this.waitForEl('#table-container');
			await this.waitForEl('#table1');

			// 2) Set Database -> trigger load tables
			if (data.report[0].dbname) {
				// Jika select db dibuat dinamis oleh newAction()
				const dbSelect = document.getElementById('dbList') || (await this.waitForEl('#dbList'));
				// Pastikan opsi sudah terisi (karena opsi diisi via AJAX)
				await this.waitForOptions(dbSelect, 2);
				// pilih db bila ada dalam opsi
				for (let i = 0; i < dbSelect.options.length; i++) {
					if (dbSelect.options[i].value === data.report[0].dbname) {
						dbSelect.selectedIndex = i;
						break;
					}
				}
				// trigger change agar table list 1 terisi
				dbSelect.dispatchEvent(new Event('change', { bubbles: true }));
			}

			// 3) Tunggu select tabel pertama tersedia & terisi
			await this.waitForEl('#tableList1');
			const tableList1 = await this.waitForOptions('#tableList1', 2);

			// 4) Set table utama dan muat kolomnya
			const patternTable = new RegExp(`\\b(${this.db})\\.([\\w_]+)\\b`, 'g');
			const tables = []
			let match;
			while ((match = patternTable.exec(data.report[0].query)) !== null) {
				tables.push(match[2]);
			}

			if (tables && tables.length) {
				const mainTable = tables[0];
				for (let i = 0; i < tableList1.options.length; i++) {
					if (tableList1.options[i].value === mainTable) {
						tableList1.selectedIndex = i;
						break;
					}
				}
				// panggil loader kolom sesuai implementasi kamu
				this.getThisField(tableList1.value, 'table1');
				// tunggu kolom muncul
				await this.waitForEl('#table1 span');
			}

			// 5) Jika ada join tables, tambah row lalu set masing-masing
			const patternJoin = new RegExp(`LEFT JOIN\\s+(${this.db}\\.[\\w_]+)\\s+ON\\s+((?:[\\w.]+\\s*=\\s*[\\w.]+(?:\\s+AND\\s+[\\w.]+\\s*=\\s*[\\w.]+)*)+)`, 'g');
			const joins = [];
			// Mencari semua matches
			while ((match = patternJoin.exec(data.report[0].query)) !== null) {
				joins.push({
					table: match[1],
					condition: match[2].split(/\s+AND\s+/).map(cond => cond.trim().split(/\s*=\s*/))
				});
			}

			if (tables && tables.length > 1) {
				for (let idx = 1; idx < tables.length; idx++) {
					// tambah row join
					this.addNewRow();
					const n = idx + 1; // baris ke-2, ke-3, dst.
					const sel = await this.waitForEl(`#tableList${n}`);
					await this.waitForOptions(sel, 2);
					// pilih tabel join
					for (let i = 0; i < sel.options.length; i++) {
						if (sel.options[i].value === tables[idx]) {
							sel.selectedIndex = i;
							break;
						}
					}
					// muat kolom join
					this.getThisField(sel.value, `table${n}`);
					await this.waitForEl(`#table${n} span`);

					joins.forEach((join, i) => {
						if (join.table === `${this.db}.${tables[idx]}`) {
							join.condition.forEach(async (cond, j) => {
								const selectJoinA = await this.waitForEl(`#join${i+1}a${j+1}`);
								const selectJoinB = await this.waitForEl(`#join${i+1}b${j+1}`);

								await this.waitForOptions(selectJoinA, 2);
								await this.waitForOptions(selectJoinB, 2);

								selectJoinA.value = cond[0];
								selectJoinB.value = cond[1];
							});
						}
					});
				}
			}

			// 6) Muat column card (selected columns)
			await this.waitForEl('#columnControl');
			this.configureColumn();

			// 6) Muat panel fungsi (orderby & function) jika belum dibuat
			//    (fungsi ini biasanya mengisi #orderby; panggil lagi supaya elemen pasti ada)
			// if (typeof this.loadFunction === 'function') {
			// 	this.loadFunction();
			// 	await this.waitForEl('#orderby');
			// }

			// 7) Set sort/order (opsional) bila elemen ada
			// if (data.sort && document.getElementById('orderby')) {
			// 	const orderby = document.getElementById('orderby');
			// 	for (let i=0;i<orderby.options.length;i++){
			// 		if (orderby.options[i].value === data.sort){ orderby.selectedIndex = i; break; }
			// 	}
			// }

			// 8) (Opsional) jika kamu punya konfigurasi kolom/alias/group/subtotal/order
			//    masukkan ke area column collector (#columnList). Di sini hanya contoh aman.
			// if (Array.isArray(data.columns) && data.columns.length) {
			// 	// Asumsikan configureColumn() akan menyiapkan canvasnya
			// 	if (typeof this.configureColumn === 'function') this.configureColumn();
			// 	await this.waitForEl('#columnList').catch(()=>{});
			// 	// Di sini kamu bisa buat span/elemen kolom sesuai strukturmu.
			// 	// Contoh sangat sederhana: buat label kolom (tanpa drag&drop)
			// 	const colList = document.getElementById('columnList');
			// 	if (colList) {
			// 		data.columns.forEach(col => {
			// 			const sp = document.createElement('span');
			// 			sp.className = 'myButton';
			// 			sp.textContent = (col.label || col.select || '').toString();
			// 			colList.appendChild(sp);
			// 		});
			// 		// regenerate parameter agar panel WHERE ter-update
			// 		if (typeof this.generateParameter === 'function') this.generateParameter();
			// 	}
			// }
		}
		
		// ==== Helpers aman untuk async DOM ====
		waitForEl(selector, { timeout = 5000, interval = 50 } = {}) {
			return new Promise((resolve, reject) => {
				const start = Date.now();
				const timer = setInterval(() => {
					const el = document.querySelector(selector);
					if (el) {
						clearInterval(timer); resolve(el);
					} else if (Date.now() - start > timeout) {
						clearInterval(timer);
						reject(new Error('Timeout waiting for ' + selector));
					}
				}, interval);
			});
		}

		waitForOptions(selector, minCount = 1, { timeout = 5000, interval = 50 } = {}) {
			return new Promise((resolve, reject) => {
				const start = Date.now();
				const timer = setInterval(() => {
					const sel = typeof selector === 'string' ? document.querySelector(selector) : selector;
					if (sel && !sel.disabled && sel.options && sel.options.length >= minCount) {
						clearInterval(timer); resolve(sel);
					} else if (Date.now() - start > timeout) {
						clearInterval(timer); reject(new Error('Timeout waiting options for ' + selector));
					}
				}, interval);
			});
		}
	}

	$.QGen = $.QGen || new owlQueriesGenerator();
})();
