( function( $ ) {

	/**
	 * Main container object.
	 */
	function WC_MNM_Select_Layout( container ) {

		var self       = this;
		this.container = container;

		this.$selects =  container.$mnm_form.find( '.mnm_select' );

		/**
		 * Init.
		 */

		this.initialize = function() {
			
			if( container.$mnm_form.hasClass( 'layout_select' ) ) {
				self.$selects.on( 'change', this.set_config );
				self.container.$mnm_form.on( 'wc-mnm-initialized', this.set_config );
				self.container.$mnm_reset.on( 'wc-mnm-reset-configuration', this.reset );
			}

		};


		/**
		 * Update Quantities.
		 */
		this.set_config = function( event, container ) {

			var config = {};
			var id;

			self.$selects.each( function(i) {
				id = parseInt( Number( $(this).val() ), 10 );
	
				if( id <= 0 ) {
					return;
				}
		
				if( id in config ) {
					config[ id ] = config[ id ] + 1;
				} else {
					config[ id ] = 1;
				}
				
			});


			self.container.update_container( $(this), config );

		};


		/**
		 * Reset Quantities.
		 */
		this.reset = function( event, container ) {

			var default_selection;

			self.$selects.each( function(i) {
				default_selection = 'undefined' !== $(this).data( 'default' ) ? $(this).data( 'default' ) : '';
				$(this).val( default_selection );			
			});

		};




	} // End WC_MNM_Select_Layout.

	/*-----------------------------------------------------------------*/
	/*  Initialization.                                                */
	/*-----------------------------------------------------------------*/

	$( 'body' ).on( 'wc-mnm-initializing', function( e, container ) {
		var layout = new WC_MNM_Select_Layout( container );
		layout.initialize();
	});

} ) ( jQuery );