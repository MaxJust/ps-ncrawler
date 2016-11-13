<?php
/**
 * nCrawler.com - инструмент автоматического мониторинга цен в интернете
 *
 * @author nCrawler.com <http://ncrawler.com>
 * @copyright  2016 nCrawler.com
 * @license GNU General Public License, version 2
 */

header("Expires: Mon, 30 March 2015 12:00:00 GMT");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");

header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

header("Location: ../");
exit;