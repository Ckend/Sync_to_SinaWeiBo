<?php
/**
* WordPress 发表文章时同步到新浪微博
* WordPress sync to Sina weibo when published a post;
* Code from https://alltoshare.com
*/
function sync_to_sina_weibo($post_ID) {
  if( wp_is_post_revision($post_ID) ) return;
  $appkey = get_option('appkey');
  $username = get_option('name');
  $userpassword = get_option('password');
  $user_content = get_option('content');
  $withPicture = get_option('withPicture');
  echo $withPicture;
  $get_post_info = get_post($post_ID);
  $get_post_excerpt = get_post($post_ID)->post_excerpt;
  $get_post_content = get_post($post_ID)->post_excerpt;
  $get_post_img = get_post($post_ID)->post_content;
	preg_match_all('/<img.*?(?: |\\t|\\r|\\n)?src=[\'"]?(.+?)[\'"]?(?:(?: |\\t|\\r|\\n)+.*?)?>/sim', $get_post_img, $strResult, PREG_PATTERN_ORDER);

  if(count($strResult[1]) > 0 && $withPicture == 1)
      $imgUrl = $strResult[1][0];//获得第一张图url地址
  else
      $imgUrl = null;
	echo $imgUrl;

  //去掉文章内的html编码的空格、换行、tab等符号
  $get_post_excerpt = str_replace("\t", " ", str_replace("\n", " ", str_replace("&nbsp;", " ", $get_post_excerpt)));
  $get_post_content = str_replace("\t", " ", str_replace("\n", " ", str_replace("&nbsp;", " ", $get_post_content)));

  //获取文章标题
  $get_post_title = get_post($post_ID)->post_title;
  $user_content = '%title%: %excerpt begin=0 len=120% ... 查看全文:%link%';
  if ( $get_post_info->post_status == 'publish' && $_POST['original_post_status'] != 'publish' ) {
	  $request = new WP_Http;
    if ($user_content != '') {
      // 微博文字
      $user_content = str_replace('%title%',strip_tags( $get_post_title ),$user_content);

      if(strstr($user_content,'%content ')!=FALSE){
        preg_match_all('/%content begin=\d* len=[1-9]\d*%/', $user_content, $user_content_content, PREG_PATTERN_ORDER);
        preg_match_all('/[0-9]\d*/', $user_content_content[0][0], $user_content_content_len, PREG_PATTERN_ORDER);
        $user_content = str_replace($user_content_content[0][0],mb_strimwidth(strip_tags( apply_filters('the_content', $get_post_content)),$user_content_content_len[0][0], $user_content_content_len[0][1]),$user_content);
      }
      if(strstr($user_content, '%excerpt ')!=FALSE){
        preg_match_all('/%excerpt begin=\d* len=[1-9]\d*%/', $user_content, $user_content_content, PREG_PATTERN_ORDER);
        preg_match_all('/[0-9]\d*/', $user_content_content[0][0], $user_content_content_len, PREG_PATTERN_ORDER);
        $user_content = str_replace($user_content_content[0][0],mb_strimwidth(strip_tags( apply_filters('the_content', $get_post_excerpt)),$user_content_content_len[0][0], $user_content_content_len[0][1]),$user_content);
      }
      $user_content = str_replace('%link%',get_permalink($post_ID),$user_content);

      echo $user_content;
      $status = $user_content;
    }else{
  	  $status = '【' . strip_tags( $get_post_title ) . '】 ' . mb_strimwidth(strip_tags( apply_filters('the_content', $get_post_excerpt)),0, 132,'...') . ' 全文地址:' . get_permalink($post_ID) ;
    }
	  $body = array( 'status' => $status, 'source'=> $appkey);
	  $headers = array('Authorization' => 'Basic ' . base64_encode("$username:$userpassword"));
    $api_url = 'https://api.weibo.com/2/statuses/share.json';
    if($imgUrl!==null)
	  {
	    $body['pic'] = $imgUrl;
	    uksort($body, 'strcmp');
	      $str_b=uniqid('------------------');
	      $str_m='--'.$str_b;
	      $str_e=$str_m. '--';
	      $tmpbody='';
	      foreach($body as $k=>$v){
	          if($k=='pic'){
	              $img_c=file_get_contents($imgUrl);
	              $url_a=explode('?', basename($imgUrl));
	              $img_n=$url_a[0];
	              $tmpbody.=$str_m."\r\n";
	              $tmpbody.='Content-Disposition: form-data; name="'.$k.'"; filename="'.$img_n.'"'."\r\n";
	              $tmpbody.="Content-Type: image/unknown\r\n\r\n";
	              $tmpbody.=$img_c."\r\n";
	          }else{
	              $tmpbody.=$str_m."\r\n";
	              $tmpbody.='Content-Disposition: form-data; name="'.$k."\"\r\n\r\n";
	              $tmpbody.=$v."\r\n";
	          }
	      }
	      $tmpbody.=$str_e;
	      $body = $tmpbody;
	    	$headers['Content-Type'] = 'multipart/form-data; boundary='.$str_b;
	    }
      $result = $request->post( $api_url , array( 'body' => $body, 'headers' => $headers ) );
	}
}
?>
