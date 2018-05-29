/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function() {
	//alert('here I am');
	document.addEventListener('DOMContentLoaded', function() {

		function compare(original, changed)
		{
			var display  = changed.nextElementSibling,
			    color    = '',
			    pre     = null,
			    diff     = JsDiff.diffLines(original.innerHTML, changed.innerHTML),
			    fragment = document.createDocumentFragment();

			diff.forEach(function(part){
				color = part.added ? '#a6f3a6' : part.removed ? '#f8cbcb' : '';
				pre = document.createElement('pre');
				pre.style.backgroundColor = color;
				pre.style.borderRadius = '.2rem';
				pre.appendChild(document.createTextNode(part.value.split('&lt;').join('<').split('&gt;').join('>')));
				fragment.appendChild(pre);
			});

			display.appendChild(fragment);
		}

		var diffs = document.querySelectorAll('.original');
		for (var i = 0, l = diffs.length; i < l; i++) {
			compare(diffs[i], diffs[i].nextElementSibling)
		}

	});
})();