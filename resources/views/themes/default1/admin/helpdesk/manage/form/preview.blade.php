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
@if(Session::has('success'))
<div class="alert alert-success alert-dismissable">
    <i class="fa fa-check-circle"></i>
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {{Session::get('success')}}
</div>
@endif
@if(Session::has('fails'))
<div class="alert alert-success alert-dismissable">
    <i class="fa fa-ban"></i><button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    <b>{!! Lang::get('lang.alert') !!} !</b> <br>
    <li class="error-message-padding">{{Session::get('fails')}}</li>
</div>
@endif
<!-- -->    
<div class="box">
    <div class="box-header">
        <?php $id = App\Model\helpdesk\Form\Forms::where('id', $id)->first(); ?>
        <h3 class="box-title">{!! Lang::get('lang.form_name') !!} : {!! $id->formname !!}</h3>
    </div>
    <div class="box-body">
        <?php
        $i = $id->id;
        $form_datas = App\Model\helpdesk\Form\Fields::where('forms_id', '=', $i)->get();
        foreach ($form_datas as $form_data) {
            if ($form_data->type == "select") {
                $form_fields = explode(',', $form_data->value);
                $var = "";
                foreach ($form_fields as $form_field) {
                    $var .= '<option value="' . $form_field . '">' . $form_field . '</option>';
                }
                echo '<label>' . ucfirst($form_data->label) . '</label><select class="form-control" name="' . $form_data->name . '">' . $var . '</select>';
            } elseif ($form_data->type == "radio") {
                $type2 = $form_data->value;
                $vals = explode(',', $type2);
                echo '<br/><label>' . ucfirst($form_data->label) . '</label><br/>';
                foreach ($vals as $val) {
                    echo '<input type="' . $form_data->type . '" name="' . $form_data->name . '"> ' . $form_data->name . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                }
            } elseif ($form_data->type == "textarea") {
                $type3 = $form_data->value;
                $v = explode(',', $type3);
                echo '<label>' . $form_data->label . '</label></br><textarea rows="' . $v[0] . '" cols="' . $v[1] . '"></textarea></br></br>';
            } elseif ($form_data->type == "checkbox") {
                $type4 = $form_data->value;
                $checks = explode(',', $type4);
                echo '<label>' . ucfirst($form_data->label) . '</label><br/>';
                foreach ($checks as $check) {
                    echo '<input type="' . $form_data->type . '" name="' . $form_data->name . '">&nbsp&nbsp' . $check;
                }
            } else {
                echo '<label>' . ucfirst($form_data->label) . '</label><input type="' . $form_data->type . '" class="form-control"   name="' . $form_data->name . '" /></br></br>';
            }
        }
        ?>             
    </div>
</div>
@stop