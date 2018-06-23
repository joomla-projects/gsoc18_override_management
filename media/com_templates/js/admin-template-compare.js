/**
 * PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
 * OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
 **/

/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function () {
	document.addEventListener('DOMContentLoaded', function () {
		var decodeHtmlspecialChars = function decodeHtmlspecialChars(text) {
			var map = {
				'&amp;': '&',
				'&#038;': '&',
				'&lt;': '<',
				'&gt;': '>',
				'&quot;': '"',
				'&#039;': "'",
				'&#8217;': '’',
				'&#8216;': '‘',
				'&#8211;': '–',
				'&#8212;': '—',
				'&#8230;': '…',
				'&#8221;': '”'
			};

			return text.replace(/\&[\w\d\#]{2,5}\;/g, function (m) {
				var n = map[m];
				return n;
			});
		};

		var compare = function compare(original, changed) {
			var display = changed.nextElementSibling;
			var color = '';
			var pre = null;
			var diff = JsDiff.diffLines(original.innerHTML, changed.innerHTML);
			var fragment = document.createDocumentFragment();

			diff.forEach(function (part) {
				if (part.added) {
					color = '#a6f3a6';
				} else if (part.removed) {
					color = '#f8cbcb';
				} else {
					color = '';
				}
				pre = document.createElement('pre');
				pre.style.backgroundColor = color;
				pre.className = 'diffview';
				pre.appendChild(document.createTextNode(decodeHtmlspecialChars(part.value)));
				fragment.appendChild(pre);
			});

			display.appendChild(fragment);
		};


		var override = document.getElementById('override-pane');
		var corePane = document.getElementById('core-pane');
		var diffMain = document.getElementById('diff-main');

		var conditionalSection = document.getElementById('conditional-section');

		var jform_show_core = document.getElementById('jform_show_core');
		var jform_show_diff = document.getElementById('jform_show_diff');

		if (jform_show_core && corePane) {
			jform_show_core.addEventListener('change', function (e) {
				var displayCore = corePane.style.display;

				if (displayCore === 'none' && e.target.id === 'jform_show_core1') {
					corePane.style.display = 'block';
					override.className = 'col-md-6';
					Joomla.editors.instances.jform_core.refresh();
				} else if (displayCore === 'block' && e.target.id === 'jform_show_core0') {
					corePane.style.display = 'none';
					override.className = 'col-md-12';
				}

				var coreState = {
					activeSwitch: e.target.id
				};

				if (typeof Storage !== 'undefined') {
					localStorage.setItem('coreSwitchState', JSON.stringify(coreState));
				}

			});
		}

		if (jform_show_diff && diffMain) {
			jform_show_diff.addEventListener('change', function (e) {
				var displayDiff = diffMain.style.display;

				if (displayDiff === 'none' && e.target.id === 'jform_show_diff1') {
					diffMain.style.display = 'block';
				} else if (displayDiff === 'block' && e.target.id === 'jform_show_diff0') {
					diffMain.style.display = 'none';
				}

				var diffState = {
					activeSwitch: e.target.id
				};

				if (typeof Storage !== 'undefined') {
					localStorage.setItem('diffSwitchState', JSON.stringify(diffState));
				}

			});
		}

		var setPrestate = function setPrestate() {
			if (typeof Storage !== 'undefined') {
				// Fetch the Storage elements
				var cState = JSON.parse(localStorage.getItem('coreSwitchState'));
				var dState = JSON.parse(localStorage.getItem('diffSwitchState'));

				// Prestate the core file view state

				// Set jform_show_core0 as default
				var cStateActiveSwitch = 'jform_show_core0';

				// Set jform_show_core0 with the value in storage if not undefined
				if (cState.activeSwitch !== undefined) {
					cStateActiveSwitch = cState.activeSwitch;
				}

				if (cStateActiveSwitch === 'jform_show_core0' && jform_show_core) {
					corePane.style.display = 'none';
					override.className = 'col-md-12';
					//console.log('Todo set state of jform_show_core');
				} else if (cStateActiveSwitch === 'jform_show_core1' && jform_show_core) {
					corePane.style.display = 'block';
					override.className = 'col-md-6';
					//console.log('Todo set state of jform_show_core');
					//console.log(jform_show_core);
				}

				// Prestate the diff view state

				// Set jform_show_diff0 as default
				var cStateActiveSwitch = 'jform_show_diff0';

				// Set jform_show_core0 with the value in storage if not undefined
				if (dState.activeSwitch !== undefined) {
					cStateActiveSwitch = dState.activeSwitch;
				}

				if (cStateActiveSwitch === 'jform_show_diff0' && jform_show_diff) {
					diffMain.style.display = 'none';
					//console.log('Todo set state of jform_show_diff');
				} else if (cStateActiveSwitch === 'jform_show_diff1' && jform_show_diff) {
					diffMain.style.display = 'block';
					//console.log('Todo set state of jform_show_diff');
				}
			}
		};

		if (jform_show_core || jform_show_diff) {
			setPrestate();
		} else if (override && conditionalSection) {
			conditionalSection.className = 'col-md-12';
			override.className = 'col-md-12';
		}

		var diffs = [].slice.call(document.querySelectorAll('#original'));

		for (var i = 0, l = diffs.length; i < l; i += 1) {
			compare(diffs[i], diffs[i].nextElementSibling);
		}
	});
})();
