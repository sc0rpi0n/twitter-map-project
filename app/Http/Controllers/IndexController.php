<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Config;
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
        
        $radius = Config::get('customsettings.tweet_radius');     
        $geoCode =  ($latlong == '') ? '' : $latlong . ',' . $radius;
        $return = array();
        
        // add search to History
        
        $searchCookie = isset($_COOKIE['search_history']) ? json_decode($_COOKIE['search_history']) : array();
        $searchCookie = get_object_vars($searchCookie);
        if(array_key_exists($searchTerm, $searchCookie)){
            $searchCookie[$searchTerm]+= 1;
        }else{
            $searchCookie[$searchTerm] = 1;
        }
        
        setcookie('search_history', json_encode($searchCookie));
        
        $hourBefore = date('Y-m-d H:i:s', strtotime('-'.Config::get('customsettings.cache_hour').' hour'));
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
            // clear cache
            $deletedCache = tweet::where('updated_at', '<', $hourBefore)->delete();
            
            // fetch tweets from Twitter
            $remoteResult = json_decode(Twitter::get('search/tweets',['q' => $searchTerm,'geocode'=>$geoCode,'format' => 'json']));

            

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
    
    /**
     * Function to view the History of location search
     * @return  list of search history
     */
    public function history(){
        $searchCookie = isset($_COOKIE['search_history']) ? json_decode($_COOKIE['search_history']) : array();
        
        return view('searchHistory')->with('searchHistory', $searchCookie);
    }
}
