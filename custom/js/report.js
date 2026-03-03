$(document).ready(function () {
	$(function () {

		$("#startDate,#endDate").datepicker({ dateFormat: "mm/dd/yy" });

		$("#getOrderReportForm").submit(function (e) {
			e.preventDefault();

			$("#orderReportResult").html("<p class='text-info'>Loading...</p>");

			$.post("php_action/getOrderReport.php", $(this).serialize(), function (data) {
				$("#orderReportResult").html(data);

				$('#orderReportTable').DataTable({
					dom: 'Bfrtip',
					pageLength: 100,
					buttons: [
						{
							extend: 'excelHtml5',
							text: '<span id="excelBtnText">Save as Excel</span>',
							title: 'Order Report',
							footer: true,
							action: function (e, dt, node, config) {
								// Show loader inside button
								$('#excelBtnText').html('<i class="glyphicon glyphicon-refresh glyphicon-spin"></i> Generating...');

								// Use default action
								$.fn.dataTable.ext.buttons.excelHtml5.action.call(this, e, dt, node, config);

								// Reset text after a short delay (Excel generation is instant usually)
								setTimeout(function () {
									$('#excelBtnText').html('Save as Excel');
								}, 1500); // adjust time if needed
							}
						}
					],
					destroy: true
				});



			});
		});

	});


});