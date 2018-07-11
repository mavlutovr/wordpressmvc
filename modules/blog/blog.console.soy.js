// This file was automatically generated from blog.console.soy.
// Please don't edit this file by hand.

/**
 * @fileoverview Templates in namespace blog_templates.
 */

if (typeof blog_templates == 'undefined') { var blog_templates = {}; }


blog_templates.tags = function(opt_data, opt_ignored) {
  var output = '<div>';
  var tagList4 = opt_data.tags;
  var tagListLen4 = tagList4.length;
  for (var tagIndex4 = 0; tagIndex4 < tagListLen4; tagIndex4++) {
    var tagData4 = tagList4[tagIndex4];
    output += '<span class="a">' + soy.$$escapeHtml(tagData4) + '</span>, ';
  }
  output += '</div>';
  return output;
};
if (goog.DEBUG) {
  blog_templates.tags.soyTemplateName = 'blog_templates.tags';
}
