<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class Party extends BaseModel
{
    // use SoftDeletes;

    public $appends = [
        "display_name",
        "full_address"
    ];

    public array $child_model_class = [
     
        PurchaseBill::class => [
            "foreignKey" => "party_id",
            "preventDelete" => true
        ],
        Complaint::class => [
            "foreignKey" => "customer_id",
            "preventDelete" => true
        ],
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'party_products')
            ->withTimestamps()
            ->withPivot('deleted_at');
    }

     public function partyProducts()
    {
        return $this->hasMany(PartyProduct::class, 'party_id');
    }

    public function finishedItems()
    {
        return $this->belongsToMany(Product::class, 'party_products');
    }

    public function city()
    {
        return $this->belongsTo(City::class, 'city_id')->withDefault(['name' => '-']);
    }

    public function getDisplayName()
    {
        $name = $this->name;

        if ($this->is_job_worker) {
            $name .= " (Job-Worker)";
        }

        return $name;
    }

    public function getDisplayNameAttribute()
    {
        return $this->getDisplayName();
    }

    public function getFullAddressAttribute()
    {
        $address = "";

        if ($this->address) {
            $address .= $this->address;
        }

        if (!$this->relationLoaded('city')) {
            $this->load('city');
        }

        if (isset($this->city->name) && $this->city->name) {
            if ($address) {
                $address .= ", ";
            }

            $address .= $this->city->name;
        }

        if (!$this->city->relationLoaded('state')) {
            $this->city->load('state');
        }

        if (isset($this->city->state->name) && $this->city->state->name) {
            if ($address) {
                $address .= ", ";
            }

            $address .= $this->city->state->name;
        }

        return $address;
    }
}
