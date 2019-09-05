// This file was automatically generated from dialog.all.soy.
// Please don't edit this file by hand.

/**
 * @fileoverview Templates in namespace dialog_templates.
 */

if (typeof dialog_templates == 'undefined') { var dialog_templates = {}; }


dialog_templates.container = function(opt_data, opt_ignored) {
  return '<div class="dialog-container"></div>';
};
if (goog.DEBUG) {
  dialog_templates.container.soyTemplateName = 'dialog_templates.container';
}


dialog_templates.window = function(opt_data, opt_ignored) {
  return '<div class="dialog js-dialog"><div class="dialog-header"><div class="js-dialog-title dialog-title"></div><div class="js-dialog-close dialog-close" title="\u0417\u0430\u043A\u0440\u044B\u0442\u044C">' + ((opt_data.closeSymbol) ? soy.$$filterNoAutoescape(opt_data.closeSymbol) : '\u2715') + '</div></div><div class="js-dialog-content dialog-content"></div></div>';
};
if (goog.DEBUG) {
  dialog_templates.window.soyTemplateName = 'dialog_templates.window';
}


dialog_templates.substrate = function(opt_data, opt_ignored) {
  return '<div class="dialog-substrate"></div>';
};
if (goog.DEBUG) {
  dialog_templates.substrate.soyTemplateName = 'dialog_templates.substrate';
}
