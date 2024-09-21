let fullNumberModal = new bootstrap.Modal("#full-number-modal");
let addRowModal = new bootstrap.Modal("#add-row-modal");

let mainTable = loadDataTable("#main-data-table");

$(document).ready(function () {
  if (localStorage.getItem("username")) {
    $("#username").val(localStorage.getItem("username"));
  }
});

$("#add-row-submit").on("click", function (e) {
  e.preventDefault();
  const form = document.getElementById("add-row-form");
  form.classList.add("was-validated");

  if (form.checkValidity() === false) {
    e.preventDefault();
    e.stopPropagation();
  } else {
    localStorage.setItem("username", $("#username").val());

    $.ajax({
      url: "/",
      type: "POST",
      data: {
        method: "createRow",
        username: $("#username").val(),
        user_input: $("#userInput").val(),
      },
      success: (response) => {
        if (response.status == "OK") {
          notify(response.message, "success");
          $("#add-row-form").find("#userInput").val("");
          addRowModal.hide();
          mainTable.ajax.reload();
        }
      },
      error: (jqXHR, textStatus, errorThrown) => {
        if (jqXHR.status == 400) {
          notify(jqXHR.responseJSON.error, "danger");
        }
      },
    });
  }
});

function loadDataTable(table_id) {
  let table = $(table_id).DataTable({
    ajax: {
      url: "/",
      type: "POST",
      beforeSend: function () {
        $(`${table_id}_processing`).attr(
          "class",
          "position-absolute top-0 start-0 end-0 bottom-0 bg-white z-3"
        );
      },
      data: function (d) {
        d.method = "getData";
        d.draw = d.draw;
        d.start = d.start;
        d.length = d.length;
        d.search.value = d.search ? d.search["value"] : "";
      },
    },
    language: {
      search: `
        <div class='input-group'>
            <i class='bi bi-search input-group-text'></i>
            _INPUT_
        </div>`,
      searchPlaceholder: "Enter username...",
      lengthMenu: `
        <div class='input-group'>
            <span class='input-group-text'>
                <i class='bi bi-arrows-vertical'></i>
                <i class='bi bi-list-columns-reverse'></i>
            </span>
            _MENU_
        </div>
      `,
      processing: `
        <div class="d-flex justify-content-center align-items-center w-100 h-100">
          <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
          </div>
        </div>`,
    },

    serverSide: true,
    processing: true,
    responsive: true,
    paging: true,
    ordering: true,
    searching: true,
    lengthChange: true,
    lengthMenu: [15, 30, 60, 100],
    pageLength: 15,
    autoWidth: false,
    order: [[4, "desc"]],
    dom: `
            <"d-flex flex-column gap-3"
                <"d-flex justify-content-between"
                    <"d-flex gap-3 align-items-center"Bl>f
                >
                <"d-flex w-100 position-relative"rt>
                <"d-flex justify-content-between"ip>
            >`,
    buttons: [
      {
        text: `<i class="bi bi-plus-lg"></i> Add Number`,
        action: function (e, dt, node, config) {
          addRowModal.show();
        },
        // className: "btn btn-primary",
        attr: {
          class: "btn btn-primary",
        },
      },
      {
        text: `<i class="bi bi-arrow-repeat"></i> Reload`,
        action: function (e, dt, node, config) {
          mainTable.ajax.reload(function () {
            notify("Table reloaded", "info");
          });
        },
      },
    ],

    columns: [
      { data: "id", className: "text-center", visible: false },
      {
        data: "username",
        className: "text-center",
        render: (data, type, row) => {
          if (type == "sort") {
            return data;
          }
          return $("<span>").addClass("fw-semibold text-dark").text(data).prop("outerHTML");
        },
      },
      {
        data: "user_input",
        className: "text-center",
        render: (data, type, row) => {
          if (type == "sort") {
            return data;
          }
          return $("<span>").addClass("fw-semibold text-primary").html(
            `<span class="text-dark">F<sub class="text-primary">${data}</sub></span>`
          ).prop("outerHTML");
        },
      },
      {
        data: "fibonacci_num",

        className: "text-center text-break",
        type: "num",
        sortable: false,
        width: "50%",
        render: function (data, type, row) {
          if (data.length > 40) {
            return (
              data.substring(0, 40) +
              "..." +
              $("<a>", {
                href: "javascript:void(0)",
                title: "View full number",
                class: "show-full-number text-decoration-none text-secondary ms-2",
                append: $("<i>").addClass("bi bi-box-arrow-up-right"),
              }).prop("outerHTML")
            );
          }

          return data;
        },
      },
      {
        data: "created_at",
        className: "text-center",
        type: "date",
        type: "num",
        render: (data, type, row, config) => {
          if (type == "sort") {
            return data ? moment(data).format("YYMMDDHHmmss") : -1;
          }
          return $("<span>")
            .addClass("text-secondary fst-italic")
            .text(moment(data).format("DD/MM/YY HH:mm:ss"))
            .prop("outerHTML");
        },
      },
    ],
    drawCallback: function (settings) {
      $(".show-full-number")
        .off("click")
        .on("click", function () {
          const row = mainTable.row($(this).closest("tr"));
          const data = row.data();

          $("#full-number-view").text(data.fibonacci_num);
          $("#user-input-number").text(data.user_input);
          $("#username_value").text(data.username);

          fullNumberModal.show();
        });
    },
  });
  return table;
}

function notify(message, type, time = 3000) {
  function ensureNotificationPlaceholder() {
    if ($(".notification-placeholder").length === 0) {
      const placeholder = $('<div class="notification-placeholder"></div>').css({
        position: "fixed",
        top: "10px",
        left: "50%",
        transform: "translateX(-50%)",
        zIndex: 9999,
        minWidth: "500px",
      });
      $("body").append(placeholder);
    }
    return $(".notification-placeholder");
  }

  const icons = {
    success: "bi bi-check-circle-fill",
    danger: "bi bi-exclamation-triangle-fill",
    info: "bi bi-info-circle-fill",
    warning: "bi bi-exclamation-circle-fill",
    secondary: "bi bi-exclamation-circle-fill",
  };

  let container = ensureNotificationPlaceholder();

  let wrapper = $("<div>")
    .addClass("w-100 alert alert-" + type + " alert-dismissible fade show")
    .css({
      marginTop: "5px",
      opacity: 0,
      transform: "translateY(-20px)",
      transition: "opacity 0.5s ease-in-out, transform 0.5s ease-in-out",
    });
  wrapper.html(
    [
      `<div><i class="${icons[type]} me-1"></i> ${message}</div>`,
      '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
    ].join("")
  );

  container.append(wrapper);

  setTimeout(function () {
    wrapper.css({ opacity: 1, transform: "translateY(0)" });
  }, 10);

  setTimeout(function () {
    wrapper.css({ opacity: 0, transform: "translateY(-20px)" }).one("transitionend", function () {
      wrapper.remove();
    });
  }, time);
}
