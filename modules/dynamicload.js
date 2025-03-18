const egHeaderFooter = mw.config.get( 'egHeaderFooter' );

let extHeaderFooterBlocks;
if ( egHeaderFooter.enableAsyncHeader ) {
	extHeaderFooterBlocks = [ 'hf-nsheader', 'hf-header' ];
} else {
	extHeaderFooterBlocks = [];
}
extHeaderFooterBlocks = extHeaderFooterBlocks.concat( [ 'hf-footer', 'hf-nsfooter' ] );

for ( let i = 0; i < extHeaderFooterBlocks.length; i++ ) {
	const block = extHeaderFooterBlocks[ i ];

	$( '.' + block ).each( ( index, element ) => {

		// FIXME: At some point, put some method of indicating unloaded content here

		// FIXME: At some point, add method to further delay loading of dynamic
		//        footers. Headers should be loaded right away, but footers should
		//        only be loaded if the user can see them (or is scrolling toward
		//        them).

		// Message ID of block (header or footer) is in the HTML ID. hf-nsheader
		// will have an ID like hf-nsheader-Help for the help namespace.
		const msgId = $( element ).attr( 'id' );

		$.get(
			mw.config.get( 'wgScriptPath' ) + '/api.php',
			{
				action: 'getheaderfooter',
				messageid: msgId,
				contexttitle: mw.config.get( 'wgPageName' ),
				format: 'json'
			},
			( response ) => {
				// var blockText = response.query.allmessages[0]["*"];
				const blockText = response.getheaderfooter.result;
				$( '#' + msgId ).html( blockText );
				$( '#' + msgId ).find( '#headertabs' ).each( ( tabIndex, tabElement ) => {
					$( tabElement ).tabs();
				} );
			}
		);

	} );

}
