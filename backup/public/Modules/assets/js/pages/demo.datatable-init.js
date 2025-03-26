$(document).ready(function () {
  "use strict";
  $("#basic-datatable").DataTable({
    ordering: false,
    // keys: !0,
    language: {
      paginate: {
    
      },
    },
    drawCallback: function () {
      $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
    },
  });
  $("#basic-datatable1").DataTable({
    ordering: false,
    // keys: !0,
    language: {
      paginate: {
    
      },
    },
    drawCallback: function () {
      $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
    },
  });
  var a = $("#datatable-buttons").DataTable({
    ordering: false,
    lengthChange: !1,
    buttons: ["copy", "print"],
    language: {
      paginate: {
        previous: "<i class='mdi mdi-chevron-left'>",
        next: "<i class='mdi mdi-chevron-right'>",
      },
    },
    drawCallback: function () {
      $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
    },
  });
  $("#selection-datatable").DataTable({
    ordering: false,
    select: { style: "multi" },
    language: {
      paginate: {
        previous: "<i class='mdi mdi-chevron-left'>",
        next: "<i class='mdi mdi-chevron-right'>",
      },
    },
    drawCallback: function () {
      $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
    },
  }),
    a
      .buttons()
      .container()
      .appendTo("#datatable-buttons_wrapper .col-md-6:eq(0)"),
    $("#alternative-page-datatable").DataTable({
      ordering: false,
      pagingType: "full_numbers",
      drawCallback: function () {
        $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
      },
    }),
    $("#scroll-vertical-datatable").DataTable({
      scrollY: "350px",
      scrollCollapse: !0,
      paging: !1,
      language: {
        paginate: {
          previous: "<i class='mdi mdi-chevron-left'>",
          next: "<i class='mdi mdi-chevron-right'>",
        },
      },
      drawCallback: function () {
        $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
      },
    }),
    $("#scroll-horizontal-datatable").DataTable({
      ordering: false,
      scrollX: !0,
      language: {
        paginate: {
          previous: "<i class='mdi mdi-chevron-left'>",
          next: "<i class='mdi mdi-chevron-right'>",
        },
      },
      drawCallback: function () {
        $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
      },
    }),
    $("#complex-header-datatable").DataTable({
      ordering: false,
      language: {
        paginate: {
          previous: "<i class='mdi mdi-chevron-left'>",
          next: "<i class='mdi mdi-chevron-right'>",
        },
      },
      drawCallback: function () {
        $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
      },
      columnDefs: [{ visible: !1, targets: -1 }],
    }),
    $("#row-callback-datatable").DataTable({
      ordering: false,
      language: {
        paginate: {
          previous: "<i class='mdi mdi-chevron-left'>",
          next: "<i class='mdi mdi-chevron-right'>",
        },
      },
      drawCallback: function () {
        $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
      },
      createdRow: function (a, e, t) {
        15e4 < +e[5].replace(/[\$,]/g, "") &&
          $("td", a).eq(5).addClass("text-danger");
      },
    }),
    $("#state-saving-datatable").DataTable({
      stateSave: !0,
      ordering: false,
      language: {
        paginate: {
          previous: "<i class='mdi mdi-chevron-left'>",
          next: "<i class='mdi mdi-chevron-right'>",
        },
      },
      drawCallback: function () {
        $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
      },
    }),
    $(".dataTables_length select").addClass("form-select form-select-sm"),
    $(".dataTables_length label").addClass("form-label");
});
