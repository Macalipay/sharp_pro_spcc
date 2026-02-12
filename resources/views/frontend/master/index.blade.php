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
    {{-- <link href="{{asset('/css/custom.css')}}" rel="stylesheet"> --}}
    <link href="{{asset('/css/custom_mobile.css')}}" rel="stylesheet">
    <link href="{{asset('/css/bootstrap-tagsinput.css')}}" rel="stylesheet">
    {{-- <script src="{{{ URL::asset('backend/js/settings.js') }}}"></script> --}}
    <script src="{{asset('/plugins/datatable/jquery.dataTables.min.js')}}" ></script>
    <script src="{{asset('/plugins/datatable/dataTables.button.min.js')}}" ></script>
    <script src="{{asset('/plugins/datatable/buttons.html5.min.js')}}" ></script>
    <script src="{{asset('/plugins/datatable/pdfmake.min.js')}}" ></script>
    <script src="{{asset('/plugins/datatable/vfs_fonts.js')}}" ></script>
    <script src="{{asset('/plugins/onscan.js')}}" ></script>
    <script src="{{asset('/plugins/onscan.min.js')}}" ></script>
    <script src="https://furcan.github.io/IconPicker/dist/iconpicker-1.5.0.js"></script>
    <script src="{{asset('/plugins/bootstrap-tagsinput.min.js')}}" ></script>
    @yield('scripts')
    @yield('styles')
    @yield('styles-2')
    <script>
        function digi() {
            const monthNames = ["January", "February", "March", "April", "May", "June","July", "August", "September", "October", "November", "December"];
            var date = new Date(),
                month = monthNames[date.getMonth()],
                days = date.getDate(),
                year = date.getFullYear(),
                hour = date.getHours(),
                minute = checkTime(date.getMinutes()),
                ss = checkTime(date.getSeconds());
                

            var date_display = month + " " + pad(days, 2) + ", " + year;

            function checkTime(i) {
                if( i < 10 ) {
                    i = "0" + i;
                }
                return i;
            }

            if ( hour > 12 ) {
                hour = hour - 12;
                if ( hour == 12 ) {
                    hour = checkTime(hour);
                    document.getElementById("clock").innerHTML = date_display + " - " + hour+":"+minute+":"+ss+" AM";
                }
                else {
                    hour = checkTime(hour);
                    document.getElementById("clock").innerHTML = date_display + " - " + hour+":"+minute+":"+ss+" PM";
                }
            }
            else {
                if(hour === 0) {
                    document.getElementById("clock").innerHTML = date_display + " - " + '12' + ":"+minute+":"+ss+" AM";
                }
                else {
                    document.getElementById("clock").innerHTML = date_display + " - " + hour+":"+minute+":"+ss+" AM";
                }
            }
            var time = setTimeout(digi,1000);
        }

        function pad (str, max) {
            str = str.toString();
            return str.length < max ? pad("0" + str, max) : str;
        }
    </script>
</head>
<body onload="digi()">
    <div class="wrapper">
        <div class="main">
            <div class="row"> 
                <div class="col-12" style="height:100%; overflow-y: auto;">
                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <script src="{{ URL::asset('backend/js/app.js') }}"></script>
    <script src="{{asset('/plugins/cropimg/cropzee.js')}}" ></script>
    <script src="{{asset('/plugins/toastr/toastr.min.js')}}" ></script>
    {{-- <script src="{{asset('/js/global.js')}}" ></script> --}}
    
    @yield('chart-js')
</body>
</html>