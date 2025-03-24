<?php

namespace MediaWiki\Extension\HeaderFooter\HookHandler;

use MediaWiki\Hook\GetDoubleUnderscoreIDsHook;

class AddMagicWords implements GetDoubleUnderscoreIDsHook {

	/**
	 * @inheritDoc
	 */
	public function onGetDoubleUnderscoreIDs( &$doubleUnderscoreIDs ) {
		$doubleUnderscoreIDs[] = 'hf_nsheader';
		$doubleUnderscoreIDs[] = 'hf_header';
		$doubleUnderscoreIDs[] = 'hf_footer';
		$doubleUnderscoreIDs[] = 'hf_nsfooter';
	}
}
