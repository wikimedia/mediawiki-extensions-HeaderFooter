<?php

use MediaWiki\Parser\ParserOutput;

/**
 * @package HeaderFooter
 */
class HeaderFooter {

	public static function onBeforePageDisplay( OutputPage $out, Skin $skin ): void {
		$action = $out->getRequest()->getVal( "action" );
		if ( ( $action == 'edit' ) || ( $action == 'submit' ) || ( $action == 'history' ) ) {
			return;
		}

		$title = $out->getTitle();
		$ns = $title->getNsText();
		$name = $title->getPrefixedDBKey();
		$meta = $out->getMetadata();

		$nsheader = self::conditionalInclude( 'hf_nsheader', 'hf-nsheader', $ns, $meta );
		$header   = self::conditionalInclude( 'hf_header', 'hf-header', $name, $meta );
		$footer   = self::conditionalInclude( 'hf_footer', 'hf-footer', $name, $meta );
		$nsfooter = self::conditionalInclude( 'hf_nsfooter', 'hf-nsfooter', $ns, $meta );

		$text = $out->mBodytext;
		$out->mBodytext = $nsheader . $header . $text . $footer . $nsfooter;

		global $egHeaderFooterEnableAsyncHeader, $egHeaderFooterEnableAsyncFooter;
		if ( $egHeaderFooterEnableAsyncFooter || $egHeaderFooterEnableAsyncHeader ) {
			$out->addModules( 'ext.headerfooter.dynamicload' );
		}
	}

	/**
	 * @param string[] &$doubleUnderscoreIDs
	 */
	public static function onGetDoubleUnderscoreIDs( array &$doubleUnderscoreIDs ): void {
		$doubleUnderscoreIDs[] = 'hf_nsheader';
		$doubleUnderscoreIDs[] = 'hf_header';
		$doubleUnderscoreIDs[] = 'hf_footer';
		$doubleUnderscoreIDs[] = 'hf_nsfooter';
	}

	/**
	 * Verifies & Strips ''disable command'', returns $content if all OK.
	 *
	 * @param string $disableWord
	 * @param string $class
	 * @param string $unique
	 * @param ParserOutput $meta
	 * @return null|string
	 */
	public static function conditionalInclude( string $disableWord, string $class, string $unique, ParserOutput $meta ):
	?string {
		if ( $meta->getPageProperty( $disableWord ) !== null ) {
			return null;
		}

		$msgId = "$class-$unique";
		// also HTML ID
		$div = "<div class='$class' id='$msgId'>";

		global $egHeaderFooterEnableAsyncHeader, $egHeaderFooterEnableAsyncFooter;

		$isHeader = $class === 'hf-nsheader' || $class === 'hf-header';
		$isFooter = $class === 'hf-nsfooter' || $class === 'hf-footer';

		if ( ( $egHeaderFooterEnableAsyncFooter && $isFooter )
			 || ( $egHeaderFooterEnableAsyncHeader && $isHeader ) ) {

			// Just drop an empty div into the page. Will fill it with async
			// request after page load
			return $div . '</div>';
		} else {
			$msgText = wfMessage( $msgId )->parse();

			// don't need to bother if there is no content.
			if ( empty( $msgText ) ) {
				return null;
			}

			if ( wfMessage( $msgId )->inContentLanguage()->isBlank() ) {
				return null;
			}

			return $div . $msgText . '</div>';
		}
	}

	public static function onResourceLoaderGetConfigVars( array &$vars ): void {
		global $egHeaderFooterEnableAsyncHeader, $egHeaderFooterEnableAsyncFooter;

		$vars['egHeaderFooter'] = [
			'enableAsyncHeader' => $egHeaderFooterEnableAsyncHeader,
			'enableAsyncFooter' => $egHeaderFooterEnableAsyncFooter,
		];
	}
}
