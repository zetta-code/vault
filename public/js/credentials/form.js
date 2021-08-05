(function () {
    function buildSelectCallback(select, items, callback) {
        if (select.length > 0) {
            var size, val = select.val();
            select.find('option').each(function(i, item) {
                var $item = $(item);
                if ($item.val() != '') {
                    $item.remove();
                }
            });
            size = items.length;
            if (size === 1) {
                select.attr('readonly', true);
                select.append(callback(items[0]));
                select.val(items[0].id);
                select.trigger('change');
            } else if (size > 1) {
                select.attr('readonly', false);
                for(var i = 0; i < size; i++) {
                    select.append(callback(items[i]));
                }
                if (typeof select.data('val') !== 'undefined' && select.data('val') !== '') {
                    select.val(select.data('val'));
                    select.trigger('change');
                    select.data('val', '');
                } else if (select.find('option[value="' + val + '"]').length > 0) {
                    select.val(val);
                    select.trigger('change');
                } else {
                    select.val('');
                    select.trigger('change');
                }
            } else {
                select.attr('readonly', true);
                select.val('');
                select.trigger('change');
            }

            if (select.hasClass('selectpicker')) {
                select.selectpicker('refresh');
            }
        }
    }

    $('#credential-account').change(function() {
        var $this = $(this),
            url = $this.data('url'),
            id = $this.val().trim(),
            sectionSelect = $('#credential-section');

        if (id !== '') {
            $.get(url + '/' + id, {}, function(data) {
                if (data.success) {
                    var sections;
                    if (typeof data.sections !== 'undefined' && data.sections.length > 0) {
                        sections = data.sections;
                    } else {
                        sections = [];
                    }

                    buildSelectCallback(sectionSelect, sections, function (item) {
                        return '<option value="' + item.id + '">' + item.name + '</option>';
                    });
                }
            });
        } else {
            buildSelectCallback(sectionSelect, []);
        }
    }).trigger('change');
})();
