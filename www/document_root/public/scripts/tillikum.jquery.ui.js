// jQuery UI autocomplete defaults
$.extend($.ui.autocomplete.prototype.options, {
  // 200ms seems to be a comfortably responsive default
  delay: 200
});

// jQuery UI datepicker defaults
var defaults = {
  changeMonth: true,
  changeYear: true,
  dateFormat: 'yy-mm-dd', // HTML5 spec
  showAnim: ''
};

$.datepicker.setDefaults(defaults);

// jQuery UI tooltip defaults
$.extend($.ui.tooltip.prototype.options, {
  hide: false,
  position: {
    at: 'bottom',
    my: 'top'
  },
  show: false
});
