<?php
declare(strict_types=1);
ob_start();require dirname(__DIR__).'/portal-private/bootstrap.php';require dirname(__DIR__).'/portal-private/NascaContent.php';
$path=getenv('PORTAL_CONFIG_FILE');if(!$path||!str_ends_with($path,'config.test.php'))throw new RuntimeException('Solo config.test.php.');
$config=require $path;if(!str_ends_with($config['db_name'],'_test'))throw new RuntimeException('Solo base _test. Ejecuta parity.php primero.');
$app=portal_app();$app->login(['username'=>'admin','password'=>'PruebaOriginal-2026!']);
// Simula una institución anterior con un programa y una persona propios.
$legacy=$app->bootstrap();$data=$legacy['data'];
$data['config']['inst']['nombreCorto']='IESTP anterior';
$data['programas']=array_values(array_filter($data['programas'],fn($r)=>$r['id']!=='legacy-program'));
$data['personas']=array_values(array_filter($data['personas'],fn($r)=>$r['id']!=='legacy-person'));
$old=$data['programas'][0];$old['id']='legacy-program';$old['slug']='legacy-program';$old['codigo']='LEGACY';$old['nombre']='Programa institucional anterior';$old['coordinadorId']='';$data['programas'][]=$old;
$person=$data['personas'][0];$person['id']='legacy-person';$person['nombres']='Persona anterior';$person['programas']=['legacy-program'];$data['personas'][]=$person;
$app->save(['revision'=>$legacy['revision'],'data'=>$data]);
$before=$app->bootstrap()['data'];$users=$app->db()->query('SELECT id,username,password,role,profile FROM pisco_users ORDER BY id')->fetchAll();
$checks=0;
function checkNasca(bool $ok,string $label):void{global $checks;if(!$ok)throw new RuntimeException($label);$checks++;echo "OK $label\n";}
// Importación por un usuario sin permiso: ni respaldo ni escritura.
$app->logout();try{NascaContent::apply($app);throw new RuntimeException('Se permitió importar sin sesión.');}catch(PortalError $e){checkNasca(in_array($e->status,[401,403],true),'Importación rechazada sin administrador');}
$app->login(['username'=>'admin','password'=>'PruebaOriginal-2026!']);
$result=NascaContent::apply($app);$after=$app->bootstrap()['data'];
checkNasca(count(array_filter($after['programas'],fn($r)=>$r['estado']==='Publicado'))===8,'Ocho programas publicados');
checkNasca(PortalPolicy::find($after['programas'],'id','legacy-program')['estado']==='Borrador','Programa anterior conservado sin publicar');
checkNasca(PortalPolicy::find($after['personas'],'id','legacy-person')['estado']==='Despublicado','Persona anterior conservada sin publicar');
checkNasca($after['config']['inst']['nombreCorto']==='IESTP “Nasca”','Identidad institucional importada');
checkNasca(is_file(PortalBackups::path($result['backup'])),'Respaldo privado creado antes de importar');
checkNasca($users===$app->db()->query('SELECT id,username,password,role,profile FROM pisco_users ORDER BY id')->fetchAll(),'Cuentas, roles y contraseñas conservados');
foreach(['mensajes','reclamos','expedientes','certificados','suscriptores'] as $key)checkNasca(PortalPolicy::equal($before[$key],$after[$key]),'Se conserva '.$key);
foreach(['estudiantes','notas','certs','titulos','sesiones','publicados','docentes','empresas','ofertas','postulaciones','perfilesLab','docs'] as $key)checkNasca(PortalPolicy::equal($before['academico'][$key],$after['academico'][$key]),'Se conserva academico.'.$key);
foreach($before['academico']['progMap'] as $pid=>$id)checkNasca(($after['academico']['progMap'][$pid]??null)===$id,'Identificador académico conservado: '.$pid);
$count=count($after['academico']['programas']);NascaContent::apply($app);$twice=$app->bootstrap()['data'];checkNasca(count($twice['academico']['programas'])===$count,'Repetir importación no duplica programas');
// El padrón público no necesita DNI inventados; si se conoce uno debe ser válido.
$seed=json_decode(file_get_contents(dirname(__DIR__).'/portal-private/data/portal.json'),true,flags:JSON_THROW_ON_ERROR);
$bad=$seed;$bad['becarios'][0]['dni']='123';try{PortalDomain::validate($seed,$bad,['rol'=>'admin']);throw new RuntimeException('Se permitió DNI incorrecto.');}catch(PortalError $e){checkNasca($e->status===422,'DNI incompleto rechazado');}
$duplicate=$seed;$row=$duplicate['becarios'][0];$row['id']='duplicado';$duplicate['becarios'][]=$row;try{PortalDomain::validate($seed,$duplicate,['rol'=>'admin']);throw new RuntimeException('Se permitió becario duplicado.');}catch(PortalError $e){checkNasca($e->status===422,'Beneficiario duplicado sin DNI rechazado por nombre');}
// La ficha pública no equivale a un CV autorizado; una revocación expresa se respeta.
$editable=$app->bootstrap();$personId=$editable['data']['personas'][0]['id'];
$editable['data']['personas'][0]['cv']['autoriza']=['ok'=>false,'revocado'=>true];
$app->save(['revision'=>$editable['revision'],'data'=>$editable['data']]);$app->logout();
$person=PortalPolicy::find($app->bootstrap()['data']['personas'],'id',$personId);
checkNasca(!isset($person['dni'])&&!isset($person['cv']),'Proyección pública sin DNI ni CV sin autorización');
checkNasca(!isset($person['fuente']),'La revocación retira la ficha del directorio público');
$app->login(['username'=>'admin','password'=>'PruebaOriginal-2026!']);
echo "$checks comprobaciones de importación aprobadas.\n";ob_end_flush();
