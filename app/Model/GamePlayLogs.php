<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class GamePlayLogs extends Model
{
    protected $table = 'tbl_gameplay_log';
	
    protected $primaryKey = 'id';

	protected $fillable = [
        'game_id',
        'userid',
        'log',
        'pick',
        'drop',
        'source',
        'ongoing_cards',
    ];
}
