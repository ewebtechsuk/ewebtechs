let timeSpentOnPage = function () {

	let time;
	let start_idletimer;
	let end_idletimer;	
	let start_activetimer;
	let end_activetimer;
	let activetimer = 0;
	let idletimer = 0;
	
	window.onload = resetTimeTrackingTimer;
	document.onload = resetTimeTrackingTimer;
	document.onmousemove = resetTimeTrackingTimer;
	document.onmousedown = resetTimeTrackingTimer; // touchscreen presses
	document.ontouchstart = resetTimeTrackingTimer;
	document.onclick = resetTimeTrackingTimer; // touchpad clicks
	document.onkeypress = resetTimeTrackingTimer;
	document.addEventListener('scroll', resetTimeTrackingTimer, true);
	
	 
	function showTimeTrackingPopup() {		
	    if (jQuery('.is-active-time-tracking-popup').length < 1 && document.visibilityState == 'visible'){
			if ( time_tracking.idle_popup == true ) {
				var string = '<div class="is-active-time-tracking-popup show-popup">' + time_tracking.idle_message+ '</div>';
			} else {
				var string = '<div class="is-active-time-tracking-popup"></div>';
			}
	    	
	    	jQuery('body').append(string);
	    	start_idletimer = Date.now();
	    }
	}

	function calculateTimeTrackingIdleTime() {
		if (document.visibilityState == 'hidden'){
			start_idletimer = Date.now();
		}
		else{
			end_ideltimer = Date.now();
			idletimer = idletimer + ((end_ideltimer - start_idletimer)/1000);			
		}
	}


	function resetTimeTrackingTimer() {
		if (jQuery('.is-active-time-tracking-popup').length >= 1 && time_tracking.idle_popup != true){
	        end_ideltimer = Date.now();
			idletimer = idletimer + ((end_ideltimer - start_idletimer)/1000);
			jQuery('.is-active-time-tracking-popup').remove();			
	    } else {
			clearTimeout(time);
			time = setTimeout(showTimeTrackingPopup, parseInt(time_tracking.idle_time) * 1000);
		}
	}

	function getTimeTrackingCachedStorage() {
	    var archive = {}, // Notice change here
            keys = Object.keys(localStorage),
            i = keys.length;
        while ( i-- ) {
	    	if ( keys[i].includes( 'time_tracking_cached_' ) ) {				
	            archive[ keys[i] ] = localStorage.getItem( keys[i] );
	    	}
        }
        return archive;
	}

	function sendFailedTimeTrackingRequests() {
		
		var previous_failure = getTimeTrackingCachedStorage();
		var data = [];
		for ( const property in previous_failure ) {			
			data = JSON.parse( previous_failure[ property ] );
			jQuery.ajax({
	            type: "POST",
	            url: time_tracking.ajaxurl,
	            dataType: "json",
	            data: {
	            	action: 'add_time_tracking_entry',
	            	nonce: time_tracking.nonce,
	                user_id: data.user_id,
	                course_id: data.course_id,
	                post_id: data.post_id,
	                total_time: data.total_time,
	                time: data.time
	            },
	            success: function( response ) {
	            	localStorage.removeItem( property );
	            },
	            error: function(XMLHttpRequest, textStatus, errorThrown) {					
	            }
	        });
		}
	}


	function sendTimeTrackingData(activetimer, is_not_mark_complete=true) {		
		
		var data = {
			user_id: time_tracking.user_id,
			course_id: time_tracking.course_id,
			post_id: time_tracking.post_id,
			total_time: activetimer,
			time: Math.round( Date.now() / 1000 )
		};		
		var current_key      = 'time_tracking_cached_' + Math.floor( Math.random() * 1000 );
		
		jQuery.ajax({
            type: "POST",
            url: time_tracking.ajaxurl,
            dataType: "json",
            data: {
            	action: 'add_time_tracking_entry',
            	nonce: time_tracking.nonce,
                user_id: time_tracking.user_id,
                course_id: time_tracking.course_id,
                post_id: time_tracking.post_id,
                total_time: activetimer,
                time: Math.round( Date.now() / 1000 )
            },
            success: function( response ) {
				start_activetimer = Date.now();
				resetTimeTrackingTimer();
				if ( ! is_not_mark_complete ) {
					jQuery( 'form.sfwd-mark-complete' )[0].submit();
				}
            },
            error: function(XMLHttpRequest, textStatus, errorThrown) {
				localStorage.setItem( current_key, JSON.stringify( data ) );	            
            }

        });
	}
    jQuery('form.sfwd-mark-complete').one('submit', function(evnt){
    	evnt.preventDefault();
		end_activetimer = Date.now(); 
	    activetimer = ((end_activetimer - start_activetimer)/1000) - idletimer;
	    sendTimeTrackingData( Math.round( activetimer ), false );
		start_activetimer = Date.now();
		resetTimeTrackingTimer();
	});
	window.addEventListener("beforeunload", function(event) {
		if(jQuery('.is-active-time-tracking-popup').length > 0){
			jQuery('.ld-dashboard-resume-timer').trigger('click');
		}
	    end_activetimer = Date.now(); 
	    activetimer = ((end_activetimer - start_activetimer)/1000) - idletimer;
	    sendTimeTrackingData( Math.round( activetimer ) );	    
	});

	jQuery(window).on('load', function(){		
		setTimeout(function(){
			sendFailedTimeTrackingRequests();
		}, 2000);

	    start_activetimer = Date.now(); 
	    window.addEventListener('visibilitychange', function (e) {
	    	if (jQuery('.is-active-time-tracking-popup').length < 1){
	        	calculateTimeTrackingIdleTime();
	    	}
	    });
	});
	
	jQuery(document).on('click', '.ld-dashboard-resume-timer', function(){
		end_ideltimer = Date.now();
		idletimer = idletimer + ((end_ideltimer - start_idletimer)/1000);
		jQuery('.is-active-time-tracking-popup').remove();
		console.log(idletimer);
	});

};
timeSpentOnPage();

