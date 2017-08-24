<?php
/*
Plugin Name: Sendmail
Version: 3.7
Plugin URL: http://kller.cn/?post=61
Description: 发送博客留言至E-mail。
Author: KLLER
Author Email: kller@foxmail.com
Author URL: http://kller.cn
*/

!defined('EMLOG_ROOT') && exit('access deined!');
require_once(EMLOG_ROOT.'/content/plugins/kl_sendmail-master/class/class.smtp.php');
require_once(EMLOG_ROOT.'/content/plugins/kl_sendmail-master/class/class.phpmailer.php');
function kl_sendmail_do($mailserver, $port, $mailuser, $mailpass, $mailto, $subject,  $content, $fromname)
{
	$mail = new KL_SENDMAIL_PHPMailer();
	$mail->CharSet = "UTF-8";
	$mail->Encoding = "base64";
	$mail->Port = $port;

	if(KL_MAIL_SENDTYPE == 1)
	{
		$mail->IsSMTP();
	}else{
		$mail->IsMail();
	}
	$mail->Host = $mailserver;
	$mail->SMTPAuth = true;
	$mail->Username = $mailuser;
	$mail->Password = $mailpass;

	$mail->From = $mailuser;
	$mail->FromName = $fromname;

	$mail->AddAddress($mailto);
	$mail->WordWrap = 500;
	$mail->IsHTML(true);
	$mail->Subject = $subject;
	$mail->Body = $content;
	$mail->AltBody = "This is the body in plain text for non-HTML mail clients";
	if($mail->Host == 'smtp.gmail.com') $mail->SMTPSecure = "ssl";
	if(!$mail->Send())
	{
		echo $mail->ErrorInfo;
		return false;
	}else{
		return true;
	}
}
function kl_sendmail_get_comment_mail()
{
	include(EMLOG_ROOT.'/content/plugins/kl_sendmail-master/kl_sendmail-master_config.php');
	if(KL_IS_SEND_MAIL == 'Y' || KL_IS_REPLY_MAIL == 'Y')
	{
		$comname = isset($_POST['comname']) ? addslashes(trim($_POST['comname'])) : '';
		$comment = isset($_POST['comment']) ? addslashes(trim($_POST['comment'])) : '';
		$commail = isset($_POST['commail']) ? addslashes(trim($_POST['commail'])) : '';
		$comurl = isset($_POST['comurl']) ? addslashes(trim($_POST['comurl'])) : '';
		$gid = isset($_POST['gid']) ? intval($_POST['gid']) : -1;
		$pid = isset($_POST['pid']) ? intval($_POST['pid']) : 0;

		$blogname = Option::get('blogname');
		$Log_Model = new Log_Model();
		$logData = $Log_Model->getOneLogForHome($gid);
		$log_title = $logData['log_title'];
		$subject = "日志《{$log_title}》收到了新的评论";
		if(strpos(KL_MAIL_TOEMAIL, '@139.com') === false)
		{
			if(!empty($commail)) $content .= "Email：{$commail}<br />";
			if(!empty($comurl)) $content .= "主页：{$comurl}<br />";
				$content = "<div style=\"color:#555;font:12px/1.5 微软雅黑,Tahoma,Helvetica,Arial,sans-serif;width:600px;margin:50px auto;border: 1px solid #e9e9e9;border-top: none;box-shadow:0 0px 0px #aaaaaa;\">
  <table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
    <tbody>
      <tr valign=\"top\" height=\"2\">
        <td width=\"190\" bgcolor=\"#0B9938\"></td>
        <td width=\"120\" bgcolor=\"#9FCE67\"></td>
        <td width=\"85\" bgcolor=\"#EDB113\"></td>
        <td width=\"85\" bgcolor=\"#FFCC02\"></td>
        <td width=\"130\" bgcolor=\"#5B1301\" valign=\"top\"></td>
      </tr>
    </tbody>
  </table>
  <div style=\"padding: 0 15px 8px;\">
    <h2 style=\"border-bottom:1px solid #e9e9e9;font-size:14px;font-weight:normal;padding:10px 0 10px;\"><span style=\"color: #12ADDB\">&gt; </span>您的文章 <a style=\"text-decoration:none;color: #12ADDB;\" href=\"".BLOG_URL."\" title=\"{$blogname}\" target=\"_blank\">{$log_title}</a> 有新回复啦！</h2>
    <div style=\"font-size:12px;color:#777;padding:0 10px;margin-top:18px\">
      <p></p>
      <p>{$comname} 给您的回复如下:</p>
      <p style=\"background-color:#f5f5f5;padding: 10px 15px;margin:18px 0\">{$comment}</p>
      <p>您可以点击 <a style=\"text-decoration:none; color:#12addb\" href=\"".Url::log($gid)."#{$pid}\" title=\"单击查看完整的回复内容\" target=\"_blank\">查看完整的回复內容</a>，欢迎再次光临 <a style=\"text-decoration:none; color:#12addb\" href=\"".BLOG_URL."\" title=\"{$blogname}\" target=\"_blank\">{$blogname}</a>！</p>
    </div>
  </div>
  <div style=\"color:#888;padding:10px;border-top:1px solid #e9e9e9;background:#f5f5f5;\">
    <p style=\"margin:0;padding:0;\">© 2017 <a style=\"color:#888;text-decoration:none;\" href=\"".BLOG_URL."\" title=\"{$blogname}\" target=\"_blank\">{$blogname}</a> - 邮件自动生成，请勿直接回复！</p>
  </div>
</div>";
		}else{
			$content = $comment;
		}
		if(KL_IS_SEND_MAIL == 'Y')
		{
			if(ROLE == 'visitor') kl_sendmail_do(KL_MAIL_SMTP, KL_MAIL_PORT, KL_MAIL_SENDEMAIL, KL_MAIL_PASSWORD, KL_MAIL_TOEMAIL, $subject, $content, $blogname);
		}
		if(KL_IS_REPLY_MAIL == 'Y')
		{
			if($pid > 0)
			{
				$DB = Database::getInstance();
				$Comment_Model = new Comment_Model();
				$pinfo = $Comment_Model->getOneComment($pid);
				if(!empty($pinfo['mail']))
				{
					$subject = "您在【{$blogname}】发表的评论收到了回复";
					$content = "<div style=\"color:#555;font:12px/1.5 微软雅黑,Tahoma,Helvetica,Arial,sans-serif;width:600px;margin:50px auto;border: 1px solid #e9e9e9;border-top: none;box-shadow:0 0px 0px #aaaaaa;\">
  <table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
    <tbody>
      <tr valign=\"top\" height=\"2\">
        <td width=\"190\" bgcolor=\"#0B9938\"></td>
        <td width=\"120\" bgcolor=\"#9FCE67\"></td>
        <td width=\"85\" bgcolor=\"#EDB113\"></td>
        <td width=\"85\" bgcolor=\"#FFCC02\"></td>
        <td width=\"130\" bgcolor=\"#5B1301\" valign=\"top\"></td>
      </tr>
    </tbody>
  </table>
  <div style=\"padding: 0 15px 8px;\">
    <h2 style=\"border-bottom:1px solid #e9e9e9;font-size:14px;font-weight:normal;padding:10px 0 10px;\"><span style=\"color: #12ADDB\">&gt; </span>您在 <a style=\"text-decoration:none;color: #12ADDB;\" href=\"".BLOG_URL."\" title=\"{$blogname}\" target=\"_blank\">{$blogname}</a> 中的留言有回复啦！</h2>
    <div style=\"font-size:12px;color:#777;padding:0 10px;margin-top:18px\">
	{$pinfo['poster']} 司机，您曾在《{$log_title}》中发表评论：
      <p></p>
      <p style=\"background-color: #f5f5f5;padding: 10px 15px;margin:18px 0\">{$pinfo['comment']}</p>
      <p>{$comname} 给您的回复如下:</p>
      <p style=\"background-color:#f5f5f5;padding: 10px 15px;margin:18px 0\"><a href=\"{$_SERVER['HTTP_REFERER']}\" rel=\"nofollow\" target=\"_blank\">@{$pinfo['poster']} </a>:{$comment}</p>
      <p>您可以点击 <a style=\"text-decoration:none; color:#12addb\" href=\"".Url::log($gid)."#{$pid}\" title=\"单击查看完整的回复内容\" target=\"_blank\">查看完整的回复內容</a>，欢迎再次光临 <a style=\"text-decoration:none; color:#12addb\" href=\"".BLOG_URL."\" title=\"{$blogname}\" target=\"_blank\">{$blogname}</a>！</p>
    </div>
  </div>
  <div style=\"color:#888;padding:10px;border-top:1px solid #e9e9e9;background:#f5f5f5;\">
    <p style=\"margin:0;padding:0;\">© 2017 <a style=\"color:#888;text-decoration:none;\" href=\"".BLOG_URL."\" title=\"{$blogname}\" target=\"_blank\">{$blogname}</a> - 邮件自动生成，请勿直接回复！</p>
  </div>
</div>";
			kl_sendmail_do(KL_MAIL_SMTP, KL_MAIL_PORT, KL_MAIL_SENDEMAIL, KL_MAIL_PASSWORD, $pinfo['mail'], $subject, $content, $blogname);
				}
			}
		}
	}else{
		return;
	}
}
addAction('comment_saved', 'kl_sendmail_get_comment_mail');

function kl_sendmail_get_twitter_mail($r, $name, $date, $tid)
{
	include(EMLOG_ROOT.'/content/plugins/kl_sendmail-master/kl_sendmail-master_config.php');
	if(KL_IS_TWITTER_MAIL == 'Y')
	{
		$DB = Database::getInstance();
		$blogname = Option::get('blogname');
		$sql = "select a.content, b.username from ".DB_PREFIX."twitter a left join ".DB_PREFIX."user b on b.uid=a.author where a.id={$tid}";
		$res = $DB->query($sql);
		$row = $DB->fetch_array($res);
		$author = $row['username'];
		$twitter = $row['content'];
		$subject = "{$author}发布的微语收到了新的回复";
		if(strpos(KL_MAIL_TOEMAIL, '@139.com') === false)
		{
		$content = "<div style=\"color:#555;font:12px/1.5 微软雅黑,Tahoma,Helvetica,Arial,sans-serif;width:600px;margin:50px auto;border: 1px solid #e9e9e9;border-top: none;box-shadow:0 0px 0px #aaaaaa;\">
	<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
    <tbody>
      <tr valign=\"top\" height=\"2\">
        <td width=\"190\" bgcolor=\"#0B9938\"></td>
        <td width=\"120\" bgcolor=\"#9FCE67\"></td>
        <td width=\"85\" bgcolor=\"#EDB113\"></td>
        <td width=\"85\" bgcolor=\"#FFCC02\"></td>
        <td width=\"130\" bgcolor=\"#5B1301\" valign=\"top\"></td>
      </tr>
    </tbody>
  </table>
	<div style=\"width:100%; height:60px; color:#666; \">
	<span style=\"height:60px; line-height:60px; margin-left:30px; font-size:14px;\">
			> 您好{$author}！您在【{$blogname}】发布的微语收到了新的回复！
  <hr style=\"color:#e9e9e9;margin:0 auto 0;\" size=\"1\" width=\"95%\" \>
	</span>
	</div>
	<div style=\"width:90%; margin:0 auto\">
		<p>{$name}对微语的回复：</p>
		<p style=\"background-color: #f5f5f5;border: 1px solid #DDD;padding: 20px;margin: 10px 0;\">
			{$r}
		</p>
		<p>
			现在就前往<a href=\"{$_SERVER['HTTP_REFERER']}\" target=\"_blank\">微语页面</a>进行查看，欢迎再次光临 <a style=\"text-decoration:none; color:#12addb\" href=\"".BLOG_URL."\" title=\"{$blogname}\" target=\"_blank\">{$blogname}</a>！
		</p>
		</div>
				<div style=\"color:#888;padding:10px;border-top:1px solid #e9e9e9;background:#f5f5f5;\">
		<p style=\"margin:0;padding:0;\">© 2017 <a style=\"color:#888;text-decoration:none;\" href=\"".BLOG_URL."\" title=\"{$blogname}\" target=\"_blank\">{$blogname}</a> - 邮件自动生成，请勿直接回复！</p>
	</div>
		</div>";
		}else{
			$content = $r;
		}
		if(ROLE == 'visitor') kl_sendmail_do(KL_MAIL_SMTP, KL_MAIL_PORT, KL_MAIL_SENDEMAIL, KL_MAIL_PASSWORD, KL_MAIL_TOEMAIL, $subject, $content, $blogname);
	}
}
addAction('reply_twitter', 'kl_sendmail_get_twitter_mail');

function kl_sendmail_put_reply_mail($commentId, $reply)
{
	global $userData;
	include(EMLOG_ROOT.'/content/plugins/kl_sendmail-master/kl_sendmail-master_config.php');
	if(KL_IS_REPLY_MAIL == 'Y')
	{
		$DB = Database::getInstance();
		$blogname = Option::get('blogname');
		$Comment_Model = new Comment_Model();
		$commentArray = $Comment_Model->getOneComment($commentId);
		extract($commentArray);
		$subject="您在【{$blogname}】发表的评论收到了回复";
		if(strpos($mail, '@139.com') === false)
		{
			$emBlog = new Log_Model();
			$logData = $emBlog->getOneLogForHome($gid);
			$log_title = $logData['log_title'];
			$content = "<div style=\"color:#555;font:12px/1.5 微软雅黑,Tahoma,Helvetica,Arial,sans-serif;width:600px;margin:50px auto;border: 1px solid #e9e9e9;border-top: none;box-shadow:0 0px 0px #aaaaaa;\">
  <table border=\"0\" cellspacing=\"0\" cellpadding=\"0\">
    <tbody>
      <tr valign=\"top\" height=\"2\">
        <td width=\"190\" bgcolor=\"#0B9938\"></td>
        <td width=\"120\" bgcolor=\"#9FCE67\"></td>
        <td width=\"85\" bgcolor=\"#EDB113\"></td>
        <td width=\"85\" bgcolor=\"#FFCC02\"></td>
        <td width=\"130\" bgcolor=\"#5B1301\" valign=\"top\"></td>
      </tr>
    </tbody>
  </table>
  <div style=\"padding: 0 15px 8px;\">
    <h2 style=\"border-bottom:1px solid #e9e9e9;font-size:14px;font-weight:normal;padding:10px 0 10px;\"><span style=\"color: #12ADDB\">&gt; </span>您在 <a style=\"text-decoration:none;color: #12ADDB;\" href=\"".BLOG_URL."\" title=\"{$blogname}\" target=\"_blank\">{$blogname}</a> 中的留言有回复啦！</h2>
    <div style=\"font-size:12px;color:#777;padding:0 10px;margin-top:18px\">
	{$poster}司机，您曾在《{$log_title}》中发表评论：
      <p></p>
      <p style=\"background-color: #f5f5f5;padding: 10px 15px;margin:18px 0\">{$comment}</p>
      <p>{$userData['username']} 给您的回复如下:</p>
      <p style=\"background-color:#f5f5f5;padding: 10px 15px;margin:18px 0\"><a href=\"{$_SERVER['HTTP_REFERER']}\" rel=\"nofollow\" target=\"_blank\">@{$poster} </a>:{$reply}</p>
      <p>您可以点击 <a style=\"text-decoration:none; color:#12addb\" href=\"".Url::log($gid)."#{$cid}\" title=\"单击查看完整的回复内容\" target=\"_blank\">查看完整的回复內容</a>，欢迎再次光临 <a style=\"text-decoration:none; color:#12addb\" href=\"".BLOG_URL."\" title=\"{$blogname}\" target=\"_blank\">{$blogname}</a>！</p>
    </div>
  </div>
  <div style=\"color:#888;padding:10px;border-top:1px solid #e9e9e9;background:#f5f5f5;\">
    <p style=\"margin:0;padding:0;\">© 2017 <a style=\"color:#888;text-decoration:none;\" href=\"".BLOG_URL."\" title=\"{$blogname}\" target=\"_blank\">{$blogname}</a> - 邮件自动生成，请勿直接回复！</p>
  </div>
</div>";
		}else{
			$content = $reply;
		}
		if($mail != '')	kl_sendmail_do(KL_MAIL_SMTP, KL_MAIL_PORT, KL_MAIL_SENDEMAIL, KL_MAIL_PASSWORD, $mail, $subject, $content, $blogname);
	}else{
		return;
	}
}
addAction('comment_reply', 'kl_sendmail_put_reply_mail');

function kl_sendmail_menu()
{
	echo '<li><a id="kl_sendmail" href="./plugin.php?plugin=kl_sendmail-master">评论通知</a></li>';
}
addAction('adm_sidebar_ext', 'kl_sendmail_menu');
?>