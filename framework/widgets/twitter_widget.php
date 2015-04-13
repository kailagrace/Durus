<?php
/******************************************************/
/*         twitter widget
/******************************************************/		 
class widget_twitter extends WP_Widget { 
	
	// Widget Settings
	function widget_twitter() {
		$widget_ops = array('description' => __('Display your latest Tweets','brad-framework') );
		$control_ops = array( 'id_base' => 'twitter' );
		$this->WP_Widget( 'twitter', __('Brad.Twitter Widget','brad-framework'), $widget_ops, $control_ops );
	}
	
	// Widget Output
	function widget($args, $instance) {
		
		require_once("twitter_oauth/twitteroauth.php");
		extract($args);
		$title = apply_filters('widget_title', $instance['title']);
		$consumer_key = $instance['consumer_key'];
		$consumer_secret = $instance['consumer_secret'];
		$access_token = $instance['access_token'];
		$access_token_secret = $instance['access_token_secret'];
		$twitter_id = $instance['twitter_id'];
		$count = (int) $instance['count'];
		echo $before_widget;
		
		if($title) {
			echo $before_title.$title.$after_title;
		}

		if($twitter_id && $consumer_key && $consumer_secret && $access_token && $access_token_secret && $count) {
			$transID = 'brad_twitter_'.$args['widget_id'];
		    $cacheTime = 20;
			
			if( ($transient = get_transient($transID)) === false  ){		 
			$connection = new TwitterOAuth($consumer_key, $consumer_secret, $access_token, $access_token_secret);
			$tweets = $connection->get("https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name=".$twitter_id."&count=".$count);
			set_transient($transID, $tweets , 60 * $cacheTime);
			}
	
		$tweets = (array) get_transient($transID);
		
		if($tweets && is_array($tweets)) {
		?>

       <div class="recent-tweets" id="recent_tweets_<?php echo $args['widget_id']; ?>">
          <ul>
          <?php foreach($tweets as $tweet){ ?>
          <li>
             <span>
             <?php
	         $tweet_text = preg_replace('/http:\/\/([a-z0-9_\.\-\+\&\!\#\~\/\,]+)/i', '&nbsp;<a href="http://$1" target="_blank">http://$1</a>&nbsp;', $tweet['text']);
	         $tweet_text = preg_replace('/@([a-z0-9_]+)/i', '&nbsp;<a href="http://twitter.com/$1" target="_blank">@$1</a>&nbsp;', $tweet_text);
	         echo $tweet_text;
	         ?>
             </span>
            <?php
		        $tweetTime = strtotime($tweet['created_at']);
		        $timeAgo = $this->time_to_ago($tweetTime);
	        ?>
           <br>
           <a href="http://twitter.com/<?php echo $tweet['user']['screen_name']; ?>/status/<?php echo $tweet['id_str'];?>" >           <?php echo $timeAgo; ?></a>
        </li>
        <?php } ?>
        </ul>
    </div>
    <?php }}
		echo $after_widget;
	}
	
	function time_to_ago($time)
	{
	   $periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
	   $lengths = array("60","60","24","7","4.35","12","10");

	   $now = time();

	       $difference     = $now - $time;
	       $tense         = "ago";

	   for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
	       $difference /= $lengths[$j];
	   }

	   $difference = round($difference);

	   if($difference != 1) {
	       $periods[$j].= "s";
	   }

	   return "$difference $periods[$j] ago ";
	}


	function update($new_instance, $old_instance)
	{
		$instance = $old_instance;

		$instance['title'] = strip_tags($new_instance['title']);
		$instance['consumer_key'] = $new_instance['consumer_key'];
		$instance['consumer_secret'] = $new_instance['consumer_secret'];
		$instance['access_token'] = $new_instance['access_token'];
		$instance['access_token_secret'] = $new_instance['access_token_secret'];
		$instance['twitter_id'] = $new_instance['twitter_id'];
		$instance['count'] = $new_instance['count'];

		return $instance;
	}

     function form($instance) {
		 
		// Set up some default widget settings
		$defaults = array('title' => 'Latest Tweets', 'twitter_id' => '', 'count' => 5, 'consumer_key' => '', 'consumer_secret' => '' , 'access_token' => '' , 'access_token_secret' => '');
		$instance = wp_parse_args((array) $instance, $defaults);

?>
<p>
  <label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
  <input class="widefat" type="text" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>">
</p>
<p>
  <label for="<?php echo $this->get_field_id('twitter_id'); ?>">Your Twitter Id:</label>
  <input class="widefat" type="text" id="<?php echo $this->get_field_id('twitter_id'); ?>" name="<?php echo $this->get_field_name('twitter_id'); ?>" value="<?php echo $instance['twitter_id']; ?>">
</p>
<p>
  <label for="<?php echo $this->get_field_id('consumer_key'); ?>">Twitter Consumer Key:</label>
  <input class="widefat" type="text" id="<?php echo $this->get_field_id('consumer_key'); ?>" name="<?php echo $this->get_field_name('consumer_key'); ?>" value="<?php echo $instance['consumer_key']; ?>">
</p>
<p>
  <label for="<?php echo $this->get_field_id('consumer_secret'); ?>">Twitter Consumer Secret:</label>
  <input class="widefat" type="text" id="<?php echo $this->get_field_id('consumer_secret'); ?>" name="<?php echo $this->get_field_name('consumer_secret'); ?>" value="<?php echo $instance['consumer_secret']; ?>">
</p>
<p>
  <label for="<?php echo $this->get_field_id('access_token'); ?>">Twitter Acess token:</label>
  <input class="widefat" type="text" id="<?php echo $this->get_field_id('access_token'); ?>" name="<?php echo $this->get_field_name('access_token'); ?>" value="<?php echo $instance['access_token']; ?>">
</p>
<p>
  <label for="<?php echo $this->get_field_id('access_token_secret'); ?>">Twitter Acess token Secret:</label>
  <input class="widefat" type="text" id="<?php echo $this->get_field_id('access_token_secret'); ?>" name="<?php echo $this->get_field_name('access_token_secret'); ?>" value="<?php echo $instance['access_token_secret']; ?>">
</p>
<p>
  <label for="<?php echo $this->get_field_id('count'); ?>">Display how many tweets?</label>
  <input class="widefat" type="text" id="<?php echo $this->get_field_id('count'); ?>" name="<?php echo $this->get_field_name('count'); ?>" value="<?php echo $instance['count']; ?>">
</p>
<?php
	}
}	

// Add Widget
function widget_twitter_init() {
	register_widget('widget_twitter');
}
add_action('widgets_init', 'widget_twitter_init');

?>
