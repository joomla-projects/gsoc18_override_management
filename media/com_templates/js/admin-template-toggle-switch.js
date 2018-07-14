/**
 * PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
 * OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
 **/

/**
 * PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
 * OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
 * */

/**
 * PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
 * OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
 * */

/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
function show_diff_changed() {
	var JformShowDiff = document.getElementById('jform_show_diff');
	if (JformShowDiff.inputs[0] && JformShowDiff.inputs[0].checked === true)
	{
		console.log('hide diff');
	}
	if (JformShowDiff.inputs[0] && JformShowDiff.inputs[0].checked === false)
	{
		console.log('show diff');
	}
}
function show_core_changed() {
	var JformShowCore = document.getElementById('jform_show_core');
	if (JformShowCore.inputs[0] && JformShowCore.inputs[0].checked === true)
	{
		console.log('hide core');
	}
	if (JformShowCore.inputs[0] && JformShowCore.inputs[0].checked === false)
	{
		console.log('show core');
	}
}
(function () {
	document.addEventListener('DOMContentLoaded', function () {
		var override = document.getElementById('override-pane');
		var corePane = document.getElementById('core-pane');
		var diffMain = document.getElementById('diff-main');

		var conditionalSection = document.getElementById('conditional-section');

		var JformShowCore = document.getElementById('jform_show_core');
		var JformShowDiff = document.getElementById('jform_show_diff');

		if (JformShowCore && corePane) {
			JformShowCore.addEventListener('change', function (e) {
				var displayCore = corePane.style.display;

				if (displayCore === 'none' && e.target.id === 'jform_show_core1') {
					corePane.style.display = 'block';
					override.className = 'col-md-6';
					JformShowCore.spans[0].classList.remove('active');
					JformShowCore.spans[1].classList.add('active');
					Joomla.editors.instances.jform_core.refresh();
				} else if (displayCore === 'block' && e.target.id === 'jform_show_core0') {
					JformShowCore.spans[0].classList.add('active');
					JformShowCore.spans[1].classList.remove('active');
					corePane.style.display = 'none';
					override.className = 'col-md-12';
				}

				if (typeof Storage !== 'undefined') {
					localStorage.setItem('coreSwitchState', e.target.id);
				}
			});
		}

		if (JformShowDiff && diffMain) {
			JformShowDiff.addEventListener('change', function (e) {
				var displayDiff = diffMain.style.display;

				if (displayDiff === 'none' && e.target.id === 'jform_show_diff1') {
					diffMain.style.display = 'block';
					JformShowDiff.spans[0].classList.remove('active');
					JformShowDiff.spans[1].classList.add('active');
				} else if (displayDiff === 'block' && e.target.id === 'jform_show_diff0') {
					JformShowDiff.spans[0].classList.add('active');
					JformShowDiff.spans[1].classList.remove('active');
					diffMain.style.display = 'none';
				}

				if (typeof Storage !== 'undefined') {
					localStorage.setItem('diffSwitchState', e.target.id);
				}
			});
		}

		var setPrestate = function setPrestate() {
			// Fetch the Storage elements
			var cState = localStorage.getItem('coreSwitchState');
			var dState = localStorage.getItem('diffSwitchState');
			if (typeof Storage !== 'undefined' && (cState || dState)) {
				var cStateActiveSwitchCore = 'jform_show_core0';
				var cStateActiveSwitchDiff = 'jform_show_diff0';

				if (cState) {
					cStateActiveSwitchCore = cState;
				}

				if (cStateActiveSwitchCore === 'jform_show_core0' && JformShowCore) {
					corePane.style.display = 'none';
					override.className = 'col-md-12';
				} else if (cStateActiveSwitchCore === 'jform_show_core1' && JformShowCore) {
					corePane.style.display = 'block';
					override.className = 'col-md-6';
					setTimeout(function () {
						JformShowCore.inputs[1].parentNode.classList.add('active');
						JformShowCore.inputs[1].checked = true;
						JformShowCore.inputs[0].checked = false;
						JformShowCore.spans[0].classList.remove('active');
						JformShowCore.spans[1].classList.add('active');
					}, 500);
				}

				if (dState) {
					cStateActiveSwitchDiff = dState;
				}

				if (cStateActiveSwitchDiff === 'jform_show_diff0' && JformShowDiff) {
					diffMain.style.display = 'none';
				} else if (cStateActiveSwitchDiff === 'jform_show_diff1' && JformShowDiff) {
					diffMain.style.display = 'block';
					setTimeout(function () {
						JformShowDiff.inputs[1].parentNode.classList.add('active');
						JformShowDiff.inputs[1].checked = true;
						JformShowDiff.inputs[0].checked = false;
						JformShowDiff.spans[0].classList.remove('active');
						JformShowDiff.spans[1].classList.add('active');
					}, 500);
				}
			}
		};

		if (JformShowCore || JformShowDiff) {
			setPrestate();
		} else if (override && conditionalSection) {
			conditionalSection.className = 'col-md-12';
			override.className = 'col-md-12';
		}
	});
})();
