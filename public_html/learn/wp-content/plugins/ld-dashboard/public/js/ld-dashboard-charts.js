const { __, _x, _n, sprintf } = wp.i18n;
let ldChart = '';
class LD_Dashboard_Chart {

	init = () => {
		if (ld_dashboard_chart_object.instructor_earning) {
			this.earningChart();
		}
		this.courseCompletionChart();
		this.singleCourseCompletionChart();
		//if ('' === ld_dashboard_chart_object.is_group_leader)
		{
			this.topCourseChart();
			this.chartFilter();
		}
		
	}
	earningChart = (filter = 'year') => {
		if (jQuery('#ld-dashboard-instructor-earning-chart-wrapper').length <= 0) {
			return;
		}

		document.querySelector('.ld-dashboard-instructor-earning-chart-wrapper').innerHTML = this.chartLoader(true);

		let params = {
			action: "ld_dashboard_get_instructor_earning_chart_data",
			nonce: ld_dashboard_chart_object.ajax_nonce,
			filter: filter,
		};
		jQuery.ajax({
			url: ld_dashboard_chart_object.ajaxurl,
			type: "post",
			data: params,
			success: (response) => {
				let result = JSON.parse(response);
				var labels = result.keys;
				var values = result.values;
				var total = result.total;
				document.querySelector('.ld-dashboard-instructor-earning-chart-wrapper').innerHTML = '<canvas id="ld-dashboard-instructor-earning-chart" class="ld-dashboard-chart-js"></canvas>';
				var ctx = document.getElementById('ld-dashboard-instructor-earning-chart');
				if (jQuery('.ldd-dashboard-earning-amount-content').length) {
					jQuery('.ldd-dashboard-earning-amount-content span.ldd-dashboard-earning-amount').html(total);
				}
				let data = {
					labels: labels,
					datasets: [{
						label: __('Earnings', 'ld-dashboard'),
						data: values,
						fill: true,
						backgroundColor: '#c4d7ed',
						borderColor: '#1d76da',
						tension: 0.1,
						pointStyle: 'circle',
						pointRadius: 3,
						pointBorderColor: '#1d76da',
						pointBorderWidth: 2,
						pointBackgroundColor: '#fff',
					}]
				};
				this.drawChart(ctx, 'line', {}, data);
			},
		});
	}

	courseCompletionChart = (filter) => {
		document.querySelector('.ld-dashboard-course-completion-report-wrapper').innerHTML = this.chartLoader(true);
		let params = {
			action: "course_complition_rate",
			nonce: ld_dashboard_chart_object.ajax_nonce,
			filter: filter,
		};
		jQuery.ajax({
			url: ld_dashboard_chart_object.ajaxurl,
			type: "post",
			data: params,
			success: (response) => {
				if (response.success) {
					let data = response.data;
					let perCourseDatas = data.courseWiseCompletion;
					let labels = [];
					let values = [];
					for (const perCourseData in perCourseDatas) {
						labels.push(perCourseDatas[perCourseData].title);
						values.push(perCourseDatas[perCourseData].completion);
					}
					this.courseCompletionSummary(data, 'all');
					document.querySelector('.ld-dashboard-course-completion-report-wrapper').innerHTML = '<div class="ld-dashboard-doughnut-chart-container"><canvas id="ld-dashboard-course-completion-chart" class="ld-dashboard-chart-js"></canvas></div>';
					var ctx = document.getElementById('ld-dashboard-course-completion-chart');
					let backgroundcolor = [];
					let bordercolor = [];
					for (let i = 0; i < labels.length; i++) {
						let r = Math.floor(Math.random() * 255);
						let g = Math.floor(Math.random() * 255);
						let b = Math.floor(Math.random() * 255);
						backgroundcolor.push('rgba(' + r + ', ' + g + ', ' + b + ')');
						bordercolor.push('rgba(' + r + ', ' + g + ', ' + b + ')');

					}

					let chartData = {
						labels: labels,
						datasets: [
							{
								data: values,
								backgroundColor: backgroundcolor,
								borderColor: bordercolor
							}
						],
					};


					let options = {
						plugins: {
							legend: {
								display: true,
								position: 'top'
							}

						},

					}
					this.drawChart(ctx, 'doughnut', options, chartData);
				} else {
					document.querySelector('.ld-dashboard-course-completion-report-summary').style.display = 'none';
					document.querySelector('.ld-dashboard-course-completion-report-wrapper').innerHTML = '<div class="ld-dashboard-chart-notice">' + response.data + '<div>';
				}

			},
		});
	}

	courseCompletionSummary = (responceData, scope = '') => {

		let summary = [
			{
				title: __('Total Students', 'ld-dashboard'),
				value: parseInt(responceData.completedCount) + parseInt(responceData.notstartedCount) + parseInt(responceData.inprogressCount)
			},
			{
				title: __('Students - Completed All Courses', 'ld-dashboard'),
				value: responceData.completedCount
			},
			{
				title: __('Students - Not Started Any', 'ld-dashboard'),
				value: responceData.notstartedCount
			},
			{
				title: __('Students - In Progress', 'ld-dashboard'),
				value: responceData.inprogressCount
			}
		];

		if ('' != scope && 'all' === scope) {
			summary.push(
				{
					title: __('Courses', 'ld-dashboard'),
					value: responceData.totlaCourses
				}
			);
		}

		let summaryHtml = [];
		summary.forEach((s, index) => {
			summaryHtml.push(`<div class="ld-dashboard-summery-right-entry"><div class="ld-dashboard-summary-lable">${s.title}: </div><div class="ld-dashboard-summary-amount">${s.value}</div></div>`);
		});

		let avgCourseCompletion = `<h3 class="ld-dashboard-chart-summary-amount">${responceData.averageCourseCompletion}%</h3><div class="ld-dashboard-summary-lable"><span>${__('AVG course completion rate', 'ld-dashboard')}</span></div>`;
		if (window.getComputedStyle(document.querySelector('.ld-dashboard-course-completion-report-summary')).display === 'none') {
			document.querySelector('.ld-dashboard-course-completion-report-summary').style.display = "flex";
		}
		// document.querySelector('.ld-dashboard-course-completion-report-summary').style.display='block';
		document.querySelector('.ld-dashbord-course-average').innerHTML = avgCourseCompletion;
		document.querySelector('.ld-dashbord-course-particulars').innerHTML = summaryHtml.join(' ');

	}

	singleCourseCompletionChart = () => {
		let courseSelect = document.getElementById('ld-dashboard-course-completion-course-filter-select');
		jQuery(courseSelect).select2();
		jQuery(courseSelect).on('select2:select', (e) => {
			document.querySelector('.ld-dashboard-course-completion-report-wrapper').innerHTML = this.chartLoader(true);
			let courseID = e.target.value;

			if ('' !== courseID && '0' !== courseID) {
				let params = {
					action: "course_complition_rate",
					nonce: ld_dashboard_chart_object.ajax_nonce,
					course: courseID,
				};

				jQuery.ajax({
					url: ld_dashboard_chart_object.ajaxurl,
					type: "post",
					data: params,
					success: (response) => {
						document.querySelector('.ld-dashboard-course-completion-report-wrapper').innerHTML = '<div class="ld-dashboard-bar-chart-container"><canvas id="ld-dashboard-course-completion-chart" class="ld-dashboard-chart-js"></canvas></div>';
						var ctx = document.getElementById('ld-dashboard-course-completion-chart');
						if (response.success) {
							let data = response.data;
							let progressDatas = data.progress_data;
							let labels = [];
							let values = [];
							values.push(response.data.completedCount);
							values.push(response.data.notstartedCount);
							values.push(response.data.inprogressCount);
							labels = [__('Complete', 'ld-dashboard'), __('Not Started', 'ld-dashboard'), __('In Progress', 'ld-dashboard')];
							let backgroundcolor = [ld_dashboard_chart_object.completed_color, ld_dashboard_chart_object.not_started_color, ld_dashboard_chart_object.in_progress_color];
							let bordercolor = [];
							// for (let i = 0; i < labels.length; i++) {
							// 	let r = Math.floor(Math.random() * 255);
							// 	let g = Math.floor(Math.random() * 255);
							// 	let b = Math.floor(Math.random() * 255);
							// 	backgroundcolor.push('rgba(' + r + ', ' + g + ', ' + b + ', 0.1)');
							// 	bordercolor.push('rgba(' + r + ', ' + g + ', ' + b + ')');

							// }

							this.courseCompletionSummary(data, 'single');
							let chartData = {
								labels: labels,
								datasets: [
									{
										label: __('Rate of completion', 'ld-dashboard'),
										data: values,
										// borderWidth: 1,
										// borderColor: bordercolor,
										backgroundColor: backgroundcolor,
										hoverOffset: 25
									}
								],
							};

							let options = {
								maintainAspectRatio: false,
								plugins: {
									legend: {
										display: true
									}

								},
								layout: {
									padding: {
										bottom: 15
									}
								}
							};
							this.drawChart(ctx, 'pie', options, chartData);
						} else {
							document.querySelector('.ld-dashboard-course-completion-report-summary').style.display = 'none';
							document.querySelector('.ld-dashboard-course-completion-report-wrapper').innerHTML = '<div class="ld-dashboard-chart-notice">' + response.data + '<div>';
						}
					},
				});
			} else {
				this.courseCompletionChart('year');
			}
		});
	}

	topCourseChart = (filter = 'year') => {
		let params = {
			action: "ld_dashboard_get_top_courses_chart_data",
			nonce: ld_dashboard_chart_object.ajax_nonce,
			filter: filter,
		};
		jQuery.ajax({
			url: ld_dashboard_chart_object.ajaxurl,
			type: "post",
			data: params,
			success: (response) => {
				let result = JSON.parse(response);
				var labels = result.keys;
				var values = result.values;
				let chart_html = '<canvas id="ld-dashboard-top-courses-chart" class="ld-dashboard-chart-js"></canvas>';
				document.querySelector('.ld-dashboard-top-courses-report-wrapper').innerHTML = chart_html;
				var ctx = document.getElementById('ld-dashboard-top-courses-chart');
				let backgroundcolor = [];
				let bordercolor = [];
				for (let i = 0; i < labels.length; i++) {
					let r = Math.floor(Math.random() * 255);
					let g = Math.floor(Math.random() * 255);
					let b = Math.floor(Math.random() * 255);
					backgroundcolor.push('rgba(' + r + ', ' + g + ', ' + b + ', 0.1)');
					bordercolor.push('rgba(' + r + ', ' + g + ', ' + b + ')');
				}
				let chartData = {
					labels: labels,
					datasets: [
						{
							label: __('Total Completions', 'ld-dashboard'),
							data: values,
							backgroundColor: backgroundcolor,
							borderColor: bordercolor,
							borderWidth: 1
						}
					],
				};

				let options = {
					maintainAspectRatio: false,
					scales: {
						y: {
							beginAtZero: true,
							// max: max
						}
					},
					plugins: {
						legend: {
							display: true
						}

					},

				};
				this.drawChart(ctx, 'bar', options, chartData);
			},
		});
	}


	chartFilter = () => {
		let filter = 'year';
		let filterEles = document.querySelectorAll('li.ld-dashboard-instructor-earning-filters-link');

		filterEles.forEach((filterEle) => {
			let type = filterEle.parentElement.dataset.type;

			filterEle.addEventListener('click', () => {
				filterEles.forEach((filterEle) => {
					filterEle.classList.remove('filter-selected');
				});

				filterEle.classList.add('filter-selected');

				filter = filterEle.dataset.filter;

				if ('earning_chart' === type) {
					this.earningChart(filter);
				} else if ('top_courses_chart' === type) {
					this.topCourseChart(filter);
				}
			});
		});
	}

	drawChart = (ctx, type, options, data) => {
		ldChart = new Chart(ctx, {
			type: type,
			options: options,
			data: data
		});
	}

	updateChart = () => {
		ldChart.destroy();
	}

	chartLoader = (display = false) => {
		if (display) {
			return '<div class="ld-dashboard-chart-loader"><img src="' + ld_dashboard_chart_object.loader + '" /></div>';
		}
	}
}

document.addEventListener("DOMContentLoaded", () => {
	if (document.body.classList.contains('ld-dashboard') && '' === ld_dashboard_chart_object.is_student) {
		new LD_Dashboard_Chart().init();
	}
});
