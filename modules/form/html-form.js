{
  const $ = jQuery;

  class HtmlForm {
  
    constructor(htmlElement) {

      const self = this;
      
      this.$html = $(htmlElement);

      // Init Elements
      this.$html.find('.JS_element').each(function () {
        self.initElement($(this));
      });
    }


    initElement($element) {
      if ($element.is(':checkbox')) {
        new HtmlFormCheckbox($element);
      }
      else {
        new HtmlFormElement($element);
      }
    }
    
  }


  class HtmlFormElement {

    constructor($html) {
      this.$html = $html;
      this.$input = this.$html.find('.JS_field');
      this.$center = this.$html.find('.js-field-center');

      this.initCenter();
    }


    getValue() {
      return this.$input.val();
    }


    initCenter() {
      if (!this.$center.length) return;

      let focused = false;

      const update = () => {
        if (this.getValue() != '' || focused) {
          this.$center.hide();
        }
        else {
          this.$center.show();
          
          if (this.$input.is('.seobit-input-focus')) {
            this.$center.addClass('_focused');
          }
          else {
            this.$center.removeClass('_focused');
          }
        }
      };

      this.$input.on('focus', () => {
        focused = true;
        update();
      });

      this.$input.on('blur', () => {
        focused = false;
        update();
      });

      this.$input.on('change keyup', update);

      update();
    }


  }


  class HtmlFormCheckbox extends HtmlFormElement {

    getValue() {
      if (this.$input.is(':checked')) {
        return this.$input.val() || 1;
      }

      return 0;
    }
  }


  wdpro.htmlForms = {
    HtmlForm,
  };


  jQuery.fn.htmlForm = function (params) {

    $(this).each(function () {
      const $container = $(this);
      const form = new HtmlForm($container.get(0));
    });

    return this;
  };

}


