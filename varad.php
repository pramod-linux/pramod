<?php
/**
 * This is the main web entry point for MediaWiki.
 *
 * If you are reading this in your web browser, your server is probably
 * not configured correctly to run PHP applications!
 *
 * See the README, INSTALL, and UPGRADE files for basic setup instructions
 * and pointers to the online documentation.
 *
 * https://www.mediawiki.org/wiki/Special:MyLanguage/MediaWiki
 *
 * ----------
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
 */

// Bail on old versions of PHP, or if composer has not been run yet to install
// dependencies. Using dirname( __FILE__ ) here because __DIR__ is PHP5.3+.
// @codingStandardsIgnoreStart MediaWiki.Usage.DirUsage.FunctionFound
require_once dirname( __FILE__ ) . '/includes/PHPVersionCheck_varad.php';
// @codingStandardsIgnoreEnd
//wfEntryPointCheck( 'index.php' );

require __DIR__ . '/includes/WebStart_varad.php';

$mediaWiki = new MediaWiki_varad();
$mediaWiki->run();

echo '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>';
echo"<script language='javascript'>
	
	$(document).ready(function(){
	    $('.reject').click(function() {
	    	var id = $(this).attr('id');
	    	var comment = $('#comment_'+id).val();
	    	
	    	var url = $(this).attr('href') + '&comment='+comment
	     	$(this).attr('href', url);
	    });

	    $('.approve').click(function() {
	    	var id = $(this).attr('id');
	    	var comment = $('#comment_'+id).val();
	    	var url = $(this).attr('href') + '&comment='+comment
	     	$(this).attr('href', url);
	    });
	});

</script>
";