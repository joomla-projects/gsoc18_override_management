/**
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
((document, submitForm) => {
  'use strict';

  // Selectors used by this script
  const buttonDataSelector = 'data-submit-task';

  /**
   * Submit the task
   * @param task
   * @param form
   */
  const submitTask = (task, form) => {
    if (task === 'templates.cancel' || document.formvalidator.isValid(form)) {
      submitForm(task, form);
    }
  };

  /**
   * Register events
   */
  const registerEvents = () => {
    const buttons = [].slice.call(document.querySelectorAll(`[${buttonDataSelector}]`));
    buttons.forEach((button) => {
      button.addEventListener('click', (e) => {
        e.preventDefault();
        const task = e.target.getAttribute(buttonDataSelector);
        submitTask(task, e.target.form);
      });
    });
  };

  document.addEventListener('DOMContentLoaded', () => {
    registerEvents();
  });
})(document, Joomla.submitform);
