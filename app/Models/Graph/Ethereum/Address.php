<?php

namespace App\Models\Graph\Ethereum;

use Vinelab\NeoEloquent\Eloquent\Model as NeoEloquent;

class Address extends NeoEloquent
{
    protected $label = 'Address';
    protected $fillable = ['address', 'type'];
    protected $connection = 'neo4j';

    public function transfers()
    {
        return $this->belongsToMany(Address::class, 'TRANSFERS');
    }

}
