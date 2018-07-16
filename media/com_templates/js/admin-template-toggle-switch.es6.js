/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

  window.showDiffChanged = function showDiffChanged() {
    const JformShowDiff = document.getElementById('jform_show_diff');
    const diffMain = document.getElementById('diff-main');

    setTimeout(() => {
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
    const JformShowCore = document.getElementById('jform_show_core');
    const override = document.getElementById('override-pane');
    const corePane = document.getElementById('core-pane');

    setTimeout(() => {
      if (JformShowCore.inputs[0]
          && JformShowCore.inputs[0].checked === true
          && corePane
          && override) {
        JformShowCore.spans[0].classList.add('active');
        JformShowCore.spans[1].classList.remove('active');
        corePane.classList.remove('active');
        override.className = 'col-md-12';

        if (typeof Storage !== 'undefined') {
          localStorage.removeItem('coreSwitchState');
        }
      }

      if (JformShowCore.inputs[0]
          && JformShowCore.inputs[0].checked === false
          && corePane
          && override) {
        corePane.classList.add('active');
        override.className = 'col-md-6';
        JformShowCore.spans[0].classList.remove('active');
        JformShowCore.spans[1].classList.add('active');
        JformShowCore.inputs[1].parentNode.classList.add('active');
        setTimeout(() => {
          Joomla.editors.instances.jform_core.refresh();
        }, 500);

        if (typeof Storage !== 'undefined') {
          localStorage.setItem('coreSwitchState', 'checked');
        }
      }
    }, 500);
  };

  document.addEventListener('DOMContentLoaded', () => {
    const JformShowDiff = document.getElementById('jform_show_diff');
    const JformShowCore = document.getElementById('jform_show_core');

    if (typeof Storage !== 'undefined' && localStorage.getItem('diffSwitchState') && JformShowDiff) {
      setTimeout(() => {
        JformShowDiff.inputs[0].checked = 'false';
        JformShowDiff.inputs[1].checked = 'true';
      }, 500);
      window.showDiffChanged();
    }

    if (typeof Storage !== 'undefined' && localStorage.getItem('coreSwitchState') && JformShowCore) {
      setTimeout(() => {
        JformShowCore.inputs[0].checked = 'false';
        JformShowCore.inputs[1].checked = 'true';
      }, 500);
      window.showCoreChanged();
    }
  });
})();
