jQuery(function($) {
    $.extend({
        // FORM FUNCTION
        form: function(url, data, method) {
            if (method == null) method = 'POST';
            if (data == null) data = {};

            var form = $('<form>').attr({
                method: method,
                action: url
            }).css({
                display: 'none'
            });

            var addData = function(name, data) {
                if ($.isArray(data)) {
                    for (var i = 0; i < data.length; i++) {
                        var value = data[i];
                        addData(name + '[]', value);
                    }
                } else if (typeof data === 'object') {
                    for (var key in data) {
                        if (data.hasOwnProperty(key)) {
                            addData(name + '[' + key + ']', data[key]);
                        }
                    }
                } else if (data != null) {
                    form.append($('<input>').attr({
                        type: 'hidden',
                        name: String(name),
                        value: String(data)
                    }));
                }
            };

            for (var key in data) {
                if (data.hasOwnProperty(key)) {
                    addData(key, data[key]);
                }
            }

            return form.appendTo('body');
        }
    });

    // ClipboardJS /////
    function initClipboardJS() {
        var btns = document.querySelectorAll('[data-clipboard]');
        for (var i = 0; i < btns.length; i++) {
            btns[i].addEventListener('mouseleave', clearTooltip);
            btns[i].addEventListener('blur', clearTooltip);
        }

        function clearTooltip(e) {
            $(e.currentTarget).tooltip('dispose');
            e.currentTarget.removeAttribute('data-original-title');
            e.currentTarget.removeAttribute('title');
            e.currentTarget.removeAttribute('aria-describedby');
        }

        function showTooltip(elem, msg) {
            $(elem).tooltip({
                trigger: 'manual',
                placement: 'bottom',
                title: msg,
            }).tooltip('show');
        }

        function fallbackMessage(action) {
            var actionMsg = '';
            var actionKey = (action === 'cut' ? 'X' : 'C');
            if (/iPhone|iPad/i.test(navigator.userAgent)) {
                actionMsg = 'No support :(';
            }
            else if (/Mac/i.test(navigator.userAgent)) {
                actionMsg = 'Press ⌘-' + actionKey + ' to ' + action;
            }
            else {
                actionMsg = 'Press Ctrl-' + actionKey + ' to ' + action;
            }
            return actionMsg;
        }

        if (typeof (ClipboardJS) === 'undefined') {
            return;
        }
        var clipboard = new ClipboardJS('[data-clipboard]', {
            text: function(trigger) {
                return $(trigger).data('clipboard-text');
            }
        });
        clipboard.on('success', function (e) {
            e.clearSelection();
            // console.info('Action:', e.action);
            // console.info('Text:', e.text);
            // console.info('Trigger:', e.trigger);
            showTooltip(e.trigger, 'Copied!');
        });
        clipboard.on('error', function (e) {
            // console.error('Action:', e.action);
            // console.error('Trigger:', e.trigger);
            showTooltip(e.trigger, fallbackMessage(e.action));
        });
    }

    // AnchorJS /////
    function initAnchorJS() {
        var anchors = new AnchorJS();
        anchors.options = {icon: '#'};
        anchors.add('.content-body > h2, .content-body > h3, .content-body > h4, .content-body > h5');
        $('.bd-content > h2, .bd-content > h3, .bd-content > h4, .bd-content > h5').wrapInner('<div></div>')
    }

    // selectize.js /////
    function initSelectizeJS() {
        var selectize = $('.selectize-tags');
        selectize.each(function () {
            var self = $(this);
            self.attr('placeholder', self.data('tag-placeholder'));
        });
        selectize = selectize.selectize({
            plugins: ['restore_on_backspace', 'remove_button'],
            delimiter: ',',
            sortField: 'text',
            persist: false,
            render: {
                item: function(item, escape) {
                    return (item.value ? '<span class="' + this.$input.data('tag-class') + '">' + escape(item.text) + '</span>' : '');
                }
            },
            createFilter: function(input) {
                for (var prop in this.options) {
                    if (this.options[prop].text === input) {
                        return false;
                    }
                }

                return true;
            },
            create: true
        });

        // selectize.each(function () {
        //     var self = $(this),
        //         opts = self.data('options').split(','), i,
        //         option = [];
        //     for (i = 0;i < opts.length; i++){
        //         option = {
        //             value: opts[i],
        //             text: opts[i]
        //         };
        //         this.selectize.registerOption(option);
        //     }
        //     // this.selectize.refreshOptions();
        //
        //     //console.log(this.selectize);
        // });
    }

    function initDropdownInTableResponsive() {
        $('.table-responsive').on('show.bs.dropdown', '.dropdown', function(e) {
            var $dropdown = $(this).children('.dropdown-menu'),
                $toggle   = $(this).children('[data-toggle="dropdown"]'),
                yPos      = $(this).offset().top + $toggle.outerHeight(true,true);

            $(this).data('dropdown-menu', $dropdown);

            $dropdown.appendTo('body');

            window.setTimeout(function() {
                var t   = $dropdown.css('transform').split(','),
                    tY  = parseInt( t[ (t.length - 1) ] ),
                    top = Math.max(0, yPos);

                $dropdown.css('top', top);
            }, 1);
        }).on('hidden.bs.dropdown', '.dropdown', function() {
            $(this).data('dropdown-menu').appendTo( $(this) );
        });
    }

    Popper.Defaults.modifiers.computeStyle.gpuAcceleration = !(window.devicePixelRatio < 1.5 && /Win/.test(navigator.platform));
    initClipboardJS();
    initAnchorJS();
    initSelectizeJS();
});

// togglePassword /////
function togglePassword(id) {
    var $self = $(id);

    if ($self.is('input[type=password]')) {
        $self.prop('type', 'text');
    } else if ($self.is('input[type=text]')) {
        $self.prop('type', 'password');
    }
}

// uploadImg /////
function uploadImg(id) {
    $('#' + id + '-upload').change(function () {
        if (this.files && this.files[0]) {
            var file = this.files[0];
            var reader = new FileReader();
            reader.onload = function (e) {
                var selectedImage = e.target.result,
                    ext = file.name.split('.').pop();
                $('#' + id + '-preview').attr('src', selectedImage);
                $('#' + id + '-label').text(
                    file.name.length > 12
                        ? file.name.substring(0, 12-ext.length) + '...' + ext
                        : file.name
                );
            };
            reader.readAsDataURL(this.files[0]);
        }
    });
}

// uploadImg /////
function checkReCaptcha() {
    var response = grecaptcha.getResponse(),
        div = $('.g-recaptcha');

    if(response.length == 0) {
        div.addClass('animated shake');
        setTimeout(function () {
            div.removeClass('animated shake');
        }, 1000);
        return false;
    } else {
        return true;
    }
}

(function () {
    // MASK JQUERY /////
    $('input.mask-int').mask('0#');
    $('input.mask-float').mask('DR', {
        translation: {
            D: {pattern: /[0-9,]/},
            R: {pattern: /[0-9,]/, recursive: true}
        }
    });
    $('input.mask-nota').mask('90,99', {
        translation: {
            v: {pattern: /[,/.]/, fallback: ','},
        },
        reverse: true
    });
    $('input.mask-enem').mask('0999v9', {
        translation: {
            v: {pattern: /[,]/, fallback: ','}
        },
        optional: true
    });
    $('input.mask-cep').mask('00000-000');
    $('input.mask-cpf').mask('000.000.000-00');
    $('input.mask-date').mask('00/00/0000');
    $('input.mask-timeHM').mask('09:09');
    // MONEY INPUT /////
    jQuery.fn.putCursorAtEnd = function () {
        return this.each(function () {
            // Cache references
            var $el = $(this),
                el = this;

            // Only focus if input isn't already
            if (!$el.is(':focus')) {
                $el.focus();
            }

            // If this function exists... (IE 9+)
            if (el.setSelectionRange) {
                // Double the length because Opera is inconsistent about whether a carriage return is one character or two.
                var len = $el.val().length * 2;

                // Timeout seems to be required for Blink
                setTimeout(function () {
                    el.setSelectionRange(len, len);
                }, 1);
            } else {
                // As a fallback, replace the contents with itself
                // Doesn't work in Chrome, but Chrome supports setSelectionRange
                $el.val($el.val());
            }

            // Scroll to the bottom, in case we're in a tall textarea
            // (Necessary for Firefox and Chrome)
            this.scrollTop = 999999;
        });
    };
    $('input.mask-money').mask('#.##0,00', {reverse: true}).on('mouseup keyup keydown keypress', function () {
        var val = this.value,
            precision = 2,
            decimalSep = ',';
        if (val) {
            if (val.length <= precision) {
                while (val.length < precision) {
                    val = '0' + val;
                }
                val = '0' + decimalSep + val;
            } else {
                var parts = val.split(decimalSep);
                parts[0] = parts[0].replace(/^0+/, '');
                if (parts[0].length === 0) {
                    parts[0] = '0';
                }
                val = parts.join(decimalSep);
            }
            this.value = val;
        }
        $(this).putCursorAtEnd();
    });
    $('input.mask-credit-card').mask('0000 0000 0000 0000');
    // MASK TELEFONE /////
    $('input.mask-phone').each(function () {
        var $this = $(this);
        $this.mask(($this.val().length === 15 ? '(00) 00000-0000' : '(00) 0000-00009'), {
            onKeyPress: function(phone, e, field, options){
                $('input.mask-phone').mask((phone.length === 15 ? '(00) 00000-0000' : '(00) 0000-00009'), options);
            },
        });
    });
    ///////////////

    // .TOOLTIP /////
    $('[data-toggle="tooltip"]').tooltip();
    // /TOOLTIP /////
})();

(function () {
    var itemsAction = $('.items-action'),
        itemsBtn = $('.items-btn'),
        itemsLabel = $('.items-label'),
        itemsAll = $('.items-all'),
        itemsCheckbox = $('.items-checkbox'),
        items = [];

    function itemsInit() {
        itemsCheckbox.filter(':checked').each(function() {
            items.push($(this).val());
        });
        itemsChanged();
    }

    function itemSelect () {
        var self = $(this);
        if(self.prop('checked')) {
            items.push(self.val());
        } else {
            items.splice(items.indexOf(self.val()), 1);
        }
        itemsChanged();
    }

    function itemsChanged() {
        if (items.length > 1) {
            if(items.length === itemsCheckbox.length && itemsAll.prop('checked') === false) {
                itemsAll.prop('checked', true)
                    .prop('indeterminate', false);
            }else if(items.length < itemsCheckbox.length && itemsAll.prop('checked') === true) {
                itemsAll.prop('checked', false)
                    .prop('indeterminate', true);
            }
            itemsLabel.text(items.length + ' items selecionados');
            itemsBtn.removeClass('d-none');
        } else if (items.length === 1) {
            itemsAll.prop('checked', itemsCheckbox.length === 1);
            itemsAll.prop('indeterminate', itemsCheckbox.length > 1);
            itemsLabel.text('1 item selecionado');
            itemsBtn.removeClass('d-none');
        } else {
            itemsAll.prop('checked', false)
                .prop('indeterminate', false);
            itemsLabel.text('Nenhum item selecionado');
            itemsBtn.addClass('d-none');
        }
    }

    function itemAllChanged() {
        if(itemsAll.prop('checked')) {
            itemsCheckbox.not(':checked').each(function() {
                var self = $(this);
                self.prop('checked', true);
                items.push(self.val());
            });
        } else {
            itemsCheckbox.filter(':checked').each(function() {
                $(this).prop('checked', false);
            });
            items = [];
        }
        itemsChanged();
    }

    function itemAction(event) {
        event.preventDefault();
        var self = $(this);
        $.form(self.data('url'), {
            step: 1,
            items: JSON.stringify(items)
        }).submit().remove();
    }

    itemsAction.click(itemAction);
    itemsCheckbox.change(itemSelect);
    itemsAll.change(itemAllChanged);
    itemsInit();
})();

(function () {
    var a = $('.table a[href="#more"]');

    function toggleRevealed(event) {
        var self = $(this),
            tr = self.closest('tr'),
            others = tr.siblings('.revealed');
        event.preventDefault();
        tr.toggleClass('revealed');
        tr.find('i').toggleClass('fa-angle-right fa-angle-down');
        //others.removeClass('revealed');
        //others.find('i').toggleClass('fa-angle-right fa-angle-down');

        var target = tr.next(),
            table = target.find('table'),
            spin = target.find('#credential-' + self.data('id') + '-spin');
            spin.siblings('p').remove();
        if (!table.data('loaded')) {
            self.addClass('disabled');
            table.addClass('d-none animated fadeIn');
            spin.removeClass('d-none');
            $.getJSON(basePath + '/vault/credentials/reveal/' + self.data('id'), function (result) {
                if (result.success) {
                    table.find('.credential-username span').text(result.username);
                    table.find('.credential-username button').removeClass('d-none').data('clipboard-text', result.username);
                    table.find('.credential-value span').text(result.value);
                    table.find('.credential-value button').removeClass('d-none').data('clipboard-text', result.value);
                    table.removeClass('d-none');
                    spin.addClass('d-none');
                    table.data('loaded', true);
                    setTimeout(function () {
                        self.removeClass('disabled');
                    }, 100);
                    setTimeout(function () {
                        table.removeClass('animated fadeIn');
                    }, 1100);
                }
            }).fail(function () {
                spin.addClass('d-none');
                spin.after('<p class="h6">Gráfico não disponível pra visualização.</p>');
            });
        }
    }

    a.click(toggleRevealed);
})();