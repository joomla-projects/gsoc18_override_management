/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function() {

	document.addEventListener('DOMContentLoaded', function() {

		function compare(original, changed) {
			var display  = changed.nextElementSibling,
			    color    = '',
			    pre     = null,
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

		var diffs = [].slice.call(document.querySelectorAll('#original'));
		for (var i = 0, l = diffs.length; i < l; i++) {
			compare(diffs[i], diffs[i].nextElementSibling)
		}

	});
})();
