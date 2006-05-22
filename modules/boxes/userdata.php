<?php
$templ['box']['rows'] = "";

// If an admin is logged in as an user
// show admin name and switch back link
if ($_COOKIE["olduserid"] != "") {
	$old_user = $db->query_first("SELECT username FROM {$config['tables']['user']} WHERE userid='{$_COOKIE["olduserid"]}'");

	if (strlen($old_user['username']) > 14) $old_user['username'] = substr($old_user['username'], 0, 11) . "...";

	$box->DotRow($lang['boxes']['userdata_admin'], "", "", "admin", 0);
	$box->EngangedRow("<b>{$old_user["username"]}</b>". $dsp->FetchUserIcon($_COOKIE["olduserid"]), "", "", "admin", 0);
	$box->EngangedRow($lang['boxes']['userdata_switch_back'], "index.php?mod=usrmgr&action=switch_user&step=11&userid={$_COOKIE["olduserid"]}", "", "admin", 0);
	$box->EmptyRow();
}

// Show username and ID
if (strlen($auth['username']) > 14) $username = substr($auth['username'], 0, 11) . "...";
else $username = $auth['username'];
$userid_formated = sprintf( "%0".$config['size']['userid_digits']."d", $auth['userid']);

$box->DotRow($lang['boxes']['userdata_username']);
$box->EngangedRow("<b>$username</b> ". $dsp->FetchUserIcon($auth["userid"]));
$box->EngangedRow("[".$lang['boxes']['userdata_id']." <i>$userid_formated</i>]");


// Show last log in and login count
$user_lg = $db->query_first("SELECT user.logins, max(auth.logintime) AS logintime
	FROM {$config['tables']['user']} AS user
	LEFT JOIN {$config['tables']['stats_auth']} AS auth ON auth.userid = user.userid
	WHERE user.userid=\"".$auth["userid"]."\"
	GROUP BY auth.userid");

$box->DotRow($lang['boxes']['userdata_last_login']);
$box->EngangedRow("<b>". $func->unixstamp2date($user_lg["logintime"], "shortdaytime") ."</b>");

$box->DotRow($lang['boxes']['userdata_logins']);
$box->EngangedRow("<b>". $user_lg["logins"] ."</b>");

$box->EmptyRow();

// Show other links
if ($cfg["user_show_ticket"]) $box->ItemRow("data", $lang['boxes']['userdata_my_ticket'], "index.php?mod=usrmgr&action=myticket", "", "menu");
$box->ItemRow("data", $lang['boxes']['userdata_change_pw'], "index.php?mod=usrmgr&action=changepw", "", "menu");
$box->ItemRow("data", $lang['boxes']['userdata_priv_settings'], "index.php?mod=usrmgr&action=settings", "", "menu");
$box->ItemRow("data", $lang['boxes']['userdata_priv_details'], "index.php?mod=usrmgr&action=details&userid={$auth["userid"]}", "", "menu");
$box->ItemRow("delete", $lang['boxes']['userdata_logout'], "index.php?mod=logout", "", "menu");


// New-Mail Notice
if (in_array('mail', $ActiveModules)) {
	$mails_new = $db->query("SELECT mailID
		FROM {$config["tables"]["mail_messages"]}
		WHERE ToUserID = '{$auth['userid']}' AND mail_status = 'active' AND rx_date = '0'
		");

	if ($db->num_rows($mails_new) > 0) {
    $templ['box']['rows'] .= $box->LinkItem("index.php?mod=mail", "<img src=\"design/{$config['lansuite']['default_design']}/images/mail_newmail.gif\" alt=\"{$lang['boxes']['userdata_new_mail']}\" border=\"0\">");
  
    // Open PopUp
    $found_not_popped_up_mail = false;
    while ($mail_new = $db->fetch_array($mails_new)) {
      if (!isset($_SESSION['mail_popup'][$mail_new['mailID']])) {
        $_SESSION['mail_popup'][$mail_new['mailID']] = 1;
        $found_not_popped_up_mail = true;
      }
    }
    if ($cfg['mail_popup_on_new_mails'] and $found_not_popped_up_mail) {
      $templ['box']['rows'] .= '<script language="JavaScript">
      OpenWindow("index.php?mod=mail&action=mail_popup&design=base", "new_mail");
      </script>';
    }
  }
  $db->free_result($mails_new);
}

$boxes['userdata'] .= $box->CreateBox("user",$lang['boxes']['userdata_my_data']);
?>