@extends('themes.default1.admin.layout.admin')

@section('Manage')
active
@stop

@section('manage-bar')
active
@stop

@section('forms')
class="active"
@stop

@section('HeadInclude')
@stop
<!-- header -->
@section('PageHeader')
<h1>{!! Lang::get('lang.forms') !!}</h1>
@stop
<!-- /header -->
<!-- breadcrumbs -->
@section('breadcrumbs')
<ol class="breadcrumb">
</ol>
@stop
<!-- /breadcrumbs -->
<!-- content -->
@section('content')
<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title">{!! Lang::get('lang.create') !!}</h3>
    </div>
    <div class="box-header with-border">
        <h3 class="box-title">{!! Lang::get('lang.instructions') !!}</h3>
        <div class="callout callout-default" style="font-style: oblique;">{!! Lang::get('lang.instructions_on_creating_form') !!}.</div>
    </div>
    <div class="box-body with-border">
        @if(Session::has('success'))
        <div class="alert alert-success alert-dismissable">
            <i class="fa fa-check-circle"></i>
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            {{Session::get('success')}}       
        </div>
        @endif
        @if(Session::has('fails'))
        <div class="alert alert-danger alert-dismissable">
            <i class="fa fa-ban"></i>
            <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
            <b>{!! Lang::get('lang.alert') !!}!</b><br>

            <li class="error-message-padding" >{{Session::get('fails')}}</li>
        </div>
        @endif
        <h3 class="box-title">{!! Lang::get('lang.form_properties') !!}</h3>
        {!! Form::open(['route'=>'forms.store']) !!}
        <div class="form-group">
            <div class="row">
                <div class="col-md-4">    
                </div>  
            </div>
        </div>
        <div class="form-group">
            <div class="row" style="margin-top: 10px;">
                <div class="col-md-4">
                    <h4 style="text-align: center">{!! Lang::get('lang.form_name') !!}: <span class="text-red"> *</span></h4>
                </div>
                <div class="col-md-4">
                    <input type="text" name="formname" class="form-control">
                </div>
            </div>
        </div>
        <h3 class="box-title">{!! Lang::get('lang.adding_fields') !!}</h3>
        <div class="callout callout-default col-md-4"> {!! Lang::get('lang.click_add_fields_button_to_add_fields') !!} </div>
        <div class="col-md-4"> 
            <button type="button" class="btn btn-primary addField" value="Show Div" onclick="showDiv()" ><i class="fa fa-plus"></i>&nbsp; {!! Lang::get('lang.add_fields') !!}</button>
        </div>
        <div class="row">
        </div>
        <div class="box-body" id="welcomeDiv"  style="display:none;">
            <table id="example2" class="table table-bordered table-striped">
                <thead>
                <th>{!! Lang::get('lang.label') !!} </th>
                <th>{!! Lang::get('lang.name') !!} </th>
                <th>{!! Lang::get('lang.type') !!} </th>
                <th>{!! Lang::get('lang.values(selected_fields)') !!} </th>
                <th>{!! Lang::get('lang.required') !!} </th>
                <th>{!! Lang::get('lang.action') !!} </th>
                </thead>
                <tbody class="inputField">
                    <tr></tr>  
                </tbody>
            </table>
        </div>  
    </div>
    <div class="box-footer">
        <input type="submit" class="btn btn-primary" value="{!! Lang::get('lang.save_form') !!}">
    </div>
</div>
{!! Form::close() !!}
<script>
    function showDiv() {
        document.getElementById('welcomeDiv').style.display = "block";
    }
    $(document).ready(function() {
        var max_fields = 10;
        var wrapper = $(".inputField");
        var add_button = $(".addField");
        var x = 1;
        $(add_button).click(function(e)
        {
            e.preventDefault();
            if (x < max_fields) {
                x++;
                $(wrapper).append('<tr><td><input type="text" class="form-control" name="label[]"></td><td><input type="text" class="form-control" name="name[]"></td><td><select name="type[]" class="form-control"><option>text</option><option>email</option><option>password</option><option>textarea</option><option>select</option><option>radio</option><option>checkbox</option><option>submit</option></select></td><td><textarea name="value[]" class="form-control"></textarea></td><td>{!! Lang::get("lang.yes") !!}&nbsp;&nbsp;<input type=radio name="required[' + x + '][]" value=1 checked>&nbsp;&nbsp;{!! Lang::get("lang.no") !!}&nbsp;&nbsp;<input type=radio name="required[' + x + '][]" value=0></td><td><button type="button" class="remove_field btn btn-danger"><i class="fa fa-trash-o"></i>&nbsp {!! Lang::get("lang.remove") !!}</button></td></tr>');
            }
        });
        $(wrapper).on("click", ".remove_field", function(e)
        {
            e.preventDefault();
            $(this).closest('tr').remove();
            x--;
        });
    });
</script>
@stop

