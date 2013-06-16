angular
  .module('tillikum', ['ui'])
  .directive('tillikumDefaultFacilityRule', ['$compile', function($compile) {
    return {
      link: function(scope, element, attrs, controller) {
        // Create and compile a button element
        var $button = $('<button ui-jq="button" style="margin-left: 5px;">Select default rule</button>');
        $compile($button)(scope);

        // Place it after the dropdown
        element.after($button);

        $button.on('click', function(ev) {
          ev.preventDefault();

          var facilityId = $('#' + element.data('facilityId')).val();

          if (!facilityId) {
            alert('You have not selected a facility yet.');

            return;
          }

          var url = Tillikum.BASE + '/facility/facility/defaultrule/id/' + facilityId + '?'

          var start = $('#' + element.data('rateStart')).val();
          var end = $('#' + element.data('rateEnd')).val();

          if (!start) {
            alert('You have not selected a rate start date yet.');

            return;
          }

          if (!end) {
            alert('You have not selected a rate end date yet.');

            return;
          }

          var params = [];
          params.push('start=' + start);
          params.push('end=' + end);

          $.getJSON(url + params.join('&'), function(templateId) {
            element.val(templateId);
          });
        });
      }
    };
  }])
  .directive('tillikumDefaultMealplanRule', ['$compile', function($compile) {
    return {
      link: function(scope, element, attrs, controller) {
        // Create and compile a button element
        var $button = $('<button ui-jq="button" style="margin-left: 5px;">Select default rule</button>');
        $compile($button)(scope);

        // Place it after the dropdown
        element.after($button);

        $button.on('click', function(ev) {
          ev.preventDefault();

          var mealplanId = $('#' + element.data('mealplanId')).val();

          if (!mealplanId) {
            alert('You have not selected a meal plan yet.');

            return;
          }

          var url = Tillikum.BASE + '/mealplan/mealplan/defaultrule/id/' + mealplanId + '?'

          var start = $('#' + element.data('rateStart')).val();
          var end = $('#' + element.data('rateEnd')).val();

          if (!start) {
            alert('You have not selected a rate start date yet.');

            return;
          }

          if (!end) {
            alert('You have not selected a rate end date yet.');

            return;
          }

          var params = [];
          params.push('start=' + start);
          params.push('end=' + end);

          $.getJSON(url + params.join('&'), function(templateId) {
            element.val(templateId);
          });
        });
      }
    };
  }]);
