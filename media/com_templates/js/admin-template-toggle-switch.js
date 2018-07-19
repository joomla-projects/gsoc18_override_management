/**
* PLEASE DO NOT MODIFY THIS FILE. WORK ON THE ES6 VERSION.
* OTHERWISE YOUR CHANGES WILL BE REPLACED ON THE NEXT BUILD.
**/

/**
 * @copyright  Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
(function () {
  'use strict';

  window.showDiffChanged = function showDiffChanged() {
    var diffMain = document.getElementById('diff-main');

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
    var override = document.getElementById('override-pane');
    var corePane = document.getElementById('core-pane');

    if (corePane && override && corePane.classList.contains('active')) {
      corePane.classList.remove('active');
      override.className = 'col-md-12';

      if (typeof Storage !== 'undefined') {
        localStorage.removeItem('coreSwitchState');
      }
    } else if (corePane && override && !corePane.classList.contains('active')) {
      corePane.classList.add('active');
      override.className = 'col-md-6';
      setTimeout(function () {
        Joomla.editors.instances.jform_core.refresh();
      }, 500);

      if (typeof Storage !== 'undefined') {
        localStorage.setItem('coreSwitchState', 'checked');
      }
    }
  };

  document.addEventListener('DOMContentLoaded', function () {
    var JformShowDiff = document.getElementById('jform_show_diff');
    var JformShowCore = document.getElementById('jform_show_core');

    if (typeof Storage !== 'undefined' && localStorage.getItem('diffSwitchState') && JformShowDiff) {
      setTimeout(function () {
        JformShowDiff.newActive = 1;
        JformShowDiff.switch();
      }, 500);
      window.showDiffChanged();
    }

    if (typeof Storage !== 'undefined' && localStorage.getItem('coreSwitchState') && JformShowCore) {
      setTimeout(function () {
        JformShowCore.newActive = 1;
        JformShowCore.switch();
      }, 500);
      window.showCoreChanged();
    }
  });
})();
