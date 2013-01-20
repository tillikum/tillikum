/**
 * jQuery UI autocomplete defaults
 */
(function($) {
  $.extend($.ui.autocomplete.prototype.options, {
    // 200ms seems to be a comfortably responsive default
    delay: 200
  });
})(jQuery);

/*
 * jQuery UI datepicker defaults
 */
(function($) {
  var defaults = {
    changeMonth: true,
    changeYear: true,
    dateFormat: 'yy-mm-dd', // HTML5 spec
    showAnim: ''
  };

  if (Modernizr['inputtypes']['date']) {
    defaults['beforeShow'] = function(input) {
      return false;
    };
  }

  $.datepicker.setDefaults(defaults);
})(jQuery);

/**
 * jQuery UI dialog defaults
 */
(function($) {
  $.extend($.ui.dialog.prototype.options, {
    // In most of our cases it's useful to remove the dialog from the DOM when
    // it's closed.
    close: function() {
      $(this).remove();
    },
    // This can be removed in jQuery UI 1.9 and replaced with whatever the
    // autofocus option ends up being called. This is here only to prevent the
    // calendar from annoyingly popping up when the first input gets focus.
    open: function() {
      $('input[type="date"]', this).blur().datepicker('hide');
    },
    width: '400'
  });
})(jQuery);

/**
 * jQuery UI tooltip defaults
 */
(function($) {
  $.extend($.ui.tooltip.prototype.options, {
    hide: false,
    position: {
      at: 'bottom',
      my: 'top'
    },
    show: false
  });
})(jQuery);

/*
 * DataTable defaults
 */
(function($) {
  $.extend($.fn.dataTable.defaults, {
    bJQueryUI: true,
    sDom: '<"H">t<"F"ip>'
  });
})(jQuery);

/*
 * DataTable extension functions
 */
(function($) {
  $.extend($.fn.dataTableExt.oSort, {
    'data-money-pre': function(a) {
      return [
        (a.match(/data\-currency="(.*?)"/) || [,''])[1],
        parseFloat((a.match(/data\-amount="(.*?)"/) || [,0])[1])
      ];
    },
    'data-money-asc': function(a, b) {
      if (a[0] == b[0]) {
        return a[1] - b[1];
      } else {
        return ((a[0] < b[0]) ? 1 : -1);
      }
    },
    'data-money-desc': function(a, b) {
      if (a[0] == b[0]) {
        return b[1] - a[1];
      } else {
        return ((a[0] < b[0]) ? -1 : 1);
      }
    },

    'time-html-pre': function(a) {
      var datetime = a.match(/datetime="(.*?)"/);

      return datetime ? Date.parse(datetime[1]) : '';
    },
    'time-html-asc': function(a, b) {
      return ((a < b) ? -1 : ((a > b) ? 1 : 0));
    },
    'time-html-desc': function(a, b) {
      return ((a < b) ? 1 : ((a > b) ? -1 : 0));
    }
  });
})(jQuery);

/*
 * HTML5 autofocus polyfill
 */
(function($) {
  if (!Modernizr['input']['autofocus']) {
    $('[autofocus=]').focus();
  }
})(jQuery);

angular.module('tillikum', ['ui'])
.directive('tillikumDefaultFacilityRule', ['$compile', function($compile) {
  return {
    link: function(scope, element, attrs, controller) {
      var $element = $(element);

      // Create and compile a button element
      var $button = $('<button ui-jq="button" style="margin-left: 5px;">Select default rule</button>');
      $compile($button)(scope);
      // Place it after the dropdown
      $element.after($button);

      $button.on('click', function(ev) {
        ev.preventDefault();

        var facilityId = $('#' + $element.data('facilityId')).val();

        if (!facilityId) {
          alert('You have not selected a facility yet.');

          return;
        }

        var url = BASE + '/facility/facility/defaultrule/id/' + facilityId + '?'

        var start = $('#' + $element.data('facilityStart')).val();
        var end = $('#' + $element.data('facilityEnd')).val();

        var params = [];
        if (start) {
          params.push('start=' + start);
        }

        if (end) {
          params.push('end=' + end);
        }

        $.getJSON(url + params.join('&'), function(templateId) {
          $element.children('option[value="' + templateId + '"]')
            .attr('selected', 'selected');
        });
      });
    }
  };
}])
.directive('tillikumDefaultMealplanRule', ['$compile', function($compile) {
  return {
    link: function(scope, element, attrs, controller) {
      var $element = $(element);

      // Create and compile a button element
      var $button = $('<button ui-jq="button" style="margin-left: 5px;">Select default rule</button>');
      $compile($button)(scope);
      // Place it after the dropdown
      $element.after($button);

      $button.on('click', function(ev) {
        ev.preventDefault();

        var mealplanId = $('#' + $element.data('mealplanId')).val();

        if (!mealplanId) {
          alert('You have not selected a meal plan yet.');

          return;
        }

        var url = BASE + '/mealplan/mealplan/defaultrule/id/' + mealplanId + '?'

        var start = $('#' + $element.data('mealplanStart')).val();
        var end = $('#' + $element.data('mealplanEnd')).val();

        var params = [];
        if (start) {
          params.push('start=' + start);
        }

        if (end) {
          params.push('end=' + end);
        }

        $.getJSON(url + params.join('&'), function(templateId) {
          $element.children('option[value="' + templateId + '"]')
            .attr('selected', 'selected');
        });
      });
    }
  };
}]);
