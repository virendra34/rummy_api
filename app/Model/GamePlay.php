<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GamePlay extends Model
{
    protected $table = 'tbl_gameplay';
	
    protected $primaryKey = 'id';

	protected $fillable = [
        'game_id',
        'userid',
        'initial_cards',
        'ongoing_cards',
    ];

    public function games(){
        return $this->hasOne('App\Models\Games', 'id', 'game_id');
    }
}
