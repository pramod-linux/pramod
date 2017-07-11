<?php
/**
 * Implements Special:Allpages
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * @file
 * @ingroup SpecialPage
 */

/**
 * Implements Special:Allpages
 *
 * @ingroup SpecialPage
 * @todo Rewrite using IndexPager
 */
class SpecialAllRejectPages extends IncludableSpecialPage {

	/**
	 * Maximum number of pages to show on single subpage.
	 *
	 * @var int $maxPerPage
	 */
	protected $maxPerPage = 345;

	/**
	 * Determines, which message describes the input field 'nsfrom'.
	 *
	 * @var string $nsfromMsg
	 */
	protected $nsfromMsg = 'allpagesfrom';

	/**
	 * Constructor
	 *
	 * @param string $name Name of the special page, as seen in links and URLs (default: 'Allpages')
	 */
	function __construct( $name = 'AllRejectpages' ) {
		parent::__construct( $name );
	}

	/**
	 * Entry point : initialise variables and call subfunctions.
	 *
	 * @param string $par Becomes "FOO" when called like Special:Allpages/FOO (default null)
	 */
	function execute( $par ) {
		$request = $this->getRequest();
		$out = $this->getOutput();

		$this->setHeaders();
		$this->outputHeader();
		$out->allowClickjacking();

		# GET values
		$from = $request->getVal( 'from', null );
		$to = $request->getVal( 'to', null );
		$namespace = $request->getInt( 'namespace' );
		$hideredirects = $request->getBool( 'hideredirects', false );

		$namespaces = $this->getLanguage()->getNamespaces();
		// echo '<pre>'; print_r($namespace); exit;
		// $out->setPageTitle(
		// 	( $namespace > 0 && array_key_exists( $namespace, $namespaces ) ) ?
		// 		$this->msg( 'allinnamespace', str_replace( '_', ' ', $namespaces[$namespace] ) ) :
		// 		$this->msg( 'allarticles' )
		// );
		$out->addModuleStyles( 'mediawiki.special' );

		if ( $par !== null ) {
			$this->showChunk( $namespace, $par, $to, $hideredirects );
		} elseif ( $from !== null && $to === null ) {
			$this->showChunk( $namespace, $from, $to, $hideredirects );
		} else {
			$this->showToplevel( $namespace, $from, $to, $hideredirects );
		}
	}

	/**
	 * Outputs the HTMLForm used on this page
	 *
	 * @param int $namespace A namespace constant (default NS_MAIN).
	 * @param string $from DbKey we are starting listing at.
	 * @param string $to DbKey we are ending listing at.
	 * @param bool $hideRedirects Dont show redirects  (default false)
	 */
	protected function outputHTMLForm( $namespace = NS_MAIN,
		$from = '', $to = '', $hideRedirects = false
	) {
		$fields = [
			'from' => [
				'type' => 'text',
				'name' => 'from',
				'id' => 'nsfrom',
				'size' => 30,
				'label-message' => 'allpagesfrom',
				'default' => str_replace( '_', ' ', $from ),
			],
			'to' => [
				'type' => 'text',
				'name' => 'to',
				'id' => 'nsto',
				'size' => 30,
				'label-message' => 'allpagesto',
				'default' => str_replace( '_', ' ', $to ),
			],
			'namespace' => [
				'type' => 'namespaceselect',
				'name' => 'namespace',
				'id' => 'namespace',
				'label-message' => 'namespace',
				'all' => null,
				'value' => $namespace,
			],
			'hideredirects' => [
				'type' => 'check',
				'name' => 'hideredirects',
				'id' => 'hidredirects',
				'label-message' => 'allpages-hide-redirects',
				'value' => $hideRedirects,
			],
		];
		$form = HTMLForm::factory( 'table', $fields, $this->getContext() );
		$form->setMethod( 'get' )
			->setWrapperLegendMsg( 'allpages' )
			->setSubmitTextMsg( 'allpagessubmit' )
			->prepareForm()
			->displayForm( false );
	}

	/**
	 * @param int $namespace (default NS_MAIN)
	 * @param string $from List all pages from this name
	 * @param string $to List all pages to this name
	 * @param bool $hideredirects Dont show redirects (default false)
	 */
	function showToplevel( $namespace = NS_MAIN, $from = '', $to = '', $hideredirects = false ) {
		$from = Title::makeTitleSafe( $namespace, $from );
		$to = Title::makeTitleSafe( $namespace, $to );
		$from = ( $from && $from->isLocal() ) ? $from->getDBkey() : null;
		$to = ( $to && $to->isLocal() ) ? $to->getDBkey() : null;

		
		$this->showChunk( $namespace, $from, $to, $hideredirects );
	}


	function showChunk( $namespace = NS_MAIN, $from = false, $to = false, $hideredirects = false ) {
		$output = $this->getOutput();

		$fromList = $this->getNamespaceKeyAndText( $namespace, $from );
		$toList = $this->getNamespaceKeyAndText( $namespace, $to );
		$namespaces = $this->getContext()->getLanguage()->getNamespaces();
		$n = 0;
		$prevTitle = null;

			list( $namespace, $fromKey, $from ) = $fromList;
			list( , $toKey, $to ) = $toList;

			$dbr = wfGetDB( DB_REPLICA );

			// echo '<pre>'; print_r($conds); exit;
			$res = $dbr->select( 'moderation',
				[ 'mod_namespace', 'mod_title' ],
				['mod_rejected = 1'],
				__METHOD__,
				[
					'ORDER BY' => 'mod_title',
					'LIMIT' => $this->maxPerPage + 1
					
				]
			);
			// echo '<pre>'; print_r($res->numRows()); exit;
			if ( $res->numRows() > 0 ) {
				
				// $out = Html::rawElement( 'div', [ 'class' => 'mw-allpages-body' ], 'Rejected Pages' );
				$out = Html::openElement( 'ul', [ 'class' => 'mw-allpages-chunk' ] );

				while ( ( $n < $this->maxPerPage ) && ( $s = $res->fetchObject() ) ) {
						$out .= '<li' .
							(' class="allpagesredirect"' ) .
							'>' .
							 "<a href = '/mediawiki/index.php?title=".$s->mod_title."&action=edit&redlink=1 ' >".$s->mod_title."</a>".
							"</li>\n";
					
					$n++;
				}
				$out .= Html::closeElement( 'ul' );

				if ( $res->numRows() > 2 ) {
					// Only apply CSS column styles if there's more than 2 entries.
					// Otherwise, rendering is broken as "mw-allpages-body"'s CSS column count is 3.
					$out = Html::rawElement( 'div', [ 'class' => 'mw-allpages-body' ], $out );
				}
			} else {
				$out = '';
			}

		// $this->outputHTMLForm( $namespace, $from, $to, $hideredirects );

		// $out1 = Html::rawElement( 'div', [ 'class' => 'Rejected-pages' ], '<p><b>Rejected Pages</b></p>' );
		// $output->addHTML( $out1 );
		$output->addHTML( $out );
	}

	/**
	 * @param int $ns The namespace of the article
	 * @param string $text The name of the article
	 * @return array( int namespace, string dbkey, string pagename ) or null on error
	 */
	protected function getNamespaceKeyAndText( $ns, $text ) {
		if ( $text == '' ) {
			# shortcut for common case
			return [ $ns, '', '' ];
		}

		$t = Title::makeTitleSafe( $ns, $text );
		if ( $t && $t->isLocal() ) {
			return [ $t->getNamespace(), $t->getDBkey(), $t->getText() ];
		} elseif ( $t ) {
			return null;
		}

		# try again, in case the problem was an empty pagename
		$text = preg_replace( '/(#|$)/', 'X$1', $text );
		$t = Title::makeTitleSafe( $ns, $text );
		if ( $t && $t->isLocal() ) {
			return [ $t->getNamespace(), '', '' ];
		} else {
			return null;
		}
	}

	/**
	 * Return an array of subpages beginning with $search that this special page will accept.
	 *
	 * @param string $search Prefix to search for
	 * @param int $limit Maximum number of results to return (usually 10)
	 * @param int $offset Number of results to skip (usually 0)
	 * @return string[] Matching subpages
	 */
	public function prefixSearchSubpages( $search, $limit, $offset ) {
		return $this->prefixSearchString( $search, $limit, $offset );
	}

	protected function getGroupName() {
		return 'pages';
	}

	public function getDescription() {
     	return 'All Reject Pages';
   	}
}
