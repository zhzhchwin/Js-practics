<?php

/**
 *      (C)��վ���ṩ  www.ituiz.com
 *      This is a freeware, But you have no right to modify or distribute
 *
 *      $Id: stroll.inc.php 2 2012-12-09 14:27:05Z except10n $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}
global $_G;
require_once './source/plugin/singcere_waterfall/singcere_waterfall.func.php';

$view = $_GET['view'];
$views = array('all', 'forum', 'tag');
if(!in_array($view, $views)) {
	$view = 'all';
}

if($wf_curspart == 1) {
	$start = $wf_perpage * ($_G['page'] - 1);
	$subperpage = $wf_init_num;
	
} else if($wf_curspart <= $wf_spart_num){
	$avgloads = intval(($wf_perpage - $wf_init_num) / ($wf_spart_num - 1));
	$subperpage = $wf_curspart == $wf_spart_num ? $wf_perpage - $wf_init_num - $avgloads * ($wf_curspart - 2) : $avgloads;
	$start = $wf_curspart == $wf_spart_num ? $wf_perpage * ($_G['page'] - 1) + $wf_perpage - $subperpage : $wf_perpage * ($_G['page'] - 1) + $wf_init_num + $subperpage * ($wf_curspart - 2);
} else {
	header('HTTP/1.1 404 Not Found');
	header('status: 404 Not Found');
	exit();
}

loadcache('stamps');
$forums = get_forums();
$sid = in_array($_GET['sid'], array(1, 2, 3, 4, 5))? $_GET['sid'] : 0;
$type = in_array($_GET['type'], array('top', 'hot', 'digest'))? $_GET['type'] : 'new';
$hc = $block_must_cover_on || !empty($_GET['hc']);
$minvalue = $view == 'hot' ? $view_hot_value : 0;
$data = array();

if($view == 'all') {
	$data[$view]['threadlist'] = getthreadlist(0, $type, $wf_forum_limit, $sid, $hc, 1, $start, $subperpage);
	$data[$view]['threadcount'] = C::t('#singcere_waterfall#dx')->count_thread($type, $minvalue, $wf_forum_limit, $sid, $hc);
	$theurl = 'plugin.php?id=singcere_waterfall&mod=stroll&view='.$view;
} else if($view == 'forum') {
	$gid = empty($_GET['gid']) ? 0 : $_GET['gid'];
	$fid = empty($_GET['fid']) ? 0 : $_GET['fid'];
	$fids = array();
	if($gid && empty($fid)) {
		if(array_key_exists($gid, $forums)) {
			$fids = array_keys($forums[$gid]['sub']);
			foreach($forums[$gid]['sub'] as $value) {
				if(!empty($value['sub'])) {$fids = array_merge($fids, $value['sub']);}
			}
		} else {
			showmessage(lang('plugin/singcere_waterfall', 'gid_no_open'));
		}
	} else if($fid) {
		if(isset($forums[$gid]) && array_key_exists($fid, $forums[$gid]['sub'])) {
			$fids = $forums[$gid]['sub'][$fid]['sub'];
			$fids[] = $fid;
		} else {
			showmessage(lang('plugin/singcere_waterfall', 'fid_no_open'));
		}
	}
	$data[$view]['threadlist'] = getthreadlist(0, $type, $fids, $sid, $hc, 1, $start, $subperpage);
	$data[$view]['threadcount'] = C::t('#singcere_waterfall#dx')->count_thread($type, $minvalue, $fids, $sid, $hc);
	$theurl = "plugin.php?id=singcere_waterfall&mod=stroll&view=$view&type=$type&gid=$gid&fid=$fid";
	
} else {
	$tagname = trim($_GET['tagname']);
	if(empty($tagname)) {
		$tagarray = C::t('#singcere_waterfall#dx')->fetch_all_tag_by_tagname($custom_tag_array);
	} else {
		$tagarray = C::t('#singcere_waterfall#dx')->fetch_all_tag_by_tagname($tagname);
		$tagarray = array($tagarray['tagid'] => $tagarray);
	}
	$tids = array();
	foreach(C::t('#singcere_waterfall#dx')->fetch_all_tid_by_tagid(array_keys($tagarray), $type, $sid, $start, $subperpage) as $value) {
		$tids[] = $value['tid'];
	}
	$data[$view]['threadcount'] = count($tids);
	$data[$view]['threadlist'] = getthreadlist($tids, '', 0, 0, 0, 1, $start, $subperpage);
	$theurl = "plugin.php?id=singcere_waterfall&mod=stroll&view=$view&type=$type&sid=$sid&tagname=$tagname";
}

$multipage = multi($data[$view]['threadcount'], $wf_perpage, $_G['page'], $theurl, 10);
include template('singcere_waterfall:waterfall');






