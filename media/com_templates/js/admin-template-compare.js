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
        var n = map[m];return n;
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

    var buttonDataSelector = 'onclick-task';
    var override = document.getElementById('override-pane');

    var toggle = function toggle(e) {
      var task = e.target.getAttribute(buttonDataSelector);
      if (task === 'template.show.core') {
        var element = document.getElementById('core-pane');
        if (element) {
          var display = element.style.display;

          if (display === 'none') {
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
      } else if (task === 'template.show.diff') {
        var _element = document.getElementById('diff-main');
        if (_element) {
          var _display = _element.style.display;

          if (_display === 'none') {
            e.target.className = 'btn btn-success';
            e.target.innerHTML = Joomla.JText._('COM_TEMPLATES_LAYOUTS_DIFFVIEW_HIDE_DIFF');
            _element.style.display = 'block';
          } else {
            e.target.className = 'btn btn-danger';
            e.target.innerHTML = Joomla.JText._('COM_TEMPLATES_LAYOUTS_DIFFVIEW_SHOW_DIFF');
            _element.style.display = 'none';
          }
        }
      }
    };

    var buttons = [].slice.call(document.querySelectorAll('[' + buttonDataSelector + ']'));
    var conditionalSection = document.getElementById('conditional-section');

    if (buttons.length !== 0) {
      buttons.forEach(function (button) {
        button.addEventListener('click', function (e) {
          e.preventDefault();
          toggle(e);
        });
      });
    } else {
      conditionalSection.className = 'col-md-12';
      override.className = 'col-md-12';
    }

    var diffs = [].slice.call(document.querySelectorAll('#original'));
    for (var i = 0, l = diffs.length; i < l; i += 1) {
      compare(diffs[i], diffs[i].nextElementSibling);
    }
  });
})();
