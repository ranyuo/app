/* QA local: dos registros persistidos por módulo y dos altas editoriales desde formularios. */
const {chromium}=require('playwright');const assert=require('node:assert/strict'),fs=require('node:fs');
const base=process.env.PISCO_BASE_URL||'http://127.0.0.1:8087';
if(!['127.0.0.1','localhost'].includes(new URL(base).hostname))throw Error('Solo base QA local; no utilizar contra producción.');
(async()=>{
 const report={scope:'local',base,editors:[],public:[],academic:[],config:[],errors:[],checks:0};
 const browser=await chromium.launch({executablePath:process.env.CHROMIUM_PATH||'/usr/bin/chromium',args:['--no-sandbox'],headless:true});
 try{
  const context=await browser.newContext({viewport:{width:1440,height:900},reducedMotion:'reduce'});await context.route('https://**/*',r=>r.abort());
  const p=await context.newPage();p.on('pageerror',e=>report.errors.push(e.message));p.setDefaultTimeout(12000);
  await p.goto(base+'/#/admin');await p.locator('#lg-u').fill('admin');await p.locator('#lg-p').fill('PruebaQA-Nasca-2026!');await p.locator('[data-login] button[type=submit]').click();await p.waitForFunction(()=>window.PORTAL_BOOTSTRAP?.user?.rol==='admin');
  const validations=await p.evaluate(()=>{
   const beneficiary=DB.becarios.find(r=>r.id==='qa2-1');
   return {
    emptyCoordinates:SPECS.locales.validate({lat:null,lng:null}),
    partialCoordinates:SPECS.locales.validate({lat:-14.8,lng:null}),
    validCoordinates:SPECS.locales.validate({lat:-14.8,lng:-74.9}),
    distinctWithoutDNI:SPECS.becas.validate({...beneficiary,id:uid(),nombres:'Otra persona QA sin DNI',dni:''}),
    duplicateWithoutDNI:SPECS.becas.validate({...beneficiary,id:uid(),dni:''})
   };
  });
  assert.deepEqual(validations.emptyCoordinates,{});assert.ok(validations.partialCoordinates.lat);assert.deepEqual(validations.validCoordinates,{});assert.deepEqual(validations.distinctWithoutDNI,{});assert.ok(validations.duplicateWithoutDNI.dni);report.checks+=5;
  const specs=await p.evaluate(()=>Object.entries(SPECS).map(([key,s])=>({key,coll:s.coll})));
  for(const {key,coll} of specs){
   const ids=await p.evaluate(coll=>DB[coll].filter(r=>r._qaRun==='QA-NASCA-DOS'||coll==='usuarios'&&r.usuario?.startsWith('qa-editor-')).slice(0,2).map(r=>r.id),coll);
   assert.equal(ids.length,2,'Dos registros '+key);
   for(const id of ids){
    await p.goto(base+'/#/admin/'+key+'/'+id);await p.locator('[data-cf]').waitFor();
    const field=await p.evaluate(()=>['titulo','nombre','nombres','cargo','denominacion','descripcion','anio'].find(n=>{const e=document.querySelector('[data-cf] [name="'+n+'"]');return e&&!e.disabled&&['text','textarea','number'].includes(e.type)&&(e.maxLength<0||e.maxLength>30)}));assert.ok(field,key);
    const el=p.locator('[data-cf] [name="'+field+'"]');const old=await el.inputValue();const next=field==='anio'?String(Number(old)-10):old+' · QA editado';await el.fill(next);await p.getByRole('button',{name:'Guardar cambios',exact:true}).click();
    if(await p.locator('[data-cf]').count())throw Error('Guardar '+key+': '+JSON.stringify(await p.locator('.errmsg,.toast').allTextContents()));
    const actual=await p.evaluate(async({coll,id,field})=>{const j=await(await fetch('/index.php?route=api%2Fportal')).json();return j.data[coll].find(r=>r.id===id)?.[field]},{coll,id,field});assert.equal(String(actual),String(next),'Edición persistida '+key);report.checks++;
    await p.goto(base+'/#/admin/'+key+'/'+id);await p.locator('[name="'+field+'"]').fill(old);await p.getByRole('button',{name:'Guardar cambios',exact:true}).click();
    report.editors.push({module:key,id,created:true,edited:true,reverted:true});console.log('Editor',key,id);
   }
  }
  await p.evaluate(()=>{ for(const coll of ['noticias','paginas','eventos'])DB[coll]=DB[coll].filter(r=>r._qaRun!=='QA-NASCA-UI');if(!saveDB())throw Error('No se pudo limpiar exclusivamente la serie QA UI local.');});
  // Crear dos noticias, páginas y eventos adicionales mediante los editores y el envío real del formulario.
  for(const key of ['noticias','paginas','eventos'])for(let index=1;index<=2;index++){
   await p.goto(base+'/#/admin/'+key+'/nuevo');await p.locator('[data-cf]').waitFor();
   const fixture=await p.evaluate(({key,index})=>{
    const source=DB[SPECS[key].coll].find(r=>r._qaRun==='QA-NASCA-DOS');const work=structuredClone(source);Object.assign(work,{id:uid(),titulo:'QA-NASCA-UI-'+key+'-'+index,slug:'qa-ui-'+key+'-'+index,estado:key==='paginas'||key==='noticias'?'Publicada':'Publicado',_qaRun:'QA-NASCA-UI'});
    if(key==='paginas'){work.clave='';work.versiones=[];}if(key==='eventos')work.fecha=new Date(Date.now()+86400000*index).toISOString();
    ADM.work=work;ADM.workNew=true;ADM.workKey=key;route();return {id:work.id,coll:SPECS[key].coll};
   },{key,index});
   // Texto escrito en el campo real, distinto del modelo inicial.
   const title='QA-NASCA-UI '+key+' '+index;await p.locator('[name=titulo]').fill(title);await p.getByRole('button',{name:'Guardar cambios',exact:true}).click();
   if(await p.locator('[data-cf]').count())throw Error('Crear '+key+': '+JSON.stringify(await p.locator('.errmsg,.toast').allTextContents()));
   const stored=await p.evaluate(async({id,coll})=>{const j=await(await fetch('/index.php?route=api%2Fportal')).json();return j.data[coll].find(r=>r.id===id)},fixture);assert.equal(stored.titulo,title);report.checks++;report.editors.push({module:key,id:stored.id,createdThroughUI:true});
  }
  // Dos escenarios de configuración: desactivar/reactivar una sección y cambiar/restaurar el orden.
  await p.goto(base+'/#/admin/dashboard');
  const config=await p.evaluate(()=>structuredClone(DB.config.home));
  for(let scenario=0;scenario<2;scenario++){
   await p.evaluate(scenario=>{if(scenario===0)DB.config.home.bloques.find(b=>b.k==='eventos').on=false;else DB.config.home.bloques.reverse();if(!saveDB())throw Error('Guardar configuración QA');},scenario);
   await p.reload();const actual=await p.evaluate(()=>DB.config.home);
   if(scenario===0)assert.equal(actual.bloques.find(b=>b.k==='eventos').on,false);else assert.equal(actual.bloques[0].k,config.bloques.at(-1).k);report.checks++;report.config.push({module:'Portada',scenario,passed:true});
   await p.evaluate(config=>{DB.config.home=config;if(!saveDB())throw Error('Restaurar configuración QA');},config);
  }
  // Renderizar la intranet académica y verificar dos registros por catálogo en datos del servidor.
  for(const [module,collection] of [['ix-alumnos','estudiantes'],['ix-horarios','sesiones'],['ix-certificados','certs'],['ix-titulos','titulos'],['ix-bolsa/gestion','ofertas']]){
   await p.goto(base+'/#/admin/'+module);await p.locator('#ix-root').waitFor();const count=await p.evaluate(collection=>DB.academico[collection].filter(r=>r._qaRun==='QA-NASCA-DOS').length,collection);assert.equal(count,2);report.academic.push({module,collection,records:count});report.checks++;
  }
  const academicCases=[['ix-alumnos',[],'estudiantes','tel'],['ix-horarios',['horarios:config','hconf:uds'],'uds','nombre'],['ix-horarios',['horarios:config','hconf:docentes'],'docentes','hasta'],['ix-horarios',['horarios:config','hconf:ambientes'],'ambientes','ubic'],['ix-certificados',['certificados:modulos'],'modulos','nombre'],['ix-certificados',['certificados:certs'],'certs','num'],['ix-certificados',['certificados:notas'],'notas','nota'],['ix-titulos',['titulos:registro'],'titulos','resol'],['ix-bolsa/gestion',['bolsa:ofertas'],'ofertas','puesto'],['ix-bolsa/gestion',['bolsa:empresas'],'empresas','sector']];
  for(const [module,tabs,collection,field] of academicCases)for(const id of [921000,922000]){
   const open=async()=>{await p.goto(base+'/#/admin/'+module);for(const tab of tabs)await p.locator('[data-tab="'+tab+'"]').click();await p.locator('[data-edit="'+collection+':'+id+'"]').click();await p.locator('#mform [name="'+field+'"]').waitFor();};
   await open();const input=p.locator('#mform [name="'+field+'"]');const original=await input.inputValue();const edited=field==='nota'?'15':field==='hasta'?'21:00':field==='tel'?'000000001':original+' QA';
   await input.fill(edited);await p.locator('#mform button[type=submit]').click();if(await p.locator('#mform').count())throw Error('Edición académica '+collection+': '+JSON.stringify(await p.locator('#mform .err,.toast').allTextContents()));
   const value=await p.evaluate(async({collection,id,field})=>{const j=await(await fetch('/index.php?route=api%2Fportal')).json();return j.data.academico[collection].find(r=>r.id===id)?.[field]},{collection,id,field});assert.equal(String(value),edited);report.checks++;
   await open();await p.locator('#mform [name="'+field+'"]').fill(original);await p.locator('#mform button[type=submit]').click();assert.equal(await p.locator('#mform').count(),0);report.checks++;report.academic.push({module,collection,id,edited:true,reverted:true});console.log('Académico',collection,id);
  }
  await p.goto(base+'/#/admin/noticias');await p.screenshot({path:'/workspace/nasca-qa/intranet-noticias.png',fullPage:true});
  const previewId=await p.evaluate(()=>DB.noticias.find(r=>r.id==='qa2-1').id);await p.goto(base+'/#/admin/noticias/'+previewId);assert.equal(await p.locator('.img-field img').first().evaluate(e=>getComputedStyle(e).objectFit),'contain');report.checks++;
  await p.screenshot({path:'/workspace/nasca-qa/intranet-editor-imagen.png',fullPage:true});
  const guest=await browser.newContext({viewport:{width:1440,height:900}});await guest.route('https://**/*',r=>r.abort());const g=await guest.newPage();g.on('pageerror',e=>report.errors.push(e.message));
  for(let i=1;i<=2;i++)for(const [route,text] of [['noticias/qa-noticia-'+i,'QA-NASCA-DOS-'+i],['paginas/qa-pagina-'+i,'QA-NASCA-DOS-'+i],['paginas/qa-ui-paginas-'+i,'QA-NASCA-UI paginas '+i]]){
   await g.goto(base+'/#/'+route);assert.ok((await g.locator('main').innerText()).includes(text),route);report.public.push({route,passed:true});report.checks++;
  }
  await g.goto(base+'/#/buscar?q=QA-NASCA-DOS-1');assert.ok((await g.locator('main').innerText()).includes('Página adicional'));report.checks++;
  await g.goto(base+'/#/eventos');assert.ok((await g.locator('main').innerText()).includes('QA-NASCA-DOS-1'));assert.ok((await g.locator('main').innerText()).includes('QA-NASCA-DOS-2'));report.checks+=2;
  await g.goto(base+'/#/noticias/qa-noticia-1');assert.equal(await g.locator('[data-adj]').count(),1);await g.locator('[data-adj]').click();report.checks++;
  await g.goto(base+'/#/noticias');const logo=g.locator('.ncard img[src*="logo-"]').first();assert.equal(await logo.evaluate(e=>getComputedStyle(e).objectFit),'contain');report.checks++;assert.equal(await g.locator('.ncard img[data-fit=cover]').first().evaluate(e=>getComputedStyle(e).objectFit),'cover');report.checks++;
  for(const width of [390,1440]){await g.setViewportSize({width,height:900});await g.evaluate(()=>Promise.all([...document.images].map(i=>{i.loading='eager';return i.decode().catch(()=>{});})));assert.ok(await g.evaluate(()=>document.documentElement.scrollWidth<=innerWidth+2));await g.screenshot({path:'/workspace/nasca-qa/public-noticias-'+width+'.png',fullPage:true});report.checks++;}
  assert.deepEqual(report.errors,[]);report.finished=new Date().toISOString();fs.writeFileSync('/workspace/nasca-qa/browser-two-records.json',JSON.stringify(report,null,2));console.log(JSON.stringify({editors:report.editors.length,public:report.public.length,checks:report.checks,errors:report.errors}));
 }finally{await browser.close()}
})().catch(e=>{console.error(e);process.exit(1)});
