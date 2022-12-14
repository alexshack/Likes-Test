
let l_buttons = document.querySelectorAll('.like__btn');

document.addEventListener("DOMContentLoaded", function() {
	LikesCall();
});

function LikesCall() {
	Array.prototype.forEach.call( l_buttons, function( but )
	{
		var form = but.parentElement;
		var post_id = form.dataset.post;
		var like_count;
		but.addEventListener( 'click', function( e )
		{
			
			var like_type = 0;
			if ( this.classList.contains('like__btn__plus') ) {
				like_type = 1;
				like_count = this.nextElementSibling;
			} else {
				like_type = -1;
				like_count = this.previousElementSibling;
			}

			var likes_old = parseInt(like_count.innerHTML);
			var likes_new = likes_old + like_type;
			var color = '';
			if (likes_new != 0) {
				if( likes_new > 0 ) {
					color = 'positive';
				} else {
					color = 'negative';
				}

			}

		    var data = { action: 'like', nonce : myajax.nonce, post_id: post_id, like_type: like_type};
		    jQuery.post( myajax.url, data, function( response ) {
				if(/^\d+$/.test(response)) {
					like_count.innerHTML = likes_new;
					like_count.className = 'like__count';
					like_count.classList.add(color);
		    		form.dataset.likes = like_type;	
				}
				else alert( response );
			});



		});
	});
}