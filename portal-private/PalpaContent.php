<?php
declare(strict_types=1);

/** Importa contenido público sin borrar cuentas, solicitudes ni expedientes académicos. */
final class PalpaContent
{
    public static function apply(PortalApp $app): array
    {
        $app->requireAdmin();
        $seed=json_decode(file_get_contents(__DIR__.'/data/portal.json'),true,flags:JSON_THROW_ON_ERROR);
        if(($seed['config']['inst']['nombreCorto']??'')!=='IES Público “Palpa”')throw new PortalError(422,'El paquete no contiene la información institucional de Palpa.');
        $before=$app->bootstrap();$next=$before['data'];
        foreach(['slides','enlaces','paginas','reconocimientos','programas','personas','jerarquica','egresados','categoriasNoticia','noticias','eventos','tiposDocumento','documentos','tupa','tupaDocs','estadisticas','licenciamiento','inversiones','convocatorias','admisiones','matriculas','tiposBeca','becasPeriodos','becarios','locales','servicios'] as $key)$next[$key]=$seed[$key];
        // Las personas y los programas antiguos pueden estar vinculados a cuentas o expedientes.
        // Se conservan fuera de la publicación, con sus identificadores originales.
        foreach(['personas','programas'] as $key){
            $ids=array_column($next[$key],'id');
            foreach($before['data'][$key] as $old)if(!in_array($old['id'],$ids,true)){$old['estado']=$key==='programas'?'Borrador':'Despublicado';$next[$key][]=$old;}
        }
        foreach(['inst','redes','whatsapp','seo','correoContacto','correoReclamos','popup','home'] as $key)$next['config'][$key]=$seed['config'][$key];
        foreach(['correo','telefono','prefijo','dias','horaIni','horaFin'] as $key)$next['config']['mpv'][$key]=$seed['config']['mpv'][$key];
        $next['config']['temas']['pisco']=$seed['config']['temas']['pisco'];
        $academic=&$next['academico'];
        foreach(['nombre','siglas','director','direccion','web','horarioAtencion'] as $key)$academic['cfg'][$key]=$seed['academico']['cfg'][$key];
        $academic['cfg']['docs']['logoIzq']=$seed['academico']['cfg']['docs']['logoIzq'];
        $academic['cfg']['docsLogoInit']=true;
        // Retirar únicamente el contacto heredado conocido; respetar correos operativos propios.
        foreach($academic['areas'] as &$area)if(preg_match('/@(?:www\.)?(?:iestpchincha|istpnasca)\.edu\.pe$/i',$area['correo']??''))$area['correo']='mesadepartes@istpalpa.edu.pe';
        unset($area);
        // No reasignar el ID numérico de un programa existente: identifica a alumnos y documentos.
        $last=max([0,...array_column($academic['programas'],'id')]);
        foreach($seed['programas'] as $program){
            $id=$academic['progMap'][$program['id']]??null;
            $exists=$id!==null&&PortalPolicy::find($academic['programas'],'id',$id);
            if(!$exists){$id=++$last;$academic['programas'][]=['id'=>$id,'cod'=>$program['codigo'],'nombre'=>$program['nombre'],'dur'=>'3 años','sem'=>6,'activo'=>true,'pid'=>$program['id']];}
            foreach($academic['programas'] as &$record)if($record['id']===$id){$record['cod']=$program['codigo'];$record['nombre']=$program['nombre'];$record['pid']=$program['id'];$record['activo']=true;}
            unset($record);
            $academic['progMap'][$program['id']]=$id;
        }
        // Mantener el historial académico de programas retirados sin ofertarlos de nuevo.
        $published=array_column($seed['programas'],'id');
        foreach($academic['programas'] as &$record)if(!in_array($record['pid']??'', $published, true))$record['activo']=false;
        unset($record);
        unset($academic);
        $backup=PortalBackups::create($app,'antes-de-importar-palpa');
        $result=$app->save(['revision'=>$before['revision'],'data'=>$next]);
        return ['revision'=>$result['revision'],'backup'=>$backup['id']];
    }
}
