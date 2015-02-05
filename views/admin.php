<?php
/***
 * 管理画面の
参考：データ検証
http://wpdocs.sourceforge.jp/%E3%83%87%E3%83%BC%E3%82%BF%E6%A4%9C%E8%A8%BC#Email 
***/


/***
 * 初期変数
***/
//エラー用
$errors = array();
//lessのURL
$less_url = $this->plugins_url.'/css/admin.less?'.time();
$less_min_url = $this->plugins_url.'/js/less.min.js';


/***
 * POST処理
***/
if (isset($_POST['mail'], $_POST['subject'], $_POST['reply'], $_POST['letters'])){

	//メール
	if (is_email($_POST['mail']) == true){
		update_option('ccc_plugin_value_mail', $_POST['mail']);
	}
	else{
		$errors[] = 'メールアドレスが正しくありません。';
	}
	
	//件名
	$subject = htmlspecialchars($_POST['subject'], ENT_QUOTES);
	update_option('ccc_plugin_value_subject', $subject);
	
	//reply
	if (is_email($_POST['reply']) == true){
		update_option('ccc_plugin_value_reply', $_POST['reply']);
	}
	else{
		$errors[] = '通知メールのアドレスが正しくありません。';
	}
	
	//文字数
	$letters = $_POST['letters'];
	if (strval($letters) == strval(intval($letters)))
	{
		update_option('ccc_plugin_value_letters', $letters);	
	}
	else
	{
		$errors[] = '整数を入力してください。';
	}
	
	
	
	//ログイン時のチェック
	$login_flg = htmlspecialchars($_POST['login_flg'], ENT_QUOTES);
	update_option('ccc_plugin_value_login_flg', $login_flg);
	
}
//エラーがあればリスト作成
if ( isset($errors) ){
$errorList = '<ul class="errors">';
	foreach ($errors as $value) {
	    $errorList .= '<li>'.$value.'</li>';
	}
$errorList .= '</ul>';	
}


/***
 * データを取得
***/
//登録データ
$mail = get_option('ccc_plugin_value_mail');
$subject = get_option('ccc_plugin_value_subject');
$reply = get_option('ccc_plugin_value_reply');
$letters = get_option('ccc_plugin_value_letters');
$login_flg = get_option('ccc_plugin_value_login_flg');



//出力
?>

<link rel="stylesheet/less" href="<?php echo $less_url; ?>" />
<script src="<?php echo $less_min_url; ?>" type="text/javascript"></script>



<!-- .cccAdminWrap -->
<div class="cccAdminWrap clearfix">
	
	
<!-- .cccMainArea -->
<div class="cccMainArea">
<h1>CCC <span style="font-size:80%;">(Check Copy Contents)</span><?php _e('Setting screen', $this->textdomain );?></h1>

<?php echo $errorList; ?>

<form action="" method="post">
<table class="type01">
<tbody>
	<tr>
		<th>
			<?php _e('Mail address to send notification', $this->textdomain );?>
		</th>
		<td>
			<input type="text" name="mail" value="<?php echo $mail; ?>" class="p80" />
			<div class="supp"><?php _e('Ex: abcd@example.com', $this->textdomain );?></div>
		</td>
	</tr>
	<tr>
		<th>
			<?php _e('Subject of notification mail', $this->textdomain );?>
		</th>
		<td>
			<input type="text" name="subject" value="<?php echo $subject; ?>" class="p80" />
		</td>
	</tr>
	<tr>
		<th>
			<?php _e('Address of notification mail', $this->textdomain );?>
		</th>
		<td>
			<input type="text" name="reply" value="<?php echo $reply;?>" class="p80" />
			<div class="supp"><?php _e('Ex: no-reply@example.com', $this->textdomain );?></div>
		</td>
	</tr>
	<tr>
		<th>
			<?php _e('Character quantity to detect', $this->textdomain );?>
		</th>
		<td>
			<input type="text" name="letters" value="<?php echo $letters; ?>" class="p20" /> <?php _e('characters or more', $this->textdomain );?>
			<div class="supp"><?php _e('Notification is sent when this number of characters or greater is copied.', $this->textdomain );?></div>
		</td>
	</tr>
	<tr>
		<th>
			<?php _e('Notification based on login user operation', $this->textdomain );?>
		</th>
		<td>

<?php
if ($login_flg == 1)
{
	echo  '<input type="checkbox" name="login_flg" value="1" checked> ';
	_e('Notify', $this->textdomain );
}
else if($login_flg != 1)
{
	echo '<input type="checkbox" name="login_flg" value="1" > ';
	_e('Notify', $this->textdomain );		
}
?>			

		<div class="supp"><?php _e('Notification is sent when the logged in user copies.', $this->textdomain );?></div>
		</td>
	</tr>
</tbody>	
</table>
<p style="text-align:center;">
<input type="submit" value="<?php _e('Register', $this->textdomain ); ?>" />
</p>
</form>
</div>
<!-- /.cccMainArea -->


<!-- .cccSideArea -->
<div class="cccSideArea">
<div class="cccSide">
<div class="inner">


<div class="box">
<?php _e('The detailed explanation of this plug in is this url.', $this->textdomain );?>
<a href="http://www.kigurumi.asia/imake/2548/" target="_blank">http://www.kigurumi.asia/imake/2548/</a>	
</div>


<div class="box">


<?php printf($this->_('If you find this plugin usefull, don\'t hesitate to buy me some present from <a href="%s">my wishlist</a>.'), 'http://www.amazon.co.jp/registry/wishlist/2TUGZOYJW8T4T/?tag=wpccc-22'); ?>

</div>


<div class="box">
<iframe src="//www.facebook.com/plugins/likebox.php?href=https%3A%2F%2Fwww.facebook.com%2Fkigurumi.oihagi&amp;width=278&amp;height=62&amp;show_faces=false&amp;colorscheme=light&amp;stream=false&amp;show_border=false&amp;header=false&amp;appId=355939381181327" scrolling="no" frameborder="0" style="border:none; overflow:hidden; width:292px; height:62px;" allowTransparency="true"></iframe>
</div>

<div class="box">
<a href="https://twitter.com/intent/tweet?screen_name=kanakogi" class="twitter-mention-button" data-lang="ja" data-related="kanakogi">Tweet to @kanakogi</a>
<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0];if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src="//platform.twitter.com/widgets.js";fjs.parentNode.insertBefore(js,fjs);}}(document,"script","twitter-wjs");</script>	
</div>


</div>
</div>
</div>
<!-- /.cccSideArea -->


</div>
<!-- /.cccAdminWrap -->