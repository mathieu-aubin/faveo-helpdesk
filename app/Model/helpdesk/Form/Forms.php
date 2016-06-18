<?php

namespace App\Model\helpdesk\Form;

use App\BaseModel;

class Forms extends BaseModel
{
    protected $table = 'custom_forms';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['formname'];
}
