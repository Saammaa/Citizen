<?php

/**
 * 适用于 Citizen 的音乐解析 API。
 * 仅支持网易云音乐。
 *
 * @see https://github.com/injahow/meting-api
 */

// 该文件应且只应输出 ERROR 层信息
error_reporting(1);

define('API_URI', api_uri());

const CACHE = true;
const CACHE_TIME = 86400;

// i.I@a12fb2b6 在未启用 APCU 扩展的 PHP 环境中无法正常工作
// 已从 true 转为 false，若需要使用 APCU，请手动切换为 true
const APCU_CACHE = false;

$type = $_GET['type'];
$id = $_GET['id'];

if (in_array($type, ['song', 'playlist'])) {
	header('content-type: application/json; charset=utf-8;');
} else if (in_array($type, ['name', 'artist'])) {
	header('content-type: text/plain; charset=utf-8;');
}

header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

$api = new Meting;
$api->format();

if ($type == 'playlist') {
	$file_path = __DIR__ . '/cache/playlist/' . $id . '.json';
	if (file_exists($file_path)) {
		if ($_SERVER['REQUEST_TIME'] - filemtime($file_path) < CACHE_TIME) {
			echo file_get_contents($file_path);
			exit;
		}
	}

	$data = $api->playlist($id);
	if ($data == '[]') {
		echo '{"error":"unknown playlist id"}';
		exit;
	}
	$data = json_decode($data);
	$playlist = array();

	foreach ($data as $song) {
		$playlist[] = array(
			'name' => $song->name,
			'artist' => implode('/', $song->artist),
			'url' => API_URI . '?type=url&id=' . $song->url_id,
			'pic' => API_URI . '?type=pic&id=' . $song->pic_id,
		);
	}
	$playlist = json_encode($playlist);

	if (CACHE) {
		file_put_contents($file_path, $playlist);
	}

	echo $playlist;
} else {
	$songNeeded = !in_array($type, ['url', 'pic']);
	if ($songNeeded && !in_array($type, ['name', 'artist', 'song'])) {
		echo '{"error":"unknown type"}';
		exit;
	}

	if (APCU_CACHE) {
		$apcuTime = $type == 'url' ? 600 : 36000;
		$apcuTypeKey = $type . $id;
		if (apcu_exists($apcuTypeKey)) {
			$data = apcu_fetch($apcuTypeKey);
			returnData($type, $data);
		}
		if ($songNeeded) {
			$apcuSongIdKey = 'song_id' . $id;
			if (apcu_exists($apcuSongIdKey)) {
				$song = apcu_fetch($apcuSongIdKey);
			}
		}
	}

	if (!$songNeeded) {
		$data = song2data($api, null, $type, $id);
	} else {
		if (!isset($song))
			$song = $api->song($id);
		if ($song == '[]') {
			echo '{"error":"unknown song"}';
			exit;
		}
		if (APCU_CACHE) {
			apcu_store($apcuSongIdKey, $song, $apcuTime);
		}
		$data = song2data($api, json_decode($song)[0], $type, $id);
	}

	if (APCU_CACHE) {
		apcu_store($apcuTypeKey, $data, $apcuTime);
	}

	returnData($type, $data);
}

function api_uri(): string {
	return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://') . $_SERVER['HTTP_HOST'] . strtok($_SERVER['REQUEST_URI'], '?');
}

function song2data($api, $song, $type, $id) {
	$data = '';
	switch ($type) {
		case 'name':
			$data = $song->name;
			break;

		case 'artist':
			$data = implode('/', $song->artist);
			break;

		case 'url':
			$m_url = json_decode($api->url($id, 320))->url;
			if ($m_url == '') break;
			if ($m_url[4] != 's') {
				$m_url = str_replace('http', 'https', $m_url);
			}

			$data = $m_url;
			break;

		case 'pic':
			$data = json_decode($api->pic($id, 480))->url;
			break;

		case 'song':
			$data = json_encode(
				array(
					array(
						'name' => $song->name,
						'artist' => implode('/', $song->artist),
						'url' => API_URI . '?type=url&id=' . $song->url_id,
						'pic' => API_URI . '?type=pic&id=' . $song->pic_id,
					)
				)
			);
			break;
	}

	if ($data == '') exit;
	return $data;
}

function returnData($type, $data): void {
	if (in_array($type, ['url', 'pic'])) {
		header('Location: ' . $data);
	} else {
		echo $data;
	}
	exit;
}

class Meting {
	public $raw;
	public $data;
	public $info;
	public $error;
	public $status;

	public $proxy = null;
	public $format = false;
	public $header;

	public function __construct() {
		$this->header = $this->curlSet();
	}

	public function cookie($value): static {
		$this->header['Cookie'] = $value;
		return $this;
	}

	public function format($value = true): static {
		$this->format = $value;
		return $this;
	}

	private function exec($api) {
		if (isset($api['encode'])) {
			$api = call_user_func_array(array($this, $api['encode']), array($api));
		}
		if ($api['method'] == 'GET') {
			if (isset($api['body'])) {
				$api['url']  .= '?' . http_build_query($api['body']);
				$api['body'] = null;
			}
		}

		$this->curl($api['url'], $api['body']);

		if (!$this->format)
			return $this->raw;

		$this->data = $this->raw;

		if (isset($api['decode'])) {
			$this->data = call_user_func_array(array($this, $api['decode']), array($this->data));
		}
		if (isset($api['format'])) {
			$this->data = $this->clean($this->data, $api['format']);
		}

		return $this->data;
	}

	private function curl($url, $payload = null): void {
		$header = array_map(function ($k, $v) {
			return $k . ': ' . $v;
		}, array_keys($this->header), $this->header);

		$curl = curl_init();

		if (!is_null($payload)) {
			curl_setopt($curl, CURLOPT_POST, 1);
			curl_setopt($curl, CURLOPT_POSTFIELDS, is_array($payload) ? http_build_query($payload) : $payload);
		}
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_TIMEOUT, 20);
		curl_setopt($curl, CURLOPT_ENCODING, 'gzip');
		curl_setopt($curl, CURLOPT_IPRESOLVE, 1);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $header);

		if ($this->proxy) {
			curl_setopt($curl, CURLOPT_PROXY, $this->proxy);
		}

		for ($i = 0; $i < 3; $i++) {
			$this->raw    = curl_exec($curl);
			$this->info   = curl_getinfo($curl);
			$this->error  = curl_errno($curl);
			$this->status = $this->error ? curl_error($curl) : '';
			if (!$this->error)
				break;
		}

		curl_close($curl);
	}

	private function pickup($array, $rule) {
		$t = explode('.', $rule);
		foreach ($t as $vo) {
			if (!isset($array[$vo]))
				return array();
			$array = $array[$vo];
		}

		return $array;
	}

	private function clean($raw, $rule): bool|string {
		$raw = json_decode($raw, true);

		if (!empty($rule)) {
			$raw = $this->pickup($raw, $rule);
		}
		if (!isset($raw[0]) && count($raw)) {
			$raw = array($raw);
		}

		$result = array_map(array($this, 'fnFormat'), $raw);
		return json_encode($result);
	}

	public function search($keyword, $option = null) {
		$api = array(
			'method' => 'POST',
			'url'    => 'http://music.163.com/api/cloudsearch/pc',
			'body'   => array(
				's'      => $keyword,
				'type'   => $option['type'] ?? 1,
				'limit'  => $option['limit'] ?? 30,
				'total'  => 'true',
				'offset' => isset($option['page']) && isset($option['limit']) ? ($option['page'] - 1) * $option['limit'] : 0,
			),
			'encode' => 'fnAES',
			'format' => 'result.songs',
		);

		return $this->exec($api);
	}

	public function song($id) {
		$api = array(
			'method' => 'POST',
			'url'    => 'http://music.163.com/api/v3/song/detail/',
			'body'   => array(
				'c' => '[{"id":' . $id . ',"v":0}]',
			),
			'encode' => 'fnAES',
			'format' => 'songs',
		);

		return $this->exec($api);
	}

	public function album($id) {
		$api = array(
			'method' => 'POST',
			'url' => 'https://music.163.com/api/v1/album/' . $id,
			'body'   => array(
				'total'	=> 'true',
				'offset' => '0',
				'id' => $id,
				'limit' => '1000',
				'ext' => 'true',
				'private_cloud' => 'true',
			),
			'encode' => 'fnAES',
			'format' => 'songs',
		);

		return $this->exec($api);
	}

	public function artist($id, $limit = 50) {
		$api = array(
			'method' => 'POST',
			'url' => 'https://music.163.com/api/v1/artist/' . $id,
			'body' => array(
				'ext' => 'true',
				'private_cloud' => 'true',
				'top' => $limit,
				'id' => $id,
			),
			'encode' => 'fnAES',
			'format' => 'hotSongs',
		);

		return $this->exec($api);
	}

	public function playlist($id) {
		$api = array(
			'method' => 'POST',
			'url' => 'http://music.163.com/api/v6/playlist/detail',
			'body' => array(
				's' => '0',
				'id' => $id,
				'n' => '1000',
				't' => '0',
			),
			'encode' => 'fnAES',
			'format' => 'playlist.tracks',
		);

		return $this->exec($api);
	}

	public function url($id, $br = 320) {
		$api = array(
			'method' => 'POST',
			'url' => 'http://music.163.com/api/song/enhance/player/url',
			'body' => array(
				'ids' => array($id),
				'br' => $br * 1000,
			),
			'encode' => 'fnAES',
			'decode' => 'fnUrl',
		);

		$this->temp['br'] = $br;

		return $this->exec($api);
	}

	public function pic($id, $size = 300) {
		$url = 'https://p3.music.126.net/' . $this->encryptId($id) . '/' . $id . '.jpg?param=' . $size . 'y' . $size;
		return json_encode(array('url' => $url));
	}

	private function curlSet() {
		return array(
			'Referer'         => 'https://music.163.com/',
			'Cookie'          => 'appver=8.2.30; os=iPhone OS; osver=15.0; EVNSM=1.0.0; buildver=2206; channel=distribution; machineid=iPhone13.3',
			'User-Agent'      => 'Mozilla/5.0 (iPhone; CPU iPhone OS 15_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Mobile/15E148 CloudMusic/0.1.1 NeteaseMusic/8.2.30',
			'X-Real-IP'       => long2ip(mt_rand(1884815360, 1884890111)),
			'Accept'          => '*/*',
			'Accept-Language' => 'zh-CN,zh;q=0.8,gl;q=0.6,zh-TW;q=0.4',
			'Connection'      => 'keep-alive',
			'Content-Type'    => 'application/x-www-form-urlencoded',
		);
	}

	private function getRandomHex($length) {
		return bin2hex(random_bytes($length / 2));
	}

	private function bchexdec($hex) {
		$dec = 0;
		$len = strlen($hex);
		for ($i = 1; $i <= $len; $i++) {
			$dec = bcadd($dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));
		}

		return $dec;
	}

	private function bcdechex($dec) {
		$hex = '';
		do {
			$last = bcmod($dec, 16);
			$hex  = dechex($last) . $hex;
			$dec  = bcdiv(bcsub($dec, $last), 16);
		} while ($dec > 0);

		return $hex;
	}

	private function str2hex($string) {
		$hex = '';
		for ($i = 0; $i < strlen($string); $i++) {
			$ord     = ord($string[$i]);
			$hexCode = dechex($ord);
			$hex     .= substr('0' . $hexCode, -2);
		}

		return $hex;
	}

	private function fnAES($api) {
		$modulus = '157794750267131502212476817800345498121872783333389747424011531025366277535262539913701806290766479189477533597854989606803194253978660329941980786072432806427833685472618792592200595694346872951301770580765135349259590167490536138082469680638514416594216629258349130257685001248172188325316586707301643237607';
		$pubkey  = '65537';
		$nonce   = '0CoJUm6Qyw8W8jud';
		$vi      = '0102030405060708';

		if (extension_loaded('bcmath')) {
			$skey = $this->getRandomHex(16);
		} else {
			$skey = 'B3v3kH4vRPWRJFfH';
		}

		$body = json_encode($api['body']);

		if (function_exists('openssl_encrypt')) {
			$body = openssl_encrypt($body, 'aes-128-cbc', $nonce, false, $vi);
			$body = openssl_encrypt($body, 'aes-128-cbc', $skey, false, $vi);
		}

		if (extension_loaded('bcmath')) {
			$skey = strrev(utf8_encode($skey));
			$skey = $this->bchexdec($this->str2hex($skey));
			$skey = bcpowmod($skey, $pubkey, $modulus);
			$skey = $this->bcdechex($skey);
			$skey = str_pad($skey, 256, '0', STR_PAD_LEFT);
		} else {
			$skey = '85302b818aea19b68db899c25dac229412d9bba9b3fcfe4f714dc016bc1686fc446a08844b1f8327fd9cb623cc189be00c5a365ac835e93d4858ee66f43fdc59e32aaed3ef24f0675d70172ef688d376a4807228c55583fe5bac647d10ecef15220feef61477c28cae8406f6f9896ed329d6db9f88757e31848a6c2ce2f94308';
		}

		$api['url']  = str_replace('/api/', '/weapi/', $api['url']);
		$api['body'] = array(
			'params'    => $body,
			'encSecKey' => $skey,
		);

		return $api;
	}

	private function encryptId($id) {
		$magic   = str_split('3go8&$8*3*3h0k(2)2');
		$song_id = str_split($id);
		for ($i = 0; $i < count($song_id); $i++) {
			$song_id[$i] = chr(ord($song_id[$i]) ^ ord($magic[$i % count($magic)]));
		}
		$result = base64_encode(md5(implode('', $song_id), 1));
		$result = str_replace(array('/', '+'), array('_', '-'), $result);

		return $result;
	}

	private function fnUrl($result): bool|string {
		$data = json_decode($result, true);
		if (isset($data['data'][0]['uf']['url'])) {
			$data['data'][0]['url'] = $data['data'][0]['uf']['url'];
		}
		if (isset($data['data'][0]['url'])) {
			$url = array(
				'url'  => $data['data'][0]['url'],
				'size' => $data['data'][0]['size'],
				'br'   => $data['data'][0]['br'] / 1000,
			);
		} else {
			$url = array(
				'url'  => '',
				'size' => 0,
				'br'   => -1,
			);
		}

		return json_encode($url);
	}

	protected function fnFormat($data): array {
		$result = array(
			'id'       => $data['id'],
			'name'     => $data['name'],
			'artist'   => array(),
			'album'    => $data['al']['name'],
			'pic_id'   => $data['al']['pic_str'] ?? $data['al']['pic'],
			'url_id'   => $data['id'],
		);

		if (isset($data['al']['picUrl'])) {
			preg_match('/\/(\d+)\./', $data['al']['picUrl'], $match);
			$result['pic_id'] = $match[1];
		}

		foreach ($data['ar'] as $vo) {
			$result['artist'][] = $vo['name'];
		}

		return $result;
	}
}
