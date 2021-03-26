{
  const $ = jQuery;

  class HtmlForm extends EventTarget {
  
    constructor(htmlElement, params) {

      super();

      params = $.extend({
        ajax: null,
        metrikaGoals: {
          startFill: null,
          tryToSend: null,
          sended: null,
        }
      }, params);
      this.params = params;

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
        if (this.params.metrikaGoals?.tryToSend) {
          wdpro.yandexMetrikaGoal(this.params.metrikaGoals?.tryToSend);
        }

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

            if (this.params.metrikaGoals?.sended) {
              wdpro.yandexMetrikaGoal(this.params.metrikaGoals?.sended);
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
      let element;

      if ($element.is(':checkbox')) {
        element = HtmlFormCheckbox($element);
      }
      else {
        element = new HtmlFormElement($element);
      }

      element.addEventListener('start-fill', () => {
        this.dispatchEvent(new Event('start-fill'));
        if (this.params.metrikaGoals?.startFill && !this.startFilled) {
          this.startFilled = true;
          wdpro.yandexMetrikaGoal(this.params.metrikaGoals?.startFill);
        }
      });

      return element;
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


  class HtmlFormElement extends EventTarget {

    constructor($html) {
      super();
      this.$html = $html;
      this.$input = this.$html.find('.JS_field');
      this.$center = this.$html.find('.js-field-center');

      this.initCenter();
      this.initInput();
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


    initInput() {
      this.$input.on('keyup change', () => {
        this.dispatchEvent(new Event('start-fill'));
      });
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


  /**
   * Form initialization
   * 
   * @param {{ ajax: Boolean, metrikaGoals: { startFill: String, tryToSend: String, sended: String }}} params
   * @returns {HtmlForm}
   */
  jQuery.fn.htmlForm = function (params) {

    const $container = $(this);
    return new HtmlForm($container.get(0), params);
  };

}


