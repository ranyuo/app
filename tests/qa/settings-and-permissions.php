<?php
declare(strict_types=1);
require dirname(__DIR__,2).'/portal-private/bootstrap.php';
$path=getenv('PORTAL_CONFIG_FILE');$config=$path?require $path:[];
if(($config['db_name']??'')!=='nasca_qa_test')throw new RuntimeException('Solo nasca_qa_test.');
$app=portal_app();$app->login(['username'=>'admin','password'=>'PruebaQA-Nasca-2026!']);
$report=[];
$mutations=[
 'Identidad'=>fn(&$d,$i)=>$d['config']['inst']['slogan']='QA escenario de identidad '.$i,
 'Redes'=>fn(&$d,$i)=>$d['config']['redes']['instagram']='https://example.test/qa-'.$i,
 'SEO'=>fn(&$d,$i)=>$d['config']['seo']['titulo']='QA escenario SEO '.$i,
 'Popup'=>fn(&$d,$i)=>$d['config']['popup']['titulo']='QA comunicado '.$i,
 'TUPA configuración'=>fn(&$d,$i)=>$d['config']['uit'][2090+$i]=5500+$i,
 'Licenciamiento'=>fn(&$d,$i)=>$d['licenciamiento']['descripcion']='QA escenario '.$i.': licencia no acreditada; texto sintético.',
 'Mesa de Partes configuración'=>fn(&$d,$i)=>$d['config']['mpv']['maxArchivos']=$i+1,
 'Reclamos configuración'=>fn(&$d,$i)=>$d['config']['lr']['alerta']=$i+1,
 'Bolsa configuración'=>fn(&$d,$i)=>$d['config']['bolsa']['responsable']='Responsable sintético QA '.$i,
];
foreach($mutations as $module=>$fn)for($i=1;$i<=2;$i++){
 $b=$app->bootstrap();$before=$b['data'];$next=$before;$fn($next,$i);
 $app->save(['revision'=>$b['revision'],'data'=>$next]);$saved=$app->bootstrap();
 if(!PortalPolicy::equal($next['config'],$saved['data']['config'])||!PortalPolicy::equal($next['licenciamiento'],$saved['data']['licenciamiento']))throw new RuntimeException('No se guardó '.$module);
 // Restituir solo los campos de configuración; conservar registros, revisiones y auditoría generados por el servidor.
 $restore=$saved['data'];$restore['config']=$before['config'];$restore['licenciamiento']=$before['licenciamiento'];
 $app->save(['revision'=>$saved['revision'],'data'=>$restore]);
 $report[]=['module'=>$module,'scenario'=>$i,'saved'=>true,'restored'=>true];
}
foreach([1,2] as $i){
 $app->logout();$app->login(['username'=>'qa-editor-'.$i,'password'=>'CuentaQA-Sintetica-2026!']);
 try{$app->requireAdmin();throw new RuntimeException('Escalamiento de privilegios permitido.');}catch(PortalError $e){if($e->status!==403)throw $e;}
 $b=$app->bootstrap();$d=$b['data'];$d['config']['inst']['nombre']='QA alteración no autorizada';
 try{$app->save(['revision'=>$b['revision'],'data'=>$d]);throw new RuntimeException('Edición de configuración ajena al rol permitida.');}catch(PortalError $e){if($e->status!==403)throw $e;}
 $report[]=['module'=>'Permisos','scenario'=>$i,'requireAdminDenied'=>true,'unauthorizedSaveDenied'=>true];
}
$app->logout();$app->login(['username'=>'admin','password'=>'PruebaQA-Nasca-2026!']);
$backupConfig=PortalBackups::config($app);
foreach([1,2] as $i){
 $backup=PortalBackups::create($app,'QA-NASCA-DOS-'.$i);
 if(!is_file(PortalBackups::path($backup['id']))||$backup['counts']['pisco_state']!==1)throw new RuntimeException('Respaldo QA inválido.');
 PortalBackups::configure($app,['enabled'=>false,'hour'=>$i,'retention'=>$i+2]);
 if(PortalBackups::config($app)['hour']!==$i)throw new RuntimeException('Programación QA no persistida.');
 $report[]=['module'=>'Respaldos','scenario'=>$i,'backup'=>$backup['id'],'scheduleSaved'=>true];
 $b=$app->bootstrap();$d=$b['data'];$offerId=920000+1000*$i;$offer=PortalPolicy::find($d['academico']['ofertas'],'id',$offerId);foreach($d['academico']['ofertas'] as &$row)if($row['id']===$offerId)$row['estado']='Por validar';unset($row);$app->save(['revision'=>$b['revision'],'data'=>$d]);
 $notifs=array_filter($app->bootstrap()['data']['academico']['notifs'],fn($r)=>($r['txt']??'')==='Oferta pendiente de validación: '.$offer['puesto']);
 if(count($notifs)<2)throw new RuntimeException('Notificaciones QA ausentes.');
 $report[]=['module'=>'Notificaciones','scenario'=>$i,'records'=>count($notifs)];
 $b=$app->bootstrap();$d=$b['data'];foreach($d['academico']['ofertas'] as &$row)if($row['id']===$offerId)$row['estado']=$offer['estado'];unset($row);$app->save(['revision'=>$b['revision'],'data'=>$d]);
}
PortalBackups::configure($app,$backupConfig);
if((int)$app->db()->query('SELECT COUNT(*) FROM pisco_audit')->fetchColumn()<2)throw new RuntimeException('Auditoría ausente.');
$report[]=['module'=>'Auditoría','scenario'=>1,'passed'=>true];$report[]=['module'=>'Auditoría','scenario'=>2,'passed'=>true];
file_put_contents('/qa/settings-and-permissions.json',json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));echo count($report)." escenarios de configuración/permisos aprobados.\n";
