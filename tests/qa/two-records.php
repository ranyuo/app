<?php
declare(strict_types=1);
ob_start();require dirname(__DIR__,2).'/portal-private/bootstrap.php';
$configPath=getenv('PORTAL_CONFIG_FILE');
if(!$configPath||!str_ends_with($configPath,'config.test.php'))throw new RuntimeException('Solo configuración de pruebas.');
$config=require $configPath;
if(($config['db_name']??'')!=='nasca_qa_test')throw new RuntimeException('Usa exclusivamente nasca_qa_test.');
$app=portal_app();
if(!$app->installed())$app->install(['install_key'=>$config['install_key'],'username'=>'admin','name'=>'Administrador QA local','email'=>'qa@example.test','password'=>'PruebaQA-Nasca-2026!','password_confirmation'=>'PruebaQA-Nasca-2026!']);
$app->login(['username'=>'admin','password'=>'PruebaQA-Nasca-2026!']);
$report=['database'=>'nasca_qa_test','scope'=>'local','run'=>'QA-NASCA-DOS','modules'=>[],'checks'=>0];
function check(bool $ok,string $label):void{global $report;if(!$ok)throw new RuntimeException($label);$report['checks']++;}
function change(callable $fn):void{global $app;$b=$app->bootstrap();$d=$b['data'];$fn($d);$app->save(['revision'=>$b['revision'],'data'=>$d]);}
function record(string $collection,array $row):void{change(function(&$d)use($collection,$row){$d[$collection]=array_values(array_filter($d[$collection],fn($r)=>$r['id']!==$row['id']));$d[$collection][]=$row;});}
function academic(string $collection,array $row):void{change(function(&$d)use($collection,$row){$d['academico'][$collection]=array_values(array_filter($d['academico'][$collection],fn($r)=>$r['id']!==$row['id']));$d['academico'][$collection][]=$row;});}
function ruc(string $s):string{$w=[5,4,3,2,7,6,5,4,3,2];$sum=0;foreach($w as $i=>$n)$sum+=(int)$s[$i]*$n;$n=11-$sum%11;return $s.($n===10?0:($n===11?1:$n));}
$seed=json_decode(file_get_contents(dirname(__DIR__,2).'/portal-private/data/portal.json'),true,flags:JSON_THROW_ON_ERROR);
$logo=$seed['config']['inst']['logo'];$photo=$seed['programas'][0]['imagen'];$program=$seed['programas'][0]['id'];$academicProgram=$seed['academico']['progMap'][$program];
// El archivo es una prueba explícita, no un documento ni una resolución institucional.
$stream="BT /F1 16 Tf 50 760 Td (QA NASCA - DOCUMENTO DE PRUEBA SIN VALOR LEGAL) Tj ET";
$objects=['<< /Type /Catalog /Pages 2 0 R >>','<< /Type /Pages /Kids [3 0 R] /Count 1 >>','<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 5 0 R >> >> /Contents 4 0 R >>',"<< /Length ".strlen($stream)." >>\nstream\n".$stream."\nendstream",'<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>'];
$pdf="%PDF-1.4\n";$offsets=[0];foreach($objects as $index=>$object){$offsets[]=strlen($pdf);$pdf.=($index+1)." 0 obj\n".$object."\nendobj\n";}$start=strlen($pdf);$pdf.="xref\n0 6\n0000000000 65535 f \n";foreach(array_slice($offsets,1) as $offset)$pdf.=sprintf('%010d 00000 n ', $offset)."\n";$pdf.="trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n".$start."\n%%EOF";
file_put_contents('/qa/QA-NASCA-documento-de-prueba.pdf',$pdf);
$file=PortalFiles::store($app,'QA-NASCA-documento-de-prueba.pdf',$pdf);
for($i=1;$i<=2;$i++){
 $tag='QA-NASCA-DOS-'.$i;$id='qa2-'.$i;$today=date('Y-m-d');$stamp=$today.'T08:00:00-05:00';
 $fixtures=[
 'categoriasNoticia'=>['id'=>$id,'nombre'=>$tag.' Categoría','slug'=>'qa-categoria-'.$i,'orden'=>90+$i,'estado'=>'Activo'],
 'tiposDocumento'=>['id'=>$id,'nombre'=>$tag.' Tipo documental','estado'=>'Activo'],
 'tiposBeca'=>['id'=>$id,'nombre'=>$tag.' Beca de prueba','descripcion'=>'Dato sintético QA; no representa un beneficio institucional.','estado'=>'Activo'],
 'becasPeriodos'=>['id'=>$id,'anio'=>2090+$i,'periodo'=>'I','vigente'=>false,'estado'=>'Publicado'],
 'personas'=>['id'=>$id,'nombres'=>'Persona QA '.$i,'apellidos'=>'Nasca Sintética','titulo'=>'','tipo'=>'Docente','especialidad'=>'Prueba QA','dni'=>'','correo'=>'qa'.$i.'@example.test','foto'=>$photo,'programas'=>[$program],'estado'=>'Publicado','cv'=>['autoriza'=>['ok'=>false]],'fuente'=>''],
 'programas'=>array_replace($seed['programas'][0],['id'=>$id,'nombre'=>$tag.' Programa de prueba','slug'=>'qa-programa-'.$i,'codigo'=>'QAP'.$i,'resumen'=>'Programa sintético para verificar el editor; no forma parte de la oferta institucional.','coordinadorId'=>'qa2-'.$i,'orden'=>90+$i,'estado'=>'Borrador']),
 'noticias'=>['id'=>$id,'titulo'=>$tag.' Noticia de control de calidad','slug'=>'qa-noticia-'.$i,'categoriaId'=>$id,'fecha'=>$stamp,'resumen'=>'Publicación QA identificada: comprobación del editor y la visualización pública.','contenido'=>'<h2>Contenido de prueba QA</h2><p>Registro sintético '.$i.'. No anuncia hechos ni actividades reales de Nasca.</p><p><a href="#/paginas/qa-pagina-'.$i.'">Página QA relacionada</a></p>','imagen'=>$i===1?$logo:$photo,'imagenAjuste'=>$i===1?'contain':'cover','video'=>'','adjuntos'=>[$file],'destacada'=>false,'autor'=>'QA local','estado'=>'Publicada'],
 'paginas'=>['id'=>$id,'clave'=>'custom-qa-'.$i,'titulo'=>$tag.' Página adicional','slug'=>'qa-pagina-'.$i,'contenido'=>'<h2>Página de prueba QA '.$i.'</h2><p>Contenido sintético para verificar creación, edición y publicación de páginas adicionales.</p>','imagen'=>$photo,'mensaje'=>'Prueba QA; no es un comunicado institucional.','versiones'=>[],'estado'=>'Publicada'],
 'eventos'=>['id'=>$id,'titulo'=>$tag.' Evento de prueba','fecha'=>date('Y-m-d',strtotime('+'.$i.' days')).'T10:00:00-05:00','lugar'=>'Entorno QA local, sin convocatoria real','descripcion'=>'Evento sintético para comprobar el calendario y la publicación.','imagen'=>$photo,'estado'=>'Publicado'],
 'inversiones'=>['id'=>$id,'titulo'=>$tag.' Inversión de prueba','tipo'=>'Otros recursos','fuente'=>'Datos sintéticos QA','monto'=>$i,'fecha'=>$today,'estadoAvance'=>'Prueba QA sin ejecución real','descripcion'=>'Registro ficticio de control de calidad; no es una inversión de Nasca.','imagen'=>$photo,'documento'=>$file,'estado'=>'Publicado'],
 'convocatorias'=>['id'=>$id,'titulo'=>$tag.' Convocatoria de prueba','tipo'=>'Otros','fecha'=>$today,'situacion'=>'Estado no confirmado','descripcion'=>'Prueba QA; no convoca postulantes.','bases'=>$file,'etapas'=>[],'estado'=>'Publicado'],
 'documentos'=>['id'=>$id,'grupo'=>'qa-documento-'.$i,'nombre'=>$tag.' Documento de prueba','tipoId'=>$id,'descripcion'=>'Archivo QA sin valor institucional.','vigencia'=>'QA 2026','version'=>'1.0','archivo'=>$file,'estado'=>'Publicado'],
 'tupa'=>['id'=>$id,'anio'=>2090+$i,'categoria'=>'Trámites','codigo'=>'QA-'.$i,'denominacion'=>$tag.' Concepto de prueba','monto'=>$i,'cuenta'=>'Sin pago: prueba QA','requisitos'=>'No realizar pagos; registro sintético.','estado'=>'Publicado'],
 'jerarquica'=>['id'=>$id,'personaId'=>$id,'cargo'=>$tag.' Cargo sintético','unidad'=>'QA','orden'=>90+$i,'esDirector'=>false,'estado'=>'Publicado'],
 'locales'=>['id'=>$id,'nombre'=>$tag.' Local de prueba','telefono'=>'000000000','direccion'=>'Entorno de pruebas QA, sin dirección real','correo'=>'qa'.$i.'@example.test','horario'=>'Sin atención real: QA','lat'=>null,'lng'=>null,'foto'=>$photo,'estado'=>'Publicado','orden'=>90+$i],
 'reconocimientos'=>['id'=>$id,'anio'=>2026,'titulo'=>$tag.' Reconocimiento de prueba','descripcion'=>'Dato ficticio para validar el editor; no representa un reconocimiento obtenido.','resolucion'=>'QA sin valor legal','foto'=>$photo,'estado'=>'Publicado'],
 'egresados'=>['id'=>$id,'programaId'=>$program,'anio'=>2025,'nombres'=>$tag.' Egresado sintético','testimonio'=>'Testimonio ficticio QA; no corresponde a un egresado real.','foto'=>$photo,'estado'=>'Publicado'],
 'certificados'=>['id'=>$id,'codigo'=>'QA-CERT-NASCA-'.$i,'dni'=>'9000000'.$i,'nombres'=>$tag.' Persona sintética','programaId'=>$program,'tipo'=>'Constancia de estudios','fecha'=>$today,'estado'=>'Válido'],
 'becarios'=>['id'=>$id,'periodoId'=>$id,'apellidos'=>'Sintético QA','nombres'=>'Beneficiario '.$i,'dni'=>'','programaId'=>$program,'tipoId'=>$id,'motivo'=>'Prueba QA sin beneficio real','estado'=>'Publicado'],
 'enlaces'=>['id'=>$id,'nombre'=>$tag.' Enlace','grupo'=>'Institucional','url'=>'https://istpnasca.edu.pe/?qa='.$i,'verificado'=>'','estado'=>'Activo'],
 'servicios'=>['id'=>$id,'nombre'=>$tag.' Servicio sintético','descripcion'=>'Servicio de prueba QA sin prestación real.','horario'=>'Solo pruebas','costo'=>'Sin pago','ubicacion'=>'Entorno QA','responsable'=>'QA','icono'=>'book','estado'=>'Publicado','orden'=>90+$i],
 'slides'=>['id'=>$id,'titulo'=>$tag.' Carrusel','texto'=>'Control de calidad: contenido sintético','boton'=>'Ver página QA','enlace'=>'#/paginas/qa-pagina-'.$i,'imagen'=>$photo,'orden'=>90+$i,'estado'=>'Publicado'],
 'admisiones'=>array_replace($seed['admisiones'][0],['id'=>$id,'anio'=>2090+$i,'titulo'=>$tag.' Admisión sintética','vigente'=>false,'descripcion'=>'QA sin convocatoria real.','estado'=>'Publicado','resultados'=>[]]),
 'matriculas'=>array_replace($seed['matriculas'][0],['id'=>$id,'periodo'=>(2090+$i).'-I','titulo'=>$tag.' Matrícula sintética','vigente'=>false,'inicioClases'=>$today,'descripcion'=>'QA sin proceso real.','estado'=>'Cerrado']),
 'estadisticas'=>['id'=>$id,'anio'=>2090+$i,'programaId'=>$program,'postulantes'=>$i,'ingresantes'=>$i,'matriculados'=>$i,'egresados'=>0],
 ];
 foreach($fixtures as $collection=>$row){$row['_qaRun']='QA-NASCA-DOS';record($collection,$row);check(PortalPolicy::find($app->bootstrap()['data'][$collection],'id',$id)!==null,'Crear '.$collection.' '.$i);$report['modules'][$collection]['created'][]=$id;}
 $username='qa-editor-'.$i;if(!PortalPolicy::find($app->bootstrap()['data']['usuarios'],'usuario',$username))$app->createAccount(['username'=>$username,'name'=>'Editor QA '.$i,'email'=>'qa-editor-'.$i.'@example.test','password'=>'CuentaQA-Sintetica-2026!','role'=>'imagen']);
 $report['modules']['usuarios']['created'][]=$username;check((bool)PortalPolicy::find($app->bootstrap()['data']['usuarios'],'usuario',$username),'Cuenta QA '.$i);
 // Cada serie académica conserva relaciones propias, evitando cruces entre los dos juegos.
 $n=920000+1000*$i;
 $af=[
 'programas'=>['id'=>$n,'cod'=>'QAA'.$i,'nombre'=>$tag.' Programa académico','dur'=>'3 años','sem'=>6,'activo'=>true],
 'uds'=>['id'=>$n,'cod'=>'QAUD'.$i,'nombre'=>$tag.' Unidad didáctica','prog'=>$n,'ciclo'=>'VI','horas'=>2,'cred'=>2],
 'docentes'=>['id'=>$n,'per'=>$id,'nom'=>'Docente QA '.$i,'dni'=>'','correo'=>'qa-doc'.$i.'@example.test','uds'=>[$n],'dias'=>['Lun','Mar'],'desde'=>'07:00','hasta'=>'22:00'],
 'ambientes'=>['id'=>$n,'cod'=>'QAAULA'.$i,'nombre'=>'Aula QA '.$i,'tipo'=>'Aula','cap'=>20,'ubic'=>'Entorno QA','prog'=>$n],
 'modulos'=>['id'=>$n,'prog'=>$n,'cod'=>'QAMOD'.$i,'nombre'=>'Módulo QA '.$i,'ciclos'=>'VI'],
 'estudiantes'=>['id'=>$n,'dni'=>'9000010'.$i,'nom'=>$tag.' Alumno sintético','prog'=>$n,'periodo'=>'2026-II','ciclo'=>'VI','sec'=>'A','turno'=>'Mañana','estado'=>'Egresado','correo'=>'qa-alumno'.$i.'@example.test','tel'=>'000000000'],
 'areas'=>['id'=>$n,'cod'=>'QAAREA'.$i,'nivel'=>'Áreas','nombre'=>'Área QA '.$i,'resp'=>'Responsable QA','correo'=>'qa-area'.$i.'@example.test','activo'=>true],
 'tramites'=>['id'=>$n,'nombre'=>'Trámite QA '.$i,'area'=>'QAAREA'.$i,'dias'=>2,'req'=>'Documento sintético'],
 'empresas'=>['id'=>$n,'ruc'=>ruc('209999990'.$i),'nom'=>$tag.' Empresa sintética','sector'=>'QA','ubic'=>'Entorno de pruebas','contacto'=>'QA','correo'=>'qa-empresa'.$i.'@example.test','validada'=>true,'estadoVal'=>'Aprobada'],
 'ofertas'=>['id'=>$n,'emp'=>$n,'puesto'=>$tag.' Oferta sintética','desc'=>'Oferta QA, no postular.','req'=>'Prueba de flujo','modal'=>'Presencial','prog'=>$n,'vac'=>1,'limite'=>date('Y-m-d',strtotime('+7 days')),'estado'=>'Publicada'],
 'certs'=>['id'=>$n,'est'=>$n,'mod'=>$n,'fecha'=>$today,'num'=>'QA-MOD-NASCA-'.$i],
 'notas'=>['id'=>$n,'est'=>$n,'ud'=>'Unidad QA '.$i,'periodo'=>'2026-II','nota'=>16+$i],
 'titulos'=>['id'=>$n,'est'=>$n,'culm'=>$today,'tit'=>$today,'cod'=>'QA-TIT-NASCA-'.$i,'resol'=>'QA sin valor legal'],
 'sesiones'=>['id'=>$n,'per'=>'2026-II','prog'=>$n,'ciclo'=>'VI','sec'=>'A','turno'=>'Mañana','ud'=>$n,'doc'=>$n,'amb'=>$n,'dia'=>'Lun','ini'=>'08:00','fin'=>'09:00'],
 ];
 foreach($af as $collection=>$row){$row['_qaRun']='QA-NASCA-DOS';academic($collection,$row);check(PortalPolicy::find($app->bootstrap()['data']['academico'][$collection],'id',$n)!==null,'Crear académico '.$collection.' '.$i);$report['modules']['academico.'.$collection]['created'][]=$n;}
 change(function(&$d)use($n){$d['academico']['publicados'][$n.'|VI|A|Mañana|2026-II']=['fecha'=>date('Y-m-d H:i'),'por'=>'QA local'];});$report['modules']['academico.publicados']['created'][]=$n.'|VI|A|Mañana|2026-II';check(true,'Horario QA publicado '.$i);
 // Prueba de los formularios públicos con desafío real del servidor, repetibilidad y seguimiento.
 foreach(['mensajes','reclamos','expedientes','suscriptores'] as $purpose){
  $challenge=$app->challenge(['purpose'=>$purpose]);$proof=['challenge_id'=>$challenge['id'],'answer'=>$_SESSION['challenges'][$challenge['id']]['answer'],'accept'=>true,'declare'=>true];$uuid=bin2hex(random_bytes(16));$uuid=substr($uuid,0,8).'-'.substr($uuid,8,4).'-4'.substr($uuid,13,3).'-a'.substr($uuid,17,3).'-'.substr($uuid,20,12);
  $row=match($purpose){
   'mensajes'=>['nombres'=>$tag.' Mensaje QA','correo'=>'qa-contacto'.$i.'@example.test','asunto'=>$tag.' Consulta de prueba','mensaje'=>'Mensaje de control de calidad; no requiere atención institucional.'],
   'reclamos'=>['nombres'=>$tag.' Reclamo QA','tipo'=>'Reclamo','tipoDoc'=>'DNI','numDoc'=>'9000020'.$i,'correo'=>'qa-reclamo'.$i.'@example.test','condicion'=>'Visitante','fechaHecho'=>$today,'descripcion'=>'Registro ficticio QA para validar seguimiento; no corresponde a un reclamo real.','pedido'=>'Prueba QA, sin pedido institucional.'],
   'expedientes'=>['nombres'=>$tag.' Trámite QA','tipoPersona'=>'Persona natural','tipoDoc'=>'DNI','numDoc'=>'9000030'.$i,'correo'=>'qa-tramite'.$i.'@example.test','telefono'=>'000000000','tipoTramite'=>'Solicitud','asunto'=>$tag.' Expediente de prueba','folios'=>1,'areaDestino'=>'MP','archivos'=>[['original'=>'QA-prueba.pdf','data'=>'data:application/pdf;base64,'.base64_encode($pdf)]]],
   'suscriptores'=>['correo'=>'qa-boletin'.$i.'@example.test']
  };
  $input=['record'=>$row,'idempotency_key'=>$uuid,'proof'=>$proof];usleep(7000000);$result=$app->submit($purpose,$input);check(in_array($result['status'],[200,201],true),'Formulario '.$purpose.' '.$i);usleep(7000000);$again=$app->submit($purpose,$input);check($again['status']===200,'Idempotencia '.$purpose.' '.$i);$report['modules'][$purpose]['created'][]=$result['record']['id']??$uuid;
 }
}
foreach($report['modules'] as $module=>$evidence)check(count($evidence['created'])>=2,'Dos registros por módulo: '.$module);
$report['finished']=date(DATE_ATOM);$out=getenv('NASCA_QA_REPORT')?:'/qa/two-records.json';file_put_contents($out,json_encode($report,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
echo json_encode(['modules'=>count($report['modules']),'checks'=>$report['checks'],'scope'=>'local'],JSON_PRETTY_PRINT),"\n";ob_end_flush();
