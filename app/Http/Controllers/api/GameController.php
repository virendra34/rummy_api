<?php

namespace App\Http\Controllers\api;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Model\Cards;
use App\Model\Games;
use App\Model\GamePlay;
use App\Model\GamePlayLogs;
use Illuminate\Validation\Rule;
use Validator;
class GameController extends Controller
{
    CONST CARDS_FOR_TWO_PLAYERS = 13;
    CONST CARDS_FOR_SIX_PLAYERS = 7;
    protected $allCards = [1 => 'AOFhearts',2 => '2OFhearts',3 => '3OFhearts',4 => '4OFhearts',5 => '5OFhearts',6 => '6OFhearts',7 => '7OFhearts',8 => '8OFhearts',9 => '9OFhearts',10 => '10OFhearts',11 => 'JOFhearts',12 => 'QOFhearts',13 => 'KOFhearts',14 => 'AOFdiams',15 => '2OFdiams',16 => '3OFdiams',17 => '4OFdiams',18 => '5OFdiams',19 => '6OFdiams',20 => '7OFdiams',21 => '8OFdiams',22 => '9OFdiams',23 => '10OFdiams',24 => 'JOFdiams',25 => 'QOFdiams',26 => 'KOFdiams',27 => 'AOFspades',28 => '2OFspades',29 => '3OFspades',30 => '4OFspades',31 => '5OFspades',32 => '6OFspades',33 => '7OFspades',34 => '8OFspades',35 => '9OFspades',36 => '10OFspades',37 => 'JOFspades',38 => 'QOFspades',39 => 'KOFspades',40 => 'AOFclubs',41 => '2OFclubs',42 => '3OFclubs',43 => '4OFclubs',44 => '5OFclubs',45 => '6OFclubs',46 => '7OFclubs',47 => '8OFclubs',48 => '9OFclubs',49 => '10OFclubs',50 => 'JOFclubs',51 => 'QOFclubs',52 => 'KOFclubs',53 => 'Joker-1',54 => 'Joker-2'];
    public function start_game(Request $req){
        $input = $req->all();
        $validator = Validator::make($req->all(), [
            'game_id' => 'required',
            'table_no' => 'required',
            'userids' => 'required|min:2|max:6',
        ]);
        if ($validator->fails()) {
            return response()->json([
                [
                    'success' => false,
                    'data' => null,
                    'message' => implode(', ', $validator->messages()->all()),
                ]
            ]);
        }
        $input['no_of_players'] = count(explode(',', $input['userids']));
        $input['game_status'] = 1;
        Games::create($input);
        $game = Games::where('game_id', $input['game_id'])->get();
        $gamePlayInput = [];
        if($input['no_of_players'] === 2){
            $cardsForPlayers = GameController::CARDS_FOR_TWO_PLAYERS;
        }elseif($input['no_of_players'] === 6){
            $cardsForPlayers = GameController::CARDS_FOR_SIX_PLAYERS;
        }
        $initialDeck = [];
        for($i = 1; $i <= $input['no_of_players']; $i++){
            $gamePlayInput[$i-1]['userid'] = $i;
            $gamePlayInput[$i-1]['game_id'] = $game[0]->id;
            $gamePlayInput[$i-1]['ongoing_cards'] = $gamePlayInput[$i-1]['initial_cards'] = implode(',',array_rand($this->allCards, $cardsForPlayers));
            $initialDeck[$gamePlayInput[$i-1]['userid']] = Cards::whereIn('id', explode(',',$gamePlayInput[$i-1]['initial_cards']))->get()??null;
        }
        GamePlay::insert($gamePlayInput);
        $game = Games::where('no_of_players', count(explode(',',$input['userids'])))->where('game_id', $input['game_id'])->where('table_no', $input['table_no'])->where('game_status', 1)->get();
        $response = [
            'game_id' => $game[0]->id,
            'initial_cards' => $initialDeck
        ];
        return response()->json([
            'success' => true,
            'data' => $response,
            'message' => 'Game started!\n'
        ]);
    }

    public function players_move(Request $req){
        try{
            $input = $req->all();
            $validator = Validator::make($req->all(), [
                'game_id' => 'required',
                'userid' => 'required',
                // 'pick' => 'required',
                // 'drop' => 'required',
                'source' => 'required',
            ]);
            if(!isset($input['pick']) && !isset($input['drop'])){
                return response()->json([
                    [
                        'success' => false,
                        'data' => null,
                        'message' => 'pick or drop field must not be empty!',
                    ]
                ]);
            }
            if ($validator->fails()) {
                return response()->json([
                    [
                        'success' => false,
                        'data' => null,
                        'message' => implode(', ', $validator->messages()->all()),
                    ]
                ]);
            }
            $game = Games::find($input['game_id']);
            if(!$game){
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Invalid game_id !!!',
                ]);
            }
            if($game->get()[0]->game_status === 0){
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'Invalid game status!!!',
                ]);
            }
            $gamePlay = GamePlay::where('game_id', $input['game_id'])->where('userid', $input['userid'])->get();
            $onGoingCards = explode(',',$gamePlay[0]->ongoing_cards);
            $pickedCard = (isset($input['pick']))? array_search($input['pick'], $this->allCards) : null;
            $droppedCard = (isset($input['drop']))? array_search($input['drop'], $this->allCards) : null;
            $validatePick = null;
            $validateDrop = null;
            if($pickedCard){
                $validatePick = array_search($input['pick'], $this->allCards);
                $this->validateCardFromOngoingCards($gamePlay, $input['pick'], 'pick');
            }
            if($droppedCard){
                $validateDrop = array_search($input['drop'], $this->allCards);
                $this->validateCardFromOngoingCards($gamePlay, $input['drop'], 'drop');
                unset($onGoingCards[array_search($droppedCard, $onGoingCards)]);
            }
            if(!$validatePick && !$validateDrop){
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'picked or dropped card name doesn\'t exist',
                ]);
            }
            # logic to detect if change occur more then 1
            $gamePlayLog = GamePlayLogs::where('game_id', $input['game_id'])->where('userid', $input['userid'])->orderBy('created_at', 'DESC')->limit(2)->get();
            // dd($gamePlayLog->toArray(), count($gamePlayLog));
            if(count($gamePlayLog) > 1){
                $arrayDiff=array_diff(explode(',', $gamePlayLog[0]->ongoing_cards), explode(',', $gamePlayLog[1]->ongoing_cards));
                if(count($arrayDiff) > 1){
                    $game->update(['game_status' => 0]);
                    return response()->json([
                        'success' => false,
                        'data' => null,
                        'message' => 'Breach Detected!!!',
                    ]);
                }
            }
            $onGoingCards[] = $pickedCard;
            $onGoingCards = trim(implode(',',$onGoingCards), ',');
            // dd($onGoingCards, $pickedCard);
            $gamePlay = GamePlay::find($gamePlay[0]->id);
            // dd($gamePlay);
            $gamePlay->update(['ongoing_cards' => $onGoingCards]);
            if(isset($input['pick'])){
                $input['log'] = "picked from {$input['source']}";
                $input['ongoing_cards'] = $onGoingCards;
                GamePlayLogs::create($input);
            }
            if(isset($input['drop'])){
                $input['log'] = "dropped from {$input['source']}";
                $input['ongoing_cards'] = $onGoingCards;
                GamePlayLogs::create($input);
            }
            $gamePlay = GamePlay::where('id', $gamePlay->get()[0]->id)->get();
            $cards = Cards::whereIn('id', explode(',', $gamePlay[0]->ongoing_cards))->get()??null;
            return response()->json([
                'success' => true,
                'data' => $cards,
                'message' => 'Updated successfully'
            ]);
        }catch(CustomException $ce){
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => $ce->getMessage()
            ]); 
        }catch(\Exception $e){
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Oops something went wrong!'
            ]);
        }
    }

    public function validateCardFromOngoingCards($gamePlay, $cardName, $move){
        $card = array_search($cardName, $this->allCards);
        if(!$card){
            throw new CustomException("Dropped card doesn't exist in any card set");
        }
        if($move === 'drop'){
            $onGoingCards = explode(',',$gamePlay[0]->ongoing_cards);
            if(!in_array(array_search($cardName, $this->allCards), $onGoingCards)){
                throw new CustomException("Dropped card doesn't exist in current card set");
            }
        }elseif($move === 'pick'){
            $onGoingCards = explode(',',$gamePlay[0]->ongoing_cards);
            // dd($onGoingCards, $this->allCards[array_search($cardName, $this->allCards)], array_search($cardName, $this->allCards));
            if(in_array(array_search($cardName, $this->allCards), $onGoingCards)){
                throw new CustomException("Picked card already exist in current card set");
            }
        }
        return true;
    }
}

class CustomException extends \Exception{}