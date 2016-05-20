<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Cookie;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Thujohn\Twitter\Facades\Twitter;
use App\tweet;

class IndexController extends Controller
{
    /**
     * Landing page showing the map and tweets for the location
     *
     * @return  tweets of particular location
     * @return  latitude of searched location
     * @return  longitude of searched location
     * @return  searched location
     */
    public function mapper(Request $request){
        return view('mapper');
    }
    /**
     * Json that returns tweets with specified search location name around specified radius
     *
     * @return  User Profile pic for tweet with search term
     * @return  latitude of tweet
     * @return  longitude of tweet
     * @return  tweet text
     */
    public function locateTweets(Request $request){
        $searchTerm = $request->get('search');
        $latlong = $request->get('geoCode','');
        $radius = '50km';    // todo change this to configurable 
        $geoCode =  ($latlong == '') ? '' : $latlong . ',' . $radius;
        
        $return = array();
        
        $hourBefore = date('Y-m-d H:i:s', strtotime('-1 hour'));
        $locationTweet = tweet::where('SearchLocation', strtolower($searchTerm))->where('updated_at', '>=', $hourBefore)->get();
        if (count($locationTweet) >  0){
            foreach($locationTweet as $status){
                $tweet = array(
                    'userPic'   =>  $status->userPic,
                    'lat'       =>  (float)$status->lat,
                    'lng'       =>  (float)$status->lng,
                    'tweet'     =>  $status->tweet,
                    'createdAt' =>  $status->createdAt
                );
                array_push($return, $tweet);
            }
            //TODO optimize routine to fetch tweets other than in cache and return updated ajax
        }else{
            // fetch tweets from Twitter
            $remoteResult = json_decode(Twitter::get('search/tweets',['q' => $searchTerm,'geocode'=>$geoCode,'format' => 'json']));

            //ToDo check DB if tweet already fetched , else save in database

            foreach($remoteResult->statuses as $status){

                if(isset($status->coordinates)){
                    $tweet = array(
                        'userPic'   =>  $status->user->profile_image_url_https,
                        'lat'       =>  $status->coordinates->coordinates[1],
                        'lng'       =>  $status->coordinates->coordinates[0],
                        'tweet'     =>  $status->text,
                        'createdAt' => date("Y-m-d H:i:s ",strtotime($status->created_at))
                    );
                    array_push($return, $tweet);
                    
                    // save tweet to cache
                    
                    $cacheTweet =  new tweet();
                    $cacheTweet->id_str = $status->id_str;
                    $cacheTweet->SearchLocation = $searchTerm;
                    $cacheTweet->tweet = $status->text;
                    $cacheTweet->lat = $status->coordinates->coordinates[1];
                    $cacheTweet->lng = $status->coordinates->coordinates[0];
                    $cacheTweet->createdAt = date("Y-m-d H:i:s ",strtotime($status->created_at));
                    $cacheTweet->userPic = $status->user->profile_image_url_https;
                    
                    $cacheTweet->save();
                    
                }

            }
        }
        
        
        return response()->json($return);
    }
}
