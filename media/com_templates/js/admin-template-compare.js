/**
 * PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
 * OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
 * */

/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function () {
  document.addEventListener('DOMContentLoaded', () => {
    const decodeHtmlspecialChars = function decodeHtmlspecialChars(text) {
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

      return text.replace(/\&[\w\d\#]{2,5}\;/g, (m) => {
        const n = map[m];
        return n;
      });
    };

    const compare = function compare(original, changed) {
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


    const override = document.getElementById('override-pane');
    const corePane = document.getElementById('core-pane');
    const diffMain = document.getElementById('diff-main');

    const conditionalSection = document.getElementById('conditional-section');

    const JformShowCore = document.getElementById('jform_show_core');
    const JformShowDiff = document.getElementById('jform_show_diff');

    if (JformShowCore && corePane) {
      JformShowCore.addEventListener('change', (e) => {
        const displayCore = corePane.style.display;

        if (displayCore === 'none' && e.target.id === 'jform_show_core1') {
          corePane.style.display = 'block';
          override.className = 'col-md-6';
          Joomla.editors.instances.jform_core.refresh();
        } else if (displayCore === 'block' && e.target.id === 'jform_show_core0') {
          corePane.style.display = 'none';
          override.className = 'col-md-12';
        }

        const coreState = {
          activeSwitch: e.target.id,
        };

        if (typeof Storage !== 'undefined') {
          localStorage.setItem('coreSwitchState', JSON.stringify(coreState));
        }
      });
    }

    if (JformShowDiff && diffMain) {
      JformShowDiff.addEventListener('change', (e) => {
        const displayDiff = diffMain.style.display;

        if (displayDiff === 'none' && e.target.id === 'jform_show_diff1') {
          diffMain.style.display = 'block';
        } else if (displayDiff === 'block' && e.target.id === 'jform_show_diff0') {
          diffMain.style.display = 'none';
        }

        const diffState = {
          activeSwitch: e.target.id,
        };

        if (typeof Storage !== 'undefined') {
          localStorage.setItem('diffSwitchState', JSON.stringify(diffState));
        }
      });
    }

    const setPrestate = function setPrestate() {
      if (typeof Storage !== 'undefined') {
        // Fetch the Storage elements
        const cState = JSON.parse(localStorage.getItem('coreSwitchState'));
        const dState = JSON.parse(localStorage.getItem('diffSwitchState'));
        let cStateActiveSwitchCore = 'jform_show_core0';
        let cStateActiveSwitchDiff = 'jform_show_diff0';

        if (cState.activeSwitch !== undefined) {
          cStateActiveSwitchCore = cState.activeSwitch;
        }

        if (cStateActiveSwitchCore === 'jform_show_core0' && JformShowCore) {
          corePane.style.display = 'none';
          override.className = 'col-md-12';
        } else if (cStateActiveSwitchCore === 'jform_show_core1' && JformShowCore) {
          corePane.style.display = 'block';
          override.className = 'col-md-6';
        }

        if (dState.activeSwitch !== undefined) {
          cStateActiveSwitchDiff = dState.activeSwitch;
        }

        if (cStateActiveSwitchDiff === 'jform_show_diff0' && JformShowDiff) {
          diffMain.style.display = 'none';
          // console.log('Todo set state of jform_show_diff');
        } else if (cStateActiveSwitchDiff === 'jform_show_diff1' && JformShowDiff) {
          diffMain.style.display = 'block';
          // console.log('Todo set state of jform_show_diff');
        }
      }
    };

    if (JformShowCore || JformShowDiff) {
      setPrestate();
    } else if (override && conditionalSection) {
      conditionalSection.className = 'col-md-12';
      override.className = 'col-md-12';
    }

    const diffs = [].slice.call(document.querySelectorAll('#original'));

    for (let i = 0, l = diffs.length; i < l; i += 1) {
      compare(diffs[i], diffs[i].nextElementSibling);
    }
  });
}());
