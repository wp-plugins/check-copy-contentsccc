<?php
/*
Plugin Name: Check Copy Contents(CCC)
Plugin URI: https://github.com/kanakogi/CCC
Description: If someone reposts or quotes text content from your website, you'll get a discreet email letting you know, along with the sections quoted and the URL.
Author: Nakashima Masahiro
Version: 1.3
Author URI: http://www.kigurumi.asia
Text Domain: ccc
Domain Path: /languages/
*/

//public define( 'CCC_PLUGIN_URL', untrailingslashit( plugins_url( '', __FILE__ ) ) );

class CheckCopyContents {
	
	public $textdomain = 'ccc';
	public $plugins_url = '';
    public $debug_mode = false;
    
    function __construct() {
	    
	    // プラグインが有効化されたときに実行されるメソッドを登録
        if (function_exists('register_activation_hook'))
        {
            register_activation_hook(__FILE__, array($this, 'activationHook'));
        }
        //無効化
        if (function_exists('register_deactivation_hook'))
        {
            register_deactivation_hook(__FILE__, array($this, 'deactivationHook'));
        }
        //アンインストール
        if ( function_exists('register_uninstall_hook'))
        {
            register_uninstall_hook( __FILE__, array($this, 'uninstallHook'));
		}
        
		//init
		add_action( 'init', array($this, 'init') );
		
		
		//ローカライズ
		add_action( 'init', array($this, 'load_textdomain') );

		
		//管理画面について
		add_action('admin_menu', array($this, 'ccc_admin_menu'));	 	 
		

		//contentへのフック
		add_filter( 'the_content', array($this, 'filter_wrap_content') );
		  
		//header()のフック
		add_filter( 'wp_head', array($this, 'filter_header') );

		//footer()のフッック
		add_filter( 'wp_footer', array($this, 'filter_footer') );
		
		
		//ajaxのアクション
		add_action('wp_ajax_cccAjax', array($this, 'cccAjax'));
		add_action('wp_ajax_nopriv_cccAjax', array($this, 'cccAjax'));	
	}
	
	
	/**
	 * init
	 */
	 public function init(){
		$this->plugins_url = untrailingslashit( plugins_url( '', __FILE__ ) ); 
	 }
	   
    
    	
	/***
	 * contentをdivで囲む
	***/
	public function filter_wrap_content ( $content ) {
		

		//the_content()をIDで囲む
		$content = '<div class="theContentWrap-ccc">'.$content."</div>";
		return $content; 
	}
	
	
	/***
	 * headerの処理
	***/
	public function filter_header(){
		
		//singleページのみ読み込み
		if(is_single() || is_page()){
			
			
			//ログインチェック
			$login_flg = get_option('ccc_plugin_value_login_flg');
	
			//ログイン時
			if( is_user_logged_in() )
			{
				//ログインユーザーも通知するなら
				if( $login_flg == 1)
				{
					$this->use_CCC();
				}
			}
			else
			{
				$this->use_CCC();	
			}
			
		}
				
	}

	/***
	* footerの処理
	***/
	public function filter_footer(){

		//nonceを付加
		wp_nonce_field('ccc_nonce', 'ccc_nonce');

	}
	
	/***
	 * 通知関数
	***/
	public function use_CCC(){
		//記事情報
		$postID = get_the_ID();
		$remote_addr = $_SERVER["REMOTE_ADDR"];
		
		//headerにjsを読み込む
		$js_url = $this->plugins_url.'/js';
		$js_selection_url = $js_url.'/jquery.selection.js';
		$js_style_url = $js_url.'/style.js';
		
		//jsを読み込み
		$nonce = wp_nonce_field('ccc_nonce','_wpnonce',true,false);
		wp_enqueue_script('jquery');
		wp_enqueue_script('ccc-onload1', $js_selection_url ) ;		
		wp_enqueue_script('ccc-onload', $js_style_url, array('jquery'));
		wp_localize_script('ccc-onload', 'CCC', array(
	        'endpoint' => admin_url('admin-ajax.php'),
			'action' => 'cccAjax',
			'postID' => $postID,
			'nonce' => $nonce,
			'remote_addr' => $remote_addr
	    ));
	}
	
	
	
	/***
	 * ajaxでPOSTされた時の処理
	***/
	public function cccAjax() {
		
		//POST取得
		$copyText = $_POST['copyText'];
		$copyText = htmlspecialchars($copyText, ENT_QUOTES);
		
		$post_url = $_POST['url'];
		$post_url = htmlspecialchars($post_url, ENT_QUOTES);
		
		$postID = $_POST['postID'];
		
		$remote_addr = $_POST['remote_addr'];
		$remote_addr = htmlspecialchars($remote_addr, ENT_QUOTES);
		
		$referrer = $_POST['referrer'];
		$nonce = $_POST['nonce'];
		
		//nonceチェック
		if( wp_verify_nonce($_POST['nonce'], 'ccc_nonce')  )
		{
			$nonce = $_POST['nonce'];
		}
		else
		{
			//終了
			return;
		}

		
		//文字数チェック
		$str_num = mb_strlen( $copyText );
		$letters = get_option('ccc_plugin_value_letters');
		if( $letters > $str_num ){
			return ;
		}
		
		
		//補足データ作成
		$server_remote = $_SERVER["HTTP_USER_AGENT"];
		$time = date( "Y/m/d (D) H:i:s", time() );
		
		
		//TODO: データを保存
		//add_post_meta( $postID, 'ccc_plugin_value_copytext', $copyText );
		

		//メール用のデータ作成
		$mail = get_option('ccc_plugin_value_mail');
		$subject = get_option('ccc_plugin_value_subject');
		$reply = get_option('ccc_plugin_value_reply');
		$underText = __('It appears that the following characters have been copied', $this->textdomain);

$mail_body=<<<mail_body__END
---------------------------------------------------
{$underText}
---------------------------------------------------

{$copyText}

---------------------------------------------------
TIME: {$time}
URL: {$post_url}
IP: {$remote_addr}
Browser: {$server_remote}
Referrer: {$referrer}
---------------------------------------------------
mail_body__END;


		
		//メール送信
		if(!$this->debug_mode){
			$result = wp_mail( $mail,$subject, $mail_body, $reply );			
		}

		//デバッグ用のjson出力
		if($this->debug_mode){
			$charset = get_bloginfo( 'charset' );
			$array = array( 'massage' => $copyText, 'result' => $result, 'debug' => $this->debug_mode, 'nonce' => $nonce  );
			$json = json_encode( $array );
			nocache_headers();
			header( "Content-Type: application/json; charset=$charset" );
			echo $json;
		}
		
		die();
	}
	
	

	/***
	 * 管理画面
	***/
	public function ccc_admin_menu(){
		add_options_page(
			'Check Copy Content', //HTMLページのタイトル
			__('CCC settings', $this->textdomain ),//管理画面のメニュー
			'manage_options', //ユーザーレベル
			'Check_Copy_Content_admin_menu', //URLに入る名前
			array($this,'ccc_edit_setting')//機能を提供する関数
			//'[icon_url]'//アイコンURL
		);
	}
	
	
	/***
	 * 管理画面を表示
	***/
	function ccc_edit_setting(){
		if(!current_user_can('manage_options')) {
			wp_die();
		}
		// Render the settings template
		include(sprintf("%s/views/admin.php", dirname(__FILE__)));
	}	
	
	
	/***
	 * ローカライズ
	***/
	public function load_textdomain() {

		load_plugin_textdomain($this->textdomain, false, dirname(plugin_basename(__FILE__)) . '/languages/');

	}
	
	

    /**
     * プラグインが有効化されたときに実行されるメソッド
     * @return void
     */
    public function activationHook()
    {
		//オプションを初期値
        if (! get_option('ccc_plugin_value_mail'))
        {	
        	$admin_email = get_option('admin_email');
            update_option('ccc_plugin_value_mail', $admin_email);
        }
        
        if (! get_option('ccc_plugin_value_subject'))
        {
            update_option('ccc_plugin_value_subject', __('Blog copy notification', $this->textdomain));
        }

        if (! get_option('ccc_plugin_value_reply'))
        {
			$url = get_bloginfo('url');
			$url = parse_url($url);
			$reply = 'no-reply@'.$url['host'];
            update_option('ccc_plugin_value_reply', $reply);
        }
		
		//文字数
		if (! get_option('ccc_plugin_value_letters'))
        {
            update_option('ccc_plugin_value_letters', 30);
        }
        
        //ログインしてたらメールしない
        if (! get_option('ccc_plugin_value_login_flg'))
        {
            update_option('ccc_plugin_value_login_flg', 1);
        }
    }
    
    /***
     * 無効化ときに実行
    ***/
    public function deactivationHook()
    {

    	delete_option('ccc_plugin_value_mail');
		delete_option('ccc_plugin_value_subject');
		delete_option('ccc_plugin_value_reply');
		delete_option('ccc_plugin_value_letters');
		delete_option('ccc_plugin_value_login_flg');

    }
    
    /***
     * アンインストール時
    ***/
    public function uninstallHook()
    {
    
    	delete_option('ccc_plugin_value_mail');
		delete_option('ccc_plugin_value_subject');
		delete_option('ccc_plugin_value_reply');
		delete_option('ccc_plugin_value_letters');
		delete_option('ccc_plugin_value_login_flg');
    }


	/**
	 * WordPressの_eのエイリアス
	 * @param string $text
	 * @return void
	 */
	public function e($text){
		_e($text, $this->textdomain);
	}
	
	/**
	 * WordPressの__のエイリアス
	 * @param string $text
	 * @return string
	 */
	public function _($text){
		return __($text, $this->textdomain);
	}
	
    	
}
$CheckCopyContents = new CheckCopyContents();




?>