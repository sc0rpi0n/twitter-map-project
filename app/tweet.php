<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class tweet extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tweets';
    
    protected $fillable = [
    	'id_str',
    	'SearchLocation',
        'tweet',
    	'lat',
    	'lng',
    	'createdAt',
        'userPic'
    ];
}
