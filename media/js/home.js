/** The lowest tweet ID which has been displayed **/
var lowestTweetId;

/** Count of total tweets displayed on the page **/
var count = 0;

/** Template of the tweet div that will be appended **/
var tweetTemplate = '\
<div class="col-md-4">\
    <div class="panel panel-default">\
        <div class="panel-heading">\
            <h3 class="tweet-owner"></h3>\
        </div>\
        <div class="panel-body">\
            <p class="tweet"></p>\
            <strong><p class="rt-count"></p></strong>\
        </div>\
    </div>\
</div>';

/**
 * Function to fetch tweets asynchronously from the API
 *
 * @callback requestCallback
 * @param none
 */
function getTweets(callback) {
    $.ajax({
        type: 'GET',
        url: 'fetch',
        data: {'max_id': lowestTweetId},    /** Lowest id present on page **/
        dataType: 'json',
        success: function(data) {
            /** If the request was unsuccessful **/
            if (!data.status) {
                alert('Some error occurred. Please try again later');
                return false;
            }
            console.log(data);

            /** Updated lowestTweetId to ID of the last tweet fetched **/
            lowestTweetId = data.tweets[data.tweets.length - 1].id_str;
            console.log('Lowest ID: ' + lowestTweetId);
            /** Fire the callback **/
            callback(data.tweets);
        }
    });
}

/**
 * Function to display the fetched tweets
 *
 * @param {array} statuses contains array of tweet objects
 */
function displayTweets(statuses) {
    /** Iterate through all the tweets **/
    $.each(statuses, function(index, tweet) {
        /** Create a new bootstrap row div after every 3 tweets displayed **/
        if (count % 3 == 0) {
            var div = $("<div/>", {'class': 'row'});
            $(".container").append(div);    /** Add row div at the end **/
        }
        console.log(tweet.id_str);
        var tweetDiv = $(tweetTemplate);
        tweetDiv.find('.tweet').html(tweet.text);
        tweetDiv.find('.rt-count').html('RT: ' + tweet.retweet_count);
        tweetDiv.find('.tweet-owner').html('@' + tweet.screen_name);
        $(".row").last().append(tweetDiv);
        count++;
    });
}

/**
 * Fired when the page is loaded
 */
$(document).ready(function() {
    /** Fetch and display the tweets **/
    getTweets(displayTweets);
});

/**
 * Fetch more tweets if the page is scrolled to bottom
 */
$(window).scroll(function() {
    if ($(window).scrollTop() + $(window).height() == $(document).height()) {
        /** Fetch and display the tweets **/
        getTweets(displayTweets);
    }
});
