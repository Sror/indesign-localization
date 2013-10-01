<?php
/*
 *
 * For examples, see example.php or the Administration interface,
 * as it makes use of nearly every API Call.
 *
 */


class acl_admin_api extends acl_api {

	/*
	 * Administration interface settings
	 */
 	/** @var int Number of items to display per page in the PAGL ACL interface. */
	var $_items_per_page = 100;
 	/** @var int Maximum number of items to display in a select box. Override to manage large collections via ACL Admin */
	var $_max_select_box_items = 100;
 	/** @var int Maximum number of items to return in an ACL Search. */
	var $_max_search_return_items = 100;

	/*
	 *
	 * Misc admin functions.
	 *
	 */

	/**
	 * return_page()
	 *
	 * Sends the user back to a passed URL, unless debug is enabled, then we don't redirect.
	 * 				If no URL is passed, try the REFERER
	 * @param string URL to return to.
	 */
	function return_page($url="") {
		global $_SERVER, $debug;

		if (empty($url) AND !empty($_SERVER[HTTP_REFERER])) {
			$this->debug_text("return_page(): URL not set, using referer!");
			$url = $_SERVER[HTTP_REFERER];
		}

		if (!$debug OR $debug==0) {
			header("Location: $url\n\n");
			exit();
		} else {
			$this->debug_text("return_page(): URL: $url -- Referer: $_SERVER[HTTP_REFERRER]");
		}
	}

	/**
	 * get_paging_data()
	 *
	 * Creates a basic array for Smarty to deal with paging large recordsets.
	 *
	 * @param ADORecordSet ADODB recordset.
	 */
	function get_paging_data($rs) {
                return array(
                                'prevpage' => $rs->absolutepage() - 1,
                                'currentpage' => $rs->absolutepage(),
                                'nextpage' => $rs->absolutepage() + 1,
                                'atfirstpage' => $rs->atfirstpage(),
                                'atlastpage' => $rs->atlastpage(),
                                'lastpageno' => $rs->lastpageno()
                        );
	}

}
?>
