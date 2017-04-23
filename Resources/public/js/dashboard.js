var translationMessages = {
    'statusNew': 'الجديدة',
    'statusInprogress': 'تحت التشغيل',
    'statusPublished': 'منشورة',
    'statusDeleted': 'محذوفة',
    'statusApproved': 'مقبولة',
    'statusRejected': 'مرفوضة',
    'statusFlagged': 'مسيئة',
    'percentage': 'النسبة بينهم',
    'from': 'من',
    'to': 'إلي',
    'visitors': 'المستخدمين',
    'staff': 'الموظفون',
    'active': 'مفعل',
    'inactive': 'غير مفعل',
    'count': 'عدد',
    'theCount': 'العدد',
    'status': 'الحالة',
    'news': 'الأخبار',
    'articles': 'المقالات',
    'inOldYearNotAvailable': 'فى السنه السابقة غير متوفر',
    'theName': 'الإسم',
    'theTime': 'الوقت',
    'comments': 'التعليقات',
    'sessionsAndUsers': 'الجلسات و المستخدمين',
    'apply': 'تنفيذ',
    'cancel': 'إلغاء',
    'publishedRecipes': 'الوصفات المنشورة',
    'publishedArticles': 'المقالات المنشورة'
};

var googleApiColumnsNames = {
    'ga:pagePath': 'رابط الصفحة',
    'ga:pageviews': 'عدد المشاهدات',
    'ga:country': 'البلد',
    'ga:city': 'المدينة',
    'ga:date': 'الوقت',
    'ga:socialNetwork': 'الشبكة الإجتماعية',
    'ga:sessions': 'الجلسات',
    'ga:users': 'الزوار',
    'ga:keyword': 'كلمة البحث',
    'ga:organicSearches': 'عدد مرات البحث',
    'ga:avgSessionDuration': 'متوسط وقت الجلسة'
};

var cssClassNames = {
    'headerRow': 'table-header',
    'tableRow': '',
    'oddTableRow': 'grey-background',
    'selectedTableRow': 'hoverSelected',
    'hoverTableRow': 'hoverRow',
    'headerCell': 'dark-cell',
    'tableCell': '',
    'rowNumberCell': 'numberCell'
};

var pieChartColors = ['#41CCC0', '#2CBAAC', '#F7DA64', '#9365B8', '#EB6B56', '#61BD6D'];

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 * @param {String} TimeInSeconds
 * @returns {String}
 */
function toHHMMSS(TimeInSeconds) {
    var sec_num = parseInt(TimeInSeconds, 10); // don't forget the second param
    var hours = Math.floor(sec_num / 3600);
    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
    var seconds = sec_num - (hours * 3600) - (minutes * 60);

    if (hours < 10) {
        hours = '0' + hours;
    }
    if (minutes < 10) {
        minutes = '0' + minutes;
    }
    if (seconds < 10) {
        seconds = '0' + seconds;
    }
    return hours + ':' + minutes + ':' + seconds;
}

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 * @param {object} dataTable
 * @returns {object}
 */
function setDataTableColumnsTitles(dataTable) {
    var dataTableColumnsLength = dataTable.cols.length;
    var numberColumns = [];
    var timeColumns = [];
    for (var i = 0; i < dataTableColumnsLength; i++) {
        dataTable.cols[i].label = googleApiColumnsNames[dataTable.cols[i].label];
        if (dataTable.cols[i].type === 'number') {
            numberColumns.push(i);
        }
        if (dataTable.cols[i].id === 'ga:avgSessionDuration') {
            timeColumns.push(i);
        }
    }
    if (typeof dataTable.rows !== 'undefined') {
        var rowsLength = dataTable.rows.length;
        var timeColumnsLength = timeColumns.length;
        var numberColumnsLength = numberColumns.length;
        if (timeColumnsLength > 0 || numberColumnsLength > 0) {
            for (var i = 0; i < rowsLength; i++) {
                for (var j = 0; j < numberColumnsLength; j++) {
                    dataTable.rows[i].c[numberColumns[j]].v = parseInt(dataTable.rows[i].c[numberColumns[j]].v);
                }
                for (var k = 0; k < timeColumnsLength; k++) {
                    dataTable.rows[i].c[timeColumns[k]].f = toHHMMSS(dataTable.rows[i].c[timeColumns[k]].v);
                }
            }
        }
    }
    return dataTable;
}

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 * @param {object} dataTable
 * @returns {object}
 */
function constructMapDataTable(dataTable) {
    var mapDataTable = [[googleApiColumnsNames['ga:city'], googleApiColumnsNames['ga:sessions'], googleApiColumnsNames['ga:users']]];
    if (typeof dataTable.rows !== 'undefined') {
        var rowsLength = dataTable.rows.length;
        for (var i = 0; i < rowsLength; i++) {
            var columnsLength = dataTable.rows[i].c.length - 1;
            var rowData = [];
            for (var j = 0; j < columnsLength; j++) {
                if (typeof dataTable.rows[i].c[j].f !== 'undefined') {
                    rowData.push(dataTable.rows[i].c[j].f);
                } else {
                    rowData.push(dataTable.rows[i].c[j].v);
                }
            }
            mapDataTable.push(rowData);
        }
    } else {
        mapDataTable.push(['', 0, 0]);
    }
    return mapDataTable;
}

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 * @param {Number} newNumber
 * @param {Number} originalNumber
 * @returns {Number}
 */
function getPercentageBetweenTwoNumbers(newNumber, originalNumber) {
    if (originalNumber > 0) {
        return Math.round((((newNumber - originalNumber) / originalNumber) * 100) * 10) / 10;
    }
    return 0;
}

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 * @param {string} fromDate
 * @param {string} toDate
 * @returns {void}
 */
function getChefsStatistics(fromDate, toDate) {
    $.ajax({
        url: chefsStatisticsUrl + '?from=' + encodeURIComponent(fromDate) + '&to=' + encodeURIComponent(toDate),
        success: function (data, textStatus, jqXHR) {
            if (jqXHR.status === 200 && typeof jqXHR.responseJSON === 'object' && typeof jqXHR.responseJSON.code !== 'undefined' && jqXHR.responseJSON.code === 200) {
                var dataTableData = {cols: [], rows: []};
                dataTableData.cols.push({id: '1', label: translationMessages.theName, type: 'string'});
                dataTableData.cols.push({id: '2', label: translationMessages.publishedRecipes, type: 'number'});
                dataTableData.cols.push({id: '3', label: translationMessages.publishedArticles, type: 'number'});
                var dataLength = data.chefs.length;
                for (var i = 0; i < dataLength; i++) {
                    dataTableData.rows.push({c: [{v: data.chefs[i].name}, {v: data.chefs[i].publishedRecipesCount}, {v: data.chefs[i].publishedArticlesCount}]});
                }
                var chefsDataTable = new google.visualization.DataTable(dataTableData);
                new google.visualization.Table(document.getElementById('chef-published-materials-table-container')).
                        draw(chefsDataTable, {'width': '100%', 'showRowNumber': true, 'page': true, 'cssClassNames': cssClassNames, 'sortColumn': 1, 'sortAscending': false});
            }
        }
    });
}

/**
 * @author Mahmoud Mostafa <mahmoud.mostafa@ibtikar.net.sa>
 * @param {string} fromDate
 * @param {string} toDate
 * @param {string} type
 * @returns {void}
 */
function getRecipesStatistics(fromDate, toDate, type) {
    $.ajax({
        url: recipeStatisticsUrl + '?from=' + encodeURIComponent(fromDate) + '&to=' + encodeURIComponent(toDate) + '&type=' + encodeURIComponent(type),
        success: function (data, textStatus, jqXHR) {
            if (jqXHR.status === 200 && typeof jqXHR.responseJSON === 'object' && typeof jqXHR.responseJSON.code !== 'undefined' && jqXHR.responseJSON.code === 200) {
                var dataTableData = {cols: [], rows: []};
                dataTableData.cols.push({id: '1', label: translationMessages.statusPublished, type: 'number'});
                dataTableData.cols.push({id: '2', label: translationMessages.statusDeleted, type: 'number'});
                dataTableData.cols.push({id: '3', label: translationMessages.statusNew, type: 'number'});
                dataTableData.rows.push({c: [{v: data.counts.publishedCount}, {v: data.counts.deletedCount}, {v: data.counts.newCount}]});
                var recipesDataTable = new google.visualization.DataTable(dataTableData);
                new google.visualization.Table(document.getElementById(type + '-statistics-table-container')).
                        draw(recipesDataTable, {'width': '100%', 'showRowNumber': true, 'page': true, 'cssClassNames': cssClassNames});
            }
        }
    });
}

google.charts.setOnLoadCallback(function () {
    gapi.analytics.ready(function () {

        gapi.analytics.auth.authorize({
            container: 'embed-api-auth-container',
            clientid: CLIENT_ID
        });

        var viewSelector = new gapi.analytics.ViewSelector({
            container: 'view-selector-container'
        });

        /**
         * Query params representing the first chart's date range.
         */
        var dateRange = {
            'start-date': 'yesterday',
            'end-date': 'today'
        };

        var usersAndSessionsCountryTableChart;
        var usersAndSessionsCountryTableDataTable;

        var dateRangeSelector = new gapi.analytics.ext.DateRangeSelector({
            container: 'date-range-selector-container'
        }).set(dateRange);

        dateRangeSelector.execute();

        $('#date-range-selector').daterangepicker({
            locale: {
                direction: 'rtl',
                format: 'YYYY-MM-DD',
                applyLabel: translationMessages.apply,
                cancelLabel: translationMessages.cancel
            },
            startDate: dateRangeSelector['start-date'],
            endDate: dateRangeSelector['end-date'],
            opens: 'left'
        }, function (start, end) {
            $('#date-range-selector-container').find('input:first').val(start.format('YYYY-MM-DD'));
            $('#date-range-selector-container').find('input:last').val(end.format('YYYY-MM-DD')).trigger('change');
        });

        getChefsStatistics(dateRangeSelector['start-date'], dateRangeSelector['end-date']);
        for (var i = 0; i < recipeTypes.length; i++) {
            getRecipesStatistics(dateRangeSelector['start-date'], dateRangeSelector['end-date'], recipeTypes[i]);
        }

        gapi.analytics.auth.on('success', function () {
            viewSelector.execute();
        });

        var usersAndSessionsCountryTableData = new gapi.analytics.report.Data({
            query: {
                'output': 'dataTable',
                'start-date': dateRange['start-date'],
                'end-date': dateRange['end-date'],
                'dimensions': 'ga:country',
                'metrics': 'ga:sessions,ga:users,ga:avgSessionDuration',
                'sort': '-ga:sessions',
                'max-results': '12'
            }
        });

        usersAndSessionsCountryTableData.on('success', function (response) {
            usersAndSessionsCountryTableChart = new google.visualization.Table(document.getElementById('users-and-sessions-country-table-container'));
            usersAndSessionsCountryTableDataTable = new google.visualization.DataTable(setDataTableColumnsTitles(response.dataTable));
            usersAndSessionsCountryTableChart.draw(usersAndSessionsCountryTableDataTable, {'width': '100%', 'showRowNumber': true, 'page': true, 'cssClassNames': cssClassNames, 'sortColumn': 1, 'sortAscending': false});
        });

        var usersAndSessionsByCountryChartData = new gapi.analytics.report.Data({
            query: {
                'output': 'dataTable',
                'start-date': dateRange['start-date'],
                'end-date': dateRange['end-date'],
                'dimensions': 'ga:date',
                'metrics': 'ga:sessions,ga:users'
            }
        });

        usersAndSessionsByCountryChartData.on('success', function (response) {
            var chart = new google.visualization.AreaChart(document.getElementById('users-and-sessions-by-country-chart-container'));
            var dataTable = new google.visualization.DataTable(setDataTableColumnsTitles(response.dataTable));
            chart.draw(dataTable, {'width': '100%', 'height': '400', vAxis: {title: translationMessages.theCount}, hAxis: {title: translationMessages.theTime}});
        });

        var saudiArabiaCitiesTableData = new gapi.analytics.report.Data({
            query: {
                'output': 'dataTable',
                'start-date': dateRange['start-date'],
                'end-date': dateRange['end-date'],
                'dimensions': 'ga:city',
                'metrics': 'ga:sessions,ga:users,ga:avgSessionDuration',
                'filters': 'ga:country==Saudi Arabia',
                'sort': '-ga:sessions',
                'max-results': '14'
            }
        });

        saudiArabiaCitiesTableData.on('success', function (response) {
            var chart = new google.visualization.Table(document.getElementById('users-and-sessions-ksa-city-table-container'));
            var dataTable = new google.visualization.DataTable(setDataTableColumnsTitles(response.dataTable));
            chart.draw(dataTable, {'width': '100%', 'showRowNumber': true, 'page': true, 'cssClassNames': cssClassNames, 'sortColumn': 1, 'sortAscending': false});
            var ksaMapChart = new google.visualization.GeoChart(document.getElementById('users-and-sessions-ksa-city-map-container'));
            ksaMapChart.draw(google.visualization.arrayToDataTable(constructMapDataTable(response.dataTable)), {'region': 'SA', displayMode: 'markers'});
        });

        var socialTrafficTableData = new gapi.analytics.report.Data({
            query: {
                'output': 'dataTable',
                'start-date': dateRange['start-date'],
                'end-date': dateRange['end-date'],
                'dimensions': 'ga:socialNetwork',
                'metrics': 'ga:sessions,ga:users,ga:avgSessionDuration',
                'filters': 'ga:hasSocialSourceReferral==Yes',
                'sort': '-ga:sessions',
                'max-results': '12'
            }
        });

        socialTrafficTableData.on('success', function (response) {
            var chart = new google.visualization.Table(document.getElementById('social-trafic-table-container'));
            var dataTable = new google.visualization.DataTable(setDataTableColumnsTitles(response.dataTable));
            chart.draw(dataTable, {'width': '100%', 'showRowNumber': true, 'page': true, 'cssClassNames': cssClassNames, 'sortColumn': 1, 'sortAscending': false});
            var barChart = new google.visualization.ColumnChart(document.getElementById('social-trafic-column-chart-container'));
            barChart.draw(dataTable, {'height': '400'});
        });

        var topViewedPagesTableData = new gapi.analytics.report.Data({
            query: {
                'output': 'dataTable',
                'start-date': dateRange['start-date'],
                'end-date': dateRange['end-date'],
                'dimensions': 'ga:pagePath',
                'metrics': 'ga:pageviews',
                'sort': '-ga:pageviews',
                'max-results': '15'
            }
        });

        topViewedPagesTableData.on('success', function (response) {
            var chart = new google.visualization.Table(document.getElementById('top-viewed-pages-container'));
            var dataTable = new google.visualization.DataTable(setDataTableColumnsTitles(response.dataTable));
            var formatter = new google.visualization.PatternFormat('<a target="_blank" href="{0}">{0}</a>');
            formatter.format(dataTable, [0]);
            chart.draw(dataTable, {'width': '100%', 'allowHtml': true, 'showRowNumber': true, 'page': true, 'cssClassNames': cssClassNames, 'sortColumn': 1, 'sortAscending': false});
        });

        var todayMostUsedSearchKeywordsTableData = new gapi.analytics.report.Data({
            query: {
                'output': 'dataTable',
                'start-date': 'yesterday',
                'end-date': 'today',
                'filters': 'ga:keyword!=(not provided)',
                'dimensions': 'ga:keyword',
                'metrics': 'ga:organicSearches',
                'sort': '-ga:organicSearches',
                'max-results': '5'
            }
        });

        todayMostUsedSearchKeywordsTableData.on('success', function (response) {
            var chart = new google.visualization.Table(document.getElementById('most-used-search-words-table-container'));
            var dataTable = new google.visualization.DataTable(setDataTableColumnsTitles(response.dataTable));
            chart.draw(dataTable, {'width': '100%', 'showRowNumber': true, 'page': true, 'cssClassNames': cssClassNames, 'sortColumn': 1, 'sortAscending': false});
        });

        dateRangeSelector.on('change', function (data) {
            usersAndSessionsCountryTableData.set({query: data}).execute();
            usersAndSessionsByCountryChartData.set({query: data});
            saudiArabiaCitiesTableData.set({query: data}).execute();
            socialTrafficTableData.set({query: data}).execute();
            topViewedPagesTableData.set({query: data}).execute();
            getChefsStatistics(data['start-date'], data['end-date']);
            for (var i = 0; i < recipeTypes.length; i++) {
                getRecipesStatistics(dateRangeSelector['start-date'], dateRangeSelector['end-date'], recipeTypes[i]);
            }
        });

        /**
         * Store a refernce to the row click listener variable so it can be
         * removed later to prevent leaking memory when the chart instance is
         * replaced.
         */
        var usersAndSessionsTableRowClickListener;

        /**
         * Update charts whenever the selected view changes.
         */
        viewSelector.on('change', function (ids) {
            var options = {query: {ids: ids}};
            usersAndSessionsCountryTableData.set(options).execute();
            usersAndSessionsByCountryChartData.set(options);
            saudiArabiaCitiesTableData.set(options).execute();
            socialTrafficTableData.set(options).execute();
            topViewedPagesTableData.set(options).execute();
            todayMostUsedSearchKeywordsTableData.set(options).execute();
        });

        /**
         * Each time the main chart is rendered, add an event listener to it so
         * that when the user clicks on a row, the line chart is updated with
         * the data from the browser in the clicked row.
         */
        usersAndSessionsCountryTableData.on('success', function () {
            var $usersAndSessionsByCountryChartTitleh3 = $('#users-and-sessions-by-country-chart-title');
            // Clear the country filter from the users chart
            usersAndSessionsByCountryChartData.set({query: {filters: null}}).execute();
            $usersAndSessionsByCountryChartTitleh3.html(translationMessages.sessionsAndUsers);
            if (usersAndSessionsTableRowClickListener) {
                google.visualization.events.removeListener(usersAndSessionsTableRowClickListener);
            }
            usersAndSessionsTableRowClickListener = google.visualization.events.addListener(usersAndSessionsCountryTableChart, 'select', function () {
                var options = {query: {filters: null}};
                var chartTitle = translationMessages.sessionsAndUsers;
                if (usersAndSessionsCountryTableChart.getSelection().length > 0) {
                    var country = usersAndSessionsCountryTableDataTable.getValue(usersAndSessionsCountryTableChart.getSelection()[0].row, 0);
                    if (country) {
                        options.query.filters = 'ga:country==' + country;
                        chartTitle += ' ' + translationMessages.from + ' ' + country;
                    }
                }
                usersAndSessionsByCountryChartData.set(options).execute();
                $usersAndSessionsByCountryChartTitleh3.html(chartTitle);
            });
        });
    });
});