// DataTable defaults
$.extend($.fn.dataTable.defaults, {
  bJQueryUI: true,
  sDom: '<"H">t<"F"ip>'
});

// DataTable extension functions
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
