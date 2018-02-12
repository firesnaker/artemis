$(document).ready(function(){
	$(".with_tooltip").tooltip({container: 'body'});

	$("#top_nav li").removeClass('active');
	setTimeout(function(){
		var page_url = $(location).attr('href');
		var current_page = page_url.substring(page_url.lastIndexOf('/')+1);
		var subdirs = ['admin/', 'audit/', 'finance/', 'master/', 'mkios/', 'purchase/', 'retail/'];
		var res = "nomatch";
		$.each(subdirs, function( index, value ) {
			var patt = new RegExp(value,'g');
			match = patt.exec(page_url);
			if (match != null)
			{
				res = match;
			}
		});

		if (res != "nomatch")
		{
			current_page = res + current_page;
		}

		$('#top_nav li a[href="'+ current_page +'"] ').addClass('active');
	}); //settimeout

	/* BEGIN common */
	$.widget( "custom.catcomplete", $.ui.autocomplete, {
		_create: function() {
			this._super();
			this.widget().menu( "option", "items", "> :not(.ui-autocomplete-category)" );
		},
		_renderMenu: function( ul, items ) {
			var that = this,
			currentCategory = "";
			$.each( items, function( index, item ) {
				var li;
				if ( item.category != currentCategory ) {
					ul.append( "<li class='ui-autocomplete-category'>" + item.category + "</li>" );
					currentCategory = item.category;
				}
				li = that._renderItemData( ul, item );
				if ( item.category ) {
					li.attr( "aria-label", item.category + " : " + item.label );
				}
			});
		}
	});

	$( "#productLookUp" ).catcomplete({
		source: function(request, response){
			 $.ajax({
				url: "ctrl/product.php",
				dataType: "json",
				data: {
					lookupName: request.term
				},
				success: function( data ) {
					response( data );
				}
			});
		},
		minLength: 1,
		select: function(event, ui){
			$("#productLookUp").val(ui.item.label);
			$("#productItem").val(ui.item.value);
			return false;
		}
	});

	$( "#supplierLookUp" ).autocomplete({
		source: function(request, response){
			 $.ajax({
				url: "ctrl/supplier.php",
				dataType: "json",
				data: {
					lookupName: request.term
				},
				success: function( data ) {
					response( data );
				}
			});
		},
		minLength: 1,
		select: function(event, ui){
			$("#supplierLookUp").val(ui.item.label);
			$("#supplierID").val(ui.item.value);
			return false;
		}
	});

	$( "#outletLookUp" ).autocomplete({
		source: function(request, response){
			 $.ajax({
				url: "ctrl/outlet.php",
				dataType: "json",
				data: {
					lookupName: request.term
				},
				success: function( data ) {
					response( data );
				}
			});
		},
		minLength: 1,
		select: function(event, ui){
			$("#outletLookUp").val(ui.item.label);
			$("#outletID").val(ui.item.value);
			return false;
		}
	});

	$( "#paymentTypeLookUp" ).autocomplete({
		source: function(request, response){
			 $.ajax({
				url: "con/paymentType.php",
				dataType: "json",
				data: {
					lookupName: request.term
				},
				success: function( data ) {
					response( data );
				}
			});
		},
		minLength: 1,
		select: function(event, ui){
			$("#paymentTypeLookUp").val(ui.item.label);
			$("#paymentTypeID").val(ui.item.value);
			return false;
		}
	});
	/* END common */

/*BEGIN PRODUCT related jQuery functions*/
	$('#formProduct')
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
			productID: {
				message: 'The Product ID is not valid.',
				validators: {
					digits: {
						message: 'The Product ID can only consist of numbers.'
					}
				}
			},
			productName: {
				message: 'The Product Name is not valid.',
				validators: {
					notEmpty: {
						message: 'The Product Name is required and cannot be empty.'
					},
					regexp: {
						regexp: /^.+$/,
						message: 'The Product Name cannot have more than 1 line.'
					}
				}
			},
			productDescription: {
				message: 'The Product Description is not valid.',
				validators: {
					regexp: {
						regexp: /./mi,
						message: 'The Product Description is always valid.'
					}
				}
			},
			productCategory: {
				message: 'The Product Category is not valid.',
				validators: {
					digits: {
						message: 'The Product Category can only consist of numbers.'
					}
				}
			},
			productCategoryAutoComplete: {
				message: 'The Product Category is not valid.',
				validators: {
					remote: {
						url: 'ctrl/validator.php',
						message: 'The Product Category does not exists.'
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
		var product_save = $.ajax({
			type: "POST",
			url: "ctrl/product.php",
			data: $('#formProduct').serialize(),
			dataType: "json"
		});
		product_save.done(function(msg) {
			$("#processBar").removeClass('alert-info').addClass('alert-success').text(msg).show();
			productTable.ajax.reload();
			setTimeout(function(){
        			$("#productModal").modal('toggle');               
    			}, 100);
		});
		product_save.fail(function(jqXHR, textStatus) {
			$("#processBar").removeClass('alert-info').addClass('alert-danger').text(textStatus).show();
		});
	});

	var productTable = $('#productList').DataTable({
		"ajax" : "ctrl/product.php",
		"columns": [
			{ "data": "ID", "searchable": false, "orderable": false },
			{ "data": "Name"},
			{ "data": "categoryName", "defaultContent": "-",
				"render": function ( data, type, full, meta ) {
					if (data != false)
					{
						return data;
					}
				}
			},
			{ "data": null, "searchable": false, "orderable": false, "defaultContent": '<button class="btn btn-primary btn-sm editProductButton">Edit</button>' },
			{ "data": "Deleted", "searchable": false, "orderable": false, "defaultContent":  '<button class="btn btn-danger btn-sm deleteProductButton">Delete</button>',
				"render": function ( data, type, full, meta ) {
					if (data == 1)
					{
						return '<button class="btn btn-warning btn-sm restoreProductButton">Restore</button>';
					}
				}
			},
		],
		"order": [[ 1, "asc"]]
	});

	productTable.on( 'order.dt search.dt', function () {
		productTable.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
			cell.innerHTML = i+1;
		} );
	}).draw();

	$("#addNewProductButton").click(function(){
		$('#formProduct').bootstrapValidator('resetForm', true);
		$('#processBar').removeClass('alert-info').removeClass('alert-success').removeClass('alert-danger').text('');
	});

	$('#productList tbody').on( 'click', '.editProductButton', function () {
		$("#productModal").modal('toggle');

		var productRowData = ( productTable.row( $(this).parents('tr') ).data() );

		$('#formProduct').bootstrapValidator('resetForm', true);
		$('#processBar').removeClass('alert-info').removeClass('alert-success').removeClass('alert-danger').addClass('alert-info').text('Processing');

		//disable all form control until product_load is done.
		$("#formProduct :input").prop("disabled", true);

		//load the product data and fill the form
		var product_load = $.ajax({
			type: "GET",
			url: "ctrl/product.php",
			data: {id : productRowData.ID},
			dataType: "json"
		});
		product_load.done(function(msg) {
			$("#processBar").removeClass('alert-info').addClass('alert-success').text('Data Load OK').show();
			$("#productID").val(msg.ID);
			$("#productName").val(msg.Name);
			$("#productCategory").val(msg.productCategory_ID);
			$("#productDescription").val(msg.Description);
			if (msg.categoryName != false)
			{
				$("#productCategoryAutoComplete").val(msg.categoryName);
			}

			//re-enable all form control
			$("#formProduct :input").prop("disabled", false);
		});
		product_load.fail(function(jqXHR, textStatus) {
			$("#processBar").removeClass('alert-info').addClass('alert-danger').text(textStatus).show();
		});
	} );

	$('#productList tbody').on( 'click', '.deleteProductButton', function () {
		var productRowData = ( productTable.row( $(this).parents('tr') ).data() );
		if ( confirm("Are You Sure to delete product '"+ productRowData.Name +"' ?") )
		{
			
			var product_delete = $.ajax({
				type: "POST",
				url: "ctrl/product.php",
				data: {deleteID : productRowData.ID},
				dataType: "json"
			});
			 productTable.ajax.reload();
		}
	});

	$('#productList tbody').on( 'click', '.restoreProductButton', function () {
		var productRowData = ( productTable.row( $(this).parents('tr') ).data() );
		if ( confirm("Are You Sure to restore product '"+ productRowData.Name +"' ?") )
		{
			
			var product_delete = $.ajax({
				type: "POST",
				url: "ctrl/product.php",
				data: {restoreID : productRowData.ID},
				dataType: "json"
			});
			 productTable.ajax.reload();
		}
	});

	$( "#productCategoryAutoComplete" ).autocomplete({
		source: function(request, response){
			 $.ajax({
				url: "ctrl/product_category.php",
				dataType: "json",
				data: {
					ac_name: request.term
				},
				success: function( data ) {
					response( data );
				}
			});
		},
		minLength: 1,
		select: function(event, ui){
			$("#productCategoryAutoComplete").val(ui.item.label);
			$("#productCategory").val(ui.item.value);
			return false;
		}
	});

	//product Category
	$('#formProductCategory')
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
			productCategoryID: {
				message: 'The Product Category ID is not valid.',
				validators: {
					digits: {
						message: 'The Product ID can only consist of number.'
					}
				}
			},
			productCategoryName: {
				message: 'The Product Category Name is not valid',
				validators: {
					notEmpty: {
						message: 'The Product Category Name is required and cannot be empty.'
					},
					regexp: {
						regexp: /^.+$/,
						message: 'The Product Category Name cannot have more than 1 line.'
					}
				}
			},
			productCategoryParentID: {
				message: 'The Product Category Parent ID is not valid.',
				validators: {
					digits: {
						message: 'The Product Category Parent ID can only consist of numbers.'
					}
				}
			},
			productCategoryParentAutoComplete: {
				message: 'The Product Category Parent is not valid.',
				validators: {
					remote: {
						url: 'ctrl/validator.php',
						message: 'The Product Category Parent does not exists.'
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
		var product_save = $.ajax({
			type: "POST",
			url: "ctrl/product_category.php",
			data: $('#formProductCategory').serialize(),
			dataType: "json"
		});
		product_save.done(function(msg) {
			$("#processBar").removeClass('alert-info').addClass('alert-success').text(msg).show();
			productCategoryTable.ajax.reload();
			setTimeout(function(){
        			$("#productCategoryModal").modal('toggle');               
    			}, 100);
		});
		product_save.fail(function(jqXHR, textStatus) {
			$("#processBar").removeClass('alert-info').addClass('alert-danger').text(textStatus).show();
		});
	});

	var productCategoryTable = $('#productCategoryList').DataTable({
		"ajax" : "ctrl/product_category.php",
		"columns": [
			{ "data": "ID", "searchable": false, "orderable": false },
			{ "data": "Name"},
			{ "data": null, "searchable": false, "orderable": false, "defaultContent": '<button class="btn btn-primary btn-sm editProductCategoryButton">Edit</button>' },
			{ "data": null, "searchable": false, "orderable": false, "defaultContent":  '<button class="btn btn-danger btn-sm deleteProductCategoryButton">Delete</button>' },
		],
		"order": [[ 1, "asc"]]
	});
	productCategoryTable.on( 'order.dt search.dt', function () {
		productCategoryTable.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
			cell.innerHTML = i+1;
		} );
	}).draw();

	$("#addNewProductCategoryButton").click(function(){
		$('#formProductCategory').bootstrapValidator('resetForm', true);
		$('#processBar').removeClass('alert-info').removeClass('alert-success').removeClass('alert-danger').text('');
	});

	$('#productCategoryList tbody').on( 'click', '.editProductCategoryButton', function () {
		$("#productCategoryModal").modal('toggle');

		var productRowData = ( productCategoryTable.row( $(this).parents('tr') ).data() );

		$('#formProductCategory').bootstrapValidator('resetForm', true);
		$('#processBar').removeClass('alert-info').removeClass('alert-success').removeClass('alert-danger').addClass('alert-info').text('Processing');

		//disable all form control until product_load is done.
		$("#formProductCategory :input").prop("disabled", true);

		//load the product data and fill the form
		var product_load = $.ajax({
			type: "GET",
			url: "ctrl/product_category.php",
			data: {id : productRowData[0]},
			dataType: "json"
		});
		product_load.done(function(msg) {
			$("#processBar").removeClass('alert-info').addClass('alert-success').text('Data Load OK').show();
			$("#productCategoryID").val(msg.ID);
			$("#productCategoryParentID").val(msg.parent_ID);
			$("#productCategoryName").val(msg.Name);
			$("#productCategoryParentAutoComplete").val(msg.parent_name);

			//re-enable all form control
			$("#formProductCategory :input").prop("disabled", false);
		});
		product_load.fail(function(jqXHR, textStatus) {
			$("#processBar").removeClass('alert-info').addClass('alert-danger').text(textStatus).show();
		});
	} );

	$('#productCategoryList tbody').on( 'click', '.deleteProductCategoryButton', function () {
		var productRowData = ( productCategoryTable.row( $(this).parents('tr') ).data() );
		if ( confirm("Are You Sure to delete product category '"+ productRowData[2] +"' ?") )
		{
			
			var product_category_delete = $.ajax({
				type: "POST",
				url: "ctrl/product_category.php",
				data: {deleteID : productRowData[0]},
				dataType: "json"
			});
			productCategoryTable.ajax.reload();
		}
	});

	$( "#productCategoryParentAutoComplete" ).autocomplete({
		source: function(request, response){
			 $.ajax({
				url: "ctrl/product_category.php",
				dataType: "json",
				data: {
					acp_name: request.term
				},
				success: function( data ) {
					response( data );
				}
			});
		},
		minLength: 1,
		select: function(event, ui){
			$("#productCategoryParentAutoComplete").val(ui.item.label);
			$("#productCategoryParentID").val(ui.item.value);
			return false;
		}
	});

/*END PRODUCT related jQuery functions*/

/*BEGIN OUTLET related jQuery functions*/
	$('#outletForm')
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
			outletID: {
				message: 'The Outlet ID is not valid',
				validators: {
					regexp: {
						regexp: /^[0-9_]+$/,
						message: 'The Outlet ID can only consist of number'
					}
				}
			},
			outletCode: {
				message: 'The Outlet Code is not valid',
				validators: {
					regexp: {
						regexp: /./mi,
						message: 'The Outlet Code is always valid.'
					}
				}
			},
			outletName: {
				message: 'The Outlet Name is not valid',
				validators: {
					notEmpty: {
						message: 'The Outlet Name is required and cannot be empty'
					},
					regexp: {
						regexp: /^.+$/,
						message: 'The Outlet Name cannot have more than 1 line.'
					}
				}
			},
			outletAddress: {
				message: 'The Outlet Address is not valid',
				validators: {
					regexp: {
						regexp: /./mi,
						message: 'The Outlet Address is always valid.'
					}
				}
			},
			outletPhone: {
				message: 'The Outlet Phone is not valid',
				validators: {
					regexp: {
						regexp: /^[\d\-\(\)]+$/,
						message: 'The Outlet Phone can only consist of numbers, dash and ( or )'
					}
				}
			},
			outletFax: {
				message: 'The Outlet Fax is not valid',
				validators: {
					regexp: {
						regexp: /^[\d\-\(\)]+$/,
						message: 'The Outlet Fax can only consist of numbers, dash and ( or )'
					}
				}
			},
			outletAllowPurchase: {
				message: 'The Outlet Allow Purchase is not valid',
				validators: {
					regexp: {
						regexp: /^[\d]+$/,
						message: 'The Outlet Allow Purchase can only consist of numbers'
					}
				}
			},
			outletParentIDAutoComplete: {
				message: 'The Outlet Parent is not valid',
				validators: {
					remote: {
						url: 'ctrl/validator.php',
						message: 'The Outlet Parent does not exists'
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
		var outlet_save = $.ajax({
			type: "POST",
			url: "ctrl/outlet.php",
			data: $('#outletForm').serialize(),
			dataType: "json"
		});
		outlet_save.done(function(msg) {
			$("#processBar").removeClass('alert-info').addClass('alert-success').text(msg).show();
			outletTable.ajax.reload();
			setTimeout(function(){
        			$("#outletModal").modal('toggle');               
    			}, 1000);
		});
		outlet_save.fail(function(jqXHR, textStatus) {
			$("#processBar").removeClass('alert-info').addClass('alert-danger').text(textStatus).show();
		});
	});

	var outletTable = $('#outletList').DataTable({
		"ajax" : "ctrl/outlet.php",
		"columns": [
			{ "data": "ID", "searchable": false, "orderable": false },
			{ "data": "code"},
			{ "data": "Name"},
			{ "data": "Address"},
			{ "data": "parentName", "defaultContent":  '-',
				"render": function ( data, type, full, meta ) {
					if (data != false)
					{
						return data;
					}
				}
			},
			{ "data": null, "searchable": false, "orderable": false, "defaultContent": '<button class="btn btn-primary btn-sm editOutletButton">Edit</button>' },
			{ "data": "Deleted", "searchable": false, "orderable": false, "defaultContent":  '<button class="btn btn-danger btn-sm deleteOutletButton">Delete</button>',
				"render": function ( data, type, full, meta ) {
					if (data == 1)
					{
						return '<button class="btn btn-warning btn-sm restoreOutletButton">Restore</button>';
					}
				}
			},
		],
		"order": [[ 2, "asc"]]
	});

	outletTable.on( 'order.dt search.dt', function () {
		outletTable.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
			cell.innerHTML = i+1;
		} );
	}).draw();

	$("#addNewOutletButton").click(function(){
		$('#outletForm').bootstrapValidator('resetForm', true);
		$('#processBar').removeClass('alert-info').removeClass('alert-success').removeClass('alert-danger').text('');
	});

	$('#outletList tbody').on( 'click', '.editOutletButton', function () {
		$("#outletModal").modal('toggle');

		var outletRowData = ( outletTable.row( $(this).parents('tr') ).data() );

		$('#outletForm').bootstrapValidator('resetForm', true);
		$('#processBar').removeClass('alert-info').removeClass('alert-success').removeClass('alert-danger').addClass('alert-info').text('Processing');

		//disable all form control until product_load is done.
		$("#outletForm :input").prop("disabled", true);

		//load the product data and fill the form
		var outlet_load = $.ajax({
			type: "GET",
			url: "ctrl/outlet.php",
			data: {id : outletRowData.ID},
			dataType: "json"
		});
		outlet_load.done(function(msg) {
			$("#processBar").removeClass('alert-info').addClass('alert-success').text('Data Load OK').show();

			$("#outletID").val(msg.ID);
			$("#outletParentID").val(msg.parentID);
			$("#outletCode").val(msg.code);
			$("#outletName").val(msg.Name);
			$("#outletAddress").val(msg.Address);
			$("#outletPhone").val(msg.Phone);
			$("#outletFax").val(msg.Fax);
			if (msg.AllowPurchase == 1)
			{
				$("#outletAllowPurchase").prop('checked', true);
			}
			if (msg.parentName != false)
			{
				$("#outletParentIDAutoComplete").val(msg.parentName);
			}

			//re-enable all form control
			$("#outletForm :input").prop("disabled", false);
		});
		outlet_load.fail(function(jqXHR, textStatus) {
			$("#processBar").removeClass('alert-info').addClass('alert-danger').text(textStatus).show();
		});
	} );

	$('#outletList tbody').on( 'click', '.deleteOutletButton', function () {
		var outletRowData = ( outletTable.row( $(this).parents('tr') ).data() );
		if ( confirm("Are You Sure to delete outlet '"+ outletRowData.Name +"' ?") )
		{
			var outlet_delete = $.ajax({
				type: "POST",
				url: "ctrl/outlet.php",
				data: {deleteID : outletRowData.ID},
				dataType: "json"
			});
			 outletTable.ajax.reload();
		}
	});

	$('#outletList tbody').on( 'click', '.restoreOutletButton', function () {
		var outletRowData = ( outletTable.row( $(this).parents('tr') ).data() );
		if ( confirm("Are You Sure to restore outlet '"+ outletRowData.Name +"' ?") )
		{
			var outlet_delete = $.ajax({
				type: "POST",
				url: "ctrl/outlet.php",
				data: {restoreID : outletRowData.ID},
				dataType: "json"
			});
			 outletTable.ajax.reload();
		}
	});

	$( "#outletParentIDAutoComplete" ).autocomplete({
		source: function(request, response){
			 $.ajax({
				url: "ctrl/outlet.php",
				dataType: "json",
				data: {
					ac_name: request.term
				},
				success: function( data ) {
					response( data );
				}
			});
		},
		minLength: 1,
		select: function(event, ui){
			$("#outletParentIDAutoComplete").val(ui.item.label);
			$("#outletParentID").val(ui.item.value);

			return false;
		}
	});
/*END OUTLET related jQuery functions*/

/*BEGIN ACCOUNT_RECEIVABLE related jQuery functions*/
	date_ar = "";
	if ( document.getElementById('account_receivable_date') )
	{
		date_ar = $("#dateBegin2").val();
		$( "#dateBeginJS2" ).datepicker();
		$( "#dateBeginJS2" ).datepicker( "setDate", $("#dateBeginJS2").val() );
		$( "#dateBeginJS2" ).datepicker("option", {"autoSize": true, "dateFormat" : "d-M-yy", "defaultDate": +0, "altField": "#dateBegin2", "altFormat" : "yy-mm-dd", "maxDate" : "+0d"});
	}
	var arTable = $('#accountReceiveableMKiosList').DataTable({
		"ajax" : "ctrl/invoice.php?sub=account_receivable_mkios&date=" + date_ar,
		"columns": [
			{ "data": "ID", "searchable": false, "orderable": false },
			{ "data": "date"},
			{ "data": "subtotal"},
			{ "data": "payment"},
			{ "data": "kodesales"},
		],
		"order": [[ 1, "asc"]]
	});

	arTable.on( 'order.dt search.dt', function () {
		arTable.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
			cell.innerHTML = i+1;
		} );
	}).draw();
/*END ACCOUNT_RECEIVABLE related jQuery functions*/

/*BEGIN USER related jQuery functions*/
	$('#userForm')
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
			userID: {
				message: 'The User ID is not valid',
				validators: {
					regexp: {
						regexp: /^[0-9_]+$/,
						message: 'The User ID can only consist of number'
					}
				}
			},
			userUsername: {
				message: 'The User Username is not valid',
				validators: {
					notEmpty: {
						message: 'The User Username is required and cannot be empty'
					},
					regexp: {
						regexp: /^[\w\s\-]+$/,
						message: 'The User Username can only consist of words, space and dash'
					}
				}
			},
			userPassword: {
				message: 'The User Password is not valid',
				validators: {
					notEmpty: {
						message: 'The User Password is required and cannot be empty'
					},
					regexp: {
						regexp: /^[\w\s\-]+$/,
						message: 'The User Password can only consist of words, space and dash'
					}
				}
			},
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
		var product_save = $.ajax({
			type: "POST",
			url: "ctrl/user.php",
			data: $('#userForm').serialize(),
			dataType: "json"
		});
		product_save.done(function(msg) {
			$("#processBar").removeClass('alert-info').addClass('alert-success').text(msg).show();
			userTable.ajax.reload();
			setTimeout(function(){
        			$("#userModal").modal('toggle');               
    			}, 1000);
		});
		product_save.fail(function(jqXHR, textStatus) {
			$("#processBar").removeClass('alert-info').addClass('alert-danger').text(textStatus).show();
		});
	});

	var userTable = $('#userList').DataTable({
		"ajax" : "ctrl/user.php",
		"columns": [
			{ "data": "ID", "searchable": false, "orderable": false },
			{ "data": "Username"},
			{ "data": null, "searchable": false, "orderable": false, "defaultContent": '<button class="btn btn-primary btn-sm editUserButton">Edit</button>' },
			{ "data": null, "searchable": false, "orderable": false, "defaultContent":  '<button class="btn btn-danger btn-sm deleteUserButton">Delete</button>' },
		],
		"order": [[ 1, "asc"]]
	});

	userTable.on( 'order.dt search.dt', function () {
		userTable.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
			cell.innerHTML = i+1;
		} );
	}).draw();

	$("#addNewUserButton").click(function(){
		$('#userForm').bootstrapValidator('resetForm', true);
		$('#processBar').removeClass('alert-info').removeClass('alert-success').removeClass('alert-danger').text('');
	});

	$('#userList tbody').on( 'click', '.editUserButton', function () {
		$("#userModal").modal('toggle');

		var userRowData = ( userTable.row( $(this).parents('tr') ).data() );

		$('#userForm').bootstrapValidator('resetForm', true);
		$('#processBar').removeClass('alert-info').removeClass('alert-success').removeClass('alert-danger').addClass('alert-info').text('Processing');

		//disable all form control until product_load is done.
		$("#userForm :input").prop("disabled", true);

		//load the product data and fill the form
		var user_load = $.ajax({
			type: "GET",
			url: "ctrl/user.php",
			data: {id : userRowData[0]},
			dataType: "json"
		});
		user_load.done(function(msg) {
			$("#processBar").removeClass('alert-info').addClass('alert-success').text('Data Load OK').show();
			$("#userID").val(msg.ID);
			$("#userUsername").val(msg.Username);
			$("#userPassword").val(msg.Password);

			//re-enable all form control
			$("#userForm :input").prop("disabled", false);
		});
		user_load.fail(function(jqXHR, textStatus) {
			$("#processBar").removeClass('alert-info').addClass('alert-danger').text(textStatus).show();
		});
	} );

	$('#userList tbody').on( 'click', '.deleteUserButton', function () {
		var userRowData = ( userTable.row( $(this).parents('tr') ).data() );

		if ( confirm("Are You Sure to delete user '"+ userRowData[1] +"' ?") )
		{
			var user_delete = $.ajax({
				type: "POST",
				url: "ctrl/user.php",
				data: {deleteID : userRowData[0]},
				dataType: "json"
			});
			 userTable.ajax.reload();
		}
	});
/*END USER related jQuery functions*/

/*BEGIN SUPPLIER related jQuery functions*/
	$('#formSupplier')
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
			supplierID: {
				message: 'The Supplier ID is not valid.',
				validators: {
					digits: {
						message: 'The Supplier ID can only consist of numbers.'
					}
				}
			},
			supplierName: {
				message: 'The Supplier Name is not valid.',
				validators: {
					notEmpty: {
						message: 'The Supplier Name is required and cannot be empty.'
					},
					regexp: {
						regexp: /^.+$/,
						message: 'The Supplier Name cannot have more than 1 line.'
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
		var supplier_save = $.ajax({
			type: "POST",
			url: "ctrl/supplier.php",
			data: $('#formSupplier').serialize(),
			dataType: "json"
		});
		supplier_save.done(function(msg) {
			$("#processBar").removeClass('alert-info').addClass('alert-success').text(msg).show();
			supplierTable.ajax.reload();
			setTimeout(function(){
        			$("#supplierModal").modal('toggle');               
    			}, 100);
		});
		supplier_save.fail(function(jqXHR, textStatus) {
			$("#processBar").removeClass('alert-info').addClass('alert-danger').text(textStatus).show();
		});
	});

	var supplierTable = $('#supplierList').DataTable({
		"ajax" : "ctrl/supplier.php",
		"columns": [
			{ "data": "id", "searchable": false, "orderable": false },
			{ "data": "name"},
			{ "data": null, "searchable": false, "orderable": false, "defaultContent": '<button class="btn btn-primary btn-sm editSupplierButton">Edit</button>' },
			{ "data": "deleted", "searchable": false, "orderable": false, "defaultContent":  '<button class="btn btn-danger btn-sm deleteSupplierButton">Delete</button>',
				"render": function ( data, type, full, meta ) {
					if (data == 1)
					{
						return '<button class="btn btn-warning btn-sm restoreSupplierButton">Restore</button>';
					}
				}
			},
		],
		"order": [[ 1, "asc"]]
	});

	supplierTable.on( 'order.dt search.dt', function () {
		supplierTable.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
			cell.innerHTML = i+1;
		} );
	}).draw();

	$("#addNewSupplierButton").click(function(){
		$('#formSupplier').bootstrapValidator('resetForm', true);
		$('#processBar').removeClass('alert-info').removeClass('alert-success').removeClass('alert-danger').text('');
	});

	$('#supplierList tbody').on( 'click', '.editSupplierButton', function () {
		$("#supplierModal").modal('toggle');

		var supplierRowData = ( supplierTable.row( $(this).parents('tr') ).data() );

		$('#formSupplier').bootstrapValidator('resetForm', true);
		$('#processBar').removeClass('alert-info').removeClass('alert-success').removeClass('alert-danger').addClass('alert-info').text('Processing');

		//disable all form control until supplier_load is done.
		$("#formSupplier :input").prop("disabled", true);

		//load the supplier data and fill the form
		var supplier_load = $.ajax({
			type: "GET",
			url: "ctrl/supplier.php",
			data: {id : supplierRowData.id},
			dataType: "json"
		});
		supplier_load.done(function(msg) {
			$("#processBar").removeClass('alert-info').addClass('alert-success').text('Data Load OK').show();
			$("#supplierID").val(msg.id);
			$("#supplierName").val(msg.name);

			//re-enable all form control
			$("#formSupplier :input").prop("disabled", false);
		});
		supplier_load.fail(function(jqXHR, textStatus) {
			$("#processBar").removeClass('alert-info').addClass('alert-danger').text(textStatus).show();
		});
	} );

	$('#supplierList tbody').on( 'click', '.deleteSupplierButton', function () {
		var supplierRowData = ( supplierTable.row( $(this).parents('tr') ).data() );
		if ( confirm("Are You Sure to delete supplier '"+ supplierRowData.name +"' ?") )
		{
			
			var supplier_delete = $.ajax({
				type: "POST",
				url: "ctrl/supplier.php",
				data: {deleteID : supplierRowData.id},
				dataType: "json"
			});
			 supplierTable.ajax.reload();
		}
	});

	$('#supplierList tbody').on( 'click', '.restoreSupplierButton', function () {
		var supplierRowData = ( supplierTable.row( $(this).parents('tr') ).data() );
		if ( confirm("Are You Sure to restore supplier '"+ supplierRowData.name +"' ?") )
		{
			var supplier_delete = $.ajax({
				type: "POST",
				url: "ctrl/supplier.php",
				data: {restoreID : supplierRowData.id},
				dataType: "json"
			});
			 supplierTable.ajax.reload();
		}
	});
/*END SUPPLIER related jQuery functions*/

/* BEGIN DATEPICKER functions */
	$.fn.modal.Constructor.prototype.enforceFocus = function() {}; //for Bootstrap modal compatibility

	var all_dp = {
		dateFormat : "d-M-yy",
		defaultDate : +0,
		autosize : true,
		showAnim : "slideDown",
		minDate : new Date(2011, 1 - 1,1),
		maxDate : "+0d",
		changeMonth: true,
      changeYear: true
	};

	$( ".regular_dp" ).datepicker(all_dp);
/* END DATEPICKER functions */

}); //document.ready





//legacy, to be removed later on. Here for compatibility mode because the js lib inclusion are done after the content is loaded. While some content do load javascript that requires javascript lib. especially jqueryui
$(function() {
	//finance/salesPayment
	var oPaymentDate = new Date();
	
	$( "#paymentDateJS" ).datepicker();
	$( "#paymentDateJS" ).datepicker( "setDate", oPaymentDate );
	$( "#paymentDateJS" ).datepicker("option", {"autoSize": true, "dateFormat" : "d-M-yy", "defaultDate": +0, "altField": "#paymentDate", "altFormat" : "yy-mm-dd", "minDate" : new Date(2011, 1 - 1,1), "maxDate" : "+0d" });

	//master/profitloss, master/accountPayable, master/accountReceivable, master/inventory, master/salesCash
	//audit/accountPayable, audit/accountReceivable, audit/inventory, audit/profitloss
	var oDateBegin = new Date();
	var oDateEnd = new Date();

	$( "#dateBeginJS" ).datepicker();
	$( "#dateBeginJS" ).datepicker( "setDate", oDateBegin );
	$( "#dateBeginJS" ).datepicker("option", {"autoSize": true, "dateFormat" : "d-M-yy", "defaultDate": +0, "altField": "#dateBegin", "altFormat" : "yy-mm-dd", "minDate" : new Date(2011, 1 - 1,1), "maxDate" : "+0d" });

	$( "#dateEndJS" ).datepicker();
	$( "#dateEndJS" ).datepicker( "setDate", oDateEnd );
	$( "#dateEndJS" ).datepicker("option", {"autoSize": true, "dateFormat" : "d-M-yy", "defaultDate": +0, "altField": "#dateEnd", "altFormat" : "yy-mm-dd", "maxDate" : "+0d"});
});