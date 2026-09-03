<?php

namespace MediaWiki\Extension\HeaderFooter\HookHandler;

use MediaWiki\Content\Hook\ContentAlterParserOutputHook;
use MediaWiki\Context\RequestContext;
use MediaWiki\Parser\ParserOutput;
use MediaWiki\Title\Title;

class AddHeaderFooter implements ContentAlterParserOutputHook {

	/**
	 * @param RequestContext $context
	 * @param ParserOutput $parserOutput
	 * @return bool
	 */
	private function shouldSkip( $context, $parserOutput ): bool {
		if (
			$context->getActionName() !== 'view' ||
			MW_ENTRY_POINT === 'api' ||
			$parserOutput->getExtensionData( 'HeaderFooter-added' ) ||
			!$parserOutput->hasText()
		) {
			return true;
		}

		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function onContentAlterParserOutput( $content, $title, $parserOutput ) {
		$context = RequestContext::getMain();
		if ( $this->shouldSkip( $context, $parserOutput ) ) {
			return;
		}

		$ns = $title->getNsText();
		$name = $title->getPrefixedDBKey();

		$nsheader = $this->generateHeaderFooter( 'hf_nsheader', 'hf-nsheader', $ns, $parserOutput, $title );
		$header   = $this->generateHeaderFooter( 'hf_header', 'hf-header', $name, $parserOutput, $title );
		$footer   = $this->generateHeaderFooter( 'hf_footer', 'hf-footer', $name, $parserOutput, $title );
		$nsfooter = $this->generateHeaderFooter( 'hf_nsfooter', 'hf-nsfooter', $ns, $parserOutput, $title );

		$text = $parserOutput->getContentHolderText();
		$parserOutput->setContentHolderText( $nsheader . $header . $text . $footer . $nsfooter );
		$parserOutput->setExtensionData( 'HeaderFooter-added', true );

		global $egHeaderFooterEnableAsyncHeader, $egHeaderFooterEnableAsyncFooter;
		if ( $egHeaderFooterEnableAsyncFooter || $egHeaderFooterEnableAsyncHeader ) {
			$context->getOutput()->addModules( 'ext.headerfooter.dynamicload' );
		}
	}

	/**
	 * Generates a header or footer unless disabled via a magic word.
	 *
	 * @param string $magicWord Magic word that disables this section
	 * @param string $hfType Type of Header/Footer
	 * @param string $pageIdentifier Namespace or prefixed title
	 * @param ParserOutput $parserOutput
	 * @param Title $title Title of the page being rendered, used as parsing
	 *  context so magic words like {{FULLPAGENAME}} and parser functions like
	 *  {{#show:}} resolve relative to the actual page instead of falling back
	 *  to the (possibly unset) global $wgTitle, which is not reliably set
	 *  outside of classic index.php page views (e.g. REST API based exports).
	 * @return string
	 */
	private function generateHeaderFooter(
		$magicWord, $hfType, $pageIdentifier, $parserOutput, Title $title
	): string {
		if ( $parserOutput->getPageProperty( $magicWord ) !== null ) {
			return '';
		}

		$msgId = "$hfType-$pageIdentifier";
		$div = "<div class='$hfType' id='$msgId'>";

		global $egHeaderFooterEnableAsyncHeader, $egHeaderFooterEnableAsyncFooter;
		$isHeader = str_contains( $hfType, 'header' );
		$isFooter = str_contains( $hfType, 'footer' );
		if ( ( $egHeaderFooterEnableAsyncFooter && $isFooter )
			|| ( $egHeaderFooterEnableAsyncHeader && $isHeader )
		) {
			// Empty div, JS will populate it
			return "$div</div>";
		}

		$msg = wfMessage( $msgId )->page( $title );
		if ( $msg->isBlank() ) {
			return '';
		}

		return $div . $msg->parse() . '</div>';
	}
}
