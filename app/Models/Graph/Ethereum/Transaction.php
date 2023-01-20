<?php

namespace App\Models\Graph\Ethereum;

use Vinelab\NeoEloquent\Eloquent\Model as NeoEloquent;

class Transaction extends NeoEloquent {
    protected $label = 'Transaction';
    protected $fillable = ['hash', 'blockNumber', 'gasUsage', 'gasPrice'];
    protected $connection = 'neo4j';

    public function out()
    {
        return $this->hasMany(Address::class, 'OUT');
    }

    public function in()
    {
        return $this->belongsToMany(Address::class, 'IN');
    }
}
