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
    protected $allCards = ['', 'AOFhearts','2OFhearts','3OFhearts','4OFhearts','5OFhearts','6OFhearts','7OFhearts','8OFhearts','9OFhearts','10OFhearts','JOFhearts','QOFhearts','KOFhearts','AOFdiams','2OFdiams','3OFdiams','4OFdiams','5OFdiams','6OFdiams','7OFdiams','8OFdiams','9OFdiams','10OFdiams','JOFdiams','QOFdiams','KOFdiams','AOFspades','2OFspades','3OFspades','4OFspades','5OFspades','6OFspades','7OFspades','8OFspades','9OFspades','10OFspades','JOFspades','QOFspades','KOFspades','AOFclubs','2OFclubs','3OFclubs','4OFclubs','5OFclubs','6OFclubs','7OFclubs','8OFclubs','9OFclubs','10OFclubs','JOFclubs','QOFclubs','KOFclubs','Joker','Joker'];
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
        $response = [
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
                unset($onGoingCards[$droppedCard]);
            }
            if(!$validatePick && !$validateDrop){
                return response()->json([
                    'success' => false,
                    'data' => null,
                    'message' => 'picked or dropped card name doesn\'t exist',
                ]);
            }
            # logic to detect if change occurance
            // $dataArrayKey = array_keys($input['']);
            // $colDiff=array_diff($hardCodedKey,$dataArrayKey);
            $onGoingCards[] = $pickedCard;
            $onGoingCards = trim(implode(',',$onGoingCards), ',');
            // dd($onGoingCards, $pickedCard);
            $gamePlay = GamePlay::find($gamePlay[0]->id);
            // dd($gamePlay);
            $gamePlay->update(['ongoing_cards' => $onGoingCards]);
            if(isset($input['pick'])){
                $input['log'] = "picked from {$input['source']}";
                GamePlayLogs::create($input);
            }
            if(isset($input['drop'])){
                $input['log'] = "dropped from {$input['source']}";
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
        }/*catch(\Exception $e){
            return response()->json([
                'success' => false,
                'data' => null,
                'message' => 'Oops something went wrong!'
            ]); 
        }*/
    }

    public function validateCardFromOngoingCards($gamePlay, $cardName, $move){
        $card = array_search($cardName, $this->allCards);
        if(!$card){
            throw new CustomException("Dropped card doesn't exist in any card set");
        }
        if($move === 'drop'){
            $onGoingCards = explode(',',$gamePlay[0]->ongoing_cards);
            if(!in_array(array_search($cardName, $this->allCards), $onGoingCards)){
                throw new CustomException("Dropped card doesn't exist in previous card set");
            }
        }elseif($move === 'pick'){
            $onGoingCards = explode(',',$gamePlay[0]->ongoing_cards);
            if(in_array(array_search($cardName, $this->allCards), $onGoingCards)){
                throw new CustomException("Picked card already exist in current card set");
            }
        }
        return true;
    }
}

class CustomException extends \Exception{}