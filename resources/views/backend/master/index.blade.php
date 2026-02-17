<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Business Solutions</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.js" integrity="sha512-n/4gHW3atM3QqRcbCn6ewmpxcLAHGaDjpEBu4xZd47N0W2oQ+6q7oc3PXstrJYXcbNU1OHdQ1T7pAP+gi5Yu8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

    <link rel="icon" type="image/png" href="/images/logo.png">
    <script src="{{asset('/js/jquery.validate.min.js')}}" ></script>
    <script src="{{asset('/plugins/moment.js')}}" ></script>
    <link href="{{{ URL::asset('backend/css/modern.css') }}}" rel="stylesheet">
    <link href="{{asset('/plugins/toastr/toastr.min.css')}}" rel="stylesheet">
    <link href="{{asset('/css/custom.css')}}" rel="stylesheet">
    <link href="{{asset('/css/custom/new_design.css')}}" rel="stylesheet">
    <link href="{{asset('/css/custom_mobile.css')}}" rel="stylesheet">
    {{-- <script src="{{{ URL::asset('backend/js/settings.js') }}}"></script> --}}
    <script src="{{asset('/plugins/datatable/jquery.dataTables.min.js')}}" ></script>
    <script src="{{asset('/plugins/datatable/dataTables.button.min.js')}}" ></script>
    <script src="{{asset('/plugins/datatable/buttons.html5.min.js')}}" ></script>
    <script src="{{asset('/plugins/datatable/pdfmake.min.js')}}" ></script>
    <script src="{{asset('/plugins/datatable/vfs_fonts.js')}}" ></script>
    
    @yield('scripts')
    @yield('styles')
    @yield('styles-2')
    <style>
    .alert {
        padding: 10px;
    }
    .centralized>button {
        padding: 5px 10px;
        border: 0px;
        font-size: 20px;
        color: #153d77;
        background: #fff;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        margin: 5px;
    }
    .alert {
        margin: 10px;
        width: auto !important;
    }
    .alert p {
        margin: 0px;
    }
    label.error {
        width: 100%;
        padding: 5px 10px;
        background: #ff9f9f;
        color: #fff;
    }
    /* Ensure text wrap and tooltip styling for DataTable cells */
    table.dataTable td {
        max-width: 100px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    table.dataTable td span {
        display: block;
    }

    table.dataTable td span[title]:hover::after {
        content: attr(title); /* Tooltip content from the title attribute */
        position: absolute;
        transform: translateY(-50%);
        left: 100%; /* Position the tooltip */
        top: 50%;
        background-color: #333;
        color: #fff;
        padding: 5px;
        border-radius: 3px;
        white-space: nowrap;
        z-index: 1;
        margin-left: 10px; /* Space between the cell and the tooltip */
        pointer-events: none;
        opacity: 0.75;
    }

    table.dataTable td span.expandable.expanded {
        white-space: normal; /* Allows full content display */
        background-color: #f9f9f9;
        border: 1px solid #ccc;
        padding: 5px;
        z-index: 10;
    }
    </style>
    <script src="https://js.pusher.com/7.1/pusher.min.js"></script>

    <script>

        // Pusher.logToConsole = true;

        var pusher = new Pusher('4fa242eb99abc2b85cce', {
            cluster: 'ap1'
        });

        var channel = pusher.subscribe('my-channel');
            channel.bind('form-submitted', function(data) {
            alert(JSON.stringify(data));
        });

        $(document).on('click', 'span.expandable', function() {
            $(this).toggleClass('expanded');
            if ($(this).hasClass('expanded')) {
                $(this).removeAttr('title');
            } else {
                $(this).attr('title', $(this).text());
            }
        });

        $('body').delegate('form input[type="text"], form textarea', 'keyup', function(event) {
            // Check if text is selected (e.g., when "Ctrl + A" is pressed or when the user selects text manually)
            if (this.selectionStart !== this.selectionEnd) {
                var start = this.selectionStart;
                var end = this.selectionEnd;

                // Convert the selected text to uppercase
                var selectedText = this.value.substring(start, end).toUpperCase();

                // Replace the selected text with its uppercase version
                this.value = this.value.substring(0, start) + selectedText + this.value.substring(end);

                // Restore the selection
                this.setSelectionRange(start, end);
            } else {
                // If no text is selected, proceed as normal
                var cursorPosition = this.selectionStart;
                this.value = this.value.toUpperCase();
                this.setSelectionRange(cursorPosition, cursorPosition);
            }
        }).delegate('form:not(.not)', 'submit', function(event) {
            event.preventDefault();
        });

      

    </script>

</head>
<body>
    <div class="wrapper">
        @include('backend.partial.sidebar')
        <div class="main">
            @include('backend.partial.header')
            <div class="custom-modal">
                <div class="custom-modal-container">
                    <div class="custom-modal-header">
                        <h3 class="custom-modal-title">MODAL TITLE</h3>
                        <span class="custom-modal-close" onclick="scion.create.modal().hide('all')"><i class="fas fa-times"></i></span>
                    </div>
                    <div class="custom-modal-body"></div>
                </div>
            </div>
            <div class="sc-modal">
                @yield('sc-modal')
            </div>
            <div class="row" style="height:calc(100% - 135px);padding: 0 30px;">
                @if(isset($type))
                    @if($type === "full-view")
                    <div class="col-xl-12" style="height:100%;">
                        @yield('content')
                    </div>
                    @else
                    <div class="col-xl-2" style="height:100%; overflow-y: auto;">
                        @yield('left-content')
                    </div>
                    <div class="col-xl-10" style="height:100%; overflow-y: auto;">
                        @yield('content')
                    </div>
                    @endif
                @else
                    <div class="col-xl-2 left-content" style="height:100%;">
                        <div class="container-fluid" style="height:100%; overflow-y: auto; overflow-x: hidden;">
                            @yield('left-content')
                        </div>
                    </div>
                    <main class="col-xl-8 content" style="height:100%;">
                        <div class="container-fluid" style="height:100%; overflow-y: auto; overflow-x: hidden;">
                            @yield('content')
                        </div>
                    </main>
                    <div class="col-xl-2 right-content" style="height:100%; overflow-x: hidden;">
                        <div class="container-fluid" style="height:100%">
                            @yield('right-content')
                        </div>
                    </div>
                @endif
            </div>
            @include('backend.partial.footer')
        </div>

    </div>


    <div class="modal fade" id="deleteMessage" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Delete Record</h5>
            </div>
            <div class="modal-body">
                Are you sure you want to delete this data?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                <button type="button" class="btn btn-primary" onclick="delete_func.yes()">Yes</button>
            </div>
        </div>
        </div>
    </div>


	<div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Change Password</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body m-3">
					<form method="POST" action="{{ route('change.password') }}" class="not">
						@csrf

						@foreach ($errors->all() as $error)
							<p class="text-danger">{{ $error }}</p>
						@endforeach

						<div class="form-group row">
							<label for="password" class="col-md-4 col-form-label text-md-right">Current Password</label>

							<div class="col-md-6">
								<div class="input-group">
									<input id="password" type="password" class="form-control" name="current_password" autocomplete="current-password">
									<div class="input-group-append">
										<button class="btn btn-outline-dark password-toggle" type="button" data-target="password" aria-label="Show current password">
											<i class="far fa-eye"></i>
										</button>
									</div>
								</div>
							</div>
						</div>

						<div class="form-group row">
							<label for="new_password" class="col-md-4 col-form-label text-md-right">New Password</label>

							<div class="col-md-6">
								<div class="input-group">
									<input id="new_password" type="password" class="form-control" name="new_password" autocomplete="current-password">
									<div class="input-group-append">
										<button class="btn btn-outline-dark password-toggle" type="button" data-target="new_password" aria-label="Show new password">
											<i class="far fa-eye"></i>
										</button>
									</div>
								</div>
							</div>
						</div>
						<div class="form-group row">
							<div class="col-md-6 offset-md-4">
								<small class="d-block mb-1 text-muted">Password Strength: <span id="password_strength_text">Weak</span></small>
								<div class="progress mb-2" style="height: 6px;">
									<div id="password_strength_bar" class="progress-bar bg-danger" role="progressbar" style="width: 0%;" aria-valuemin="0" aria-valuemax="100"></div>
								</div>
								<small id="password_rule_uppercase" class="d-block text-danger"><i class="fas fa-times mr-1"></i>Must contain uppercase</small>
								<small id="password_rule_number" class="d-block text-danger"><i class="fas fa-times mr-1"></i>Must contain number</small>
								<small id="password_rule_symbol" class="d-block text-danger"><i class="fas fa-times mr-1"></i>Must contain symbol</small>
								<small id="password_rule_length" class="d-block text-danger"><i class="fas fa-times mr-1"></i>Minimum 8-12 characters</small>
							</div>
						</div>

						<div class="form-group row">
							<label for="new_confirm_password" class="col-md-4 col-form-label text-md-right">New Confirm Password</label>

							<div class="col-md-6">
								<div class="input-group">
									<input id="new_confirm_password" type="password" class="form-control" name="new_confirm_password" autocomplete="current-password">
									<div class="input-group-append">
										<button class="btn btn-outline-dark password-toggle" type="button" data-target="new_confirm_password" aria-label="Show confirm password">
											<i class="far fa-eye"></i>
										</button>
									</div>
								</div>
							</div>
						</div>

						<div class="form-group row mb-0">
							<div class="col-md-8 offset-md-4">
								<button type="submit" class="btn btn-primary not">
									Update Password
								</button>
							</div>
						</div>
					</form>

				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="changePhotoModal" tabindex="-1" role="dialog" aria-hidden="true">
		<div class="modal-dialog" role="document">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Change Profile Picture</h5>
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body m-3">
					<form method="POST" action="{{ route('change.picture') }}" enctype="multipart/form-data" class="not">
						@csrf
						<div class="form-group row">
							<input type="hidden" name="user_id" id="user_id" value="{{Auth::user()->id}}"/>
							<label class="col-form-label col-sm-3 text-sm-right">Profile Picture</label>
							<div class="col-sm-9">
								<input type="file" name="picture" class="form-control">
							</div>
						</div>

						<div class="form-group row mb-0">
							<div class="col-md-8 offset-md-4">
								<button type="submit" class="btn btn-primary ">
									Upload
								</button>
							</div>
						</div>
					</form>

				</div>
			</div>
		</div>
	</div>

    <script src="{{ URL::asset('backend/js/app.js') }}"></script>
    <script src="/js/scion_formatter.js"></script>
    <script src="{{asset('/plugins/cropimg/cropzee.js')}}" ></script>
    <script src="{{asset('/plugins/toastr/toastr.min.js')}}" ></script>
    <script src="{{asset('/js/global.js')}}" ></script>
    <script>
		$(function() {
			// Datatables basic
			$('#datatables-basic').DataTable({
				responsive: true
			});
			// Datatables with Buttons
			var datatablesButtons = $('#datatables-buttons').DataTable({
				lengthChange: !1,
				buttons: ["copy", "print"],
				responsive: true
			});

			datatablesButtons.buttons().container().appendTo("#datatables-buttons_wrapper .col-md-6:eq(0)");

			$('.password-toggle').on('click', function() {
				var targetId = $(this).data('target');
				var input = $('#' + targetId);
				var icon = $(this).find('i');
				var isPassword = input.attr('type') === 'password';

				input.attr('type', isPassword ? 'text' : 'password');
				icon.toggleClass('fa-eye fa-eye-slash');
			});

			function evaluatePassword(password) {
				var result = {
					uppercase: /[A-Z]/.test(password),
					number: /[0-9]/.test(password),
					symbol: /[^A-Za-z0-9]/.test(password),
					length: password.length >= 8 && password.length <= 12
				};

				result.score = (result.uppercase ? 1 : 0)
					+ (result.number ? 1 : 0)
					+ (result.symbol ? 1 : 0)
					+ (result.length ? 1 : 0);
				result.valid = result.score === 4;

				return result;
			}

			function setRuleState(selector, isValid) {
				var rule = $(selector);
				var icon = rule.find('i');

				rule.toggleClass('text-success', isValid).toggleClass('text-danger', !isValid);
				icon.toggleClass('fa-check', isValid).toggleClass('fa-times', !isValid);
			}

			function updateStrengthUI() {
				var password = $('#new_password').val() || '';
				var result = evaluatePassword(password);
				var percent = (result.score / 4) * 100;
				var strengthLabel = 'Weak';
				var strengthClass = 'bg-danger';

				if (result.score === 2) {
					strengthLabel = 'Fair';
					strengthClass = 'bg-warning';
				} else if (result.score === 3) {
					strengthLabel = 'Good';
					strengthClass = 'bg-info';
				} else if (result.score === 4) {
					strengthLabel = 'Strong';
					strengthClass = 'bg-success';
				}

				$('#password_strength_text').text(strengthLabel);
				$('#password_strength_bar')
					.css('width', percent + '%')
					.attr('aria-valuenow', percent)
					.removeClass('bg-danger bg-warning bg-info bg-success')
					.addClass(strengthClass);

				setRuleState('#password_rule_uppercase', result.uppercase);
				setRuleState('#password_rule_number', result.number);
				setRuleState('#password_rule_symbol', result.symbol);
				setRuleState('#password_rule_length', result.length);

				return result.valid;
			}

			$('#new_password').on('input', updateStrengthUI);
			$('#changePasswordModal form').on('submit', function(e) {
				if (!updateStrengthUI()) {
					e.preventDefault();
					toastr.error('New password does not meet policy requirements.');
				}
			});

			updateStrengthUI();

			@if($errors->has('current_password') || $errors->has('new_password') || $errors->has('new_confirm_password'))
				$('#changePasswordModal').modal('show');
			@endif

		});

	</script>

    @yield('chart-js')
</body>
</html>
