/**
 * PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
 * OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
 **/

/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function () {
	'use strict';

	var showDiffChangedOff = function showDiffChangedOff() {
		var diffMain = document.getElementById('diff-main');

		if (diffMain) {
			diffMain.classList.remove('active');

			if (typeof Storage !== 'undefined') {
				localStorage.removeItem('diffSwitchState');
			}
		}
	};

	var showDiffChangedOn = function showDiffChangedOn() {
		var diffMain = document.getElementById('diff-main');

		if (diffMain) {
			diffMain.classList.add('active');

			if (typeof Storage !== 'undefined') {
				localStorage.setItem('diffSwitchState', 'checked');
			}
		}
	};

	var showCoreChangedOff = function showCoreChangedOff() {
		var override = document.getElementById('override-pane');
		var corePane = document.getElementById('core-pane');

		if (corePane && override) {
			corePane.classList.remove('active');
			override.className = 'col-md-12';

			if (typeof Storage !== 'undefined') {
				localStorage.removeItem('coreSwitchState');
			}
		}
	};

	var showCoreChangedOn = function showCoreChangedOn() {
		var override = document.getElementById('override-pane');
		var corePane = document.getElementById('core-pane');

		if (corePane && override) {
			corePane.classList.add('active');
			override.className = 'col-md-6';

			setTimeout(function () {
				Joomla.editors.instances.jform_core.refresh();
			}, 1000);

			if (typeof Storage !== 'undefined') {
				localStorage.setItem('coreSwitchState', 'checked');
			}
		}
	};

	document.addEventListener('DOMContentLoaded', function () {
		var JformShowDiff = document.getElementById('jform_show_diff');
		var JformShowCore = document.getElementById('jform_show_core');

		if (JformShowDiff) {
			JformShowDiff.addEventListener('joomla.switcher.on', showDiffChangedOn);
			JformShowDiff.addEventListener('joomla.switcher.off', showDiffChangedOff);
		}

		if (JformShowCore) {
			JformShowCore.addEventListener('joomla.switcher.on', showCoreChangedOn);
			JformShowCore.addEventListener('joomla.switcher.off', showCoreChangedOff);
		}

		if (typeof Storage !== 'undefined' && localStorage.getItem('coreSwitchState') && JformShowCore) {
			setTimeout(function () {
				JformShowCore.newActive = 1;
				JformShowCore.switch();
			}, 500);
			showCoreChangedOn();
		}
		if (typeof Storage !== 'undefined' && localStorage.getItem('diffSwitchState') && JformShowDiff) {
			setTimeout(function () {
				JformShowDiff.newActive = 1;
				JformShowDiff.switch();
			}, 500);
			showDiffChangedOn();
		}
	});
})();
