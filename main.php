<?php
/*
 * plugin name: Google Map with Wp
 * author: Mahibul Hasan
 */

define('GCALENDERDIR', dirname(__FILE__));
define("GCALENDERURL", plugins_url('', __FILE__));

include GCALENDERDIR . '/classes/gc.class.php';
Gc_Integration :: init();