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
				self.$selects.on( 'change', { container: container }, this.handle_change );
				self.container.$mnm_form.on( 'wc-mnm-initialized', this.set_config );
				self.container.$mnm_reset.on( 'wc-mnm-reset-configuration', this.reset );
			}

		};

		/**
		 * Handle input change.
		 */
		this.handle_change = function( event ) {
			self.set_config( event, event.data.container );
		};

		/**
		 *  Fetch config.
		 */
		this.get_config = function( container ) {
			var config = container.price_data.quantities;
			for ( var prop in config ) {
				config[prop] = 0;
			}

			self.$selects.each( function() {
				var id = parseInt( Number( $(this).val() ), 10 );
	
				if( id <= 0 ) {
					return;
				}
		
				if( id in config ) {
					config[ id ] = config[ id ] + 1;
				} else {
					config[ id ] = 1;
				}
				
			});

			return config;
		};

		/**
		 * Update Quantities.
		 */
		this.set_config = function( event, container ) {
			container.update_container( event.currentTarget, self.get_config( container ) );
		};

		/**
		 * Reset Quantities.
		 */
		this.reset = function( event, container ) {
			
			var default_selection;

			self.$selects.each( function() {
				default_selection = 'undefined' !== $(this).data( 'default' ) ? $(this).data( 'default' ) : '';
				$(this).val( default_selection );			
			});

			self.set_config( event, container );

			return false; // This tells MNM not to run the update_container();

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