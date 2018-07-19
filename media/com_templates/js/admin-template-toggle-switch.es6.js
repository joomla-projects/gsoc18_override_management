/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(() => {
  'use strict';

  window.showDiffChanged = function showDiffChanged() {
    const diffMain = document.getElementById('diff-main');

    if (diffMain && diffMain.classList.contains('active')) {
      diffMain.classList.remove('active');

      if (typeof Storage !== 'undefined') {
        localStorage.removeItem('diffSwitchState');
      }
    } else if (diffMain && !diffMain.classList.contains('active')) {
      diffMain.classList.add('active');

      if (typeof Storage !== 'undefined') {
        localStorage.setItem('diffSwitchState', 'checked');
      }
    }
  };

  window.showCoreChanged = function showCoreChanged() {
    const override = document.getElementById('override-pane');
    const corePane = document.getElementById('core-pane');

    if (corePane && override && corePane.classList.contains('active')) {
      corePane.classList.remove('active');
      override.className = 'col-md-12';

      if (typeof Storage !== 'undefined') {
        localStorage.removeItem('coreSwitchState');
      }
    } else if (corePane && override && !corePane.classList.contains('active')) {
      corePane.classList.add('active');
      override.className = 'col-md-6';
      setTimeout(() => {
        Joomla.editors.instances.jform_core.refresh();
      }, 500);

      if (typeof Storage !== 'undefined') {
        localStorage.setItem('coreSwitchState', 'checked');
      }
    }
  };

  document.addEventListener('DOMContentLoaded', () => {
    const JformShowDiff = document.getElementById('jform_show_diff');
    const JformShowCore = document.getElementById('jform_show_core');

    if (typeof Storage !== 'undefined' && localStorage.getItem('diffSwitchState') && JformShowDiff) {
      setTimeout(() => {
        JformShowDiff.newActive = 1;
        JformShowDiff.switch();
      }, 500);
      window.showDiffChanged();
    }

    if (typeof Storage !== 'undefined' && localStorage.getItem('coreSwitchState') && JformShowCore) {
      setTimeout(() => {
        JformShowCore.newActive = 1;
        JformShowCore.switch();
      }, 500);
      window.showCoreChanged();
    }
  });
})();
