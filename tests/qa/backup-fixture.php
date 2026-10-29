<?php
declare(strict_types=1);
// Preparador exclusivo de la base local de regresión, sin borrar sus datos.
$path=getenv('PORTAL_CONFIG_FILE');
if(!$path||!str_ends_with($path,'config.test.php'))throw new RuntimeException('Usa config.test.php local.');
$config=require $path;
if(($config['db_name']??'')!=='nasca_test')throw new RuntimeException('Solo nasca_test local.');
require dirname(__DIR__,2).'/portal-private/bootstrap.php';
$app=portal_app();$username='qa29-local-backup';$password='QA29-Backup-Local-2026!';
if(!$app->installed())$app->install(['install_key'=>$config['install_key'],'username'=>$username,'name'=>'QA29 administrador local','email'=>'qa29-local-backup@example.test','password'=>$password,'password_confirmation'=>$password]);
$stmt=$app->db()->prepare('SELECT id FROM pisco_users WHERE username=?');$stmt->execute([$username]);
if(!$stmt->fetchColumn())$app->createAccount(['username'=>$username,'name'=>'QA29 administrador local','email'=>'qa29-local-backup@example.test','password'=>$password,'role'=>'admin'],false);
$app->login(['username'=>$username,'password'=>$password]);
echo "Preparación local correcta.\n";
