var push_type = '';
var data = null;
var _token = $('meta[name="csrf-token"]').attr('content');
var table = null;

$(function() {
    
    toastr.options.positionClass = 'toast-bottom-right';

    // Webcam.set({
    //     width: 490,
    //     height: 350,
    //     image_format: 'jpeg',
    //     jpeg_quality: 90
    // });
    
    // Webcam.attach('#my_camera');

    onScan.attachTo(document, {
        suffixKeyCodes: [13],
        reactToPaste: true,
        onScan: function(sCode, iQty) {
            $.get('/front/attendance/get_employee/' + sCode, function(response) {
                var val = response.record;
                
                if(val !== null) {

                    if(push_type !== '') {
        
                        var full_name = val.firstname + " " + val.lastname;

                        data = {
                            _token: _token,
                            employee_id: val.id,
                            type: push_type,
                            log_type: $('#log_type').val(),
                            // picture: take_snapshot()
                            picture: ''
                        }

                        $('.user-details img').attr('src', '/images/employee/' + val.profile_img);
                        
                        $('#user-fullname').text(full_name);
                        $('#user-email').text(val.email);
                        $('#user-id').text("#" + val.employee_no);
    
                        $('.scan-instruct').css('display', 'none');
                        $('.personal-info').css('display', 'block');
    
                        $.post('/front/attendance/save', data).done(function(response) {
                            setTimeout(() => {
                                $('#attendance_table').DataTable().draw();
                                clearField();
                            }, 2000);

                            toastr.info(response.message);
                        });
                    }
                }
                else {
                    toastr.error('Invalid Card');
                }
            });
        }
    });

    $('.btn-time').click(function() {
        push_type = $(this).attr('data-type');
        // take_snapshot();
        $('#timeLogModal').modal('show');
    });

    
    table = $('#attendance_table').DataTable({
        responsive: true,
        // processing: true,
        serverSide: true,
        paging: false,
        ordering: false,
        dom: '',
        buttons: [],
        ajax: {
            url: '/front/attendance/get/'+$('#log_type').val(),
            type: 'GET'
        },
        columns: [
            { data: null, title:"NAME", className: 'userRow', width: '100px', render: function(data, type, row, meta) {
                return row.employee !== null ? "<img class='profile-icon' src='/images/employee/"+row.employee.profile_img+"' width='40px'/>"+"<span class='profile-name'>"+row.employee.firstname + " " + row.employee.lastname+"</span>":"-";
            }},
            { data: null, title:"DATE", render: function(data, type, row, meta) {
                return moment(row.date).format('MMM DD, YYYY');
            }},
            { data: null, title:"TIME IN", render: function(data, type, row, meta) {
                return row.time_in !== null?moment(row.time_in).format('hh:mm A'):"-";
            }},
            { data: null, title:"BREAK OUT", render: function(data, type, row, meta) {
                return row.break_out !== null?moment( row.break_out).format('hh:mm A'):"-";
            }},
            { data: null, title:"BREAK IN", render: function(data, type, row, meta) {
                return row.break_in !== null?moment(row.break_in).format('hh:mm A'):"-";
            }},
            { data: null, title:"TIME OUT", render: function(data, type, row, meta) {
                return row.time_out !== null?moment(row.time_out).format('hh:mm A'):"-";
            }},
            { data: null, title:"OT IN", render: function(data, type, row, meta) {
                return row.ot_in !== null?moment(row.ot_in).format('hh:mm A'):"-";
            }},
            { data: null, title:"OT OUT", render: function(data, type, row, meta) {
                return row.ot_out !== null?moment(row.ot_out).format('hh:mm A'):"-";
            }}
        ]
    });

});

function clearField() {
    $('#user-fullname').text('---');
    $('#user-email').text('---');
    $('#user-id').text("#000000000");
    $('.user-details img').attr('src', '/images/profile/avatar.jpg');
    
    $('.scan-instruct').css('display', 'flex');
    $('.personal-info').css('display', 'none');

    $('#timeLogModal').modal('hide');
}

function take_snapshot() {
    var pic = '';
    Webcam.snap( function(data_uri) {
        pic = data_uri;
        // $(".image-tag").val(data_uri);
        // document.getElementById('results').innerHTML = '<img src="'+data_uri+'"/>';
    } );
    return pic;
}

function toggleFullscreen() {
    let elem = document.querySelector("body");

    if (!document.fullscreenElement) {
        elem.requestFullscreen().catch((err) => {
        alert(
            `Error attempting to enable fullscreen mode: ${err.message} (${err.name})`,
        );
        });
    } else {
        document.exitFullscreen();
    }
}

function sampleLog(sCode) {
    $.get('/front/attendance/get_employee/' + sCode, function(response) {
        var val = response.record;
        
        if(val !== null) {

            if(push_type !== '') {

                var full_name = val.firstname + " " + val.lastname;

                data = {
                    _token: _token,
                    employee_id: val.id,
                    type: push_type,
                    log_type: $('#log_type').val(),
                    // picture: take_snapshot()
                    picture: ''
                }

                $('.user-details img').attr('src', '/images/employee/' + val.profile_img);
                
                $('#user-fullname').text(full_name);
                $('#user-email').text(val.email);
                $('#user-id').text("#" + val.employee_no);

                $('.scan-instruct').css('display', 'none');
                $('.personal-info').css('display', 'block');

                $.post('/front/attendance/save', data).done(function(response) {
                    setTimeout(() => {
                        $('#attendance_table').DataTable().draw();
                        clearField();
                    }, 2000);

                    toastr.info(response.message);
                });
            }
        }
        else {
            toastr.error('Invalid Card');
        }
    });
}