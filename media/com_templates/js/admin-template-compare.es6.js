/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  document.addEventListener('DOMContentLoaded', () => {
    const decodeHtmlspecialChars = (text) => {
      const map = {
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
        '&#8221;': '”',
      };

      return text.replace(/\&[\w\d\#]{2,5}\;/g, (m) => { const n = map[m]; return n; });
    };

    const compare = (original, changed) => {
      const display = changed.nextElementSibling;
      let color = '';
      let pre = null;
      const diff = JsDiff.diffLines(original.innerHTML, changed.innerHTML);
      const fragment = document.createDocumentFragment();

      diff.forEach((part) => {
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

    const buttonDataSelector = 'onclick-task';
    const override = document.getElementById('override-pane');

    const toggle = (e) => {
      const task = e.target.getAttribute(buttonDataSelector);
      if (task === 'template.show.core') {
        const element = document.getElementById('core-pane');
        if (element) {
          const { display } = element.style;
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
        const element = document.getElementById('diff-main');
        if (element) {
          const { display } = element.style;
          if (display === 'none') {
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
    };

    const buttons = [].slice.call(document.querySelectorAll(`[${buttonDataSelector}]`));
    const conditionalSection = document.getElementById('conditional-section');

    if (buttons.length !== 0) {
      buttons.forEach((button) => {
        button.addEventListener('click', (e) => {
          e.preventDefault();
          toggle(e);
        });
      });
    } else {
      conditionalSection.className = 'col-md-12';
      override.className = 'col-md-12';
    }

    const diffs = [].slice.call(document.querySelectorAll('#original'));
    for (let i = 0, l = diffs.length; i < l; i += 1) {
      compare(diffs[i], diffs[i].nextElementSibling);
    }
  });
})();
