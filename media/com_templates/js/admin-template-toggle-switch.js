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

			// Callback executed when JformShowCore was found
			function handleJformCore() {
				Joomla.editors.instances.jform_core.refresh();
				console.log('handleJformCore');
			}

			// Set up the mutation observer
			var observerJformShowCore = new MutationObserver(function (mutations, me) {
				if (Joomla.editors.instances.jform_core) {
					handleJformCore();
					me.disconnect();
					return;
				}
			});

			// Start observing
			observerJformShowCore.observe(corePane, {
				attributes: true
			});

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

			// Callback executed when JformShowCore was found
			function handleJformShowCore() {
				JformShowCore.newActive = 1;
				JformShowCore.switch();
			}

			// Set up the mutation observer
			var observerJformShowCore = new MutationObserver(function (mutations, me) {
				if (JformShowDiff) {
					handleJformShowCore();
					me.disconnect();
					return;
				}
			});

			// Start observing
			observerJformShowCore.observe(JformShowCore, {
				childList: true,
				subtree: true
			});

			showCoreChangedOn();
		}

		if (typeof Storage !== 'undefined' && localStorage.getItem('diffSwitchState') && JformShowDiff) {

			// Callback executed when JformShowDiff was found
			function handleJformShowDiff() {
				JformShowDiff.newActive = 1;
				JformShowDiff.switch();
			}

			// Set up the mutation observer
			var observerJformShowDiff = new MutationObserver(function (mutations, me) {
				if (JformShowDiff) {
					handleJformShowDiff();
					me.disconnect();
					return;
				}
			});

			// Start observing
			observerJformShowDiff.observe(JformShowDiff, {
				childList: true,
				subtree: true
			});

			showDiffChangedOn();
		}
	});
})();
