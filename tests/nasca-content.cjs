/* Identidad, trazabilidad y relaciones del catálogo institucional de Nasca. */
const fs=require('node:fs'),path=require('node:path'),assert=require('node:assert/strict'),crypto=require('node:crypto'),vm=require('node:vm');
const root=path.resolve(__dirname,'..');
const data=JSON.parse(fs.readFileSync(path.join(root,'portal-private/data/portal.json')));
const sources=JSON.parse(fs.readFileSync(path.join(root,'portal-private/data/nasca-sources.json')));
assert.equal(data.config.inst.nombreCorto,'IESTP “Nasca”');
assert.equal(data.config.inst.fechaCreacion,'1980-01-10');
assert.equal(data.config.inst.codigoModular,'1116557');
assert.equal(data.config.inst.telefono,'056-522681');
assert.deepEqual(data.config.home.cifras.map(c=>c.valor),['auto:anios','auto:programas','1980']);
assert.equal(data.academico.cfg.nombre,data.config.inst.nombre);
assert.equal(data.programas.length,8);assert.equal(data.personas.length,59);
assert.equal(data.personas.filter(p=>p.variantesPublicadas).length,2);
assert.deepEqual(data.programas.map(p=>p.id),['cont','enf','got','pa','apsti','ei','eli','ma']);
assert.equal(data.tupa.length,40);assert.equal(data.tupaDocs.anio,2025);
assert.equal(data.becarios.length,37);assert.equal(data.becasPeriodos[0].anio,2025);assert.equal(data.becasPeriodos[0].vigente,false);
assert.equal(data.matriculas[0].periodo,'2026-I');assert.equal(data.matriculas[0].vigente,false);
assert.match(data.admisiones[0].descripcion,/9 de abril.*29 de marzo/);
assert.equal(data.admisiones[0].reglamento,null);
assert.equal(data.licenciamiento.resolucion,'');assert.equal(data.config.inst.lat,null);
const exists=(rows,id)=>rows.some(r=>r.id===id);
for(const j of data.jerarquica)assert.ok(exists(data.personas,j.personaId));
for(const p of data.programas){
 assert.ok(exists(data.personas,p.coordinadorId),p.nombre);
 assert.ok(data.personas.find(r=>r.id===p.coordinadorId).programas.includes(p.id));
 assert.ok(p.modulos.length>=3);assert.ok(p.malla.url.startsWith('/assets/nasca/'));
 assert.ok(p.malla.fuente.includes('2024'));assert.ok(p.modulos.every(m=>m.uds.length===0),'No inventar unidades, horas ni créditos de la malla PDF');
 assert.ok(data.academico.programas.some(a=>a.id===data.academico.progMap[p.id]&&a.pid===p.id));
}
for(const b of data.becarios){assert.ok(exists(data.programas,b.programaId));assert.ok(exists(data.becasPeriodos,b.periodoId));assert.ok(exists(data.tiposBeca,b.tipoId));assert.equal(b.dni,'');}
for(const person of data.personas){assert.equal(person.dni,'');assert.equal(person.cv.autoriza.ok,false);assert.ok(person.fuente.startsWith('https://istpnasca.edu.pe/'));assert.ok(person.nombres);}
for(const key of ['estudiantes','notas','certs','titulos','empresas','ofertas','sesiones'])assert.equal(data.academico[key].length,0,key+' debe comenzar sin datos operativos ficticios');
assert.equal(data.certificados.length,0);
const unique=new Set();for(const row of data.tupa){assert.equal(row.anio,2025);assert.ok(Number.isFinite(row.monto)&&row.monto>=0);assert.ok(!unique.has(row.codigo));unique.add(row.codigo);assert.match(row.cuenta,/00611009305/);}
assert.equal(data.tupa.find(r=>r.codigo==='23').monto,80);
assert.equal(data.estadisticas.filter(s=>s.anio===2025).reduce((n,s)=>n+s.postulantes,0),406);
assert.ok(data.estadisticas.filter(s=>s.anio===2025).every(s=>s.matriculados===undefined),'No inventar matrícula 2025 ausente en fuente');
let refs=0;
function inspect(value){
 if(typeof value==='string'){
  assert.ok(!/chincha|iestpchincha|056-480141|Doris Elizabeth|Profesional de Ejemplo|ph:/i.test(value),value.slice(0,100));
  for(const url of value.match(/\/assets\/nasca\/[A-Za-z0-9_.-]+/g)||[]){assert.ok(fs.existsSync(path.join(root,'public_html',url)),url);refs++;}
 }else if(Array.isArray(value))value.forEach(inspect);
 else if(value&&typeof value==='object')Object.values(value).forEach(inspect);
}
inspect(data);assert.ok(refs>100);
for(const asset of sources.archivos){
 const bytes=fs.readFileSync(path.join(root,'public_html',asset.url));
 assert.equal(bytes.length,asset.bytes);assert.equal(crypto.createHash('sha256').update(bytes).digest('hex'),asset.sha256);
 assert.equal(new URL(asset.fuente).hostname,'istpnasca.edu.pe');
 if(asset.type==='application/pdf')assert.equal(bytes.subarray(0,5).toString(),'%PDF-');
}
const view=fs.readFileSync(path.join(root,'portal-private/views/portal.php'),'utf8');
for(const script of view.matchAll(/<script(?:\s[^>]*)?>([\s\S]*?)<\/script>/g))if(!script[1].includes('<?'))new vm.Script(script[1]);
assert.ok(!/OF_DOC|OF_NEWS|Profesional de Ejemplo/.test(view),'Sin catálogos de demostración heredados');
console.log(`Nasca: 8 programas, 59 fichas, 40 tasas, 37 becarios, relaciones y ${sources.archivos.length} archivos oficiales verificados; sin contenido heredado.`);
