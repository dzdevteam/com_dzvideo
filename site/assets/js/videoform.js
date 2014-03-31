jQuery(document).ready(function() {
    var KEY = "AIzaSyAnLvPvicVNjWC4Ha2IdaOx6k3R9KxNmb4";
    var alert = function(message) {
        jQuery('#alert').html('<div class="alert alert-danger"><button type="button" class="close" data-dismiss="alert">&times;</button>' + message + '</div>');
    }
    var clear = function() {
        jQuery('#alert').html('');
    }
    var notice = function() {
        jQuery('#alert').html('<div class="alert"><button type="button" class="close" data-dismiss="alert">&times;</button>Loading Youtube information...</div>');
    }
    jQuery('#jform_link').on('keyup', function() {
        var regexp = /.*(?:youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=)([^#\&\?]*).*/;
        var matches = jQuery(this).val().match(regexp);
        if (matches == null) {
            alert("Invalid Youtube link!");
            return;
        } else {
            clear();
        }
        
        var video_id = matches[1];
        notice();
        jQuery.get(
            'https://www.googleapis.com/youtube/v3/videos', 
            {
                part: 'id,snippet,player,contentDetails',
                id: video_id,
                key: KEY
            },
            function(data) {
                if (data.items.length == 0) {
                    alert("Youtube clip not found");
                } else {
                    clear();
                    var video = data.items[0];
                    jQuery('#jform_title').val(video.snippet.title);
                    jQuery('#jform_shortdesc').val(video.snippet.description);
                    jQuery('#jform_author').val(video.snippet.channelTitle);
                    jQuery('#embed').html(video.player.embedHtml);
                    jQuery('#jform_videoid').val(video.id);
                    jQuery('#jform_images_mqdefault').val(video.snippet.thumbnails.default.url);
                    time_parts = video.contentDetails.duration.match(/^PT(\d*)M(\d*)S/);
                    jQuery('#jform_length').val(parseInt(time_parts[1]) * 60 + parseInt(time_parts[2]));
                }
            }
        );
            
    });
})