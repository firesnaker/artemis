/*BEGIN invoice Purchase related jQuery functions*/

	function load_purchase_data_for_verification_modal(purchase_id)
	{
		if (purchase_id > 0)
		{
			//load the product data and fill the form
			var row_load = $.ajax({
				type: "GET",
				url: "ctrl/purchase.php",
				data: {id : purchase_id},
				dataType: "json"
			});
			row_load.done(function(msg) {
				$("#verifyPurchaseOutlet").html(msg.outletName);
				$("#verifyPurchaseDate").html(msg.Date);
				$("#verifyPurchaseNotes").html(msg.Notes);
			});
		}
	}


	$('#formPurchase')
	.bootstrapValidator({
		message: 'This value is not valid',
		excluded: [':disabled'],
		feedbackIcons: {
			required: 'fa fa-asterisk',
			valid: 'fa fa-check',
			invalid: 'fa fa-times',
			validating: 'fa fa-refresh'
		},
		fields: {
			purchaseID: {
				message: 'The Purchase ID is not valid.',
				validators: {
					digits: {
						message: 'The Purchase ID can only consist of numbers.'
					}
				}
			},
			outletID: {
				message: 'The Outlet ID is not valid.',
				validators: {
					digits: {
						message: 'The Outlet ID can only consist of numbers.'
					}
				}
			},
			supplierID: {
				message: 'The Supplier ID is not valid.',
				validators: {
					digits: {
						message: 'The Supplier ID can only consist of numbers.'
					}
				}
			},
			paymentTypeID: {
				message: 'The Payment Type ID is not valid.',
				validators: {
					digits: {
						message: 'The Payment Type ID can only consist of numbers.'
					}
				}
			},
			purchaseDate: {
				message: 'The Purchase Date is not valid.',
				validators: {
					regexp: {
						regexp: /./mi,
						message: 'The Purchase Date is always valid.'
					}
				}
			},
			purchaseNotes: {
				message: 'The Purchase Notes is not valid.',
				validators: {
					regexp: {
						regexp: /./mi,
						message: 'The Purchase Notes is always valid.'
					}
				}
			},
			outletLookUp: {
				message: 'The Outlet is not valid.',
				validators: {
					remote: {
						url: 'ctrl/validator.php',
						message: 'The Outlet does not exists.'
					}
				}
			},
			supplierLookUp: {
				message: 'The Supplier is not valid.',
				validators: {
					remote: {
						url: 'ctrl/validator.php',
						message: 'The Supplier does not exists.'
					}
				}
			},
			paymentTypeLookUp: {
				message: 'The Payment Type is not valid.',
				validators: {
					remote: {
						url: 'ctrl/validator.php',
						message: 'The PaymentType does not exists.'
					}
				}
			}
		}
	})
	.on('success.form.bv', function(e) {
		// Prevent form submission
		e.preventDefault();

		// Get the form instance
		var $form = $(e.target);

		// Get the BootstrapValidator instance
		var bv = $form.data('bootstrapValidator');
		bv.disableSubmitButtons(false);

		// Use Ajax to submit form data
		var form_save = $.ajax({
			type: "POST",
			url: "ctrl/purchase.php",
			data: $('#formPurchase').serialize(),
			dataType: "json"
		});
		form_save.done(function(msg) {
			if (msg == "Save Denied" || msg == "Save Failed")
			{
				$("#processBar").removeClass('alert-info').addClass('alert-danger').text(msg).show();
			}
			else
			{
				$("#processBar").removeClass('alert-info').addClass('alert-success').text(msg).show();
				purchaseTable.ajax.reload(null, true);

				//load the product data and fill the form
				var row_load = $.ajax({
					type: "GET",
					url: "ctrl/purchase.php",
					data: {id : msg},
					dataType: "json"
				});
				row_load.done(function(msg) {
					$("#processBar").removeClass('alert-info').addClass('alert-success').text('Data Load OK').show();
					$("#purchaseID").val(msg.ID);
					$("#outletID").val(msg.outletID);
					$("#purchaseDate").val(msg.Date);
					$("#purchaseNotes").val(msg.Notes);

					$("#purchaseDetail_purchaseID").val(msg.ID);

					//re-enable all form control
					$("#purchaseForm :input").prop("disabled", false);
		
					updatePurchaseDetail();
				});
				row_load.fail(function(jqXHR, textStatus) {
					$("#processBar").removeClass('alert-info').addClass('alert-danger').text(textStatus).show();
				});
			}
		});
		form_save.fail(function(jqXHR, textStatus) {
			$("#processBar").removeClass('alert-info').addClass('alert-danger').text(textStatus).show();
		});
	});

	$('#formVerifyPurchase')
	.bootstrapValidator({
		message: 'This value is not valid',
		excluded: [':disabled'],
		feedbackIcons: {
			required: 'fa fa-asterisk',
			valid: 'fa fa-check',
			invalid: 'fa fa-times',
			validating: 'fa fa-refresh'
		},
		fields: {
			verifyPurchaseID: {
				message: 'The Purchase ID is not valid.',
				validators: {
					digits: {
						message: 'The Purchase ID can only consist of numbers.'
					}
				}
			},
			verifyNotes: {
				message: 'The Verify Notes is not valid.',
				validators: {
					regexp: {
						regexp: /./mi,
						message: 'The Verify Notes is always valid.'
					}
				}
			}
		}
	})
	.on('success.form.bv', function(e) {
		// Prevent form submission
		e.preventDefault();

		// Get the form instance
		var $form = $(e.target);

		// Get the BootstrapValidator instance
		var bv = $form.data('bootstrapValidator');
		bv.disableSubmitButtons(false);

		// Use Ajax to submit form data
		var form_save = $.ajax({
			type: "POST",
			url: "con/verify.php",
			data: $('#formVerifyPurchase').serialize(),
			dataType: "json"
		});
		form_save.done(function(msg) {
			if (msg == "Save Denied" || msg == "Save Failed" || msg == "unknown error")
			{
				$("#verifyProcessBar").removeClass('alert-info').addClass('alert-danger').text(msg).show();
			}
			else
			{
				$("#verifyProcessBar").removeClass('alert-info').addClass('alert-success').text(msg).show();
				purchaseTable.ajax.reload(null, false);

				setTimeout(function(){
        			$("#verifyModal").modal('toggle');               
    			}, 100);
			}
		});
		form_save.fail(function(jqXHR, textStatus) {
			$("#verifyProcessBar").removeClass('alert-info').addClass('alert-danger').text(textStatus).show();
		});
	});

	var page_url = document.location.href;
	var is_master = page_url.search("/master/purchase.php");
	var is_finance = page_url.search("/finance/purchase.php");
	var is_audit = page_url.search("/audit/purchase.php");

	var purchaseTable = $('#purchaseList').DataTable({
		"ajax" : {
			"url" : "ctrl/purchase.php"
		},
		"deferRender": true,
		"columns": [
			{ "data": "ID", "searchable": false, "orderable": false },
			{ "data" : "outletName", "className" : "clickForDetails", "visible":false },
			{ "data": "DateDT",
				"render": {
					_: "display",
					sort: "timestamp"
				},
				"className" : "clickForDetails"
			},
			{ "data" : "Notes", "className" : "clickForDetails" },
			{ "data": "Date", "searchable": false, "orderable": false, "defaultContent": '<button class="btn btn-primary btn-sm editPurchaseButton">Ubah</button>',
				"render": function ( data, type, full, meta ) {
					var page_url = document.location.href;
					var is_retail = page_url.search("/retail/purchase.php");

					//disable edit if not current month
					var db_date = new Date(data);
					var today_date = new Date();
					if ( is_retail != -1 //is retail
						&& ( !( db_date.getMonth() == today_date.getMonth() //not current month
						&& db_date.getYear() == today_date.getYear() ) ) //not current year
					)
					{
						return 'Disabled';
					}
				}
			},
			{ "data" : null, "searchable": false, "orderable": false, "defaultContent" : '<button class="btn btn-info btn-sm printPurchaseButton">Cetak</button>' },
			{ "data" : "verified", "visible": false, "searchable": false, "orderable": true, "defaultContent" : '<button class="btn btn-success btn-sm verifiedPurchaseButton">Verified</button>',
				"render": function ( data, type, full, meta ) {
					if (data == 0)
					{
						return '<button class="btn btn-warning btn-sm quickVerifyPurchaseButton">Quick Verify</button> <button class="btn btn-warning btn-sm verifyPurchaseButton">Verify with Notes</button>';
					}
				}
			}
		],
		"order": [[ 2, "desc"]]
	});

	purchaseTable.on( 'order.dt search.dt', function () {
		purchaseTable.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
			cell.innerHTML = i+1;
		});
	}).draw();

	if (is_master != -1) //is_master
	{
		purchaseTable.column(1).visible(true);
		purchaseTable.column(6).visible(true);
	}

	if (is_finance != -1) //is_finance
	{
		purchaseTable.column(1).visible(true);
		purchaseTable.column(4).visible(false);
		purchaseTable.column(5).visible(false);
		purchaseTable.column(6).visible(true);
	}

	if (is_audit != -1) //is_audit
	{
		purchaseTable.column(1).visible(true);
		purchaseTable.column(4).visible(false);
		purchaseTable.column(5).visible(false);
		purchaseTable.column(6).visible(false);
	}

	$("#addNewPurchaseButton").click(function(){
		$('#formPurchase').bootstrapValidator('resetForm', true);
		$( "#purchaseDate" ).datepicker( "setDate", new Date() );

		updatePurchaseDetail();

		$('#processBar').removeClass('alert-info').removeClass('alert-success').removeClass('alert-danger').text('');
	});

	$('#purchaseList tbody').on( 'click', '.editPurchaseButton', function () {
		$("#purchaseModal").modal('toggle');

		var rowData = ( purchaseTable.row( $(this).parents('tr') ).data() );

		$('#formPurchase').bootstrapValidator('resetForm', true);
		$('#formPurchaseDetail').bootstrapValidator('resetForm', true);
		$('#processBar').removeClass('alert-info').removeClass('alert-success').removeClass('alert-danger').addClass('alert-info').text('Processing');

		//disable all form control until product_load is done.
		$("#formPurchase :input").prop("disabled", true);

		//load the product data and fill the form
		var row_load = $.ajax({
			type: "GET",
			url: "ctrl/purchase.php",
			data: {id : rowData['ID']},
			dataType: "json"
		});
		row_load.done(function(msg) {
			$("#processBar").removeClass('alert-info').addClass('alert-success').text('Data Load OK').show();
			$("#purchaseID").val(msg.ID);
			$("#outletID").val(msg.outletID);
			$("#supplierID").val(msg.supplierID);
			$("#paymentTypeID").val(msg.paymentTypeID);
			$("#purchaseDate").val(msg.Date);
			$("#purchaseNotes").val(msg.Notes);
			if (msg.supplierName != false)
			{
				$("#supplierLookUp").val(msg.supplierName);
			}
			if (msg.outletName != false)
			{
				$("#outletLookUp").val(msg.outletName);
			}
			if (msg.paymentTypeName != false)
			{
				$("#paymentTypeLookUp").val(msg.paymentTypeName);
			}

			$("#purchaseDetail_purchaseID").val(msg.ID);
			//re-enable all form control
			$("#formPurchase :input").prop("disabled", false);

			updatePurchaseDetail();
		});
		row_load.fail(function(jqXHR, textStatus) {
			$("#processBar").removeClass('alert-info').addClass('alert-danger').text(textStatus).show();
		});
	} );

	$('#purchaseList tbody').on( 'click', '.printPurchaseButton', function () {
		var rowData = ( purchaseTable.row( $(this).parents('tr') ).data() );

		//open new window
		window.open('retail/purchasePrint.php?purchaseID=' + rowData["ID"]);
	} );

	$('#purchaseList tbody').on( 'click', '.verifyPurchaseButton', function () {
		$("#verifyModal").modal('toggle');

		var rowData = ( purchaseTable.row( $(this).parents('tr') ).data() );

		load_purchase_data_for_verification_modal(rowData['ID']);

		$("#formVerifyPurchase").show();
		$("#formVerifiedPurchase").hide();

		$('#formVerifyPurchase').bootstrapValidator('resetForm', true);

		$("#verifyPurchaseID").val(rowData['ID']);

		$('#verifyProcessBar').removeClass('alert-info').removeClass('alert-success').removeClass('alert-danger').text('');
	});

	$('#purchaseList tbody').on( 'click', '.quickVerifyPurchaseButton', function () {
		var rowData = ( purchaseTable.row( $(this).parents('tr') ).data() );

		var form_save = $.ajax({
			type: "POST",
			url: "con/verify.php",
			data: "verifyPurchaseID=" + rowData['ID'] + "&verifyNotes=QuickVerify",
			dataType: "json"
		});
		form_save.done(function(msg) {
			if (msg == "Save Denied" || msg == "Save Failed" || msg == "unknown error")
			{
				
			}
			else
			{
				purchaseTable.ajax.reload(null, false);
			}
		});
	});

	$('#purchaseList tbody').on( 'click', '.verifiedPurchaseButton', function () {
		$("#verifyModal").modal('toggle');

		var rowData = ( purchaseTable.row( $(this).parents('tr') ).data() );

		load_purchase_data_for_verification_modal(rowData['ID']);

		$("#formVerifyPurchase").hide();
		$("#formVerifiedPurchase").show();

		//load the product data and fill the form
		var row_load = $.ajax({
			type: "GET",
			url: "con/verify.php",
			data: {purchase_id : rowData['ID']},
			dataType: "json"
		});
		row_load.done(function(msg) {
			$("#verifiedProcessBar").removeClass('alert-info').addClass('alert-success').text('Data Load OK').show();

			$("#verifiedUser").val(msg.user_id);
			$("#verifiedDate").val(msg.date);
			$("#verifiedNotes").val(msg.notes);
			//disnable all form control
			$("#formVerifiedPurchase :input").prop("disabled", true);
		});
		row_load.fail(function(jqXHR, textStatus) {
			$("#verifiedProcessBar").removeClass('alert-info').addClass('alert-danger').text(textStatus).show();
		});
	});

	function show_details(master_id)
	{
		//load the product data and fill the form
		var details_load = $.ajax({
			type: "GET",
			url: "ctrl/purchase.php",
			data: {master_id : master_id},
			dataType: "json"
		});
		details_load.done(function(msg) {
			details_row = '';
			$.each(msg.data, function(key, value){
				details_row += '<tr>' +
					'<td style="text-align:right;">' + value.Quantity + '</td>' +
					'<td style="text-align:left;">' + value.product_Name + '</td>' +
					'<td style="text-align:left;">' + value.SnStart + ' - ' + value.SnEnd + '</td>' +
				'</tr>';
			});

			if (details_row == '')
			{
				details_table = "No Data";
			}
			else
			{
				details_table = '<table width="100%" cellpadding="5" cellspacing="0" border="1" style="padding-left:50px;">' +
					'<tr>' +
						'<td width="25%" style="font-weight:bold;text-align:center">Jumlah</td>' +
						'<td width="25%" style="font-weight:bold;text-align:center">Barang</td>' +
						'<td width="50%" style="font-weight:bold;text-align:center">No. Seri</td>' +
					'</tr>' +
					details_row +
				'</table>';
			}

			$("#purchase_detail" + master_id).html(details_table);
		});

		return '<div id="purchase_detail'+ master_id +'" class="alert-info">No Data</div>';
	}

	$('#purchaseList tbody').on( 'click', '.clickForDetails', function ()
	{
		var rowData = ( purchaseTable.row( $(this).parents('tr') ).data() );

		var tr = $(this).closest('tr');
		var row = purchaseTable.row( tr );

		if ( row.child.isShown() ) {
			// This row is already open - close it
			row.child.hide();
			tr.removeClass('shown');
		}
		else {
			// Open this row
			row.child(show_details(rowData['ID'])).show();
			tr.addClass('shown');
		}
	});

	function updatePurchaseDetail()
	{
		var page_url = document.location.href;
		var is_master = page_url.search("/master/purchase.php");

		var purchaseDetailTable = $('#purchaseDetailList').DataTable({
			"ajax" : {
				"url" : "ctrl/purchase.php",
				data: { master_id : $("#purchaseID").val() } 
			},
			"columns": [
				{ "data": "ID", "searchable": false, "orderable": false },
				{ "data": "Quantity", "searchable": false, "orderable": false },
				{ "data" : "product_Name", "searchable": false, "orderable": false },
				{ "data": "Price", "visible": false, "searchable": false, "orderable": false },
				{ "data" : "SnRange", "searchable": false, "orderable": false },
				{ "data": null, "searchable": false, "orderable": false, "defaultContent": '<button class="btn btn-primary btn-sm editPurchaseDetailButton">Ubah</button>'},
			],
			"destroy": true,
			"paging": false,
			"ordering": true,
			"info": false,
			"filter": false
		});

		purchaseDetailTable.on( 'order.dt search.dt', function () {
			purchaseDetailTable.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
				cell.innerHTML = i+1;
			});
		}).draw();

		if (is_master != -1) //is_master
		{
			purchaseDetailTable.column(3).visible(true);
		}

		if ( $("#purchaseID").val() > 0 )
		{
			$("#panel_purchase_detail").show();
		}
		else
		{
			$("#panel_purchase_detail").hide();
		}
	}

	$('#formPurchaseDetail')
	.bootstrapValidator({
		message: 'This value is not valid',
		excluded: [':disabled'],
		feedbackIcons: {
			required: 'fa fa-asterisk',
			valid: 'fa fa-check',
			invalid: 'fa fa-times',
			validating: 'fa fa-refresh'
		},
		fields: {
			purchaseDetail_ID: {
				message: 'The Purchase Detail ID is not valid.',
				validators: {
					digits: {
						message: 'The Purchase Detail ID can only consist of numbers.'
					}
				}
			},
			productItem: {
				message: 'The Product Item is not valid.',
				validators: {
					digits: {
						message: 'The Product Item can only consist of numbers.'
					}
				}
			},
			purchaseDetail_Quantity: {
				message: 'The Quantity is not valid.',
				validators: {
					digits: {
						message: 'The Quantity can only consist of numbers.'
					}
				}
			},
			productLookUp: {
				message: 'The Product LookUp is not valid.',
				validators: {
					regexp: {
						regexp: /./mi,
						message: 'The Product LookUp is always valid.'
					}
				}
			},
			purchaseDetail_Price: {
				message: 'The Price is not valid.',
				validators: {
					numeric: {
						message: 'The Price can only consist of numbers.'
					}
				}
			},
			purchaseDetail_SnStart: {
				message: 'The SN Start is not valid.',
				validators: {
					regexp: {
						regexp: /./mi,
						message: 'The SN Start is always valid.'
					}
				}
			},
			purchaseDetail_SnEnd: {
				message: 'The SN End is not valid.',
				validators: {
					regexp: {
						regexp: /./mi,
						message: 'The SN End is always valid.'
					}
				}
			}
		}
	})
	.on('success.form.bv', function(e) {
		// Prevent form submission
		e.preventDefault();

		// Get the form instance
		var $form = $(e.target);

		// Get the BootstrapValidator instance
		var bv = $form.data('bootstrapValidator');
		bv.disableSubmitButtons(false);

		// Use Ajax to submit form data
		var form_save = $.ajax({
			type: "POST",
			url: "ctrl/purchase.php",
			data: $('#formPurchaseDetail').serialize(),
			dataType: "json"
		});
		form_save.done(function(msg) {
			if (msg == "Save Denied" || msg == "Save Failed")
			{
				$("#processBar").removeClass('alert-info').addClass('alert-danger').text(msg).show();
			}
			else
			{
				$("#processBar").removeClass('alert-info').addClass('alert-success').text(msg).show();
				$('#formPurchaseDetail').bootstrapValidator('resetForm', true);
				updatePurchaseDetail();
			}
		});
		form_save.fail(function(jqXHR, textStatus) {
			$("#processBar").removeClass('alert-info').addClass('alert-danger').text(textStatus).show();
		});
	});

	$('#purchaseDetailList tbody').on( 'click', '.editPurchaseDetailButton', function () {
		//get dataTable instance
		var purchaseDetailTable = $( "#purchaseDetailList" ).DataTable();
		var rowData = ( purchaseDetailTable.row( $(this).parents('tr') ).data() );

		$('#purchaseDetailForm').bootstrapValidator('resetForm', true);
		$('#processBar').removeClass('alert-info').removeClass('alert-success').removeClass('alert-danger').addClass('alert-info').text('Processing');

		//load the product data and fill the form
		var row_load = $.ajax({
			type: "GET",
			url: "ctrl/purchase.php",
			data: {detail_id : rowData['ID']},
			dataType: "json"
		});
		row_load.done(function(msg) {
			$("#processBar").removeClass('alert-info').addClass('alert-success').text('Data Load OK').show();
			$("#purchaseDetail_ID").val(msg.ID);
			$("#purchaseDetail_purchaseID").val(msg.purchase_ID);
			$("#productItem").val(msg.product_ID);
			$("#purchaseDetail_Quantity").val(msg.Quantity);
			$("#productLookUp").val(msg.product_Name);
			$("#purchaseDetail_Price").val(msg.Price);
			$("#purchaseDetail_SnStart").val(msg.SnStart);
			$("#purchaseDetail_SnEnd").val(msg.SnEnd);
		});
		row_load.fail(function(jqXHR, textStatus) {
			$("#processBar").removeClass('alert-info').addClass('alert-danger').text(textStatus).show();
		});
	} );
/*END invoice Purchase related jQuery functions*/	