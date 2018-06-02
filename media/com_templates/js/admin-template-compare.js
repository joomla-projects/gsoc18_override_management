/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function() {

	document.addEventListener('DOMContentLoaded', function() {

		function compare(original, changed) {
			var display  = changed.nextElementSibling,
			    color    = '',
			    pre      = null,
			    diff     = JsDiff.diffLines(original.innerHTML, changed.innerHTML),
			    fragment = document.createDocumentFragment();

			diff.forEach(function(part){
				color = part.added ? '#a6f3a6' : part.removed ? '#f8cbcb' : '';
				pre = document.createElement('pre');
				pre.style.backgroundColor = color;
				pre.className = 'diffview';
				pre.appendChild(document.createTextNode(decodeHtmlspecialChars(part.value)));
				fragment.appendChild(pre);
			});

			display.appendChild(fragment);
		}

		function decodeHtmlspecialChars(text) {
			var map = {
				'&amp;'  : '&',
				'&#038;' : "&",
				'&lt;'   : '<',
				'&gt;'   : '>',
				'&quot;' : '"',
				'&#039;' : "'",
				'&#8217;': "’",
				'&#8216;': "‘",
				'&#8211;': "–",
				'&#8212;': "—",
				'&#8230;': "…",
				'&#8221;': '”'
			};

			return text.replace(/\&[\w\d\#]{2,5}\;/g, function(m) { return map[m]; });
		}

		function empty(obj) {
			for(var key in obj) {
				if(obj.hasOwnProperty(key))
				return false;
			}
			return true;
		}

		var buttonDataSelector = 'onclick-task';
		var buttons=[].slice.call(document.querySelectorAll('[' + buttonDataSelector + ']'));
		var override = document.getElementById('override-pane');
		var conditionalSection = document.getElementById('conditional-section');

		if(!empty(buttons)) {
			buttons.forEach(function(button) {
				button.addEventListener('click', function(e) {
				e.preventDefault();
				var task = e.target.getAttribute(buttonDataSelector);
					if(task == 'template.show.core') {
						var element = document.getElementById('core-pane');
						if(element) {
							var display = element.style.display;
							if(display == 'none') {
								e.target.className = 'btn btn-success';
								e.target.innerHTML = Joomla.JText._('COM_TEMPLATES_LAYOUTS_DIFFVIEW_HIDE_CORE');
								element.style.display = 'block';
								override.className = 'col-md-6';
							} else {
								e.target.className = 'btn btn-danger';
								e.target.innerHTML = Joomla.JText._('COM_TEMPLATES_LAYOUTS_DIFFVIEW_SHOW_CORE');
								element.style.display = 'none';
								override.className = 'col-md-12';
							}
						}
					} else {
						if(task == 'template.show.diff') {
							var element = document.getElementById('diff-main');
							if(element) {
								var display = element.style.display;
								if(display == 'none') {
									e.target.className = 'btn btn-success';
									e.target.innerHTML = Joomla.JText._('COM_TEMPLATES_LAYOUTS_DIFFVIEW_HIDE_DIFF');
									element.style.display = 'block';
								} else {
									e.target.className = 'btn btn-danger';
									e.target.innerHTML = Joomla.JText._('COM_TEMPLATES_LAYOUTS_DIFFVIEW_SHOW_DIFF');
									element.style.display = 'none';
								}
							}
						}
					}
				});
			});
		} else {
			conditionalSection.className = 'col-md-12';
			override.className = 'col-md-12';
		}

		var diffs = [].slice.call(document.querySelectorAll('#original'));
		for (var i = 0, l = diffs.length; i < l; i++) {
			compare(diffs[i], diffs[i].nextElementSibling)
		}
	});
})();
