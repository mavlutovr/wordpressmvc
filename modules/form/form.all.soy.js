// This file was automatically generated from form.all.soy.
// Please don't edit this file by hand.

/**
 * @fileoverview Templates in namespace wdpro.templates.forms.
 */

if (typeof wdpro == 'undefined') { var wdpro = {}; }
if (typeof wdpro.templates == 'undefined') { wdpro.templates = {}; }
if (typeof wdpro.templates.forms == 'undefined') { wdpro.templates.forms = {}; }


wdpro.templates.forms.form = function(opt_data, opt_ignored) {
  var output = '<div class="wdpro-form"><div class="JS_messages_container"></div>' + ((! opt_data.params['removeFormTag']) ? '<form action="' + soy.$$escapeHtml(opt_data.form['action']) + '" method="' + soy.$$escapeHtml(opt_data.form['method']) + '" class="' + soy.$$escapeHtml(opt_data.params['class']) + '">' : '') + '<div class="wdpro-form-width JS_groups_container">';
  var groupList14 = opt_data.groups;
  var groupListLen14 = groupList14.length;
  for (var groupIndex14 = 0; groupIndex14 < groupListLen14; groupIndex14++) {
    var groupData14 = groupList14[groupIndex14];
    output += soy.$$filterNoAutoescape(groupData14);
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
    output += ': \n\n';
    var errorList28 = opt_data.errors;
    var errorListLen28 = errorList28.length;
    for (var errorIndex28 = 0; errorIndex28 < errorListLen28; errorIndex28++) {
      var errorData28 = errorList28[errorIndex28];
      output += ((! (errorIndex28 == 0)) ? ', ' : '') + soy.$$escapeHtml(errorData28);
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


wdpro.templates.forms.group = function(opt_data, opt_ignored) {
  var output = '<div class="wdpro-form-group JS_group">';
  var elementList39 = opt_data.elements;
  var elementListLen39 = elementList39.length;
  for (var elementIndex39 = 0; elementIndex39 < elementListLen39; elementIndex39++) {
    var elementData39 = elementList39[elementIndex39];
    output += soy.$$filterNoAutoescape(elementData39);
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
  var attrNameList226 = soy.$$getMapKeys(opt_data.attrs);
  var attrNameListLen226 = attrNameList226.length;
  for (var attrNameIndex226 = 0; attrNameIndex226 < attrNameListLen226; attrNameIndex226++) {
    var attrNameData226 = attrNameList226[attrNameIndex226];
    output += (opt_data.attrs[attrNameData226]) ? ' ' + soy.$$escapeHtml(attrNameData226) + '="' + soy.$$escapeHtml(opt_data.attrs[attrNameData226]) + '"' : '';
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
  return ((opt_data.data['nothing']) ? '' : '') + '<input type="submit" value="' + soy.$$escapeHtml(opt_data.data['value'] ? opt_data.data['value'] : '') + '"' + wdpro.templates.forms.attrs(opt_data) + '/>';
};
if (goog.DEBUG) {
  wdpro.templates.forms.submitField.soyTemplateName = 'wdpro.templates.forms.submitField';
}


wdpro.templates.forms.selectField = function(opt_data, opt_ignored) {
  var output = ((opt_data.attrs['nothing']) ? '' : '') + '<select ' + wdpro.templates.forms.attrs(opt_data) + '>';
  var nList315 = soy.$$getMapKeys(opt_data.data['options']);
  var nListLen315 = nList315.length;
  for (var nIndex315 = 0; nIndex315 < nListLen315; nIndex315++) {
    var nData315 = nList315[nIndex315];
    output += '<option value="' + soy.$$escapeHtml(opt_data.data['options'][nData315][0]) + '"' + ((opt_data.data['value'] == opt_data.data['options'][nData315][0]) ? ' selected="selected"' : '') + '>' + soy.$$escapeHtml(opt_data.data['options'][nData315][1]) + '</option>';
  }
  output += '</select>';
  return output;
};
if (goog.DEBUG) {
  wdpro.templates.forms.selectField.soyTemplateName = 'wdpro.templates.forms.selectField';
}


wdpro.templates.forms.hiddenField = function(opt_data, opt_ignored) {
  return ((opt_data.data['nothing']) ? '' : '') + '<input type="hidden" value="' + soy.$$escapeHtml(opt_data.data['value'] ? opt_data.data['value'] : '') + '"' + wdpro.templates.forms.attrs(opt_data) + '/>';
};
if (goog.DEBUG) {
  wdpro.templates.forms.hiddenField.soyTemplateName = 'wdpro.templates.forms.hiddenField';
}


wdpro.templates.forms.fileField = function(opt_data, opt_ignored) {
  return ((opt_data.data['nothing']) ? '' : '') + '<div><div><input type="file" value=""' + ((opt_data.data['multiple']) ? 'multiple="multiple"' : '') + wdpro.templates.forms.attrs(opt_data) + '/><input type="hidden" name="' + soy.$$escapeHtml(opt_data.attrs['name']) + '" class="js-file-name" value="' + soy.$$escapeHtml(opt_data.attrs['value']) + '" /></div><div class="js-file_list"></div></div>';
};
if (goog.DEBUG) {
  wdpro.templates.forms.fileField.soyTemplateName = 'wdpro.templates.forms.fileField';
}


wdpro.templates.forms.requiredStar = function(opt_data, opt_ignored) {
  return '<span class="required_star" title="\u041F\u043E\u043B\u0435 \u043E\u0431\u044F\u0437\u0430\u0442\u0435\u043B\u044C\u043D\u043E \u0434\u043B\u044F \u0437\u0430\u043F\u043E\u043B\u043D\u0435\u043D\u0438\u044F">*</span>';
};
if (goog.DEBUG) {
  wdpro.templates.forms.requiredStar.soyTemplateName = 'wdpro.templates.forms.requiredStar';
}


wdpro.templates.forms.fileLoaded = function(opt_data, opt_ignored) {
  return '<div class="g-mt5 js-sortable"><div class="g-inline">' + ((opt_data.url) ? '<a href="' + soy.$$escapeHtml(opt_data.url) + '" target="_blank">' : '') + soy.$$escapeHtml(opt_data.name) + ((opt_data.url) ? '</a>' : '') + '</div><div class="wdpro-button-16 wdpro-button-del js-del" title="\u0423\u0434\u0430\u043B\u0438\u0442\u044C \u0444\u0430\u0439\u043B"></div></div>';
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