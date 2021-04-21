/**
* General Search
*/
(function () {
  function parseJson(file, next) {
    var reader = new FileReader();
    reader.readAsText(file);
    reader.onload = function () {
      try {
        var rows = JSON.parse(reader.result);
      } catch (e) {
        return $.notify('Invalid JSON', 'danger');
      }
      next({rows: rows});
    };
  }

  function parseCsv(file, next) {
    $(file).parse({
      config: {
        header: true,
        skipEmptyLines: true,
        complete: function (results, file) {
          var rows = results.data;
          if (typeof rows !== 'object' || !(rows instanceof Array)) {
            return $.notify('Invalid CSV', 'danger');
          }

          rows.forEach(function(row, i) {
            var json = $.registry();
            for (var key in row) {
              if (!row[key] || !(row[key] + '').length) {
                continue;
              }

              var keys = key.split('/');
              json.set(...keys, row[key]);
            }

            rows[i] = json.get();
          });
          next({rows: rows});
        },
        error: function (error, file, input, reason) {
          $.notify(error.message, 'error');
        }
      }
    });
  }

  function importSend(url, notifier, progress, complete, data) {
    $.post(url, data, function (response) {
      notifier.fadeOut('fast', function () {
        notifier.remove();
      });

      if (response.error) {
        var message = response.message;
        var row, value;
        if (response.validation && typeof response.validation.rows === 'object') {
          for (var index in response.validation.rows) {
            for (var name in response.validation.rows[index]) {
              row = parseInt(index) + 1
              value = response.validation.rows[index][name];
              message += `<br /> ROW ${row} - ${name} - ${value}`;
            }
          }
        }

        return $.notify(message, 'danger');
      }

      if (typeof complete === 'undefined') {
        complete = response.message;
      }

      $.notify(complete, 'success');

      setTimeout(function () {
        window.location.reload();
      }, 1000);
    });
  }

  /**
   * Search table check all
   */
  $(window).on('table-checkall-init', function (e, trigger) {
    var table = $(trigger).data('table');

    var show = $($(trigger).data('toggle-show'));
    var hide = $($(trigger).data('toggle-hide'));

    function toggle(on) {
      if (on) {
        show.removeClass('d-none');
        hide.addClass('d-none');
      } else {
        show.addClass('d-none');
        hide.removeClass('d-none');
      }
    }

    $(trigger).click(function () {
      if ($(trigger).prop('checked')) {
        $('input[type="checkbox"]', table).prop('checked', true);
        toggle(true);
      } else {
        $('input[type="checkbox"]', table).prop('checked', false);
        toggle(false);
      }
    });

    $('input[type="checkbox"]', table).click(function () {
      var anyChecked = false;
      var allChecked = true;
      $('input[type="checkbox"]', table).each(function () {
        if (!$(this).prop('checked')) {
          allChecked = false;
        }

        if ($(this).prop('checked')) {
          anyChecked = true;
        }
      });

      $(trigger).prop('checked', allChecked);
      toggle(anyChecked);
    });
  });

  /**
   * Importer init
   */
  $(window).on('import-init', function (e, trigger) {
    $(trigger).toggleClass('disabled');

    $.require('components/papaparse/papaparse.min.js', function () {
      $(trigger).toggleClass('disabled');
    });
  });

  /**
   * Importer tool
   */
  $(window).on('import-click', function (e, trigger) {
    var url = $(trigger).attr('data-url');
    var progress = $(trigger).attr('data-progress');
    var complete = $(trigger).attr('data-complete');

    if (typeof progress === 'undefined') {
      progress = 'We are importing you data. Please do not refresh page.';
    }

    //make a file
    $('<input type="file" />')
      .attr(
        'accept',
        [
          'text/plain',
          'text/csv',
          'text/x-csv',
          'application/vnd.ms-excel',
          'application/csv',
          'application/x-csv',
          'text/comma-separated-values',
          'text/x-comma-separated-values',
          'text/tab-separated-values',
          'text/json',
          'application/json'
        ].join(',')
      )
      .change(function () {
        var message = '<div>'+progress+'</div>';
        var notifier = $.notify(message, 'info', 0);

        if (!this.files || !this.files[0]) {
          return;
        }

        //switch for file mimes
        switch (this.files[0].name.split('.').pop().toLowerCase()) {
          case 'csv':
            parseCsv(this, importSend.bind(null, url, notifier, progress, complete));
            break;
          case 'json':
            parseJson(this.files[0], importSend.bind(null, url, notifier, progress, complete));
            break;
        }
      })
      .click();
  });

  /**
   * Confirm UI
   */
  $(window).on('confirm-click', function (e, trigger) {
    if (!window.confirm('Are you sure you want to remove?')) {
      e.preventDefault();
      e.stopPropagation();
      return false;
    }
  });
})();
