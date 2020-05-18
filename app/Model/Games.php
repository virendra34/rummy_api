<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
class Games extends Model
{
    protected $table = 'tbl_games';
	
    protected $primaryKey = 'id';

	protected $fillable = [
        'game_id',
        'table_no',
        'no_of_players',
        'game_status'
    ];

    public function gameplays(){
        return $this->hasMany('App\Models\Gameplay', 'game_id', 'id');
    }
}
