<?php

/** @noinspection PhpUnhandledExceptionInspection */
check_lock_file();
$SCRIPT_NAME = basename(__FILE__);
$STEP = get_step();
if ($STEP === FALSE) redirect("/" . $SCRIPT_NAME . "?step=0");
$STEPS = build_steps();

header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

//region 主页面
?>

<!doctype html>
<html lang="zh-cn">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
	<title>Discuz! Q 安装/升级向导 </title>
	<link href="https://dl.discuz.chat/lib/4.5.0/bootstrap.min.css" rel="stylesheet">
	<script src="https://dl.discuz.chat/lib/4.5.0/jquery.min.js"></script>
	<script src="https://dl.discuz.chat/lib/4.5.0/bootstrap.min.js"></script>
	<style>
		html {
			font-size: 16px;
		}

		body {
			font-family: -apple-system, BlinkMacSystemFont, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol", "Segoe UI", "PingFang SC", "Hiragino Sans GB", "Microsoft YaHei", "Helvetica Neue", Helvetica, Arial, sans-serif;
			font-weight: 500
		}

		h1,
		h2,
		h3,
		h4,
		h5,
		h6 {
			margin-bottom: 0;
		}

		p {
			margin-top: 0.25em;
			margin-bottom: 0.25em;
		}

		.container {
			max-width: 960px;
			padding-top: 80px;
		}

		.navbar .in .nav li {
			display: block;
			float: none;
			width: 100%;
		}

		.hidden {
			display: none;
		}
	</style>
	<script>
		function next_step() {
			$('#button_next').addClass('disabled');
			$('#spinner').removeClass('hidden');
			if ($('#userform').length !== 0) {
				$('#userform').submit();
				return;
			}
			if ($('#userforminstall').length !== 0) {
				$.post('<?= get_server_url() . "/install" ?>', $('#userforminstall').serialize())
					.done(function(data) {
						localStorage.clear();
						localStorage.setItem('officeDb_Authorization', JSON.stringify(data.token));
						window.location.href = '/';
					})
					.fail(function(data) {
						$('#button_next').removeClass('disabled');
						$('#spinner').addClass('hidden');
						$('#errormsgdiv').removeClass('hidden');
						if (data.responseText !== undefined) {
							$('#errormsg').text("错误信息：" + data.responseText);
						} else {
							$('#errormsg').text("错误信息：" + data);
						}
					});

				return;
			}
			window.location.href = "/<?= $SCRIPT_NAME ?>?step=<?= $STEP + 1 ?>";
		}
	</script>
</head>

<body>
	<nav class="navbar fixed-top navbar-expand-sm navbar-light bg-light">
		<a class="navbar-brand" href=""><img alt="discuzq logo" src="https://discuz.chat/static/images/logo.png" height="32" /> 安装/升级向导 </a>
		<button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
			<span class="navbar-toggler-icon"></span>
		</button>
	</nav>
	<div class="container">
		<div class="d-flex justify-content-center">
			<div class="spinner-border hidden my-3" role="status" id="spinner">
				<span class="sr-only">Loading...</span>
			</div>
		</div>
		<?php try {
			$func = $STEPS[$STEP];
			if ($func) {
				call_user_func($func);
			} else {
				redirect("/" . $SCRIPT_NAME . "?step=0");
			} ?>
			<div class="row mb-3">
				<div class="col">
					<?php if ($STEP > 0) { ?>
						<a class="btn btn-outline-secondary" href="javascript:history.back()">上一步</a>
					<?php } ?>
					<a class="btn btn-primary float-right" id="button_next" href="javascript:next_step();">下一步</a>
				</div>
			</div>
		<?php } catch (Exception $e) { ?>
			<div class="alert alert-danger" role="alert">
				<p>安装无法继续，请纠正以下错误。</p>
				<p>错误信息：</p>
				<p><?= $e->getMessage() ?></p>
			</div>
			<div class="row mb-3">
				<div class="col">
					<?php if ($STEP > 1) { ?>
						<a class="btn btn-outline-secondary" href="javascript:history.back()">上一步</a>
					<?php } ?>
					<a class="btn btn-outline-primary float-right" href="javascript:window.location.href=window.location.href">重试</a>
				</div>
			</div>
		<?php } ?>
	</div>

</body>

</html>

<?php
//endregion

//region 锁定文件
function check_lock_file()
{
	$thisscript = __FILE__;
	if (file_exists($thisscript . ".lock")) {
?>
		为了安全，本文件每次运行成功后会被自动锁定，如果想再次运行，请删除 dl.php.lock 后重试。
	<?php
		exit(0);
	}
}

function make_lock_file()
{
	$thisscript = __FILE__;
	file_put_contents($thisscript . ".lock", "locked");
}

//endregion

//region 镜像相关
function check_image()
{
	$current_dir = __DIR__;
	$parent_dir = realpath(dirname($current_dir));
	$file = $parent_dir . "/.dzq-image";
	if (!file_exists($file)) return FALSE;
	return file_get_contents($file);
}

function image_welcome()
{
	$parsed = parse_url(get_server_url());
	$url = $parsed['scheme'] . "://" . $parsed['host'] . ':8888';
	?>
	<div class="jumbotron">
		<h1 class="display-4">欢迎使用Discuz! Q官方镜像</h1>
		<p class="lead">本程序将帮助你下载并安装Discuz! Q的最新版本</p>
		<hr class="my-4">
		<ul>
			<li>本镜像基于CentOS和宝塔，如果你是第一次运行，点击<a href="<?= $url ?>" target="_blank">此处</a>立即修改你的宝塔管理员密码</li>
			<li>如果宝塔无法访问，请检查自己的<a href="https://console.cloud.tencent.com/cvm/securitygroup" target="_blank">安全组设置</a></li>
			<li>宝塔中已经安装并配置了MySQL 5.7和Nginx</li>
			<li>MySQL已经建立了一个名为discuz的数据库，用户名也是discuz，密码请在宝塔中查看</li>
		</ul>
	</div>
<?php
}

//endregion

//region Step 0: 检查系统环境

function check_env()
{
	global $LANG;
	$env_check = array(
		'php_version' => true,
		'https' => true
	);

	$LANG['php_version'] = 'PHP版本要求 7.2以上';
	if (version_compare(phpversion(), '7.2.0', '<')) {
		$env_check['php_version'] = false;
	}

	$ext_check = extension_check();
	$env_check = array_merge($env_check, $ext_check);

	$func_check = function_check();
	$env_check = array_merge($env_check, $func_check);

	$LANG['https'] = '站点推荐使用HTTPS';
	if (!is_https()) {
		$env_check['https'] = 'warn';
	}

	return $env_check;
}


/*
 * 检查扩展模块是否加载，并生成$LANG相关文字
 */
function extension_check()
{
	global $LANG;
	$ext_check = array();
	$needed_extensions = array(
		'bcmath', 'ctype', 'curl', 'dom', 'fileinfo', 'gd', 'json', 'mbstring', 'openssl', 'pdo', 'pdo_mysql', 'tokenizer', 'xml', 'zip'
	);

	$all_passed = true;
	foreach ($needed_extensions as $ext) {
		if (!extension_loaded($ext)) {
			$all_passed = false;
			$ext_check['extension_' . $ext] = false;
			$LANG['extension_' . $ext] = "PHP扩展要求支持 " . $ext;
		}
	}
	if ($all_passed) {
		$ext_check['extension_all'] = true;
		$LANG['extension_all'] = "PHP扩展检查";
	}

	return $ext_check;
}

function function_check()
{
	global $LANG;
	$func_check = array();
	$needed_functions = array(
		'symlink', 'readlink', 'putenv', 'realpath'
	);

	$all_passed = true;
	foreach ($needed_functions as $func) {
		if (!function_exists($func)) {
			$all_passed = false;
			$func_check['function_' . $func] = false;
			$LANG['function_' . $func] = "PHP函数要求启用 " . $func;
		}
	}
	if ($all_passed) {
		$func_check['function_all'] = true;
		$LANG['function_all'] = "PHP函数检查";
	}

	$LANG['function_openssl_pkey_new'] = "PHP函数openssl生成密钥测试";
	$LANG['function_openssl_pkey_export'] = "PHP函数openssl导出密钥测试";
	$func_check['function_openssl_pkey_new'] = true;
	$func_check['function_openssl_pkey_export'] = true;
	$pkey = openssl_pkey_new(['private_key_bits' => 2048]);
	if ($pkey === FALSE) {
		$func_check['function_openssl_pkey_new'] = false;
		$func_check['function_openssl_pkey_export'] = false;
	} else {
		$func_check['function_openssl_pkey_export'] = openssl_pkey_export($pkey, $pkeyout);
	}

	return $func_check;
}

function check_env_and_extension()
{
	global $LANG;
	$env_check = check_env();
	$check_all = true;
?>
	<div class="alert alert-primary" role="alert">
		<h2>说明</h2>
		<p>在运行本程序之前，请先确认已经<a href="https://www.dnspod.cn/promo/discuzq" target="_blank">申请过Discuz! Q的内测资格</a>，并在<a href="https://console.cloud.tencent.com/cam/capi">API密钥管理</a>处获取了自己的Secret ID和Secret Key。</p>
		<p>请先配置好服务器的域名和SSL等，使用用户要访问的域名来访问本安装程序。</p>
		<p>本程序用于帮助用户完成在安装Discuz! Q之前必要的环境检查、下载与安装工作，也可用于Discuz! Q升级时的下载。
			本程序<b>不能自动完成</b>HTTP服务器的配置，数据库的安装配置，Discuz! 升级脚本执行等任务。</p>
		<p>本程序运行过程中的提示都很重要，请一定认真阅读</p>
	</div>
	<div class="alert alert-primary" role="alert">
		环境检查
	</div>
	<ul class="list-group my-3">
		<?php foreach ($env_check as $chk => $result) {
			if (!$result) $check_all = false;
		?>
			<li class="list-group-item d-flex justify-content-between align-items-center">
				<?= $LANG[$chk] ?>
				<?php if ($result === 'warn') { ?>
					<span class="badge badge-warning">警告</span>
				<?php } else if ($result) { ?>
					<span class="badge badge-primary">成功</span>
				<?php } else { ?>
					<span class="badge badge-danger">失败</span>
				<?php } ?>
			</li>
		<?php } ?>
	</ul>
<?php
	if (!$check_all) throw new Exception('环境检查失败，请按要求确认PHP版本，启用PHP扩展与函数。如果在Windows下openssl函数检查失败，请参考<a href="https://discuz.chat/docs/install_faq.html#windows%E4%B8%8Bssl%E7%9B%B8%E5%85%B3%E5%87%BD%E6%95%B0%E4%B8%8D%E5%8F%AF%E7%94%A8" target="_blank">这里</a>进行配置');
}

//endregion

//region Step 1: 检查目录结构
function pre_check()
{
	global $SCRIPT_NAME;
	$pre_check = array(
		'base_name' => true,
		'get_parent_dir' => true,
		'dir_name' => true,
		'base_writable' => true,
		'parent_writable' => true
	);
	$LANG = array();
	$check_all = true;

	$dir = __DIR__;
	$base_name = basename($dir);
	$LANG['base_name'] = "检查当前目录 $dir 目录名称是否为 public";
	if ($base_name !== 'public') {
		$pre_check['base_name'] = false;
	}

	$dir_name = realpath(dirname($dir));
	$LANG['get_parent_dir'] = "检查是否可获取到上级目录";
	if (!$dir_name) {
		$pre_check['get_parent_dir'] = false;
	}

	$LANG['dir_name'] = "检查上级目录 $dir_name 不能为根目录";
	if (!$dir_name || $dir_name === "/" || preg_match("/^[a-z|A-Z]:[\/|\\\]?$/m", $dir_name)) {
		$pre_check['dir_name'] = false;
	}

	$LANG['base_writable'] = "检查当前目录 $dir 是否可写";
	if (!writable_check($dir)) {
		$pre_check['base_writable'] = false;
	}

	$LANG['parent_writable'] = "检查上级目录 $dir_name 是否可写";
	if (!writable_check($dir_name)) {
		$pre_check['parent_writable'] = false;
	}
?>
	<div class="alert alert-primary" role="alert">
		<h2>安装目录预检查</h2>
	</div>
	<p> 在安装之前，请在准备安装的目录中建立一个 discuz 目录，并在 discuz 目录下再建立一个 public 子目录，将本文件放在 public 下。创建完的目录结构类似于： </p>
	<pre>
   /wwwroot
    └── discuz
	└── public
	    └──<?= $SCRIPT_NAME ?>
	</pre>
	<p>
		然后请阅读<a href="https://discuz.chat/docs/install.html#web-%E6%9C%8D%E5%8A%A1%E5%99%A8%E9%85%8D%E7%BD%AE">安装文档中关于Web服务器配置的内容</a>，配置好Web服务器，
		将Web服务器的根目录指向刚刚建立的 public 目录。
	</p>
	<p>
		如果使用宝塔，请设置 网站目录 指向 discuz 目录，设置 运行目录 为 /public
	</p>
	<ul class="list-group my-3">
		<?php foreach ($pre_check as $chk => $result) {
			if (!$result) $check_all = false;
		?>
			<li class="list-group-item d-flex justify-content-between align-items-center">
				<?= $LANG[$chk] ?>
				<?php if ($result === 'warn') { ?>
					<span class="badge badge-warning">警告</span>
				<?php } else if ($result) { ?>
					<span class="badge badge-primary">成功</span>
				<?php } else { ?>
					<span class="badge badge-danger">失败</span>
				<?php } ?>
			</li>
		<?php } ?>
	</ul>
	<?php
	if (!$check_all) {
		throw new Exception("预检查失败，请修复以上失败的检查后重试");
	}
	?>
	<div class="alert alert-success" role="alert">
		<p>预检查成功。</p>
		<p>当前目录 <?= $dir ?></p>
		<?php if ($ver = existing_installation()) { ?>
			<p>存在Discuz! Q <?= $ver ?>，继续下一步将进行升级</p>
		<?php } else { ?>
			<p>Discuz! Q将被安装到 <?= $dir_name ?>，点击下一步继续安装</p>
		<?php } ?>
	</div>
<?php
}

//endregion

//region Step 2: 用户选择版本

function download_package()
{
	global $STEP, $SCRIPT_NAME;
	$packages = download_json("https://cloud.discuz.chat/packages.json");
	if (!isset($packages->includes)) {
		throw new Exception("元数据解析错误");
	}
	$detail_url_array = array_keys(get_object_vars($packages->includes));
	if (sizeof($detail_url_array) != 1) {
		throw new Exception("元数据内容错误");
	}
	$detail_url = "https://cloud.discuz.chat/" . $detail_url_array[0];

	$detail_packages = download_json($detail_url);
	if (!isset($detail_packages->packages)) {
		throw new Exception("详细元数据解析错误");
	}
	$detail_packages = $detail_packages->packages;
	if (!isset($detail_packages->{'qcloud/discuz'})) {
		throw new Exception("详细元数据无法获取qcloud/discuz");
	}
	$discuz = $detail_packages->{'qcloud/discuz'};
	$versions = array_keys(get_object_vars($discuz));
	$versions = array_diff($versions, array("dev-master"));
	usort($versions, 'version_compare');
?>
	<div class="alert alert-primary" role="alert">
		<p>下载主程序</p>
		<p>请先确认已经<a href="https://www.dnspod.cn/promo/discuzq" target="_blank">申请过Discuz! Q的内测资格</a>，并在<a href="https://console.cloud.tencent.com/cam/capi">API密钥管理</a>处获取了自己的Secret ID和Secret Key。
	</div>
	<p>请选择计划安装的版本</p>
	<form action="/<?= $SCRIPT_NAME ?>" method="get" class="mx-2" id="userform">
		<input type="hidden" name="step" value="<?= $STEP + 1 ?>">
		<?php foreach ($versions as $i => $ver) {
			if (!isset($discuz->{$ver}->dist)) continue;
			$tmp = explode('/', $discuz->{$ver}->dist->url);
			$url = urlencode(end($tmp));
		?>
			<div class="form-check my-2">
				<input class="form-check-input" type="radio" name="ver" id="versions_select_<?= $i ?>" value="<?= $url ?>" <?= $i === sizeof($versions) - 1 ? 'checked' : '' ?>>
				<label class="form-check-label" for="versions_select_<?= $i ?>">
					<?= $ver ?>
				</label>
			</div>
		<?php } ?>
		<div class="my-3">
			<div class="form-group">
				<label for="secret_id">请输入腾讯云Secret ID</label>
				<input type="text" class="form-control" id="secret_id" name="sid" placeholder="Secret ID">
			</div>
			<div class="form-group">
				<label for="secret_key">请输入腾讯云Secret Key</label>
				<input type="text" class="form-control" id="secret_key" name="skey" placeholder="Secret Key">
			</div>
		</div>
	</form>
	<div class="alert alert-success" role="alert">
		接下来将会验证Secret ID/Key，并下载Discuz! Q的主程序，点击下一步后请耐心等待。
	</div>

<?php
}

//endregion

//region Step 3: 下载主程序

function download_dzq_main()
{
	global $STEP, $SCRIPT_NAME;
	$target_dir = realpath(dirname(__DIR__));
	try_mkdir($target_dir);

	$sid = get_var('sid');
	$skey = get_var('skey');
	if (!$sid || !$skey) throw new Exception("Secret ID/Key 不能为空");

	$version = get_var('ver');
	$url = 'https://cloud.discuz.chat/dist/qcloud/discuz/' . $version;

	$tmpfile = __DIR__ . "/" . uniqid('dzq', true) . '.zip';
	download_as_file_with_auth($url, $sid, $skey, $tmpfile);

	extract_zip($tmpfile, $target_dir);

	unlink($tmpfile);
?>
	<div class="alert alert-primary" role="alert">
		主程序下载完成
	</div>
	<div class="alert alert-success" role="alert">
		Discuz! Q主程序已经下载完成并解压缩到 <?= $target_dir ?> ，下一步将下载Discuz! Q的支持包，文件较大，点击下一步后请耐心等待。
	</div>
	<form action="/<?= $SCRIPT_NAME ?>" method="get" class="mx-2" id="userform">
		<input type="hidden" name="step" value="<?= $STEP + 1 ?>">
		<input type="hidden" name="ver" value="<?= $version ?>">
	</form>
<?php
}

//endregion

//region Step 4: 下载Vendor
function download_vendor()
{
	$target_dir = realpath(dirname(__DIR__));
	if (!file_exists($target_dir)) {
		throw new Exception("主程序还未下载成功，请返回上一步");
	}
	$ver = get_var('ver');
	if (preg_match("/qcloud-discuz-(v.*)-.*\.zip/m", $ver, $m)) {
		$ver = $m[1];
	} else {
		throw new Exception("版本号格式错误");
	}

	$url = "https://dl.discuz.chat/offline/$ver.zip";
	$tmpfile = __DIR__ . "/" . uniqid('vendor', true) . '.zip';
	download_as_file_with_auth($url, false, false, $tmpfile);
	extract_zip($tmpfile, $target_dir);
	unlink($tmpfile);
?>
	<div class="alert alert-primary" role="alert">
		代码下载完成
	</div>
	<div class="alert alert-success" role="alert">
		<p>Discuz! Q的所有文件已经下载完成并解压缩到 <?= $target_dir ?> 。</p>
		<?php if (existing_installation()) {
			make_lock_file();
		?>
			<p>最新版的代码已经更新完成，接下来请按<a href="https://discuz.chat/docs/upgrade.html#%E5%85%B6%E5%AE%83%E5%8D%87%E7%BA%A7%E6%89%80%E9%9C%80%E5%B7%A5%E4%BD%9C">这里的文档</a>，执行升级所需的其它工作</p>
			<p>完成升级后，不要忘记重启php-fpm服务</p>
			<form action="https://discuz.chat/docs/upgrade.html#%E5%85%B6%E5%AE%83%E5%8D%87%E7%BA%A7%E6%89%80%E9%9C%80%E5%B7%A5%E4%BD%9C" method="get" class="mx-2" id="userform">
			</form>
		<?php } ?>
	</div>
<?php
}

//endregion

//region Step 5: 检查软连接
function check_symlink()
{
?>
	<div class="alert alert-primary" role="alert">
		检查软连接
	</div>
	<?php
	$public_dir = realpath(__DIR__);
	$storage_dir = realpath(dirname(__DIR__) . "/storage/app/public");
	if (!file_exists($storage_dir)) {
		throw new Exception("$storage_dir 不存在，请返回第一步重新下载安装");
	}
	$public_storage = $public_dir . DIRECTORY_SEPARATOR . "storage";
	if (file_exists($public_storage)) {
		if (!(is_link($public_storage) && readlink($public_storage) == $storage_dir)) {
			throw new Exception("$public_storage 已存在，并且指向不正确，请删除后，再重试本步骤");
		}
	} else {
		if (is_link($public_storage)) {
			throw new Exception("$public_storage 已存在，并且指向不存在的目录，请删除后，再重试本步骤");
		}
		if (symlink($storage_dir, $public_storage) === false) {
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				throw new Exception("建立软连接失败，请在服务器上以管理员身份打开命令提示符，运行 mklink /d $public_storage $storage_dir ，然后重试本步骤");
			} else {
				throw new Exception("建立软连接失败，请在服务器上运行 ln -s $storage_dir $public_storage 后，重试本步骤");
			}
		}
	}
	?>
	<div class="alert alert-success" role="alert">
		软连接已经建立成功，<?= $public_storage ?> 指向 <?= $storage_dir ?>
	</div>
<?php
}

//endregion

//region Step 6: 检查/install /api /index.html的配置是否正确
function check_httpd_config()
{
	$base_url = get_server_url();
	$check_all = true;
	$url_check = array(
		'install_url' => true,
		'index_url1' => true,
		'index_url2' => true,
		'api_url' => true
	);
	$LANG = array();

	$install_path = $base_url . "/install";
	$install_str = http_download_to_str($base_url . "/install");
	$LANG['install_url'] = "访问 $install_path 测试";
	if ($install_str === FALSE || !preg_match("/adminUsername/m", $install_str)) {
		$url_check['install_url'] = false;
	}

	$index_path = $base_url;
	$index_str = http_download_to_str($index_path);
	$LANG['index_url1'] = "访问 $index_path 测试";
	if ($index_str === FALSE || !preg_match("/<div id=app><\/div>/m", $index_str)) {
		$url_check['index_url1'] = false;
	}

	$index_path = $base_url . "/notexists";
	$index_str = http_download_to_str($index_path);
	$LANG['index_url2'] = "访问 $index_path 测试";
	if ($index_str === FALSE || !preg_match("/<div id=app><\/div>/m", $index_str)) {
		$url_check['index_url2'] = false;
	}

	$api_path = $base_url . "/api";
	$api_str = http_download_to_str($api_path);
	$LANG['api_url'] = "访问 $api_path 测试";
	if (json_decode($api_str) === NULL) {
		$url_check['api_url'] = false;
	}

?>
	<div class="alert alert-primary" role="alert">
		HTTP服务器配置检查
		<p>请参考 <a href="https://discuz.chat/docs/install.html#web-%E6%9C%8D%E5%8A%A1%E5%99%A8%E9%85%8D%E7%BD%AE"> 服务器配置文档 </a> 对Web服务器进行配置。
	</div>
	<ul class="list-group my-3">
		<?php foreach ($url_check as $chk => $result) {
			if (!$result) $check_all = false;
		?>
			<li class="list-group-item d-flex justify-content-between align-items-center">
				<?= $LANG[$chk] ?>
				<?php if ($result === 'warn') { ?>
					<span class="badge badge-warning">警告</span>
				<?php } else if ($result) { ?>
					<span class="badge badge-success">成功</span>
				<?php } else { ?>
					<span class="badge badge-danger">失败</span>
				<?php } ?>
			</li>
		<?php } ?>
	</ul>
	<?php
	if (!$check_all) {
		throw new Exception("HTTP服务器配置检查错误，请对照文档检查自己的服务器配置，确保 /install /api /index.html 等配置正确 ");
	} else {
	?>
		<p>所有准备工作都已完成，接下来进入Discuz! Q的初始化程序，进行系统初始化。请确认自己的数据库是MySQL 5.7及以上，MariaDB 10.2及以上，点击下一步。</p>
	<?php
	}
}

//endregion

//region Step 7: 显示Discuz! Q初始化界面

function show_init_form()
{
	global $SCRIPT_NAME, $STEP;

	$action = get_var('action');

	$forumTitle = get_var('forumTitle');
	$mysqlHost = get_var('mysqlHost');
	$mysqlDatabase = get_var('mysqlDatabase');
	$mysqlUsername = get_var('mysqlUsername');
	$mysqlPassword = get_var('mysqlPassword');
	$tablePrefix = get_var('tablePrefix');
	$adminUsername = get_var('adminUsername');
	$adminPassword = get_var('adminPassword');
	$adminPasswordConfirmation = get_var('adminPasswordConfirmation');

	// 判断每个字段是否合法
	$forumTitleInvalid = $action && !$forumTitle;
	$mysqlHostInvalid = $action && !$mysqlHost;
	$mysqlDatabaseInvalid = $action && !$mysqlDatabase;
	$mysqlUsernameInvalid = $action && !$mysqlUsername;
	$mysqlPasswordInvalid = $action && !$mysqlPassword;
	$tablePrefixInvalid = $action && $tablePrefix && !preg_match("/^\w+$/", $tablePrefix);
	$adminUsernameInvalid = $action && !$adminUsername;
	$adminPasswordInvalid = $action && (!$adminPassword || !$adminPasswordConfirmation || ($adminPasswordConfirmation != $adminPassword));

	// 需要检查数据库连接
	$mysqlConnectCheck = $action && !$mysqlHostInvalid && !$mysqlUsernameInvalid && !$mysqlPasswordInvalid;

	$mysqlVersionInvalid = true;
	$mysqlConnectInvalid = true;
	$mysqlUserPassInvalid = true;
	$mysqlDatabaseDbInvalid = true;
	$mysqlDatabaseDbInvalidMsg = "";
	$mysqlVersionInvalidMsg = "";

	if ($mysqlConnectCheck) {
		$r = check_mysql_connection($mysqlHost, $mysqlUsername, $mysqlPassword);
		$mysqlConnectInvalid = ($r === -1) || $r === false;
		$mysqlUserPassInvalid = ($r === -2);
		$mysqlHostInvalid = $mysqlHostInvalid || $mysqlConnectInvalid;

		if ($mysqlUserPassInvalid) {
			$mysqlUsernameInvalid = true;
			$mysqlPasswordInvalid = true;
		}

		if (!$mysqlConnectInvalid && !$mysqlUserPassInvalid) { // 如果数据库可连接
			$r = check_mysql_version($mysqlHost, $mysqlUsername, $mysqlPassword);
			if ($r !== true) { // 如果数据库版本错误，也标记mysqlHost字段不合法
				$mysqlHostInvalid = true;
				$mysqlVersionInvalid = true;
				$mysqlVersionInvalidMsg = $r;
			} else {
				$mysqlVersionInvalid = false;
			}
			if (!$mysqlDatabaseInvalid) { // 如果输入了数据库名称
				$r = check_mysql_database($mysqlHost, $mysqlUsername, $mysqlPassword, $mysqlDatabase);
				if ($r !== true) {
					$mysqlDatabaseInvalid = true;
					$mysqlDatabaseDbInvalidMsg = $r;
					$mysqlDatabaseDbInvalid = true;
				}
			}
		}
	}

	$ready_to_install = $action && !$forumTitleInvalid && !$mysqlHostInvalid && !$mysqlDatabaseInvalid && !$mysqlUsernameInvalid
		&& !$mysqlPasswordInvalid && !$tablePrefixInvalid && !$adminUsernameInvalid && !$adminPasswordInvalid && !$mysqlUserPassInvalid;

	if ($ready_to_install) {
		make_lock_file();
	?>
		<div class="alert alert-success" role="alert">
			<p>Discuz! Q 完成安装</p>
			<p>所有测试都已完成，点击下一步完成安装 ！</p>
		</div>
		<div class="alert alert-danger hidden" id="errormsgdiv" role="alert">
			<p>安装错误</p>
			<p id="errormsg"></p>
		</div>
	<?php } else { ?>
		<div class="alert alert-primary" role="alert">
			<p>Discuz! Q 初始化</p>
			<p>请填写以下信息，点击下一步测试是否可安装</p>
		</div>
	<?php } ?>
	<form action="<?= $SCRIPT_NAME ?>?step=<?= $STEP ?>" method="post" id="userform<?= $ready_to_install ? 'install' : '' ?>">
		<input type="hidden" name="action" value="1" />
		<div class="form-group row">
			<label for="forum_title" class="col-sm-2 offset-sm-2 col-form-label">站点名称</label>
			<div class="col-sm-6">
				<input type="text" class="form-control <?= $forumTitleInvalid ? "is-invalid" : "" ?>" id="forum_title" name="forumTitle" value="<?= $forumTitle ?>" required>
				<?php if ($action && !$forumTitle) { ?>
					<div class="invalid-feedback">站点名称不能为空</div>
				<?php } ?>
			</div>
		</div>
		<div class="form-group row">
			<label for="mysql_host" class="col-sm-2 offset-sm-2 col-form-label">MySQL 服务器地址</label>
			<div class="col-sm-6">
				<input type="text" class="form-control <?= $mysqlHostInvalid ? "is-invalid" : "" ?>" id="mysql_host" name="mysqlHost" value="<?= $mysqlHost ? $mysqlHost : "127.0.0.1" ?>" required>
				<?php if ($action && !$mysqlHost) { ?>
					<div class="invalid-feedback">MySQL 服务器不能为空</div>
				<?php } ?>
				<?php if ($mysqlConnectCheck && $mysqlConnectInvalid) { ?>
					<div class="invalid-feedback">MySQL 服务器无法连接，请检查服务器的IP与端口(可用:指定端口号)</div>
				<?php } ?>
				<?php if ($mysqlConnectCheck && $mysqlVersionInvalid) { ?>
					<div class="invalid-feedback"><?= $mysqlVersionInvalidMsg ?></div>
				<?php } ?>
			</div>
		</div>
		<div class="form-group row">
			<label for="mysql_database" class="col-sm-2 offset-sm-2 col-form-label">数据库名称</label>
			<div class="col-sm-6">
				<input type="text" class="form-control <?= $mysqlDatabaseInvalid ? "is-invalid" : "" ?>" id="mysql_database" name="mysqlDatabase" value="<?= $mysqlDatabase ?>" required>
				<?php if ($action && !$mysqlDatabase) { ?>
					<div class="invalid-feedback">数据库名称不能为空</div>
				<?php } else if ($action && $mysqlDatabaseDbInvalid) { ?>
					<div class="invalid-feedback"><?= $mysqlDatabaseDbInvalidMsg ?></div>
				<?php } ?>
			</div>
		</div>
		<div class="form-group row">
			<label for="mysql_username" class="col-sm-2 offset-sm-2 col-form-label">MySQL 用户名</label>
			<div class="col-sm-6">
				<input type="text" class="form-control <?= $mysqlUsernameInvalid ? "is-invalid" : "" ?>" id="mysql_username" name="mysqlUsername" value="<?= $mysqlUsername ?>" required>
				<?php if ($action && !$mysqlUsername) { ?>
					<div class="invalid-feedback">MySQL 用户名不能为空</div>
				<?php } ?>
				<?php if ($action && $mysqlUserPassInvalid) { ?>
					<div class="invalid-feedback">使用您输入的 MySQL 用户名密码组合无法连接到数据库</div>
				<?php } ?>
			</div>
		</div>
		<div class="form-group row">
			<label for="mysql_password" class="col-sm-2 offset-sm-2 col-form-label">MySQL 密码</label>
			<div class="col-sm-6">
				<input type="password" class="form-control <?= $mysqlPasswordInvalid ? "is-invalid" : "" ?>" id="mysql_password" name="mysqlPassword" value="<?= $mysqlPassword ?>" required>
				<?php if ($action && !$mysqlPassword) { ?>
					<div class="invalid-feedback">MySQL 密码不能为空</div>
				<?php } ?>
				<?php if ($action && $mysqlUserPassInvalid) { ?>
					<div class="invalid-feedback">使用您输入的 MySQL 用户名密码组合无法连接到数据库</div>
				<?php } ?>
			</div>
		</div>
		<div class="form-group row">
			<label for="table_prefix" class="col-sm-2 offset-sm-2 col-form-label">表前缀(可选)</label>
			<div class="col-sm-6">
				<input type="text" class="form-control <?= $tablePrefixInvalid ? "is-invalid" : "" ?>" id="table_prefix" name="tablePrefix" value="<?= $tablePrefix ?>" required>
				<?php if ($action && $tablePrefixInvalid) { ?>
					<div class="invalid-feedback">表前缀含有不合法字符</div>
				<?php } ?>
			</div>
		</div>
		<div class="form-group row">
			<label for="admin_username" class="col-sm-2 offset-sm-2 col-form-label">设置管理员用户名</label>
			<div class="col-sm-6">
				<input type="text" class="form-control <?= $adminUsernameInvalid ? "is-invalid" : "" ?>" id="admin_username" name="adminUsername" value="<?= $adminUsername ?>" required>
				<?php if ($action && !$adminUsername) { ?>
					<div class="invalid-feedback">管理员用户名不能为空</div>
				<?php } ?>
			</div>
		</div>
		<div class="form-group row">
			<label for="admin_password" class="col-sm-2 offset-sm-2 col-form-label">设置管理员密码</label>
			<div class="col-sm-6">
				<input type="password" class="form-control <?= $adminPasswordInvalid ? "is-invalid" : "" ?>" id="admin_password" name="adminPassword" value="<?= $adminPassword ?>" required>
				<?php if ($action && !$adminPassword) { ?>
					<div class="invalid-feedback">管理员密码不能为空</div>
				<?php } else if ($action && ($adminPassword != $adminPasswordConfirmation)) { ?>
					<div class="invalid-feedback">管理员密码两次输入不一致</div>
				<?php } ?>
			</div>
		</div>
		<div class="form-group row">
			<label for="admin_password2" class="col-sm-2 offset-sm-2 col-form-label">管理员密码确认</label>
			<div class="col-sm-6">
				<input type="password" class="form-control <?= $adminPasswordInvalid ? "is-invalid" : "" ?>" id="admin_password2" name="adminPasswordConfirmation" value="<?= $adminPasswordConfirmation ?>" required>
				<?php if ($action && !$adminPasswordConfirmation) { ?>
					<div class="invalid-feedback">管理员密码确认不能为空</div>
				<?php } else if ($action && ($adminPassword != $adminPasswordConfirmation)) { ?>
					<div class="invalid-feedback">管理员密码两次输入不一致</div>
				<?php } ?>
			</div>
		</div>
	</form>
<?php
}

//endregion

//region 升级
function upgrade_existing_installation()
{
}
//endregion

//region 公共函数

function get_step()
{
	return get_var('step');
}

function download_json($url)
{
	$pkg_str = http_download_to_str($url);
	if ($pkg_str === FALSE) {
		throw new Exception("下载错误: " . $url);
	}
	return json_decode($pkg_str);
}

function http_download_to_str($url)
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	$response = curl_exec($ch);
	if ($response === FALSE) {
		throw new Exception("下载 $url 错误，错误信息：" . curl_error($ch));
	}
	curl_close($ch);
	return $response;
}

function get_var($var)
{
	if (!isset($_GET[$var])) {
		if (!isset($_POST[$var])) return false;
		return $_POST[$var];
	}
	return $_GET[$var];
}

function redirect($url)
{
	header('Location: ' . $url);
	exit(0);
}

function is_https()
{
	if (isset($_SERVER["HTTPS"]) && strtolower($_SERVER["HTTPS"]) != "off") {
		return true;
	}
	if (isset($_SERVER["HTTP_X_FORWARDED_PROTO"]) && strtolower($_SERVER["HTTP_X_FORWARDED_PROTO"]) == "https") {
		return true;
	}
	if (isset($_SERVER["HTTP_SCHEME"]) && strtolower($_SERVER["HTTP_SCHEME"]) == "https") {
		return true;
	}
	if (isset($_SERVER["HTTP_FROM_HTTPS"]) && strtolower($_SERVER["HTTP_FROM_HTTPS"]) != "off") {
		return true;
	}
	if (isset($_SERVER["SERVER_PORT"]) && $_SERVER["SERVER_PORT"] == 443) {
		return true;
	}
	return false;
}

function download_as_file_with_auth($url, $username, $password, $targetfile)
{
	$out = fopen($targetfile, 'wb');
	if ($out === FALSE) {
		throw new Exception("无法创建文件 $targetfile , 请重新返回第一步，检查目录权限");
	}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_FILE, $out);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	if ($username && $password) curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
	if (curl_exec($ch) === FALSE) {
		unlink($targetfile);
		throw new Exception("无法下载 $url, 错误信息：" . curl_error($ch));
	}
	$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	if ($http_code === 403) {
		unlink($targetfile);
		throw new Exception("无法下载 $url, 认证信息错误，请返回上一步检查Secret ID/Key是否正确");
	}
	if ($http_code !== 200) {
		unlink($targetfile);
		throw new Exception("无法下载 $url, 错误代码：$http_code");
	}
	curl_close($ch);
	fclose($out);
}

function extract_zip($zipfile, $targetfolder)
{
	$zip = new ZipArchive;
	$res = $zip->open($zipfile);
	if ($res === true) {
		if ($zip->extractTo($targetfolder) === FALSE) {
			throw new Exception("无法解压缩 $zipfile 到 $targetfolder");
		}
		$zip->close();
	} else {
		throw new Exception("无法打开 $zipfile");
	}
}

function try_mkdir($path, $mode = 0777, $recursive = false)
{
	if (file_exists($path)) return;
	mkdir($path, $mode, $recursive);
}

function writable_check($dir)
{
	$tmpfile = $dir . "/" . uniqid('test', true);
	if (file_put_contents($tmpfile, "hello") === FALSE) {
		return false;
	}
	if (!file_exists($tmpfile)) {
		return false;
	}
	return unlink($tmpfile);
}

function existing_installation()
{
	$dir = __DIR__;
	if (file_exists($dir . "/../config/config.php") || file_exists($dir . "/../storage/install.lock")) {
		$appfile = $dir . "/../framework/src/Foundation/Application.php";
		if (!file_exists($appfile)) {
			$appfile = $dir . "/../vendor/discuz/core/src/Foundation/Application.php";
			if (!file_exists($appfile)) {
				return false;
			}
		}
		$content = file_get_contents($appfile);
		$match = NULL;
		preg_match("/const VERSION = '(.*)';$/m", $content, $match);
		if ($match) {
			return $match[1];
		}
		return false;
	}
	return false;
}

function get_server_url()
{
	$url = is_https() ? "https://" : "http://";
	$url .= $_SERVER['HTTP_HOST'];
	return $url;
}

function check_mysql_connection($host, $username, $password)
{
	$port = 3306;
	if (strpos($host, ":") !== FALSE) {
		list($host, $port) = explode(":", $host);
	}
	try {
		$conn = "mysql:host=$host;port=$port;charset=utf8mb4";
		return new PDO($conn, $username, $password);
	} catch (PDOException $e) {
		if ($e->getCode() === 2002) {
			return -1; // -1 表示连接被拒绝
		}
		if ($e->getCode() === 1045) {
			return -2; // -2 表示用户名/密码错误
		}
		return false;
	}
}

function check_mysql_version($host, $username, $password)
{
	$pdo = check_mysql_connection($host, $username, $password);
	if ($pdo === FALSE) {
		return "数据库无法连接";
	}
	if ($q = $pdo->query('SELECT VERSION()')) {
		$version = $q->fetchColumn();
		if (strpos($version, 'MariaDB') !== FALSE) {
			if (version_compare($version, '10.3.0', '>=')) {
				return true;
			}
		} else {
			if (version_compare($version, '8.0.0', '>=')) {
				return true;
			}
		}
	} else {
		return "无法查询数据库版本";
	}
	if ($q = $pdo->query("SELECT @@GLOBAL.innodb_default_row_format")) {
		$rowformat = $q->fetchColumn();
		if ($rowformat != "dynamic") {
			return "MySQL配置不正确，请确认innodb_default_row_format配置为dynamic";
		}
		$large_prefix = $pdo->query("SELECT @@GLOBAL.innodb_large_prefix")->fetchColumn();
		if ($large_prefix != 1) {
			return "MySQL配置不正确，请确认innodb_large_prefix配置为on";
		}
	} else {
		return "MySQL版本太低，请使用MySQL 5.7.9版本以上或MariaDB 10.2以上";
	}
	return true;
}

function check_mysql_database($host, $username, $password, $database)
{
	$pdo = check_mysql_connection($host, $username, $password);
	if ($pdo === FALSE) {
		return "数据库无法连接";
	}
	if ($pdo->exec("USE $database") !== FALSE) {
		if ($pdo->query("SHOW TABLES")->rowCount() > 0) {
			return "数据库 $database 不为空，请清空后重试";
		}
		return true;
	} else {
		if ($q = $pdo->query("SHOW DATABASES LIKE '$database'")) {
			if ($q->rowCount() > 0) {
				return "无法切换到数据库 $database";
			}
			if ($pdo->query("CREATE DATABASE $database DEFAULT CHARACTER SET = `utf8mb4` DEFAULT COLLATE = `utf8mb4_unicode_ci`") === FALSE) {
				return "无法创建数据库 $database ，请检查用户权限";
			}
			return true;
		}
		return "无法获取数据库列表";
	}
}

function build_steps()
{
	$image_ver = check_image();
	$steps = NULL;

	if ($image_ver === FALSE) {
		$steps = array(
			0 => 'check_env_and_extension',
			1 => 'pre_check',
			2 => 'download_package',
			3 => 'download_dzq_main',
			4 => 'download_vendor',
			5 => 'check_symlink',
			6 => 'check_httpd_config',
			7 => 'show_init_form',
		);
	} else {
		$steps = array(
			0 => 'image_welcome',
			1 => 'download_package',
			2 => 'download_dzq_main',
			3 => 'download_vendor',
			4 => 'show_init_form'
		);
	}

	return $steps;
}

//endregion
