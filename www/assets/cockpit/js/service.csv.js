/**
 * CSV Library based on ExcellentExport (with table I/O removed).
 * at https://github.com/jmaister/excellentexport .
 * Git hash aab78f417393d80620f47a02bc4da17a79b221eb
 * -----
 * ORIGINAL EXCELLENT EXPORT COMMENT:
 * ExcellentExport.
 * A client side Javascript export to Excel.
 *
 * @author: Jordi Burgos (jordiburgos@gmail.com)
 *
 * Based on:
 * https://gist.github.com/insin/1031969
 * http://jsfiddle.net/insin/cmewv/
 *
 * CSV: http://en.wikipedia.org/wiki/Comma-separated_values
 */

/*
 * Base64 encoder/decoder from: http://jsperf.com/base64-optimized
 */

/*jslint browser: true, bitwise: true, plusplus: true, vars: true, white: true */

/**
 * SERVICE DOCUMENTATION:
 * CSVService exports two functions addCSVById and addCSVToAnchor, which sets
 * the href attribute of an anchor tag to a data URI such that the browser will
 * download the file. Check the end of this file for full function signature
 * and details.  addCSVById is a simple wrapper around addCSVToAnchor that
 * allows passing by a CSS ID rather than the object itself.
 */
/* global NGApp */
NGApp.factory('CSVService', function () {
	var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
	var fromCharCode = String.fromCharCode;
	var INVALID_CHARACTER_ERR = (function () {
			"use strict";
			// fabricate a suitable error object
			try {
				document.createElement('$');
			} catch (error) {
				return error;
			}
		}());

	// encoder
	var btoa = window.btoa;
	if (!btoa) {
		btoa = function (string) {
			"use strict";
			var a, b, b1, b2, b3, b4, c, i = 0, len = string.length, max = Math.max, result = '';

			while (i < len) {
				a = string.charCodeAt(i++) || 0;
				b = string.charCodeAt(i++) || 0;
				c = string.charCodeAt(i++) || 0;

				if (max(a, b, c) > 0xFF) {
					throw INVALID_CHARACTER_ERR;
				}

				b1 = (a >> 2) & 0x3F;
				b2 = ((a & 0x3) << 4) | ((b >> 4) & 0xF);
				b3 = ((b & 0xF) << 2) | ((c >> 6) & 0x3);
				b4 = c & 0x3F;

				if (!b) {
					b3 = b4 = 64;
				} else if (!c) {
					b4 = 64;
				}
				result += characters.charAt(b1) + characters.charAt(b2) + characters.charAt(b3) + characters.charAt(b4);
			}
			return result;
		};
	}

	// decoder
	var atob = window.atob;
	if (!atob) {
		atob = function(string) {
			"use strict";
			string = string.replace(new RegExp("=+$"), '');
			var a, b, b1, b2, b3, b4, c, i = 0, len = string.length, chars = [];

			if (len % 4 === 1) {
				throw INVALID_CHARACTER_ERR;
			}

			while (i < len) {
				b1 = characters.indexOf(string.charAt(i++));
				b2 = characters.indexOf(string.charAt(i++));
				b3 = characters.indexOf(string.charAt(i++));
				b4 = characters.indexOf(string.charAt(i++));

				a = ((b1 & 0x3F) << 2) | ((b2 >> 4) & 0x3);
				b = ((b2 & 0xF) << 4) | ((b3 >> 2) & 0xF);
				c = ((b3 & 0x3) << 6) | (b4 & 0x3F);

				chars.push(fromCharCode(a));
				if (b) {
					chars.push(fromCharCode(b));
				}
				if (c) {
					chars.push(fromCharCode(c));
				}
			}
			return chars.join('');
		};
	}
	var csvSeparator = ',';
	// we're not supporting excel right now because it's a little overkill but we could in the future...
	var uri = {excel: 'data:application/vnd.ms-excel;base64,', csv: 'data:application/csv;base64,'};
	var template = {excel: '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40"><head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet><x:Name>{worksheet}</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions></x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]--></head><body><table>{table}</table></body></html>'};
	var csvDelimiter = ",";
	var csvNewLine = "\r\n";
	var defaultFilename = 'data.csv';
	var base64 = function(s) {
		return btoa(window.unescape(encodeURIComponent(s)));
	};
	var format = function(s, c) {
		return s.replace(new RegExp("{(\\w+)}", "g"), function(m, p) {
			return c[p];
		});
	};

	var fixCSVField = function(value, delimiter) {
		var fixedValue = value;
		var addQuotes = (value.indexOf(delimiter) !== -1) || (value.indexOf('\r') !== -1) || (value.indexOf('\n') !== -1);
		var replaceDoubleQuotes = (value.indexOf('"') !== -1);

		if (replaceDoubleQuotes) {
			fixedValue = fixedValue.replace(/"/g, '""');
		}
		if (addQuotes || replaceDoubleQuotes) {
			fixedValue = '"' + fixedValue + '"';
		}
		return fixedValue;
	};

	var toString = function (elem) {
		if (elem === null || elem === undefined) {
			return '';
		}
		if (typeof elem === 'string') {
			return elem;
		}
		return elem.toString();
	};

	var matrixToCSV = function(matrix, delimiter, newLine) {
		newLine = (newLine === null || newLine === undefined) ? csvNewLine : newLine;
		delimiter = (delimiter === null || delimiter === undefined) ? csvDelimiter : delimiter;
		var data = "";
		var numCols = matrix.length;
		var numRows = matrix[0].length;
		var i, j, cell, row;
		for (i = 0; i < numCols; i++) {
			row = matrix[i];
			if (row.length !== numRows) {
				throw new Error('mismatched number of rows on column ' + i + '(expected: ' + numRows + ', but saw: ' + row.length + ' rows)');
			}
			for (j = 0; j < numRows; j++) {
				cell = row[j];
				data = data + (j ? delimiter : '') + fixCSVField(toString(cell), delimiter);
			}
			data = data + newLine;
		}
		return data;
	};

	// exported functions
	return {
		/** 
		 * addCSVToAnchor adds a Data URI to the given anchor tag that will cause the browser to download matrix as a CSV
		 * @param {HTMLAnchorElement} anchor - the anchor tag to append the data URI to
		 * @param {Array of Arrays} matrix - row-major array of arrays (i.e.,
		 *   [[1, 2, 3, 4], [5, 6, 7, 8]], 5 is row 2, column 1
		 * @param {String} filename (optional) - filename to set on download
		 *   tag (if not passed and `download` attribute is present, will not set
		 *   anything)
		 * @param {String} delimiter (optional, default ',') - delimiter to use in CSV
		 * @param {String} newLine (optional, default '\r\n') - character to use to end lines
		 * @returns {Boolean} if the operation was successful (currently always returns true)
		 **/
		addCSVToAnchor: function(anchor, matrix, filename, delimiter, newLine) {
			if (delimiter !== undefined && delimiter) {
				delimiter = csvDelimiter;
			}
			if (newLine !== undefined && newLine) {
				newLine = csvNewLine;
			}
			var csvData = matrixToCSV(matrix);
			var hrefvalue = uri.csv + base64(csvData);
			anchor.href = hrefvalue;
			if (filename) {
				anchor.download = filename;
			}
			if (!anchor.download) {
				anchor.download = 'data.csv';
			}
			return true;
		},
		/**
		 * Simple wrapper around addCSVToAnchor. Arguments are the same except for first arguemnt.
		 * @param {String} anchorID - a CSS ID that must point directly at an anchor tag.
		 * @returns {Boolean} whether operation was successful
		 *
		 * @throws Error if the ID did not point to any tag or the tag found was not an anchor tag.
		 **/
		addCSVById: function(anchorID, matrix, filename, delimiter, newLine) {
			var anchor = document.getElementByID(anchorID);
			if (!anchor instanceof HTMLAnchorElement) {
				throw new Error('cannot convert CSV Data for ID ' + anchorID + '. ID does not exist or is not an HTMLAnchorElement');
			}
			return this.addCSVToAnchor(anchor, matrix, filename, delimiter, newLine);
		}
	};
});
