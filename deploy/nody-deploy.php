<?

$cmd[] = 'ls -la ..  2>&1 &';
$cmd[] = 'git status -s  2>&1 &';
$cmd[] = 'git pull  2>&1 &';
$cmd[] = "ssh-agent bash -c 'ssh-add /app/deploy/id_rsa; cd ../; pwd; git pull  2>&1 &'";
//$cmd = 'git status -s';

foreach ($cmd as $c) {
	echo shell_exec($c);
}
