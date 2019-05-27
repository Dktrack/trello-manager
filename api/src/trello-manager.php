<?php

namespace src\trelloManager;
use \Datetime;

class TrelloManager {
    private $trelloKey = '';
    private $trelloToken  = '';
    private $idTableau = '';
    private $idListToClean = '';

    public function __construct() {
        
        $config = require ( __DIR__ . '/configTrello.php');
        // var_dump($config);
        $this->trelloKey = $config["trelloKey"];
        $this->trelloToken = $config["trelloToken"];
        $this->idTableau = $config["idTableau"];
        $this->idListToClean = $config["idListToClean"];
    }

    public function archiveDONE() {
        $timeNow = new DateTime("NOW");
        $cards = self::getList( $this->idListToClean );
        if($cards) {
            foreach($cards as $card) {
                $actions = self::getCardActions( $card->id );
                if($actions) {
                    foreach($actions as $action) {
                        if($action->data && $action->data->listAfter) {
                            $d = new DateTime($action->date);
                            // $since = $d->diff($timeNow, true);
                            // echo "Il y a ", $since->format('%a days and %h'), "->", $delta, " jours", "\n";
                            $delta = ($timeNow->getTimestamp() - $d->getTimestamp()) / 86400; // différence en jours
                            echo "Il y a ", round($delta, 2), " jours : ", $card->name, "\n";
                            if($delta > 7) {
                                if(self::closeCard($card->id)) echo "-> La carte a été archivée\n";
                                else echo "-> La carte n'a pas pu être archivée\n";
                            }
                            break;
                        }
                    }
                }
            }
        } else {
            echo "Liste à nettoyer introuvable :(\n";
        }
    }

    private function getList($listID) {
        $route = 'lists/' . $listID . '/cards';
        $rtn = self::trelloRequest($route);
        if($rtn["status"] == 200) {
            $cards = json_decode($rtn["response"]);
            return $cards;
        } else {
            echo "Liste à nettoyer introuvable :(\n";
            return NULL;
        }
    }

    private function getCardActions( $card_id ) {
        $reponse = self::trelloRequest('cards/' . $card_id. '/actions', array( "filter" => "updateCard" ));
        if($reponse["status"] == 200) {
            $actions = json_decode($reponse["response"]);
            return $actions;
        } else {
            echo "Carte " . $card->id ." est introuvable\n";
            return NULL;
        }
    }

    private function closeCard($card_id) {
        $rtn = self::trelloRequest("cards/" . $card_id, array("closed" => true), "PUT");
        return $rtn["status"] == 200;
    }

    private function trelloRequest($cmd, $params = array(), $methode = "GET") {
        // url de l'API Trello
        $url = 'https://api.trello.com/1/' . $cmd;

        // ajout des identifiants
        $params['key'] = $this->trelloKey;
        $params['token'] = $this->trelloToken;

        $paramsStr = implode('&', array_map(
            function ($v, $k) {
                if(is_array($v)){
                    return $k.'[]='.implode('&'.$k.'[]=', $v);
                }else{
                    return $k.'='.$v;
                }
            }, 
            $params, 
            array_keys($params)
        ));

        $url .= "?" . $paramsStr;

        // echo 'Envoi d\'une requête : ', $url, "\n";

        $ch = curl_init(); 
        curl_setopt($ch, CURLOPT_URL, $url); 
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $methode);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE); 
        $body = curl_exec($ch); 
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); 
        curl_close($ch); 

        $retour = array(
            'status' => $httpCode,
            'response' => $body
        );
        return $retour;
    }


}