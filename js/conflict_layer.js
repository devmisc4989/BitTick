;
$(document).on('ready', function () {
    bt_conflict_layer = {
        client_id: '',
        project_id: '',
        reload_target: '',
        confirm_start: '',
        // Calls the server method via AJAX to get possible conflicts (when trying to start a project or when clicking on a "conflict" icon).
        getProjectConflicts: function (confirm) {
            var self = this;
            $.ajax({
                type: "POST",
                url: BTeditorVars.BaseSslUrl + 'lpc/getProjectConflicts',
                datatype: 'JSON',
                data: {
                    'lpcid': this.project_id
                }
            }).done(function (res) {
                res = $.parseJSON(res);
                self.displayConflictTable(res, confirm);
            }).fail(function () {
                console.log('error connecting with the server (conflict_layer.js->bindIconClick)');
                return false;
            });
        },
        // After getting the response from the server, creates the HTML with the table to display the conflicted projects
        displayConflictTable: function (res, confirm) {

            if (confirm && !res.conflicts) {
                return doToggleCollection(this.project_id, 1, this.client_id, this.reload_target);
            }

            var htm = '<tr>' +
                    '<th>' + conflictLayerLang['table name'] + '</th>' +
                    '<th>' + conflictLayerLang['table type'] + '</th>' +
                    '<th>' + conflictLayerLang['table problem'] + '</th></tr>';

            if (res.conflicts) {
                $.each(res.projects, function (index, project) {
                    htm += '<tr>' +
                            '<td>' + project['name'] + '</td>' +
                            '<td>' + project['type'] + '</td>' +
                            '<td>' + conflictLayerLang['table conflicts'][project['problem']] + '</td></tr>';
                });
            }

            $('#project_conflict_table').find('tbody').html(htm);
            $('#project_conflict_name').html(res.lpc_name);
            $('#project_conflict_intro').html(conflictLayerLang['conflict intro'][res.code]);

            if (!confirm) {
                $('#project_conflict_layer').find('h1').first().html(conflictLayerLang['title']);
                $('#conflict_layer_confirm').fadeOut(0);
            } else {
                var btn_text = '';
                if ($('table#collectiondetails').length > 0) {
                    btn_text = $('a.btn_start_continue').filter(':visible').html();
                } else {
                    btn_text = $('tr#' + this.project_id).find('a.btn_start_continue').html();
                }

                $('#conflict_layer_continue').val(btn_text.replace(/^\s+|\s+$/g, ''));
                $('#project_conflict_layer').find('h1').first().html(conflictLayerLang['confitm_title']);
                $('#conflict_layer_confirm').fadeIn(0);
                this.bindConfirmation();
            }

            OpenPopup('#project_conflict_layer', true, null);
        },
        // When the "confirmation" layer is displayed, catches when the user clicks on the buttons (either "cancel" or "start project")
        bindConfirmation: function () {
            var self = this;
            $('#conflict_layer_continue, #conflict_layer_cancel').off('click');

            $('#conflict_layer_continue').on('click', function () {
                return doToggleCollection(self.project_id, 1, self.client_id, self.reload_target);
            });

            $('#conflict_layer_cancel').on('click', function () {
                $.fancybox.close();
            });
        },
        // When clicking on the "conflict" icon in the dashboard we satore the project data and call "getProjectConflicts()"
        bindIconClick: function () {
            var self = this;
            $('.lpc_conflict_warning').on('click', function () {
                var $row = $(this).closest('tr');
                self.project_id = $row.attr('id');
                self.getProjectConflicts(false);
            });
        },
        init: function () {
            this.bindIconClick();
        }
    };
    bt_conflict_layer.init();
});