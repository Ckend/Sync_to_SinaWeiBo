# WordPress新浪微博同步助手

基于WordPress的新浪微博同步助手，发布新文章的时候将会自动同步到微博上。当然你需要提供以下信息给这个脚本：

- 账号
- 密码
- 新浪微博Appkey. 

此外，若你想要在WordPress以外的地方使用这个脚本，其实很简单，只要把wpHttp改成其他方式的post请求即可。下面是一个WordPress外的使用示例。

```php
function post_to_sina_weibo($status, $pic, $token) {
  $url='https://api.weibo.com/2/statuses/share.json';
  $status = urlencode($status);
  $options = array(
    'status' => $status,
    'access_token' => $token,
    'pic' => new \CurlFile($pic, 'image/png', '1.png')
  );
  $ch = curl_init();
  $timeout = 5;
  curl_setopt ($ch, CURLOPT_URL, $url);
  curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
  curl_setopt ($ch, CURLOPT_POSTFIELDS, $options);
  $file_contents = curl_exec(\$ch);
  curl_close($ch);
  echo $file_contents;
}

```

