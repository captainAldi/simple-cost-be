<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HistoryCostDetail extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function historyCost() {
        return $this->belongsTo('App\Models\HistoryCostDetail', 'history_cost_id', 'id');
    }

    public function getCreatedAtAttribute($date)
    {
        return Carbon::parse($date)->timezone(env('APP_TIMEZONE'))->format('d-M-Y H:i:s');
    }

    public function getUpdatedAtAttribute($date)
    {
        return Carbon::parse($date)->timezone(env('APP_TIMEZONE'))->format('d-M-Y H:i:s');
    }
}
