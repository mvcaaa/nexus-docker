<?php
if (!preg_match("@^/branches@", $_SERVER["REQUEST_URI"])) {
	require_once('boot_nexus_server.php');
	return;
}

$boot_path = "/var/www/boot";

// require libraries
require_once("lib/toml.php");

// get defaults
try {
	$config = (object) Toml::parseFile($boot_path . '/defaults.toml');
	$config->db = $config->database;
	unset($config->database);
	$host = $config->db['host'];
	$db   = $config->db['environment'];
	$user = $config->db['username'];
	$pass = $config->db['password'];
	$charset = 'utf8';

	$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

	$opt = [
	    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
	    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
	    PDO::ATTR_EMULATE_PREPARES   => false
	];

	try {
		$pdo = new PDO($dsn, $user, $pass, $opt);
	} catch(Exception $ex) {
		die($ex);
	}

} catch (Exception $e) {
	_boot_installation_error("Could not parse default config file");
}

if(!@$_GET['branch']) $_GET['branch'] = 'dev_dev';

// get branches data & check that requested branch exists
function _get_branches() {
	$branches = [];

	// try FETCH-HEAD
	if(is_file("/var/www/branches/base-FETCH-HEAD-copy")) {
		$git_fetch_head = file_get_contents("/var/www/branches/base-FETCH-HEAD-copy");

		foreach(explode("\n", $git_fetch_head) as $line) {
			$matches = [];
			if(preg_match("@branch[ \t]+'(.*)'@", $line, $matches))
				$branches[] = $matches[1];
		}
	}

	// try packed-refs
	if(is_file("/var/www/branches/base-packed-refs-copy")) {
		$packed_refs = file_get_contents("/var/www/branches/base-packed-refs-copy");

		foreach(explode("\n", $packed_refs) as $line) {
			if(preg_match("@.*refs/tags/(.*)@", $line, $matches))
				$branches[] = $matches[1];
		}

		if(sizeof($branches) == 1) return false;
	}

	return $branches;
}

function list_databases($branches, $pdo) {
	// display index
	$dbNames = [];
	$sql = 'SHOW DATABASES';
	foreach ($pdo->query($sql) as $row) { $dbNames[] = $row['Database']; }

	$installationNames = [];
	foreach($dbNames as $dbName) {
		$matches = [];
		if(preg_match("@^nexus_([a-zA-Z0-9\-\_]+)_([0-9]+)$@", $dbName, $matches)) {
			$inst = $matches[1];
			if(!isset($installationNames[$inst])) $installationNames[$inst] = array();
			$installationNames[$inst][] = $matches[2];
		}
	}
	$installationNames['dev'][] = 'nexus';

	// get dev dirs
	ksort($installationNames);
	?>

	<table class='table table-condensed table-striped'>
		<thead>
			<tr>
				<th align=left>Installation</th>
				<th align=left>Databases</th>
			</tr>
		</thead>

		<tbody>
			<?php
			foreach($installationNames as $name => $versions) {
				rsort($versions);

				print("<tr>");
				print("<td style='vertical-align: top;'>" . $name . "</td>");
				print("<td style='font-size: 0.85em;'>");
				rsort($versions);

				print("<a href='/branches/$name" . (isset($_GET['branch']) ? htmlspecialchars("." . $_GET['branch']) : "") . "/latest/'>latest</a>&nbsp;&nbsp;");

				$first = true;
				foreach($versions as $version) {
					print(($first ? "<span style='font-size: 0.7em;'>(" : "") . "<a href='/branches/$name" . (isset($_GET['branch']) ? htmlspecialchars("." . $_GET['branch']) : "") . "/$version/'>$version</a>" . ($first ? ")</span>" : "") . "&nbsp; ");
					$first = false;
				}
				print("</td>");
				print("</tr>");

			}
			?>
		</tbody>
	</table>
	<?php
}

// stupid loop for getting a branch. git-up (every 1min) loses the FETCH_HEAD file
$branches = _get_branches();

// check for index page
if(in_array($_SERVER['REQUEST_URI'], array('/branches/', '/branches')) || strstr($_SERVER["REQUEST_URI"], 'branches/?branch=') !== false) {
	// we're at index, display it and exit

	?>
	<head>
		<title>Nexus branches</title>
	</head>

	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">
	<body>
		<div class="container">
			<h1>
				Nexus Branches

				<small>
					<a href='http://office.enkora.fi/changelog/nexus/' class="btn btn-default">Changelog</a>
				</small>

				<a href="http://www.enkora.fi"><img src="/branches/dev.dev_dev/nexus/resource/enkora.png" class="pull-right"></a>
			</h1>

			<?php list_databases($branches, $pdo) ?>
		</div>
	</body>
	<?php
	exit;
}

$matches = [];
// extract version information from url
if(!preg_match('@/branches/([a-zA-Z0-9\-\_]+)(\.([a-zA-Z0-9\-\_\.]+)){0,1}/([a-zA-Z0-9]+)(/.*){0,1}@', $_SERVER['REQUEST_URI'], $matches)) {
        header("HTTP/1.0 404 Not Found");
        die("Could not extract setup/db information from URL");
}

// set version info into variables (if you want to debug, do print_r($matches))
$installation_name = $matches[1];
$db_date = $matches[4];
if($branch_explicit = !empty($matches[3])) {
	$branch = $matches[3];
	$installation_part = $installation_name . '.' . $branch;
}
else {
	$branch = 'develop';
	$installation_part = $installation_name;
}

// modify server variables so that nexus believe's we're really in this virtual directory
$vars = ['SCRIPT_FILENAME', 'SCRIPT_NAME', 'SCRIPT_FILE', 'DOCUMENT_URI', 'PHP_SELF'];
$replaced_uri = 'branches/' . $installation_part . '/' . $db_date . '/index.php';
foreach($vars as $var) {
	//echo "$var = {$_SERVER[$var]}<br/>";
	if (isset($_SERVER[$var])) {
		$_SERVER[$var] = str_replace('branches/index.php', $replaced_uri, $_SERVER[$var]);
	}
	//echo "$var = {$_SERVER[$var]}<br/>";
	//echo "<br/>";
}

//$_SERVER['REQUEST_URI'] = str_replace("." . $branch, '', $_SERVER['REQUEST_URI']);

// check that branch exists
if(preg_match("/^dev_([a-zA-Z0-9_]+)$/", $branch, $arr)) {
	// it's a dev environment
	if ($arr[1] == 'dev') $arr[1] = '';
	$_app_root = '/var/www/installations/' . $arr[1] . 'nexus/';
} else {
	// it's a git branch
	if(!in_array($branch, $branches)) {
		header("HTTP/1.0 404 Not Found");
		die("Branch " . $branch . " not found");
	}

	// check if we're at the site index
	$is_index_file = $_SERVER["REQUEST_URI"] . "index.php" == "/" . $replaced_uri || $_SERVER["REQUEST_URI"] . "/index.php" == "/" . $replaced_uri;

	// check if branch folder exists
	if(!is_dir('/var/www/branches/' . $branch . '/')) {
		// doesn't exist: checkout remote branch at base, clone base, checkout branch
		exec("cd /var/www/branches/base"); //; git checkout -b " . $branch . " remotes/origin/" . $branch);
		exec("git clone /var/www/branches/base /var/www/branches/" . $branch, $_dev_branches_output, $exit);
		//exec("chmod g+w /var/www/branches/" . $branch);
		exec("cd /var/www/branches/" . $branch . "; git checkout " . $branch);
		exec('/usr/local/bin/composer.phar --working-dir=/var/www/branches/' . $branch . '/lib install', $err);
		exec("cd /var/www/branches/" . $branch . "; git checkout lib/composer.lock");

	} elseif($is_index_file) {
		// exists! and we're at index file, check for new versions (from base)
		exec("cd /var/www/branches/" . $branch . "; git pull", $_dev_branches_output, $err);
		if (strpos($a, 'Already up-to-date') !== false) {
			exec('/usr/local/bin/composer.phar --working-dir=/var/www/branches/' . $branch . '/lib install', $err);
			exec("cd /var/www/branches/" . $branch . "; git checkout lib/composer.lock");
		}
	}

	$_app_root = '/var/www/branches/' . $branch . '/';
}

unset($_GET['branch']);

/// STANDARD NEXUS INIT BEGINS

// System settings
if ($installation_name == 'dev') {
	$dbname = 'nexus';
	$config->default_organization_id = 6;
} else {
	if ($db_date == "latest") {
	$dbNames = [];
	$sql = 'SHOW DATABASES';
	foreach ($pdo->query($sql) as $row) { $dbNames[] = $row['Database']; }

		$db_date = null;
		foreach ($dbNames as $dbName) {
			if (strpos($dbName, "nexus_" . $installation_name . "_") !== false) {
				$arr = explode("_", $dbName);
				$arr_db = $arr[sizeof($arr)-1];
				if ($arr_db > $db_date || $db_date == null) $db_date = $arr_db;
			}
		}
	}

	$dbname = 'nexus_' . $installation_name . '_' . $db_date;
}

$config->installation_code  = $installation_name;
$config->installation_title = $installation_name;
$config->environment        = 'dev';
$config->session_code       = str_replace('-', '_', 'test_branches_' . $installation_name . "_" . str_replace(".", "_", $branch));
$config->db['dbname']       = $dbname;

$config->app_root        = $_app_root;
$config->app_path        = $config->app_root . 'nexus_app/';
$config->lib_path        = $config->app_root . 'lib/';
$config->redis = true;

$_SERVER['SCRIPT_FILENAME'] = str_replace('boot_nexus.php', $_app_root . '/index.php', $_SERVER['SCRIPT_FILENAME']);

set_include_path($config->lib_path . PATH_SEPARATOR . get_include_path());
require_once('Nexus.php');
Nexus::run($config);

if(isset($_dev_branches_output)) var_dump($_dev_branches_output);
