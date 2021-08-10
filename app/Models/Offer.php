<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Offer extends Model
{
    use HasFactory;

    protected $table = "offers";
    protected $fillable = [
        'price',
        'product',
        'image_url',
        'link',
        'source',
        'store',
        'active',
        'published',
        'review',
        'alerted_sound',
        'link_updated'
    ];
//    public function setPriceAttribute($value){
//        $this->attributes['price'] = ($value*100);
//    }

    public function getCreatedAtAttribute($value)
    {

        $hour =  Carbon::createFromTimestamp(strtotime($value))
            ->timezone('America/Sao_Paulo')
            ->toDateTimeString();

        return Carbon::parse($hour)->format('d/m/Y H:i:s');
    }
    public function getUpdatedAtAttribute($value)
    {

        $hour =  Carbon::createFromTimestamp(strtotime($value))
            ->timezone('America/Sao_Paulo')
            ->toDateTimeString();

        return Carbon::parse($hour)->format('d/m/Y H:i:s');
    }
}
