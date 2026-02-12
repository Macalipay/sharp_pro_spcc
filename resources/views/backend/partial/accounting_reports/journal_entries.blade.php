<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.js" integrity="sha512-n/4gHW3atM3QqRcbCn6ewmpxcLAHGaDjpEBu4xZd47N0W2oQ+6q7oc3PXstrJYXcbNU1OHdQ1T7pAP+gi5Yu8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    
    <script src="{{asset('/js/jquery.validate.min.js')}}" ></script>
    <script src="{{asset('/plugins/moment.js')}}" ></script>
    <link href="{{{ URL::asset('backend/css/modern.css') }}}" rel="stylesheet">
    <link href="{{asset('/plugins/toastr/toastr.min.css')}}" rel="stylesheet">
    <link href="{{asset('/css/custom/accounting_reports/journal_entries.css')}}" rel="stylesheet">
    <link href="{{asset('/css/custom.css')}}" rel="stylesheet">
    <link href="{{asset('/css/custom_mobile.css')}}" rel="stylesheet">
    {{-- <script src="{{{ URL::asset('backend/js/settings.js') }}}"></script> --}}
    <script src="{{asset('/plugins/datatable/jquery.dataTables.min.js')}}" ></script>
    <script src="{{asset('/plugins/datatable/dataTables.button.min.js')}}" ></script>
    <script src="{{asset('/plugins/datatable/buttons.html5.min.js')}}" ></script>
    <script src="{{asset('/plugins/datatable/pdfmake.min.js')}}" ></script>
    <script src="{{asset('/plugins/datatable/vfs_fonts.js')}}" ></script>

    <style>
    
    </style>
</head>
<body>
    <div class="container">
        <div class="spacer"></div>
        <div class="row">
            <div class="col-4" style="text-align: left;">
                <img src="/images/logo-dark.png" style="width: 70px;" alt="">
            </div>
            <div class="col-4" style="text-align: center;">
                <p class="mb-0 text-title">Journal Report</p>
                <p class="mb-0">SP Construction Corporation</p>

            </div>
            <div class="col-4" style="text-align: right;">
                <p class="mb-0">From 09/14/24</p>
                <p class="mb-0">To 09/14/24</p>
            </div>
        </div>
        <div class="spacer"></div>
        <div class="row">
            <div class="col-12">
            <table>
                <tr>
                    <th>Account</th>
                    <th>Debit</th>
                    <th>Credit</th>
                </tr>
                <tr>
                    <td style="width:70%">Cash</td>
                    <td>0.00</td>
                    <td>1,500.00</td>
                </tr>
                <tr>
                    <td style="width:70%">Transportation</td>
                    <td>2,000.00</td>
                    <td>0.00</td>
                </tr>
                <tr>
                    <td style="width:70%">Travel Expenses</td>
                    <td>1,400.00</td>
                    <td>0.00</td>
                </tr>
                <tr>
                    <td style="width:70%">Utilities Expenses</td>
                    <td>5,000.00</td>
                    <td>0.00</td>
                </tr>
            </table>
            </div>
        </div>
        <div class="row">
            <div class="col-12">
            <table>
                <tr>
                    <th>Account</th>
                    <th>Debit</th>
                    <th>Credit</th>
                </tr>
                <tr>
                    <td style="width:70%">Cash</td>
                    <td>0.00</td>
                    <td>1,500.00</td>
                </tr>
                <tr>
                    <td style="width:70%">Transportation</td>
                    <td>2,000.00</td>
                    <td>0.00</td>
                </tr>
                <tr>
                    <td style="width:70%">Travel Expenses</td>
                    <td>1,400.00</td>
                    <td>0.00</td>
                </tr>
                <tr>
                    <td style="width:70%">Utilities Expenses</td>
                    <td>5,000.00</td>
                    <td>0.00</td>
                </tr>
            </table>
            </div>
        </div>
    </div>
</body>
<script src="{{ URL::asset('backend/js/app.js') }}"></script>

<script src="{{asset('/plugins/cropimg/cropzee.js')}}" ></script>
<script src="{{asset('/plugins/toastr/toastr.min.js')}}" ></script>
<script src="{{asset('/js/global.js')}}" ></script>
</html>