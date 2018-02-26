<?php
/*
Plugin Name: Sync_to_SinaWeiBo
Plugin URI: https://github.com/Ckend/Sync_to_SinaWeiBo
Description: 新浪微博同步助手，发布新文章的时候将会自动同步到微博上，可自定义微博内容模板，是否带图片。
Version: 1.0
Author: Alltoshare
Author URI: https://alltoshare.com
E-mail: ckend@alltoshare.com
License: GPL
*/
if ( ! defined( 'WPINC' ) ) {
    die;
}

define( 'Sync_Weibo_PATH', plugin_dir_path( __FILE__ ) );

include_once( Sync_Weibo_PATH . "class-weibo.php" );
add_action('save_post', 'sync_to_sina_weibo', 10, 1);

add_action('admin_menu', 'Sync_Weibopage');
function Sync_Weibopage() {
  //call register settings function
	add_action( 'admin_init', 'register_Sync_Weibosettings' );
  add_options_page('微博自动同步助手','微博自动同步助手', 'manage_options', 'Sync_Weibo', 'Sync_Weibosettings_page' );
}

function register_Sync_Weibosettings() {
	//register our settings
	register_setting( 'sync-weibo-settings', 'appkey' );
  register_setting( 'sync-weibo-settings', 'name' );
  register_setting( 'sync-weibo-settings', 'password' );
  register_setting( 'sync-weibo-settings', 'content' );
  register_setting( 'sync-weibo-settings', 'withPicture' );
}

function Sync_Weibosettings_page() {
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }
?>
<div class="wrap">
<h2>同步新浪微博配置(Sync Weibo Options)</h2><br />
<div id="poststuff" class="metabox-holder has-right-sidebar">
  <div class="inner-sidebar">
    <div style="position:absolute;" class="meta-box-sortabless ui-sortable" id="side-sortables">
      <div class="postbox" id="sm_pnres">
            <h3 class="hndle"><span>捐赠</span></h3>
            <div class="inside" style="margin:0;padding-top:10px;background-color:#ffffe0;">
                <p>
                  本插件由<a href="https://alltoshare.com">极致分享</a>开发和维护，如果你想支持开发者，可以以微信支付的方式进行捐助。非常感谢您的支持!
                </p>
                  <br />
                  <?php
                  $file = Sync_Weibo_PATH . "Support.png";
                  if($fp = fopen($file,"rb", 0))
                  {
                      $gambar = fread($fp,filesize($file));
                      fclose($fp);
                      $base64 = chunk_split(base64_encode($gambar));
                      $encode = '<img src="data:image/png;base64,' . $base64 .'" alt="微信支付" width="250"/>';
                      echo $encode;
                  }
                    ?>
            </div>
        </div>
    </div>
  </div>
	<div class="has-sidebar-content" id="post-body-content">
    <p>若您不需要使用自定义微博模板，将其置空即可使用默认模板。</p>
    <p>模板支持以下元字符：</p>
    <p>%title%: 提取标题</p>
    <p>%excerpt begin=0 len=120%: 提取摘要。将取从begin开始的len个长度的字符。</p>
    <p>%content begin=0 len=120%: 提取内容。 注意事项同上。</p>
    <p>%link%: 提取文章链接。</p>
    <p>模板中以上字符仅允许出现一次，另外%link%必须出现一次，此外len不应该超过120。</p>
    <p>缺少%link%, 或者文字长度超出微博允许范围将可能同步失败。</p>
    <p>示例： %title%: %excerpt begin=0 len=120% ... 查看全文:%link%</p>
		<form method="post" action="options.php">
		  <?php settings_fields( 'sync-weibo-settings' ); ?>
			<table class="form-table">
				<tr valign="top">
				</tr>
				<tr valign="top">
				<th scope="row">新浪微博账号(Sina Weibo name) </th>
				<td>
					<input name="name" type="text" id="name" value="<?php echo get_option('name'); ?>" class="regular-text" />
				</td>
				</tr>
				<tr valign="top">
				<th scope="row">新浪微博密码(Sina Weibo password)</th>
				<td>
					<input name="password" type="password" id="password" value="<?php echo get_option('password'); ?>" class="regular-text" />
				</td>
				</tr>
				<tr valign="top">
				<th scope="row">新浪微博appkey(Sina Weibo appkey)</th>
				<td>
					<input name="appkey" type="text" id="appkey" value="<?php echo get_option('appkey'); ?>" class="regular-text" />
				</td>
				</tr>
        <tr valign="top">
				<th scope="row">自定义微博模板</th>
				<td>
					<input name="content" type="text" id="content" value="<?php echo get_option('content'); ?>" class="regular-text" />
				</td>
        </tr>
			</table>
      <th scope="row">是否带图片     </th>
      <td>
        <input name="withPicture" type="radio" id="withPicture_no" value="0"<?php checked(0, get_option('withPicture')); ?> />
        <label class="description">否</label>
        <input name="withPicture" type="radio" id="withPicture_yes" value="1"<?php checked(1, get_option('withPicture')); ?> />
        <label class="description">是</label>
      </td>
		  <p class="submit">
			<input type="submit" class="button-primary" value="<?php  _e('Save Changes') ?>" />
			</p>
		</form>
	</div>
</div>
</div>



<?php
}

?>
