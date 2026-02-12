@extends('backend.master.index')

@section('title', 'APPLICATION')

@section('breadcrumbs')
    <span>MAINTENANCE</span> / <span class="highlight">APPLICATION</span>
@endsection

@section('left-content')
    @include('backend.partial.component.tab_list', [
        'id'=>'company_menu', 
        'data'=>array(
            array('id'=>'apps', 'title'=>'APPS', 'active'=>true),
            array('id'=>'app_item', 'title'=>'APP ITEM', 'active'=>false),
            array('id'=>'app_type', 'title'=>'APP TYPE MAINTENANCE', 'active'=>false)
        )
    ])
@endsection

@section('content')
<div class="row" style="height:100%;">
    <div class="col-12" style="height:100%;">
        <div class="tab" style="height:100%;">
            <form class="form-record" method="post" id="companyProfile">
                <div id="apps_tab" class="form-tab">
                    <h4 class="form-title">APPLICATIONS</h4>
                    <div class="row">
                        <div class="col-12">
                            <table id="app_table" class="table table-striped" style="width:100%"> </table>
                        </div>
                    </div>
                </div>
                <div id="app_item_tab" class="form-tab">
                    <h4 class="form-title">APPLICATION ITEM</h4>
                    <div class="row">
                        <div class="col-12">
                            <table id="app_item_table" class="table table-striped" style="width:100%"> </table>
                        </div>
                    </div>
                </div>
                <div id="app_type_tab" class="form-tab">
                    <h4 class="form-title">ITEM TYPE</h4>
                    <div class="row">
                        <div class="col-12">
                            <div class="col-12">
                                <table id="item_type_table" class="table table-striped" style="width:100%"> </table>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
            
        </div>
    </div>
</div>
<div id="application_modal" class="content-modal">
    <form method="post" id="classForm" class="form-record">
        <div class="row">
            <div class="form-group col-md-12 app_name">
                <label>APP NAME</label>
                <input type="text" class="form-control" id="app_name" name="app_name" placeholder="APP NAME"/>
            </div>
            <div class="form-group col-md-12 app_order">
                <label>APP ORDER</label>
                <input type="text" class="form-control" id="app_order" name="app_order" placeholder="APP ORDER"/>
            </div>
            <div class="form-group col-md-12 app_type">
                <label>TYPE</label>
                <input type="text" class="form-control" id="app_type" name="app_type" placeholder="APP TYPE"/>
            </div>
            <div class="form-group col-md-12 app_category">
                <label>CATEGORY</label>
                <input type="text" class="form-control" id="app_category" name="app_category" placeholder="APP CATEGORY"/>
            </div>
            <div class="form-group col-md-12 app_icon">
                <label>CATEGORY</label>
                <input type="text" class="form-control" id="app_category" name="app_category" placeholder="APP CATEGORY"/>
            </div>
            <div class="form-group col-md-12 app_icon">
                <label>LINK</label>
                <input type="text" class="form-control" id="link" name="link" placeholder="LINK"/>
            </div>
            <div class="form-group col-md-12">
                <label class="form-check">
                    <input class="form-check-input" id="other_company_benefits" name="other_company_benefits" value="0" type="checkbox" onchange="$('#'+this.id).val($('#'+this.id).prop('checked') === true?'1':'0');">
                    <span class="form-check-label">
                    BLOCKED
                    </span>
                </label>
            </div>
        </div>
    </form>
</div>
@endsection

@section('scripts')
    <script src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script>
        $(function() {
            modal_content = 'application_modal';
            module_url = '/settings/apps';
            module_type = 'settings';
            tab_active = 'apps';

            scion.centralized_button(false, true, true, true);
            scion.action.tab(tab_active);

            apps(module_url);

            $('#apps').click(function(){
                $('#app_item_table').DataTable().destroy();
                apps(module_url);
            })

            $('#app_item').click(function(){
                $('#app_table').DataTable().destroy();
                app_item('/settings/app_items');
            })

            $('#app_type').click(function(){
                app_type();
            })
        });

        function apps(module_url) {
            scion.create.table(
                'app_table',  
                module_url + '/get', 
                [
                    { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                        var html = "";
                        html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                        html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/settings/apps/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                        return html;
                    }},
                    { data: "DT_RowIndex", title:"#" },
                    { data: "app_name", title: "Name" },
                    { data: "app_order", title: "Order" },
                    { data: "app_category", title: "Category" },
                    { data: "app_icon", title: "Icon" },
                    { data: "link", title: "Link" },
                    { data: "status", title: "Status" }
                ]
            );
        }

        function app_item(module_url) {
            scion.create.table(
                'app_item_table',  
                module_url + '/get', 
                [
                    { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                        var html = "";
                        html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                        html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/settings/app_items/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                        return html;
                    }},
                    { data: "DT_RowIndex", title:"#" },
                    { data: "item_name", title: "Name" },
                ]
            );
        }

        function apps() {
            
        }

        function app_type() {
            scion.create.table(
                'app_table',  
                module_url + '/get', 
                [
                    { data: "id", title:"<input type='checkbox' class='multi-checkbox' onclick='scion.table.checkAll()'/>", render: function(data, type, row, meta) {
                        var html = "";
                        html += '<input type="checkbox" class="single-checkbox" value="'+row.id+'" onclick="scion.table.checkOne()"/>';
                        html += '<a href="#" class="align-middle edit" onclick="scion.record.edit('+"'/settings/apps/edit/', "+ row.id +')"><i class="fas fa-pen"></i></a>';
                        return html;
                    }},
                    { data: "DT_RowIndex", title:"#" },
                    { data: "app_name", title: "Name" },
                    { data: "app_order", title: "Order" },
                    { data: "app_category", title: "Category" },
                    { data: "app_icon", title: "Icon" },
                    { data: "link", title: "Link" },
                    { data: "status", title: "Status" }
                ]
            );
        }

        function success() {
            switch(actions) {
                case 'save':
                    $('#app_table').DataTable().draw();
                    break;
                case 'update':
                    break;
            }
        }

        function error() {}

        function delete_success() {
            $('#app_table').DataTable().draw();
        }

        function delete_error() {}
        
        function generateData() {
            form_data = {
                _token: _token,
                description: $('#description').val(),
                tax_applicable: $('#tax_applicable').val(),
                government_mandated_benefits: $('#government_mandated_benefits').val(),
                other_company_benefits: $('#other_company_benefits').val()
            };

            return form_data;
        }
        
    </script>
@endsection