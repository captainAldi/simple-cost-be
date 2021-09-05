<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class HistoryCost extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function historyCostDetails() {
        return $this->hasMany('App\Models\HistoryCostDetail', 'history_cost_id', 'id');
    }

    public function costServer() {
        return $this->belongsTo('App\Models\Cost', 'cost_id', 'id');
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
