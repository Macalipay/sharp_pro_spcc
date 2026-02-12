<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js" integrity="sha512-894YE6QWD5I59HgZOGReFYm4dnWc1Qt5NtvYSaNcOP+u1T9qYdvdihz0PPSiiqn/+/3e7Jo4EaG7TubfWGUrMQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.js" integrity="sha512-n/4gHW3atM3QqRcbCn6ewmpxcLAHGaDjpEBu4xZd47N0W2oQ+6q7oc3PXstrJYXcbNU1OHdQ1T7pAP+gi5Yu8g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="{{asset('/js/jquery.validate.min.js')}}" ></script>
<script src="{{asset('/plugins/moment.js')}}" ></script>
<link href="{{{ URL::asset('backend/css/modern.css') }}}" rel="stylesheet">
<link href="{{asset('/plugins/toastr/toastr.min.css')}}" rel="stylesheet">
<link href="{{asset('/css/custom.css')}}" rel="stylesheet">
<link href="{{asset('/css/custom_mobile.css')}}" rel="stylesheet">
{{-- <script src="{{{ URL::asset('backend/js/settings.js') }}}"></script> --}}
<script src="{{asset('/plugins/datatable/jquery.dataTables.min.js')}}" ></script>
<script src="{{asset('/plugins/datatable/dataTables.button.min.js')}}" ></script>
<script src="{{asset('/plugins/datatable/buttons.html5.min.js')}}" ></script>
<script src="{{asset('/plugins/datatable/pdfmake.min.js')}}" ></script>
<script src="{{asset('/plugins/datatable/vfs_fonts.js')}}" ></script>
<link href="https://fonts.googleapis.com/css2?family=Archivo+Black&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Archivo+Black&display=swap" rel="stylesheet">
<style>
    .body {

    }

    .cv-cover {
        background-image: url(images/cv-cover-bg.png);
        height: 550px;;
        background-size: cover;
    }
    .cv-img>img {
        width: 230px;
        margin: auto;
        border: 5px solid white;
    }
    .row.cv-row {
        height: 80%;
    }
    .row.cv-row-2 {
        height: 20%;
    }
    .cv-img {
        margin: auto;
        width: 210px;
    }
    .col-6.cv-col {
        margin: auto;
    }
    .cv-info>img {
        width: 90px;
        margin-bottom: 20px;
    }
    .cv-info {
        margin-left: 145px;
    }
    p.cv-info-name {
        font-size: 35px;
        font-weight: bold;
        text-transform: uppercase;
        color: white;
        font-family: 'Archivo Black';
        margin-bottom: 15px !important;
    }
    p.cv-info-position {
        text-transform: uppercase;
        color: white;
        font-family: 'Poppins';
        letter-spacing: 5px;
    }
    .icon-text {
        display: inline-flex;
    }
    .col-4.cv-col {
        text-align: center;
    }
    .icon-text>img {
        width: 50px;
        height: 50px;
    }
    .icon-text>p {
        color: white;
        font-family: 'Poppins';
        font-weight: bold;
        margin-left: 15px;
        margin-top: 10px;
        font-size: 18px;
    }
    .cv-body {
        background-image: url(images/cv-info-bg.png);
        height: 550px;
        background-size: cover;
    }
    .cv-body-info {
        margin-left: 80px;
    }
    .cv-body-info-2 {
        margin-right: 80px;
    }
    p.cv-company-info {
        font-size: 18px;
        font-weight: bold;
        font-family: 'Poppins';
        color: white;
        margin-bottom: 0px !important;

    }
    .cv-info-container {
        margin-bottom: 10px;
    }
    span.cv-year {
        background: white;
        padding: 5px;
        font-size: 12px;
        color: #151f6d;
        border-radius: 10px;
        font-weight: 900;
        margin: 0px 0px 0px 10px;
    }
    .cv-body {
        background-image: url(images/cv-info-bg.png);
        height: auto;
        background-size: cover;
        background-repeat: repeat;
        padding: 4em 0px;
        height: 1092px;
    }
    .row.cv-body-row {
        padding-top: 15px;
    }
    p.cv-company-title {
        font-size: 14px;
        color: #ffffff;
        margin-bottom: 0px !important;
    }
    
    
</style>
<body>
    <div class="container">
        <div class="" id="cvPrint">
            <div style="height: 11in; margin: auto;">
                <div class="cv-cover">
                    <div class="row cv-row">
                        <div class="col-6 cv-col">
                            <div class="cv-info">
                                <img src="/images/logo-2-white.png" alt="">
                                <p class="cv-info-name">Darisse Eligino</p>
                                <p class="cv-info-position">Marketing</p>
                            </div>
                            
                        </div>
                        <div class="col-6 cv-col">
                            <p class="cv-img">
                                <img src="/images/profile/avatar.jpg" alt="">
                            </p>
                        </div>
                    </div>
                    <div class="row cv-row-2">
                        <div class="col-4 cv-col">
                            <div class="icon-text">
                                <img src="/images/icon-call.png" alt="">
                                <p>+123-456-7890</p>
                            </div>
                        </div>
                        <div class="col-4 cv-col">
                            <div class="icon-text">
                                <img src="/images/icon-email.png" alt="">
                                <p>darrisse.eligion@gmail.com</p>
                            </div>
                        </div>
                        <div class="col-4 cv-col">
                            <div class="icon-text">
                                <img src="/images/icon-web.png" alt="">
                                <p>spintl.holdings</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="cv-body">
                    <div class="row cv-body-row">
                        <div class="col-6">
                            <div class="cv-body-info">
                                <p class="cv-info-name">Company</p>
                                <div class="cv-info-container">
                                    <p class="cv-company-info">August 08, 2024</p>
                                    <p class="cv-company-title">Date of Employment</p>
                                </div>
                                <div class="cv-info-container">
                                    <p class="cv-company-info">Marketing Assistant</p>
                                    <p class="cv-company-title">Position</p>
                                </div>
                                <div class="cv-info-container">
                                    <p class="cv-company-info">Marketing</p>
                                    <p class="cv-company-title">Department</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="cv-body-info-2">
                                <p class="cv-info-name">Education</p>
                            </div>
                            <div class="cv-info-container">
                                <p class="cv-company-info">University of Sto Tomas <span class="cv-year">YYYY-YYYY</span></p> 
                                <p class="cv-company-title">Date of Employment</p>
                            </div>
                        </div>
                    </div>
                    <div class="row cv-body-row">
                        <div class="col-6">
                            <div class="cv-body-info">
                                <p class="cv-info-name">Trainings</p>
                                <div class="cv-info-container">
                                    <p class="cv-company-info">	AWS Certified Solutions Architect<span class="cv-year">August 20, 2024</span></p> 
                                    <p class="cv-company-title">Amazon Web Services</p>
                                    <p class="cv-company-title">000000-0001A</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="cv-body-info-2">
                                <p class="cv-info-name">Certifications</p>
                            </div>
                            <div class="cv-info-container">
                                <p class="cv-company-info">	Cisco Cloud Collaboration Solutions (CCS)<span class="cv-year">August 20, 2024</span></p> 
                                <p class="cv-company-title">Cisco Network</p>
                                <p class="cv-company-title">000000-0001A</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
