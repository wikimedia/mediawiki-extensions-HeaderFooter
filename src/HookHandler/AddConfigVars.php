<?php

namespace MediaWiki\Extension\HeaderFooter\HookHandler;

use MediaWiki\Config\Config;
use MediaWiki\ResourceLoader\Hook\ResourceLoaderGetConfigVarsHook;

class AddConfigVars implements ResourceLoaderGetConfigVarsHook {

	/**
	 * @inheritDoc
	 */
	public function onResourceLoaderGetConfigVars( array &$vars, $skin, Config $config ): void {
		global $egHeaderFooterEnableAsyncHeader, $egHeaderFooterEnableAsyncFooter;

		$vars['egHeaderFooter'] = [
			'enableAsyncHeader' => $egHeaderFooterEnableAsyncHeader,
			'enableAsyncFooter' => $egHeaderFooterEnableAsyncFooter,
		];
	}
}
