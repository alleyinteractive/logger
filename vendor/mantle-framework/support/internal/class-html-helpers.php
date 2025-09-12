<?php
/**
 * Crawler_Helpers class file
 *
 * phpcs:disable
 *
 * @package Mantle
 */

namespace Mantle\Support\Internal;

/**
 * Helpers that exist in Symfony's DomCrawler but are not accessible because
 * they are private.
 *
 * These methods are kept as-is and not converted to WordPress-style code to
 * make updating them easier.
 *
 * @access private
 */
readonly class HTML_Helpers {
	/**
	 * Helper function for getting a body element
	 * from an HTML fragment
	 *
	 * @access private
	 *
	 * @param string $html A fragment of HTML code
	 * @param string $charset
	 * @return \DOMNode The body node containing child nodes created from the HTML fragment
	 */
	public static function get_body_node_from_html_fragment( $html, string $charset = 'UTF-8' ): \DOMNode {
		$html = '<html><body>' . $html . '</body></html>';

		return self::parseXhtml( $html, $charset )->getElementsByTagName( 'body' )->item( 0 );
	}

	/**
	 * Function originally taken from Symfony\Component\DomCrawler\Crawler
	 * (c) Fabien Potencier <fabien@symfony.com>
	 * License: MIT
	 *
	 * @access private
	 */
	private static function parseXhtml( string $htmlContent, string $charset = 'UTF-8' ): \DOMDocument {
		$htmlContent = self::convertToHtmlEntities( $htmlContent, $charset );

		$internalErrors = libxml_use_internal_errors( true );

		$dom                  = new \DOMDocument( '1.0', $charset );
		$dom->validateOnParse = true;

		if ( '' !== trim( $htmlContent ) ) {
			// PHP DOMDocument->loadHTML method tends to "eat" closing tags in html strings within script elements
			// Option LIBXML_SCHEMA_CREATE seems to prevent this
			// see https://stackoverflow.com/questions/24575136/domdocument-removes-html-tags-in-javascript-string.
			@$dom->loadHTML( $htmlContent, \LIBXML_SCHEMA_CREATE );
		}

		libxml_use_internal_errors( $internalErrors );

		return $dom;
	}

	/**
	 * Converts charset to HTML-entities to ensure valid parsing.
	 *
	 * @throws \Exception Thrown on internal error.
	 * @access private
	 */
	private static function convertToHtmlEntities( string $htmlContent, string $charset = 'UTF-8' ): string {
		set_error_handler( static fn () => throw new \Exception() );

		try {
			return mb_encode_numericentity( $htmlContent, [ 0x80, 0x10FFFF, 0, 0x1FFFFF ], $charset );
		} catch ( \Exception | \ValueError ) {
			try {
					$htmlContent = iconv( $charset, 'UTF-8', $htmlContent );
					$htmlContent = mb_encode_numericentity( $htmlContent, [ 0x80, 0x10FFFF, 0, 0x1FFFFF ], 'UTF-8' );
			} catch ( \Exception | \ValueError ) {}

			return $htmlContent;
		} finally {
			restore_error_handler();
		}
	}
}
