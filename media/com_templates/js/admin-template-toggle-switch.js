/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function () {
	'use strict';

	window.showDiffChanged = function showDiffChanged() {
		var JformShowDiff = document.getElementById('jform_show_diff');
		var diffMain = document.getElementById('diff-main');

		setTimeout(function () {
			if (JformShowDiff.inputs[0] && JformShowDiff.inputs[0].checked === true && diffMain) {
				diffMain.classList.remove('active');
				JformShowDiff.spans[0].classList.add('active');
				JformShowDiff.spans[1].classList.remove('active');

				if (typeof Storage !== 'undefined') {
					localStorage.removeItem('diffSwitchState');
				}
			}

			if (JformShowDiff.inputs[0] && JformShowDiff.inputs[0].checked === false && diffMain) {
				diffMain.classList.add('active');
				JformShowDiff.spans[0].classList.remove('active');
				JformShowDiff.spans[1].classList.add('active');
				JformShowDiff.inputs[1].parentNode.classList.add('active');

				if (typeof Storage !== 'undefined') {
					localStorage.setItem('diffSwitchState', 'checked');
				}
			}
		}, 500);
	};

	window.showCoreChanged = function showCoreChanged() {
		var JformShowCore = document.getElementById('jform_show_core');
		var override = document.getElementById('override-pane');
		var corePane = document.getElementById('core-pane');

		setTimeout(function () {
			if (JformShowCore.inputs[0] && JformShowCore.inputs[0].checked === true && corePane) {
				JformShowCore.spans[0].classList.add('active');
				JformShowCore.spans[1].classList.remove('active');
				corePane.style.display = 'none';
				override.className = 'col-md-12';

				if (typeof Storage !== 'undefined') {
					localStorage.removeItem('coreSwitchState');
				}
			}

			if (JformShowCore.inputs[0] && JformShowCore.inputs[0].checked === false && corePane) {
				corePane.style.display = 'block';
				override.className = 'col-md-6';
				JformShowCore.spans[0].classList.remove('active');
				JformShowCore.spans[1].classList.add('active');
				JformShowCore.inputs[1].parentNode.classList.add('active');
				Joomla.editors.instances.jform_core.refresh();

				if (typeof Storage !== 'undefined') {
					localStorage.setItem('coreSwitchState', 'checked');
				}
			}
		}, 500);
	};

	document.addEventListener('DOMContentLoaded', function () {
		var JformShowDiff = document.getElementById('jform_show_diff');
		var JformShowCore = document.getElementById('jform_show_core');

		if (typeof Storage !== 'undefined' && localStorage.getItem('diffSwitchState') && JformShowDiff) {
			setTimeout(function () {
				JformShowDiff.inputs[0].checked = 'false';
				JformShowDiff.inputs[1].checked = 'true';
			}, 500);
			window.showDiffChanged();
		}

		if (typeof Storage !== 'undefined' && localStorage.getItem('coreSwitchState') && JformShowCore) {
			setTimeout(function () {
				JformShowCore.inputs[0].checked = 'false';
				JformShowCore.inputs[1].checked = 'true';
			}, 500);
			window.showCoreChanged();
		}
	});
})();
