'use strict';

class AIOVGPlaylistButtonElement extends HTMLElement {

    /**
     * Element created.
     */
    constructor() {
        super(); 
        
        // Set references to the DOM elements used by the component       
        this._toggleDropdownButtonEl = null;
        this._closeDropdownButtonEl  = null;
        this._dropdownEl             = null;
        this._playlistTitleInputEl   = null;  
        this._createPlaylistButtonEl = null;        

        // Set references to the private properties used by the component
        this._isRendered = false; 
        this._isLoading  = false; 
        this._options    = {};  
    }

     /**
     * Browser calls this method when the element is added to the document.
     * (can be called many times if an element is repeatedly added/removed)
     */
     connectedCallback() {    
        if ( this._isRendered ) {
            return false; 
        }

        if ( this.postId == 0 ) {
            return false;
        }

        this._options = window.aiovg_playlists;

        this._isRendered = true;       

        this._render();

        this._toggleDropdownButtonEl = this.querySelector( '.aiovg-button-toggle-dropdown' );  
        this._closeDropdownButtonEl  = this.querySelector( '.aiovg-button-close-dropdown' );
        this._dropdownEl             = this.querySelector( '.aiovg-dropdown' ); 
        this._dropdownHeaderEl       = this.querySelector( '.aiovg-dropdown-header' ); 
        this._dropdownBodyEl         = this.querySelector( '.aiovg-dropdown-body' ); 
        this._dropdownFooterEl       = this.querySelector( '.aiovg-dropdown-footer' ); 
        this._playlistTitleInputEl   = this.querySelector( '.aiovg-form-control-title' );
        this._createPlaylistButtonEl = this.querySelector( '.aiovg-button-create-playlist' );   
        
        this._toggleDropdownButtonEl.addEventListener( 'click', ( event ) => this._toggleDropdown( event ) );
        this._closeDropdownButtonEl.addEventListener( 'click', ( event ) => this._closeDropdown( event ) );
        this._playlistTitleInputEl.addEventListener( 'input', ( event ) => this._validatePlaylistTitleField( event ) );
        this._createPlaylistButtonEl.addEventListener( 'click', ( event ) => this._createPlaylist( event ) );
        
        jQuery( this ).on( 'change', 'input[type=checkbox]', ( event ) => this._togglePlaylistItem( event ) );      

        this._load();       
    }

    /**
     * Browser calls this method when the element is removed from the document.
     * (can be called many times if an element is repeatedly added/removed)
     */
    disconnectedCallback() {
        this._toggleDropdownButtonEl.removeEventListener( 'click', ( event ) => this._toggleDropdown( event ) );
        this._closeDropdownButtonEl.removeEventListener( 'click', ( event ) => this._closeDropdown( event ) );
        this._playlistTitleInputEl.removeEventListener( 'input', ( event ) => this._validatePlaylistTitleField( event ) );
        this._createPlaylistButtonEl.removeEventListener( 'click', ( event ) => this._createPlaylist( event ) )

        jQuery( this ).off( 'change', 'input[type=checkbox]', ( event ) => this._togglePlaylistItem( event ) ); 
    }

    /**
     * Define getters and setters for attributes.
     */ 

    get postId() {
        return parseInt( this.getAttribute( 'post_id' ) || 0 );
    }

    get userId() {
        return parseInt( this._options.user_id );
    }

    get limit() {
        return parseInt( this._options.limit );
    }

    get isLoaded() {
        return this.hasAttribute( 'loaded' );
    }

    set isLoaded( value ) {
        return this.setAttribute( 'loaded', value );
    }

    /**
     * Define private methods.
     */ 

    _render() {
        let html = '';

        // Button: Toggle dropdown
        html += '<button type="button" class="aiovg-button-toggle-dropdown">';
        html += '<svg xmlns="http://www.w3.org/2000/svg" fill="none" width="16" height="16" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="aiovg-flex-shrink-0">' +
            '<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 16.875h3.375m0 0h3.375m-3.375 0V13.5m0 3.375v3.375M6 10.5h2.25a2.25 2.25 0 0 0 2.25-2.25V6a2.25 2.25 0 0 0-2.25-2.25H6A2.25 2.25 0 0 0 3.75 6v2.25A2.25 2.25 0 0 0 6 10.5Zm0 9.75h2.25A2.25 2.25 0 0 0 10.5 18v-2.25a2.25 2.25 0 0 0-2.25-2.25H6a2.25 2.25 0 0 0-2.25 2.25V18A2.25 2.25 0 0 0 6 20.25Zm9.75-9.75H18a2.25 2.25 0 0 0 2.25-2.25V6A2.25 2.25 0 0 0 18 3.75h-2.25A2.25 2.25 0 0 0 13.5 6v2.25a2.25 2.25 0 0 0 2.25 2.25Z" />' +
        '</svg>';
        html += this._options.i18n.button_add_to_playlist;
        html += '</button>';

        // Dropdown
        html += '<div class="aiovg-dropdown" hidden>';

        // Dropdown: Header
        html += '<div class="aiovg-dropdown-header">';

        html += '<div class="aiovg-dropdown-status">';
        html += this._options.i18n.dropdown_header_text;	
        html += '</div>';

        html += '<button type="button" class="aiovg-button-close-dropdown">';
		html += '<svg xmlns="http://www.w3.org/2000/svg" fill="none" width="16" height="16" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="aiovg-flex-shrink-0">' +
		    '<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />' +
		'</svg>';
		html += '</button>';
        
        html += '</div>';

        // Dropdown: Body
        html += '<div class="aiovg-dropdown-body">';

        html += '<div class="aiovg-dropdown-no-items">';
        html += '<label class="aiovg-text-muted aiovg-text-small">';
        html += this._options.i18n.playlists_not_found;
        html += '</label>';
        html += '</div>';

        html += '</div>';

        // Dropdown: Footer
        html += '<div class="aiovg-dropdown-footer">';

        html += '<div class="aiovg-form-group">';
        html += '<input type="text" class="aiovg-form-control aiovg-form-control-title" placeholder="' + this._options.i18n.title_field_placeholder + '" />';
        html += '<button type="button" class="aiovg-button-create-playlist" title="' +  this._options.i18n.button_add_playlist + '">';
        html += '<svg xmlns="http://www.w3.org/2000/svg" fill="none" width="16" height="16" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="aiovg-flex-shrink-0">' +
            '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />' +
        '</svg>';		
        html += '</button>';
        html += '</div>';

        html += '<div class="aiovg-dropdown-status aiovg-text-muted aiovg-text-small aiovg-hide-if-empty">';
        html += '</div>';

        html += '</div>';

        html += '</div>';

        this.innerHTML = html;
    }

    _load() {
        let data = {
            'action': 'aiovg_get_playlists_info',
            'user_id': this.userId,
            'post_id': this.postId,
            'security': this._options.ajax_nonce
        };

        this._fetch( data, ( response ) => {
            this.isLoaded = true;

            if ( response.status == 'success' ) {
                this._dropdownBodyEl.insertAdjacentHTML( 'afterbegin', response.html );

                let playlistsCount = this._dropdownEl.querySelectorAll( '.aiovg-item' ).length;

                if ( playlistsCount > 0 ) {
                    this._dropdownEl.querySelector( '.aiovg-dropdown-no-items' ).hidden = true;
                }

                if ( this.limit > 0 ) {
                    if ( playlistsCount >= this.limit ) {
                        this._dropdownFooterEl.querySelector( '.aiovg-form-group' ).remove();
                        this._dropdownFooterEl.querySelector( '.aiovg-dropdown-status' ).innerHTML = this._options.i18n.note_limit_reached;
                    } else {
                        this._dropdownFooterEl.querySelector( '.aiovg-dropdown-status' ).innerHTML = this._options.i18n.note_limit;
                    }
                }
            }    
        });
    }

    _toggleDropdown( event ) {
        if ( this.userId === 0 ) {
            alert( this._options.i18n.alert_login_required );
            return false;
        }

        if ( ! this.isLoaded ) {
            return false;
        }

        this._dropdownEl.hidden = ! this._dropdownEl.hidden;
    }

    _closeDropdown( event ) {
        this._dropdownEl.hidden = true;
    }

    _validatePlaylistTitleField( event ) {
        let playlistTitle = this._playlistTitleInputEl.value.trim();

        if ( playlistTitle == '' ) {
            this._playlistTitleInputEl.classList.add( 'aiovg-field-invalid' );
        } else {
            this._playlistTitleInputEl.classList.remove( 'aiovg-field-invalid' );
        }
    }

    _createPlaylist( event ) {
        let playlistTitle = this._playlistTitleInputEl.value.trim();

        if ( playlistTitle == '' ) {
            this._playlistTitleInputEl.classList.add( 'aiovg-field-invalid' );
            return false;
        }

        if ( this._isLoading ) {
            return false;
        }

        this._isLoading = true;
        this._createPlaylistButtonEl.querySelector( 'svg' ).classList.add( 'aiovg-animate-rotate' );

        let data = {
            'action': 'aiovg_create_playlist',                    
            'user_id': this.userId,
            'post_id': this.postId,
            'playlist_title': playlistTitle,
            'security': this._options.ajax_nonce
        };
    
        this._fetch( data, ( response ) => {
            this._playlistTitleInputEl.value = '';

            this._isLoading = false;
            this._createPlaylistButtonEl.querySelector( 'svg' ).classList.remove( 'aiovg-animate-rotate' );

            if ( response.status == 'success' ) {
                this._dropdownBodyEl.querySelector( '.aiovg-dropdown-no-items' ).hidden = true;
                this._dropdownBodyEl.insertAdjacentHTML( 'beforeend', response.html );

                if ( this.limit > 0 ) {
                    let playlistsCount = this._dropdownEl.querySelectorAll( '.aiovg-item' ).length;

                    if ( playlistsCount >= this.limit ) {
                        this._dropdownFooterEl.querySelector( '.aiovg-form-group' ).remove();
                        this._dropdownFooterEl.querySelector( '.aiovg-dropdown-status' ).innerHTML = this._options.i18n.note_limit_reached;
                    }
                }

                this._dropdownEl.hidden = true;
            } else {
                alert( response.message );
            }                    
        });     
    }

    _togglePlaylistItem( event ) {
        let html = '<span class="aiovg-spinner"></span> ';
        html += '<span class="aiovg-text-success aiovg-text-small">';
        html += ( event.target.checked ? this._options.i18n.status_added : this._options.i18n.status_removed );
        html += '</span>';

        let statusEl = this._dropdownHeaderEl.querySelector( '.aiovg-dropdown-status' );
        statusEl.innerHTML = html;

        let data = {
            'action': ( event.target.checked ? 'aiovg_add_to_playlist' : 'aiovg_remove_from_playlist' ),                   
            'user_id': this.userId,
            'post_id': this.postId,
            'playlist_id': event.target.value,
            'security': this._options.ajax_nonce
        };
        
        this._fetch( data, ( response ) => {
            statusEl.innerHTML = this._options.i18n.dropdown_header_text;

            if ( response.status == 'error' ) {
                alert( response.message );
            }
        });  
    }

    _fetch( data, callback ) {
        jQuery.post( this._options.ajax_url, data, callback, 'json' ); 						
    }

}

(function( $ ) {	

    var aiovg = window.aiovg_playlists;    

	/**
	 * Called when the page has loaded.
	 */
	$(function() {                      

        // Register custom element.
        customElements.define( 'aiovg-playlist-button', AIOVGPlaylistButtonElement );

        // Close opened playlists dropdown    
        document.addEventListener( 'click', ( event ) => {
            const self = event.target.closest( 'aiovg-playlist-button' );

            document.querySelectorAll( 'aiovg-playlist-button' ).forEach(( el ) => {
                if ( el !== self ) { 
                    el.querySelector( '.aiovg-dropdown' ).hidden = true;
                }	
            });		
        });

        // My Playlists
        $( '#aiovg-playlists .aiovg-item-playlist' ).each(function() {
            var $wrapperEl = $( this ); 

            var userId     = parseInt( aiovg.user_id );
            var playlistId = parseInt( $wrapperEl.data( 'playlist_id' ) );           

            // Insert the playlist title edit field
            var insertEditField = function( title ) {
                var html = '<input type="text" class="aiovg-form-control aiovg-form-control-title aiovg-flex-grow-1" placeholder="' + aiovg.i18n.title_field_placeholder + '" value="' + title + '" />';
                html += '<button type="button" class="aiovg-button-update-playlist aiovg-no-margin">';
                html += aiovg.i18n.button_update;
                html += '</button>';

                $wrapperEl.find( '.aiovg-title' ).html( html );
            };

            // Replace title with an input field
            $wrapperEl.on( 'click', '.aiovg-button-edit-playlist', function() {
                var title = $wrapperEl.find( '.aiovg-title div' ).html();
                insertEditField( title );                
            });

            // Update playlist, replace input field with the title text
            $wrapperEl.on( 'click', '.aiovg-button-update-playlist', function() {
                var playlistTitle = $wrapperEl.find( '.aiovg-form-control-title' ).val();

                var html = ' <div class="aiovg-flex-grow-1">' + playlistTitle + '</div>';
                html += '<button type="button" class="aiovg-button-edit-playlist aiovg-no-margin aiovg-leading-none">';
                html += '<svg xmlns="http://www.w3.org/2000/svg" fill="none" width="16" height="16" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="aiovg-flex-shrink-0">';
                html += '<path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />';
                html += '</svg>';
                html += '</button>';

                $wrapperEl.find( '.aiovg-title' ).html( html );

                var data = {
                    'action': 'aiovg_update_playlist',                    
                    'user_id': userId,
                    'playlist_id': playlistId,
                    'playlist_title': playlistTitle,
                    'security': aiovg.ajax_nonce
                };
                
                $.post( aiovg.ajax_url, data, function( response ) {                     
                    if ( response.status == 'error' ) {   
                        insertEditField( playlistTitle );
                        alert( response.message );
                    }
                }, 'json');                
            });
        });

	});

})( jQuery );
