// This file was automatically generated from form.all.soy.
// Please don't edit this file by hand.

/**
 * @fileoverview Templates in namespace wdpro.templates.forms.
 */

if (typeof wdpro == 'undefined') { var wdpro = {}; }
if (typeof wdpro.templates == 'undefined') { wdpro.templates = {}; }
if (typeof wdpro.templates.forms == 'undefined') { wdpro.templates.forms = {}; }


wdpro.templates.forms.form = function(opt_data, opt_ignored) {
  var output = '<div class="wdpro-form"><div class="JS_messages_container"></div>';
  if (! opt_data.params['removeFormTag']) {
    output += '<form action="' + soy.$$escapeHtml(opt_data.form['action']) + '" method="' + soy.$$escapeHtml(opt_data.form['method']) + '" class="' + soy.$$escapeHtml(opt_data.params['class']) + '"';
    var attrNameList13 = soy.$$getMapKeys(opt_data.params['attributes']);
    var attrNameListLen13 = attrNameList13.length;
    for (var attrNameIndex13 = 0; attrNameIndex13 < attrNameListLen13; attrNameIndex13++) {
      var attrNameData13 = attrNameList13[attrNameIndex13];
      output += ' ' + soy.$$escapeHtml(attrNameData13) + '="' + soy.$$escapeHtml(opt_data.params['attributes'][attrNameData13]) + '"';
    }
    output += '>';
  }
  output += '<div class="wdpro-form-width JS_groups_container">';
  var groupList22 = opt_data.groups;
  var groupListLen22 = groupList22.length;
  for (var groupIndex22 = 0; groupIndex22 < groupListLen22; groupIndex22++) {
    var groupData22 = groupList22[groupIndex22];
    output += soy.$$filterNoAutoescape(groupData22);
  }
  output += '</div>' + ((! opt_data.params['removeFormTag']) ? '</form>' : '') + '</div>';
  return output;
};
if (goog.DEBUG) {
  wdpro.templates.forms.form.soyTemplateName = 'wdpro.templates.forms.form';
}


wdpro.templates.forms.errors = function(opt_data, opt_ignored) {
  var output = '' + soy.$$escapeHtml(opt_data.errorsPrefix);
  if (opt_data.errors != null && opt_data.errors.length) {
    output += (opt_data.errorsPrefix) ? ': \n\n' : '';
    var errorList38 = opt_data.errors;
    var errorListLen38 = errorList38.length;
    for (var errorIndex38 = 0; errorIndex38 < errorListLen38; errorIndex38++) {
      var errorData38 = errorList38[errorIndex38];
      output += ((! (errorIndex38 == 0)) ? ', ' : '') + soy.$$escapeHtml(errorData38);
    }
    output += '.';
  } else {
    output += '!';
  }
  return output;
};
if (goog.DEBUG) {
  wdpro.templates.forms.errors.soyTemplateName = 'wdpro.templates.forms.errors';
}


wdpro.templates.forms.errorsWithoutPrefix = function(opt_data, opt_ignored) {
  var output = '';
  if (opt_data.errors != null && opt_data.errors.length) {
    var errorList50 = opt_data.errors;
    var errorListLen50 = errorList50.length;
    for (var errorIndex50 = 0; errorIndex50 < errorListLen50; errorIndex50++) {
      var errorData50 = errorList50[errorIndex50];
      output += ((! (errorIndex50 == 0)) ? ', ' : '') + soy.$$escapeHtml(errorData50);
    }
  }
  return output;
};
if (goog.DEBUG) {
  wdpro.templates.forms.errorsWithoutPrefix.soyTemplateName = 'wdpro.templates.forms.errorsWithoutPrefix';
}


wdpro.templates.forms.group = function(opt_data, opt_ignored) {
  var output = '<div class="wdpro-form-group JS_group">';
  var elementList58 = opt_data.elements;
  var elementListLen58 = elementList58.length;
  for (var elementIndex58 = 0; elementIndex58 < elementListLen58; elementIndex58++) {
    var elementData58 = elementList58[elementIndex58];
    output += soy.$$filterNoAutoescape(elementData58);
  }
  output += '</div>';
  return output;
};
if (goog.DEBUG) {
  wdpro.templates.forms.group.soyTemplateName = 'wdpro.templates.forms.group';
}


wdpro.templates.forms.element = function(opt_data, opt_ignored) {
  return '<div class="wdpro-form-element JS_element g-inline-top' + ((opt_data.data['lang']) ? ' js-lang-element lang-element' : '') + '"' + ((opt_data.data['lang']) ? 'data-lang="' + soy.$$escapeHtml(opt_data.data['current_lang']) + '"' : '') + '>' + ((opt_data.data['top']) ? '<div class="wdpro-form-element-top">' + ((opt_data.data['leftExists'] || opt_data.data['left'] != null) ? '<div class="JS_left g-inline-top wdpro-form-element-left JS_left_equalizing_target"></div>' : '') + '<div class="wdpro-form-label g-inline-top' + ((opt_data.data['top']['class']) ? ' ' + soy.$$escapeHtml(opt_data.data['top']['class']) : '') + '"' + ((opt_data.data['top']['style']) ? ' style="' + soy.$$escapeHtml(opt_data.data['top']['style']) + '"' : '') + '>' + ((opt_data.data['top']['labelId']) ? '<label for="' + soy.$$escapeHtml(opt_data.data['top']['labelId']) + '">' : '') + soy.$$filterNoAutoescape(opt_data.data['top']['text']) + ((opt_data.data['top']['requiredStar']) ? ' ' + soy.$$filterNoAutoescape(opt_data.data['top']['requiredStar']) : '') + ((opt_data.data['top']['labelId']) ? '</label>' : '') + '</div></div>' : '') + '<div class="wdpro-form-element-middle">' + ((opt_data.data['leftExists'] || opt_data.data['left'] != null) ? '<div class=" JS_left_equalizing_source ' + ((opt_data.data['autoLeft']) ? 'JS_left_text ' : '') + 'wdpro-form-element-left g-inline-top ">' + ((opt_data.data['left']) ? '<div class="wdpro-form-label' + ((opt_data.data['left']['nowrap']) ? ' g-nowrap' : '') + ((opt_data.data['left']['class']) ? ' ' + soy.$$escapeHtml(opt_data.data['left']['class']) : '') + '"' + ((opt_data.data['left']['style']) ? ' style="' + soy.$$escapeHtml(opt_data.data['left']['style']) + '"' : '') + '>' + ((opt_data.data['left']['labelId']) ? '<label for="' + soy.$$escapeHtml(opt_data.data['left']['labelId']) + '">' : '') + soy.$$filterNoAutoescape(opt_data.data['left']['text']) + ((opt_data.data['left']['requiredStar']) ? ' ' + soy.$$filterNoAutoescape(opt_data.data['left']['requiredStar']) : '') + ((opt_data.data['left']['labelId']) ? '</label>' : '') + '</div>' : '') + '</div>' : '') + '<div class="wdpro-form-element-input g-inline-top' + ((opt_data.data['error']) ? ' wdpro-form-element-error' : '') + '">' + ((opt_data.data['error']) ? '<div class="wdpro-form-error">' + soy.$$filterNoAutoescape(opt_data.data['error']) + '</div>' : '') + ((opt_data.data['center']) ? '<div class="wdpro-form-center js-field-center">' + soy.$$escapeHtml(opt_data.data['center']['text']) + ((opt_data.data['center']['requiredStar']) ? ' ' + soy.$$filterNoAutoescape(opt_data.data['center']['requiredStar']) : '') + '</div>' : '') + '<div class="wdpro-form-element-input_container JS_input_container' + ((opt_data.data['autoWidth']) ? ' JS_auto_width' : '') + '">' + soy.$$filterNoAutoescape(opt_data.data['inputjQueryMarker']) + '</div><div class="JS_input_additional"></div></div>' + ((opt_data.data['right']) ? '<div class="wdpro-form-element-right g-inline-top"><div class="wdpro-form-label' + ((opt_data.data['right']['class']) ? ' ' + soy.$$escapeHtml(opt_data.data['right']['class']) : '') + '"' + ((opt_data.data['right']['style']) ? ' style="' + soy.$$escapeHtml(opt_data.data['right']['style']) + '"' : '') + '>' + ((opt_data.data['right']['labelId']) ? '<label for="' + soy.$$escapeHtml(opt_data.data['right']['labelId']) + '">' : '') + soy.$$filterNoAutoescape(opt_data.data['right']['text']) + ((opt_data.data['right']['requiredStar']) ? ' ' + soy.$$filterNoAutoescape(opt_data.data['right']['requiredStar']) : '') + ((opt_data.data['right']['labelId']) ? '</label>' : '') + '</div></div>' : '') + '</div>' + ((opt_data.data['bottom']) ? '<div class="wdpro-form-element-bottom">' + ((opt_data.data['leftExists'] || opt_data.data['left'] != null) ? '<div class="JS_left g-inline-top wdpro-form-element-left JS_left_equalizing_target"></div>' : '') + '<div class="wdpro-form-label g-inline-top' + ((opt_data.data['bottom']['class']) ? ' ' + soy.$$escapeHtml(opt_data.data['bottom']['class']) : '') + '"' + ((opt_data.data['bottom']['style']) ? ' style="' + soy.$$escapeHtml(opt_data.data['bottom']['style']) + '"' : '') + '>' + soy.$$filterNoAutoescape(opt_data.data['bottom']['text']) + '</div></div>' : '') + '</div>';
};
if (goog.DEBUG) {
  wdpro.templates.forms.element.soyTemplateName = 'wdpro.templates.forms.element';
}


wdpro.templates.forms.icon = function(opt_data, opt_ignored) {
  return '<span class="wdpro-form-element-icon"><img src="' + soy.$$escapeHtml(opt_data.data['icon']) + '" /></span>';
};
if (goog.DEBUG) {
  wdpro.templates.forms.icon.soyTemplateName = 'wdpro.templates.forms.icon';
}


wdpro.templates.forms.label = function(opt_data, opt_ignored) {
  return '<span>:' + soy.$$escapeHtml(opt_data.data['label']) + ((opt_data.data['required']) ? ' *' : '') + '</span>';
};
if (goog.DEBUG) {
  wdpro.templates.forms.label.soyTemplateName = 'wdpro.templates.forms.label';
}


wdpro.templates.forms.attrs = function(opt_data, opt_ignored) {
  var output = '';
  var attrNameList245 = soy.$$getMapKeys(opt_data.attrs);
  var attrNameListLen245 = attrNameList245.length;
  for (var attrNameIndex245 = 0; attrNameIndex245 < attrNameListLen245; attrNameIndex245++) {
    var attrNameData245 = attrNameList245[attrNameIndex245];
    output += (opt_data.attrs[attrNameData245]) ? ' ' + soy.$$escapeHtml(attrNameData245) + '="' + soy.$$escapeHtml(opt_data.attrs[attrNameData245]) + '"' : '';
  }
  return output;
};
if (goog.DEBUG) {
  wdpro.templates.forms.attrs.soyTemplateName = 'wdpro.templates.forms.attrs';
}


wdpro.templates.forms.stringField = function(opt_data, opt_ignored) {
  return ((opt_data.data['nothing']) ? '' : '') + '<input type="text"' + wdpro.templates.forms.attrs(opt_data) + '/>';
};
if (goog.DEBUG) {
  wdpro.templates.forms.stringField.soyTemplateName = 'wdpro.templates.forms.stringField';
}


wdpro.templates.forms.emailField = function(opt_data, opt_ignored) {
  return ((opt_data.data['nothing']) ? '' : '') + '<input type="email"' + wdpro.templates.forms.attrs(opt_data) + '/>';
};
if (goog.DEBUG) {
  wdpro.templates.forms.emailField.soyTemplateName = 'wdpro.templates.forms.emailField';
}


wdpro.templates.forms.dateField = function(opt_data, opt_ignored) {
  return ((opt_data.data['nothing']) ? '' : '') + '<span><input type="hidden" name="' + soy.$$escapeHtml(opt_data.data['fieldName']) + '" value="" class="JS_field" /><input type="text"' + wdpro.templates.forms.attrs(opt_data) + '/></span>';
};
if (goog.DEBUG) {
  wdpro.templates.forms.dateField.soyTemplateName = 'wdpro.templates.forms.dateField';
}


wdpro.templates.forms.spinnerField = function(opt_data, opt_ignored) {
  return ((opt_data.data['nothing']) ? '' : '') + '<div><div class="g-inline-block-top"><input type="text"' + wdpro.templates.forms.attrs(opt_data) + '/></div><div class="g-inline-block-top wdpro-form-spinner-buttons"><div class="js-spinner-button wdpro-form-spinner-button" data-delta="1">+</div><div class="js-spinner-button wdpro-form-spinner-button" data-delta="-1">-</div></div></div>';
};
if (goog.DEBUG) {
  wdpro.templates.forms.spinnerField.soyTemplateName = 'wdpro.templates.forms.spinnerField';
}


wdpro.templates.forms.passField = function(opt_data, opt_ignored) {
  return ((opt_data.data['nothing']) ? '' : '') + '<input type="password"' + wdpro.templates.forms.attrs(opt_data) + '/>';
};
if (goog.DEBUG) {
  wdpro.templates.forms.passField.soyTemplateName = 'wdpro.templates.forms.passField';
}


wdpro.templates.forms.textField = function(opt_data, opt_ignored) {
  return ((opt_data.data['nothing']) ? '' : '') + '<textarea' + wdpro.templates.forms.attrs(opt_data) + '>' + soy.$$escapeHtml(opt_data.data['value'] ? opt_data.data['value'] : '') + '</textarea>';
};
if (goog.DEBUG) {
  wdpro.templates.forms.textField.soyTemplateName = 'wdpro.templates.forms.textField';
}


wdpro.templates.forms.checkField = function(opt_data, opt_ignored) {
  return ((opt_data.data['nothing']) ? '' : '') + '<span><input type="hidden" name="' + soy.$$escapeHtml(opt_data.data['fieldName']) + '" value="" /><input type="checkbox"' + wdpro.templates.forms.attrs(opt_data) + '/><label for="' + soy.$$escapeHtml(opt_data.data['labelId']) + '" class="checkbox_design"></label></span>';
};
if (goog.DEBUG) {
  wdpro.templates.forms.checkField.soyTemplateName = 'wdpro.templates.forms.checkField';
}


wdpro.templates.forms.captchaField = function(opt_data, opt_ignored) {
  return ((opt_data.data['nothing']) ? '' : '') + '<span class="wdpro-form-captha--grid"><img src="' + soy.$$escapeHtml(opt_data.data['src']) + '"><span>&#8594;</span><input type="text" required' + wdpro.templates.forms.attrs(opt_data) + '/></span>';
};
if (goog.DEBUG) {
  wdpro.templates.forms.captchaField.soyTemplateName = 'wdpro.templates.forms.captchaField';
}


wdpro.templates.forms.buttonField = function(opt_data, opt_ignored) {
  return ((opt_data.data['nothing']) ? '' : '') + '<input type="button" value="' + soy.$$escapeHtml(opt_data.data['value'] ? opt_data.data['value'] : '') + '"' + wdpro.templates.forms.attrs(opt_data) + '/>';
};
if (goog.DEBUG) {
  wdpro.templates.forms.buttonField.soyTemplateName = 'wdpro.templates.forms.buttonField';
}


wdpro.templates.forms.buttonFieldButtonTag = function(opt_data, opt_ignored) {
  return ((opt_data.data['nothing']) ? '' : '') + '<button ' + wdpro.templates.forms.attrs(opt_data) + '>' + soy.$$escapeHtml(opt_data.data['value'] ? opt_data.data['value'] : '') + '</button>';
};
if (goog.DEBUG) {
  wdpro.templates.forms.buttonFieldButtonTag.soyTemplateName = 'wdpro.templates.forms.buttonFieldButtonTag';
}


wdpro.templates.forms.submitField = function(opt_data, opt_ignored) {
  return ((opt_data.data['nothing']) ? '' : '') + '<input type="submit" value="' + ((opt_data.data['value']) ? soy.$$escapeHtml(opt_data.data['value']) : '') + '"' + wdpro.templates.forms.attrs(opt_data) + '/>';
};
if (goog.DEBUG) {
  wdpro.templates.forms.submitField.soyTemplateName = 'wdpro.templates.forms.submitField';
}


wdpro.templates.forms.selectField = function(opt_data, opt_ignored) {
  var output = ((opt_data.attrs['nothing']) ? '' : '') + '<div><select ' + wdpro.templates.forms.attrs(opt_data) + '>';
  var nList344 = soy.$$getMapKeys(opt_data.data['options']);
  var nListLen344 = nList344.length;
  for (var nIndex344 = 0; nIndex344 < nListLen344; nIndex344++) {
    var nData344 = nList344[nIndex344];
    output += '<option value="' + soy.$$escapeHtml(opt_data.data['options'][nData344][0]) + '"' + ((opt_data.data['value'] == opt_data.data['options'][nData344][0]) ? ' selected="selected"' : '') + '>' + soy.$$escapeHtml(opt_data.data['options'][nData344][1]) + '</option>';
  }
  output += '</select>' + ((opt_data.data['multiple']) ? '<div class="wdpro-form-select-multiple-info">\u0423\u0434\u0435\u0440\u0436\u0438\u0432\u0430\u0439\u0442\u0435 CTRL, \u0447\u0442\u043E\u0431\u044B \u0432\u044B\u0431\u0440\u0430\u0442\u044C \u043D\u0435\u0441\u043A\u043E\u043B\u044C\u043A\u043E</div>' : '') + '</div>';
  return output;
};
if (goog.DEBUG) {
  wdpro.templates.forms.selectField.soyTemplateName = 'wdpro.templates.forms.selectField';
}


wdpro.templates.forms.checksField = function(opt_data, opt_ignored) {
  return ((opt_data.attrs['nothing']) ? '' : '') + '<div><div class="js-checks-hiddens"></div>' + ((opt_data.data['options']) ? wdpro.templates.forms.checksLevel({options: opt_data.data['options'], data: opt_data.data}) : '') + '</div>';
};
if (goog.DEBUG) {
  wdpro.templates.forms.checksField.soyTemplateName = 'wdpro.templates.forms.checksField';
}


wdpro.templates.forms.checksLevel = function(opt_data, opt_ignored) {
  var output = '<div class="wdpro-form-checks-level">';
  var optionList372 = opt_data.options;
  var optionListLen372 = optionList372.length;
  for (var optionIndex372 = 0; optionIndex372 < optionListLen372; optionIndex372++) {
    var optionData372 = optionList372[optionIndex372];
    output += '<div class=""><label>' + ((optionData372['value'] && ! opt_data.data['disabled'][optionData372['value']]) ? '<input type="checkbox" data-value="' + soy.$$escapeHtml(optionData372['value']) + '" class="js-checks-check" /> ' : '') + soy.$$escapeHtml(optionData372['text']) + '</label></div>' + ((optionData372['options']) ? wdpro.templates.forms.checksLevel({options: optionData372['options'], data: opt_data.data}) : '');
  }
  output += '</div>';
  return output;
};
if (goog.DEBUG) {
  wdpro.templates.forms.checksLevel.soyTemplateName = 'wdpro.templates.forms.checksLevel';
}


wdpro.templates.forms.hiddenField = function(opt_data, opt_ignored) {
  return ((opt_data.data['nothing']) ? '' : '') + '<input type="hidden" value="' + soy.$$escapeHtml(opt_data.data['value'] ? opt_data.data['value'] : '') + '"' + wdpro.templates.forms.attrs(opt_data) + '/>';
};
if (goog.DEBUG) {
  wdpro.templates.forms.hiddenField.soyTemplateName = 'wdpro.templates.forms.hiddenField';
}


wdpro.templates.forms.fileField = function(opt_data, opt_ignored) {
  return ((opt_data.data['nothing']) ? '' : '') + '<div><div class="wdpro-input-file-container"><input type="file" value=""' + ((opt_data.data['multiple']) ? 'multiple="multiple"' : '') + wdpro.templates.forms.attrs(opt_data) + '/><input type="hidden" name="' + soy.$$escapeHtml(opt_data.attrs['name']) + '" class="js-file-name" value="' + soy.$$escapeHtml(opt_data.attrs['value']) + '" /></div><div class="js-file_list"></div></div>';
};
if (goog.DEBUG) {
  wdpro.templates.forms.fileField.soyTemplateName = 'wdpro.templates.forms.fileField';
}


wdpro.templates.forms.htmlContent = function(opt_data, opt_ignored) {
  return ((opt_data.attrs['nothing']) ? '' : '') + '<div ' + wdpro.templates.forms.attrs(opt_data) + '>' + soy.$$escapeHtml(opt_data.html) + '</div>';
};
if (goog.DEBUG) {
  wdpro.templates.forms.htmlContent.soyTemplateName = 'wdpro.templates.forms.htmlContent';
}


wdpro.templates.forms.requiredStar = function(opt_data, opt_ignored) {
  return '<span class="required_star" title="\u041F\u043E\u043B\u0435 \u043E\u0431\u044F\u0437\u0430\u0442\u0435\u043B\u044C\u043D\u043E \u0434\u043B\u044F \u0437\u0430\u043F\u043E\u043B\u043D\u0435\u043D\u0438\u044F">*</span>';
};
if (goog.DEBUG) {
  wdpro.templates.forms.requiredStar.soyTemplateName = 'wdpro.templates.forms.requiredStar';
}


wdpro.templates.forms.fileLoaded = function(opt_data, opt_ignored) {
  return '<div class="g-mt5 js-sortable wdpro-input-file-list-element"><div class="g-inline wdpro-input-file-list-element-label">' + ((opt_data.url) ? '<a href="' + soy.$$escapeHtml(opt_data.url) + '" target="_blank">' : '') + soy.$$escapeHtml(opt_data.name) + ((opt_data.url) ? '</a>' : '') + '</div><div class="wdpro-button-16 wdpro-button-del js-del" title="\u0423\u0434\u0430\u043B\u0438\u0442\u044C \u0444\u0430\u0439\u043B"></div></div>';
};
if (goog.DEBUG) {
  wdpro.templates.forms.fileLoaded.soyTemplateName = 'wdpro.templates.forms.fileLoaded';
}


wdpro.templates.forms.imageLoaded = function(opt_data, opt_ignored) {
  return '<div class="g-mt10 g-inline-top g-mr20 js-sortable"><div class="g-inline"><a href="' + soy.$$escapeHtml(opt_data.url) + '" target="_blank" class="g-block wdpro-image-border"></a></div><div class="wdpro-button-16 wdpro-button-del js-del" title="\u0423\u0434\u0430\u043B\u0438\u0442\u044C \u0444\u0430\u0439\u043B"></div></div>';
};
if (goog.DEBUG) {
  wdpro.templates.forms.imageLoaded.soyTemplateName = 'wdpro.templates.forms.imageLoaded';
}
