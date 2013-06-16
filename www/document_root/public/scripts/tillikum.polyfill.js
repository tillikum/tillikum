// HTML5 autofocus polyfill
if (!Modernizr['input']['autofocus']) {
  $('[autofocus=]').focus();
}

// HTML5 date picker polyfill
if (Modernizr['inputtypes']['date']) {
  $.datepicker.setDefaults({
    "beforeShow": function(input) {
      return false;
    }
  });
}
