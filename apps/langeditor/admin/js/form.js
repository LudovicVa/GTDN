/**
 * Script for Installer module
 * 
 * @author Julien Blatecky <julien.blatecky@creatiwity.net>
 * @version 0.4
 */
$("body").ready(function() {
    var Form, FormNode;

    Form = (function() {

        function Form(options) {
            var handlers = {}, id = options.id;

            if (!options || !options.id) {
                console.error("options parameter is missing or empty, unable to create form.");
                return;
            }

            this.url = options.url ? options.url : document.location;

            this.context = {
                id: id,
                form: this,
                indexed: {}
            };

            this.$alert = $('[data-wity-form-alert="' + id + '"]');
            this.$submit = $('[data-wity-form-submit="' + id + '"]');

            this.$summary = $('[data-wity-form-summary="' + id + '"]');
            if (this.$summary.length > 0) {
                this.context.$summary = this.$summary;
            }

            this.$tabs = $('[data-wity-form-tabs="' + id + '"]');
            if (this.$tabs.length > 0) {
                this.context.$tabs = this.$tabs;
            }

            this.$content = $('[data-wity-form-content="' + id + '"]');
            if (this.$content.length > 0) {
                this.context.$content = this.$content;
            }

            $('[data-wity-form-' + id + '-onsuccess]').each(function () {
                var $this = $(this), handler = $this.attr('data-wity-form-' + id + '-onsuccess');

                handlers.success = handlers.success || [];
                handlers.success.push([$this, handler]);
            });

            $('[data-wity-form-' + id + '-onfailure]').each(function () {
                var $this = $(this), handler = $this.attr('data-wity-form-' + id + '-onfailure');

                handlers.failure = handlers.failure || [];
                handlers.failure.push([$this, handler]);
            });

            this.node = new FormNode(options, this.context, this);
        }

        Form.prototype.updateStatus = function() {
            // Check validated or not to allow click
        };

        Form.prototype.getAjaxHtml = function (url, datas, callback, context) {
            var _url = url || this.url;

            $.ajax({
                url: _url,
                data: datas,
                success: callback,
                type: 'POST'
            });
        }

        Form.prototype.ajax = function (datas, callback, context) {
            var realCallback, that = this;

            // show loading

            realCallback = function(data, textStatus, jqXHR) {
                var json;

                try {
                    json = $.parseJSON(data);
                } catch(e) {
                    // Display debug
                    return;
                }

                // process json

                //process callback ?
                if(callback) {
                    return callback.call(context, json);
                }
            };

            $.ajax({
                url: this.url,
                data: datas,
                success: realCallback,
                type: 'POST'
            });
        };


        return Form;

    })();

    FormNode = (function() {

        var tabCounter = 0, NOT_YET_VALIDATED = 0, NOT_VALIDATED = 1, NOT_VALIDATED_EMPTY_REQUIRED = 2, VALIDATING = 3, VALIDATED = 4, EMPTY_NOT_REQUIRED = 5;

        /**
         * Validation updateStatus
         */

        function FormNode(options, context, parent) {
            var _i, _len, $tabTitle, $tabStatus, $tabContainer,
                $tabContent, $summaryLi, $summaryStatus,
                $alertContainer, $fieldContainer, $field,
                validationTimer, contentCache, that = this;

            if (!options || !context || !context.id || !context.form || !context.$content || context.$content.length === 0) {
                console.debug("context passed to FormNode isn't valid.");
                return false;
            }

            context.indexed[this.id] = this;

            this.id = options.id;
            this.views = [];
            this.indexed = context.indexed;
            this.parent = parent;

            this.name = options.name || this.id;

            this.required = (options.required === true) || (options.root === true);
            this.validated = NOT_YET_VALIDATED;
            this.summary = false;

            this.validateDatas = options.validate || {};

            // Build validate

            // Build FormNode in DOM and in logic
            contentCache = context.$content;

            if (options.root === true) {
                // Do nothing
            } else if (options.tab && options.tab === true) {
                if (context.$tabs.length > 0) {
                    $tabStatus = $('<i class="glyphicon"></i>');
                    $tabTitle = $('<a href="#form_' + context.id + '_' + this.id + '" data-toggle="tab"></a>').append($tabStatus).append(' ' + this.name);
                    $tabTitle = $('<li></li>').append($tabTitle);

                    $tabContainer = $('<div class="tab-pane" id="form_' + context.id + '_' + this.id + '"></div>');
                    $tabContent = $('<fieldset class="form-horizontal"></fieldset>').appendTo($tabContainer);

                    if (tabCounter === 0) {
                        $tabTitle.addClass('active');
                        $tabContainer.addClass('active');
                    }

                    context.$tabs.append($tabTitle);
                    context.$content.append($tabContainer);
                    context.$summary.append($('<h4>' + this.name + '</h4>'));

                    context.$content = $tabContent;

                    this.views.push({
                        updater: function() {
                            $tabStatus.removeClass('glyphicon-remove glyphicon-ok');

                            switch (that.validated) {
                                case NOT_YET_VALIDATED:
                                    $tabStatus.addClass('glyphicon-remove');
                                    break;

                                case NOT_VALIDATED:
                                    $tabStatus.addClass('glyphicon-remove');
                                    break;

                                case VALIDATED:
                                    $tabStatus.addClass('glyphicon-ok');
                                    break;
                            }
                        }
                    });

                    tabCounter++;
                }
            } else if (!options.virtual) {
                this.summary = options.summary !== false;
                this.type = options.type || "text";

                $alertContainer = $('<div></div>');
                $fieldContainer = $('<div class="form-group"></div>');

                if (this.type === "select") {
                    $field = $('<select name="' + this.id + '" class="form-control"></select>');

                    if ($.isArray(options.options)) {
                        for (_i = 0, _len = options.options.length; _i < _len; ++_i) {
                            $field.append('<option value="' + options.options[_i].value + '">' + options.options[_i].text + '</option>')
                        }

                        if (options.value) {
                            $field.val(options.value);
                        }
                    } else if (options.options.url) {
                        context.form.getAjaxHtml(options.options.url, null, function (data) {
                            $field.append(data);

                            if (options.value) {
                                $field.val(options.value);
                            }
                        });
                    } else {
                        console.debug("No option provided for " + this.name + " in the form named " + context.id);
                    }
                } else {
                    $field = $('<input type="' + this.type + '" class="form-control"></input>');

                    if (options.placeholder) {
                        $field.attr('placeholder', options.placeholder);
                    }

                    if (options.value) {
                        $field.val(options.value);
                    }
                }

                this.$field = $field;

                context.$content.append($alertContainer);
                context.$content.append($fieldContainer);
                $fieldContainer.append('<label class="col-md-3 control-label" for="' + this.id + '">' + this.name + '</label>')
                $fieldContainer.append($('<div class="col-md-9"></div>').append($field));

                this.views.push({
                    updater: function() {
                        $fieldContainer.removeClass('has-success has-error has-warning');

                        switch (that.validated) {
                            case NOT_YET_VALIDATED:
                                break;

                            case NOT_VALIDATED:
                                $fieldContainer.addClass('has-error');
                                break;

                            case VALIDATED:
                                $fieldContainer.addClass('has-success');
                                break;
                        }
                    }
                });

                $field.on('blur changed', function() {
                    if (validationTimer) {
                        clearTimeout(validationTimer);
                    }

                    validationTimer = setTimeout(function() {that.validate();}, 0);
                });

            } else {
                this.summary = (options.summary === true);
            }

            if (this.summary) {
                $summaryStatus = $('<i class="glyphicon"></i>');
                $summaryLi = $('<li></li>');

                $summaryLi.append($summaryStatus);
                $summaryLi.append(' ' + this.name);

                context.$summary.append($summaryLi);

                this.views.push({
                    updater: function() {
                        $summaryLi.removeClass('text-success text-danger text-warning text-primary text-muted');
                        $summaryStatus.removeClass('glyphicon-remove glyphicon-ok');

                        switch (that.validated) {
                            case NOT_YET_VALIDATED:
                                $summaryLi.addClass('text-primary');
                                break;

                            case NOT_VALIDATED:
                                $summaryLi.addClass('text-danger');
                                $summaryStatus.addClass('glyphicon-remove');
                                break;

                            case VALIDATED:
                                $summaryLi.addClass('text-success');
                                $summaryStatus.addClass('glyphicon-ok');
                                break;
                        }
                    }
                });
            }

            // Constructs all children of this node
            this.childs = [];

            if (options.childs && options.childs.length > 0) {
                for (_i = 0, _len = options.childs.length; _i < _len; ++_i) {
                    this.childs.push(new FormNode(options.childs[_i], context, this));
                }
            }

            context.$content = contentCache;

            if (options.hr === true) {
                context.$content.append('<hr />');
            }

            this.render();
        }

        FormNode.prototype.isValidated = function() {
            return (this.validated === VALIDATED);
        };

        FormNode.prototype.getValues = function() {
            var _i, _len, values = {};

            if(this.$field && this.$field.length > 0) {
                values[this.id] = this.$field.val();
            }

            for (_i = 0, _len = this.childs.length; _i < _len; ++_i) {
                $.extend(values, this.childs[_i].getValues());
            }

            return values;
        };

        FormNode.prototype.hasChanged = function() {
            var _i, _len, changed, currentValue;

            if (this.$field && this.$field.length > 0) {
                currentValue = this.$field.val();

                changed = this.value !== currentValue;
                if (changed === true) {
                    this.value = currentValue;
                    return true;
                }
            }

            for (_i = 0, _len = this.childs.length; _i < _len; ++_i) {
                if (this.childs[_i].hasChanged() === true) {
                    return true;
                }
            }

            return false;
        };

        FormNode.prototype.isEmpty = function() {
            return (this.value === null || this.value === undefined || this.value === "");
        };

        FormNode.prototype.hasFocus = function() {
            var _i, _len;

            if (this.$field && this.$field.length > 0) {
                if(this.$field.is(':focus')) {
                    return true;
                }
            }

            for (_i = 0, _len = this.childs.length; _i < _len; ++_i) {
                if (this.childs[_i].hasFocus() === true) {
                    return true;
                }
            }

            return false;
        };

        FormNode.prototype.updateStatus = function() {
            var _i, _len, child, childrenValid = true;

            for (_i = 0, _len = this.childs.length; _i < _len; ++_i) {
                childrenValid = childrenValid && (this.childs[_i].validated === VALIDATED || this.childs[_i].validated === EMPTY_NOT_REQUIRED);
            }

            if (childrenValid === false) {
                this.validated = NOT_VALIDATED;
            }
        };

        FormNode.prototype.triggerParentUpdate = function() {
            this.parent.updateStatus();
        };

        FormNode.prototype.validate = function() {
            var empty, regexp, localValid = true, localMessage, remoteValid = true;

            if (this.hasChanged()) {
                this.validated = VALIDATING;

                empty = this.isEmpty();

                if (this.required && empty) {
                    this.validated = NOT_VALIDATED_EMPTY_REQUIRED;
                } else if (empty) {
                    this.validated = EMPTY_NOT_REQUIRED;
                } else {
                    if (this.validateDatas.local && this.value) {
                        if (this.validateDatas.local.type === "regexp") {
                            regexp = new RegExp(this.validateDatas.local.options, "i");
                            localValid = regexp.test(this.value);
                            localMessage = this.validateDatas.local.message;
                        } else if (this.validateDatas.local.type === "equals") {
                            localValid = (this.indexed[this.validateDatas.local.options].value === this.value);
                            localMessage = this.validateDatas.local.message;
                        }

                        if (!localValid) {
                            this.message = this.message || [];
                            this.message.push({
                                type: "danger",
                                message: localMessage
                            });

                            this.validated = NOT_VALIDATED;
                        }
                    }

                    if (localValid && this.validateDatas.remote) {

                    }
                }

                if (localValid && remoteValid) {
                    this.validated = VALIDATED;
                }

                this.render();
                this.triggerParentUpdate();
            }

            // Trigger updateStatus on parent

            // Triggered on blur or changed or keyup && no focus

            // Test empty, if required return false (always update view), else return true
            // Not empty
            // Validate local
            // Test "requires" -> how ?
            // If not return false
            // Otherwise, validate remote
            // Update view (loading)
            // On callback, if content changed, abort result
            // If not
        };

        FormNode.prototype.render = function() {
            var _i, _len;

            for (_i = 0, _len = this.views.length; _i < _len; ++_i) {
                this.views[_i].updater();
            }
        };

        return FormNode;

    })();

	(function () {

		var site = {
			id: "witycms",
			root: true,

			childs: [
                {
                    id: "site",
                    name: "Site",
                    tab: true,
                    required: true,

                    childs: [
                        {
                            id: "site_name",
                            name: "Site name",
                            required: true,
                            value: "GET_SITE_NAME",

                            validate: {
                                remote: 'site_name'
                            }
                        },
                        {
                            id: "base",
                            name: "Base URL",
                            required: true,
                            value: "GET_BASE",

                            validate: {
                                local: {
                                    type: "regexp",
                                    options: "^(http|https|ftp)\:\/\/[A-Z0-9][A-Z0-9_-]*(\.[A-Z0-9][A-Z0-9_-]*)*(:[0-9]+)?(\/[A-Z0-9~\._-]+)*\/?$",
                                    message: "Base URL must be a valid URL"
                                },
                                remote: 'base_url'
                            },
                            type: "url"
                        },
                        {
                            id: "theme",
                            name: "Theme",
                            required: true,
                            value: "GET_DEFAULT_THEME",

                            validate: {
                                remote: 'theme'
                            },
                            autocomplete: "GET_THEMES"
                        },
                        {
                            id: "language",
                            name: "Language",
                            required: true,
                            type: "select",
                            value: "GET_LANG",

                            options: [
                                {
                                    value: "en-EN",
                                    text: "English (en-EN)"
                                },
                                {
                                    value: "fr-FR",
                                    text: "Français (fr-FR)"
                                }
                            ]
                        },
                        {
                            id: "timezone",
                            name: "Timezone",
                            required: true,
                            type: "select",
                            hr: true,
                            value: "GET_TIMEZONE",

                            options: {
                                url: "installer/view/timezones.html"
                            }
                        },
                        {
                            id: "front_app",
                            name: "Front app.",
                            required: true,
                            autocomplete: "GET_FRONT_APPS",
                            value: "GET_DEFAULT_FRONT",

                            validate: {
                                remote: 'front_app'
                            }
                        },
                        {
                            id: "admin_app",
                            name: "Admin app.",
                            required: true,
                            autocomplete: "GET_ADMIN_APPS",
                            value: "GET_DEFAULT_ADMIN",

                            validate: {
                                remote: 'admin_app'
                            }
                    ]
                }
            ]
        }

        /*var database = {
            id: "witycms",
            root: true,

            childs: [
                {
                    id: "database",
                        name: "Database",
                    tab: true,
                    required: true,

                    childs: [
                    {
                        id: "credentials",
                        name: "Credentials",
                        virtual: true,
                        required: true,
                        summary: true,
                        hr: true,

                        validate: {
                            remote: 'db_credentials'
                        },

                        childs: [
                            {
                                id: "dbserver",
                                name: "Server",
                                required: true,
                                summary: false
                            },
                            {
                                id: "dbport",
                                name: "Port",

                                validate: {
                                    local: {
                                        type: "regexp",
                                        options: "^[0-9]*$",
                                        message: "Database port must be a number"
                                    }
                                },

                                type: "number",
                                placeholder: "3306",
                                summary: false
                            },
                            {
                                id: "dbuser",
                                name: "User",

                                required: true,
                                summary: false
                            },
                            {
                                id: "dbpassword",
                                name: "Password",

                                type: "password",
                                summary: false
                            }
                        ]
                    },
                    {
                        id: "dbname",
                        name: "Database name",
                        required: true,
                        requires: "credentials",

                        validate: {
                            remote: 'db_name'
                        }
                    },
                    {
                        id: "dbprefix",
                        name: "Tables prefix",
                        requires: "dbname",

                        validate: {
                            remote: 'tables_prefix'
                        }
                    }
                   ]
                }
            ]
        }*/

        new Form(site);
	})();
});