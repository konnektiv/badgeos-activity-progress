(function ($) {

	$( '#badgeos_activity_progress_type' ).select2({
		ajax: {
			url: ajaxurl,
			type: 'POST',
			data: function ( term ) {
				return {
					q: term,
					action: 'get-achievement-types'
				};
			},
			results: function ( results, page ) {
				return {
					results: results.data
				};
			}
		},
		id: function ( item ) {
			return item.name;
		},
		formatResult: function ( item ) {
			return item.label;
		},
		formatSelection: function ( item ) {
			return item.label;
		},
		placeholder: badgeos_shortcode_embed_messages.post_type_placeholder,
		allowClear: true,
		multiple: true
	});

	$( '#badgeos_activity_progress_user_id' ).select2({
		ajax: {
			url: ajaxurl,
			type: 'POST',
			data: function( term ) {
				return {
					q: term,
					action: 'get-users'
				};
			},
			results: function( results, page ) {
				return {
					results: results.data
				};
			}
		},
		id: function( item ) {
			return item.ID;
		},
		formatResult: function ( item ) {
			return item.user_login;
		},
		formatSelection: function ( item ) {
			return item.user_login;
		},
		placeholder: badgeos_shortcode_embed_messages.user_placeholder,
		allowClear: true
	});

}(jQuery));
