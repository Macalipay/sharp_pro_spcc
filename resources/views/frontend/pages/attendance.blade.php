
@extends('frontend.master.index')

@section('content')
<input type="hidden" id="log_type" value="{{$type}}">
<div class="main" style="background: #fff;padding: 40px;">
    <div class="row" style="height: 100%;">
        <div class="col-6">
            <img src="/images/logo-dark.png" height="65px" alt="coffee-blanc" onclick="toggleFullscreen()"/>
        </div>
        <div class="col-6 text-right">
            <p style="margin-bottom: 0px;" class="digital-clock" id="clock"></p>
        </div>
    </div>
    <div class="row">
        <div class="col-12 mb-2 mt-3">
            <h1 class="text-center">ATTENDANCE MANAGEMENT SYSTEM</h1>
        </div>
        <div class="col-9">
            <table id="attendance_table" class="table table-striped" style="width:100%"></table>
        </div>
        
        <div class="col-3">
            <button class="btn-time" data-type="time_in">TIME IN</button>
            <button class="btn-time" data-type="break_out">BREAK OUT</button>
            <button class="btn-time" data-type="break_in">BREAK IN</button>
            <button class="btn-time" data-type="time_out">TIME OUT</button>
            <div class="row">
                <div class="col-6">
                    <button class="btn-time btn-ot" data-type="ot_in">OT IN</button>
                </div>
                <div class="col-6">
                    <button class="btn-time btn-ot" data-type="ot_out">OT OUT</button>
                </div>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="timeLogModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle">ATTENDANCE MANAGEMENT SYSTEM</h5>
                <button type="button" class="close" onclick="clearField()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-8">
                        <div id="my_camera"></div>
                        <input type="hidden" class='form-control image-tag'>
                    </div>
                    <div class="col-4">
                        <div class="user-details">
                            <div class="scan-instruct text-center">
                                <span>--- TAP YOUR CARD ---</span>
                            </div>
                            <div class="row personal-info">
                                <div class="col-12">
                                    <img src="/images/profile/avatar.jpg" alt="user-profile-picture" width="100%">
                                </div>
                                <div class="col-12">
                                    <h2 id="user-fullname">---</h2>
                                    <span id="user-id">#000000000</span>
                                    <span id="user-email">---</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="/js/frontend/pages/attendance.js"></script>
<script src="/plugins/webcam.min.js"></script>
@endsection

@section('styles')
<link href="{{asset('/css/custom/attendance.css')}}" rel="stylesheet">
@endsection