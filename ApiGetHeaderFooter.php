<?php

/**
 * API module for MediaWiki's HeaderFooter extension.
 *
 * @author James Montalvo
 * @since Version 3.0
 */

use Wikimedia\ParamValidator\ParamValidator;
use MediaWiki\MediaWikiServices;

/**
 * API module to review revisions
 */
class ApiGetHeaderFooter extends ApiBase {

	public function execute() {

		$params = $this->extractRequestParams();
		$contextTitle = Title::newFromDBkey( $params['contexttitle'] );
		if ( ! $contextTitle ) {
			$this->dieWithError( "Not a valid contexttitle.", 'notarget' );
		}

		$messageId = $params['messageid'];

		$messageText = wfMessage( $messageId )->title( $contextTitle )->text();

		// don't need to bother if there is no content.
		if ( empty( $messageText ) ) {
			$messageText = '';
		}

		if ( wfMessage( $messageId )->inContentLanguage()->isBlank() ) {
			$messageText = '';
		}

		$messageText = MediaWikiServices::getInstance()->getParser()->parse(
			$messageText,
			$contextTitle,
			ParserOptions::newFromUser( $this->getUser() )
		)->getText();

		$this->getResult()->addValue( null, $this->getModuleName(), array( 'result' => $messageText ) );

	}

	public function getAllowedParams() {
		return array(
			'contexttitle' => array(
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_TYPE => 'string'
			),
			'messageid' => array(
				ParamValidator::PARAM_REQUIRED => true,
				ParamValidator::PARAM_TYPE => 'string'
			)
		);
	}

	/**
	 * @see ApiBase::getExamplesMessages()
	 */
	protected function getExamplesMessages() {
		return array(
			'action=getheaderfooter&contexttitle=Main_Page&messageid=Hf-nsfooter-'
				=> 'apihelp-getheaderfooter-example-1',
		);
	}

	public function mustBePosted() {
		return false;
	}

	public function isWriteMode() {
		return false;
	}

}
