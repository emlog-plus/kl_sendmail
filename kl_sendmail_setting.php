<?php
/**
 * sendmail插件
 * design by KLLER
 */

!defined('EMLOG_ROOT') && exit('access deined!');

function plugin_setting_view()
{
	include(EMLOG_ROOT.'/content/plugins/kl_sendmail/kl_sendmail_config.php');
?>
<script type="text/javascript">
jQuery.fn.onlyPressNum = function(){$(this).css('ime-mode','disabled');$(this).css('-moz-user-select','none');$(this).bind('keydown',function(event){var k=event.keyCode;if(!((k==13)||(k==9)||(k==35)||(k == 36)||(k==8)||(k==46)||(k>=48&&k<=57)||(k>=96&&k<=105)||(k>=37&&k<=40))){event.preventDefault();}})}
jQuery(function($){
	$('#port').onlyPressNum();
	$('#testsend').click(function(){$('#testresult').html('邮件发送中..');$.get('../content/plugins/kl_sendmail/kl_sendmail_test_do.php',{sid:Math.random()},function(result){if($.trim(result)!=''){$('#testresult').html(result);}else{$('#testresult').html('发送失败！');}});});
$("#kl_sendmail").addClass('active-page');
$("#menu_mg").addClass('active');
});
setTimeout(hideActived,2600);
</script>
<div class="heading-bg  card-views">
<ul class="breadcrumbs">
<li><a href="./"><i class="fa fa-home"></i> 首页</a></li>
<li class="active">评论通知</li>
</ul>
</div>
<?php if(isset($_GET['setting'])):?>
<div class="actived alert alert-success alert-dismissable">
<button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
插件设置完成
</div>
<?php endif;?>
<div class="row">
<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12">
<div class="panel panel-primary card-view">
<div class="panel-heading">
<div class="pull-left">
<h6 class="panel-title txt-light">温馨提示</h6>
</div>
<div class="clearfix"></div>
</div>
<div class="panel-wrapper collapse in">
<div class="panel-body">
<p>发送方式设置为SMTP方式时,发信邮箱必须支持smtp并且开启smtp服务才能发送成功。<br />
相关参考资料： <a href="http://kller.cn/?post=77" target="_blank">关于邮箱开启smtp服务的方法</a></p>
</div>
</div>
</div>
<form id="form1" name="form1" method="post" action="plugin.php?plugin=kl_sendmail&action=setting">
<div class="row">
<div class="col-sm-12">
<div class="panel panel-default card-view">
<div class="tab-content">
<div class="form-group">
    <label>SMTP服务器,如:smtp.163.com</label>
                <input name="smtp" class="form-control" type="text" id="smtp" value="<?php echo KL_MAIL_SMTP;?>"/>
            </div>
<div class="form-group">
    <label>SMTP端口,一般默认25</label>
<input name="port" type="text" id="port" class="form-control" style="ime-mode:disabled;width:180px;" value="<?php echo KL_MAIL_PORT;?>"/>
  </div> 
<div class="form-group">
    <label>发信邮箱</label>
<input name="sendemail" type="text" id="sendemail" class="form-control" value="<?php echo KL_MAIL_SENDEMAIL;?>"/>
  </div>     
<div class="form-group">
    <label>发信密码</label>
<input type="password" name="password" value="<?php echo KL_MAIL_PASSWORD;?>" class="form-control" />
  </div>       
  <div class="form-group">
    <label>发送方式</label>
<div class="radio radio-success">
<input type="radio" name="sendtype" value="0" <?php if(KL_MAIL_SENDTYPE == 0) echo 'checked'; ?> />
<label> Mail方式 </label>
</div>    
<div class="radio radio-success">
<input type="radio" name="sendtype" value="1" <?php if(KL_MAIL_SENDTYPE == 1) echo 'checked'; ?> />
<label> SMTP方式 </label>
</div>
<div class="checkbox checkbox-success">		<input type="checkbox" name="issendmail" value="Y" <?php if(KL_IS_SEND_MAIL == 'Y') echo 'checked';?>/>
<label> 收到评论时通知自己 </label>
</div>
<div class="checkbox checkbox-success">		<input type="checkbox" name="isreplymail" value="Y" <?php if(KL_IS_REPLY_MAIL == 'Y') echo 'checked';?>/>
<label> 回复评论时通知评论者 </label>
</div>
<div class="checkbox checkbox-success">		<input type="checkbox" name="istwittermail" value="Y" <?php if(KL_IS_TWITTER_MAIL == 'Y') echo 'checked';?>/>
<label> 收到碎语回复时通知自己 </label>
</div>
</div>    
    <div class="form-group">
 <input name="Input" type="submit" value="保　存"   class="btn btn-danger"/>                       
</div>
</div>
</div>
</div>
</div>
<div class="row">
<div class="col-sm-12">
<div class="panel panel-default card-view">
<div class="tab-content">
    <div class="form-group">
<input id="testsend" class="btn btn-success" type="button" value="发送一封测试邮件" />
</div>
    <div class="form-group">
<div id="testresult" style="height:64px; padding:10px; border:1px dashed #ccc; overflow:auto;/*background-color:#bbd9e2;*/">
</div>
</div>
</div>
</div>
</div>
</div>
</form>
<?php
}

function plugin_setting()
{
	//修改配置信息
	$fso = fopen(EMLOG_ROOT.'/content/plugins/kl_sendmail/kl_sendmail_config.php','r'); //获取配置文件内容
	$config = fread($fso,filesize(EMLOG_ROOT.'/content/plugins/kl_sendmail/kl_sendmail_config.php'));
	fclose($fso);

	$smtp=htmlspecialchars($_POST['smtp'], ENT_QUOTES);
	$port=htmlspecialchars($_POST['port'], ENT_QUOTES);
	$sendemail=htmlspecialchars($_POST['sendemail'], ENT_QUOTES);
	$password=htmlspecialchars($_POST['password'], ENT_QUOTES);
	$toemail=htmlspecialchars($_POST['toemail'], ENT_QUOTES);
	$sendtype=intval($_POST['sendtype']);
	$issendmail = isset($_POST['issendmail']) ? 'Y' : '';
	$isreplymail = isset($_POST['isreplymail']) ? 'Y' : '';
	$istwittermail = isset($_POST['istwittermail']) ? 'Y' : '';

	$patt = array(
	"/define\('KL_MAIL_SMTP',(.*)\)/",
	"/define\('KL_MAIL_PORT',(.*)\)/",
	"/define\('KL_MAIL_SENDEMAIL',(.*)\)/",
	"/define\('KL_MAIL_PASSWORD',(.*)\)/",
	"/define\('KL_MAIL_TOEMAIL',(.*)\)/",
	"/define\('KL_MAIL_SENDTYPE',(.*)\)/",
	"/define\('KL_IS_SEND_MAIL',(.*)\)/",
	"/define\('KL_IS_REPLY_MAIL',(.*)\)/",
	"/define\('KL_IS_TWITTER_MAIL',(.*)\)/",
	);

	$replace = array(
	"define('KL_MAIL_SMTP','".$smtp."')",
	"define('KL_MAIL_PORT','".$port."')",
	"define('KL_MAIL_SENDEMAIL','".$sendemail."')",
	"define('KL_MAIL_PASSWORD','".$password."')",
	"define('KL_MAIL_TOEMAIL','".$toemail."')",
	"define('KL_MAIL_SENDTYPE','".$sendtype."')",
	"define('KL_IS_SEND_MAIL','".$issendmail."')",
	"define('KL_IS_REPLY_MAIL','".$isreplymail."')",
	"define('KL_IS_TWITTER_MAIL','".$istwittermail."')",
	);

	$new_config = preg_replace($patt, $replace, $config);
	$fso = fopen(EMLOG_ROOT.'/content/plugins/kl_sendmail/kl_sendmail_config.php','w'); //写入替换后的配置文件
	fwrite($fso,$new_config);
	fclose($fso);
}
?>
