<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Cost extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $guarded = [];

    public function costDetails() {
        return $this->hasMany('App\Models\CostDetail', 'cost_id', 'id');
    }

    public function costHistory() {
        return $this->hasMany('App\Models\HistoryCost', 'cost_id', 'id');
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
