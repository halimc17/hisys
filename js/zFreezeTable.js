/*
 * zFreezeTable.js
 * Helper untuk membekukan seluruh tabel atau hanya header tabel.
 * Kompatibel dengan tabel yang dibuat secara langsung maupun melalui AJAX.
 *
 * Pemakaian:
 *   freezeTable('#detailTable', 'header');
 *   freezeTable('#summaryTable', 'table');
 *   freezeTable('#detailTable', 'header', { topElement: '#summaryTable' });
 */
(function (window, document) {
  "use strict";

  var freezeInstances = [];

  function hasClass(element, className) {
    return new RegExp("(^|\\s)" + className + "(\\s|$)").test(
      element.className,
    );
  }

  function addClass(element, className) {
    if (!hasClass(element, className)) {
      element.className += (element.className ? " " : "") + className;
    }
  }

  function removeClass(element, className) {
    var expression = new RegExp("(^|\\s)" + className + "(?=\\s|$)", "g");
    element.className = element.className
      .replace(expression, " ")
      .replace(/^\s+|\s+$/g, "")
      .replace(/\s{2,}/g, " ");
  }

  function isElement(value) {
    return value && value.nodeType === 1;
  }

  function toArray(collection) {
    var result = [];
    var i;

    if (!collection) {
      return result;
    }

    for (i = 0; i < collection.length; i++) {
      result.push(collection[i]);
    }

    return result;
  }

  function resolveElements(target) {
    if (typeof target === "string") {
      return toArray(document.querySelectorAll(target));
    }

    if (isElement(target)) {
      return [target];
    }

    if (target && typeof target.length === "number") {
      return toArray(target);
    }

    return [];
  }

  function resolveTables(target) {
    var elements = resolveElements(target);
    var tables = [];
    var i;
    var j;
    var childTables;

    for (i = 0; i < elements.length; i++) {
      if (String(elements[i].tagName).toLowerCase() === "table") {
        tables.push(elements[i]);
      } else {
        childTables = elements[i].querySelectorAll("table");
        for (j = 0; j < childTables.length; j++) {
          tables.push(childTables[j]);
        }
      }
    }

    return tables;
  }

  function resolveSingleElement(target) {
    var elements = resolveElements(target);
    return elements.length > 0 ? elements[0] : null;
  }

  function normalizeMode(mode) {
    mode = String(mode || "header").toLowerCase();

    if (mode === "head" || mode === "thead") {
      return "header";
    }

    if (mode === "full" || mode === "all") {
      return "table";
    }

    return mode;
  }

  function numberValue(value, defaultValue) {
    value = parseFloat(value);
    return isNaN(value) ? defaultValue : value;
  }

  function getOuterHeight(element) {
    var style;
    var height;

    if (!element) {
      return 0;
    }

    height = element.getBoundingClientRect().height;

    if (window.getComputedStyle) {
      style = window.getComputedStyle(element);
      height += numberValue(style.marginTop, 0);
      height += numberValue(style.marginBottom, 0);
    }

    return height;
  }

  function resolveTop(options) {
    var top = options.top;
    var topElement;

    if (typeof top === "function") {
      top = top();
    }

    top = numberValue(top, 0);

    if (options.topElement) {
      topElement = resolveSingleElement(options.topElement);
      top += getOuterHeight(topElement);
    }

    return top;
  }

  function saveStyle(element, properties) {
    var original = {};
    var i;

    for (i = 0; i < properties.length; i++) {
      original[properties[i]] = element.style[properties[i]];
    }

    return original;
  }

  function restoreStyle(element, original) {
    var property;

    if (!original) {
      return;
    }

    for (property in original) {
      if (original.hasOwnProperty(property)) {
        element.style[property] = original[property];
      }
    }
  }

  function getBackgroundColor(element, fallbackElement, defaultColor) {
    var color = "";
    var fallbackColor = "";

    if (window.getComputedStyle) {
      color = window.getComputedStyle(element).backgroundColor;

      if (
        (!color || color === "transparent" || color === "rgba(0, 0, 0, 0)") &&
        fallbackElement
      ) {
        fallbackColor = window.getComputedStyle(fallbackElement).backgroundColor;
        color = fallbackColor;
      }
    }

    if (!color || color === "transparent" || color === "rgba(0, 0, 0, 0)") {
      color = defaultColor || "#ffffff";
    }

    return color;
  }

  function clearHeaderState(state) {
    var i;

    for (i = 0; i < state.cells.length; i++) {
      restoreStyle(state.cells[i].element, state.cells[i].originalStyle);
      removeClass(state.cells[i].element, "z-freeze-header-cell");
    }

    state.cells = [];
    removeClass(state.table, "z-freeze-header");
  }

  function applyHeader(state) {
    var table = state.table;
    var thead = table.getElementsByTagName("thead")[0];
    var rows;
    var row;
    var cells;
    var cell;
    var rowTop;
    var rowHeight;
    var i;
    var j;
    var cellData;
    var background;

    clearHeaderState(state);

    if (!thead) {
      return;
    }

    addClass(table, "z-freeze-header");

    rows = thead.rows;
    rowTop = resolveTop(state.options);

    for (i = 0; i < rows.length; i++) {
      row = rows[i];
      cells = row.cells;
      rowHeight = row.getBoundingClientRect().height;

      for (j = 0; j < cells.length; j++) {
        cell = cells[j];
        cellData = {
          element: cell,
          originalStyle: saveStyle(cell, [
            "top",
            "zIndex",
            "backgroundColor",
          ]),
        };

        background =
          state.options.background ||
          getBackgroundColor(cell, row, state.options.defaultBackground);

        addClass(cell, "z-freeze-header-cell");
        cell.style.top = rowTop + "px";
        cell.style.zIndex = String(state.options.zIndex + (rows.length - i));
        cell.style.backgroundColor = background;

        state.cells.push(cellData);
      }

      rowTop += rowHeight;
    }
  }

  function clearTableState(state) {
    restoreStyle(state.table, state.tableOriginalStyle);
    removeClass(state.table, "z-freeze-table");
  }

  function applyTable(state) {
    var background;

    clearTableState(state);
    addClass(state.table, "z-freeze-table");

    background =
      state.options.background ||
      getBackgroundColor(
        state.table,
        state.table.parentNode,
        state.options.defaultBackground,
      );

    state.table.style.top = resolveTop(state.options) + "px";
    state.table.style.zIndex = String(state.options.zIndex);
    state.table.style.backgroundColor = background;
  }

  function refreshState(state) {
    if (!state || !state.table || !state.table.parentNode) {
      return;
    }

    if (state.mode === "table") {
      applyTable(state);
    } else {
      applyHeader(state);
    }
  }

  function scheduleRefresh(state) {
    if (state.refreshTimer) {
      window.clearTimeout(state.refreshTimer);
    }

    state.refreshTimer = window.setTimeout(function () {
      refreshState(state);
    }, 30);
  }

  function destroyState(state) {
    var index;

    if (!state) {
      return;
    }

    if (state.resizeHandler) {
      if (window.removeEventListener) {
        window.removeEventListener("resize", state.resizeHandler, false);
      } else if (window.detachEvent) {
        window.detachEvent("onresize", state.resizeHandler);
      }
    }

    if (state.resizeObserver) {
      state.resizeObserver.disconnect();
    }

    if (state.refreshTimer) {
      window.clearTimeout(state.refreshTimer);
    }

    if (state.mode === "table") {
      clearTableState(state);
    } else {
      clearHeaderState(state);
    }

    state.table._zFreezeTableState = null;

    index = freezeInstances.indexOf(state);
    if (index !== -1) {
      freezeInstances.splice(index, 1);
    }
  }

  function createState(table, mode, options) {
    var state;
    var topElement;

    if (table._zFreezeTableState) {
      destroyState(table._zFreezeTableState);
    }

    state = {
      table: table,
      mode: mode,
      options: options,
      cells: [],
      tableOriginalStyle: saveStyle(table, [
        "top",
        "zIndex",
        "backgroundColor",
      ]),
      resizeHandler: null,
      resizeObserver: null,
      refreshTimer: null,
    };

    state.resizeHandler = function () {
      scheduleRefresh(state);
    };

    if (window.addEventListener) {
      window.addEventListener("resize", state.resizeHandler, false);
    } else if (window.attachEvent) {
      window.attachEvent("onresize", state.resizeHandler);
    }

    if (window.ResizeObserver && options.observeResize !== false) {
      state.resizeObserver = new window.ResizeObserver(function () {
        scheduleRefresh(state);
      });
      state.resizeObserver.observe(table);

      if (options.topElement) {
        topElement = resolveSingleElement(options.topElement);
        if (topElement) {
          state.resizeObserver.observe(topElement);
        }
      }
    }

    table._zFreezeTableState = state;
    freezeInstances.push(state);
    refreshState(state);

    return state;
  }

  function mergeOptions(options, mode) {
    var result = {
      top: 0,
      topElement: null,
      zIndex: mode === "table" ? 60 : 50,
      background: null,
      defaultBackground: "#ffffff",
      observeResize: true,
    };
    var property;

    options = options || {};

    for (property in options) {
      if (options.hasOwnProperty(property)) {
        result[property] = options[property];
      }
    }

    result.zIndex = numberValue(result.zIndex, mode === "table" ? 60 : 50);

    return result;
  }

  function freezeTable(target, mode, options) {
    var tables = resolveTables(target);
    var states = [];
    var i;

    mode = normalizeMode(mode);

    if (mode !== "header" && mode !== "table") {
      if (window.console && console.warn) {
        console.warn(
          "freezeTable: mode harus 'header' atau 'table'. Mode diterima:",
          mode,
        );
      }
      return states;
    }

    options = mergeOptions(options, mode);

    for (i = 0; i < tables.length; i++) {
      states.push(createState(tables[i], mode, options));
    }

    return states;
  }

  function refreshFreezeTable(target) {
    var tables;
    var i;

    if (typeof target === "undefined" || target === null) {
      for (i = 0; i < freezeInstances.length; i++) {
        refreshState(freezeInstances[i]);
      }
      return;
    }

    tables = resolveTables(target);
    for (i = 0; i < tables.length; i++) {
      if (tables[i]._zFreezeTableState) {
        refreshState(tables[i]._zFreezeTableState);
      }
    }
  }

  function unfreezeTable(target) {
    var tables = resolveTables(target);
    var i;

    for (i = 0; i < tables.length; i++) {
      if (tables[i]._zFreezeTableState) {
        destroyState(tables[i]._zFreezeTableState);
      }
    }
  }

  window.freezeTable = freezeTable;
  window.refreshFreezeTable = refreshFreezeTable;
  window.unfreezeTable = unfreezeTable;
})(window, document);
