{
  const $ = jQuery;

  class HtmlForm {
  
    constructor(htmlElement, params = { ajax: null }) {

      const self = this;
      
      this.$html = $(htmlElement);
      this.$form = this.$html.is('form') ? this.$html : this.$html.find('form');
      this.$submitButton = this.$form.find('[type="submit"]');
      this.$messages = this.$html.find('.js-messages');

      if (params.ajax === null) {
        params.ajax = this.$form.data('ajax');
      }

      // Init Elements
      this.$form.find('.JS_element').each(function () {
        self.initElement($(this));
      });

      // Submit
      this.$form.on('submit', e => {
        if (params.ajax) {
          let url = this.$form.attr('action');
          this.loading();

          let data = this.$form.serializeObject();

          wdpro.ajax(url, data, res => {

            if (res.error) {
              this.showMessage(res.error, { type: error })
            }

            else if (res.message) {
              this.showMessage(res.message);
            }

            if (res.metrika) {
              wdpro.yandexMetrikaGoal(res.metrika);
            }

            if (res.hideForm) {
              if (res.message) {
                this.$messages.css('min-height', this.$form.height());
              }
              this.hide();
            }
          });

          e.preventDefault();
          return false;
        }
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


    hide() {
      this.$form.hide();
    }


    showMessage(message, params = { type: '' }) {
      this.$messages.empty();

      if (message) {
        let $message = $('<div/>').html(message);
        if (params.type === 'error') {
          $message.addClass('error');
        }
        this.$messages.show().append($message);
      }

      else {
        this.$messages.hide();
      }
    }


    valid() {

    }


    loading() {
      this.$submitButton.loading();
    }


    loadingStop() {
      this.$submitButton.loadingStop();
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


