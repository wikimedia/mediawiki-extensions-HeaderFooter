<?php

/**
 * API module for MediaWiki's HeaderFooter extension.
 *
 * @author James Montalvo
 * @since Version 3.0
 */

use MediaWiki\Api\ApiBase;
use MediaWiki\MediaWikiServices;
use MediaWiki\Parser\ParserOptions;
use MediaWiki\Title\Title;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * API module to review revisions
 */
class ApiGetHeaderFooter extends ApiBase {

	public function execute() {
		$params = $this->extractRequestParams();
		$contextTitle = Title::newFromDBkey( $params['contexttitle'] );
		if ( !$contextTitle ) {
			$this->dieWithError( "Not a valid contexttitle.", 'notarget' );
		}

		$messageId = $params['messageid'];

		$messageText = $this->msg( $messageId )->page( $contextTitle )->text();

		// don't need to bother if there is no content.
		if ( empty( $messageText ) ) {
			$messageText = '';
		}

		if ( $this->msg( $messageId )->inContentLanguage()->isBlank() ) {
			$messageText = '';
		}

		$messageText = MediaWikiServices::getInstance()->getParser()->parse(
			$messageText,
			$contextTitle,
			ParserOptions::newFromUser( $this->getUser() )
		)->getText();

		$this->getResult()->addValue( null, $this->getModuleName(), [ 'result' => $messageText ] );
	}

	/** @inheritDoc */
	public function getAllowedParams() {
		return [
			'contexttitle' => [
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_TYPE => 'string'
			],
			'messageid' => [
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_TYPE => 'string'
			]
		];
	}

	/** @inheritDoc */
	protected function getExamplesMessages() {
		return [
			'action=getheaderfooter&contexttitle=Main_Page&messageid=Hf-nsfooter-'
				=> 'apihelp-getheaderfooter-example-1',
		];
	}

	/** @inheritDoc */
	public function mustBePosted() {
		return false;
	}

	/** @inheritDoc */
	public function isWriteMode() {
		return false;
	}

}
