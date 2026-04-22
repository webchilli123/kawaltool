<?php

namespace App\Models;

use App\Helpers\DateUtility;

class Followup extends BaseModel
{
    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->beforeSave();
        });

        self::updating(function ($model) {
            $model->beforeSave();
        });
    }

    protected function beforeSave()
    {
        $date_fields = ["follow_up_date"];
        foreach ($date_fields as $date_field) {
            if ($this->{$date_field}) {
                $this->{$date_field} = DateUtility::getDate($this->{$date_field}, DateUtility::DATE_FORMAT);
            }
        }
    }

    public function getFollowUpDateAttribute($value)
    {
        if ($value) {
            return DateUtility::getDate($value, DateUtility::DATE_OUT_FORMAT);
        }

        return $value;
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function followupUser()
    {
        return $this->belongsTo(User::class, 'follow_up_user_id');
    }
}
