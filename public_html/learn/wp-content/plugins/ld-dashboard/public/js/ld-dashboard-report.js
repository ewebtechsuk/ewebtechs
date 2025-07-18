// const { __, _x, _n, sprintf } = wp.i18n;
class LD_Dashboard_Report {

    constructor() {
        let vars = {
            ldTable: null,
            ldTableHidden: null,
            lessonId: 0,
            groupId: 0,
            courseId: 0,
            quizId: 0,
            status: "all",
        };
        this.init(vars);
    }

    init = (vars) => {
        this.createTable(vars);
        this.getReportData(vars);
        this.groupFilter(vars);
        this.courseFilter(vars);
        this.lessonFilter(vars);
        this.quizFilter(vars);
        this.statusFilter(vars);
    }

    createTable = (vars, data) => {
        let self = this;
        let table = document.getElementById('ld-dashboard-report-table');
        let tableType = table.dataset.table;
        let columnOrder = '';
        switch (tableType) {
            case 'essay-report':
                columnOrder = [1, 'desc'];
                break;
            case 'assignment-report':
                columnOrder = [1, 'desc'];
                break;
            case 'quizz-report':
                columnOrder = [6, 'desc'];
                break;
        }

		console.log(this.tableColumns(tableType));
        let options = {
            aLengthMenu: [
                [15, 30, 60, -1],
                [15, 30, 60, __('All', 'ld-dashboard')],
            ],
            iDisplayLength: 15,
            responsive: false,
            select: { style: "multi", selector: "td:first-child" },
            stateSave: false,
            dom: '<"ld-dashboard-datatable-header"Bf>rt<"ld-groups-datatable-footer"lpi>',
            oLanguage: {
                sProcessing: "<img src='" + lddRepots.ldd_url + "public/img/wpspin-2x.gif'>"
            },
            paging: true,
            processing: true,
            order: [columnOrder],
            autoWidth: true,
            columns: this.tableColumns(tableType),
            buttons: ['csv'],
        };

        if (null !== vars.ldTable) {
            if (typeof vars.ldTable.fnDestroy === "function"){
                vars.ldTable.fnDestroy();
            }else{
                vars.ldTable.destroy();
            }
           
            options.data = data;
            options.columnDefs = 'essay-report' === tableType ? [{ className: "ldd__cell-checkbox", targets: [0] }] : '';
            // options.columns = this.tableColumns(tableType);
            vars.ldTable = new DataTable(table, options);
            this.approveEssay();
            this.approveAssingment();
            this.viewMoreColumns(data);
            this.quizStats();
            // this.columnsVisiblity(this.tableColumns(tableType), vars.ldTable );
        } else {
            vars.ldTable = new DataTable(table, options);
        }
    }

    tableColumns = (tableType) => {
        if ('assignment-report' === tableType) {
            return [
                { data: "id", title: __('ID', 'ld-dashboard'), className: "ldd-report-cell ldd-report-cell ldd-report-assignment-cell ldd-report-assignment-cell--id", visible: false },
                {
                    data: "title", title: __('Title', 'ld-dashboard'), name: "title", className: "ldd-report-cell ldd-report-assignment-cell ldd-report-assignment-cell--title", visible: true
                },
                { data: "author", title: __('Username', 'ld-dashboard'), name: "author", className: "ldd-report-cell ldd-report-assignment-cell ldd-report-assignment-cell--author" },
                { data: "action", title: __('Action', 'ld-dashboard'), name: "action", className: "ldd-report-cell ldd-report-assignment-cell ldd-report-assignment-cell--action" },
                { data: "points", title: __('Points', 'ld-dashboard'), name: "points", className: "ldd-report-cell ldd-report-assignment-cell ldd-report-assignment-cell--points" },
                { data: "status", title: __('Status', 'ld-dashboard'), name: "status", className: "ldd-report-cell ldd-report-assignment-cell ldd-report-assignment-cell--status", visible: true },
                {
                    data: "assignedCourse",
                    title: __('Assigned ', 'ld-dashboard') + '' + lddRepots.localized.assignedCourse,
                    name: "assignedCourse",
                    className: "ldd-report-cell ldd-report-assignment-cell ldd-report-assignment-cell--assigned-course",
                    visible: false
                },
                {
                    data: "assignedlesson",
                    title: __('Assigned ', 'ld-dashboard') + '' + lddRepots.localized.assignedlesson,
                    name: "assignedlesson",
                    className: "ldd-report-cell ldd-report-assignment-cell ldd-report-assignment-cell--assigned-lesson",
                    visible: false
                },
                {
                    data: "first_name",
                    title: __('First name', 'ld-dashboard'),
                    name: "first_name",
                    className: "ldd-report-cell ldd-report-assignment-cell ldd-report-assignment-cell--first-name",
                    visible: false
                },
                { data: "last_name", title: __('Last name', 'ld-dashboard'), name: "last_name", className: "ldd-report-cell ldd-report-assignment-cell ldd-report-assignment-cell--last-name", visible: false },
                { data: "comments", title: lddRepots.localized.comments, name: "comments", className: "ldd-report-cell ldd-report-assignment-cell ldd-report-assignment-cell--comments", visible: false },
                { data: "date", title: lddRepots.localized.date, name: "date", className: "ldd-report-cell ldd-report-assignment-cell ldd-report-assignment-cell--date", visible: false, orderable: true },
            ]

        } else if ('quizz-report' === tableType) {
            return [
                { data: "id", title: __('ID', 'ld-dashboard'), className: "ldd-report-cell ldd-report-cell ldd-report-quiz-cell ldd-report-quiz-cell--id", visible: false, orderable: false },
                { data: "first_name", title: __('Name', 'ld-dashboard'), className: "ldd-report-cell ldd-report-quiz-cell ldd-report-quiz-cell--first-name", visible: true },
                { data: "user_name", title: __('Username', 'ld-dashboard'), className: "ldd-report-cell ldd-report-quiz-cell ldd-report-quiz-cell--author", visible: true },
                { data: "quiz_score", title: __('Quiz Score', 'ld-dashboard'), className: "ldd-report-cell ldd-report-quiz-cell ldd-report-quiz-cell--quiz-score" },
                { data: "quiz_modal", title: __('Report', 'ld-dashboard'), orderable: false, searchable: false, className: "ldd-report-cell ldd-report-quiz-cell ldd-report-quiz-cell--detailed-report", visible: true },
                { data: "quiz_date", title: __('Date', 'ld-dashboard'), className: "ldd-report-cell ldd-report-quiz-cell ldd-report-quiz-cell--date", orderable: true, orderSequence: ["desc"], visible: true },
                { data: "action", title: __('Action', 'ld-dashboard'), name: "action", className: "ldd-report-cell ldd-report-quiz-cell ldd-report-quiz-cell--action", orderable: false, visible: false },
                { data: "user_email", title: __('Email', 'ld-dashboard'), className: "ldd-report-cell ldd-report-quiz-cell ldd-report-quiz-cell--email", visible: false },
                { data: "last_name", title: __('Last name', 'ld-dashboard'), className: "ldd-report-cell ldd-report-quiz-cell ldd-report-quiz-cell--last-name", visible: false },
            ]

        } else {
            return [
                { data: "id", title: __('ID', 'ld-dashboard'), className: "ldd-report-cell ldd-report-cell ldd-report-essays-cell ldd-report-essays-cell--id", orderable: true },
                {
                    data: "title", title: __('Title', 'ld-dashboard'), className: "ldd-report-cell ldd-report-essays-cell ldd-report-essays-cell--title"
                },
                { data: "author", title: __('Username', 'ld-dashboard'), className: "ldd-report-cell ldd-report-essays-cell ldd-report-essays-cell--author" },
                { data: "points", title: __('Points', 'ld-dashboard'), className: "ldd-report-cell ldd-report-essays-cell ldd-report-essays-cell--points", visible: true },
                { data: "status", title: __('Status', 'ld-dashboard'), className: "ldd-report-cell ldd-report-essays-cell ldd-report-essays-cell--status" },
                { data: "action", title: __('Action', 'ld-dashboard'), name: "action", className: "ldd-report-cell ldd-report-essays-cell ldd-report-essays-cell--action" },
                {
                    data: "question_text",
                    title: lddRepots.localized.question_text,
                    className: "ldd-report-cell ldd-report-essays-cell ldd-report-essays-cell--question-text",
                    visible: false,
                },
                { data: "content", title: lddRepots.localized.content, className: "ldd-report-cell ldd-report-essays-cell ldd-report-essays-cell--content", visible: false, },
                {
                    data: "assignedCourse",
                    title: lddRepots.localized.assignedCourse,
                    className: "ldd-report-cell ldd-report-essays-cell ldd-report-essays-cell--assigned-course",
                    visible: false,
                },
                {
                    data: "assignedlesson",
                    title: lddRepots.localized.assignedlesson,
                    className: "ldd-report-cell ldd-report-essays-cell ldd-report-essays-cell--assigned-lesson",
                    visible: false,
                },
                {
                    data: "assignedquiz",
                    title: lddRepots.localized.assignedquiz,
                    className: "ldd-report-cell ldd-report-essays-cell ldd-report-essays-cell--assigned-quiz",
                    visible: false,
                },
                {
                    data: "first_name",
                    title: __('First name', 'ld-dashboard'),
                    className: "ldd-report-cell ldd-report-essays-cell ldd-report-essays-cell--first-name",
                    visible: false,
                },
                { data: "last_name", title: __('Last name', 'ld-dashboard'), className: "ldd-report-cell ldd-report-essays-cell ldd-report-essays-cell--last-name", visible: false, },
                { data: "comments", title: lddRepots.localized.comments, className: "ldd-report-cell ldd-report-essays-cell ldd-report-essays-cell--comments", visible: false, },
                { data: "date", title: lddRepots.localized.date, className: "ldd-report-cell ldd-report-essays-cell ldd-report-essays-cell--date", visible: false, orderSequence: ["desc"] },
            ]
        }
    }

    viewMoreColumns = (data) => {
        let viewMoerButtons = document.querySelectorAll('.ldd-report-view-more');
        if (null != viewMoerButtons) {
            viewMoerButtons.forEach((viewMoerBurron) => {
                viewMoerBurron.addEventListener(('click'), (e) => {
                    e.preventDefault();
                    let rowID = e.target.dataset.rowId;
                    let tableType = document.getElementById('ld-dashboard-report-table').dataset.table;
                    let columns = this.tableColumns(tableType);
                    let ul = document.createElement('ul');
                    let uniqueData = data.filter((obj, index, self) =>
                        index === self.findIndex((t) => t.id === obj.id)
                    );

                    columns.map(item1 => {
                        uniqueData.forEach((item2, index) => {
                            if (Object.keys(item2).includes(item1.data)) {
                                if (rowID == item2.id && 'action' !== item1.data && 'points' !== item1.data) {
                                    if ('status' === item1.data) {
                                        if (item2.status.includes('button')) {
                                            item2.status = __('Not Graded', 'ld-dashboard');
                                        }
                                    }
                                    let liElement = document.createElement('li');
                                    ul.appendChild(liElement);
                                    liElement.innerHTML = '<strong>' + item1.title + '</strong><span>' + item2[item1.data] + '</span>';
                                }
                            }
                        });
                    });

                    Swal.fire({
                        html: ul,
                        focusConfirm: false,
                        target: document.querySelector('.ld-dashboard-main-wrapper')
                    }), function () {
                        this.approveEssay();
                    };
                });
            });

        }
    }

    // onlyInLeft = (left, right) => {
    //     left.filter(leftValue)
    //         !right.some(rightValue)
    // }

    columnsVisiblity = (columns, table) => {
        let columnVisiblity = [];
        let visibilityWrapper = document.createElement('div');
        visibilityWrapper.id = 'ldd-column-visibility';
        columnVisiblity.push('<span>' + __('Customize Columns:', 'ld-dashboard') + '</span>');
        document.querySelector('.ld-dashboard-datatable-header').after(visibilityWrapper);
        if (columns) {
            columns.forEach((column, index) => {
                columnVisiblity.push('<lable><input type="checkbox" class="toggle-vis" data-column="' + index + '" value="' + column.title + '" />' + column.title + '</lable>');
            });
            visibilityWrapper.innerHTML = columnVisiblity.join(" ");
        }
        this.toggleColumnVisiblity(table);
    }

    toggleColumnVisiblity = (table) => {
        let toggles = document.querySelectorAll('.toggle-vis');
        toggles.forEach((toggle) => {
            toggle.addEventListener(('click'), (e) => {
                e.preventDefault();

                // if (e.target.checked){
                //     e.target.classList.add('enable');
                // }

                // Get the column API object
                let column = table.column(e.target.dataset.column);
                // Toggle the visibility
                column.visible(!column.visible());

            });
        });

    }

    getReportData = (vars) => {
        let self = this;
        document.querySelector('.dataTables_processing').style.display = "block";

        let restRoute = 'ldd_get_essays_data';
        if ('assignment-report' == lddRepots.ld_dashboard_page) {
            restRoute = 'ldd_get_assignment_data';
        } else if ('quizz-report' == lddRepots.ld_dashboard_page) {
            restRoute = 'ldd_get_quiz_data';
        }

        fetch(lddRepots.root + restRoute + '/', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json; charset=UTF-8',
                'X-WP-Nonce': lddRepots.nonce
            },
            body: JSON.stringify({
                "lessonId": vars.lessonId,
                "groupId": vars.groupId,
                "courseId": vars.courseId,
                "quizId": vars.quizId,
                "status": vars.status
            })
        }).then((response) => {
            return response.json();
        }).then((data) => {
            // clearInterval(interval);
            // document.getElementById("group-loader").remove();
            document.querySelector('.dataTables_processing').style.display = "none";
            this.createTable(vars, data);
        }).catch((error) => console.error(error));
    }

    groupFilter = (vars) => {
        let groupFilter = document.getElementById('ld-dashboard-report-group');

        if (null != groupFilter) {
            groupFilter.addEventListener('change', (e) => {
                let val = e.target.value;
                vars.groupId = parseInt(val);

                let filters = document.querySelectorAll('.ld-change-drop-down');
                filters.forEach((filter, i) => {
                    if (i == 1) {
                        filter.disabled = false;
                    }

                    //Check for course filter
                    if (i == 1) {
                        if (filter.options.length == 3) {
                            filter.options[1].setAttribute('selected', true);
                            filter.classList.add('select-h3');
                        }
                    }
                });
                this.createTable(vars);
                this.getReportData(vars);
            });
        }
    }

    courseFilter = (vars) => {
        let __courseFilter = document.getElementById('ld-dashboard-report-course');

        __courseFilter.addEventListener('change', (e) => {
            let val = e.target.value;
            vars.courseId = parseInt(val);
            let lessonFilterElement = document.getElementById('ld-dashboard-report-lesson');
            let quizzFilterElement = document.getElementById('ld-dashboard-report-quiz');
            let lessonOptions = '';
            let quizzOPtions = '';

            if (0 == val) {
                lessonOptions = this.createOptions(lessonFilterElement, [0]);
                vars.lessonId = 0;
                vars.quizId = 0;
            } else {
                if (0 != vars.groupId) {
                    if (undefined != lddRepots.relationships[vars.groupId][val]) {
                        lessonOptions = this.createOptions(lessonFilterElement, lddRepots.relationships[vars.groupId][val]);
                        quizzOPtions = this.createOptions(quizzFilterElement, lddRepots.quiz_relationships[vars.groupId][val]);
                    }
                } else {

                    if (undefined != lddRepots.relationships[vars.courseId]) {
                        lessonOptions = this.createOptions(lessonFilterElement, lddRepots.relationships[vars.courseId][val]);
                    }

                    if (undefined != lddRepots.quiz_relationships[vars.courseId]) {
                        quizzOPtions = this.createOptions(quizzFilterElement, lddRepots.quiz_relationships[vars.courseId][val]);
                    }
                }
            }

            /********************* For Essay Filter **************************************/
            if (null != lessonFilterElement && null != quizzFilterElement) {

                if (lessonOptions.length >= 1) {
                    lessonFilterElement.disabled = false;

                    //Add options to select
                    this.addOption(lessonFilterElement, lessonOptions);
                    vars.lessonId = lessonFilterElement.value;
                } else {
                    lessonFilterElement.disabled = true;


                    /******************** New No Lesson Option ********************/
                    // if (lessonFilterElement.options.length != lessonOptions.length) {
                    //     for (let i = 0; i < lessonFilterElement.options.length; i++) {
                    //         if (0 === i) {
                    //             let noLessonOption = lessonFilterElement.options[i];
                    //             noLessonOption.style.display = "block";
                    //             noLessonOption.setAttribute('selected', true);
                    //         }else{
                    //             lessonFilterElement.options[i].removeAttribute('selected');
                    //             lessonFilterElement.options[i].style.display = "none";
                    //         }
                    //     }
                    // }

                    /******************** New No Quiz Option ********************/
                    // if (quizzFilterElement.options.length != quizzOPtions.length) {
                    //     for (let i = 0; i < quizzFilterElement.options.length; i++) {
                    //         if (0 === i) {
                    //             let noQuizzOption = quizzFilterElement.options[i];
                    //             noQuizzOption.style.display = "block";
                    //             noQuizzOption.setAttribute('selected', true);
                    //         } else {
                    //             quizzFilterElement.options[i].removeAttribute('selected');
                    //             quizzFilterElement.options[i].style.display = "none";
                    //         }
                    //     }
                    // }

                }

                if (quizzOPtions.length >= 1) {

                    quizzFilterElement.disabled = false;

                    this.addOption(quizzFilterElement, quizzOPtions);
                    vars.lessonId = quizzFilterElement.value;
                } else {
                    quizzFilterElement.disabled = true;
                }
            }

            /********************* For Assignment Report Filter **************************************/
            if (null != lessonFilterElement) {
                if (lessonOptions.length >= 1) {
                    lessonFilterElement.disabled = false;

                    //Add options to select
                    this.addOption(lessonFilterElement, lessonOptions);
                    vars.lessonId = lessonFilterElement.value;
                } else {
                    lessonFilterElement.disabled = true;
                }

            }

            /********************* For Quizz Report Filter **************************************/
            if (null != quizzFilterElement) {
                if (quizzOPtions.length >= 1) {

                    quizzFilterElement.disabled = false;

                    this.addOption(quizzFilterElement, quizzOPtions);
                    vars.lessonId = quizzFilterElement.value;
                } else {
                    quizzFilterElement.disabled = true;
                }

            }

            this.createTable(vars);
            this.getReportData(vars);
        });
    }

    lessonFilter = (vars) => {
        let __lessonFilter = document.getElementById('ld-dashboard-report-lesson');

        if (null != __lessonFilter) {
            __lessonFilter.addEventListener('change', (e) => {
                let val = e.target.value;
                let quizzFilterElement = document.getElementById('ld-dashboard-report-quiz');
                let quizzOptions = '';
                if (0 == val) {
                    quizzOptions = this.createOptions(quizzFilterElement, [0]);
                } else {
                    if (0 != vars.groupId) {
                        quizzOptions = this.createOptions(quizzFilterElement, lddRepots.quiz_relationships[vars.groupId][vars.courseId]);
                    } else {
                        quizzOptions = this.createOptions(quizzFilterElement, lddRepots.quiz_relationships[vars.courseId][vars.courseId]);
                    }
                }

                if (quizzOptions.length > 1) {
                    quizzFilterElement.disabled = false;
                }

                this.addOption(quizzFilterElement, quizzOptions);
                vars.lessonId = parseInt(val);
                this.createTable(vars);
                this.getReportData(vars);
            });
        }

    }

    quizFilter = (vars) => {

        let __quizFilter = document.getElementById('ld-dashboard-report-quiz');
        if (null !== __quizFilter) {
            __quizFilter.addEventListener('change', (e) => {
                let val = e.target.value;
                console.log(val);
                vars.quizId = parseInt(val);
                this.createTable(vars);
                this.getReportData(vars);
            });
        }

    }

    statusFilter = (vars) => {
        let statusFilter = document.getElementById('ld-dashboard-report-status');

        if (null !== statusFilter) {
            statusFilter.addEventListener('change', (e) => {
                let val = e.target.value;
                vars.status = val;
                this.createTable(vars);
                this.getReportData(vars);
            });
        }

    }

    createOptions = (source, ids) => {
        if (undefined == ids || null == ids) {
            return;
        }

        let initSourse = true;
        let sourseOpt = '';
        let options = [];

        if (initSourse) {
            sourseOpt = source;
            initSourse = false;
        }

        ids = ids.map(Number);
        if (ids.length >= 1) {
            ids.push(0);
        }

        if (null !== sourseOpt) {
            for (let opt of sourseOpt) {
                let optVal = parseInt(opt.value);
                if (!isNaN(optVal)) {
                    if (ids.indexOf(optVal) != -1) {
                        opt.style.display = 'block';
                        options.push(opt);
                    }
                }
            }
        }
        return options;
    }

    addOption = (filter, options) => {
        if (undefined !== options && options.length > 0) {
            this.removeOptions(filter, options);
            options.forEach((option, key) => {
                if (0 == key) {
                    option.setAttribute('selected', true);
                }
                option.style.display = "block";
            });
        }
    }

    removeOptions = (filter, filterOptions) => {
        if (null != filter) {
            for (let filterOption of filter.options) {
                if ('' == filterOption.value) {
                    filterOption.remove();
                }
                if (!filterOptions.includes(filterOption)) {
                    filterOption.removeAttribute('selected');
                    filterOption.style.display = "none";
                }
            }
        }
    }

    approveEssay = () => {
        let approveButtons = document.querySelectorAll('.essay_approve_single');
        approveButtons.forEach((approveButton, index) => {
            approveButton.addEventListener('click', (e) => {
                let essay_id = e.target.dataset.id;
                let essay_points = document.getElementById('essay_points_' + essay_id).value;
                let max_points = document.getElementById('essay_points_' + essay_id).getAttribute("max");
                if ('0' === max_points) {
                    Swal.fire({
                        text: __('The essay will only be approved which has max points greater than 0.', 'ld-dashboard'),
                        icon: 'error',
                    });
                    return false;
                }
                let params = {
                    action: "ld_dashboard_essay_approve_single",
                    essay_id: essay_id,
                    essay_points: essay_points,
                    nonce: ld_dashboard_js_object.ajax_nonce,
                };
                jQuery.ajax({
                    url: ld_dashboard_js_object.ajaxurl,
                    type: "post",
                    data: params,
                    success: function (response) {
                        location.reload(true);
                    },
                });
            });
        });
    }

    approveAssingment = () => {
        let approveButtons = document.querySelectorAll('.ld-dashboard-approve-assignment-btn');
        approveButtons.forEach((approveButton, index) => {
            approveButton.addEventListener('click', (e) => {
                e.preventDefault();
                let assignmentID = e.target.dataset.id;
                if ('enabled' === document.getElementById('assignment_points_' + assignmentID).dataset.point){
                    var assingmentPoints = document.getElementById('assignment_points_' + assignmentID).value;
                    let maxPoints = document.getElementById('assignment_points_' + assignmentID).getAttribute("max");

                    if ('0' === maxPoints) {
                        Swal.fire({
                            text: __('The assingment will only be approved which has max points greater than 0.', 'ld-dashboard'),
                            icon: 'error',
                        });
                        return false;
                    }
                }
                
                
                
                let params = {
                    action: "ld_dashboard_approve_assignment",
                    assignment_id: assignmentID,
                    assingmentPoints: assingmentPoints,
                    nonce: ld_dashboard_js_object.ajax_nonce,
                };
                jQuery.ajax({
                    url: ld_dashboard_js_object.ajaxurl,
                    type: "post",
                    data: params,
                    success: function (response) {
                        location.reload(true);
                    },
                });


            });
        });
    }

    quizStats = () => {
        let buttons = document.querySelectorAll('a.user_statistic');
        buttons.forEach((button) => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                let modalOverlay = document.getElementById('wpProQuiz_user_overlay');
                let loader = document.getElementById('wpProQuiz_loadUserData');
                modalOverlay.style.display = "block";
                loader.style.display = "block";
                let refId = e.target.dataset.ref_id;
                let quizId = e.target.dataset.quiz_id;
                let userId = e.target.dataset.user_id;
                let statistic_nonce = e.target.dataset.statistic_nonce;
                let security = e.target.dataset.nonce;
                let action = 'wp_pro_quiz_admin_ajax';
                let param = { action: action, func: "statisticLoadUser", nonce: security, data: { quizId: quizId, userId: userId, refId: refId, statistic_nonce: statistic_nonce, avg: 0 } };
                let modalContent = document.getElementById('wpProQuiz_user_content');
                jQuery.ajax({
                    type: "POST",
                    url: ld_dashboard_js_object.ajaxurl,
                    dataType: "json",
                    cache: false,
                    data: param,
                    error: function (response) {
                        loader.style.display = "none";
                        modalContent.innerHTML = response.responseText;
                        document.getElementById('wpProQuiz_overlay_close').addEventListener('click', (e) => {
                            modalOverlay.style.display = "none";
                        });
                    },
                    success: function (response) {
                        loader.style.display = "none";
                        modalContent.innerHTML = response.html;
                        document.querySelector('a.wpProQuiz_update').remove();
                        document.getElementById('wpProQuiz_overlay_close').addEventListener('click', (e) => {
                            modalOverlay.style.display = "none";
                        });
                    }
                });
            });
        });
    }
}





document.addEventListener("DOMContentLoaded", () => {
    let bodyClasses = document.body.classList;
    if (bodyClasses.contains('assignment-report') || bodyClasses.contains('essay-report') || bodyClasses.contains('quizz-report')) {
        new LD_Dashboard_Report();
    }

});